<?php
/**
 * Main Reunion Registration Plugin Class - Complete with All Fixes
 */
final class Reunion_Registration_Plugin {

    private static $instance;
    private $table_name;
    private $db_version = '1.4';
    private static $form_message = '';
    private static $newly_registered_record = null;

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'reunion_registrations';
        $this->load_hooks();
        $this->init_sms(); // SMS initialization
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_hooks() {
        add_action('init', [$this, 'handle_public_form_submission']);
        add_action('admin_init', [$this, 'handle_admin_redirect_actions']);
        add_action('admin_init', [$this, 'handle_export_requests']); // Export handler
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        add_shortcode('reunion_registration_form', [$this, 'registration_form_shortcode']);
        add_shortcode('reunion_acknowledgement_slip', [$this, 'acknowledgement_slip_shortcode']);
        add_shortcode('reunion_rules_page', [$this, 'rules_page_shortcode']);
        
        // SMS AJAX handlers
        add_action('wp_ajax_reunion_check_sms_balance', [$this, 'ajax_check_sms_balance']);
        add_action('wp_ajax_reunion_send_test_sms', [$this, 'ajax_send_test_sms']);
    }
    
    /**
     * Initialize SMS functionality
     */
    private function init_sms() {
        require_once REUNION_REG_PLUGIN_DIR . 'includes/class-reunion-sms.php';
        Reunion_SMS::get_instance();
    }
    
    public function enqueue_admin_styles($hook_suffix) {
        if (strpos($hook_suffix, 'reunion-') === false) return;
        wp_enqueue_style('reunion-admin-style', REUNION_REG_PLUGIN_URL . 'assets/css/admin-style.css', [], REUNION_REG_VERSION);
    }

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reunion_registrations';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            unique_id varchar(20) NOT NULL,
            name varchar(100) NOT NULL,
            father_name varchar(100) DEFAULT NULL,
            mother_name varchar(100) DEFAULT NULL,
            profession varchar(100) DEFAULT NULL,
            blood_group varchar(10) DEFAULT NULL,
            batch smallint(4) NOT NULL,
            tshirt_size varchar(20) DEFAULT NULL,
            spouse_status varchar(3) NOT NULL,
            spouse_name varchar(100) DEFAULT NULL,
            child_status varchar(3) NOT NULL,
            child_details text DEFAULT NULL,
            mobile_number varchar(20) NOT NULL,
            profile_picture_url varchar(255) DEFAULT NULL,
            payment_method varchar(20) NOT NULL,
            payment_details text NOT NULL,
            status varchar(20) DEFAULT 'Pending' NOT NULL,
            event_year smallint(4) NOT NULL,
            total_fee decimal(10,2) NOT NULL DEFAULT '0.00',
            registration_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_id (unique_id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        update_option('reunion_reg_db_version', '1.4');
        
        // Create SMS log table
        require_once REUNION_REG_PLUGIN_DIR . 'includes/class-reunion-sms.php';
        Reunion_SMS::create_sms_log_table();
    }

    public function update_db_check() {
        if (get_site_option('reunion_reg_db_version') != $this->db_version) {
            self::activate();
        }
    }

    public function admin_menu() {
        add_menu_page(__('Reunion', 'reunion-reg'), __('Reunion Admin', 'reunion-reg'), 'manage_options', 'reunion-registrations', [$this, 'admin_page_router'], 'dashicons-groups', 25);
        add_submenu_page('reunion-registrations', __('All Registrations', 'reunion-reg'), __('All Registrations', 'reunion-reg'), 'manage_options', 'reunion-registrations');
        add_submenu_page('reunion-registrations', __('Reports', 'reunion-reg'), __('Reports', 'reunion-reg'), 'manage_options', 'reunion-reports', [$this, 'reports_page']);
        add_submenu_page('reunion-registrations', __('Settings', 'reunion-reg'), __('Settings', 'reunion-reg'), 'manage_options', 'reunion-settings', [$this, 'settings_page']);
        add_submenu_page('reunion-registrations', __('SMS Settings', 'reunion-reg'), __('SMS Settings', 'reunion-reg'), 'manage_options', 'reunion-sms-settings', [$this, 'sms_settings_page']);
        add_submenu_page('reunion-registrations', __('SMS Logs', 'reunion-reg'), __('SMS Logs', 'reunion-reg'), 'manage_options', 'reunion-sms-logs', [$this, 'sms_logs_page']);
    }

    public function admin_page_router() {
        $this->handle_page_actions();
        if (isset($_GET['view_id'])) { $this->display_single_entry(intval($_GET['view_id'])); } 
        elseif (isset($_GET['edit_id'])) { $this->display_edit_form(intval($_GET['edit_id'])); } 
        else { $this->display_list_page(); }
    }

