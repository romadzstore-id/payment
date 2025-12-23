<?php
// pay.php
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
    header('Location: index.php');
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

// Check if already paid or expired
$current_status = $paymentSystem->checkTransactionStatus($transaction_id);
if ($current_status != 'pending') {
    header('Location: detail.php?id=' . $transaction_id);
    exit;
}

// Calculate remaining time
$expiry_time = strtotime($transaction['expired_at']);
$current_time = time();
$remaining_seconds = max(0, $expiry_time - $current_time);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran #<?php echo $transaction['invoice']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <i class="fas fa-qrcode"></i>
                <span>PEMBAYARAN QRIS</span>
            </div>
            <div class="user-info">
                <a href="detail.php?id=<?php echo $transaction_id; ?>" class="btn btn-secondary" style="padding: 8px 15px;">
                    <i class="fas fa-arrow-left"></i> Detail
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
            <a href="pay.php?id=<?php echo $transaction_id; ?>" class="nav-btn active">
                <i class="fas fa-qrcode"></i> Bayar
            </a>
            <a href="logout.php" class="nav-btn" style="margin-left: auto;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>

        <!-- Payment Content -->
        <div class="main-content">
            <!-- Left Column: QR Code -->
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-qrcode"></i> Scan QR Code
                </h2>
                
                <div class="qr-container">
                    <?php if ($transaction['qr_code']): ?>
                        <div class="qr-code">
                            <img src="<?php echo htmlspecialchars($transaction['qr_code']); ?>" 
                                 alt="QR Code" 
                                 id="qrImage">
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px;">
                            <div class="spinner"></div>
                            <p style="margin-top: 20px; color: var(--gray);">
                                Generating QR Code...
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px;">
                        <button onclick="refreshQR()" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Refresh QR Code
                        </button>
                        <button onclick="saveQR()" class="btn btn-success">
                            <i class="fas fa-download"></i> Simpan QR
                        </button>
                    </div>
                </div>
                
                <!-- Payment Instructions -->
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-info-circle"></i> Instruksi Pembayaran
                    </h3>
                    <div style="background: var(--light); padding: 20px; border-radius: var(--border-radius);">
                        <ol style="padding-left: 20px;">
                            <li style="margin-bottom: 10px;">
                                <strong>Buka aplikasi e-wallet atau mobile banking</strong> yang mendukung QRIS
                            </li>
                            <li style="margin-bottom: 10px;">
                                <strong>Pilih fitur scan QR Code</strong> di aplikasi Anda
                            </li>
                            <li style="margin-bottom: 10px;">
                                <strong>Arahkan kamera</strong> ke QR Code di atas
                            </li>
                            <li style="margin-bottom: 10px;">
                                <strong>Periksa nominal pembayaran</strong> harus sesuai dengan:
                                <div style="text-align: center; margin: 10px 0;">
                                    <div style="font-size: 24px; font-weight: bold; color: var(--danger);">
                                        <?php echo formatCurrency($transaction['total_amount']); ?>
                                    </div>
                                </div>
                            </li>
                            <li style="margin-bottom: 10px;">
                                <strong>Konfirmasi pembayaran</strong> di aplikasi Anda
                            </li>
                            <li>
                                <strong>Tunggu verifikasi otomatis</strong> (maksimal 2 menit)
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Right Column: Payment Details -->
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-receipt"></i> Detail Pembayaran
                </h2>
                
                <div class="payment-details">
                    <div class="detail-row">
                        <span>Invoice</span>
                        <strong><?php echo htmlspecialchars($transaction['invoice']); ?></strong>
                    </div>
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
                
                <!-- Countdown Timer -->
                <div style="margin-top: 30px; text-align: center;">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-clock"></i> Sisa Waktu
                    </h3>
                    <div class="countdown" id="countdownTimer">
                        <?php
                        $minutes = floor($remaining_seconds / 60);
                        $seconds = $remaining_seconds % 60;
                        printf("%02d:%02d", $minutes, $seconds);
                        ?>
                    </div>
                    <p style="color: var(--gray); margin-top: 10px;">
                        Berlaku hingga: <?php echo formatDate($transaction['expired_at'], 'H:i'); ?>
                    </p>
                </div>
                
                <!-- Status Check -->
                <div style="margin-top: 30px;">
                    <div id="statusMessage" style="display: none;"></div>
                    
                    <button onclick="checkPaymentStatus()" class="btn btn-primary btn-block">
                        <i class="fas fa-sync-alt"></i> Cek Status Pembayaran
                    </button>
                    
                    <div style="text-align: center; margin-top: 15px;">
                        <small style="color: var(--gray);">
                            <i class="fas fa-info-circle"></i>
                            Status diperiksa otomatis setiap 5 detik
                        </small>
                    </div>
                </div>
                
                <!-- Payment Methods -->
                <div style="margin-top: 30px;">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-mobile-alt"></i> Aplikasi yang Mendukung
                    </h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
                        <div style="padding: 10px; background: var(--light); border-radius: var(--border-radius);">
                            <i class="fas fa-wallet" style="color: #00aaff;"></i>
                            <p style="margin-top: 5px; font-size: 12px;">DANA</p>
                        </div>
                        <div style="padding: 10px; background: var(--light); border-radius: var(--border-radius);">
                            <i class="fas fa-qrcode" style="color: #ff6b00;"></i>
                            <p style="margin-top: 5px; font-size: 12px;">OVO</p>
                        </div>
                        <div style="padding: 10px; background: var(--light); border-radius: var(--border-radius);">
                            <i class="fas fa-bolt" style="color: #ffcc00;"></i>
                            <p style="margin-top: 5px; font-size: 12px;">GoPay</p>
                        </div>
                        <div style="padding: 10px; background: var(--light); border-radius: var(--border-radius);">
                            <i class="fas fa-university" style="color: #0088cc;"></i>
                            <p style="margin-top: 5px; font-size: 12px;">BCA Mobile</p>
                        </div>
                        <div style="padding: 10px; background: var(--light); border-radius: var(--border-radius);">
                            <i class="fas fa-mobile" style="color: #ff3366;"></i>
                            <p style="margin-top: 5px; font-size: 12px;">LinkAja</p>
                        </div>
                        <div style="padding: 10px; background: var(--light); border-radius: var(--border-radius);">
                            <i class="fas fa-shield-alt" style="color: #28a745;"></i>
                            <p style="margin-top: 5px; font-size: 12px;">ShopeePay</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Auto Check Status Info -->
        <div class="card" style="margin-top: 20px;">
            <div style="display: flex; align-items: center; gap: 15px; padding: 15px;">
                <i class="fas fa-robot" style="font-size: 24px; color: var(--primary);"></i>
                <div>
                    <strong>Sistem Verifikasi Otomatis</strong>
                    <p style="color: var(--gray); margin-top: 5px; font-size: 14px;">
                        Pembayaran akan diverifikasi setiap 5 detik. Jika sudah bayar, tunggu beberapa saat hingga status berubah.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown Timer
        let remainingSeconds = <?php echo $remaining_seconds; ?>;
        
        function updateCountdown() {
            if (remainingSeconds <= 0) {
                document.getElementById('countdownTimer').innerHTML = '00:00';
                document.getElementById('countdownTimer').classList.add('expired');
                window.location.href = 'detail.php?id=<?php echo $transaction_id; ?>';
                return;
            }
            
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            
            document.getElementById('countdownTimer').innerHTML = 
                minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            
            remainingSeconds--;
        }
        
        // Update countdown every second
        setInterval(updateCountdown, 1000);
        
        // Check payment status
        function checkPaymentStatus() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memeriksa...';
            btn.disabled = true;
            
            fetch('api/check_status.php?id=<?php echo $transaction_id; ?>')
                .then(response => response.json())
                .then(data => {
                    const statusMessage = document.getElementById('statusMessage');
                    
                    if (data.success) {
                        if (data.status === 'paid') {
                            statusMessage.innerHTML = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Pembayaran Berhasil!</strong>
                                    Mengalihkan ke halaman detail...
                                </div>
                            `;
                            statusMessage.style.display = 'block';
                            
                            // Redirect to detail page after 2 seconds
                            setTimeout(() => {
                                window.location.href = 'detail.php?id=<?php echo $transaction_id; ?>';
                            }, 2000);
                            
                        } else if (data.status === 'pending') {
                            statusMessage.innerHTML = `
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock"></i>
                                    <strong>Masih Menunggu Pembayaran</strong>
                                    Silakan selesaikan pembayaran di aplikasi Anda.
                                </div>
                            `;
                            statusMessage.style.display = 'block';
                            
                            // Re-enable button
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }
                    } else {
                        statusMessage.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Gagal Memeriksa Status</strong>
                                Silakan coba lagi.
                            </div>
                        `;
                        statusMessage.style.display = 'block';
                        
                        // Re-enable button
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('statusMessage').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Terjadi Kesalahan</strong>
                            Silakan refresh halaman.
                        </div>
                    `;
                    document.getElementById('statusMessage').style.display = 'block';
                    
                    // Re-enable button
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
        
        // Refresh QR Code
        function refreshQR() {
            const qrImage = document.getElementById('qrImage');
            if (qrImage) {
                qrImage.src = qrImage.src.split('?')[0] + '?t=' + new Date().getTime();
            }
        }
        
        // Save QR Code
        function saveQR() {
            const qrImage = document.getElementById('qrImage');
            if (qrImage) {
                const link = document.createElement('a');
                link.href = qrImage.src;
                link.download = 'QR_Payment_<?php echo $transaction['invoice']; ?>.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        
        // Auto-check payment status every 5 seconds
        setInterval(() => {
            fetch('api/check_status.php?id=<?php echo $transaction_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.status === 'paid') {
                        window.location.href = 'detail.php?id=<?php echo $transaction_id; ?>';
                    }
                });
        }, 5000);
        
        // Auto-refresh page if expired
        setTimeout(() => {
            window.location.reload();
        }, 60000); // Refresh every minute
    </script>
</body>
</html>