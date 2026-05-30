# GATIFIN – Pengelolaan Keuangan Pribadi

> Versi HTML statis untuk deploy di **GitHub Pages** (tanpa backend PHP/MySQL)

## 🌐 Demo Live

Buka `index.html` atau deploy ke GitHub Pages untuk melihat demo.

**Login demo:** username `demo` / password `demo123`

---

## 📁 Struktur File

```
GATIFIN/
├── index.html          ← Aplikasi utama (SPA – semua halaman)
├── login.html          ← Halaman login
├── assets/
│   ├── css/
│   │   └── style.css   ← Stylesheet utama
│   ├── js/
│   │   └── script.js   ← JavaScript utama
│   └── img/            ← Gambar & logo
└── README.md
```

---

## ✅ Fitur yang Berjalan di Versi HTML

| Halaman | Status |
|---|---|
| Login (tampilan) | ✅ |
| Dashboard | ✅ Data dummy |
| Transaksi | ✅ Data dummy |
| Laporan (grafik) | ✅ Chart.js |
| Analisis Finansial | ✅ Perhitungan lokal |
| Data Master | ✅ Tampilan + demo CRUD |
| Profil | ✅ Tampilan |
| Pengaturan | ✅ Dark mode, mata uang |

---

## ⚠️ Fitur yang Memerlukan Backend PHP

Fitur-fitur berikut membutuhkan server PHP + MySQL dan **tidak berfungsi** di versi HTML:

- Autentikasi nyata (login/register tersimpan)
- Penyimpanan data transaksi ke database
- Scan struk/nota dengan Gemini AI
- Upload foto profil
- Sinkronisasi data antar perangkat

---

## 🚀 Deploy ke GitHub Pages

1. Upload seluruh folder ini ke repositori GitHub
2. Masuk ke **Settings → Pages**
3. Pilih branch `main`, folder `/ (root)`
4. Klik **Save** – situs aktif dalam beberapa menit

---

## 🖥️ Jalankan Versi PHP (Full)

Untuk fitur lengkap, jalankan di server lokal:

```bash
# Install XAMPP/Laragon, letakkan folder di htdocs
# Import database SQL
# Akses via http://localhost/GATIFIN/
```

---

*GATIFIN © 2026 – Pengelolaan Keuangan Pribadi yang Cerdas & Akurat*
