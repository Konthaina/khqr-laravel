<?php

declare(strict_types=1);

namespace Konthaina\Khqr\Tests;

use Konthaina\Khqr\BakongApiClient;
use Konthaina\Khqr\KHQRGenerator;
use PHPUnit\Framework\TestCase;

final class BakongApiClientTest extends TestCase
{
    public function test_check_transaction_by_md5_requires_non_empty_md5(): void
    {
        $client = new BakongApiClient('test-access-token');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('md5 is required');

        $client->checkTransactionByMd5('   ');
    }

    public function test_renew_token_requires_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email is required');

        BakongApiClient::renewToken('   ');
    }

    public function test_khqr_generator_renew_token_requires_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email is required');

        KHQRGenerator::renewBakongToken('');
    }
}
