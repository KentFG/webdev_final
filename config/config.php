<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'gonzagas_apparel';
const DB_USER = 'root';
const DB_PASS = '';
const BASE_PATH = '/gonzagas-apparel-web';

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $exception): void {
    error_log($exception->getMessage() . " in " . $exception->getFile() . ':' . $exception->getLine());
    if (!headers_sent()) {
        header('Location: ' . BASE_PATH . '/error.php');
        exit;
    }
    echo 'Something went wrong. Please try again later.';
});
