<?php

declare(strict_types=1);

// override values in config-local.php

return array_replace_recursive(
    [
        // list of used SMS parsers
        'sms.parsers' => [],
    ],
    is_file(__DIR__ . '/config-local.php') ? require __DIR__ . '/config-local.php' : []
);