<?php
declare(strict_types = 1);

/**
 * Saves raw Fio API responses, to compare the real format with the test fixtures.
 *
 * Usage: FIO_TOKEN=... php bin/save-responses.php [days=90] [outDir=responses]
 *
 * It does not call the "last" endpoint, because that call moves the server-side stop marker.
 */

require __DIR__ . '/../vendor/autoload.php';

use Composer\CaBundle\CaBundle;
use FioApi\UrlBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

$token = getenv('FIO_TOKEN');
if ($token === false || $token === '') {
    fwrite(STDERR, "Set the FIO_TOKEN environment variable.\n");
    exit(1);
}
$days = (int) ($argv[1] ?? 90);
$outDir = $argv[2] ?? 'responses';

$urls = new UrlBuilder($token);
$client = new Client([
    RequestOptions::VERIFY => CaBundle::getSystemCaRootBundlePath(),
    RequestOptions::HTTP_ERRORS => false,
]);
if (!is_dir($outDir) && !mkdir($outDir, 0700, true)) {
    fwrite(STDERR, "Cannot create {$outDir}.\n");
    exit(1);
}
$prefix = date('Ymd-His');
$first = true;

$save = function (string $name, string $url) use ($client, $outDir, $prefix, &$first): string {
    // The API rejects a second call with the same token within 30 seconds (HTTP 409)
    if (!$first) {
        echo "Waiting 30 s...\n";
        sleep(31);
    }
    $first = false;

    $response = $client->request('GET', $url);
    $body = (string) $response->getBody();
    $file = sprintf('%s/%s-%s-%d.txt', $outDir, $prefix, $name, $response->getStatusCode());
    file_put_contents($file, $body);
    echo "{$name}: HTTP {$response->getStatusCode()} -> {$file}\n";

    return $body;
};

$save('periods', $urls->buildPeriodsUrl(new DateTimeImmutable("-{$days} days"), new DateTimeImmutable()));

$lastStatement = $save('last-statement', $urls->buildLastStatementUrl());
if (preg_match('/(\d{4})\D+(\d+)/', $lastStatement, $m) === 1) {
    $save("statement-{$m[1]}-{$m[2]}", $urls->buildByIdUrl((int) $m[1], (int) $m[2]));
} else {
    echo "Cannot read the statement year and number from the lastStatement response, statement skipped.\n";
}
