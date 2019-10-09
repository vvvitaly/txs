<?php

declare(strict_types=1);

// override values in config-local.php

use vvvitaly\txs\Csv\CsvColumn;
use vvvitaly\txs\Csv\CsvControl;
use vvvitaly\txs\Infrastructure\Console\Csv\CsvConfigPreset;

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

        // list of configurations for exporting from CSV
        'csv.presets' => [
            (new CsvConfigPreset('tinkoff'))
                ->setColumns([
                    CsvColumn::DATE,        // "Дата операции"
                    CsvColumn::IGNORE,      // "Дата платежа"
                    CsvColumn::ACCOUNT,     // "Номер карты"
                    CsvColumn::IGNORE,      // "Статус"
                    CsvColumn::AMOUNT,      // "Сумма операции"
                    CsvColumn::CURRENCY,    // "Валюта операции"
                    CsvColumn::IGNORE,      // "Сумма платежа"
                    CsvColumn::IGNORE,      // "Валюта платежа"
                    CsvColumn::IGNORE,      // "Кэшбэк"
                    CsvColumn::IGNORE,      // "Категория"
                    CsvColumn::IGNORE,      // "MCC"
                    CsvColumn::DESCRIPTION, // "Описание"
                    CsvColumn::IGNORE,      // "Бонусы (включая кэшбэк)"
                ])
                ->setControl(new CsvControl(';'))
                ->setEncoding('windows-1251')
                ->setRowsFilter(static function (array $row) {
                    return isset($row[3]) && $row[3] === 'OK';
                }),
        ],
    ],
    is_file(__DIR__ . '/config-local.php') ? require __DIR__ . '/config-local.php' : []
);