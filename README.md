# Fio API PHP implemention

[![Latest Stable Version](https://poser.pugx.org/mhujer/fio-api-php/version.png)](https://packagist.org/packages/mhujer/fio-api-php) [![Total Downloads](https://poser.pugx.org/mhujer/fio-api-php/downloads.png)](https://packagist.org/packages/mhujer/fio-api-php) [![License](https://poser.pugx.org/mhujer/fio-api-php/license.svg)](https://packagist.org/packages/mhujer/fio-api-php) [![Coverage Status](https://coveralls.io/repos/mhujer/fio-api-php/badge.svg?branch=master)](https://coveralls.io/r/mhujer/fio-api-php?branch=master)

Fio bank REST API implementation in PHP. It allows you to download and iterate through account balance changes.

[There is a Symfony Bundle](https://github.com/mhujer/fio-api-bundle) for using this library in a Symfony app.

Usage
----
1. Install the latest version with `composer require mhujer/fio-api-php`
2. Create a *token* in the ebanking (Nastavení / API)
3. Use it according to the example bellow and check the docblocks

```php
<?php
require_once 'vendor/autoload.php';

$downloader = new FioApi\Downloader('TOKEN@todo');
$transactionList = $downloader->downloadSince(new \DateTimeImmutable('-1 week'));

foreach ($transactionList->getTransactions() as $transaction) {
    var_dump($transaction); //object with getters
}

```

#### Available endpoints:
- `downloadFromTo(DateTimeInterface $from, DateTimeInterface $to): TransactionList`
- `downloadSince(DateTimeInterface $since): TransactionList`
- `downloadStatement(int $year, int $id): TransactionList` - downloads an official statement, see [Official statements](#official-statements)
- `downloadPdf(int $year, int $id): string` - saves an official statement as a PDF file and returns its path, see [Official statements](#official-statements)
- `downloadLastStatement(): array` - returns `['year' => int, 'id' => int]` of the newest official statement
- `downloadLast(): TransactionList`
- `setLastId(string $id)` - sets the last downloaded ID through the API

Official statements
-------------------
The bank numbers the official statements in each year, from 1. The `$id` parameter of
`downloadStatement()` and `downloadPdf()` is this statement number. **It is not the month.**

The number is equal to the month only if the account has monthly statements and existed for the full year.
If the account was opened during the year, or if it has statements more or less often than monthly,
the numbers are different. For example, in the Fio API documentation, statement 3 of 2012 is for August 2012.

How to find the statement numbers:
- **The newest statement:** `downloadLastStatement()` returns its year and number,
  for example `['year' => 2026, 'id' => 10]`. In that year, statements 1 to 10 exist.
- **The statements of an earlier year:** the API has no call that lists them. Start at 1 and increase the number
  until the request fails. The Fio API documentation does not say which error the API returns
  for a statement number that does not exist.
- **The period of a statement:** download it with `downloadStatement()` and read
  `getDateStart()` and `getDateEnd()` of the result.

The API accepts one call for each token every 30 seconds. A faster call causes a `TooGreedyException`.

```php
<?php
// The third argument is the directory for the PDF files
$downloader = new FioApi\Downloader('TOKEN@todo', null, __DIR__ . '/statements');

$last = $downloader->downloadLastStatement(); // ['year' => 2026, 'id' => 10]

// Find the period of each statement in the newest year
for ($id = 1; $id <= $last['id']; $id++) {
    sleep(30);
    $statement = $downloader->downloadStatement($last['year'], $id);
    printf("%d: %s - %s\n", $id, $statement->getDateStart()->format('Y-m-d'), $statement->getDateEnd()->format('Y-m-d'));
}

// Save the newest statement as a PDF file
sleep(30);
$file = $downloader->downloadPdf(...$last);
```

Saving real API responses
-------------------------
To compare the real API format with the test fixtures, save raw responses with:

```
FIO_TOKEN=... php bin/save-responses.php [days=90] [outDir=responses]
```

It does not call the `last` endpoint, because that call moves the stop marker on the server.

Requirements
------------
Fio API PHP works with PHP 8.2 or higher.

Submitting bugs and feature requests
------------------------------------
Bugs and feature request are tracked on [GitHub](https://github.com/mhujer/fio-api-php/issues)

Author
------
Martin Hujer - <https://www.martinhujer.cz>

Changelog
----------

## 6.0.0 (2026-11-08)
- **BC break:** `Transaction::getPaymentOrderId()`, `TransactionList::getIdFrom()` and `TransactionList::getIdTo()` return `?int` instead of `?float`
- **BC break:** minimal supported version is PHP 8.2
- **BC break:** requires `guzzlehttp/guzzle` `^7.15.2 || ^8.0.1` (Guzzle 6 and older Guzzle 7 versions have known security issues)
- added `Transaction::getSenderBic()` (column26) and `Transaction::getPayerReference()` (column27)
- added `TransactionList::getYearList()` and `TransactionList::getIdList()`
- added `Downloader::downloadStatement()` for official statements (`by-id` endpoint)
- added `Downloader::downloadLastStatement()` (`lastStatement` endpoint)
- added `Downloader::downloadPdf()` to save an official statement as a PDF file
- added `bin/save-responses.php` to save raw API responses

## 5.0.0 (2024-06-07)
- [#31](https://github.com/mhujer/fio-api-php/pull/31) add `composer/ca-bundle` as a required dependency instead of bundled root cert (thx @feldsam!)

## 4.2.0 (2024-05-30)
- [#28](https://github.com/mhujer/fio-api-php/pull/28) use new Fio API URL (thx @feldsam!)

## 4.1.2 (2019-12-28)
- [#19](https://github.com/mhujer/fio-api-php/pull/19) gracefully handle response with empty column8 (thx @fmasa!)

## 4.1.1 (2019-01-28)
- [#17](https://github.com/mhujer/fio-api-php/pull/17) added senderName (nazev protiuctu) (thx @jan-stanek!)

## 4.1.0 (2018-04-13)
- [#13](https://github.com/mhujer/fio-api-php/pull/13) Support /last and /set-last-id endpoints (thx @jiripudil!)

## 4.0.1 (2017-08-09)
- [#12](https://github.com/mhujer/fio-api-php/pull/12) handle empty transaction list  (thx @soukicz!)

## 4.0.0 (2017-08-04)
- [#9](https://github.com/mhujer/fio-api-php/pull/9) minimal supported version is PHP 7.1
- [#9](https://github.com/mhujer/fio-api-php/pull/9)`DateTime` replaced with `DateTimeImmutable` (or `DateTimeInterface`)
- [#9](https://github.com/mhujer/fio-api-php/pull/9) strict types and primitive typehints are used everywhere

## 3.0.0 (2016-11-24)
- dropped support for PHP <7

## 2.3.0 (2016-11-24)
- [#7](https://github.com/mhujer/fio-api-php/pull/7): added official composer CA bundle support (@soukicz)

## 2.2.0 (2016-03-13)
- [#2](https://github.com/mhujer/fio-api-php/pull/2): added [Kdyby/CurlCaBundle](https://github.com/Kdyby/CurlCaBundle)
 	as an optional dependency (@mhujer)

## 2.1.0 (2016-03-12)
- [#1](https://github.com/mhujer/fio-api-php/pull/1): updated default GeoTrust certificate (@soukiii)
- [#1](https://github.com/mhujer/fio-api-php/pull/1): added `specification` field in transaction (@soukiii)

## 2.0.0 (2015-06-14)
- upgraded to Guzzle 6
- support for PHP 5.4 dropped (as Guzzle 6 requires PHP 5.5+)

## 1.0.3 (2015-06-14)
- updated root certificate (Root 3) as the Fio changed it on 2014-05-26

## 1.0.0 (2015-04-05)
- initial release
