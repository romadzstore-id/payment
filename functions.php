<?php
// includes/functions.php

require_once 'database.php';
require_once __DIR__ . '/../config/payment.php';

class PaymentSystem {
    private $db;
    private $config;
    
    public function __construct() {
        $this->db = new Database();
        $this->config = include(__DIR__ . '/../config/payment.php');
    }
    
    // Get current mode
    public function isDemoMode() {
        return $this->config['mode'] === 'demo';
    }
    
    // Generate unique invoice
    public function generateInvoice($user_id) {
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        return "INV-{$date}-{$user_id}-{$random}";
    }
    
    // Generate unique code for payment
    public function generateUniqueCode() {
        return mt_rand(11, 999);
    }
    
    // Create new transaction
    public function createTransaction($user_id, $package_id) {
        // Get package details
        $this->db->query("SELECT * FROM packages WHERE id = :id");
        $this->db->bind(':id', $package_id);
        $package = $this->db->single();
        
        if (!$package) return false;
        
        // Generate invoice and unique code
        $invoice = $this->generateInvoice($user_id);
        $unique_code = $this->generateUniqueCode();
        $amount = $package['price'];
        $total_amount = $amount + $unique_code;
        
        // Calculate expiry time
        $expired_at = date('Y-m-d H:i:s', strtotime("+{$package['validity_minutes']} minutes"));
        
        // Generate QR Code data
        $qr_data = $this->generateQRData($invoice, $total_amount);
        
        // Insert transaction
        $this->db->query("
            INSERT INTO transactions 
            (user_id, package_id, invoice, amount, unique_code, total_amount, qr_code, expired_at) 
            VALUES 
            (:user_id, :package_id, :invoice, :amount, :unique_code, :total_amount, :qr_code, :expired_at)
        ");
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':package_id', $package_id);
        $this->db->bind(':invoice', $invoice);
        $this->db->bind(':amount', $amount);
        $this->db->bind(':unique_code', $unique_code);
        $this->db->bind(':total_amount', $total_amount);
        $this->db->bind(':qr_code', $qr_data['qr_url']);
        $this->db->bind(':expired_at', $expired_at);
        
        if ($this->db->execute()) {
            $transaction_id = $this->db->lastInsertId();
            
            // Log the transaction
            $this->logPayment($transaction_id, 'created', 'Transaction created');
            
            return [
                'id' => $transaction_id,
                'invoice' => $invoice,
                'amount' => $amount,
                'unique_code' => $unique_code,
                'total_amount' => $total_amount,
                'qr_code' => $qr_data['qr_url'],
                'expired_at' => $expired_at,
                'package' => $package
            ];
        }
        
        return false;
    }
    
    // Generate QR Code
    private function generateQRData($invoice, $amount) {
        $mode = $this->config['mode'];
        $qris_settings = $this->config['qris'];
        
        if ($mode === 'demo') {
            // Demo QR Code
            $qr_content = "DEMO|{$invoice}|{$amount}|" . time();
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size={$qris_settings['size']}&data=" . urlencode($qr_content);
        } else {
            // Production QR Code (Tripay example)
            $qr_content = $this->generateProductionQR($invoice, $amount);
            $qr_url = $qr_content; // URL from payment gateway
        }
        
        return [
            'qr_content' => $qr_content,
            'qr_url' => $qr_url
        ];
    }
    
    // Get user transactions (HISTORY FUNCTION)
    public function getUserTransactions($user_id, $limit = 20, $offset = 0) {
        $this->db->query("
            SELECT t.*, p.name as package_name, p.quota 
            FROM transactions t
            JOIN packages p ON t.package_id = p.id
            WHERE t.user_id = :user_id
            ORDER BY t.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    // Get transaction by ID
    public function getTransaction($id, $user_id = null) {
        $sql = "SELECT t.*, p.name as package_name, p.quota, p.description 
                FROM transactions t
                JOIN packages p ON t.package_id = p.id
                WHERE t.id = :id";
        
        if ($user_id) {
            $sql .= " AND t.user_id = :user_id";
        }
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        if ($user_id) {
            $this->db->bind(':user_id', $user_id);
        }
        
        return $this->db->single();
    }
    
    // Check transaction status
    public function checkTransactionStatus($id) {
        $transaction = $this->getTransaction($id);
        
        if (!$transaction) return false;
        
        // Check if expired
        if (strtotime($transaction['expired_at']) < time() && $transaction['status'] == 'pending') {
            $this->updateTransactionStatus($id, 'expired');
            return 'expired';
        }
        
        return $transaction['status'];
    }
    
    // Update transaction status
    public function updateTransactionStatus($id, $status, $reference = null) {
        $this->db->query("
            UPDATE transactions 
            SET status = :status, 
                reference = COALESCE(:reference, reference),
                paid_at = CASE WHEN :status = 'paid' THEN NOW() ELSE paid_at END
            WHERE id = :id
        ");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        $this->db->bind(':reference', $reference);
        
        if ($this->db->execute()) {
            $this->logPayment($id, 'status_update', "Status changed to {$status}");
            return true;
        }
        
        return false;
    }
    
    // Log payment activity
    public function logPayment($transaction_id, $action, $note) {
        $this->db->query("
            INSERT INTO payment_logs (transaction_id, action, note, ip_address, user_agent)
            VALUES (:transaction_id, :action, :note, :ip_address, :user_agent)
        ");
        
        $this->db->bind(':transaction_id', $transaction_id);
        $this->db->bind(':action', $action);
        $this->db->bind(':note', $note);
        $this->db->bind(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
        $this->db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        
        return $this->db->execute();
    }
    
    // Get active packages
    public function getActivePackages() {
        $this->db->query("SELECT * FROM packages WHERE status = 'active' ORDER BY price ASC");
        return $this->db->resultSet();
    }
    
    // Get transaction count for pagination
    public function getUserTransactionCount($user_id) {
        $this->db->query("SELECT COUNT(*) as total FROM transactions WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
}

// Global helper functions
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

function getTimeRemaining($expiry_time) {
    $now = time();
    $expiry = strtotime($expiry_time);
    $diff = $expiry - $now;
    
    if ($diff <= 0) return '00:00';
    
    $minutes = floor($diff / 60);
    $seconds = $diff % 60;
    
    return sprintf("%02d:%02d", $minutes, $seconds);
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'paid' => '<span class="badge badge-success">Paid</span>',
        'expired' => '<span class="badge badge-danger">Expired</span>',
        'failed' => '<span class="badge badge-danger">Failed</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}