<?php
// detail.php
session_start();
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check transaction ID
if (!isset($_GET['id'])) {
    header('Location: history.php');
    exit;
}

$paymentSystem = new PaymentSystem();
$user_id = $_SESSION['user_id'];
$transaction_id = intval($_GET['id']);

// Get transaction details
$transaction = $paymentSystem->getTransaction($transaction_id, $user_id);

if (!$transaction) {
    header('Location: history.php');
    exit;
}

// Check status
$current_status = $paymentSystem->checkTransactionStatus($transaction_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi #<?php echo $transaction['id']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <i class="fas fa-receipt"></i>
                <span>DETAIL TRANSAKSI</span>
            </div>
            <div class="user-info">
                <a href="history.php" class="btn btn-secondary" style="padding: 8px 15px;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="nav-menu">
            <a href="index.php" class="nav-btn">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="history.php" class="nav-btn">
                <i class="fas fa-history"></i> History
            </a>
            <a href="detail.php?id=<?php echo $transaction_id; ?>" class="nav-btn active">
                <i class="fas fa-receipt"></i> Detail
            </a>
            <a href="logout.php" class="nav-btn" style="margin-left: auto;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>

        <!-- Transaction Status Banner -->
        <div class="card">
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 36px; margin-bottom: 10px;">
                    <?php echo getStatusBadge($current_status); ?>
                </div>
                <h1 style="color: var(--primary); margin-bottom: 10px;">
                    <?php echo htmlspecialchars($transaction['invoice']); ?>
                </h1>
                <p style="color: var(--gray);">
                    Dibuat: <?php echo formatDate($transaction['created_at'], 'd/m/Y H:i:s'); ?>
                </p>
            </div>
        </div>

        <div class="main-content">
            <!-- Left Column: Transaction Details -->
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i> Informasi Transaksi
                </h2>
                
                <div class="payment-details">
                    <div class="detail-row">
                        <span>Paket</span>
                        <strong><?php echo htmlspecialchars($transaction['package_name']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Quota</span>
                        <strong><?php echo htmlspecialchars($transaction['quota']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Harga Paket</span>
                        <strong><?php echo formatCurrency($transaction['amount']); ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Kode Unik</span>
                        <strong><?php echo formatCurrency($transaction['unique_code']); ?></strong>
                    </div>
                    <div class="detail-row" style="border-top: 2px solid var(--primary);">
                        <span style="font-size: 18px;">Total Bayar</span>
                        <strong style="font-size: 24px; color: var(--danger);">
                            <?php echo formatCurrency($transaction['total_amount']); ?>
                        </strong>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-credit-card"></i> Metode Pembayaran
                    </h3>
                    <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--light); border-radius: var(--border-radius);">
                        <i class="fas fa-qrcode" style="font-size: 30px; color: var(--primary);"></i>
                        <div>
                            <strong>QRIS (QR Code Indonesian Standard)</strong>
                            <p style="color: var(--gray); margin-top: 5px; font-size: 14px;">
                                Scan dengan aplikasi e-wallet atau mobile banking
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if ($transaction['reference']): ?>
                    <div style="margin-top: 20px;">
                        <h3 style="margin-bottom: 15px; color: var(--primary);">
                            <i class="fas fa-hashtag"></i> Referensi Pembayaran
                        </h3>
                        <div style="padding: 15px; background: var(--light); border-radius: var(--border-radius); font-family: monospace;">
                            <?php echo htmlspecialchars($transaction['reference']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Actions & Timeline -->
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-cogs"></i> Aksi
                </h2>
                
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php if ($current_status == 'pending'): ?>
                        <a href="pay.php?id=<?php echo $transaction_id; ?>" class="btn btn-primary">
                            <i class="fas fa-qrcode"></i> Tampilkan QR Code
                        </a>
                        
                        <div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: var(--border-radius);">
                            <i class="fas fa-clock" style="font-size: 24px; color: #856404; margin-bottom: 10px;"></i>
                            <p style="color: #856404;">
                                <strong>Batas Waktu:</strong><br>
                                <?php echo formatDate($transaction['expired_at'], 'd/m/Y H:i:s'); ?>
                            </p>
                        </div>
                        
                        <button onclick="checkStatus(<?php echo $transaction_id; ?>)" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Cek Status Sekarang
                        </button>
                        
                    <?php elseif ($current_status == 'paid'): ?>
                        <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: var(--border-radius);">
                            <i class="fas fa-check-circle" style="font-size: 36px; color: #155724; margin-bottom: 10px;"></i>
                            <h3 style="color: #155724;">Pembayaran Berhasil</h3>
                            <p style="color: #155724; margin-top: 10px;">
                                Dibayar pada: <?php echo formatDate($transaction['paid_at'], 'd/m/Y H:i:s'); ?>
                            </p>
                        </div>
                        
                        <button onclick="printInvoice(<?php echo $transaction_id; ?>)" class="btn btn-success">
                            <i class="fas fa-print"></i> Cetak Invoice
                        </button>
                        
                    <?php elseif ($current_status == 'expired'): ?>
                        <div style="text-align: center; padding: 20px; background: #f8d7da; border-radius: var(--border-radius);">
                            <i class="fas fa-exclamation-triangle" style="font-size: 36px; color: #721c24; margin-bottom: 10px;"></i>
                            <h3 style="color: #721c24;">Transaksi Kadaluarsa</h3>
                            <p style="color: #721c24; margin-top: 10px;">
                                Kadaluarsa: <?php echo formatDate($transaction['expired_at'], 'd/m/Y H:i:s'); ?>
                            </p>
                        </div>
                        
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Buat Transaksi Baru
                        </a>
                        
                    <?php endif; ?>
                    
                    <a href="history.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Kembali ke History
                    </a>
                </div>
                
                <!-- Timeline -->
                <div style="margin-top: 30px;">
                    <h3 class="card-title">
                        <i class="fas fa-stream"></i> Timeline
                    </h3>
                    
                    <div style="position: relative; padding-left: 30px; margin-top: 20px;">
                        <!-- Timeline line -->
                        <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: var(--primary);"></div>
                        
                        <!-- Created -->
                        <div style="position: relative; margin-bottom: 20px;">
                            <div style="position: absolute; left: -30px; top: 0; width: 20px; height: 20px; background: var(--primary); border-radius: 50%;"></div>
                            <div>
                                <strong>Transaksi Dibuat</strong>
                                <p style="color: var(--gray); margin-top: 5px;">
                                    <?php echo formatDate($transaction['created_at'], 'd/m/Y H:i:s'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Status changes -->
                        <?php if ($current_status == 'paid' && $transaction['paid_at']): ?>
                            <div style="position: relative; margin-bottom: 20px;">
                                <div style="position: absolute; left: -30px; top: 0; width: 20px; height: 20px; background: var(--success); border-radius: 50%;"></div>
                                <div>
                                    <strong>Pembayaran Berhasil</strong>
                                    <p style="color: var(--gray); margin-top: 5px;">
                                        <?php echo formatDate($transaction['paid_at'], 'd/m/Y H:i:s'); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Expired -->
                        <?php if ($current_status == 'expired'): ?>
                            <div style="position: relative;">
                                <div style="position: absolute; left: -30px; top: 0; width: 20px; height: 20px; background: var(--danger); border-radius: 50%;"></div>
                                <div>
                                    <strong>Transaksi Kadaluarsa</strong>
                                    <p style="color: var(--gray); margin-top: 5px;">
                                        <?php echo formatDate($transaction['expired_at'], 'd/m/Y H:i:s'); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkStatus(transactionId) {
            const btn = event.target;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengecek...';
            btn.disabled = true;
            
            fetch('api/check_status.php?id=' + transactionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh page after 1 second
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert('Gagal memeriksa status');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
        
        function printInvoice(transactionId) {
            window.open('print.php?id=' + transactionId, '_blank');
        }
        
        // Auto refresh if status is pending
        <?php if ($current_status == 'pending'): ?>
            setTimeout(() => {
                window.location.reload();
            }, 30000); // Every 30 seconds
        <?php endif; ?>
    </script>
</body>
</html>