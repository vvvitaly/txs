<?php

declare(strict_types=1);

namespace App\Ofd\Api;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * OFD request based on QR contents in format:
 *  t=<date:YYYYMMDDTHHSS>&s=<amount with decimals>&fn=<ФН, 16 digits>&i=<ФД, 1-10 digits>&fp=<ФПД, 1-10 digits>&n=1
 *
 * For example,
 *  t=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1
 */
final class OfdRequest
{
    /**
     * @var DateTimeImmutable
     */
    public $date;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string "ФН", "fn" in QR
     */
    public $fiscalDriveNumber;

    /**
     * @var string "ФД", "i" in QR
     */
    public $fiscalDocumentNumber;

    /**
     * @var string "ФП", "ФПД", "fp" in QR
     */
    public $fiscalSign;

    /**
     * @param DateTimeImmutable $date
     * @param float $amount
     * @param string $fiscalDriveNumber
     * @param string $fiscalDocumentNumber
     * @param string $fiscalSign
     */
    private function __construct(
        DateTimeImmutable $date,
        float $amount,
        string $fiscalDriveNumber,
        string $fiscalDocumentNumber,
        string $fiscalSign
    ) {
        $this->date = $date;
        $this->amount = $amount;
        $this->fiscalDriveNumber = $fiscalDriveNumber;
        $this->fiscalDocumentNumber = $fiscalDocumentNumber;
        $this->fiscalSign = $fiscalSign;
    }

    /**
     * Create instance from QR contents, like
     *  t=<date:YYYYMMDDTHHSS>&s=<amount with decimals>&fn=<ФН, 16 digits>&i=<ФД, 1-10 digits>&fp=<ФПД, 1-10 digits>&n=1
     *
     * @param string $content
     *
     * @return OfdRequest
     * @throws InvalidArgumentException
     */
    public static function fromQr(string $content): self
    {
        $params = [];
        parse_str($content, $params);

        foreach (['t', 's', 'fn', 'i', 'fp'] as $paramName) {
            if (empty($params[$paramName])) {
                throw new InvalidArgumentException("Required parameter \"{$paramName}\" is missing");
            }
        }

        try {
            $date = new DateTimeImmutable($params['t']);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Parameter "t" is invalid', 0, $e);
        }

        return new static(
            $date,
            (float)$params['s'],
            $params['fn'],
            $params['i'],
            $params['fp']
        );
    }
}