<?php

declare(strict_types=1);

require 'config.php';

function validateDomain(string $domain): bool
{
    return filter_var(
        $domain,
        FILTER_VALIDATE_DOMAIN,
        FILTER_FLAG_HOSTNAME
    ) !== false;
}

function lookupDNS(string $domain, string $type): array
{
    global $SUPPORTED_RECORDS;

    if (!validateDomain($domain)) {
        return [
            'success' => false,
            'message' => 'Invalid domain.'
        ];
    }

    if (!isset($SUPPORTED_RECORDS[$type])) {
        return [
            'success' => false,
            'message' => 'Unsupported record type.'
        ];
    }

    $records = @dns_get_record(
        $domain,
        $SUPPORTED_RECORDS[$type]
    );

    if (!$records) {
        return [
            'success' => false,
            'message' => 'No DNS records found.'
        ];
    }

    return [
        'success' => true,
        'records' => $records
    ];
}
