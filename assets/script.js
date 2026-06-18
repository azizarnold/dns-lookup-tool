/**
 * Hostking DNS Lookup Tool — script.js
 */

'use strict';

/* ── DOM ────────────────────────────────────────── */
const form        = document.getElementById('lookupForm');
const domainInput = document.getElementById('domain');
const typeSelect  = document.getElementById('recordType');
const lookupBtn   = document.getElementById('lookupBtn');
const resultsArea = document.getElementById('resultsArea');
const spinner     = document.getElementById('spinner');
const themeToggle = document.getElementById('themeToggle');
const themeIcon   = themeToggle.querySelector('.theme-icon');
const quickBtns   = document.querySelectorAll('.quick-btn');

let lastResult = null;
let toastTimer = null;

/* ── Theme ──────────────────────────────────────── */
function initTheme() {
  const stored = localStorage.getItem('hk-theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (stored === 'dark' || (!stored && prefersDark)) setTheme('dark');
}

function setTheme(mode) {
  if (mode === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    themeIcon.textContent = '☀️';
    localStorage.setItem('hk-theme', 'dark');
  } else {
    document.documentElement.removeAttribute('data-theme');
    themeIcon.textContent = '🌙';
    localStorage.setItem('hk-theme', 'light');
  }
}

themeToggle.addEventListener('click', () => {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  setTheme(isDark ? 'light' : 'dark');
});

/* ── Quick type buttons ─────────────────────────── */
quickBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    const type = btn.dataset.type;
    typeSelect.value = type;
    quickBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (domainInput.value.trim()) submitLookup();
  });
});

// Sync active state on select change
typeSelect.addEventListener('change', () => {
  quickBtns.forEach(b => {
    b.classList.toggle('active', b.dataset.type === typeSelect.value);
  });
});

/* ── Form submit via AJAX ───────────────────────── */
form.addEventListener('submit', (e) => {
  e.preventDefault();
  submitLookup();
});

async function submitLookup() {
  const domain = domainInput.value.trim();
  if (!domain) {
    domainInput.focus();
    return;
  }

  setLoading(true);
  resultsArea.innerHTML = '';

  const formData = new FormData();
  formData.append('domain', domain);
  formData.append('record_type', typeSelect.value);

  try {
    const response = await fetch('lookup.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData,
    });

    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const data = await response.json();
    lastResult = data;
    resultsArea.innerHTML = renderResults(data);
    resultsArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

  } catch (err) {
    resultsArea.innerHTML = renderError('Request failed. Please check your connection and try again.');
  } finally {
    setLoading(false);
  }
}

/* ── Render results ─────────────────────────────── */
function renderResults(data) {
  if (!data.success) {
    return `<div class="card result-card error-card">
      <div class="result-header">
        <span><span class="result-icon">⚠️</span><span class="result-title">${esc(data.domain)}</span></span>
      </div>
      <p class="error-message">${esc(data.error)}</p>
    </div>`;
  }

  const rows = data.records.map(rec => `
    <tr>
      <td><span class="badge badge-${rec.type.toLowerCase()}">${esc(rec.type)}</span></td>
      <td class="record-value">${esc(rec.value)}</td>
      <td class="ttl-cell">${formatTTL(rec.ttl)}</td>
    </tr>`).join('');

  return `<div class="card result-card">
    <div class="result-header">
      <div class="result-meta">
        <h2 class="result-domain">${esc(data.domain)}</h2>
        <span class="result-stats">${data.count} record${data.count === 1 ? '' : 's'} · ${data.query_time}ms</span>
      </div>
      <div class="result-actions">
        <button class="btn btn-sm" onclick="copyResults()" title="Copy as text">📋 Copy</button>
        <button class="btn btn-sm" onclick="exportJSON()" title="Export as JSON">⬇ JSON</button>
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
        <tbody>${rows}</tbody>
      </table>
    </div>
  </div>`;
}

function renderError(msg) {
  return `<div class="card result-card error-card">
    <p class="error-message">⚠️ ${esc(msg)}</p>
  </div>`;
}

/* ── Copy results ───────────────────────────────── */
window.copyResults = async function () {
  if (!lastResult || !lastResult.records) return;
  const lines = lastResult.records.map(r => `${r.type}\t${r.value}\tTTL:${r.ttl}`);
  const text = `DNS Lookup: ${lastResult.domain} (${lastResult.record_type})\n\n${lines.join('\n')}`;
  try {
    await navigator.clipboard.writeText(text);
    showToast('✅ Copied to clipboard');
  } catch {
    showToast('⚠️ Copy failed — try selecting manually');
  }
};

/* ── Export JSON ────────────────────────────────── */
window.exportJSON = function (dataArg) {
  const data = dataArg || lastResult;
  if (!data) return;
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement('a');
  a.href     = url;
  a.download = `dns-${data.domain}-${data.record_type}.json`;
  a.click();
  URL.revokeObjectURL(url);
};

/* ── Helpers ────────────────────────────────────── */
function setLoading(on) {
  lookupBtn.classList.toggle('loading', on);
  lookupBtn.disabled = on;
}

function formatTTL(seconds) {
  if (seconds < 60)    return `${seconds}s`;
  if (seconds < 3600)  return `${Math.round(seconds / 60)}m`;
  if (seconds < 86400) return `${Math.round(seconds / 3600)}h`;
  return `${Math.round(seconds / 86400)}d`;
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = String(str ?? '');
  return d.innerHTML;
}

function showToast(msg) {
  let toast = document.getElementById('hkToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'hkToast';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}

/* ── Init ───────────────────────────────────────── */
initTheme();

// Auto-focus domain input
domainInput.focus();

// Highlight matching quick button on load
quickBtns.forEach(b => {
  if (b.dataset.type === typeSelect.value) b.classList.add('active');
});
