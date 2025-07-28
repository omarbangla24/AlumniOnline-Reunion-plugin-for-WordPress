<?php
/**
 * SMS Integration Class for Reunion Registration Plugin
 * File: includes/class-reunion-sms.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Reunion_SMS {
    
    private static $instance = null;
    
    private function __construct() {
        // Hook into registration and status changes
        add_action('reunion_after_registration', [$this, 'send_registration_sms'], 10, 2);
        add_action('reunion_status_changed_to_paid', [$this, 'send_payment_confirmation_sms'], 10, 2);
    }
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Send SMS using GreenWeb API
     */
    public function send_sms($to, $message) {
        $sms_enabled = get_option('reunion_sms_enabled', 'no');
        if ($sms_enabled !== 'yes') {
            return ['status' => 'error', 'message' => 'SMS service is disabled'];
        }
        
        $token = get_option('reunion_sms_token', '');
        if (empty($token)) {
            return ['status' => 'error', 'message' => 'SMS token not configured'];
        }
        
        // Format phone number
        $to = $this->format_phone_number($to);
        if (!$to) {
            return ['status' => 'error', 'message' => 'Invalid phone number'];
        }
        
        $url = "https://api.greenweb.com.bd/api.php?json";
        
        $data = array(
            'to' => $to,
            'message' => $message,
            'token' => $token
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['status' => 'error', 'message' => 'CURL Error: ' . $error];
        }
        
        // Parse JSON response
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => 'error', 'message' => 'Invalid JSON response', 'raw' => $response];
        }
        
        // Check first result
        if (is_array($result) && isset($result[0])) {
            $first_result = $result[0];
            if ($first_result['status'] === 'SENT') {
                return ['status' => 'success', 'message' => $first_result['statusmsg'], 'data' => $result];
            } else {
                return ['status' => 'error', 'message' => $first_result['statusmsg'], 'data' => $result];
            }
        }
        
        return ['status' => 'error', 'message' => 'Unknown response format', 'raw' => $response];
    }
    
    /**
     * Send bulk SMS with different messages
     */
    public function send_bulk_sms($sms_data) {
        $sms_enabled = get_option('reunion_sms_enabled', 'no');
        if ($sms_enabled !== 'yes') {
            return ['status' => 'error', 'message' => 'SMS service is disabled'];
        }
        
        $token = get_option('reunion_sms_token', '');
        if (empty($token)) {
            return ['status' => 'error', 'message' => 'SMS token not configured'];
        }
        
        // Format sms data
        $formatted_data = [];
        foreach ($sms_data as $sms) {
            $phone = $this->format_phone_number($sms['to']);
            if ($phone) {
                $formatted_data[] = [
                    'to' => $phone,
                    'message' => $sms['message']
                ];
            }
        }
        
        if (empty($formatted_data)) {
            return ['status' => 'error', 'message' => 'No valid phone numbers'];
        }
        
        $url = "https://api.bdbulksms.net/api.php?json";
        
        $post_data = json_encode([
            'token' => $token,
            'smsdata' => $formatted_data
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['status' => 'error', 'message' => 'CURL Error: ' . $error];
        }
        
        return ['status' => 'success', 'message' => 'Bulk SMS sent', 'raw' => $response];
    }
    
    /**
     * Format phone number to Bangladesh format
     */
    private function format_phone_number($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Bangladesh number
        if (strlen($phone) == 11 && substr($phone, 0, 2) == '01') {
            // Local format: 01XXXXXXXXX
            return '+88' . $phone;
        } elseif (strlen($phone) == 13 && substr($phone, 0, 2) == '88') {
            // With country code: 8801XXXXXXXXX
            return '+' . $phone;
        } elseif (strlen($phone) == 14 && substr($phone, 0, 3) == '+88') {
            // Full format: +8801XXXXXXXXX
            return $phone;
        }
        
        return false;
    }
    
    /**
     * Replace template variables
     */
    private function replace_template_variables($template, $data) {
        $replacements = [
            '{name}' => $data['name'] ?? '',
            '{unique_id}' => $data['unique_id'] ?? '',
            '{batch}' => $data['batch'] ?? '',
            '{mobile}' => $data['mobile_number'] ?? '',
            '{fee}' => $data['total_fee'] ?? '',
            '{status}' => $data['status'] ?? '',
            '{event_year}' => $data['event_year'] ?? '',
            '{payment_method}' => $data['payment_method'] ?? '',
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Send SMS after registration
     */
    public function send_registration_sms($registration_data, $registration_id) {
        $template = get_option('reunion_sms_registration_template', '');
        if (empty($template)) {
            $template = "Dear {name}, Your reunion registration (ID: {unique_id}) has been received. Fee: {fee} BDT. Status: {status}. Thank you!";
        }
        
        $message = $this->replace_template_variables($template, $registration_data);
        $result = $this->send_sms($registration_data['mobile_number'], $message);
        
        // Log SMS
        $this->log_sms($registration_id, 'registration', $message, $result);
        
        return $result;
    }
    
    /**
     * Send SMS when payment is confirmed
     */
    public function send_payment_confirmation_sms($registration_data, $registration_id) {
        $template = get_option('reunion_sms_paid_template', '');
        if (empty($template)) {
            $template = "Dear {name}, Your payment for reunion registration (ID: {unique_id}) has been confirmed. Fee paid: {fee} BDT. See you at the event!";
        }
        
        $message = $this->replace_template_variables($template, $registration_data);
        $result = $this->send_sms($registration_data['mobile_number'], $message);
        
        // Log SMS
        $this->log_sms($registration_id, 'payment_confirmed', $message, $result);
        
        return $result;
    }
    
    /**
     * Get SMS balance
     */
    public function get_sms_balance() {
        $token = get_option('reunion_sms_token', '');
        if (empty($token)) {
            return ['status' => 'error', 'message' => 'SMS token not configured'];
        }
        
        $url = "https://api.greenweb.com.bd/g_api.php?token={$token}&balance&json";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['status' => 'error', 'message' => 'CURL Error: ' . $error];
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to parse as plain text
            return ['status' => 'success', 'balance' => trim($response)];
        }
        
        return ['status' => 'success', 'data' => $result];
    }
    
    /**
     * Test SMS
     */
    public function send_test_sms($phone_number) {
        $message = "Test SMS from Reunion Registration Plugin. If you receive this, SMS integration is working correctly!";
        return $this->send_sms($phone_number, $message);
    }
    
    /**
     * Log SMS activity
     */
    private function log_sms($registration_id, $type, $message, $result) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reunion_sms_logs';
        
        $wpdb->insert(
            $table_name,
            [
                'registration_id' => $registration_id,
                'sms_type' => $type,
                'message' => $message,
                'status' => $result['status'],
                'response' => json_encode($result),
                'sent_at' => current_time('mysql')
            ]
        );
    }
    
    /**
     * Create SMS log table
     */
    public static function create_sms_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reunion_sms_logs';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            registration_id mediumint(9),
            sms_type varchar(50),
            message text,
            status varchar(20),
            response text,
            sent_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}