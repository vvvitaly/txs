<?php /** @noinspection PhpMissingDocCommentInspection */

declare(strict_types=1);

namespace tests\Exporters\Processors\Normalizers;

use PHPUnit\Framework\TestCase;
use vvvitaly\txs\Exporters\Processors\Normalizers\SpacesNormalizer;

final class SpacesNormalizerTest extends TestCase
{
    public function testInvoke(): void
    {
        $this->assertEquals('test str', (new SpacesNormalizer())('     test     str   '));
    }
}