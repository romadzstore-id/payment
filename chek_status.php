<?php
// api/check_status.php
header('Content-Type: application/json');
session_start();
require_once '../includes/functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check transaction ID
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID required']);
    exit;
}

$paymentSystem = new PaymentSystem();
$transaction_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Verify transaction belongs to user
$transaction = $paymentSystem->getTransaction($transaction_id, $user_id);

if (!$transaction) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit;
}

// Check and update status
$current_status = $paymentSystem->checkTransactionStatus($transaction_id);

// For demo mode: simulate payment success after 30 seconds
if ($paymentSystem->isDemoMode() && $current_status == 'pending') {
    $created_time = strtotime($transaction['created_at']);
    $current_time = time();
    
    // If more than 30 seconds have passed, mark as paid (demo)
    if (($current_time - $created_time) > 30) {
        $paymentSystem->updateTransactionStatus($transaction_id, 'paid', 'DEMO-' . time());
        $current_status = 'paid';
    }
}

echo json_encode([
    'success' => true,
    'status' => $current_status,
    'invoice' => $transaction['invoice'],
    'amount' => $transaction['total_amount']
]);