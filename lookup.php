<?php
declare(strict_types=1);

/**
 * Hostking DNS Lookup Tool — lookup.php
 * Handles DNS queries and AJAX JSON responses.
 */

/* ── Constants ──────────────────────────────────── */
const MAX_DOMAIN_LENGTH = 253;
const SUPPORTED_TYPES   = ['A','AAAA','MX','TXT','NS','CNAME','SOA','CAA','ALL'];

const DNS_TYPE_MAP = [
    'A'     => DNS_A,
    'AAAA'  => DNS_AAAA,
    'MX'    => DNS_MX,
    'TXT'   => DNS_TXT,
    'NS'    => DNS_NS,
    'CNAME' => DNS_CNAME,
    'SOA'   => DNS_SOA,
    'CAA'   => DNS_CAA,
    'ALL'   => DNS_ALL,
];

/* ── If called via AJAX return JSON ─────────────── */
if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    $domain     = sanitizeDomain($_POST['domain'] ?? '');
    $recordType = sanitizeType($_POST['record_type'] ?? 'A');
    echo json_encode(performLookup($domain, $recordType), JSON_PRETTY_PRINT);
    exit;
}

/* ── If included from index.php, run lookup ─────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain     = sanitizeDomain($_POST['domain'] ?? '');
    $recordType = sanitizeType($_POST['record_type'] ?? 'A');
    $lookupResult = performLookup($domain, $recordType);
}

/* ── Sanitize domain input ──────────────────────── */
function sanitizeDomain(string $domain): string
{
    $domain = trim($domain);
    $domain = strtolower($domain);
    // Strip protocol if entered
    $domain = preg_replace('#^https?://#', '', $domain);
    // Strip trailing slash and path
    $domain = explode('/', $domain)[0];
    // Strip port
    $domain = explode(':', $domain)[0];
    return $domain;
}

/* ── Sanitize record type ───────────────────────── */
function sanitizeType(string $type): string
{
    $type = strtoupper(trim($type));
    return in_array($type, SUPPORTED_TYPES, true) ? $type : 'A';
}

/* ── Validate domain ────────────────────────────── */
function validateDomain(string $domain): array
{
    if (empty($domain)) {
        return ['valid' => false, 'error' => 'Please enter a domain name.'];
    }
    if (strlen($domain) > MAX_DOMAIN_LENGTH) {
        return ['valid' => false, 'error' => 'Domain name is too long.'];
    }
    if (!preg_match('/^(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/', $domain)) {
        return ['valid' => false, 'error' => 'Invalid domain name format.'];
    }
    return ['valid' => true, 'error' => ''];
}

/* ── Perform DNS lookup ─────────────────────────── */
function performLookup(string $domain, string $recordType): array
{
    $validation = validateDomain($domain);
    if (!$validation['valid']) {
        return [
            'success'     => false,
            'domain'      => $domain,
            'record_type' => $recordType,
            'error'       => $validation['error'],
            'records'     => [],
            'query_time'  => 0,
        ];
    }

    $dnsConst = DNS_TYPE_MAP[$recordType] ?? DNS_ALL;
    $start    = microtime(true);
    $raw      = @dns_get_record($domain, $dnsConst);
    $elapsed  = round((microtime(true) - $start) * 1000, 2);

    if ($raw === false || empty($raw)) {
        return [
            'success'     => false,
            'domain'      => $domain,
            'record_type' => $recordType,
            'error'       => "No {$recordType} records found for {$domain}.",
            'records'     => [],
            'query_time'  => $elapsed,
        ];
    }

    return [
        'success'     => true,
        'domain'      => $domain,
        'record_type' => $recordType,
        'error'       => '',
        'records'     => formatRecords($raw, $recordType),
        'count'       => count($raw),
        'query_time'  => $elapsed,
    ];
}