    /**
     * Handle Export Requests (Excel/PDF)
     */
    public function handle_export_requests() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'reunion-registrations' || !isset($_GET['export'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        global $wpdb;
        
        // Get filters
        $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $batch_filter = isset($_GET['batch_filter']) ? intval($_GET['batch_filter']) : '';
        $year_filter = isset($_GET['year_filter']) ? intval($_GET['year_filter']) : '';
        $status_filter = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : '';
        
        // Build WHERE clause
        $where = []; $params = [];
        if ($search_term) { $where[] = "(name LIKE %s OR unique_id LIKE %s OR mobile_number LIKE %s)"; $like = '%' . $wpdb->esc_like($search_term) . '%'; $params[] = $like; $params[] = $like; $params[] = $like; }
        if ($batch_filter) { $where[] = "batch = %d"; $params[] = $batch_filter; }
        if ($year_filter) { $where[] = "event_year = %d"; $params[] = $year_filter; }
        if ($status_filter) { $where[] = "status = %s"; $params[] = $status_filter; }
        
        $sql_where = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_name} {$sql_where} ORDER BY registration_date DESC", $params));

        $export_type = sanitize_text_field($_GET['export']);
        
        if ($export_type === 'excel') {
            $this->export_to_excel($results);
        } elseif ($export_type === 'pdf') {
            $this->export_to_pdf($results);
        }
    }

    /**
     * Export to Excel
     */
    private function export_to_excel($data) {
        $filename = 'reunion-registrations-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        $headers = [
            'Registration ID',
            'Name',
            'Father Name',
            'Mother Name',
            'Profession',
            'Blood Group',
            'Batch',
            'Event Year',
            'T-Shirt Size',
            'Mobile Number',
            'Spouse Status',
            'Spouse Name',
            'Child Status',
            'Child Details',
            'Payment Method',
            'Payment Details',
            'Total Fee',
            'Status',
            'Registration Date'
        ];
        
        fputcsv($output, $headers);
        
