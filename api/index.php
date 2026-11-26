<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Vercel Serverless PHP Entrypoint (Gumi Coffee)
|--------------------------------------------------------------------------
|
| Ensures writable runtime directories exist under /tmp on Vercel's
| read-only filesystem before forwarding execution to public/index.php.
|
*/

$tmpDirectories = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
];

foreach ($tmpDirectories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
}

require __DIR__.'/../public/index.php';