/* ── Format raw DNS records ─────────────────────── */
function formatRecords(array $raw, string $requestedType): array
{
    $formatted = [];

    foreach ($raw as $r) {
        $type = strtoupper($r['type'] ?? $requestedType);
        $ttl  = $r['ttl'] ?? 0;

        $value = match ($type) {
            'A'     => $r['ip'] ?? '',
            'AAAA'  => $r['ipv6'] ?? '',
            'MX'    => ($r['pri'] ?? 0) . ' ' . ($r['target'] ?? ''),
            'TXT'   => $r['txt'] ?? implode('', $r['entries'] ?? []),
            'NS'    => rtrim($r['target'] ?? '', '.'),
            'CNAME' => rtrim($r['target'] ?? '', '.'),
            'SOA'   => sprintf(
                'mname=%s rname=%s serial=%s refresh=%s retry=%s expire=%s minimum=%s',
                rtrim($r['mname'] ?? '', '.'),
                rtrim($r['rname'] ?? '', '.'),
                $r['serial'] ?? '',
                $r['refresh'] ?? '',
                $r['retry'] ?? '',
                $r['expire'] ?? '',
                $r['minimum-ttl'] ?? ''
            ),
            'CAA'   => ($r['flags'] ?? 0) . ' ' . ($r['tag'] ?? '') . ' ' . ($r['value'] ?? ''),
            default => json_encode($r),
        };

        $formatted[] = [
            'type'  => $type,
            'ttl'   => $ttl,
            'value' => $value,
            'raw'   => $r,
        ];
    }

    // Sort: MX by priority, others by value
    usort($formatted, static function (array $a, array $b): int {
        if ($a['type'] === 'MX' && $b['type'] === 'MX') {
            return ($a['raw']['pri'] ?? 0) <=> ($b['raw']['pri'] ?? 0);
        }
        return strcmp($a['value'], $b['value']);
    });

    return $formatted;
}

/* ── Render results as HTML (for non-AJAX) ──────── */
function renderResults(array $result): string
{
    if (empty($result)) return '';

    if (!$result['success']) {
        return sprintf(
            '<div class="card result-card error-card">
               <div class="result-header">
                 <span class="result-icon">⚠️</span>
                 <span class="result-title">%s</span>
               </div>
               <p class="error-message">%s</p>
             </div>',
            htmlspecialchars($result['domain']),
            htmlspecialchars($result['error'])
        );
    }

    $rows = '';
    foreach ($result['records'] as $rec) {
        $rows .= sprintf(
            '<tr>
               <td><span class="badge badge-%s">%s</span></td>
               <td class="record-value">%s</td>
               <td class="ttl-cell">%s</td>
             </tr>',
            strtolower(htmlspecialchars($rec['type'])),
            htmlspecialchars($rec['type']),
            htmlspecialchars($rec['value']),
            formatTTL((int)$rec['ttl'])
        );
    }

    $exportData = htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT), ENT_QUOTES);

    return sprintf(
        '<div class="card result-card">
           <div class="result-header">
             <div class="result-meta">
               <h2 class="result-domain">%s</h2>
               <span class="result-stats">%d record%s · %sms</span>
             </div>
             <div class="result-actions">
               <button class="btn btn-sm" onclick="copyResults()" title="Copy results">📋 Copy</button>
               <button class="btn btn-sm" onclick="exportJSON(%s)" title="Export as JSON">⬇ JSON</button>
             </div>
           </div>
           <div class="table-wrap">
             <table class="results-table" id="resultsTable">
               <thead>
                 <tr>
                   <th>Type</th>
                   <th>Value</th>
                   <th>TTL</th>
                 </tr>
               </thead>
               <tbody>%s</tbody>
             </table>
           </div>
         </div>',
        htmlspecialchars($result['domain']),
        $result['count'],
        $result['count'] === 1 ? '' : 's',
        $result['query_time'],
        $exportData,
        $rows
    );
}

/* ── Format TTL in human-readable form ──────────── */
function formatTTL(int $seconds): string
{
    if ($seconds < 60)   return "{$seconds}s";
    if ($seconds < 3600) return round($seconds / 60) . 'm';
    if ($seconds < 86400) return round($seconds / 3600) . 'h';
    return round($seconds / 86400) . 'd';
}
