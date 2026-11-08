<?php
declare(strict_types = 1);

namespace FioApi;

use PHPUnit\Framework\Assert;

class TransactionListTest extends \PHPUnit\Framework\TestCase
{
    public function testTransactionListValuesAreProperlySet(): void
    {
        $transactionList = json_decode((string) file_get_contents(__DIR__ . '/data/example-response.json'));

        $transactionList = TransactionList::create($transactionList->accountStatement);

        Assert::assertSame(500.0, $transactionList->getOpeningBalance());
        Assert::assertSame(1000.0, $transactionList->getClosingBalance());
        Assert::assertEquals(new \DateTimeImmutable('2015-03-30+0200'), $transactionList->getDateStart());
        Assert::assertEquals(new \DateTimeImmutable('2015-03-31+0200'), $transactionList->getDateEnd());
        Assert::assertNull($transactionList->getYearList());
        Assert::assertNull($transactionList->getIdList());
        Assert::assertSame(1111111111, $transactionList->getIdFrom());
        Assert::assertSame(1111111999, $transactionList->getIdTo());
        Assert::assertNull($transactionList->getIdLastDownload());
    }

    public function testEmptyTransactionList(): void
    {
        $transactionList = json_decode((string) file_get_contents(__DIR__ . '/data/example-empty-response.json'));

        $transactionList = TransactionList::create($transactionList->accountStatement);

        Assert::assertSame(0.0, $transactionList->getOpeningBalance());
        Assert::assertSame(0.0, $transactionList->getClosingBalance());
        Assert::assertEquals(new \DateTimeImmutable('2017-08-06+0200'), $transactionList->getDateStart());
        Assert::assertEquals(new \DateTimeImmutable('2017-08-08+0200'), $transactionList->getDateEnd());
        Assert::assertNull($transactionList->getIdFrom());
        Assert::assertNull($transactionList->getIdTo());
        Assert::assertNull($transactionList->getIdLastDownload());
        Assert::assertCount(0, $transactionList->getTransactions());
    }

    public function testStatementIdentificationIsSet(): void
    {
        $transactionList = json_decode((string) file_get_contents(__DIR__ . '/data/example-statement-response.json'));

        $transactionList = TransactionList::create($transactionList->accountStatement);

        Assert::assertSame(2017, $transactionList->getYearList());
        Assert::assertSame(8, $transactionList->getIdList());
    }
}
