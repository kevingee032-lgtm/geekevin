<?php
// ============================================================
// WARUNGPOS — Sistem Kasir Modern dengan AI Asisten
// ============================================================
// File tunggal PHP yang menangani:
//   1. Session untuk keranjang belanja & riwayat transaksi
//   2. Database produk (array statis)
//   3. AJAX handler (add/remove/checkout/AI chat)
//   4. Tampilan HTML/CSS/JS untuk antarmuka kasir
// ============================================================

session_start(); // Mulai sesi PHP agar data keranjang tersimpan selama browser dibuka

// ----------------------------------------------------------
// INISIALISASI SESSION
// Pastikan variabel session sudah ada sebelum digunakan
// ----------------------------------------------------------
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Keranjang belanja: array asosiatif [product_id => item_data]
}
if (!isset($_SESSION['transactions'])) {
    $_SESSION['transactions'] = []; // Riwayat transaksi yang sudah selesai
}

// ----------------------------------------------------------
// DATABASE PRODUK
// Dalam aplikasi nyata, ini akan diambil dari database MySQL.
// Format: id, nama, harga (Rupiah), kategori, stok, emoji
// ----------------------------------------------------------
$products = [
    ['id' => 1,  'name' => 'Nasi Goreng Spesial', 'price' => 25000, 'category' => 'Makanan',  'stock' => 50,  'emoji' => '🍳'],
    ['id' => 2,  'name' => 'Mie Ayam Bakso',       'price' => 20000, 'category' => 'Makanan',  'stock' => 40,  'emoji' => '🍜'],
    ['id' => 3,  'name' => 'Soto Ayam',             'price' => 18000, 'category' => 'Makanan',  'stock' => 35,  'emoji' => '🥣'],
    ['id' => 4,  'name' => 'Gado-Gado',             'price' => 15000, 'category' => 'Makanan',  'stock' => 30,  'emoji' => '🥗'],
    ['id' => 5,  'name' => 'Ayam Bakar',            'price' => 35000, 'category' => 'Makanan',  'stock' => 25,  'emoji' => '🍗'],
    ['id' => 6,  'name' => 'Es Teh Manis',          'price' => 5000,  'category' => 'Minuman',  'stock' => 100, 'emoji' => '🧋'],
    ['id' => 7,  'name' => 'Jus Alpukat',           'price' => 15000, 'category' => 'Minuman',  'stock' => 60,  'emoji' => '🥤'],
    ['id' => 8,  'name' => 'Air Mineral',           'price' => 3000,  'category' => 'Minuman',  'stock' => 200, 'emoji' => '💧'],
    ['id' => 9,  'name' => 'Kopi Hitam',            'price' => 8000,  'category' => 'Minuman',  'stock' => 80,  'emoji' => '☕'],
    ['id' => 10, 'name' => 'Kerupuk',               'price' => 2000,  'category' => 'Snack',    'stock' => 150, 'emoji' => '🍘'],
    ['id' => 11, 'name' => 'Pisang Goreng',         'price' => 10000, 'category' => 'Snack',    'stock' => 45,  'emoji' => '🍌'],
    ['id' => 12, 'name' => 'Tempe Mendoan',         'price' => 8000,  'category' => 'Snack',    'stock' => 55,  'emoji' => '🫘'],
];

// ----------------------------------------------------------
// AJAX REQUEST HANDLER
// Semua request POST dari JavaScript ditangani di sini.
// Response selalu berupa JSON agar mudah diproses di frontend.
// ----------------------------------------------------------
if (isset($_POST['action'])) {
    header('Content-Type: application/json'); // Beritahu browser bahwa respons adalah JSON

    // --- TAMBAH PRODUK KE KERANJANG ---
    // Dipanggil saat user klik kartu produk
    if ($_POST['action'] === 'add_to_cart') {
        $productId = (int)$_POST['product_id']; // Cast ke integer untuk keamanan
        foreach ($products as $p) {
            if ($p['id'] === $productId) {
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId]['qty']++; // Tambah qty jika sudah ada
                } else {
                    // Tambahkan sebagai item baru di keranjang
                    $_SESSION['cart'][$productId] = [
                        'id'    => $p['id'],
                        'name'  => $p['name'],
                        'price' => $p['price'],
                        'emoji' => $p['emoji'],
                        'qty'   => 1
                    ];
                }
                break;
            }
        }
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }

    // --- KURANGI QTY PRODUK DARI KERANJANG ---
    // Jika qty menjadi 0, item dihapus dari keranjang
    if ($_POST['action'] === 'remove_from_cart') {
        $productId = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            if ($_SESSION['cart'][$productId]['qty'] > 1) {
                $_SESSION['cart'][$productId]['qty']--; // Kurangi qty
            } else {
                unset($_SESSION['cart'][$productId]); // Hapus item jika qty sudah 1
            }
        }
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }

    // --- HAPUS ITEM LANGSUNG DARI KERANJANG ---
    // Dipanggil saat user klik tombol ✕ pada item keranjang
    if ($_POST['action'] === 'delete_item') {
        $productId = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$productId]);
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
        exit;
    }

    // --- PROSES CHECKOUT / PEMBAYARAN ---
    // Validasi pembayaran, simpan transaksi, kosongkan keranjang
    if ($_POST['action'] === 'checkout') {
        $payment = (float)$_POST['payment']; // Jumlah uang yang dibayarkan pelanggan
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['qty']; // Hitung total belanja
        }
        $change = $payment - $total; // Hitung kembalian

        // Validasi: uang tidak cukup
        if ($change < 0) {
            echo json_encode(['success' => false, 'message' => 'Pembayaran kurang!']);
            exit;
        }

        // Buat objek transaksi dan simpan ke session
        $transaction = [
            'id'      => 'TRX' . date('YmdHis'), // ID unik berdasarkan waktu
            'date'    => date('d/m/Y H:i:s'),
            'items'   => $_SESSION['cart'],
            'total'   => $total,
            'payment' => $payment,
            'change'  => $change
        ];
        $_SESSION['transactions'][] = $transaction; // Tambah ke riwayat
        $_SESSION['cart'] = [];                      // Kosongkan keranjang

        echo json_encode(['success' => true, 'transaction' => $transaction]);
        exit;
    }

    // --- KOSONGKAN SELURUH KERANJANG ---
    if ($_POST['action'] === 'clear_cart') {
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        exit;
    }

    // --- AI CHAT HANDLER ---
    // Meneruskan pesan user ke Anthropic API dan mengembalikan respons AI.
    // Diproses di backend PHP agar API key tidak terekspos ke browser.
    if ($_POST['action'] === 'ai_chat') {
        $userMessage = trim($_POST['message'] ?? '');
        if (empty($userMessage)) {
            echo json_encode(['success' => false, 'reply' => 'Pesan kosong.']);
            exit;
        }

        // ⚠️ PENTING: Ganti nilai ini dengan API key Anthropic kamu!
        // Dapatkan API key di: https://console.anthropic.com/
        // Jangan pernah taruh API key langsung di kode yang dishare ke publik.
        // Sebaiknya gunakan environment variable: getenv('ANTHROPIC_API_KEY')
        $apiKey = getenv('ANTHROPIC_API_KEY') ?: 'MASUKKAN_API_KEY_ANTHROPIC_KAMU_DI_SINI';

        // Bangun ringkasan data warung untuk konteks AI
        $totalTransactions = count($_SESSION['transactions']);
        $totalRevenue      = array_sum(array_column($_SESSION['transactions'], 'total'));
        $cartItems         = array_values($_SESSION['cart']);
        $cartTotal         = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cartItems));

        // Ringkasan riwayat transaksi (maks 5 transaksi terakhir)
        $recentTrx = array_slice(array_reverse($_SESSION['transactions']), 0, 5);
        $trxSummary = '';
        foreach ($recentTrx as $trx) {
            $itemNames = implode(', ', array_map(fn($i) => $i['name'].'×'.$i['qty'], $trx['items']));
            $trxSummary .= "- [{$trx['id']}] {$trx['date']}: {$itemNames} = Rp ".number_format($trx['total'],0,',','.')." \n";
        }

        // System prompt: instruksi kepribadian dan konteks data untuk AI
        $systemPrompt = "Kamu adalah AI Asisten bernama 'WakiPOS' untuk sistem kasir warung makan bernama 'WarungPOS'.
