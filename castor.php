<?php

use Castor\Attribute\AsTask;
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

//import('src/Command/JeopardyCommand.php');

try {
    import('.castor/vendor/tacman/castor-tools/castor.php');
} catch (Throwable $e) {
    io()->error('castor composer install');
    io()->error($e->getMessage());
}

#[AsTask('congress:details', description: 'Fetch details from wikipedia')]
function congress_details(): void
{
    run('bin/console state:iterate Official --marking=new --transition=fetch_wiki');
    run('bin/console mess:stats');
    io()->writeln('make sure the message consumer is running');
}

#[AsTask('load:marvel', description: 'Fetch details from wikipedia')]
function marvel(): void
{
    run(sprintf('bin/console import:convert %s --output=%s --zip-path=marvel-search-master/records', 'zip/marvel.zip', 'data/marvel.jsonl'));
    $importCmd = sprintf(
        'bin/console import:entities App\\\\Entity\\\\Marvel data/marvel.jsonl',
    );
    run($importCmd);

}

/**
 * Return the available demo datasets, keyed by code.
 *
 * @return array<string, Dataset>
 */
function demo_datasets(): array
{

        $datasets = [
            new Dataset(
                name: 'amst_en',
                url: 'https://statics.belowthesurface.amsterdam/downloadbare-datasets/Downloadtabel_EN.csv',
                target: 'data/amst_en.csv',
            ),
            new Dataset(
                name: 'amst_nl',
                url: 'https://statics.belowthesurface.amsterdam/downloadbare-datasets/Downloadtabel_NL.csv',
                target: 'data/amst_nl.csv',
            ),
        ];
    foreach ($datasets as $dataset) {
        $map[$dataset->name] = $dataset;
    }
    return $map;
}

function wam(Dataset $dataset)
{
    $zipPath = 'zip/wam.zip';
    if (!file_exists($zipPath)) {
        throw new \RuntimeException(sprintf('WAM zip not found at %s', $zipPath));
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath) === true) {
        io()->writeln('Unzipping wam.zip');
        $destDir = __DIR__ . '/data/';
        if (!\is_dir($destDir)) {
            \mkdir($destDir, 0777, true);
        }
        $zip->extractTo($destDir, 'wam-dywer.csv');
        $zip->close();
        io()->writeln('WAM CSV was extracted to ' . realpath($dataset->target));
    } else {
        throw new \RuntimeException('Failed to open WAM ZIP file at ' . $zipPath);
    }

}

#[AsTask('download')]
function download(?string $code=null): void
{


    $map = demo_datasets();
    if ($code && !array_key_exists($code, $map)) {
        io()->error("The code '{$code}' does not exist: " . implode('|', array_keys($map)));
        return;
    }
    $datasets = $code ? [$map[$code]] : array_values($map);
    foreach ($datasets as $dataset) {
        if ($dataset->name === 'wam') {
            wam($dataset);
            continue;
        }
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
    #[\Castor\Attribute\AsArgument(description: 'Dataset code (e.g. wcma|car|wine|marvel|wam)')]
    string $code = '',
    #[Opt(description: 'Limit number of entities to import')]
    ?int $limit = null,
    #[\Castor\Attribute\AsOption(description: "reset the database")] bool $reset = false
): void {
    /** @var array<string, Dataset> $map */
    $map = demo_datasets();

    if ($code === '') {
        io()->writeln('Available dataset codes:');
        foreach ($map as $k => $dataset) {
            io()->writeln(sprintf('  - %s (%s)', $k, $dataset->target));
        }

        return;
    }

    if (!\array_key_exists($code, $map)) {
        io()->error("The code '{$code}' does not exist: " . implode('|', array_keys($map)));

        return;
    }

    $dataset = $map[$code];

    if (!$dataset->jsonl) {
        io()->warning("stopped, no jsonl, maybe run another command?");
        return;
    }
    $cmd = 'bin/console import:convert %s --dataset=%s';
    $convertCmd = sprintf(
        $cmd,
        $dataset->target,
        $dataset->name
    );
    io()->writeln($convertCmd);
    run($convertCmd);

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
    run($importCmd);
    // In the new world, code generation (code:entity) and templates are explicit steps.
    // This Castor task focuses on:
    //   download → convert/profile → import.
}
