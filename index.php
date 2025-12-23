<?php
// index.php
session_start();
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$paymentSystem = new PaymentSystem();
$user_id = $_SESSION['user_id'];

// Handle package selection
$selected_package = isset($_POST['package_id']) ? intval($_POST['package_id']) : null;

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_package']) && $selected_package) {
    $transaction = $paymentSystem->createTransaction($user_id, $selected_package);
    
    if ($transaction) {
        header("Location: pay.php?id=" . $transaction['id']);
        exit;
    } else {
        $error = "Gagal membuat transaksi. Silakan coba lagi.";
    }
}

// Get packages
$packages = $paymentSystem->getActivePackages();

// Get recent transactions for sidebar
$recent_transactions = $paymentSystem->getUserTransactions($user_id, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Pembelian Paket</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <i class="fas fa-bolt"></i>
                <span>PANEL PAYMENT</span>
            </div>
            <div class="user-info">
                <span class="balance">
                    <i class="fas fa-wallet"></i> Rp 0
                </span>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="nav-menu">
            <a href="index.php" class="nav-btn active">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="history.php" class="nav-btn">
                <i class="fas fa-history"></i> History
            </a>
            <a href="profile.php" class="nav-btn">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="logout.php" class="nav-btn" style="margin-left: auto;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Packages Section -->
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-box"></i> Pilih Paket
                </h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="packageForm">
                    <div class="packages-grid">
                        <?php foreach ($packages as $package): ?>
                            <div class="package-card <?php echo ($selected_package == $package['id']) ? 'selected' : ''; ?>"
                                 onclick="selectPackage(<?php echo $package['id']; ?>)">
                                <div class="package-badge">POPULAR</div>
                                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                                <div class="package-quota"><?php echo htmlspecialchars($package['quota']); ?></div>
                                <div class="package-price">
                                    <?php echo formatCurrency($package['price']); ?>
                                </div>
                                <p class="package-validity">
                                    <i class="fas fa-clock"></i> <?php echo $package['validity_minutes']; ?> menit
                                </p>
                                <input type="radio" name="package_id" value="<?php echo $package['id']; ?>" 
                                       id="package_<?php echo $package['id']; ?>"
                                       style="display: none;"
                                       <?php echo ($selected_package == $package['id']) ? 'checked' : ''; ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($selected_package): ?>
                        <button type="submit" name="buy_package" class="btn btn-primary btn-block" style="margin-top: 20px;">
                            <i class="fas fa-shopping-cart"></i> BELI SEKARANG
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Recent Transactions Sidebar -->
            <div class="card">
                <h2 class="card-title">
                    <i class="fas fa-history"></i> Transaksi Terbaru
                </h2>
                
                <?php if (empty($recent_transactions)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 20px;">
                        Belum ada transaksi
                    </p>
                <?php else: ?>
                    <div class="history-table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_transactions as $trans): ?>
                                    <tr onclick="window.location='detail.php?id=<?php echo $trans['id']; ?>'" style="cursor: pointer;">
                                        <td>
                                            <small><?php echo substr($trans['invoice'], 0, 15) . '...'; ?></small>
                                        </td>
                                        <td><?php echo getStatusBadge($trans['status']); ?></td>
                                        <td><?php echo formatCurrency($trans['total_amount']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="history.php" class="btn btn-secondary btn-block" style="margin-top: 15px;">
                        <i class="fas fa-list"></i> Lihat Semua
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>© <?php echo date('Y'); ?> Panel Payment QRIS | Mode: <strong><?php echo strtoupper($paymentSystem->isDemoMode() ? 'DEMO' : 'PRODUCTION'); ?></strong></p>
        </footer>
    </div>

    <script>
        function selectPackage(packageId) {
            // Uncheck all radios
            document.querySelectorAll('input[name="package_id"]').forEach(radio => {
                radio.checked = false;
                radio.closest('.package-card').classList.remove('selected');
            });
            
            // Check selected package
            const radio = document.getElementById('package_' + packageId);
            const card = radio.closest('.package-card');
            
            radio.checked = true;
            card.classList.add('selected');
            
            // Submit form automatically after 500ms
            setTimeout(() => {
                document.getElementById('packageForm').submit();
            }, 500);
        }
        
        // Auto-refresh page every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>