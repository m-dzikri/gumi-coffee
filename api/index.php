<?php

declare(strict_types=1);

// Pre-create writable /tmp runtime directories required on Vercel's read-only filesystem.

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
