<?php
// Paksa Laravel pake folder /tmp biar ga kena mental (read-only error)
$_ENV['APP_CONFIG_CACHE'] = '/tmp/config.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/events.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/routes.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp';

// Set default config buat serverless
$_ENV['CACHE_DRIVER'] = 'array';
$_ENV['LOG_CHANNEL'] = 'stderr'; // Biar errornya muncul di log Vercel, gak disimpen di file
$_ENV['SESSION_DRIVER'] = 'cookie';

require __DIR__ . '/../public/index.php';