<?php

use Castor\Attribute\AsTask;
use Survos\CoreBundle\Service\SurvosUtils;
use Survos\JsonlBundle\IO\JsonlReader;
use Survos\MeiliBundle\Model\Dataset;

use function Castor\{io, run, capture, import, http_download};

$autoloadCandidates = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
];
foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        break;
    }
}
use League\Csv\Reader;
use Castor\Attribute\{AsOption,AsArgument};

try {
    import('.castor/vendor/tacman/castor-tools/castor.php');
} catch (Throwable $e) {
    io()->error('castor composer install');
    io()->error($e->getMessage());
}

const EN_FILENAME='data/amst_english.csv';

/**
 * Return the available demo datasets, keyed by code.
 *
 * @return array<string, Dataset>
 */
function demo_datasets(): array
{
        $datasets = [
            new Dataset(
                name: 'amst',
                url: 'https://statics.belowthesurface.amsterdam/downloadbare-datasets/Downloadtabel_NL.csv',
                target: 'data/amst.csv',
            ),
        ];
    foreach ($datasets as $dataset) {
        $map[$dataset->name] = $dataset;
    }
    return $map;
}

#[AsTask('build')]
function build(
    #[AsOption("limit the number of records to import")] ?int $limit=null,
): void
{
    download(); // download the data
    load_database('amst', $limit); // exports to .jsonl and then imports
}

#[AsTask('translate', "Translate the strings to English in babel")]
function translate(): void
{
    run('bin/console babel:translate');
}

#[AsTask('download')]
function download(?string $code=null): void
{
    if (!file_exists(EN_FILENAME)) {
        $translationUrl = 'https://statics.belowthesurface.amsterdam/downloadbare-datasets/Downloadtabel_EN.csv';
        http_download($translationUrl, EN_FILENAME);
    }

    $map = demo_datasets();
    if ($code && !array_key_exists($code, $map)) {
        io()->error("The code '{$code}' does not exist: " . implode('|', array_keys($map)));
        return;
    }
    $datasets = $code ? [$map[$code]] : array_values($map);
    foreach ($datasets as $dataset) {
        // use fs()?
        if ($dataset->url) {
            if (!file_exists($dataset->target)) {
                $dir = \dirname($dataset->target);
                if ($dir !== '' && !\is_dir($dir)) {
                    \mkdir($dir, 0777, true);
                }

                io()->writeln(sprintf('Downloading %s → %s', $dataset->url, $dataset->target));
                http_download($dataset->url, $dataset->target);
                io()->writeln(realpath($dataset->target) . ' written');
                if ($dataset->afterDownload) {
                    io()->warning($dataset->afterDownload);
                    run($dataset->afterDownload);
                }

            } else {
                io()->writeln(sprintf('Target %s already exists, skipping download.', $dataset->target));
            }
        }
    }

    // now create a k/v lookup table using nl as the source

}

