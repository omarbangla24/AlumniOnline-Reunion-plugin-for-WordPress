<?php
/**
 * Main Reunion Registration Plugin Class.
 */
final class Reunion_Registration_Plugin {

    private static $instance;
    private $table_name;
    private $db_version = '1.4';
    private static $form_message = '';
    
    // ১. এই নতুন লাইনটি যোগ করা হয়েছে। এটি নতুন রেজিস্ট্রেশনের তথ্য ধরে রাখে।
    private static $newly_registered_record = null;

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'reunion_registrations';
        $this->load_hooks();
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
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        add_shortcode('reunion_registration_form', [$this, 'registration_form_shortcode']);
        add_shortcode('reunion_acknowledgement_slip', [$this, 'acknowledgement_slip_shortcode']);
        add_shortcode('reunion_rules_page', [$this, 'rules_page_shortcode']);
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
    }

    public function admin_page_router() {
        $this->handle_page_actions();
        if (isset($_GET['view_id'])) { $this->display_single_entry(intval($_GET['view_id'])); } 
        elseif (isset($_GET['edit_id'])) { $this->display_edit_form(intval($_GET['edit_id'])); } 
        else { $this->display_list_page(); }
    }
    
    public function handle_admin_redirect_actions() {
        global $wpdb;
        if (isset($_GET['page']) && $_GET['page'] === 'reunion-registrations' && isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
            if (check_admin_referer('reunion_status_update_' . $_GET['id'])) {
                $wpdb->update($this->table_name, ['status' => sanitize_text_field($_GET['new_status'])], ['id' => intval($_GET['id'])]);
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
    
    private function handle_edit_submission() { 
        global $wpdb;
        $id = intval($_POST['registration_id']);
        $data = [
            'name' => sanitize_text_field($_POST['reg_name']),
            'father_name' => sanitize_text_field($_POST['father_name']),
            'mother_name' => sanitize_text_field($_POST['mother_name']),
            'profession' => sanitize_text_field($_POST['profession']),
            'blood_group' => sanitize_text_field($_POST['blood_group']),
            'batch' => intval($_POST['reg_batch']), 'event_year' => intval($_POST['event_year']),
            'tshirt_size' => sanitize_text_field($_POST['tshirt_size']), 'spouse_status' => sanitize_text_field($_POST['spouse_status']), 
            'spouse_name' => ($_POST['spouse_status'] === 'Yes') ? sanitize_text_field($_POST['spouse_name']) : null,
            'child_status' => sanitize_text_field($_POST['child_status']), 'mobile_number' => sanitize_text_field($_POST['mobile_number']),
            'payment_method' => sanitize_text_field($_POST['payment_method']), 'total_fee' => floatval($_POST['total_fee'])
        ];
        $children = [];
        if ($data['child_status'] === 'Yes' && !empty($_POST['child_name'])) {
            for ($i = 0; $i < count($_POST['child_name']); $i++) { if (!empty($_POST['child_name'][$i])) { $children[] = ['name' => sanitize_text_field($_POST['child_name'][$i]), 'dob' => sanitize_text_field($_POST['child_age'][$i])]; } }
        }
        $data['child_details'] = json_encode($children);
        $payment_data = [];
        if ($data['payment_method'] === 'bKash') { $payment_data = ['bkash_number' => sanitize_text_field($_POST['bkash_number']), 'transaction_id' => sanitize_text_field($_POST['transaction_id'])]; } 
        elseif ($data['payment_method'] === 'Bank') { $payment_data = ['bank_account_name' => sanitize_text_field($_POST['bank_account_name']), 'bank_account_number' => sanitize_text_field($_POST['bank_account_number'])]; }
        $data['payment_details'] = json_encode($payment_data);
        
        if ($wpdb->update($this->table_name, $data, ['id' => $id])) { add_action('admin_notices', function(){ echo '<div class="notice notice-success is-dismissible"><p>' . __('Registration updated successfully!', 'reunion-reg') . '</p></div>'; }); } 
        else { add_action('admin_notices', function(){ echo '<div class="notice notice-error is-dismissible"><p>' . __('Update failed.', 'reunion-reg') . '</p></div>'; }); }
    }

    private function display_list_page() {
        global $wpdb;
        $this->handle_page_actions();
        if (isset($_GET['status_updated'])) { add_action('admin_notices', function(){ echo '<div class="notice notice-success is-dismissible"><p>' . __('Status updated successfully!', 'reunion-reg') . '</p></div>'; }); }
        
        $search_term = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : ''; $batch_filter = isset($_REQUEST['batch_filter']) ? intval($_REQUEST['batch_filter']) : ''; $year_filter = isset($_REQUEST['year_filter']) ? intval($_REQUEST['year_filter']) : '';
        $where = []; $params = [];
        if ($search_term) { $where[] = "(name LIKE %s OR unique_id LIKE %s OR mobile_number LIKE %s)"; $like = '%' . $wpdb->esc_like($search_term) . '%'; $params[] = $like; $params[] = $like; $params[] = $like; }
        if ($batch_filter) { $where[] = "batch = %d"; $params[] = $batch_filter; }
        if ($year_filter) { $where[] = "event_year = %d"; $params[] = $year_filter; }
        $sql_where = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $per_page = 20; $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1; $offset = ($current_page - 1) * $per_page;
        $total_items = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$this->table_name} {$sql_where}", $params));
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table_name} {$sql_where} ORDER BY registration_date DESC LIMIT %d, %d", array_merge($params, [$offset, $per_page])));
        $batches = $wpdb->get_col("SELECT DISTINCT batch FROM {$this->table_name} ORDER BY batch DESC");
        $event_years = $wpdb->get_col("SELECT DISTINCT event_year FROM {$this->table_name} ORDER BY event_year DESC");
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
            update_option('reunion_logo_url', sanitize_url($_POST['reunion_logo_url']));
            update_option('reunion_registration_fee', sanitize_text_field($_POST['reunion_registration_fee']));
            update_option('reunion_spouse_fee', sanitize_text_field($_POST['reunion_spouse_fee']));
            update_option('reunion_child_fee', sanitize_text_field($_POST['reunion_child_fee']));
            update_option('reunion_tshirt_sizes', sanitize_text_field($_POST['reunion_tshirt_sizes']));
            update_option('reunion_current_event_year', intval($_POST['reunion_current_event_year']));
            update_option('reunion_bkash_details', sanitize_text_field($_POST['reunion_bkash_details']));
            update_option('reunion_bank_details', wp_kses_post($_POST['reunion_bank_details']));
            add_action('admin_notices', function(){ echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>'; });
        }
        $settings = [
            'logo_url' => get_option('reunion_logo_url', ''), 'reg_fee' => get_option('reunion_registration_fee', '2000'),
            'spouse_fee' => get_option('reunion_spouse_fee', '1000'), 'child_fee' => get_option('reunion_child_fee', '500'),
            'tshirt_sizes' => get_option('reunion_tshirt_sizes', 'S,M,L,XL,XXL'),
            'event_year' => get_option('reunion_current_event_year', date('Y')), 'bkash_details' => get_option('reunion_bkash_details', ''),
            'bank_details' => get_option('reunion_bank_details', ''),
        ];
        require_once REUNION_REG_PLUGIN_DIR . 'views/admin-settings-view.php';
    }

    // ২. এই সম্পূর্ণ ফাংশনটি নতুন কোড দিয়ে প্রতিস্থাপন করা হয়েছে।
    public function registration_form_shortcode() {
        ob_start();
        
        if (null !== self::$newly_registered_record) {
            echo '<div class="reunion-form"><div class="form-message success" style="
    background: #0080001a;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #7eb17e;
    text-align: center;
">' . __('ধন্যবাদ আপনার রেজিস্ট্রেশন সফল হয়েছে। কর্তৃপক্ষ সর্বোচ্চ ২৪ ঘণ্টার মধ্যে আপনার তথ্য যাচাই করে রেজিস্ট্রেশন স্ট্যাটাস "Pending" থেকে "Paid" হিসেবে আপডেট করে দেবে।', 'reunion-reg') . '</div></div>';
            echo $this->acknowledgement_slip_shortcode(['record' => self::$newly_registered_record]);
            self::$newly_registered_record = null;
        } else {
            if (!empty(self::$form_message)) {
                echo self::$form_message;
            }
            $settings = [
                'current_event_year' => get_option('reunion_current_event_year', date('Y')), 'reg_fee' => get_option('reunion_registration_fee', '2000'),
                'spouse_fee' => get_option('reunion_spouse_fee', '1000'), 'child_fee' => get_option('reunion_child_fee', '500'),
                'tshirt_sizes' => get_option('reunion_tshirt_sizes', 'S,M,L,XL,XXL'),
                'bkash_details' => get_option('reunion_bkash_details', ''), 'bank_details' => get_option('reunion_bank_details', ''),
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

    // ৩. এই ফাংশনের ভেতরের if ব্লকের কোড পরিবর্তন করা হয়েছে।
 // Updated handle_public_form_submission method - replace in main plugin class
    public function handle_public_form_submission() {
        if (isset($_POST['action']) && $_POST['action'] === 'reunion_register' && check_admin_referer('reunion_reg_nonce')) {
            global $wpdb;
            $data = [];
            
            $data['unique_id'] = time();
            $data['name'] = sanitize_text_field($_POST['reg_name']);
            $data['father_name'] = sanitize_text_field($_POST['father_name']);
            $data['mother_name'] = sanitize_text_field($_POST['mother_name']);
            $data['profession'] = sanitize_text_field($_POST['profession']);
            $data['blood_group'] = sanitize_text_field($_POST['blood_group']);
            $data['batch'] = intval($_POST['reg_batch']);
            $data['tshirt_size'] = sanitize_text_field($_POST['tshirt_size']);
            $data['event_year'] = intval($_POST['event_year']);
            $data['spouse_status'] = sanitize_text_field($_POST['spouse_status']);
            $data['spouse_name'] = ($_POST['spouse_status'] === 'Yes') ? sanitize_text_field($_POST['spouse_name']) : null;
            $data['child_status'] = sanitize_text_field($_POST['child_status']);
            
            $children = [];
            if ($data['child_status'] === 'Yes' && !empty($_POST['child_name'])) {
                for ($i = 0; $i < count($_POST['child_name']); $i++) { 
                    $children[] = [
                        'name' => sanitize_text_field($_POST['child_name'][$i]), 
                        'dob' => sanitize_text_field($_POST['child_age'][$i])
                    ]; 
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

            if ($wpdb->insert($this->table_name, $data)) {
                $new_record = (object) $data;
                $new_record->profile_picture_url = $data['profile_picture_url'];
                self::$newly_registered_record = $new_record;
                self::$form_message = '';
            } else { 
                self::$form_message = '<div class="reunion-form"><div class="form-message error">' . __('Registration failed. Database Error: ' . $wpdb->last_error, 'reunion-reg') . '</div></div>';
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
    
    private function get_record_by_id($id) { global $wpdb; return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)); }
    private function get_record_by_query($query) { global $wpdb; return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE unique_id = %s OR mobile_number = %s", $query, $query)); }
}
