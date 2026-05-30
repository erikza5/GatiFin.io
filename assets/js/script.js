$(document).ready(function () {
    // DataTables Global Init
    if ($('.datatable-responsive').length > 0) {
        $('.datatable-responsive').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari:",
                searchPlaceholder: "Cari data...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "_START_â€“_END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(dari _MAX_ total)",
                zeroRecords: "Data tidak ditemukan",
                paginate: { first: "Â«", last: "Â»", next: "â€º", previous: "â€¹" }
            },
            dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"lf>rt<"d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"ip>'
        });
    }
});

/* GATIFIN global preferences: theme, currency, and language */
(function () {
    const rates = {
        IDR: { symbol: 'Rp', rate: 1, locale: 'id-ID' },
        USD: { symbol: '$', rate: 1 / 16000, locale: 'en-US' },
        EUR: { symbol: 'â‚¬', rate: 1 / 17500, locale: 'de-DE' },
        MYR: { symbol: 'RM', rate: 1 / 3400, locale: 'ms-MY' },
        SAR: { symbol: 'ï·¼', rate: 1 / 4260, locale: 'ar-SA' }
    };

    const dictionary = {
        en: {
            'Dashboard Keuangan': 'Financial Dashboard',
            'Data Transaksi': 'Transaction Data',
            'Laporan Keuangan': 'Financial Report',
            'Analisis Finansial': 'Financial Analysis',
            'Pengaturan Sistem': 'System Settings',
            'Tambah Transaksi': 'Add Transaction',
            'Tambah Manual': 'Add Manually',
            'Scan Nota/Struk': 'Scan Receipt',
            'Total Pemasukan': 'Total Income',
            'Total Pengeluaran': 'Total Expense',
            'Saldo Bersih': 'Net Balance',
            'Mata Uang Default': 'Default Currency',
            'Tema Antarmuka': 'Interface Theme',
            'Mode Gelap (Dark Theme)': 'Dark Mode',
            'Keamanan Akun': 'Account Security',
            'Keluar dari Akun': 'Sign Out'
        },
        id: {}
    };

    function applyTheme() {
        const theme = localStorage.getItem('gatifin_theme') || 'light';
        document.documentElement.dataset.theme = theme;
        document.body.classList.toggle('dark-mode-active', theme === 'dark');
    }

    function parseIdr(text) {
        return Number(String(text).replace(/[^\d]/g, '')) || 0;
    }

    function formatCurrency(value, currency) {
        const cfg = rates[currency] || rates.IDR;
        const converted = value * cfg.rate;
        const digits = currency === 'IDR' ? 0 : 2;
        return cfg.symbol + ' ' + converted.toLocaleString(cfg.locale, {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits
        });
    }

    function applyCurrency() {
        const currency = localStorage.getItem('gatifin_currency') || 'IDR';
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
            const parent = node.parentElement;
            if (!parent || ['SCRIPT', 'STYLE', 'TEXTAREA', 'INPUT', 'SELECT'].includes(parent.tagName)) return;
            if (!parent.dataset.gatifinOriginalText && /Rp\s*[0-9.]+/.test(node.nodeValue)) {
                parent.dataset.gatifinOriginalText = node.nodeValue;
            }
            const original = parent.dataset.gatifinOriginalText;
            if (!original) return;
            node.nodeValue = original.replace(/Rp\s*[0-9.]+/g, (match) => formatCurrency(parseIdr(match), currency));
        });
    }

    function applyLanguage() {
        const lang = localStorage.getItem('gatifin_language') || 'id';
        if (lang === 'id') return;
        const map = dictionary[lang] || {};
        document.querySelectorAll('h1,h2,h3,h4,h5,h6,p,span,label,button,a,option,small,th').forEach((el) => {
            const text = el.childNodes.length === 1 ? el.textContent.trim() : '';
            if (map[text]) el.textContent = map[text];
        });
    }

    function ensureSettingsControls() {
        const currency = document.getElementById('selectMataUang');
        if (currency) {
            ['MYR|MYR (RM) - Ringgit Malaysia', 'SAR|SAR (ï·¼) - Riyal Saudi'].forEach((item) => {
                const [value, label] = item.split('|');
                if (!currency.querySelector(`option[value="${value}"]`)) {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = label;
                    currency.appendChild(opt);
                }
            });
            currency.value = localStorage.getItem('gatifin_currency') || 'IDR';
        }

        if (currency && !document.getElementById('selectBahasa')) {
            const col = document.createElement('div');
            col.className = 'col-12 col-md-6';
            col.innerHTML = `
                <label class="form-label small fw-bold text-secondary">Bahasa</label>
                <select id="selectBahasa" class="form-select form-select-settings">
                    <option value="id">Indonesia</option>
                    <option value="en">English</option>
                </select>`;
            currency.closest('.row').appendChild(col);
            document.getElementById('selectBahasa').value = localStorage.getItem('gatifin_language') || 'id';
            document.getElementById('selectBahasa').addEventListener('change', function () {
                localStorage.setItem('gatifin_language', this.value);
                location.reload();
            });
        }
    }

    window.toggleDarkMode = function (enabled) {
        localStorage.setItem('gatifin_theme', enabled ? 'dark' : 'light');
        applyTheme();
    };

    window.saveCurrencyPreference = function () {
        const selected = document.getElementById('selectMataUang')?.value || 'IDR';
        localStorage.setItem('gatifin_currency', selected);
        applyCurrency();
        if (window.Swal) Swal.fire({ icon: 'success', title: 'Preferensi diperbarui', text: 'Format mata uang diterapkan ke tampilan aplikasi.', confirmButtonColor: '#006D5B' });
    };

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme();
        ensureSettingsControls();
        applyCurrency();
        applyLanguage();
    });
})();


function gatifinApplyThemeHotfix() {
    const theme = localStorage.getItem('gatifin_theme') || 'light';
    document.documentElement.dataset.theme = theme;
    document.documentElement.classList.toggle('dark-mode-active', theme === 'dark');
    document.body.classList.toggle('dark-mode-active', theme === 'dark');
}
document.addEventListener('DOMContentLoaded', gatifinApplyThemeHotfix);
window.addEventListener('storage', gatifinApplyThemeHotfix);