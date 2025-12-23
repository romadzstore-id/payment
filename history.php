<?php
// history.php
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

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get transactions
$transactions = $paymentSystem->getUserTransactions($user_id, $limit, $offset);
$total_transactions = $paymentSystem->getUserTransactionCount($user_id);
$total_pages = ceil($total_transactions / $limit);

// Handle filter
$filter = $_GET['filter'] ?? 'all';
if ($filter !== 'all') {
    // Filter logic here
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Transaksi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <i class="fas fa-history"></i>
                <span>HISTORY TRANSAKSI</span>
            </div>
            <div class="user-info">
                <a href="index.php" class="btn btn-secondary" style="padding: 8px 15px;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="nav-menu">
            <a href="index.php" class="nav-btn">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="history.php" class="nav-btn active">
                <i class="fas fa-history"></i> History
            </a>
            <a href="profile.php" class="nav-btn">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="logout.php" class="nav-btn" style="margin-left: auto;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>

        <!-- Filter Section -->
        <div class="card">
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="?filter=all&page=1" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                    Semua (<?php echo $total_transactions; ?>)
                </a>
                <a href="?filter=pending&page=1" class="btn <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-clock"></i> Pending
                </a>
                <a href="?filter=paid&page=1" class="btn <?php echo $filter === 'paid' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-check-circle"></i> Paid
                </a>
                <a href="?filter=expired&page=1" class="btn <?php echo $filter === 'expired' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-exclamation-circle"></i> Expired
                </a>
            </div>
        </div>

        <!-- History Table -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-list"></i> Daftar Transaksi
                <span style="font-size: 14px; color: var(--gray); margin-left: 10px;">
                    Total: <?php echo $total_transactions; ?> transaksi
                </span>
            </h2>
            
            <?php if (empty($transactions)): ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-inbox" style="font-size: 60px; color: var(--gray-light); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--gray);">Belum ada transaksi</h3>
                    <p style="color: var(--gray); margin-top: 10px;">Mulai dengan membeli paket pertama Anda</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-shopping-cart"></i> Beli Paket
                    </a>
                </div>
            <?php else: ?>
                <div class="history-table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Paket</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($trans['invoice']); ?></strong>
                                        <br>
                                        <small style="color: var(--gray);">Ref: <?php echo $trans['reference'] ?? '-'; ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($trans['package_name']); ?>
                                        <br>
                                        <small style="color: var(--gray);"><?php echo $trans['quota']; ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo formatCurrency($trans['total_amount']); ?></strong>
                                        <br>
                                        <small style="color: var(--gray);">
                                            Pokok: <?php echo formatCurrency($trans['amount']); ?>
                                            + Unik: <?php echo formatCurrency($trans['unique_code']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($trans['status']); ?>
                                        <br>
                                        <small style="color: var(--gray);">
                                            <?php 
                                            if ($trans['status'] == 'pending') {
                                                echo 'Exp: ' . formatDate($trans['expired_at'], 'H:i');
                                            } elseif ($trans['status'] == 'paid') {
                                                echo 'Paid: ' . formatDate($trans['paid_at'], 'H:i');
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo formatDate($trans['created_at']); ?>
                                        <br>
                                        <small style="color: var(--gray);">
                                            <?php echo formatDate($trans['created_at'], 'H:i:s'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <a href="detail.php?id=<?php echo $trans['id']; ?>" 
                                               class="btn btn-success" 
                                               style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($trans['status'] == 'pending'): ?>
                                                <a href="pay.php?id=<?php echo $trans['id']; ?>" 
                                                   class="btn btn-primary" 
                                                   style="padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-qrcode"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($trans['status'] == 'paid'): ?>
                                                <button onclick="printInvoice(<?php echo $trans['id']; ?>)" 
                                                        class="btn btn-secondary" 
                                                        style="padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                <span class="page-link">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>" class="page-link">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Summary -->
        <div class="card">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Ringkasan
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="text-align: center; padding: 15px; background: var(--light); border-radius: var(--border-radius);">
                    <div style="font-size: 24px; font-weight: bold; color: var(--primary);">
                        <?php echo $total_transactions; ?>
                    </div>
                    <div style="color: var(--gray);">Total Transaksi</div>
                </div>
                <div style="text-align: center; padding: 15px; background: var(--light); border-radius: var(--border-radius);">
                    <div style="font-size: 24px; font-weight: bold; color: var(--success);">
                        <?php
                        // Count paid transactions (example)
                        echo count(array_filter($transactions, function($t) {
                            return $t['status'] == 'paid';
                        }));
                        ?>
                    </div>
                    <div style="color: var(--gray);">Transaksi Sukses</div>
                </div>
                <div style="text-align: center; padding: 15px; background: var(--light); border-radius: var(--border-radius);">
                    <div style="font-size: 24px; font-weight: bold; color: var(--danger);">
                        <?php
                        // Calculate total spent (example)
                        $total_spent = 0;
                        foreach ($transactions as $t) {
                            if ($t['status'] == 'paid') {
                                $total_spent += $t['total_amount'];
                            }
                        }
                        echo formatCurrency($total_spent);
                        ?>
                    </div>
                    <div style="color: var(--gray);">Total Pengeluaran</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printInvoice(transactionId) {
            window.open('print.php?id=' + transactionId, '_blank');
        }
        
        // Auto refresh page every 60 seconds if there are pending transactions
        const hasPending = <?php 
            echo json_encode(array_filter($transactions, function($t) {
                return $t['status'] == 'pending';
            }) ? true : false);
        ?>;
        
        if (hasPending) {
            setTimeout(() => {
                window.location.reload();
            }, 60000);
        }
    </script>
</body>
</html>