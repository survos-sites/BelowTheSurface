<?php

namespace App\Service;

use Survos\BabelBundle\Event\TranslateStringEvent;
use Survos\BabelBundle\Runtime\BabelRuntime;
use Survos\CoreBundle\Service\SurvosUtils;
use Survos\JsonlBundle\IO\JsonlReader;
use Survos\JsonlBundle\IO\JsonlReaderInterface;
use Survos\LibreTranslateBundle\Service\LibreTranslateService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class TranslationService
{

    public function __construct(
//        private JsonlReaderInterface $jsonlReader,
    )
    {
    }


    #[AsEventListener(TranslateStringEvent::class)]
    public function onTranslateStringEvent(TranslateStringEvent $event): void
    {
        static $dict = [];
        if (!$dict) {
            $file = 'data/amst_nl.jsonl';
            $nlReader = new JsonlReader($file);
            $enReader = new JsonlReader('data/amst_en.jsonl');
            $enIterator = $enReader->getIterator();
            $nlIterator = $nlReader->getIterator();
            $idx = 0;
            while ($nl = $nlIterator->current()) {
                $idx++;
                $en = $enIterator->current();
                $enIterator->next();
                $nlIterator->next();
//                $en = $enIterator->next();
                assert($en['code'] == $nl['code']);
                foreach (['object','subcategorie','niveau1','niveau2','niveau3','niveau4'] as $translatableField) {
                    if ($orig = $en[$translatableField] ?? null) {
                        if (!array_key_exists($translatableField, $nl)) {
                            dd($en, $nl, $translatableField);
                        }
                        SurvosUtils::assertKeyExists($translatableField, $nl, "Missing $translatableField $orig in row " . $idx);
                        $trans = $nl[$translatableField];
                        $key = md5($orig);
//                        $key = BabelRuntime::hash($trans, 'en');
                        if (!array_key_exists($key, $dict)) {
                            $dict[$key] = $trans;
                        }
                    }
                }
            }
            dump(count($dict));



            if (false)
            foreach ($nlReader->getIterator() as $idx => $jsonlObject) {
                $en = $enIterator->current();
                dump(en: $en, nl: $jsonlObject, idx: $idx);
                assert($en['code'] === $jsonlObject['code'], "out of sync on $idx");
                foreach (['object','subcategorie'] as $translatableField) {
                    if ($orig = $en[$translatableField]??null) {
                        if (!array_key_exists($translatableField, $jsonlObject)) {
                            dd($en, $jsonlObject, $translatableField);
                        }
                        SurvosUtils::assertKeyExists($translatableField, $jsonlObject, "Missing $translatableField $orig in row " . $idx);
                        $trans = $jsonlObject[$translatableField];
                        $key = md5($orig);
//                        $key = BabelRuntime::hash($trans, 'en');
                        if (!array_key_exists($key, $dict)) {
                            $dict[$key] = $trans;
                        }
                    }
                    try {
                        $enIterator->next();
                    } catch (\RuntimeException $e) {
                        // need to handle end better
                    }
                }
            }
            dump(sizeof($dict));
        }
        $localHash = md5($event->original);
        SurvosUtils::assertKeyExists($localHash, $dict);
        $translated = $dict[$localHash];
        $event->translated = $translated;
    }


}
