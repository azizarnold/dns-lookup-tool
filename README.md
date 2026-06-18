# 🔍 DNS Lookup Tool

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4)](https://www.php.net)
[![GitHub Stars](https://img.shields.io/github/stars/hostking/dns-lookup-tool?style=flat)](https://github.com/hostking/dns-lookup-tool)

A fast, clean, open-source DNS lookup tool built with PHP, HTML5, CSS3 and vanilla JavaScript. Look up any DNS record type instantly — with AJAX live results, JSON export, dark mode and a responsive mobile-first interface.

---

## ✨ Features

- ✅ **8 record types** — A, AAAA, MX, TXT, NS, CNAME, SOA, CAA (plus ALL)
- ⚡ **AJAX lookups** — no full page reload
- 📋 **Copy results** to clipboard as plain text
- ⬇️ **Export as JSON** — download results as a JSON file
- 🕐 **TTL display** — shown in human-readable format (seconds, minutes, hours, days)
- 🎯 **Quick-type buttons** — switch record types in one click
- 🌙 **Dark mode** with system preference detection
- 📱 **Fully responsive** — works on mobile, tablet and desktop
- 🔒 **Input validation** — sanitises and validates domain names server-side
- ♿ **Accessible** — ARIA labels and keyboard navigation
- ⚡ **Zero JavaScript dependencies**
- 🚀 **Self-contained** — runs on any PHP shared hosting

---

## 📋 Supported Record Types

| Type | Description |
|------|-------------|
| **A** | Maps a domain to an IPv4 address |
| **AAAA** | Maps a domain to an IPv6 address |
| **MX** | Mail Exchange — identifies mail servers for the domain |
| **TXT** | Text records — used for SPF, DKIM, DMARC, domain verification |
| **NS** | Nameservers — authoritative DNS servers for the domain |
| **CNAME** | Canonical Name — an alias pointing to another domain |
| **SOA** | Start of Authority — administrative DNS zone information |
| **CAA** | Certification Authority Authorization — controls SSL issuance |
| **ALL** | Queries all available record types |

---

## 🚀 Installation

### Requirements

- PHP 8.0 or higher
- PHP `dns_get_record()` function enabled (standard on all cPanel, DirectAdmin and Plesk hosts)

### Option 1: Upload to shared hosting

1. [Download the ZIP](https://github.com/hostking/dns-lookup-tool/archive/refs/heads/main.zip)
2. Extract and upload all files to your hosting via FTP or File Manager
3. Visit the URL in your browser — it works immediately

### Option 2: Clone and run locally

```bash
git clone https://github.com/hostking/dns-lookup-tool.git
cd dns-lookup-tool
php -S localhost:8080
```

Then open http://localhost:8080 in your browser.

### Option 3: Docker

```bash
docker run -p 8080:80 -v $(pwd):/var/www/html php:8.2-apache
```

---

## 📂 Folder Structure

```
dns-lookup-tool/
│
├── index.php               # Main UI and entry point
├── lookup.php              # DNS lookup logic + AJAX handler
├── README.md
├── LICENSE
├── CHANGELOG.md
├── CONTRIBUTING.md
├── SECURITY.md
├── .gitignore
│
├── assets/
│   ├── style.css           # All styles (dark mode included)
│   └── script.js           # AJAX, rendering, copy, export
│
└── .github/
    └── workflows/
        └── validate.yml
```

---

## 🔌 API / AJAX Usage

The tool includes a built-in JSON API endpoint. Send a POST request with the `X-Requested-With: XMLHttpRequest` header to `lookup.php`:

```bash
curl -X POST https://your-domain.com/dns-lookup-tool/lookup.php \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "domain=hostking.host&record_type=MX"
```

**Response:**

```json
{
  "success": true,
  "domain": "hostking.host",
  "record_type": "MX",
  "error": "",
  "records": [
    {
      "type": "MX",
      "ttl": 3600,
      "value": "10 mail.hostking.host",
      "raw": { ... }
    }
  ],
  "count": 1,
  "query_time": 12.5
}
```

**Error response:**

```json
{
  "success": false,
  "domain": "notadomain",
  "record_type": "A",
  "error": "Invalid domain name format.",
  "records": [],
  "query_time": 0
}
```

---

## 🛡️ Security

- All input is sanitised and validated server-side before any DNS query
- Domain names are checked against RFC-compliant regex
- Protocols, ports and paths are stripped from user input automatically
- PHP error display is not exposed to users
- No database — no SQL injection surface
- No user data is stored or logged

---

## 💡 Use Cases

**Website owners:**
- Verify DNS has propagated after changing nameservers
- Check MX records are pointing to the correct mail server
- Confirm TXT records (SPF, DKIM, DMARC) are set correctly
- Debug CNAME chains

**System administrators:**
- Quick DNS record verification without installing dig or nslookup
- Audit DNS configurations
- Export results for documentation

**Developers:**
- Integrate via the built-in JSON API
- Embed in hosting dashboards or client portals

**Hosting companies:**
- Offer as a branded tool to customers
- Reduce support tickets by letting customers self-check DNS

---

## 🤝 Contributing

Contributions are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md).

**Ideas for future versions:**
- Bulk domain lookup
- DNS propagation comparison (multiple resolvers)
- DNSSEC validation
- History / recently checked domains
- Shareable result URLs
- Reverse DNS (PTR) lookup
- Whois integration

---

## 📄 License

MIT License — see [LICENSE](LICENSE).

---

## 🌍 About Hostking

This project is maintained by **Hostking**, a global web hosting provider offering fast, secure and reliable hosting solutions for businesses worldwide. Founded in 2013, Hostking provides shared hosting, WordPress hosting, VPS hosting, reseller hosting, domain registration, business email and SSL certificates.

Our services include:

- Shared Web Hosting
- WordPress Hosting
- VPS Hosting
- Reseller Hosting
- Domain Registration
- Business Email Hosting
- SSL Certificates

Visit Hostking:

🌍 International — https://www.hostking.host  
🇿🇦 South Africa — https://www.hostking.co.za  
🇦🇪 United Arab Emirates — https://www.hostking.ae  
🇳🇬 Nigeria — https://www.hostking.com.ng  

If you found this project useful, please consider giving it a ⭐ on GitHub!
