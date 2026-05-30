# GATIFIN — Panduan Deploy ke Railway

## Struktur Repo yang Benar

```
repo-kamu/
├── GATIFIN/              ← folder project utama (isi dari ZIP)
│   ├── index.php
│   ├── login.php
│   ├── config/
│   │   ├── koneksi.php   ← GANTI dengan file koneksi.php baru
│   │   └── gemini_key.php ← GANTI dengan file gemini_key.php baru
│   ├── pages/
│   ├── includes/
│   ├── assets/
│   └── ...
├── nixpacks.toml         ← file baru
├── railway.json          ← file baru
└── .gitignore            ← file baru
```

---

## Langkah Deploy

### 1. Siapkan GitHub Repository
```bash
git init
git add .
git commit -m "Initial commit GATIFIN"
git remote add origin https://github.com/username/nama-repo.git
git push -u origin main
```

### 2. Buat Project di Railway
1. Buka https://railway.app → Login
2. Klik **"New Project"** → **"Deploy from GitHub repo"**
3. Pilih repo GATIFIN kamu
4. Tunggu build selesai

### 3. Tambah Database MySQL
1. Di dashboard project → klik **"+ New"**
2. Pilih **"Database"** → **"MySQL"**
3. Railway otomatis mengisi env vars koneksi

### 4. Set Environment Variables
Pergi ke service PHP → tab **"Variables"** → tambahkan:

| Variable       | Value                        |
|----------------|------------------------------|
| `GEMINI_API_KEY` | API key Gemini kamu        |

> Variabel `MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`
> otomatis diisi Railway dari MySQL service — tidak perlu diisi manual.

### 5. Import Database
1. Di MySQL service → tab **"Connect"** → copy kredensial
2. Buka TablePlus / DBeaver → koneksikan ke Railway MySQL
3. Import file `.sql` database Gatfin kamu

### 6. Generate Domain
Di service PHP → tab **"Settings"** → **"Networking"** → klik **"Generate Domain"**

---

## ⚠️ Penting

- **Ganti API key Gemini** di Google AI Studio karena key lama sudah terekspos di file ZIP.
- Folder `assets/foto/` untuk upload foto user tidak persisten di Railway
  (file hilang saat redeploy). Untuk produksi, gunakan cloud storage seperti Cloudinary atau S3.
