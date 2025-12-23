<?php
// api/callback.php
header('Content-Type: application/json');
require_once '../includes/functions.php';

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// For production: verify signature from payment gateway
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentSystem = new PaymentSystem();
    
    // Extract transaction data from callback
    $invoice = $data['invoice'] ?? null;
    $reference = $data['reference'] ?? null;
    $status = $data['status'] ?? null;
    $amount = $data['amount'] ?? null;
    
    if ($invoice && $status) {
        // Find transaction by invoice
        $paymentSystem->db->query("SELECT * FROM transactions WHERE invoice = :invoice");
        $paymentSystem->db->bind(':invoice', $invoice);
        $transaction = $paymentSystem->db->single();
        
        if ($transaction) {
            // Update transaction status
            $paymentSystem->updateTransactionStatus($transaction['id'], $status, $reference);
            
            // Log the callback
            $paymentSystem->logPayment($transaction['id'], 'callback_received', 
                json_encode($data));
            
            echo json_encode(['success' => true, 'message' => 'Callback processed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid callback data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}