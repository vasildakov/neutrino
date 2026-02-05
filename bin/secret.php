<?php

declare(strict_types=1);

use Neutrino\Security\Cryptography\Sodium\SodiumCryptography;
use Neutrino\Security\Cryptography\Sodium\SodiumEncryptionKey;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$keyB64 = 'Qx0X8z1m4Qn2ZJ7Jc1d0nJ0y1H6k6m7M5HcZ0H1Xz2s=';
$key = SodiumEncryptionKey::fromBase64($keyB64);

$crypto = new SodiumCryptography($key);

$encrypted = $crypto->encrypt('test123');
dump($encrypted);

$decrypted = $crypto->decrypt($encrypted);
dump($decrypted);