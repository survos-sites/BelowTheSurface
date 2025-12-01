<?php

namespace App\Service;

use Survos\BabelBundle\Event\TranslateStringEvent;
use Survos\BabelBundle\Runtime\BabelRuntime;
use Survos\CoreBundle\Service\SurvosUtils;
use Survos\JsonlBundle\IO\JsonlReader;
use Survos\JsonlBundle\IO\JsonlReaderInterface;
use Survos\JsonlBundle\IO\JsonlWriter;
use Survos\LibreTranslateBundle\Service\LibreTranslateService;
use Survos\TranslatorBundle\Model\TranslationRequest;
use Survos\TranslatorBundle\Service\TranslatorManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class TranslationService
{

    public function __construct(
        private TranslatorManager $translatorManager,
//        private JsonlReaderInterface $jsonlReader,
    )
    {
    }


    #[AsEventListener(TranslateStringEvent::class)]
    public function onTranslateStringEvent(TranslateStringEvent $event): void
    {
        static $dict = [];
        // the English is already translated for us.
        if ($event->targetLocale === 'en') {
            // this happens at the babel level, so we just translate the strings.
            // we are not updating the individual entities, e.g. with a doctrine iterator loop.
            if (empty($dict)) {
                $dict = json_decode(file_get_contents('data/amst_dictionary.json'), true);
            }
            $src = $event->original;

            assert(array_key_exists($src, $dict), "Missing $src in dict");
            $event->translated = $dict[$src];
            return;
        }
        $fn = sprintf("data/amst.%s.jsonl", $event->targetLocale);
        if (empty($dict)) {
            if (file_exists($fn)) {
                $reader = new JsonlReader($fn);
                foreach ($reader as $item) {
                    $dict[$item['src']] = $item['translated'];
                }
            }
        }
        if (array_key_exists($event->original, $dict)) {
            $event->translated = $dict[$event->original];
            return;
        }
        $translator = $this->translatorManager->by('libre');
//        $translator = $this->translatorManager->by('deepl');
        $writer = JsonlWriter::open($fn);

        $translated = $translator?->translate(
            new TranslationRequest($event->original, $event->sourceLocale, $event->targetLocale),
        );
        $obj = ['src' => $event->original, 'translated' => $translated->translatedText];
        $writer->write($obj);
        $dict[$event->original] = $translated->translatedText;
        $writer->close();
        $event->translated = $translated->translatedText;
    }


}