        // Data rows
        foreach ($data as $row) {
            $child_details = '';
            if (!empty($row->child_details)) {
                $children = json_decode($row->child_details, true);
                if (is_array($children)) {
                    $child_names = [];
                    foreach ($children as $child) {
                        $child_names[] = $child['name'] . ' (' . $child['dob'] . ')';
                    }
                    $child_details = implode(', ', $child_names);
                }
            }
            
            $payment_details = '';
            if (!empty($row->payment_details)) {
                $payment = json_decode($row->payment_details, true);
                if (is_array($payment)) {
                    if ($row->payment_method === 'bKash') {
                        $payment_details = 'Number: ' . ($payment['bkash_number'] ?? '') . ', TrxID: ' . ($payment['transaction_id'] ?? '');
                    } elseif ($row->payment_method === 'Bank') {
                        $payment_details = 'A/C Name: ' . ($payment['bank_account_name'] ?? '') . ', A/C No: ' . ($payment['bank_account_number'] ?? '');
                    }
                }
            }
            
            $csv_row = [
                $row->unique_id,
                $row->name,
                $row->father_name,
                $row->mother_name,
                $row->profession,
                $row->blood_group,
                $row->batch,
                $row->event_year,
                $row->tshirt_size,
                $row->mobile_number,
                $row->spouse_status,
                $row->spouse_name,
                $row->child_status,
                $child_details,
                $row->payment_method,
                $payment_details,
                number_format((float)$row->total_fee, 2),
                $row->status,
                date('Y-m-d H:i:s', strtotime($row->registration_date))
            ];
            
            fputcsv($output, $csv_row);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export to PDF
     */
    private function export_to_pdf($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reunion Registrations Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
                h1 { text-align: center; color: #333; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .status-paid { color: green; font-weight: bold; }
                .status-pending { color: orange; font-weight: bold; }
                .status-cancelled { color: red; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Reunion Registrations Report</h1>
            <p><strong>Generated:</strong> <?php echo date('F d, Y H:i:s'); ?></p>
            <p><strong>Total Records:</strong> <?php echo count($data); ?></p>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Batch</th>
                        <th>Mobile</th>
                        <th>Payment</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->unique_id); ?></td>
                        <td><?php echo esc_html($row->name); ?></td>
                        <td><?php echo esc_html($row->batch); ?></td>
                        <td><?php echo esc_html($row->mobile_number); ?></td>
                        <td><?php echo esc_html($row->payment_method); ?></td>
                        <td><?php echo number_format((float)$row->total_fee, 2); ?> BDT</td>
                        <td class="status-<?php echo strtolower($row->status); ?>">
                            <?php echo esc_html($row->status); ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row->registration_date)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        $html = ob_get_clean();
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="reunion-registrations-' . date('Y-m-d') . '.html"');
        
        echo $html;
        exit;
    }
    
    public function handle_admin_redirect_actions() {
        global $wpdb;
        if (isset($_GET['page']) && $_GET['page'] === 'reunion-registrations' && isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
            if (check_admin_referer('reunion_status_update_' . $_GET['id'])) {
                $id = intval($_GET['id']);
                $new_status = sanitize_text_field($_GET['new_status']);
                
                $wpdb->update($this->table_name, ['status' => $new_status], ['id' => $id]);
                
                // If status changed to Paid, trigger SMS
                if ($new_status === 'Paid') {
                    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
                    if ($record) {
                        do_action('reunion_status_changed_to_paid', $record, $id);
                    }
                }
                
                wp_safe_redirect(admin_url('admin.php?page=reunion-registrations&status_updated=1'));
                exit;
            }
        }
    }

    private function handle_page_actions() {
        global $wpdb;
        if (isset($_POST['action']) && $_POST['action'] === 'edit_registration' && isset($_POST['registration_id']) && check_admin_referer('reunion_edit_nonce_' . $_POST['registration_id'])) { $this->handle_edit_submission(); }
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && check_admin_referer('reunion_delete_nonce_' . $_GET['id'])) { $wpdb->delete($this->table_name, ['id' => intval($_GET['id'])], ['%d']); add_action('admin_notices', function(){ echo '<div class="notice notice-success is-dismissible"><p>' . __('Registration deleted!', 'reunion-reg') . '</p></div>'; }); }
        
        if ((isset($_POST['reunion_bulk_action']) && $_POST['reunion_bulk_action'] !== '-1') && isset($_POST['registration_ids'])) {
            if (check_admin_referer('reunion_bulk_action_nonce')) {
                $ids = array_map('intval', $_POST['registration_ids']);
                $action = sanitize_text_field($_POST['reunion_bulk_action']);
                if (empty($ids)) { add_action('admin_notices', function(){ echo '<div class="notice notice-warning is-dismissible"><p>' . __('Please select items.', 'reunion-reg') . '</p></div>'; }); return; }
                $ids_placeholder = implode(', ', array_fill(0, count($ids), '%d'));
                if ($action === 'bulk-delete') { $wpdb->query($wpdb->prepare("DELETE FROM {$this->table_name} WHERE id IN ($ids_placeholder)", $ids)); add_action('admin_notices', function() use ($ids) { echo '<div class="notice notice-success is-dismissible"><p>' . count($ids) . ' ' . __('registrations deleted.', 'reunion-reg') . '</p></div>'; }); } 
                elseif ($action === 'bulk-mark-paid') { $wpdb->query($wpdb->prepare("UPDATE {$this->table_name} SET status = 'Paid' WHERE id IN ($ids_placeholder)", $ids)); add_action('admin_notices', function() use ($ids) { echo '<div class="notice notice-success is-dismissible"><p>' . count($ids) . ' ' . __('registrations marked as Paid.', 'reunion-reg') . '</p></div>'; }); } 
                elseif ($action === 'bulk-mark-pending') { $wpdb->query($wpdb->prepare("UPDATE {$this->table_name} SET status = 'Pending' WHERE id IN ($ids_placeholder)", $ids)); add_action('admin_notices', function() use ($ids) { echo '<div class="notice notice-success is-dismissible"><p>' . count($ids) . ' ' . __('registrations marked as Pending.', 'reunion-reg') . '</p></div>'; }); }
            }
        }
    }
    
    /**
     * Handle edit submission - FIXED with better unique ID validation
     */
    private function handle_edit_submission() { 
        global $wpdb;
        $id = intval($_POST['registration_id']);
        
        $data = [
            'name' => sanitize_text_field($_POST['reg_name']),
            'father_name' => sanitize_text_field($_POST['father_name']),
            'mother_name' => sanitize_text_field($_POST['mother_name']),
            'profession' => sanitize_text_field($_POST['profession']),
            'blood_group' => sanitize_text_field($_POST['blood_group']),
            'batch' => intval($_POST['reg_batch']), 
            'event_year' => intval($_POST['event_year']),
            'tshirt_size' => sanitize_text_field($_POST['tshirt_size']), 
            'spouse_status' => sanitize_text_field($_POST['spouse_status']), 
            'spouse_name' => ($_POST['spouse_status'] === 'Yes') ? sanitize_text_field($_POST['spouse_name']) : null,
            'child_status' => sanitize_text_field($_POST['child_status']), 
            'mobile_number' => sanitize_text_field($_POST['mobile_number']),
            'payment_method' => sanitize_text_field($_POST['payment_method']), 
            'total_fee' => floatval($_POST['total_fee']),
            'status' => sanitize_text_field($_POST['status'])
        ];

        // Handle unique_id change with better validation
        if (isset($_POST['unique_id']) && !empty($_POST['unique_id'])) {
            $new_unique_id = sanitize_text_field($_POST['unique_id']);
            $current_unique_id = $wpdb->get_var($wpdb->prepare(
                "SELECT unique_id FROM {$this->table_name} WHERE id = %d", 
                $id
            ));
            
            // Only check for duplicates if the ID is actually changing
            if ($new_unique_id !== $current_unique_id) {
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$this->table_name} WHERE unique_id = %s AND id != %d", 
                    $new_unique_id, 
                    $id
                ));
                
