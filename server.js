const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const app = express();

app.use(cors());
app.use(express.json());

// Konfigurasi Koneksi MariaDB/MySQL
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',      // Sesuaikan dengan user MySQL Anda
    password: '',      // Sesuaikan dengan password MySQL Anda
    database: 'gatifin_db'
});

db.connect(err => {
    if (err) throw err;
    console.log('Terhubung ke Database MariaDB!');
});

// Endpoint untuk Login
app.post('/transactions', (req, res) => {
    const { type, amount, description, transaction_date } = req.body;
    
    // Mapping value 'masuk' -> 'Pemasukan' dan 'keluar' -> 'Pengeluaran'
    // agar sesuai dengan ENUM di database Anda
    const dbType = type === 'masuk' ? 'Pemasukan' : 'Pengeluaran';

    const sql = `INSERT INTO transactions 
                 (type, amount, description, transaction_date) 
                 VALUES (?, ?, ?, ?)`;
                 
    db.query(sql, [dbType, amount, description, transaction_date], (err, result) => {
        if (err) {
            console.error(err);
            return res.status(500).send(err);
        }
        res.json({ success: true, id: result.insertId });
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

// Simpan transaksi baru
app.post('/transactions', (req, res) => {
    const { username, type, amount, category, note, date } = req.body;
    const sql = "INSERT INTO transactions (username, type, amount, category, note, date) VALUES (?, ?, ?, ?, ?, ?)";
    db.query(sql, [username, type, amount, category, note, date], (err, result) => {
        if (err) return res.status(500).send(err);
        res.json({ message: "Data berhasil disimpan", id: result.insertId });
    });
});

// Hapus transaksi
app.delete('/transactions/:id', (req, res) => {
    db.query("DELETE FROM transactions WHERE id = ?", [req.params.id], (err, result) => {
        if (err) return res.status(500).send(err);
        res.json({ message: "Data berhasil dihapus" });
    });
});

app.listen(3000, () => console.log('Server berjalan di port 3000'));