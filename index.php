<?php
declare(strict_types=1);

$appName    = 'DNS Lookup Tool';
$appVersion = '1.0.0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Free DNS lookup tool. Check A, AAAA, MX, TXT, NS, CNAME, SOA and CAA records instantly.">
  <meta name="keywords" content="dns lookup, dns checker, mx record, txt record, ns record, cname, a record, hosting">
  <title>DNS Lookup Tool — Hostking Open Source</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <header class="site-header">
    <div class="container">
      <div class="header-inner">
        <a href="https://www.hostking.host" class="brand" target="_blank" rel="noopener">
          <span class="brand-name">Hostking</span>
          <span class="brand-tag">Open Source</span>
        </a>
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
          <span class="theme-icon">🌙</span>
        </button>
      </div>
    </div>
  </header>

  <main class="main">
    <div class="container">

      <section class="hero">
        <h1>DNS Lookup Tool</h1>
        <p class="hero-sub">Instantly query DNS records for any domain. Supports A, AAAA, MX, TXT, NS, CNAME, SOA and CAA record types.</p>
      </section>

      <div class="card">
        <form id="lookupForm" method="POST" action="lookup.php" novalidate>
          <div class="form-row">
            <div class="input-group">
              <label for="domain" class="sr-only">Domain name</label>
              <input
                type="text"
                id="domain"
                name="domain"
                class="text-input"
                placeholder="e.g. hostking.host or mail.example.com"
                autocomplete="off"
                autocapitalize="none"
                spellcheck="false"
                required
                aria-required="true"
                value="<?= isset($_POST['domain']) ? htmlspecialchars($_POST['domain']) : '' ?>">
            </div>
            <div class="select-group">
              <label for="recordType" class="sr-only">Record type</label>
              <select id="recordType" name="record_type" class="select-input" aria-label="DNS record type">
                <?php
                $types = ['A','AAAA','MX','TXT','NS','CNAME','SOA','CAA','ALL'];
                $selected = $_POST['record_type'] ?? 'A';
                foreach ($types as $t):
                ?>
                <option value="<?= $t ?>" <?= $selected === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-primary" id="lookupBtn">
              <span class="btn-text">Lookup</span>
              <span class="btn-spinner" id="spinner" aria-hidden="true"></span>
            </button>
          </div>
          <div class="quick-types" role="group" aria-label="Quick record type selection">
            <span class="quick-label">Quick:</span>
            <?php foreach (array_slice($types, 0, -1) as $t): ?>
            <button type="button" class="quick-btn" data-type="<?= $t ?>"><?= $t ?></button>
            <?php endforeach; ?>
          </div>
        </form>
      </div>

      <div id="resultsArea">
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require 'lookup.php';
            echo renderResults($lookupResult ?? []);
        }
        ?>
      </div>

      <div class="card info-card">
        <h2>DNS Record Types</h2>
        <div class="record-types-grid">
          <div class="record-info">
            <strong>A</strong>
            <p>Maps a domain to an IPv4 address. The most common record type.</p>
          </div>
          <div class="record-info">
            <strong>AAAA</strong>
            <p>Maps a domain to an IPv6 address.</p>
          </div>
          <div class="record-info">
            <strong>MX</strong>
            <p>Mail Exchange — specifies the mail server responsible for receiving email for a domain.</p>
          </div>
          <div class="record-info">
            <strong>TXT</strong>
            <p>Text records used for SPF, DKIM, DMARC, domain verification and more.</p>
          </div>
          <div class="record-info">
            <strong>NS</strong>
            <p>Nameserver records — defines which DNS servers are authoritative for a domain.</p>
          </div>
          <div class="record-info">
            <strong>CNAME</strong>
            <p>Canonical Name — creates an alias from one domain name to another.</p>
          </div>
          <div class="record-info">
            <strong>SOA</strong>
            <p>Start of Authority — contains administrative information about a DNS zone.</p>
          </div>
          <div class="record-info">
            <strong>CAA</strong>
            <p>Certification Authority Authorization — controls which CAs can issue SSL certificates.</p>
          </div>
        </div>
      </div>

    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <p>Maintained by <strong>Hostking</strong> — open source DNS tools for developers and system administrators.</p>
      <div class="footer-links">
        <a href="https://www.hostking.host" target="_blank" rel="noopener">🌍 hostking.host</a>
        <a href="https://www.hostking.co.za" target="_blank" rel="noopener">🇿🇦 hostking.co.za</a>
        <a href="https://www.hostking.ae" target="_blank" rel="noopener">🇦🇪 hostking.ae</a>
        <a href="https://www.hostking.com.ng" target="_blank" rel="noopener">🇳🇬 hostking.com.ng</a>
      </div>
      <p class="footer-license">Released under the <a href="LICENSE">MIT License</a></p>
    </div>
  </footer>

  <script src="assets/script.js"></script>

</body>
</html>
