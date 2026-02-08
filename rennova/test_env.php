<?php

require_once 'vendor/autoload.php';

// Load .env file first
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "PURCHASE_ORDER_EMAILS from .env: " . $_ENV['PURCHASE_ORDER_EMAILS'] ?? 'NOT SET' . "\n";
echo "PURCHASE_ORDER_EMAILS from env(): " . env('PURCHASE_ORDER_EMAILS') ?? 'NOT SET' . "\n";

// Now bootstrap app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "PURCHASE_ORDER_EMAILS from config: " . json_encode(config('mail.purchase_order_emails')) . "\n";
