<?php

declare(strict_types=1);

/**
 * Writes _data/source-xref.json from the `@see` manual URLs in the src/ docblocks of local checkouts
 *
 * Usage:
 *   php bin/gen_source_xref.php bearsunday/BEAR.Resource@1.x=/path/to/BEAR.Resource [...]
 *   php bin/gen_source_xref.php --check   # every manual URL in the JSON has its anchor in _site/
 *
 * The entries of each given repository are replaced; the entries of other repositories are kept.
 */

const XREF_FILE = __DIR__ . '/../_data/source-xref.json';
const SCHEMA_URL = 'https://bearsunday.github.io/schemas/source-xref.schema.json';
const MANUAL_URL = '~@see\s+(https://bearsunday\.github\.io/manuals/1\.0/en/[A-Za-z0-9_.-]+\.html#[A-Za-z0-9_-]+)~';

/** @return array<string, list<string>> */
function loadXref(): array
{
    if (! is_file(XREF_FILE)) {
        return [];
    }

    /** @var array<string, mixed> $json */
    $json = json_decode((string) file_get_contents(XREF_FILE), true, 512, JSON_THROW_ON_ERROR);
    unset($json['$schema']);

    /** @var array<string, list<string>> $json */
    return $json;
}

/**
 * Reads the top-level src* directories of the checkout (src/, src-annotation/, ...), skipping any named *deprecated*
 *
 * @return array<string, list<string>>
 */
function scan(string $repo, string $branch, string $checkout): array
{
    $root = rtrim($checkout, '/');
    $dirs = array_filter(glob($root . '/src*', GLOB_ONLYDIR) ?: [], static fn (string $dir): bool => ! str_contains(basename($dir), 'deprecated'));
    if ($dirs === []) {
        throw new RuntimeException("{$root}: no src directory");
    }

    $xref = [];
    foreach ($dirs as $dir) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all(MANUAL_URL, (string) file_get_contents($file->getPathname()), $matches);
            if ($matches[1] === []) {
                continue;
            }

            $path = substr($file->getPathname(), strlen($root) + 1);
            $xref["https://github.com/{$repo}/blob/{$branch}/{$path}"] = array_values(array_unique($matches[1]));
        }
    }

    return $xref;
}

/** @param array<string, list<string>> $xref */
function check(array $xref): int
{
    $missing = 0;
    foreach ($xref as $source => $manuals) {
        foreach ($manuals as $manual) {
            [$page, $anchor] = explode('#', substr($manual, strlen('https://bearsunday.github.io/')), 2);
            $html = __DIR__ . '/../_site/' . $page;
            if (! is_file($html)) {
                fwrite(STDERR, "{$html}: not built; run bundle exec jekyll build first\n");

                return 2;
            }

            if (! str_contains((string) file_get_contents($html), 'id="' . $anchor . '"')) {
                fwrite(STDERR, "{$manual}: no such anchor (from {$source})\n");
                $missing++;
            }
        }
    }

    return $missing === 0 ? 0 : 1;
}

$args = array_slice($argv, 1);
if ($args === ['--check']) {
    exit(check(loadXref()));
}

if ($args === []) {
    fwrite(STDERR, "Usage: php bin/gen_source_xref.php owner/repo@branch=/path/to/checkout [...] | --check\n");
    exit(2);
}

$xref = loadXref();
foreach ($args as $arg) {
    if (! preg_match('~^([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+)@([^=]+)=(.+)$~', $arg, $m)) {
        fwrite(STDERR, "{$arg}: expected owner/repo@branch=/path/to/checkout\n");
        exit(2);
    }

    [, $repo, $branch, $checkout] = $m;
    $prefix = "https://github.com/{$repo}/blob/";
    $xref = array_filter($xref, static fn (string $source): bool => ! str_starts_with($source, $prefix), ARRAY_FILTER_USE_KEY);
    $xref += scan($repo, $branch, $checkout);
}

ksort($xref);
if (! is_dir(dirname(XREF_FILE))) {
    mkdir(dirname(XREF_FILE));
}

file_put_contents(XREF_FILE, json_encode(['$schema' => SCHEMA_URL] + $xref, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
echo count($xref), " source files in ", XREF_FILE, "\n";
