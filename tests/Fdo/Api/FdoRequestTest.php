<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Fdo\Api;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Fdo\Api\FdoRequest;

final class FdoRequestTest extends TestCase
{
    /**
     * @param string $qr
     * @param string $expectedDate
     * @param float $expectedAmount
     * @param string $expectedFn
     * @param string $expectedFd
     * @param string $expectedFpd
     *
     * @dataProvider providerFromQr
     * @throws Exception
     */
    public function testFromQr(
        string $qr,
        string $expectedDate,
        float $expectedAmount,
        string $expectedFn,
        string $expectedFd,
        string $expectedFpd
    ): void {
        $actual = FdoRequest::fromQr($qr);

        $this->assertEquals(new DateTimeImmutable($expectedDate), $actual->date);
        $this->assertEquals($expectedAmount, $actual->amount);
        $this->assertEquals($expectedFn, $actual->fiscalDriveNumber);
        $this->assertEquals($expectedFd, $actual->fiscalDocumentNumber);
        $this->assertEquals($expectedFpd, $actual->fiscalSign);
    }

    /**
     * @param string $qr
     * @param string $expectedError
     *
     * @dataProvider providerFromQrInvalidContent
     */
    public function testFromQrInvalidContent(string $qr, string $expectedError): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/{$expectedError}/i");
        FdoRequest::fromQr($qr);
    }

    public function testAsQr(): void
    {
        $qr = 't=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1';
        $req = FdoRequest::fromQr($qr);
        $this->assertEquals($qr, $req->asQr());
    }

    public function providerFromQr(): array
    {
        return [
            'regular qr' => [
                't=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '2019-08-11 11:39:00',
                1405,
                '9280440300200295',
                '14378',
                '3796110719',
            ],
            'regular qr, date format' => [
                't=2019-08-11T11:39&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '2019-08-11 11:39:00',
                1405,
                '9280440300200295',
                '14378',
                '3796110719',
            ],
            'regular qr, float amount' => [
                't=20190811T1139&s=1405.33&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '2019-08-11 11:39:00',
                1405.33,
                '9280440300200295',
                '14378',
                '3796110719',
            ],
            'regular qr, int amount' => [
                't=20190811T1139&s=1405&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '2019-08-11 11:39:00',
                1405,
                '9280440300200295',
                '14378',
                '3796110719',
            ],
        ];
    }

    public function providerFromQrInvalidContent(): array
    {
        return [
            'missing date' => [
                's=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '"t" is missing',
            ],
            'missing amount' => [
                't=20190811T1139&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '"s" is missing',
            ],
            'missing fn' => [
                't=20190811T1139&s=1405.00&i=14378&fp=3796110719&n=1',
                '"fn" is missing',
            ],
            'missing i' => [
                't=20190811T1139&s=1405.00&fn=9280440300200295&fp=3796110719&n=1',
                '"i" is missing',
            ],
            'missing fp' => [
                't=20190811T1139&s=1405.00&fn=9280440300200295&i=14378&n=1',
                '"fp" is missing',
            ],
            'invalid date' => [
                't=201908T113&s=1405.00&fn=9280440300200295&i=14378&fp=3796110719&n=1',
                '"t" is invalid',
            ],
        ];
    }
}