<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Hostking DNS Lookup Tool
|--------------------------------------------------------------------------
|
| Configuration File
|
*/

define('APP_NAME', 'Hostking DNS Lookup Tool');
define('APP_VERSION', '1.0.0');

define('MAX_DOMAIN_LENGTH', 253);

$SUPPORTED_RECORDS = [
    'A'     => DNS_A,
    'AAAA'  => DNS_AAAA,
    'MX'    => DNS_MX,
    'TXT'   => DNS_TXT,
    'NS'    => DNS_NS,
    'CNAME' => DNS_CNAME,
    'SOA'   => DNS_SOA,
    'CAA'   => DNS_CAA
];
