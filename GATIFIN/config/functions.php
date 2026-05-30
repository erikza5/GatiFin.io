<?php
/**
 * Menghasilkan kode transaksi dengan format:
 * TRX-[USER]-[KAT]-[SUB]-[DOMPET]-[TANGGAL]-[RAND]
 */
function generateKodeTransaksi($id_user, $id_kategori, $id_sub, $id_dompet) {
    // 1. Tanggal (YYMMDD)
    $tanggal = date('ymd');
    
    // 2. Padding agar panjang konsisten (User 3 digit, lainnya 2 digit)
    $u = str_pad($id_user, 3, "0", STR_PAD_LEFT);
    $c = str_pad($id_kategori, 2, "0", STR_PAD_LEFT);
    $s = str_pad($id_sub, 2, "0", STR_PAD_LEFT);
    $d = str_pad($id_dompet, 2, "0", STR_PAD_LEFT);
    
    // 3. Randomizer 3 digit
    $rand = rand(100, 999);
    
    // Hasil: TRX-002-01-05-01-260522-842
    return "TRX-{$u}-{$c}-{$s}-{$d}-{$tanggal}-{$rand}";
}
?>