#[AsTask('dictionary')]
function createDictionary()
{

    $translatableFields = ['object', 'subcategorie', 'niveau1', 'niveau2', 'niveau3', 'niveau4'];

    $enReader = Reader::from('data/amst_english.csv');
    $enReader->setHeaderOffset(0);

    $nlReader = Reader::from('data/amst.csv');
    $nlReader->setHeaderOffset(0);

    $dict = [];

// Use MultipleIterator to keep both in sync
    $multiIterator = new MultipleIterator(MultipleIterator::MIT_NEED_ALL);
    $multiIterator->attachIterator($nlReader->getRecords());
    $multiIterator->attachIterator($enReader->getRecords());

    foreach ($multiIterator as [$nlRecord, $enRecord]) {
        // Sanity check: ensure rows are aligned
        assert($nlRecord['vondstnummer'] === $enRecord['vondstnummer'],
            sprintf('Row mismatch: %s vs %s', $nlRecord['vondstnummer'], $enRecord['vondstnummer']));

        foreach (array_keys($nlRecord) as $field) {
            $nlValue = trim($nlRecord[$field] ?? '');
            $enValue = trim($enRecord[$field] ?? '');

//            if ($nlValue !== '' && $enValue !== '' && $nlValue !== $enValue) {
                $dict[$nlValue] = $enValue;
//            }
        }
    }
    file_put_contents('data/amst_dictionary.json', json_encode($dict, JSON_PRETTY_PRINT));
    dd();


// Result: ['Spel & recreatie' => 'Games & Recreation', ...]

    $dict = [];
    $enReader = \League\Csv\Reader::from(EN_FILENAME);
    $enReader->setHeaderOffset(0);
//    $iterator = $enReader->getIterator();

//        $file = 'data/amst_nl.jsonl';

    $nlReader = \League\Csv\Reader::from('data/amst.csv');
    $nlReader->setHeaderOffset(0);
//    $iterator = $nlReader->getIterator();

//        $nlReader =    new JsonlReader($file);
//        $enReader = new JsonlReader(EN_FILENAME);
        $enIterator = $enReader->getIterator();
        $nlIterator = $nlReader->getIterator();
        $translatableFields = ['object', 'subcategorie', 'niveau1', 'niveau2', 'niveau3', 'niveau4'];
        $idx = 0;
//        dump($enIterator->current());
        foreach ($nlIterator as $csv) {
            $lookup = SurvosUtils::removeNullsAndEmptyArrays($csv);
            foreach ($lookup as $var=>$value) {
                if (!in_array($var, $translatableFields)) {
                    dump($var, $translatableFields);
                    continue;
                }
                $key = md5($value);
                $dict[$key] = $value;
            }
        }

        // loop through the English-language version and find the mapped keys

        foreach ($enIterator as $en) {
//        while ($en = $enIterator->current()) {
            $idx++;
            SurvosUtils::removeNullsAndEmptyArrays($en);
            dump($en);
            $srcRecord = $nlIterator->next();
            dd($srcRecord);
            $srcRecord = $nlIterator->current();
            $enIterator->next();
            $nlIterator->next();
//                $srcRecord = $enIterator->next();
            assert($srcRecord['code'] == $en['code']);
            foreach ($translatableFields as $translatableField) {
                if ($orig = $srcRecord[$translatableField] ?? null) {
                    if (!array_key_exists($translatableField, $en)) {
                        dd($srcRecord, $en, $translatableField);
                    }
                    SurvosUtils::assertKeyExists($translatableField, $en, "Missing $translatableField $orig in row " . $idx);
                    $trans = $en[$translatableField];
                    $key = md5($orig);
//                        $key = BabelRuntime::hash($trans, 'srcRecord');
                    if (!array_key_exists($key, $dict)) {
                        $dict[$key] = $trans;
                    }
                }
            }
            dd($dict);
        }
        dump(count($dict));
}

/**
 * Loads the database for a given demo dataset:
 *   - downloads the raw dataset (if needed)
 *   - runs import:convert to produce JSONL + profile
 *   - runs import:entities to import into Doctrine
 *
 * Code generation (code:entity) remains a separate, explicit step.
 */
#[AsTask('load', description: 'Loads the database for a demo dataset')]
function load_database(
    #[AsOption(description: 'Limit number of entities to import')]
    ?int $limit = null,
    #[AsOption(description: "reset the database")] bool $reset = false
): void {
    $code = 'amst';
    /** @var array<string, Dataset> $map */
    $map = demo_datasets();
    $dataset = $map[$code];

    if (!$dataset->jsonl) {
        io()->warning("stopped, no jsonl, maybe run another command?");
        return;
    }
    if (!file_exists($dataset->jsonl)) {
        $cmd = 'bin/console import:convert %s --dataset=%s';
        $convertCmd = sprintf(
            $cmd,
            $dataset->target,
            $dataset->name
        );
        io()->writeln($convertCmd);
        run($convertCmd);
    }

    // 4) Import entities into Doctrine.
    //
    // Note: This assumes your entity class is App\Entity\<Code>, e.g. App\Entity\Car
    // and that you've already generated the entity via code:entity.
    $limitArg = $limit ? sprintf(' --limit=%d', $limit) : '';
    $cmd = 'bin/console import:entities %s %s%s';
    if ($limit) {
        $cmd .= ' --limit ' . $limit;
    }
    if ($reset) {
        $cmd .= ' --reset';
    }
    $importCmd = sprintf(
        $cmd,
        ucfirst($code),
        $dataset->jsonl,
        $limitArg
    );
    io()->writeln($importCmd);
    dd($importCmd);
    run($importCmd);
    // In the new world, code generation (code:entity) and templates are explicit steps.
    // This Castor task focuses on:
    //   download → convert/profile → import.
}
