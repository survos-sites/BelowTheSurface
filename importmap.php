<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'admin' => [
        'path' => './assets/admin.js',
        'entrypoint' => true,
    ],
    'meili' => [
        'path' => './assets/meili.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'twig' => [
        'version' => '1.17.1',
    ],
    'locutus/php/strings/sprintf' => [
        'version' => '2.0.16',
    ],
    'locutus/php/strings/vsprintf' => [
        'version' => '2.0.16',
    ],
    'locutus/php/math/round' => [
        'version' => '2.0.16',
    ],
    'locutus/php/math/max' => [
        'version' => '2.0.16',
    ],
    'locutus/php/math/min' => [
        'version' => '2.0.16',
    ],
    'locutus/php/strings/strip_tags' => [
        'version' => '2.0.16',
    ],
    'locutus/php/datetime/strtotime' => [
        'version' => '2.0.16',
    ],
    'locutus/php/datetime/date' => [
        'version' => '2.0.16',
    ],
    'locutus/php/var/boolval' => [
        'version' => '2.0.16',
    ],
    'debug' => [
        'version' => '4.4.3',
    ],
    'ms' => [
        'version' => '2.1.3',
    ],
    'stimulus-attributes' => [
        'version' => '1.0.2',
    ],
    'escape-html' => [
        'version' => '1.0.3',
    ],
    'fos-routing' => [
        'version' => '0.0.6',
    ],
    'instantsearch.js' => [
        'version' => '4.85.2',
    ],
    '@algolia/events' => [
        'version' => '4.0.1',
    ],
    'algoliasearch-helper' => [
        'version' => '3.26.1',
    ],
    'qs' => [
        'version' => '6.9.7',
    ],
    'algoliasearch-helper/types/algoliasearch.js' => [
        'version' => '3.26.1',
    ],
    'instantsearch.js/es/widgets' => [
        'version' => '4.85.2',
    ],
    'instantsearch-ui-components' => [
        'version' => '0.15.2',
    ],
    'preact' => [
        'version' => '10.28.0',
    ],
    'hogan.js' => [
        'version' => '3.0.2',
    ],
    'htm/preact' => [
        'version' => '3.1.1',
    ],
    'preact/hooks' => [
        'version' => '10.28.0',
    ],
    'ai' => [
        'version' => '5.0.108',
    ],
    '@babel/runtime/helpers/extends' => [
        'version' => '7.28.4',
    ],
    '@babel/runtime/helpers/objectWithoutProperties' => [
        'version' => '7.28.4',
    ],
    '@babel/runtime/helpers/typeof' => [
        'version' => '7.28.4',
    ],
    '@babel/runtime/helpers/defineProperty' => [
        'version' => '7.28.4',
    ],
    '@babel/runtime/helpers/slicedToArray' => [
        'version' => '7.28.4',
    ],
    '@babel/runtime/helpers/toConsumableArray' => [
        'version' => '7.28.4',
    ],
    'markdown-to-jsx' => [
        'version' => '7.7.17',
    ],
    'htm' => [
        'version' => '3.1.1',
    ],
    '@ai-sdk/gateway' => [
        'version' => '2.0.18',
    ],
    '@ai-sdk/provider-utils' => [
        'version' => '3.0.18',
    ],
    '@ai-sdk/provider' => [
        'version' => '2.0.0',
    ],
    'zod/v4' => [
        'version' => '4.1.13',
    ],
    '@opentelemetry/api' => [
        'version' => '1.9.0',
    ],
    'react' => [
        'version' => '19.2.0',
    ],
    '@vercel/oidc' => [
        'version' => '3.0.5',
    ],
    'eventsource-parser/stream' => [
        'version' => '3.0.6',
    ],
    'zod/v3' => [
        'version' => '4.1.13',
    ],
    '@standard-schema/spec' => [
        'version' => '1.0.0',
    ],
    'instantsearch.css/themes/algolia.min.css' => [
        'version' => '8.8.0',
        'type' => 'css',
    ],
    '@meilisearch/instant-meilisearch' => [
        'version' => '0.29.0',
    ],
    'meilisearch' => [
        'version' => '0.54.0',
    ],
    '@stimulus-components/dialog' => [
        'version' => '1.0.1',
    ],
    '@andypf/json-viewer' => [
        'version' => '2.2.0',
    ],
    'pretty-print-json' => [
        'version' => '3.0.6',
    ],
    'pretty-print-json/dist/css/pretty-print-json.min.css' => [
        'version' => '3.0.6',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    '@tabler/core' => [
        'version' => '1.4.0',
    ],
    '@tabler/core/dist/css/tabler.min.css' => [
        'version' => '1.4.0',
        'type' => 'css',
    ],
    'intl-messageformat' => [
        'version' => '10.7.18',
    ],
    'tslib' => [
        'version' => '2.8.1',
    ],
    '@formatjs/fast-memoize' => [
        'version' => '2.2.7',
    ],
    '@formatjs/icu-messageformat-parser' => [
        'version' => '2.11.4',
    ],
    '@formatjs/icu-skeleton-parser' => [
        'version' => '1.8.16',
    ],
    '@symfony/ux-translator' => [
        'path' => './vendor/symfony/ux-translator/assets/dist/translator_controller.js',
    ],
    'chart.js' => [
        'version' => '3.9.1',
    ],
];
