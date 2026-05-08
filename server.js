const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const app = express();

app.use(cors());
app.use(express.json());

// Konfigurasi Koneksi MariaDB/MySQL Railway
const db = mysql.createConnection({
    host: process.env.MYSQLHOST,
    user: process.env.MYSQLUSER,
    password: process.env.MYSQLPASSWORD,
    database: process.env.MYSQLDATABASE,
    port: process.env.MYSQLPORT
});

db.connect(err => {
    if (err) {
        console.error('Koneksi Gagal:', err);
        return;
    }
    console.log('Terhubung ke Database MariaDB!');
});

// 1. Simpan Transaksi Baru
app.post('/transactions', (req, res) => {
    // Menambahkan field 'category' agar sesuai dengan form di frontend[cite: 1, 3]
    const { username, type, amount, category, description, transaction_date } = req.body;
    
    const sql = "INSERT INTO transactions (username, type, amount, category, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?)";
    
    db.query(sql, [username, type, amount, category, description, transaction_date], (err, result) => {
        if (err) {
            console.error("Error simpan transaksi:", err);
            return res.status(500).json({ success: false, error: err.message });
        }
        res.json({ success: true, message: "Data berhasil disimpan", id: result.insertId });
    });
});

// 2. Endpoint Login (Diperbaiki untuk sinkronisasi frontend)
app.post('/login', (req, res) => {
    const { username, password } = req.body;

    const query = "SELECT * FROM users WHERE username = ? AND password = ?";
    db.query(query, [username, password], (err, results) => {
        if (err) {
            return res.status(500).json({ success: false, message: "Kesalahan server." });
        }

        if (results.length > 0) {
            // Mengirim data user agar frontend bisa menyimpan session[cite: 3]
            res.json({ success: true, username: results[0].username });
        } else {
            res.json({ success: false, message: "Username atau password salah!" });
        }
    });
});

// 3. Endpoint Registrasi[cite: 3]
app.post('/register', (req, res) => {
    const { username, email, password, full_name } = req.body;
    const sql = "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
    
    db.query(sql, [username, email, password, full_name], (err, result) => {
        if (err) {
            if (err.code === 'ER_DUP_ENTRY') {
                return res.status(400).json({ success: false, message: "Username/Email sudah ada!" });
            }
            return res.status(500).json({ success: false, message: "Gagal mendaftar." });
        }
        res.json({ success: true, message: "Pendaftaran berhasil!" });
    });
});

// 4. Ambil Transaksi (Diperbaiki: Filter berdasarkan username agar data tidak bercampur)[cite: 3]
app.get('/transactions/:username', (req, res) => {
    const sql = "SELECT * FROM transactions WHERE username = ? ORDER BY transaction_date DESC";
    db.query(sql, [req.params.username], (err, results) => {
        if (err) return res.status(500).send(err);
        res.json(results);
    });
});

// 5. Hapus Transaksi[cite: 3]
app.delete('/transactions/:id', (req, res) => {
    // Gunakan 'transaction_id' atau 'id' sesuai struktur tabel Anda
    db.query("DELETE FROM transactions WHERE transaction_id = ?", [req.params.id], (err, result) => {
        if (err) return res.status(500).send(err);
        res.json({ success: true, message: "Data berhasil dihapus" });
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server berjalan di port ${PORT}`);
});
