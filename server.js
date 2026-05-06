const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const app = express();

app.use(cors());
app.use(express.json());

// Konfigurasi Koneksi MariaDB/MySQL
const db = mysql.createConnection({
    host: process.env.MYSQLHOST,
    user: process.env.MYSQLUSER,
    password: process.env.MYSQLPASSWORD,
    database: process.env.MYSQLDATABASE,
    port: process.env.MYSQLPORT
});

db.connect(err => {
    if (err) throw err;
    console.log('Terhubung ke Database MariaDB!');
});

// Simpan transaksi baru (Versi Gabungan)
app.post('/transactions', (req, res) => {
    const { username, type, amount, category, description, transaction_date } = req.body;
    
    // Pastikan kolom ini sesuai dengan nama kolom di tabel MySQL Railway Anda
    const sql = "INSERT INTO transactions (username, type, amount, category, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?)";
    
    db.query(sql, [username, type, amount, category, description, transaction_date], (err, result) => {
        if (err) {
            console.error("Error saat simpan transaksi:", err);
            return res.status(500).json({ success: false, error: err.message });
        }
        res.json({ success: true, message: "Data berhasil disimpan", id: result.insertId });
    });
});

// Endpoint untuk Login
app.post('/login', (req, res) => {
    const { username, password } = req.body;

    // Mencari user di tabel 'users'
    const query = "SELECT * FROM users WHERE username = ? AND password = ?";
    db.query(query, [username, password], (err, results) => {
        if (err) {
            console.error("Error saat login:", err);
            return res.status(500).json({ success: false, message: "Terjadi kesalahan pada server." });
        }

        if (results.length > 0) {
            // Jika ketemu
            res.json({ success: true, username: results[0].username });
        } else {
            // Jika tidak ketemu
            res.json({ success: false, message: "Username atau password salah!" });
        }
    });
});

// Endpoint untuk Registrasi)
app.post('/register', async (req, res) => {
    const { username, email, password, full_name } = req.body;
    const sql = "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
    
    db.query(sql, [username, email, password, full_name], (err, result) => {
        if (err) {
            console.error(err);
            if (err.code === 'ER_DUP_ENTRY') {
                return res.status(400).json({ success: false, message: "Username/Email sudah ada!" });
            }
            return res.status(500).json({ success: false, message: "Gagal mendaftar." });
        }
        res.json({ success: true, message: "Pendaftaran berhasil!" });
    });
});
// Ambil data transaksi berdasarkan username
app.get('/transactions/:username', (req, res) => {
    // Karena database Anda menggunakan user_id (INT) sementara UI menggunakan username (String),
    // Untuk sementara kita asumsikan pencarian berdasarkan data yang ada. 
    // Jika Anda punya tabel users, sebaiknya lakukan JOIN.
    const sql = "SELECT * FROM transactions ORDER BY transaction_date DESC";
    db.query(sql, (err, results) => {
        if (err) return res.status(500).send(err);
        res.json(results);
    });
});

// Hapus transaksi
app.delete('/transactions/:id', (req, res) => {
    db.query("DELETE FROM transactions WHERE id = ?", [req.params.id], (err, result) => {
        if (err) return res.status(500).send(err);
        res.json({ message: "Data berhasil dihapus" });
    });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server berjalan di port ${PORT}`);
});
