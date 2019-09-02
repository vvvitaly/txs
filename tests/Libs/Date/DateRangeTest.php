<?php

/** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Libs\Date;

use App\Libs\Date\DatesRange;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    /**
     * @dataProvider providerInRange
     *
     * @param DateTimeImmutable $testingDate
     * @param DateTimeImmutable|null $begin
     * @param DateTimeImmutable|null $end
     * @param bool $expectedIsIn
     */
    public function testContains(
        DateTimeImmutable $testingDate,
        ?DateTimeImmutable $begin,
        ?DateTimeImmutable $end,
        bool $expectedIsIn
    ): void {
        $range = new DatesRange($begin, $end);
        $this->assertEquals($expectedIsIn, $range->contains($testingDate));
    }

    public function providerInRange(): array
    {
        $dt = static function (string $date) {
            return new DateTimeImmutable($date);
        };

        return [
            'in, two dates' => [
                $dt('2019-08-02 15:21:49'),
                $dt('2019-08-01 23:12:22'),
                $dt('2019-08-03 13:23:47'),
                true,
            ],
            'greater, two dates' => [
                $dt('2019-08-03 13:23:48'),
                $dt('2019-08-01 23:12:22'),
                $dt('2019-08-03 13:23:47'),
                false,
            ],
            'less, two dates' => [
                $dt('2019-08-01 13:23:46'),
                $dt('2019-08-01 23:12:22'),
                $dt('2019-08-03 13:23:47'),
                false,
            ],
            'in, only start' => [
                $dt('2123-08-02 15:21:49'),
                $dt('2019-08-01 23:12:22'),
                null,
                true,
            ],
            'less, only start' => [
                $dt('2019-08-01 15:21:49'),
                $dt('2019-08-01 23:12:22'),
                null,
                false,
            ],
            'in, only end' => [
                $dt('1627-08-02 15:21:49'),
                null,
                $dt('2019-08-01 23:12:22'),
                true,
            ],
            'greater, only end' => [
                $dt('2182-08-02 15:21:49'),
                null,
                $dt('2019-08-01 23:12:22'),
                false,
            ],
        ];
    }
}