Tugasmu adalah membantu pemilik warung dengan analisa bisnis, rekomendasi strategi, dan pertanyaan operasional.
Berikan jawaban yang singkat, padat, dan actionable dalam Bahasa Indonesia. Gunakan emoji secukupnya.

=== DATA WARUNG SAAT INI ===
Total transaksi hari ini: {$totalTransactions} transaksi
Total pendapatan: Rp " . number_format($totalRevenue, 0, ',', '.') . "
Keranjang aktif: " . count($cartItems) . " item senilai Rp " . number_format($cartTotal, 0, ',', '.') . "

=== RIWAYAT TRANSAKSI TERBARU ===
" . ($trxSummary ?: "Belum ada transaksi.") . "

=== DAFTAR PRODUK TERSEDIA ===
" . implode(', ', array_map(fn($p) => "{$p['name']} (Rp ".number_format($p['price'],0,',','.').", {$p['category']})", $products));

        // Kirim request ke Anthropic API menggunakan cURL
        $payload = json_encode([
            'model'      => 'claude-sonnet-4-20250514', // Model Claude yang digunakan
            'max_tokens' => 1000,                        // Batas panjang respons
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage]
            ]
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,              // Kembalikan response sebagai string
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,                 // Header autentikasi Anthropic
                'anthropic-version: 2023-06-01'          // Versi API yang digunakan
            ],
            CURLOPT_TIMEOUT        => 30,                // Timeout 30 detik
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Kode HTTP response (200 = sukses)
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            // Jika API gagal, kembalikan pesan error yang informatif
            $errorData = json_decode($response, true);
            $errorMsg  = $errorData['error']['message'] ?? 'Gagal terhubung ke AI.';
            echo json_encode(['success' => false, 'reply' => '❌ ' . $errorMsg]);
            exit;
        }

        // Parse respons JSON dari Anthropic API
        $data  = json_decode($response, true);
        $reply = $data['content'][0]['text'] ?? 'Maaf, tidak ada respons dari AI.';
        echo json_encode(['success' => true, 'reply' => $reply]);
        exit;
    }

    exit; // Akhiri semua AJAX request
}