                if ($existing) {
                    add_action('admin_notices', function() use ($new_unique_id) { 
                        echo '<div class="notice notice-error is-dismissible"><p>' . 
                        sprintf(__('Registration ID "%s" already exists! Please choose a different ID.', 'reunion-reg'), esc_html($new_unique_id)) . 
                        '</p></div>'; 
                    });
                    return;
                } else {
                    $data['unique_id'] = $new_unique_id;
                }
            }
        }

        // Handle profile picture URL update
        if (isset($_POST['profile_picture_url'])) {
            $data['profile_picture_url'] = sanitize_url($_POST['profile_picture_url']);
        }

        // Handle new profile picture upload
        if (!empty($_FILES['new_profile_picture']['name']) && $_FILES['new_profile_picture']['error'] === UPLOAD_ERR_OK) {
            if (!function_exists('wp_handle_upload')) { 
                require_once(ABSPATH . 'wp-admin/includes/file.php'); 
            }
            
            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($_FILES['new_profile_picture'], $upload_overrides);
            
            if ($movefile && !isset($movefile['error'])) { 
                $data['profile_picture_url'] = $movefile['url']; 
            } else {
                add_action('admin_notices', function() use ($movefile) {
                    $error_message = isset($movefile['error']) ? $movefile['error'] : 'Unknown upload error';
                    echo '<div class="notice notice-warning is-dismissible"><p>' . 
                    sprintf(__('Profile picture upload failed: %s', 'reunion-reg'), esc_html($error_message)) . 
                    '</p></div>';
                });
            }
        }
        
        $children = [];
        if ($data['child_status'] === 'Yes' && !empty($_POST['child_name'])) {
            for ($i = 0; $i < count($_POST['child_name']); $i++) { 
                if (!empty($_POST['child_name'][$i])) { 
                    $children[] = [
                        'name' => sanitize_text_field($_POST['child_name'][$i]), 
                        'dob' => sanitize_text_field($_POST['child_age'][$i])
                    ]; 
                } 
            }
        }
        $data['child_details'] = json_encode($children);
        
        $payment_data = [];
        if ($data['payment_method'] === 'bKash') { 
            $payment_data = [
                'bkash_number' => sanitize_text_field($_POST['bkash_number']), 
                'transaction_id' => sanitize_text_field($_POST['transaction_id'])
            ]; 
        } elseif ($data['payment_method'] === 'Bank') { 
            $payment_data = [
                'bank_account_name' => sanitize_text_field($_POST['bank_account_name']), 
                'bank_account_number' => sanitize_text_field($_POST['bank_account_number'])
            ]; 
        }
        $data['payment_details'] = json_encode($payment_data);
        
        // Perform the update
        $result = $wpdb->update($this->table_name, $data, ['id' => $id]);
        
        if ($result !== false) { 
            add_action('admin_notices', function(){ 
                echo '<div class="notice notice-success is-dismissible"><p>' . 
                __('Registration updated successfully!', 'reunion-reg') . 
                '</p></div>'; 
            }); 
            
            // If status changed to Paid, trigger SMS
            if (isset($data['status']) && $data['status'] === 'Paid') {
                $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
                if ($record) {
                    do_action('reunion_status_changed_to_paid', $record, $id);
                }
            }
        } else { 
            add_action('admin_notices', function() use ($wpdb){ 
                echo '<div class="notice notice-error is-dismissible"><p>' . 
                sprintf(__('Update failed: %s', 'reunion-reg'), esc_html($wpdb->last_error)) . 
                '</p></div>'; 
            }); 
        }
    }

    private function display_list_page() {
        global $wpdb;
        $this->handle_page_actions();
        if (isset($_GET['status_updated'])) { add_action('admin_notices', function(){ echo '<div class="notice notice-success is-dismissible"><p>' . __('Status updated successfully!', 'reunion-reg') . '</p></div>'; }); }
        
        $search_term = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : ''; 
        $batch_filter = isset($_REQUEST['batch_filter']) ? intval($_REQUEST['batch_filter']) : ''; 
        $year_filter = isset($_REQUEST['year_filter']) ? intval($_REQUEST['year_filter']) : '';
        $status_filter = isset($_REQUEST['status_filter']) ? sanitize_text_field($_REQUEST['status_filter']) : '';
        
        $where = []; $params = [];
        if ($search_term) { $where[] = "(name LIKE %s OR unique_id LIKE %s OR mobile_number LIKE %s)"; $like = '%' . $wpdb->esc_like($search_term) . '%'; $params[] = $like; $params[] = $like; $params[] = $like; }
        if ($batch_filter) { $where[] = "batch = %d"; $params[] = $batch_filter; }
        if ($year_filter) { $where[] = "event_year = %d"; $params[] = $year_filter; }
        if ($status_filter) { $where[] = "status = %s"; $params[] = $status_filter; }
        
        $sql_where = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $per_page = 20; $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1; $offset = ($current_page - 1) * $per_page;
        $total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$this->table_name} {$sql_where}", $params));
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_name} {$sql_where} ORDER BY registration_date DESC LIMIT %d, %d", array_merge($params, [$offset, $per_page])));
        $batches = $wpdb->get_col("SELECT DISTINCT batch FROM {$this->table_name} ORDER BY batch DESC");
        $event_years = $wpdb->get_col("SELECT DISTINCT event_year FROM {$this->table_name} ORDER BY event_year DESC");
        $statuses = ['Pending', 'Paid', 'Cancelled'];
        
        require_once REUNION_REG_PLUGIN_DIR . 'views/admin-list-view.php';
    }

    private function display_edit_form($id) {
        global $wpdb;
        $this->handle_page_actions();
        $record = $this->get_record_by_id($id);
        if (!$record) { echo '<div class="wrap"><h1>Not Found</h1></div>'; return; }
        $child_details = json_decode($record->child_details, true);
        $payment_details = json_decode($record->payment_details, true);
        require REUNION_REG_PLUGIN_DIR . 'views/admin-edit-view.php';
    }

    private function display_single_entry($id) {
        $record = $this->get_record_by_id($id);
        echo '<div class="wrap"><h1>Registration Details</h1><a href="?page=reunion-registrations" class="button" style="margin-bottom:20px;">&larr; Back to Registrations</a>';
        echo $this->acknowledgement_slip_shortcode(['record' => $record]);
        echo '</div>';
    }

    public function reports_page() { 
        global $wpdb;
        $all_event_years = $wpdb->get_col("SELECT DISTINCT event_year FROM {$this->table_name} ORDER BY event_year DESC");
        $current_year_setting = get_option('reunion_current_event_year', date('Y'));
        $selected_year = isset($_GET['event_year']) ? intval($_GET['event_year']) : $current_year_setting;
        
        $where_clause = "WHERE event_year = %d";
        $all_registrations_for_year = $wpdb->get_results($wpdb->prepare("SELECT status, spouse_status, child_status, child_details, total_fee FROM {$this->table_name} $where_clause", $selected_year));
        
        $status_counts = ['Pending' => 0, 'Paid' => 0, 'Cancelled' => 0];
        $spouse_count = 0; $child_count = 0;
        $total_paid_amount = 0;

        foreach ($all_registrations_for_year as $reg) {
            if (isset($status_counts[$reg->status])) {
                $status_counts[$reg->status]++;
                if ($reg->status === 'Paid') {
                    $total_paid_amount += (float)$reg->total_fee;
                }
            }
            if ($reg->spouse_status === 'Yes') { $spouse_count++; }
            if ($reg->child_status === 'Yes' && !empty($reg->child_details)) {
                $children = json_decode($reg->child_details, true);
                if (is_array($children)) { $child_count += count($children); }
            }
        }
        $total_registrations = count($all_registrations_for_year);
        $batch_counts = $wpdb->get_results($wpdb->prepare("SELECT batch, COUNT(id) as count FROM {$this->table_name} $where_clause GROUP BY batch ORDER BY count DESC", $selected_year));
        
        require_once REUNION_REG_PLUGIN_DIR . 'views/admin-reports-view.php';
    }
    
    public function settings_page() {
        if (isset($_POST['reunion_save_settings']) && check_admin_referer('reunion_settings_nonce')) {
            // Save all settings
            update_option('reunion_logo_url', sanitize_url($_POST['reunion_logo_url']));
            update_option('reunion_registration_fee', sanitize_text_field($_POST['reunion_registration_fee']));
            update_option('reunion_spouse_fee', sanitize_text_field($_POST['reunion_spouse_fee']));
            update_option('reunion_child_fee', sanitize_text_field($_POST['reunion_child_fee']));
            update_option('reunion_tshirt_sizes', sanitize_text_field($_POST['reunion_tshirt_sizes']));
            update_option('reunion_current_event_year', intval($_POST['reunion_current_event_year']));
            update_option('reunion_bkash_details', sanitize_text_field($_POST['reunion_bkash_details']));
            update_option('reunion_bank_details', wp_kses_post($_POST['reunion_bank_details']));
            
            // Save Registration ID Format
            update_option('reunion_registration_id_format', sanitize_text_field($_POST['reunion_registration_id_format'] ?? 'YEAR0001'));
            
            // Save bKash charge settings
            update_option('reunion_bkash_charge_registration', sanitize_text_field($_POST['reunion_bkash_charge_registration'] ?? '0'));
            update_option('reunion_bkash_charge_spouse', sanitize_text_field($_POST['reunion_bkash_charge_spouse'] ?? '0'));
            update_option('reunion_bkash_charge_child', sanitize_text_field($_POST['reunion_bkash_charge_child'] ?? '0'));
            
            add_action('admin_notices', function(){ 
                echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>'; 
            });
        }
        
        $settings = [
            'logo_url' => get_option('reunion_logo_url', ''), 
            'reg_fee' => get_option('reunion_registration_fee', '2000'),
            'spouse_fee' => get_option('reunion_spouse_fee', '1000'), 
            'child_fee' => get_option('reunion_child_fee', '500'),
            'tshirt_sizes' => get_option('reunion_tshirt_sizes', 'S,M,L,XL,XXL'),
            'event_year' => get_option('reunion_current_event_year', date('Y')), 
            'bkash_details' => get_option('reunion_bkash_details', ''),
            'bank_details' => get_option('reunion_bank_details', ''),
            'registration_id_format' => get_option('reunion_registration_id_format', 'YEAR0001'),
            'bkash_charge_registration' => get_option('reunion_bkash_charge_registration', '0'),
            'bkash_charge_spouse' => get_option('reunion_bkash_charge_spouse', '0'),
            'bkash_charge_child' => get_option('reunion_bkash_charge_child', '0'),
        ];
        
        require_once REUNION_REG_PLUGIN_DIR . 'views/admin-settings-view.php';
    }

    /**
     * Generate Registration ID based on format - SIMPLIFIED VERSION
     */
    private function generate_registration_id($event_year) {
        return $this->generate_unique_registration_id($event_year);
    }

    public function registration_form_shortcode() {
        ob_start();
        
        if (null !== self::$newly_registered_record) {
            echo '<div class="reunion-form"><div class="form-message success" style="
    background: #0080001a;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #7eb17e;
    text-align: center;
">' . __('ধন্যবাদ আপনার রেজিস্ট্রেশন সফল হয়েছে। কর্তৃপক্ষ সর্বোচ্চ ২৪ ঘণ্টার মধ্যে আপনার তথ্য যাচাই করে রেজিস্ট্রেশন স্ট্যাটাস "Pending" থেকে "Paid" হিসেবে আপডেট করে দেবে।', 'reunion-reg') . '</div></div>';
            echo $this->acknowledgement_slip_shortcode(['record' => self::$newly_registered_record]);
            self::$newly_registered_record = null;
        } else {
            if (!empty(self::$form_message)) {
                echo self::$form_message;
            }
            $settings = [
                'current_event_year' => get_option('reunion_current_event_year', date('Y')), 
                'reg_fee' => get_option('reunion_registration_fee', '2000'),
                'spouse_fee' => get_option('reunion_spouse_fee', '1000'), 
                'child_fee' => get_option('reunion_child_fee', '500'),
                'tshirt_sizes' => get_option('reunion_tshirt_sizes', 'S,M,L,XL,XXL'),
                'bkash_details' => get_option('reunion_bkash_details', ''), 
                'bank_details' => get_option('reunion_bank_details', ''),
                'bkash_charge_registration' => get_option('reunion_bkash_charge_registration', '0'),
                'bkash_charge_spouse' => get_option('reunion_bkash_charge_spouse', '0'),
                'bkash_charge_child' => get_option('reunion_bkash_charge_child', '0'),
            ];
            require_once REUNION_REG_PLUGIN_DIR . 'views/public-registration-form.php';
        }
        
        return ob_get_clean();
    }
    
    public function rules_page_shortcode() {
        ob_start();
        require_once REUNION_REG_PLUGIN_DIR . 'views/public-rules-view.php';
        return ob_get_clean();
    }

    /**
     * Handle public form submission - UPDATED VERSION
     */
    public function handle_public_form_submission() {
        if (isset($_POST['action']) && $_POST['action'] === 'reunion_register' && check_admin_referer('reunion_reg_nonce')) {
            global $wpdb;
            
            try {
                $data = [];
                
                // Generate unique registration ID
                $event_year = intval($_POST['event_year']);
                $data['unique_id'] = $this->generate_registration_id($event_year);
                
                $data['name'] = sanitize_text_field($_POST['reg_name']);
                $data['father_name'] = sanitize_text_field($_POST['father_name']);
                $data['mother_name'] = sanitize_text_field($_POST['mother_name']);
                $data['profession'] = sanitize_text_field($_POST['profession']);
                $data['blood_group'] = sanitize_text_field($_POST['blood_group']);
                $data['batch'] = intval($_POST['reg_batch']);
                $data['tshirt_size'] = sanitize_text_field($_POST['tshirt_size']);
                $data['event_year'] = $event_year;
                $data['spouse_status'] = sanitize_text_field($_POST['spouse_status']);
                $data['spouse_name'] = ($_POST['spouse_status'] === 'Yes') ? sanitize_text_field($_POST['spouse_name']) : null;
                $data['child_status'] = sanitize_text_field($_POST['child_status']);
                
                $children = [];
                if ($data['child_status'] === 'Yes' && !empty($_POST['child_name'])) {
                    for ($i = 0; $i < count($_POST['child_name']); $i++) { 
                        if (!empty($_POST['child_name'][$i])) {
                            $children[] = [
                                'name' => sanitize_text_field($_POST['child_name'][$i]), 
                                'dob' => sanitize_text_field($_POST['child_age'][$i])
                            ]; 
                        }
                    }
                }
                $data['child_details'] = json_encode($children);
                
                $data['mobile_number'] = sanitize_text_field($_POST['mobile_number']);
                
                // Handle optional profile picture upload
                $data['profile_picture_url'] = '';
                if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    if (!function_exists('wp_handle_upload')) { 
                        require_once(ABSPATH . 'wp-admin/includes/file.php'); 
                    }
                    $movefile = wp_handle_upload($_FILES['profile_picture'], ['test_form' => false]);
                    if ($movefile && !isset($movefile['error'])) { 
                        $data['profile_picture_url'] = $movefile['url']; 
                    }
                }

                $data['payment_method'] = sanitize_text_field($_POST['payment_method']);
                $payment_data = [];
                if ($data['payment_method'] === 'bKash') { 
                    $payment_data = [
                        'bkash_number' => sanitize_text_field($_POST['bkash_number']), 
                        'transaction_id' => sanitize_text_field($_POST['transaction_id'])
                    ]; 
                } elseif ($data['payment_method'] === 'Bank') { 
                    $payment_data = [
                        'bank_account_name' => sanitize_text_field($_POST['bank_account_name']), 
                        'bank_account_number' => sanitize_text_field($_POST['bank_account_number'])
                    ]; 
                }
                $data['payment_details'] = json_encode($payment_data);

                $data['status'] = 'Pending';
                $data['total_fee'] = floatval($_POST['total_fee']);
                $data['registration_date'] = current_time('mysql');

                // Insert into database
                $result = $wpdb->insert($this->table_name, $data);

                if ($result !== false) {
                    $new_record_id = $wpdb->insert_id;
                    $new_record = (object) $data;
                    $new_record->id = $new_record_id;
                    self::$newly_registered_record = $new_record;
                    self::$form_message = '';
                    
                    // Trigger SMS notification
                    do_action('reunion_after_registration', $data, $new_record_id);
                } else { 
                    throw new Exception('Database insertion failed: ' . $wpdb->last_error);
                }
                
            } catch (Exception $e) {
                error_log('Reunion Registration Error: ' . $e->getMessage());
                self::$form_message = '<div class="reunion-form"><div class="form-message error">' . 
                    __('Registration failed due to a technical issue. Please try again. If the problem persists, contact support.', 'reunion-reg') . 
                    '<br><small>Error details have been logged for review.</small></div></div>';
            }
        }
    }
    
    public function acknowledgement_slip_shortcode($atts) {
        $record = $atts['record'] ?? null;
        if (!$record) {
            $search_term = isset($_GET['search_query']) ? sanitize_text_field($_GET['search_query']) : '';
            if ($search_term) { $record = $this->get_record_by_query($search_term); }
        }
        $logo_url = get_option('reunion_logo_url', ''); $logo_base64 = '';
        if ($logo_url) {
            $response = wp_remote_get($logo_url);
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $image_data = wp_remote_retrieve_body($response); $mime_type = wp_remote_retrieve_header($response, 'content-type');
                if ($mime_type && $image_data) { $logo_base64 = 'data:' . $mime_type . ';base64,' . base64_encode($image_data); }
            }
        }
        ob_start();
        include REUNION_REG_PLUGIN_DIR . 'views/public-slip-view.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for checking SMS balance - FIXED VERSION
     */
    public function ajax_check_sms_balance() {
        check_ajax_referer('reunion_sms_ajax', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $sms = Reunion_SMS::get_instance();
        $result = $sms->get_sms_balance();
        
        if ($result['status'] === 'success') {
            $balance = $result['balance'];
            
            // Ensure balance is a string/number, not an object
            if (is_array($balance) || is_object($balance)) {
                // If it's still an object/array, try to extract the balance
                if (is_array($balance) && isset($balance['balance'])) {
                    $balance = $balance['balance'];
                } elseif (is_object($balance) && isset($balance->balance)) {
                    $balance = $balance->balance;
                } else {
                    $balance = 'Unable to parse balance';
                }
            }
            
            wp_send_json_success(['balance' => $balance]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * AJAX handler for sending test SMS
     */
    public function ajax_send_test_sms() {
        check_ajax_referer('reunion_sms_ajax', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $phone = sanitize_text_field($_POST['phone']);
        if (empty($phone)) {
            wp_send_json_error(['message' => 'Phone number is required']);
        }
        
        $sms = Reunion_SMS::get_instance();
        $result = $sms->send_test_sms($phone);
        
        if ($result['status'] === 'success') {
            wp_send_json_success(['message' => $result['message']]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
    
    /**
     * SMS Settings Page
     */
    public function sms_settings_page() {
        if (isset($_POST['reunion_save_sms_settings']) && check_admin_referer('reunion_sms_settings_nonce')) {
            update_option('reunion_sms_enabled', isset($_POST['reunion_sms_enabled']) ? 'yes' : 'no');
            update_option('reunion_sms_token', sanitize_text_field($_POST['reunion_sms_token']));
            update_option('reunion_sms_registration_template', wp_kses_post($_POST['reunion_sms_registration_template']));
            update_option('reunion_sms_paid_template', wp_kses_post($_POST['reunion_sms_paid_template']));
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('SMS settings saved successfully!', 'reunion-reg') . '</p></div>';
            });
        }
        
        require_once REUNION_REG_PLUGIN_DIR . 'views/admin-sms-settings-view.php';
    }
    
    /**
     * SMS Logs Page
     */
    public function sms_logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reunion_sms_logs';
        
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name}");
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, r.name, r.unique_id 
             FROM {$table_name} l 
             LEFT JOIN {$this->table_name} r ON l.registration_id = r.id 
             ORDER BY l.sent_at DESC 
             LIMIT %d, %d",
            $offset, $per_page
        ));
        
        require_once REUNION_REG_PLUGIN_DIR . 'views/admin-sms-logs-view.php';
    }
    
    private function get_record_by_id($id) { 
        global $wpdb; 
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)); 
    }
    
    private function get_record_by_query($query) { 
        global $wpdb; 
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE unique_id = %s OR mobile_number = %s", $query, $query)); 
    }

    /**
     * Check if registration ID is unique
     */
    private function is_unique_id_available($unique_id, $exclude_id = null) {
        global $wpdb;
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE unique_id = %s";
        $params = [$unique_id];
        
        if ($exclude_id) {
            $sql .= " AND id != %d";
            $params[] = $exclude_id;
        }
        
        $count = $wpdb->get_var($wpdb->prepare($sql, $params));
        return $count == 0;
    }
    
    /**
     * Generate a truly unique registration ID
     */
    private function generate_unique_registration_id($event_year, $max_attempts = 100) {
        for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
            $unique_id = $this->generate_registration_id_format($event_year, $attempt);
            
            if ($this->is_unique_id_available($unique_id)) {
                return $unique_id;
            }
        }
        
        // Ultimate fallback - should never happen
        return 'REG' . $event_year . '_' . time() . '_' . rand(1000, 9999);
    }
    
    /**
     * Generate registration ID based on format with attempt number
     */
    private function generate_registration_id_format($event_year, $attempt = 1) {
        global $wpdb;
        
        $format = get_option('reunion_registration_id_format', 'YEAR0001');
        
        if (strpos($format, 'YEAR') !== false) {
            // Get the next number for this year
            $latest = $wpdb->get_var($wpdb->prepare(
                "SELECT unique_id FROM {$this->table_name} WHERE event_year = %d AND unique_id LIKE %s ORDER BY id DESC LIMIT 1", 
                $event_year,
                str_replace('YEAR', $event_year, str_replace('0001', '%', $format))
            ));
            
            $next_number = 1;
            if ($latest) {
                // Extract number from existing ID
                preg_match('/(\d+)$/', $latest, $matches);
                if (!empty($matches[1])) {
                    $next_number = intval($matches[1]) + 1;
                }
            }
            
            // Add attempt offset to avoid duplicates
            $next_number += ($attempt - 1);
            
            // Generate new ID
            $number_part = str_pad($next_number, 4, '0', STR_PAD_LEFT);
            $unique_id = str_replace('YEAR', $event_year, $format);
            $unique_id = preg_replace('/0+$/', $number_part, $unique_id);
            
            return $unique_id;
        }
        
        // Fallback format
        return $event_year . str_pad($attempt, 4, '0', STR_PAD_LEFT);
    }
}