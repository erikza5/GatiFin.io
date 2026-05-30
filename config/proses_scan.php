<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';
if (file_exists(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}

function scan_is_redirect_request(): bool
{
    return isset($_POST['redirect']) && $_POST['redirect'] === 'dashboard';
}

function scan_respond(array $payload, int $status = 200): void
{
    if (ob_get_length()) {
        ob_end_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function scan_redirect(string $status, array $scanResult = null): void
{
    if ($scanResult !== null) {
        $_SESSION['scan_result'] = $scanResult;
        $target = '../index.php?page=dashboard&scan=success';
    } else {
        $target = '../index.php?page=dashboard&status=' . $status;
    }

    if (ob_get_length()) {
        ob_end_clean();
    }
    header('Location: ' . $target);
    exit;
}

function scan_fail(string $message, int $status = 400): void
{
    if (scan_is_redirect_request()) {
        $_SESSION['scan_error'] = $message;
        scan_redirect('gagal_scan');
    }

    scan_respond(['success' => false, 'message' => $message], $status);
}

function scan_table_columns(mysqli $koneksi, string $table): array
{
    $columns = [];
    $safeTable = str_replace('`', '', $table);
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `$safeTable`");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[$row['Field']] = true;
        }
    }
    return $columns;
}

function scan_insert_transaksi(mysqli $koneksi, int $userId, array $data): array
{
    $columns = scan_table_columns($koneksi, 'transaksi');
    $idKategori = (int)($data['id_kategori'] ?? 0);
    $idDompet = (int)($data['id_dompet'] ?? 0);
    $tanggal = $data['tanggal'] ?? date('Y-m-d');
    $nominal = (float)($data['nominal'] ?? 0);
    $toko = trim($data['toko'] ?? 'Toko Umum');
    $keterangan = trim($data['keterangan'] ?? ('Belanja di ' . $toko));

    if ($idKategori <= 0 || $idDompet <= 0 || $nominal <= 0) {
        return ['success' => false, 'message' => 'Kategori, dompet, dan nominal wajib diisi dengan benar.'];
    }

    $fields = [
        'user_id' => $userId,
        'id_kategori' => $idKategori,
        'id_dompet' => $idDompet,
        'tanggal' => $tanggal,
        'jumlah' => $nominal,
        'keterangan' => $keterangan,
    ];

    if (isset($columns['id_sub'])) {
        $fields['id_sub'] = (int)($data['id_sub'] ?? 0);
    }

    if (isset($columns['kode_transaksi'])) {
        $idSub = (int)($fields['id_sub'] ?? 0);
        $fields['kode_transaksi'] = function_exists('generateKodeTransaksi')
            ? generateKodeTransaksi($userId, $idKategori, $idSub, $idDompet)
            : ('TX-' . strtoupper(substr(md5(uniqid('', true)), 0, 8)));
    }

    $insertColumns = [];
    $insertValues = [];
    foreach ($fields as $column => $value) {
        if (!isset($columns[$column])) {
            continue;
        }
        $insertColumns[] = '`' . $column . '`';
        $insertValues[] = "'" . mysqli_real_escape_string($koneksi, (string)$value) . "'";
    }

    $query = 'INSERT INTO transaksi (' . implode(', ', $insertColumns) . ') VALUES (' . implode(', ', $insertValues) . ')';

    if (mysqli_query($koneksi, $query)) {
        return ['success' => true, 'message' => 'Data transaksi berhasil disimpan!'];
    }

    return ['success' => false, 'message' => 'Gagal menulis ke database: ' . mysqli_error($koneksi)];
}

function scan_get_gemini_api_key(): string
{
    $candidates = [
        getenv('GEMINI_API_KEY'),
        getenv('GOOGLE_API_KEY'),
        $_ENV['GEMINI_API_KEY'] ?? null,
        $_SERVER['GEMINI_API_KEY'] ?? null,
    ];

    $keyFile = __DIR__ . '/gemini_key.php';
    if (file_exists($keyFile)) {
        $loaded = include $keyFile;
        if (is_string($loaded)) {
            $candidates[] = $loaded;
        }
        if (defined('GEMINI_API_KEY')) {
            $candidates[] = GEMINI_API_KEY;
        }
    }

    foreach ($candidates as $candidate) {
        $candidate = trim((string)$candidate);
        if ($candidate !== '' && $candidate !== 'PASTE_API_KEY_GEMINI_ANDA_DISINI') {
            return $candidate;
        }
    }

    return '';
}

function scan_get_gemini_model(): string
{
    $candidates = [
        getenv('GEMINI_MODEL'),
        $_ENV['GEMINI_MODEL'] ?? null,
        $_SERVER['GEMINI_MODEL'] ?? null,
    ];

    $keyFile = __DIR__ . '/gemini_key.php';
    if (file_exists($keyFile)) {
        include $keyFile;
        if (defined('GEMINI_MODEL')) {
            $candidates[] = GEMINI_MODEL;
        }
    }

    foreach ($candidates as $candidate) {
        $candidate = trim((string)$candidate);
        if ($candidate !== '') {
            return preg_replace('/^models\//', '', $candidate);
        }
    }

    return 'gemini-2.0-flash';
}

function scan_get_gemini_models(): array
{
    $primary = scan_get_gemini_model();
    $fallbacks = ['gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-flash-lite'];
    $models = array_values(array_unique(array_filter(array_merge([$primary], $fallbacks))));

    return array_map(function ($model) {
        return preg_replace('/^models\//', '', trim((string)$model));
    }, $models);
}

function scan_friendly_api_error(string $message, string $model): string
{
    $lower = strtolower($message);

    if (strpos($lower, 'quota') !== false || strpos($lower, 'rate limit') !== false || strpos($lower, 'exceeded') !== false) {
        return "Kuota Gemini untuk model $model sedang habis atau belum aktif. Coba tunggu beberapa menit, ganti GEMINI_MODEL ke gemini-2.5-flash-lite, atau gunakan API key Google AI Studio yang kuotanya masih tersedia.";
    }

    if (strpos($lower, 'not found') !== false || strpos($lower, 'not supported') !== false) {
        return "Model Gemini $model tidak tersedia untuk API key ini. Ganti GEMINI_MODEL di config/gemini_key.php ke gemini-2.5-flash atau gemini-2.5-flash-lite.";
    }

    if (strpos($lower, 'api key') !== false || strpos($lower, 'permission') !== false || strpos($lower, 'denied') !== false) {
        return 'API key Gemini tidak valid atau belum punya izin. Buat API key baru di Google AI Studio lalu masukkan ke config/gemini_key.php.';
    }

    return 'Analisis AI gagal: ' . $message;
}

function scan_extract_json(string $text): ?array
{
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);

    $decoded = json_decode($text, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    if (preg_match('/\{.*\}/s', $text, $match)) {
        $decoded = json_decode($match[0], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    return null;
}

function scan_parse_receipt_text(string $text): ?array
{
    $plain = strtoupper($text);
    $plain = str_replace(["\r", "\t"], ["\n", " "], $plain);

    $total = null;
    if (preg_match('/\b(?:GRAND\s+TOTAL|TOTAL\s+BELANJA|TOTAL\s+HARGA|TOTAL)\b\s*[:=]?\s*(?:RP\.?\s*)?([0-9][0-9\s.,]*)/i', $plain, $match)) {
        $total = scan_normalize_nominal($match[1]);
    }

    if (!$total && preg_match_all('/\b(?:HARGA\s+JUAL)\b\s*[:=]?\s*(?:RP\.?\s*)?([0-9][0-9\s.,]*)/i', $plain, $matches)) {
        $total = scan_normalize_nominal(end($matches[1]));
    }

    $date = date('Y-m-d');
    if (preg_match('/\b(\d{1,2})[.\-\/](\d{1,2})[.\-\/](\d{2,4})\b/', $plain, $dateMatch)) {
        $year = (int)$dateMatch[3];
        if ($year < 100) {
            $year += ($year >= 70) ? 1900 : 2000;
        }
        $date = sprintf('%04d-%02d-%02d', $year, (int)$dateMatch[2], (int)$dateMatch[1]);
    }

    $store = 'Toko Umum';
    $knownStores = ['INDOMARET', 'ALFAMART', 'ALFAMIDI', 'LAWSON', 'CIRCLE K', 'SUPERINDO', 'HYPERMART', 'MINISO'];
    foreach ($knownStores as $knownStore) {
        if (strpos($plain, $knownStore) !== false) {
            $store = ucwords(strtolower($knownStore));
            break;
        }
    }

    if ($total && $total > 0) {
        return [
            'toko' => $store,
            'tanggal' => $date,
            'nominal' => $total,
        ];
    }

    return null;
}

function scan_normalize_date($value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return date('Y-m-d');
    }

    if (preg_match('/^(\d{1,2})[.\-\/](\d{1,2})[.\-\/](\d{2,4})$/', $value, $match)) {
        $year = (int)$match[3];
        if ($year < 100) {
            $year += ($year >= 70) ? 1900 : 2000;
        }
        return sprintf('%04d-%02d-%02d', $year, (int)$match[2], (int)$match[1]);
    }

    $timestamp = strtotime(str_replace('/', '-', $value));
    return $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
}

function scan_normalize_nominal($value): int
{
    if (is_numeric($value)) {
        return max(0, (int)round((float)$value));
    }

    $digits = preg_replace('/[^0-9]/', '', (string)$value);
    return $digits === '' ? 0 : (int)$digits;
}

if (!$koneksi) {
    scan_fail('Gagal terhubung ke database lokal.', 500);
}

$id_user_login = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($id_user_login <= 0) {
    scan_fail('Sesi berakhir, silakan login kembali.', 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'simpan_database') {
    $result = scan_insert_transaksi($koneksi, $id_user_login, [
        'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
        'nominal' => $_POST['nominal'] ?? 0,
        'id_kategori' => $_POST['id_kategori'] ?? 0,
        'id_dompet' => $_POST['id_dompet'] ?? 0,
        'toko' => $_POST['toko'] ?? 'Toko Umum',
    ]);

    scan_respond($result, $result['success'] ? 200 : 400);
}

$file_key = isset($_FILES['image_struk']) ? 'image_struk' : (isset($_FILES['foto_nota']) ? 'foto_nota' : null);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $file_key === null) {
    scan_fail('Metode akses ditolak. Form pengiriman berkas tidak sesuai.', 405);
}

if (($_FILES[$file_key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    scan_fail('File gambar nota tidak valid atau gagal diunggah.');
}

$image_path = $_FILES[$file_key]['tmp_name'] ?? '';
if ($image_path === '' || !is_uploaded_file($image_path)) {
    scan_fail('File gambar nota tidak valid atau corrupt.');
}

$maxSize = 8 * 1024 * 1024;
if (($_FILES[$file_key]['size'] ?? 0) > $maxSize) {
    scan_fail('Ukuran gambar terlalu besar. Maksimal 8 MB.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$image_mime = $finfo->file($image_path) ?: ($_FILES[$file_key]['type'] ?? '');
$allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($image_mime, $allowed_mimes, true)) {
    scan_fail('Format gambar belum didukung. Gunakan JPG, PNG, atau WEBP.');
}

$api_key = scan_get_gemini_api_key();
if ($api_key === '') {
    scan_fail('Kunci API Gemini belum tersedia. Isi config/gemini_key.php atau environment GEMINI_API_KEY terlebih dahulu.');
}

$image_data = base64_encode(file_get_contents($image_path));
$prompt = 'Baca struk/nota minimarket Indonesia seperti Indomaret, Alfamart, Alfamidi, dan toko lain. Kembalikan hanya JSON valid tanpa markdown dengan field: toko (nama merchant), tanggal (format YYYY-MM-DD; tanggal seperti 14.09.16 berarti 2016-09-14; gunakan ' . date('Y-m-d') . ' jika tidak ada), nominal (integer total akhir yang harus dibayar). Prioritaskan baris TOTAL/GRAND TOTAL/TOTAL BELANJA sebagai nominal. Abaikan TUNAI, BAYAR, KEMBALI, ANDA HEMAT, DISKON, SUBTOTAL item, nomor telepon, nomor struk, dan harga satuan barang. Untuk contoh Indomaret: TOTAL : 33,900 berarti nominal 33900.';

$payload = [
    'contents' => [[
        'parts' => [
            ['text' => $prompt],
            ['inlineData' => ['mimeType' => $image_mime, 'data' => $image_data]],
        ],
    ]],
    'generationConfig' => [
        'temperature' => 0.1,
        'responseMimeType' => 'application/json',
    ],
];

$result = null;
$lastApiMessage = 'Server AI menolak permintaan.';
$lastModel = scan_get_gemini_model();

foreach (scan_get_gemini_models() as $model) {
    $lastModel = $model;
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($api_key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 45,
    ]);

    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        $lastApiMessage = 'Gagal terhubung dengan server Google AI: ' . $curl_error;
        continue;
    }

    $candidateResult = json_decode($response, true);
    if ($http_code >= 200 && $http_code < 300) {
        $result = $candidateResult;
        break;
    }

    $lastApiMessage = $candidateResult['error']['message'] ?? 'Server AI menolak permintaan.';
}

if (!$result) {
    scan_fail(scan_friendly_api_error($lastApiMessage, $lastModel), 502);
}

$ai_text_output = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
$data_json = scan_extract_json($ai_text_output);
if (!$data_json) {
    $data_json = scan_parse_receipt_text($ai_text_output);
}

if (!$data_json) {
    scan_fail('Sistem gagal membaca format keluaran AI atau menemukan baris TOTAL pada struk.');
}

$toko_clean = trim((string)($data_json['toko'] ?? 'Toko Umum'));
$tanggal_clean = scan_normalize_date($data_json['tanggal'] ?? date('Y-m-d'));
$nominal_clean = scan_normalize_nominal($data_json['nominal'] ?? 0);

if ($nominal_clean <= 0) {
    $fallback_json = scan_parse_receipt_text($ai_text_output);
    if ($fallback_json) {
        $toko_clean = trim((string)($fallback_json['toko'] ?? $toko_clean));
        $tanggal_clean = scan_normalize_date($fallback_json['tanggal'] ?? $tanggal_clean);
        $nominal_clean = scan_normalize_nominal($fallback_json['nominal'] ?? 0);
    }
}

if ($nominal_clean <= 0) {
    scan_fail('AI belum menemukan baris TOTAL pada struk. Pastikan bagian TOTAL terlihat jelas, bukan hanya TUNAI atau KEMBALI.');
}

if ($toko_clean === '') {
    $toko_clean = 'Toko Umum';
}

if (scan_is_redirect_request()) {
    scan_redirect('sukses', [
        'tanggal' => $tanggal_clean,
        'jumlah' => $nominal_clean,
        'keterangan' => 'Belanja di ' . $toko_clean,
    ]);
}

scan_respond([
    'success' => true,
    'data' => [
        'toko' => htmlspecialchars($toko_clean, ENT_QUOTES, 'UTF-8'),
        'tanggal' => htmlspecialchars($tanggal_clean, ENT_QUOTES, 'UTF-8'),
        'nominal' => $nominal_clean,
    ],
]);