// ----------------------------------------------------------
// KALKULASI DATA UNTUK TAMPILAN AWAL
// Dihitung sekali saat halaman dimuat pertama kali
// ----------------------------------------------------------
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['qty'];
}
$cartCount = array_sum(array_column($_SESSION['cart'], 'qty')); // Total jumlah item
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WarungPOS — Sistem Kasir Modern</title>
    <!-- Google Fonts: Space Mono untuk angka/kode, Syne untuk judul/UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===================================================
           CSS VARIABLES — Tema warna aplikasi
           Ubah nilai di sini untuk mengganti tema warna
           =================================================== */
        :root {
            --bg:           #0d0d0d; /* Latar belakang utama (hitam) */
            --surface:      #161616; /* Permukaan panel/kartu */
            --surface2:     #1f1f1f; /* Permukaan lebih terang untuk nested element */
            --border:       #2a2a2a; /* Warna garis pembatas */
            --accent:       #f5c842; /* Warna aksen utama (kuning) */
            --accent2:      #e8483c; /* Warna aksen merah (untuk error/hapus) */
            --green:        #3ddc84; /* Warna hijau (untuk sukses/kembalian) */
            --text:         #f0f0f0; /* Warna teks utama */
            --muted:        #888;    /* Warna teks redup (label, placeholder) */
            --font-display: 'Syne', sans-serif;      /* Font untuk judul & tombol */
            --font-mono:    'Space Mono', monospace; /* Font untuk angka & kode */
        }

        /* Reset: hilangkan margin/padding bawaan browser */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-display);
            min-height: 100vh;
            overflow-x: hidden; /* Cegah scroll horizontal */
        }

        /* ===================================================
           HEADER — Navigasi atas (logo, tab menu, jam)
           Position sticky agar tetap terlihat saat scroll
           =================================================== */
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; /* Header mengikuti scroll */
            top: 0;
            z-index: 100;     /* Di atas konten lain */
        }
        .logo { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .logo span { color: var(--accent); } /* "POS" berwarna kuning */
        .header-info { font-family: var(--font-mono); font-size: 11px; color: var(--muted); }

        /* Tab navigasi: Kasir / AI Asisten / Riwayat */
        .nav-tabs { display: flex; gap: 4px; }
        .nav-tab {
            padding: 6px 16px;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            font-family: var(--font-display);
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            background: transparent;
            color: var(--muted);
        }
        .nav-tab.active { background: var(--accent); color: #000; border-color: var(--accent); }
        .nav-tab:hover:not(.active) { border-color: var(--border); color: var(--text); }

        /* ===================================================
           LAYOUT UTAMA — Dua kolom: produk (kiri) + keranjang (kanan)
           =================================================== */
        .app-layout {
            display: grid;
            grid-template-columns: 1fr 360px; /* Produk fleksibel, keranjang 360px */
            height: calc(100vh - 60px);        /* Isi sisa tinggi layar */
        }

        /* ===================================================
           PANEL KIRI — Daftar produk
           =================================================== */
        .products-panel { overflow-y: auto; padding: 20px; }
        /* Custom scrollbar agar tidak mengganggu tampilan */
        .products-panel::-webkit-scrollbar { width: 4px; }
        .products-panel::-webkit-scrollbar-track { background: transparent; }
        .products-panel::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

        /* Kolom pencarian produk */
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input {
            flex: 1;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 16px;
            color: var(--text);
            font-family: var(--font-display);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus { border-color: var(--accent); } /* Highlight saat aktif */
        .search-input::placeholder { color: var(--muted); }

        /* Filter kategori: Semua / Makanan / Minuman / Snack */
        .filter-chips { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .chip {
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--muted);
            background: transparent;
        }
        .chip.active { background: var(--accent); color: #000; border-color: var(--accent); }

        /* Grid kartu produk — auto-fill agar responsif */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        /* Kartu satu produk */
        .product-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        /* Efek overlay kuning saat hover */
        .product-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--accent);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .product-card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .product-card:hover::before { opacity: 0.04; }
        .product-card:active { transform: scale(0.97); } /* Efek tekan */
        .product-emoji  { font-size: 32px; margin-bottom: 10px; display: block; }
        .product-name   { font-size: 13px; font-weight: 700; margin-bottom: 6px; line-height: 1.3; }
        .product-price  { font-family: var(--font-mono); font-size: 13px; color: var(--accent); font-weight: 700; }
        .product-cat    { font-size: 10px; color: var(--muted); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Indikator produk yang sudah ada di keranjang */
        .product-card.in-cart { border-color: var(--green); }
        .in-cart-badge {
            position: absolute;
            top: 8px; right: 8px;
            background: var(--green);
            color: #000;
            font-size: 10px; font-weight: 700;
            font-family: var(--font-mono);
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* ===================================================
           PANEL KANAN — Keranjang belanja
           =================================================== */
        .cart-panel {
            background: var(--surface);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column; /* Stack: header > items > summary */
            overflow: hidden;
        }

        /* Judul keranjang + badge jumlah item + tombol kosongkan */
        .cart-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cart-title { font-size: 14px; font-weight: 700; }
        .cart-count { background: var(--accent); color: #000; font-family: var(--font-mono); font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
        .btn-clear { background: transparent; border: 1px solid var(--border); color: var(--muted); font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer; font-family: var(--font-display); transition: all 0.2s; }
        .btn-clear:hover { border-color: var(--accent2); color: var(--accent2); }

        /* Area list item keranjang dengan scroll */
        .cart-items { flex: 1; overflow-y: auto; padding: 12px; }
        .cart-items::-webkit-scrollbar { width: 3px; }
        .cart-items::-webkit-scrollbar-thumb { background: var(--border); }

        /* Tampilan saat keranjang kosong */
        .cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--muted); gap: 10px; }
        .cart-empty-icon { font-size: 48px; opacity: 0.3; }
        .cart-empty-text { font-size: 13px; }

        /* Satu baris item di keranjang */
        .cart-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            display: grid;
            grid-template-columns: auto 1fr auto; /* emoji | info | kontrol */
            gap: 10px;
            align-items: center;
            animation: slideIn 0.2s ease; /* Animasi saat item baru ditambah */
        }
        @keyframes slideIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
        .cart-item-emoji { font-size: 24px; }
        .cart-item-name  { font-size: 12px; font-weight: 700; margin-bottom: 3px; }
        .cart-item-price { font-family: var(--font-mono); font-size: 11px; color: var(--accent); }

        /* Tombol +/- qty dan tombol hapus */
        .cart-item-controls { display: flex; align-items: center; gap: 8px; }
        .qty-btn { width: 26px; height: 26px; border-radius: 6px; border: 1px solid var(--border); background: transparent; color: var(--text); cursor: pointer; font-size: 14px; font-weight: 700; transition: all 0.15s; display: flex; align-items: center; justify-content: center; }
        .qty-btn:hover { background: var(--accent); color: #000; border-color: var(--accent); }
        .qty-display { font-family: var(--font-mono); font-size: 13px; font-weight: 700; min-width: 20px; text-align: center; }
        .delete-btn { background: transparent; border: none; color: var(--muted); cursor: pointer; font-size: 14px; padding: 2px; transition: color 0.2s; }
        .delete-btn:hover { color: var(--accent2); }

        /* ===================================================
           RINGKASAN KERANJANG — Total, pembayaran, checkout
           =================================================== */
        .cart-summary { padding: 16px 20px; border-top: 1px solid var(--border); background: var(--bg); }
        .summary-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-bottom: 6px; font-family: var(--font-mono); }
        .summary-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; margin: 12px 0; padding: 10px 0; border-top: 1px solid var(--border); }
        .total-amount { color: var(--accent); font-family: var(--font-mono); }

        /* Input jumlah pembayaran & tombol nominal cepat */
        .payment-section { margin-top: 10px; }
        .payment-label { font-size: 11px; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .payment-input { width: 100%; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; color: var(--text); font-family: var(--font-mono); font-size: 16px; font-weight: 700; outline: none; transition: border-color 0.2s; margin-bottom: 8px; }
        .payment-input:focus { border-color: var(--accent); }

        /* 3 tombol nominal bayar cepat (auto-update sesuai total) */
        .quick-pay { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 12px; }
        .quick-pay-btn { background: var(--surface2); border: 1px solid var(--border); color: var(--text); font-size: 11px; font-family: var(--font-mono); padding: 6px 4px; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 700; }
        .quick-pay-btn:hover { border-color: var(--accent); color: var(--accent); }

        /* Tombol Bayar Sekarang */
        .btn-checkout { width: 100%; background: var(--accent); color: #000; border: none; border-radius: 10px; padding: 14px; font-size: 15px; font-weight: 800; font-family: var(--font-display); cursor: pointer; transition: all 0.2s; letter-spacing: -0.3px; }
        .btn-checkout:hover { background: #ffd45e; transform: translateY(-1px); }
        .btn-checkout:active { transform: scale(0.98); }
        .btn-checkout:disabled { background: var(--border); color: var(--muted); cursor: not-allowed; transform: none; } /* Nonaktif jika keranjang kosong */

        /* ===================================================
           PANEL AI ASISTEN — Chat dengan AI
           =================================================== */
        #ai-panel { display: none; padding: 20px; height: calc(100vh - 60px); overflow-y: auto; flex-direction: column; }
        #ai-panel.active { display: flex; }
        .ai-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .ai-avatar { width: 44px; height: 44px; background: linear-gradient(135deg, var(--accent), #ff8c42); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .ai-title { font-size: 18px; font-weight: 800; }
        .ai-subtitle { font-size: 12px; color: var(--muted); }

        /* 4 tombol pertanyaan cepat untuk AI */
        .ai-suggestions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .suggestion-btn { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 12px; cursor: pointer; text-align: left; transition: all 0.2s; font-family: var(--font-display); color: var(--text); }
        .suggestion-btn:hover { border-color: var(--accent); }
        .suggestion-icon { font-size: 20px; margin-bottom: 6px; display: block; }
        .suggestion-text { font-size: 12px; font-weight: 600; line-height: 1.4; }

        /* Kotak percakapan chat */
        .chat-container { flex: 1; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; }
        .chat-messages { flex: 1; padding: 16px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; min-height: 300px; max-height: 400px; }
        .chat-messages::-webkit-scrollbar { width: 3px; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--border); }

        /* Satu pesan (user atau AI) */
        .msg { display: flex; gap: 10px; animation: fadeUp 0.3s ease; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .msg.user { flex-direction: row-reverse; } /* Pesan user di kanan */
        .msg-avatar { width: 32px; height: 32px; border-radius: 8px; background: var(--surface2); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
        .msg.user .msg-avatar { background: var(--accent); }
        .msg-bubble { max-width: 75%; background: var(--surface2); border-radius: 12px 12px 12px 4px; padding: 10px 14px; font-size: 13px; line-height: 1.5; }
        .msg.user .msg-bubble { background: var(--accent); color: #000; border-radius: 12px 12px 4px 12px; font-weight: 600; }

        /* Animasi titik-titik loading saat AI sedang membalas */
        .typing-indicator { display: flex; gap: 4px; padding: 14px; }
        .dot { width: 6px; height: 6px; background: var(--muted); border-radius: 50%; animation: bounce 1.2s infinite; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-6px); } }

        /* Input teks + tombol kirim pesan */
        .chat-input-area { padding: 12px; border-top: 1px solid var(--border); display: flex; gap: 8px; }
        .chat-input { flex: 1; background: var(--surface2); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; color: var(--text); font-family: var(--font-display); font-size: 13px; outline: none; transition: border-color 0.2s; }
        .chat-input:focus { border-color: var(--accent); }
        .chat-input::placeholder { color: var(--muted); }
        .send-btn { background: var(--accent); border: none; border-radius: 8px; width: 40px; height: 40px; cursor: pointer; font-size: 16px; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .send-btn:hover { background: #ffd45e; }

        /* ===================================================
           PANEL RIWAYAT TRANSAKSI
           =================================================== */
        #history-panel { display: none; padding: 20px; height: calc(100vh - 60px); overflow-y: auto; }
        #history-panel.active { display: block; }
        .history-title { font-size: 20px; font-weight: 800; margin-bottom: 20px; }

        /* Kartu satu transaksi */
        .transaction-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        .trx-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .trx-id     { font-family: var(--font-mono); font-size: 12px; color: var(--accent); font-weight: 700; }
        .trx-date   { font-size: 11px; color: var(--muted); font-family: var(--font-mono); }
        .trx-items  { font-size: 12px; color: var(--muted); margin-bottom: 8px; }
        .trx-footer { display: flex; justify-content: space-between; align-items: center; }
        .trx-total  { font-family: var(--font-mono); font-size: 15px; font-weight: 700; color: var(--green); }
        .trx-badge  { background: var(--green); color: #000; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }

        /* ===================================================
           MODAL STRUK PEMBAYARAN — Muncul setelah checkout berhasil
           =================================================== */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 200; display: none; align-items: center; justify-content: center; animation: fadeIn 0.2s; }
        .modal-overlay.active { display: flex; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 32px; width: 420px; text-align: center; animation: scaleIn 0.25s ease; }
        @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-icon  { font-size: 48px; margin-bottom: 16px; }
        .modal-title { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        .modal-sub   { font-size: 13px; color: var(--muted); margin-bottom: 20px; }

        /* Kotak struk bergaya mesin kasir (border dashed) */
        .receipt-box { background: var(--surface2); border: 1px dashed var(--border); border-radius: 10px; padding: 16px; text-align: left; margin-bottom: 20px; font-family: var(--font-mono); font-size: 12px; }
        .receipt-row   { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .receipt-total { border-top: 1px dashed var(--border); padding-top: 8px; margin-top: 8px; font-size: 14px; font-weight: 700; color: var(--accent); }

        /* Tampilan besar untuk angka kembalian */
        .change-display { font-size: 28px; font-weight: 800; color: var(--green); font-family: var(--font-mono); margin: 12px 0; }
        .btn-modal { background: var(--accent); color: #000; border: none; padding: 12px 32px; border-radius: 10px; font-size: 14px; font-weight: 700; font-family: var(--font-display); cursor: pointer; transition: all 0.2s; }
        .btn-modal:hover { background: #ffd45e; }

        /* Scrollbar untuk panel AI dan Riwayat */
        #ai-panel::-webkit-scrollbar, #history-panel::-webkit-scrollbar { width: 4px; }
        #ai-panel::-webkit-scrollbar-thumb, #history-panel::-webkit-scrollbar-thumb { background: var(--border); }
    </style>
</head>
<body>

<!-- ============================================================
     HEADER — Logo, navigasi tab, dan jam digital
     ============================================================ -->
<header>
    <div class="logo">Warung<span>POS</span></div>

    <!-- Tab navigasi antar panel -->
    <div class="nav-tabs">
        <button class="nav-tab active" onclick="switchTab('kasir', this)">🏪 Kasir</button>
        <button class="nav-tab" onclick="switchTab('ai', this)">🤖 AI Asisten</button>
        <button class="nav-tab" onclick="switchTab('history', this)">📋 Riwayat</button>
    </div>

    <!-- Jam digital yang update tiap detik -->
    <div class="header-info" id="clock"></div>
</header>

<!-- ============================================================
     PANEL UTAMA KASIR — Produk (kiri) + Keranjang (kanan)
     ============================================================ -->
<div id="main-panel" class="app-layout">

    <!-- ======================================================
         PANEL KIRI — Daftar Produk
         ====================================================== -->
    <div class="products-panel">

        <!-- Input pencarian produk (filter real-time tanpa reload) -->
        <div class="search-bar">
            <input type="text" class="search-input"
                   placeholder="🔍  Cari produk..."
                   id="searchInput"
                   oninput="filterProducts()"> <!-- Dipanggil tiap ketuk keyboard -->
        </div>

        <!-- Tombol filter berdasarkan kategori -->
        <div class="filter-chips">
            <button class="chip active" onclick="filterCategory('Semua', this)">Semua</button>
            <button class="chip" onclick="filterCategory('Makanan', this)">🍽️ Makanan</button>
            <button class="chip" onclick="filterCategory('Minuman', this)">🥤 Minuman</button>
            <button class="chip" onclick="filterCategory('Snack', this)">🍿 Snack</button>
        </div>

        <!-- Grid kartu produk — dirender oleh PHP dari array $products -->
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $p): ?>
            <!-- data-id, data-name, data-category dipakai oleh JS untuk filter -->
            <div class="product-card <?= isset($_SESSION['cart'][$p['id']]) ? 'in-cart' : '' ?>"
                 data-id="<?= $p['id'] ?>"
                 data-name="<?= strtolower($p['name']) ?>"
                 data-category="<?= $p['category'] ?>"
                 onclick="addToCart(<?= $p['id'] ?>)"> <!-- Klik = tambah ke keranjang -->

                <!-- Badge qty jika produk sudah ada di keranjang -->
                <?php if (isset($_SESSION['cart'][$p['id']])): ?>
                <div class="in-cart-badge">×<?= $_SESSION['cart'][$p['id']]['qty'] ?></div>
                <?php endif; ?>

                <span class="product-emoji"><?= $p['emoji'] ?></span>
                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="product-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>
                <div class="product-cat"><?= $p['category'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ======================================================
         PANEL KANAN — Keranjang Belanja
         ====================================================== -->
    <div class="cart-panel">

        <!-- Header keranjang: judul + jumlah item + tombol kosongkan -->
        <div class="cart-header">
            <div class="cart-title">🛒 Keranjang</div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="cart-count" id="cartCount"><?= $cartCount ?></span>
                <button class="btn-clear" onclick="clearCart()">Kosongkan</button>
            </div>
        </div>

        <!-- Daftar item dalam keranjang (diupdate JS tanpa reload) -->
        <div class="cart-items" id="cartItems">
            <?php if (empty($_SESSION['cart'])): ?>
            <!-- Tampilan keranjang kosong -->
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                <div class="cart-empty-text">Keranjang kosong</div>
            </div>
            <?php else: ?>
                <!-- Render setiap item dari session keranjang -->
                <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="cart-item" id="cart-item-<?= $item['id'] ?>">
                    <span class="cart-item-emoji"><?= $item['emoji'] ?></span>
                    <div>
                        <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="cart-item-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></div>
                    </div>
                    <!-- Kontrol qty: hapus, kurang, tampil, tambah -->
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                        <button class="delete-btn" onclick="deleteItem(<?= $item['id'] ?>)">✕</button>
                        <div class="cart-item-controls">
                            <button class="qty-btn" onclick="removeItem(<?= $item['id'] ?>)">−</button>
                            <span class="qty-display"><?= $item['qty'] ?></span>
                            <button class="qty-btn" onclick="addToCart(<?= $item['id'] ?>)">+</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Ringkasan total + input pembayaran + tombol checkout -->
        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotalDisplay">Rp <?= number_format($cartTotal, 0, ',', '.') ?></span>
            </div>
            <div class="summary-row">
                <span>Items</span>
                <span id="itemsDisplay"><?= $cartCount ?> item</span>
            </div>
            <div class="summary-total">
                <span>TOTAL</span>
                <span class="total-amount" id="totalDisplay">Rp <?= number_format($cartTotal, 0, ',', '.') ?></span>
            </div>

            <div class="payment-section">
                <div class="payment-label">Pembayaran</div>
                <!-- Input jumlah uang dari pelanggan -->
                <input type="number" class="payment-input" id="paymentInput"
                       placeholder="0" oninput="updateChange()"> <!-- Auto-hitung kembalian -->

                <!-- 3 tombol nominal cepat (auto-update sesuai total belanja) -->
                <div class="quick-pay" id="quickPay">
                    <button class="quick-pay-btn" onclick="setPayment(50000)">50rb</button>
                    <button class="quick-pay-btn" onclick="setPayment(100000)">100rb</button>
                    <button class="quick-pay-btn" onclick="setPayment(150000)">150rb</button>
                </div>

                <!-- Baris kembalian (muncul jika nominal bayar diisi) -->
                <div class="summary-row" id="changeRow" style="display:none;">
                    <span>Kembalian</span>
                    <span id="changeDisplay" style="color: var(--green); font-weight:700;"></span>
                </div>

                <!-- Tombol checkout (disabled jika keranjang kosong) -->
                <button class="btn-checkout" id="checkoutBtn"
                        onclick="checkout()"
                        <?= empty($_SESSION['cart']) ? 'disabled' : '' ?>>
                    💳 Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     PANEL AI ASISTEN — Chat dengan Claude AI
     Komunikasi ke API dilakukan via AJAX ke backend PHP (aman)
     ============================================================ -->
<div id="ai-panel">
    <div class="ai-header">
        <div class="ai-avatar">🤖</div>
        <div>
            <div class="ai-title">AI Asisten Kasir</div>
            <div class="ai-subtitle">Didukung oleh Claude · Tanya apapun tentang bisnis kamu</div>
        </div>
    </div>

    <!-- Tombol pertanyaan cepat yang bisa diklik langsung -->
    <div class="ai-suggestions">
        <button class="suggestion-btn" onclick="askAI('Analisa penjualan hari ini dan berikan rekomendasi')">
            <span class="suggestion-icon">📊</span>
            <div class="suggestion-text">Analisa penjualan hari ini</div>
        </button>
        <button class="suggestion-btn" onclick="askAI('Produk apa yang paling laku dan mengapa?')">
            <span class="suggestion-icon">🏆</span>
            <div class="suggestion-text">Produk terlaris</div>
        </button>
        <button class="suggestion-btn" onclick="askAI('Berikan strategi promosi untuk meningkatkan penjualan')">
            <span class="suggestion-icon">💡</span>
            <div class="suggestion-text">Strategi promosi</div>
        </button>
        <button class="suggestion-btn" onclick="askAI('Hitung keuntungan bersih dan margin profit hari ini')">
            <span class="suggestion-icon">💰</span>
            <div class="suggestion-text">Hitung keuntungan</div>
        </button>
    </div>

    <!-- Area chat: riwayat pesan + input baru -->
    <div class="chat-container">
        <!-- Daftar pesan (AI dan user) — scroll otomatis ke bawah -->
        <div class="chat-messages" id="chatMessages">
            <!-- Pesan sambutan awal dari AI -->
            <div class="msg">
                <div class="msg-avatar">🤖</div>
                <div class="msg-bubble">
                    Halo! Saya <strong>WakiPOS</strong>, AI Asisten untuk warung kamu 👋<br><br>
                    Saya bisa membantu menganalisa penjualan, memberikan rekomendasi strategi bisnis, menghitung keuntungan, atau menjawab pertanyaan seputar operasional. Ada yang bisa saya bantu?
                </div>
            </div>
        </div>
        <!-- Input pesan baru + tombol kirim -->
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="chatInput"
                   placeholder="Ketik pertanyaan kamu..."
                   onkeydown="if(event.key==='Enter') sendChat()"> <!-- Enter = kirim -->
            <button class="send-btn" onclick="sendChat()">➤</button>
        </div>
    </div>
</div>

<!-- ============================================================
     PANEL RIWAYAT TRANSAKSI — Daftar semua transaksi hari ini
     Dirender dari $_SESSION['transactions'] oleh PHP
     ============================================================ -->
<div id="history-panel">
    <div class="history-title">📋 Riwayat Transaksi</div>
    <?php if (empty($_SESSION['transactions'])): ?>
    <!-- Tampilan jika belum ada transaksi -->
    <div style="text-align:center; color:var(--muted); padding:60px 0;">
        <div style="font-size:48px;margin-bottom:12px;">📭</div>
        <div>Belum ada transaksi hari ini</div>
    </div>
    <?php else: ?>
        <!-- Tampilkan transaksi terbaru di atas (array_reverse) -->
        <?php foreach (array_reverse($_SESSION['transactions']) as $trx): ?>
        <div class="transaction-card">
            <div class="trx-header">
                <span class="trx-id"><?= $trx['id'] ?></span>       <!-- ID unik transaksi -->
                <span class="trx-date"><?= $trx['date'] ?></span>   <!-- Tanggal & jam -->
            </div>
            <div class="trx-items">
                <?php
                // Gabungkan semua item menjadi satu baris teks
                $itemNames = array_map(fn($i) => "{$i['name']} ×{$i['qty']}", $trx['items']);
                echo implode(', ', $itemNames);
                ?>
            </div>
            <div class="trx-footer">
                <span class="trx-total">Rp <?= number_format($trx['total'], 0, ',', '.') ?></span>
                <span class="trx-badge">✓ LUNAS</span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================================
     MODAL STRUK — Muncul setelah pembayaran berhasil
     Konten diisi oleh JavaScript fungsi showReceipt()
     ============================================================ -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal">
        <div class="modal-icon">✅</div>
        <div class="modal-title">Transaksi Berhasil!</div>
        <div class="modal-sub">Terima kasih telah berbelanja</div>
        <div class="receipt-box" id="receiptContent"></div> <!-- Diisi JS -->
        <div style="margin-bottom:16px;">
            <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">KEMBALIAN</div>
            <div class="change-display" id="changeModal"></div> <!-- Angka kembalian besar -->
        </div>
        <button class="btn-modal" onclick="closeModal()">Transaksi Baru 🎉</button>
    </div>
</div>

<script>
// ============================================================
// DATA PRODUK — Dikirim dari PHP ke JavaScript sebagai JSON
// Digunakan untuk filter kategori di sisi klien (tanpa reload)
// ============================================================
const products = <?= json_encode($products) ?>;

// ============================================================
// JAM DIGITAL — Update setiap detik
// ============================================================
function updateClock() {
    const now = new Date();
    const d = now.toLocaleDateString('id-ID', {weekday:'short', day:'numeric', month:'short'});
    const t = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
    document.getElementById('clock').textContent = `${d} • ${t}`;
}
updateClock();
setInterval(updateClock, 1000); // Panggil setiap 1000ms (1 detik)

// ============================================================
// NAVIGASI TAB — Beralih antar panel (Kasir / AI / Riwayat)
// ============================================================
function switchTab(tab, el) {
    // Hapus class active dari semua tab lalu tambahkan ke yang diklik
    document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');

    // Sembunyikan semua panel
    document.getElementById('main-panel').style.display = 'none';
    document.getElementById('ai-panel').classList.remove('active');
    document.getElementById('history-panel').classList.remove('active');

    // Tampilkan panel yang dipilih
    if (tab === 'kasir') {
        document.getElementById('main-panel').style.display = 'grid';
    } else if (tab === 'ai') {
        document.getElementById('ai-panel').classList.add('active');
    } else if (tab === 'history') {
        document.getElementById('history-panel').classList.add('active');
    }
}

// ============================================================
// FILTER PRODUK — Kombinasi filter kategori + pencarian teks
// Bekerja di sisi klien (tanpa request ke server)
// ============================================================
let currentCategory = 'Semua'; // Kategori aktif saat ini

function filterCategory(cat, el) {
    currentCategory = cat;
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    filterProducts(); // Terapkan filter
}

function filterProducts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const name    = card.dataset.name;     // data-name dari PHP
        const cat     = card.dataset.category; // data-category dari PHP
        const matchSearch = name.includes(q);  // Cocokkan dengan kata kunci
        const matchCat    = currentCategory === 'Semua' || cat === currentCategory;
        // Tampilkan hanya jika KEDUANYA cocok
        card.style.display = (matchSearch && matchCat) ? 'block' : 'none';
    });
}

// ============================================================
// OPERASI KERANJANG — Semua request dikirim sebagai AJAX POST
// Response JSON digunakan untuk update tampilan tanpa reload
// ============================================================

// Tambah produk ke keranjang (atau tambah qty jika sudah ada)
function addToCart(productId) {
    fetch('', { // '' = request ke file ini sendiri (self-referencing)
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=add_to_cart&product_id=${productId}`
    })
    .then(r => r.json())
    .then(data => { if (data.success) updateCart(data.cart); });
}

// Kurangi qty satu item (hapus jika qty = 1)
function removeItem(productId) {
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=remove_from_cart&product_id=${productId}`
    })
    .then(r => r.json())
    .then(data => { if (data.success) updateCart(data.cart); });
}

// Hapus item langsung dari keranjang (tanpa memandang qty)
function deleteItem(productId) {
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=delete_item&product_id=${productId}`
    })
    .then(r => r.json())
    .then(data => { if (data.success) updateCart(data.cart); });
}

// Kosongkan seluruh keranjang (dengan konfirmasi)
function clearCart() {
    if (!confirm('Kosongkan keranjang?')) return;
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=clear_cart'
    })
    .then(r => r.json())
    .then(data => { if (data.success) updateCart({}); });
}

// ============================================================
// UPDATE TAMPILAN KERANJANG
// Dipanggil setelah setiap operasi keranjang berhasil.
// Merender ulang seluruh UI keranjang dari data terbaru.
// ============================================================
function formatRp(n) {
    // Format angka ke Rupiah: 25000 → "Rp 25.000"
    return 'Rp ' + parseInt(n).toLocaleString('id-ID');
}

function updateCart(cart) {
    // --- Update badge dan border kartu produk ---
    document.querySelectorAll('.product-card').forEach(card => {
        const id     = parseInt(card.dataset.id);
        const inCart = cart[id]; // undefined jika tidak ada di keranjang
        card.classList.toggle('in-cart', !!inCart); // Toggle border hijau
        let badge = card.querySelector('.in-cart-badge');
        if (inCart) {
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'in-cart-badge';
                card.prepend(badge);
            }
            badge.textContent = '×' + inCart.qty;
        } else {
            if (badge) badge.remove();
        }
    });

    // --- Render ulang daftar item keranjang ---
    const cartItemsEl = document.getElementById('cartItems');
    const items = Object.values(cart); // Ubah object ke array

    if (items.length === 0) {
        // Tampilkan placeholder keranjang kosong
        cartItemsEl.innerHTML = '<div class="cart-empty"><div class="cart-empty-icon">🛒</div><div class="cart-empty-text">Keranjang kosong</div></div>';
        document.getElementById('checkoutBtn').disabled = true;
    } else {
        // Render setiap item sebagai HTML string
        let html = '';
        items.forEach(item => {
            html += `
            <div class="cart-item" id="cart-item-${item.id}">
                <span class="cart-item-emoji">${item.emoji}</span>
                <div>
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatRp(item.price)}</div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                    <button class="delete-btn" onclick="deleteItem(${item.id})">✕</button>
                    <div class="cart-item-controls">
                        <button class="qty-btn" onclick="removeItem(${item.id})">−</button>
                        <span class="qty-display">${item.qty}</span>
                        <button class="qty-btn" onclick="addToCart(${item.id})">+</button>
                    </div>
                </div>
            </div>`;
        });
        cartItemsEl.innerHTML = html;
        document.getElementById('checkoutBtn').disabled = false;
    }

    // --- Update angka total dan jumlah item ---
    const total = items.reduce((s, i) => s + i.price * i.qty, 0);
    const count = items.reduce((s, i) => s + i.qty, 0);
    document.getElementById('subtotalDisplay').textContent = formatRp(total);
    document.getElementById('totalDisplay').textContent    = formatRp(total);
    document.getElementById('cartCount').textContent       = count;
    document.getElementById('itemsDisplay').textContent    = count + ' item';

    updateChange();       // Recalculate kembalian
    updateQuickPay(total); // Update nominal tombol cepat
}

// Update label tombol pembayaran cepat berdasarkan total
function updateQuickPay(total) {
    if (!total) return;
    // Hitung 3 nominal yang relevan: pas, bulat ke atas, satu tingkat lebih
    const amounts = [
        Math.ceil(total / 50000) * 50000,
        Math.ceil(total / 100000) * 100000,
        Math.ceil(total / 100000) * 100000 + 100000
    ];
    const btns = document.querySelectorAll('.quick-pay-btn');
    amounts.forEach((amt, i) => {
        if (btns[i]) {
            btns[i].textContent = (amt >= 1000000 ? (amt/1000000).toFixed(1)+'jt' : (amt/1000)+'rb');
            btns[i].onclick = () => setPayment(amt);
        }
    });
}

// Isi input pembayaran dengan nominal yang dipilih
function setPayment(amount) {
    document.getElementById('paymentInput').value = amount;
    updateChange();
}

// Hitung dan tampilkan kembalian secara real-time
function updateChange() {
    const totalText = document.getElementById('totalDisplay').textContent;
    const total     = parseInt(totalText.replace(/[^0-9]/g, '')); // Ambil angka saja
    const payment   = parseInt(document.getElementById('paymentInput').value) || 0;
    const change    = payment - total;
    const changeRow = document.getElementById('changeRow');
    const changeDisplay = document.getElementById('changeDisplay');

    if (payment > 0 && total > 0) {
        changeRow.style.display = 'flex';
        if (change >= 0) {
            changeDisplay.textContent  = formatRp(change);
            changeDisplay.style.color  = 'var(--green)'; // Hijau = cukup/lebih
        } else {
            changeDisplay.textContent = '-' + formatRp(Math.abs(change));
            changeDisplay.style.color = 'var(--accent2)'; // Merah = kurang
        }
    } else {
        changeRow.style.display = 'none'; // Sembunyikan jika belum ada input
    }
}

// ============================================================
// PROSES CHECKOUT / PEMBAYARAN
// ============================================================
function checkout() {
    const payment = parseInt(document.getElementById('paymentInput').value);
    if (!payment || payment <= 0) {
        alert('Masukkan jumlah pembayaran!');
        return;
    }
    // Kirim ke backend PHP untuk diproses dan disimpan ke session
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=checkout&payment=${payment}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showReceipt(data.transaction); // Tampilkan modal struk
            updateCart({});                // Kosongkan tampilan keranjang
            document.getElementById('paymentInput').value = '';
            document.getElementById('changeRow').style.display = 'none';
        } else {
            alert(data.message); // Tampilkan pesan error (misal: bayar kurang)
        }
    });
}

// Tampilkan modal struk dengan data transaksi yang baru selesai
function showReceipt(trx) {
    let html = `<div style="text-align:center;margin-bottom:10px;font-weight:700;">🧾 STRUK PEMBAYARAN</div>`;
    html += `<div class="receipt-row"><span>${trx.id}</span><span>${trx.date}</span></div>`;
    html += `<hr style="border-color:var(--border);margin:8px 0;">`;
    // List item yang dibeli
    Object.values(trx.items).forEach(item => {
        html += `<div class="receipt-row">
            <span>${item.name} ×${item.qty}</span>
            <span>${formatRp(item.price * item.qty)}</span>
        </div>`;
    });
    html += `<div class="receipt-row receipt-total"><span>TOTAL</span><span>${formatRp(trx.total)}</span></div>`;
    html += `<div class="receipt-row"><span>Bayar</span><span>${formatRp(trx.payment)}</span></div>`;

    document.getElementById('receiptContent').innerHTML = html;
    document.getElementById('changeModal').textContent  = formatRp(trx.change);
    document.getElementById('checkoutModal').classList.add('active'); // Tampilkan modal
}

// Tutup modal dan reload halaman (supaya riwayat di-update dari session)
function closeModal() {
    document.getElementById('checkoutModal').classList.remove('active');
    location.reload();
}

// ============================================================
// AI CHAT — Kirim pesan ke backend PHP, lalu tampilkan balasan
// Backend PHP yang meneruskan ke Anthropic API (lebih aman)
// ============================================================

// Dipanggil saat user tekan Enter atau klik tombol kirim
async function sendChat() {
    const input = document.getElementById('chatInput');
    const msg   = input.value.trim();
    if (!msg) return;
    input.value = '';
    await askAI(msg);
}

// Proses pengiriman pesan dan tampilkan balasan AI
async function askAI(message) {
    const chatMessages = document.getElementById('chatMessages');

    // Tambahkan pesan user ke tampilan chat
    chatMessages.innerHTML += `
        <div class="msg user">
            <div class="msg-avatar">👤</div>
            <div class="msg-bubble">${escapeHtml(message)}</div>
        </div>`;

    // Tampilkan animasi "sedang mengetik" sambil menunggu respons
    const typingId = 'typing-' + Date.now();
    chatMessages.innerHTML += `
        <div class="msg" id="${typingId}">
            <div class="msg-avatar">🤖</div>
            <div class="msg-bubble">
                <div class="typing-indicator">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            </div>
        </div>`;
    chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll ke bawah

    try {
        // Kirim pesan ke backend PHP (BUKAN langsung ke Anthropic API)
        // Ini lebih aman karena API key disimpan di server, bukan di browser
        const formData = new URLSearchParams();
        formData.append('action', 'ai_chat');
        formData.append('message', message);

        const response = await fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        });

        const data = await response.json();
        document.getElementById(typingId).remove(); // Hapus animasi loading

        if (data.success) {
            // Tampilkan balasan AI
            chatMessages.innerHTML += `
                <div class="msg">
                    <div class="msg-avatar">🤖</div>
                    <div class="msg-bubble">${formatAIResponse(data.reply)}</div>
                </div>`;
        } else {
            // Tampilkan pesan error dari backend
            chatMessages.innerHTML += `
                <div class="msg">
                    <div class="msg-avatar">🤖</div>
                    <div class="msg-bubble" style="color:var(--accent2)">
                        ❌ ${escapeHtml(data.reply || 'Gagal mendapat respons AI.')}
                    </div>
                </div>`;
        }
    } catch (err) {
        // Error jaringan atau parsing
        document.getElementById(typingId)?.remove();
        chatMessages.innerHTML += `
            <div class="msg">
                <div class="msg-avatar">🤖</div>
                <div class="msg-bubble" style="color:var(--accent2)">
                    ❌ Koneksi gagal. Periksa server PHP kamu.
                </div>
            </div>`;
    }
    chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll ke bawah setelah respons
}

// Konversi Markdown sederhana dari AI ke HTML
// **bold** → <strong>bold</strong>, newline → <br>
function formatAIResponse(text) {
    return text
        .replace(/&/g, '&amp;')       // Escape HTML terlebih dahulu
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
        .replace(/\n/g, '<br>');       // Newline ke <br>
}

// Escape karakter HTML untuk mencegah XSS injection
function escapeHtml(text) {
    return text
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;');
}

// ============================================================
// INISIALISASI — Tampilkan panel kasir saat pertama kali load
// ============================================================
document.getElementById('main-panel').style.display = 'grid';
</script>

</body>
</html>