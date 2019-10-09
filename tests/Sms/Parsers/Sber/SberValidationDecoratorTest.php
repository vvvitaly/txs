<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Sms\Parsers\Sber;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Sms\Message;
use vvvitaly\txs\Sms\Parsers\MessageParserInterface;
use vvvitaly\txs\Sms\Parsers\Sber\SberValidationDecorator;

final class SberValidationDecoratorTest extends TestCase
{
    /**
     * @param Message $sms
     * @param $expectedValid
     *
     * @dataProvider providerParse
     */
    public function testParse(Message $sms, $expectedValid): void
    {
        $inner = $this->createMock(MessageParserInterface::class);

        if ($expectedValid) {
            $inner->expects($this->once())
                ->method('parse')
                ->with($this->identicalTo($sms));
        } else {
            $inner->expects($this->never())
                ->method('parse');
        }

        $decorator = new SberValidationDecorator($inner);
        $decorator->parse($sms);
    }

    public function providerParse(): array
    {
        return [
            'from 900' => [new Message('900', new DateTimeImmutable(), 'some'), true],
            'wrong address' => [new Message('9000', new DateTimeImmutable(), 'some'), false],
        ];
    }
}