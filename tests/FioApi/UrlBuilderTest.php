<?php
declare(strict_types = 1);

namespace FioApi;

use PHPUnit\Framework\Assert;

class UrlBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingTokenExceptionIsThrownForEmptyToken(): void
    {
        $this->expectException(\FioApi\Exceptions\MissingTokenException::class);

        new UrlBuilder('');
    }

    public function testTokenCanBeSetThroughConstructor(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame('token1', $urlBuilder->getToken());
    }

    public function testTokenCanBeChangedThroughSetter(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        $urlBuilder->setToken('token2');
        Assert::assertSame('token2', $urlBuilder->getToken());
    }

    public function testBuildPeriodsUrlReturnValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/periods/token1/2015-03-25/2015-03-31/transactions.json',
            $urlBuilder->buildPeriodsUrl(
                new \DateTimeImmutable('2015-03-25'),
                new \DateTimeImmutable('2015-03-31')
            )
        );
    }

    public function testBuildByIdUrlReturnValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/by-id/token1/2012/1/transactions.json',
            $urlBuilder->buildByIdUrl(2012, 1)
        );
    }

    public function testBuildLastStatementUrlReturnValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/lastStatement/token1/statement',
            $urlBuilder->buildLastStatementUrl()
        );
    }

    public function testBuildPdfUrlReturnValidUrl(): void
    {
        $urlBuilder = new UrlBuilder('token1');
        Assert::assertSame(
            'https://fioapi.fio.cz/v1/rest/by-id/token1/2012/3/transactions.pdf',
            $urlBuilder->buildPdf(2012, 3)
        );
    }
}
