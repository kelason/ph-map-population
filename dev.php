<?php

/**
 * Cross-platform dev server launcher.
 *
 * On Linux/macOS: runs all services including `pail` (real-time log streaming).
 * On Windows: skips `pail` since it requires the `pcntl` extension (Unix-only).
 */

$isWindows = PHP_OS_FAMILY === 'Windows';
$mode = $argv[1] ?? 'dev';

$colors = $isWindows
    ? '#93c5fd,#c4b5fd,#fdba74'
    : '#93c5fd,#c4b5fd,#fb7185,#fdba74';

if ($mode === 'ssr') {
    $commands = $isWindows
        ? '"php artisan serve" "php artisan queue:listen --tries=1" "php artisan inertia:start-ssr" --names=server,queue,ssr'
        : '"php artisan serve" "php artisan queue:listen --tries=1" "php artisan pail --timeout=0" "php artisan inertia:start-ssr" --names=server,queue,logs,ssr';
} else {
    $commands = $isWindows
        ? '"php artisan serve" "php artisan queue:listen --tries=1" "npm run dev" --names=server,queue,vite'
        : '"php artisan serve" "php artisan queue:listen --tries=1" "php artisan pail --timeout=0" "npm run dev" --names=server,queue,logs,vite';
}

$cmd = sprintf('npx concurrently -c "%s" %s --kill-others', $colors, $commands);

if ($isWindows) {
    echo "[dev] Windows detected — skipping pail (requires pcntl extension)\n";
}

passthru($cmd, $code);
exit($code);
