<?php

declare(strict_types=1);

// override values in config-local.php

return array_replace_recursive(
    [
        // list of used SMS parsers
        'sms.parsers' => [],

        // Vmeste
        'vmeste.logger' => null,    // null or Logger instance
        'vmeste.cache' => null,     // null or CacheInterface

        // FDO
        'fdo.http.logger' => null,  // null or Logger instance

        /**
         * list of descriptions aliases and replacements
         * @see \vvvitaly\txs\Exporters\Processors\Normalizers\DescriptionAlias
         */
        'export.aliases' => [],
    ],
    is_file(__DIR__ . '/config-local.php') ? require __DIR__ . '/config-local.php' : []
);