<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - <?= NAMA_TOKO ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f0f12;
            --surface: #18181f;
            --surface2: #22222c;
            --border: #2e2e3a;
            --accent: #f0c040;
            --accent2: #e07b30;
            --text: #e8e8f0;
            --text2: #8888a0;
            --danger: #e05050;
            --success: #40c08a;
            --info: #4090e0;
            --radius: 12px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 20px;
            color: var(--accent);
            letter-spacing: -0.5px;
        }
        .logo span { color: var(--text2); font-weight: 400; }
        .header-nav { display: flex; gap: 6px; }
        .nav-btn {
            background: none;
            border: 1px solid var(--border);
            color: var(--text2);
            padding: 7px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-btn:hover, .nav-btn.active {
            background: var(--surface2);
            border-color: var(--accent);
            color: var(--accent);
        }
        .time-display {
            font-family: 'DM Mono', monospace;
            font-size: 13px;
            color: var(--text2);
        }

        /* MAIN LAYOUT */
        .main {
            display: grid;
            grid-template-columns: 1fr 380px;
            flex: 1;
            height: calc(100vh - 60px);
            overflow: hidden;
        }

        /* LEFT PANEL - Products */
        .panel-left {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .search-bar {
            padding: 16px 20px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            gap: 10px;
        }
        .search-input {
            flex: 1;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus { border-color: var(--accent); }
        .search-input::placeholder { color: var(--text2); }
        .category-filter {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text2);
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            outline: none;
            font-family: 'DM Sans', sans-serif;
        }

        /* Product Grid */
        .product-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
        }
        .product-area::-webkit-scrollbar { width: 4px; }
        .product-area::-webkit-scrollbar-track { background: transparent; }
        .product-area::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }
        .product-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .product-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--accent)10, transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .product-card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .product-card:hover::before { opacity: 1; }
        .product-card:active { transform: scale(0.97); }
        .product-card.stok-habis { opacity: 0.4; cursor: not-allowed; }
        .product-code {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: var(--text2);
            margin-bottom: 6px;
        }
        .product-name {
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .product-price {
            font-family: 'DM Mono', monospace;
            font-size: 15px;
            font-weight: 500;
            color: var(--accent);
        }
        .product-stok {
            font-size: 11px;
            color: var(--text2);
            margin-top: 6px;
        }
        .stok-badge {
            display: inline-block;
            background: var(--success)20;
            color: var(--success);
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid var(--success)30;
        }
        .stok-badge.low { background: var(--danger)20; color: var(--danger); border-color: var(--danger)30; }

        /* RIGHT PANEL - Cart */
        .panel-right {
            background: var(--surface);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }
        .cart-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }
        .cart-title {
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-count {
            background: var(--accent);
            color: var(--bg);
            font-size: 12px;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 20px;
            font-family: 'DM Mono', monospace;
        }
        .no-trx {
            font-family: 'DM Mono', monospace;
            font-size: 10px;
            color: var(--text2);
            margin-top: 4px;
        }

        /* Cart Items */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }
        .cart-items::-webkit-scrollbar { width: 3px; }
        .cart-items::-webkit-scrollbar-thumb { background: var(--border); }
        .cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text2);
            gap: 8px;
        }
        .cart-empty-icon { font-size: 40px; opacity: 0.3; }
        .cart-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.2s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .item-info { flex: 1; min-width: 0; }
        .item-name {
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-price { font-family: 'DM Mono', monospace; font-size: 11px; color: var(--text2); }
        .item-subtotal { font-family: 'DM Mono', monospace; font-size: 13px; color: var(--accent); font-weight: 500; }
        .qty-control { display: flex; align-items: center; gap: 6px; }
        .qty-btn {
            width: 26px; height: 26px;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        .qty-btn:hover { border-color: var(--accent); color: var(--accent); }
        .qty-num {
            font-family: 'DM Mono', monospace;
            font-size: 14px;
            min-width: 24px;
            text-align: center;
        }
        .remove-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 16px;
            opacity: 0.5;
            transition: opacity 0.2s;
            padding: 4px;
        }
        .remove-btn:hover { opacity: 1; }

        /* Cart Footer */
        .cart-footer {
            border-top: 1px solid var(--border);
            padding: 16px 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            font-size: 13px;
            color: var(--text2);
        }
        .summary-row.total {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            border-top: 1px solid var(--border);
            margin-top: 6px;
            padding-top: 10px;
        }
        .summary-row.total .val { color: var(--accent); }
        .diskon-input {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'DM Mono', monospace;
            width: 100px;
            text-align: right;
            outline: none;
        }
        .diskon-input:focus { border-color: var(--accent); }
        .bayar-section { margin-top: 14px; }
        .bayar-label { font-size: 12px; color: var(--text2); margin-bottom: 6px; }
        .bayar-input {
            width: 100%;
            background: var(--bg);
            border: 2px solid var(--border);
            color: var(--text);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 18px;
            font-family: 'DM Mono', monospace;
            text-align: right;
            outline: none;
            transition: border-color 0.2s;
            margin-bottom: 8px;
        }
        .bayar-input:focus { border-color: var(--accent); }
        .kembalian-display {
            background: var(--success)15;
            border: 1px solid var(--success)30;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .kembalian-display .label { font-size: 12px; color: var(--success); }
        .kembalian-display .val { font-family: 'DM Mono', monospace; font-size: 16px; color: var(--success); font-weight: 500; }
        .metode-group { display: flex; gap: 6px; margin-bottom: 12px; }
        .metode-btn {
            flex: 1;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text2);
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.2s;
        }
        .metode-btn.active { background: var(--accent)20; border-color: var(--accent); color: var(--accent); }
        .btn-bayar {
            width: 100%;
            background: var(--accent);
            color: var(--bg);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-family: 'Syne', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.5px;
        }
        .btn-bayar:hover { background: #f5d060; transform: translateY(-1px); }
        .btn-bayar:active { transform: scale(0.98); }
        .btn-bayar:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
        .btn-clear {
            width: 100%;
            background: none;
            border: 1px solid var(--danger)40;
            color: var(--danger);
            padding: 9px;
            border-radius: 10px;
            font-size: 13px;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.2s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-clear:hover { background: var(--danger)10; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: #000000c0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            width: 420px;
            max-width: 95vw;
            animation: popIn 0.2s ease;
        }
        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-icon {
            width: 64px; height: 64px;
            background: var(--success)20;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 16px;
        }
        .modal-title {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }
        .modal-subtitle { text-align: center; color: var(--text2); font-size: 13px; margin-bottom: 24px; }
        .struk {
            background: var(--bg);
            border-radius: 12px;
            padding: 16px;
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            line-height: 1.8;
        }
        .struk-header { text-align: center; margin-bottom: 12px; }
        .struk-divider { border: none; border-top: 1px dashed var(--border); margin: 8px 0; }
        .struk-row { display: flex; justify-content: space-between; }
        .struk-total { font-weight: 700; font-size: 14px; color: var(--accent); }
        .modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
        .modal-btn {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-family: 'Syne', sans-serif;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .modal-btn.primary { background: var(--accent); color: var(--bg); }
        .modal-btn.secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .modal-btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13px;
            z-index: 9999;
            animation: toastIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        @keyframes toastIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .toast.success { border-color: var(--success)50; }
        .toast.error { border-color: var(--danger)50; }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="logo"><?= NAMA_TOKO ?> <span>/ Kasir</span></div>
    <nav class="header-nav">
        <a href="index.php" class="nav-btn active">🏪 Kasir</a>
        <a href="pages/produk.php" class="nav-btn">📦 Produk</a>
        <a href="pages/transaksi.php" class="nav-btn">🧾 Transaksi</a>
        <a href="pages/laporan.php" class="nav-btn">📊 Laporan</a>
    </nav>
    <div class="time-display" id="clock"></div>
</header>

<!-- MAIN -->
<div class="main">

    <!-- LEFT: PRODUK -->
    <div class="panel-left">
        <div class="search-bar">
            <input type="text" class="search-input" id="searchInput" placeholder="🔍  Cari produk atau scan barcode..." oninput="filterProduk()">
            <select class="category-filter" id="katFilter" onchange="filterProduk()">
                <option value="">Semua Kategori</option>
                <?php
                $db = getDB();
                $kats = $db->query("SELECT * FROM kategori ORDER BY nama")->fetchAll();
                foreach ($kats as $k): ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="product-area">
            <div class="product-grid" id="productGrid">
                <?php
                $produk = $db->query("SELECT p.*, k.nama as kategori_nama FROM produk p LEFT JOIN kategori k ON p.kategori_id = k.id WHERE p.aktif = 1 ORDER BY p.nama")->fetchAll();
                foreach ($produk as $p):
                    $low = $p['stok'] <= 5;
                    $habis = $p['stok'] <= 0;
                ?>
                <div class="product-card <?= $habis ? 'stok-habis' : '' ?>"
                     data-id="<?= $p['id'] ?>"
                     data-nama="<?= htmlspecialchars($p['nama']) ?>"
                     data-harga="<?= $p['harga_jual'] ?>"
                     data-stok="<?= $p['stok'] ?>"
                     data-kode="<?= htmlspecialchars($p['kode']) ?>"
                     data-kat="<?= $p['kategori_id'] ?>"
                     onclick="tambahKeKeranjang(this)">
                    <div class="product-code"><?= htmlspecialchars($p['kode']) ?></div>
                    <div class="product-name"><?= htmlspecialchars($p['nama']) ?></div>
                    <div class="product-price"><?= formatRupiah($p['harga_jual']) ?></div>
                    <div class="product-stok">
                        <?php if ($habis): ?>
                            <span class="stok-badge low">Stok Habis</span>
                        <?php elseif ($low): ?>
                            <span class="stok-badge low">Sisa <?= $p['stok'] ?></span>
                        <?php else: ?>
                            <span class="stok-badge">Stok: <?= $p['stok'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT: KERANJANG -->
    <div class="panel-right">
        <div class="cart-header">
            <div class="cart-title">
                Keranjang
                <span class="cart-count" id="cartCount">0</span>
            </div>
            <div class="no-trx" id="noTrx"><?= generateNoTransaksi() ?></div>
        </div>

        <div class="cart-items" id="cartItems">
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                <div>Keranjang kosong</div>
                <small style="color:var(--text2);font-size:11px">Klik produk untuk menambahkan</small>
            </div>
        </div>

        <div class="cart-footer">
            <div class="summary-row">
                <span>Subtotal</span>
                <span id="subtotal">Rp 0</span>
            </div>
            <div class="summary-row">
                <span>Diskon (Rp)</span>
                <input type="number" class="diskon-input" id="diskonInput" value="0" min="0" oninput="updateTotal()" placeholder="0">
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span class="val" id="total">Rp 0</span>
            </div>

            <div class="bayar-section">
                <div class="metode-group">
                    <button class="metode-btn active" onclick="setMetode('tunai', this)">💵 Tunai</button>
                    <button class="metode-btn" onclick="setMetode('transfer', this)">🏦 Transfer</button>
                    <button class="metode-btn" onclick="setMetode('qris', this)">📱 QRIS</button>
                </div>
                <div class="bayar-label">Uang Diterima</div>
                <input type="number" class="bayar-input" id="uangBayar" placeholder="0" oninput="updateKembalian()">
                <div class="kembalian-display">
                    <span class="label">Kembalian</span>
                    <span class="val" id="kembalianDisplay">Rp 0</span>
                </div>
                <button class="btn-bayar" id="btnBayar" onclick="prosesTransaksi()" disabled>
                    BAYAR SEKARANG
                </button>
                <button class="btn-clear" onclick="clearCart()">🗑 Hapus Semua</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SUKSES -->
<div class="modal-overlay" id="modalSukses">
    <div class="modal">
        <div class="modal-icon">✅</div>
        <div class="modal-title">Transaksi Berhasil!</div>
        <div class="modal-subtitle" id="modalSubtitle"></div>
        <div class="struk" id="strukContent"></div>
        <div class="modal-buttons">
            <button class="modal-btn secondary" onclick="cetakStruk()">🖨 Cetak Struk</button>
            <button class="modal-btn primary" onclick="transaksiBaruBaru()">✨ Transaksi Baru</button>
        </div>
    </div>
</div>

<script>
// State
let cart = [];
let metodeBayar = 'tunai';
let noTransaksi = document.getElementById('noTrx').textContent;
let lastTransaksi = null;

// Clock
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent =
        now.toLocaleDateString('id-ID', {weekday:'short', day:'numeric', month:'short'}) + ' — ' +
        now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
}
updateClock();
setInterval(updateClock, 1000);

// Format Rupiah
function formatRp(n) {
    return 'Rp ' + Math.floor(n).toLocaleString('id-ID');
}

// Filter produk
function filterProduk() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const kat = document.getElementById('katFilter').value;
    document.querySelectorAll('.product-card').forEach(card => {
        const nama = card.dataset.nama.toLowerCase();
        const kode = card.dataset.kode.toLowerCase();
        const cardKat = card.dataset.kat;
        const matchSearch = nama.includes(q) || kode.includes(q);
        const matchKat = !kat || cardKat === kat;
        card.style.display = (matchSearch && matchKat) ? '' : 'none';
    });
}

// Tambah ke keranjang
function tambahKeKeranjang(el) {
    const id = el.dataset.id;
    const nama = el.dataset.nama;
    const harga = parseFloat(el.dataset.harga);
    const stok = parseInt(el.dataset.stok);
    if (stok <= 0) return;

    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty >= stok) {
            showToast('⚠ Stok tidak cukup!', 'error');
            return;
        }
        existing.qty++;
        existing.subtotal = existing.qty * existing.harga;
    } else {
        cart.push({ id, nama, harga, qty: 1, subtotal: harga, stok });
    }

    renderCart();
    showToast('✓ ' + nama + ' ditambahkan', 'success');
}

// Render Cart
function renderCart() {
    const container = document.getElementById('cartItems');
    document.getElementById('cartCount').textContent = cart.reduce((a, i) => a + i.qty, 0);

    if (cart.length === 0) {
        container.innerHTML = `<div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <div>Keranjang kosong</div>
            <small style="color:var(--text2);font-size:11px">Klik produk untuk menambahkan</small>
        </div>`;
        updateTotal();
        return;
    }

    container.innerHTML = cart.map((item, idx) => `
        <div class="cart-item">
            <div class="item-info">
                <div class="item-name">${item.nama}</div>
                <div class="item-price">${formatRp(item.harga)} × ${item.qty}</div>
            </div>
            <div class="qty-control">
                <button class="qty-btn" onclick="ubahQty(${idx}, -1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button class="qty-btn" onclick="ubahQty(${idx}, 1)">+</button>
            </div>
            <div style="text-align:right;min-width:80px">
                <div class="item-subtotal">${formatRp(item.subtotal)}</div>
                <button class="remove-btn" onclick="hapusItem(${idx})">🗑</button>
            </div>
        </div>
    `).join('');

    updateTotal();
}

function ubahQty(idx, delta) {
    const item = cart[idx];
    const newQty = item.qty + delta;
    if (newQty <= 0) { hapusItem(idx); return; }
    if (newQty > item.stok) { showToast('⚠ Melebihi stok!', 'error'); return; }
    item.qty = newQty;
    item.subtotal = item.qty * item.harga;
    renderCart();
}

function hapusItem(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function clearCart() {
    if (cart.length === 0) return;
    if (confirm('Hapus semua item dari keranjang?')) {
        cart = [];
        renderCart();
    }
}

function setMetode(m, btn) {
    metodeBayar = m;
    document.querySelectorAll('.metode-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function updateTotal() {
    const subtotal = cart.reduce((a, i) => a + i.subtotal, 0);
    const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
    const total = Math.max(0, subtotal - diskon);
    document.getElementById('subtotal').textContent = formatRp(subtotal);
    document.getElementById('total').textContent = formatRp(total);
    updateKembalian();
    document.getElementById('btnBayar').disabled = cart.length === 0 || total <= 0;
}

function updateKembalian() {
    const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
    const subtotal = cart.reduce((a, i) => a + i.subtotal, 0);
    const total = Math.max(0, subtotal - diskon);
    const bayar = parseFloat(document.getElementById('uangBayar').value) || 0;
    const kembalian = bayar - total;
    document.getElementById('kembalianDisplay').textContent = formatRp(Math.max(0, kembalian));
}

// Proses transaksi
async function prosesTransaksi() {
    const diskon = parseFloat(document.getElementById('diskonInput').value) || 0;
    const subtotal = cart.reduce((a, i) => a + i.subtotal, 0);
    const total = Math.max(0, subtotal - diskon);
    const bayar = parseFloat(document.getElementById('uangBayar').value) || 0;

    if (bayar < total && metodeBayar === 'tunai') {
        showToast('⚠ Uang bayar kurang!', 'error');
        return;
    }

    const data = {
        no_transaksi: noTransaksi,
        items: cart,
        total_harga: subtotal,
        diskon: diskon,
        total_bayar: total,
        uang_bayar: metodeBayar === 'tunai' ? bayar : total,
        kembalian: metodeBayar === 'tunai' ? bayar - total : 0,
        metode_bayar: metodeBayar
    };

    try {
        document.getElementById('btnBayar').disabled = true;
        document.getElementById('btnBayar').textContent = 'Memproses...';

        const res = await fetch('pages/api_transaksi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (result.success) {
            lastTransaksi = { ...data, ...result };
            tampilkanSukses(result, data);
        } else {
            showToast('❌ Gagal: ' + result.message, 'error');
            document.getElementById('btnBayar').disabled = false;
            document.getElementById('btnBayar').textContent = 'BAYAR SEKARANG';
        }
    } catch (e) {
        showToast('❌ Error koneksi server', 'error');
        document.getElementById('btnBayar').disabled = false;
        document.getElementById('btnBayar').textContent = 'BAYAR SEKARANG';
    }
}

function tampilkanSukses(result, data) {
    document.getElementById('modalSubtitle').textContent = 'No. ' + noTransaksi;

    const items = cart.map(i =>
        `<div class="struk-row"><span>${i.nama} ×${i.qty}</span><span>${formatRp(i.subtotal)}</span></div>`
    ).join('');

    document.getElementById('strukContent').innerHTML = `
        <div class="struk-header">
            <strong><?= NAMA_TOKO ?></strong><br>
            <small><?= ALAMAT_TOKO ?><br><?= TELP_TOKO ?></small>
        </div>
        <hr class="struk-divider">
        <div class="struk-row"><span>No. Transaksi</span><span>${noTransaksi}</span></div>
        <div class="struk-row"><span>Waktu</span><span>${new Date().toLocaleString('id-ID')}</span></div>
        <div class="struk-row"><span>Kasir</span><span>Admin</span></div>
        <hr class="struk-divider">
        ${items}
        <hr class="struk-divider">
        <div class="struk-row"><span>Subtotal</span><span>${formatRp(data.total_harga)}</span></div>
        ${data.diskon > 0 ? `<div class="struk-row"><span>Diskon</span><span>-${formatRp(data.diskon)}</span></div>` : ''}
        <div class="struk-row struk-total"><span>TOTAL</span><span>${formatRp(data.total_bayar)}</span></div>
        <div class="struk-row"><span>Bayar (${data.metode_bayar})</span><span>${formatRp(data.uang_bayar)}</span></div>
        <div class="struk-row"><span>Kembali</span><span>${formatRp(data.kembalian)}</span></div>
        <hr class="struk-divider">
        <div style="text-align:center;font-size:11px;color:#888"><?= TAGLINE_TOKO ?><br>Terima kasih telah berbelanja!</div>
    `;

    document.getElementById('modalSukses').classList.add('show');
}

function cetakStruk() {
    const content = document.getElementById('strukContent').innerHTML;
    const w = window.open('', '_blank');
    w.document.write(`
        <html><head><title>Struk</title>
        <style>body{font-family:monospace;font-size:12px;max-width:300px;margin:auto;padding:16px;}
        hr{border-top:1px dashed #999;border-bottom:none;margin:6px 0;}
        .r{display:flex;justify-content:space-between;margin:2px 0;}
        .c{text-align:center;}</style></head><body>
        <div class="c"><strong><?= NAMA_TOKO ?></strong><br><small><?= ALAMAT_TOKO ?></small></div>
        ${content.replace(/class="struk-row struk-total"/g, 'class="r" style="font-weight:bold"')
                  .replace(/class="struk-row"/g, 'class="r"')
                  .replace(/class="struk-header"/g, 'class="c"')
                  .replace(/class="struk-divider"/g, '')
                  .replace(/<hr/g, '<hr style="border-top:1px dashed #999;border-bottom:none;margin:6px 0;"')}
        </body></html>`);
    w.print();
    w.close();
}

function transaksiBaruBaru() {
    cart = [];
    document.getElementById('diskonInput').value = 0;
    document.getElementById('uangBayar').value = '';
    document.getElementById('kembalianDisplay').textContent = 'Rp 0';
    document.getElementById('modalSukses').classList.remove('show');
    document.getElementById('btnBayar').textContent = 'BAYAR SEKARANG';
    // Generate nomor baru
    const now = new Date();
    const ymd = now.getFullYear().toString() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0');
    noTransaksi = 'TRX' + ymd + String(Math.floor(Math.random()*9999)).padStart(4,'0');
    document.getElementById('noTrx').textContent = noTransaksi;
    renderCart();
    // Refresh stok produk
    location.reload();
}

// Toast notification
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

// Init
renderCart();
</script>
</body>
</html>
