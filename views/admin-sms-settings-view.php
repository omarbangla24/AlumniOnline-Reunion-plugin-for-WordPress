<!-- File: views/admin-sms-settings-view.php -->
<div class="wrap">
    <h1><?php _e('SMS Settings', 'reunion-reg'); ?></h1>
    
    <?php if (isset($_GET['sms_test_sent'])): ?>
        <div class="notice notice-info is-dismissible">
            <p><?php _e('Test SMS has been sent. Please check your phone.', 'reunion-reg'); ?></p>
        </div>
    <?php endif; ?>
    
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <form method="post" action="<?php echo admin_url('admin.php?page=reunion-sms-settings'); ?>">
                    <?php wp_nonce_field('reunion_sms_settings_nonce'); ?>
                    
                    <!-- Enable/Disable SMS -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('SMS Service', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Enable SMS Notifications', 'reunion-reg'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="reunion_sms_enabled" value="yes" <?php checked(get_option('reunion_sms_enabled', 'no'), 'yes'); ?>>
                                            <?php _e('Enable SMS notifications for registrations and payment confirmations', 'reunion-reg'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- API Configuration -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('API Configuration', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="reunion_sms_token"><?php _e('API Token', 'reunion-reg'); ?></label></th>
                                    <td>
                                        <input type="text" id="reunion_sms_token" name="reunion_sms_token" value="<?php echo esc_attr(get_option('reunion_sms_token', '')); ?>" class="regular-text">
                                        <p class="description">
                                            <?php _e('Generate token from:', 'reunion-reg'); ?> 
                                            <a href="https://sms.greenweb.com.bd/index.php?ref=gen_token.php" target="_blank">GreenWeb SMS Panel</a>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('SMS Balance', 'reunion-reg'); ?></th>
                                    <td>
                                        <div id="sms-balance-display">
                                            <span class="spinner" style="float: none; margin: 0;"></span>
                                            <span id="balance-text"><?php _e('Click to check balance', 'reunion-reg'); ?></span>
                                        </div>
                                        <button type="button" class="button" id="check-balance-btn"><?php _e('Check Balance', 'reunion-reg'); ?></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- SMS Templates -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('SMS Templates', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="reunion_sms_registration_template"><?php _e('Registration SMS Template', 'reunion-reg'); ?></label>
                                    </th>
                                    <td>
                                        <textarea id="reunion_sms_registration_template" name="reunion_sms_registration_template" rows="4" class="large-text"><?php echo esc_textarea(get_option('reunion_sms_registration_template', 'Dear {name}, Your reunion registration (ID: {unique_id}) has been received. Fee: {fee} BDT. Status: {status}. Thank you!')); ?></textarea>
                                        <p class="description">
                                            <?php _e('Available variables:', 'reunion-reg'); ?> 
                                            <code>{name}</code>, <code>{unique_id}</code>, <code>{batch}</code>, 
                                            <code>{mobile}</code>, <code>{fee}</code>, <code>{status}</code>, 
                                            <code>{event_year}</code>, <code>{payment_method}</code>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="reunion_sms_paid_template"><?php _e('Payment Confirmed SMS Template', 'reunion-reg'); ?></label>
                                    </th>
                                    <td>
                                        <textarea id="reunion_sms_paid_template" name="reunion_sms_paid_template" rows="4" class="large-text"><?php echo esc_textarea(get_option('reunion_sms_paid_template', 'Dear {name}, Your payment for reunion registration (ID: {unique_id}) has been confirmed. Fee paid: {fee} BDT. See you at the event!')); ?></textarea>
                                        <p class="description">
                                            <?php _e('This SMS will be sent when status is changed to "Paid"', 'reunion-reg'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Test SMS -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Test SMS', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="test_sms_number"><?php _e('Test Phone Number', 'reunion-reg'); ?></label>
                                    </th>
                                    <td>
                                        <input type="tel" id="test_sms_number" name="test_sms_number" value="" class="regular-text" placeholder="01XXXXXXXXX">
                                        <button type="button" class="button" id="send-test-sms-btn"><?php _e('Send Test SMS', 'reunion-reg'); ?></button>
                                        <p class="description"><?php _e('Enter a phone number to test SMS functionality', 'reunion-reg'); ?></p>
                                        <div id="test-sms-result" style="margin-top: 10px;"></div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="reunion_save_sms_settings" class="button button-primary" value="<?php _e('Save SMS Settings', 'reunion-reg'); ?>">
                    </p>
                </form>
            </div>
            
            <!-- Sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="postbox">
                    <h2 class="hndle"><span><?php _e('SMS Service Info', 'reunion-reg'); ?></span></h2>
                    <div class="inside">
                        <p><strong><?php _e('Service Provider:', 'reunion-reg'); ?></strong> GreenWeb</p>
                        <p><strong><?php _e('API Documentation:', 'reunion-reg'); ?></strong> 
                            <a href="http://api.greenweb.com.bd/" target="_blank">View Docs</a>
                        </p>
                        <p><strong><?php _e('SMS Panel:', 'reunion-reg'); ?></strong> 
                            <a href="https://sms.greenweb.com.bd/" target="_blank">Login to Panel</a>
                        </p>
                        <hr>
                        <p><strong><?php _e('Support:', 'reunion-reg'); ?></strong></p>
                        <p>For SMS related issues, contact GreenWeb support.</p>
                    </div>
                </div>
                
                <div class="postbox">
                    <h2 class="hndle"><span><?php _e('SMS Logs', 'reunion-reg'); ?></span></h2>
                    <div class="inside">
                        <p><?php _e('Recent SMS activity:', 'reunion-reg'); ?></p>
                        <?php
                        global $wpdb;
                        $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}reunion_sms_logs ORDER BY sent_at DESC LIMIT 5");
                        if ($logs) {
                            echo '<ul style="font-size: 12px;">';
                            foreach ($logs as $log) {
                                $status_color = ($log->status == 'success') ? 'green' : 'red';
                                echo '<li>';
                                echo '<span style="color: ' . $status_color . ';">' . strtoupper($log->status) . '</span> - ';
                                echo esc_html($log->sms_type) . ' - ';
                                echo date('d M, H:i', strtotime($log->sent_at));
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p style="color: #666;">' . __('No SMS sent yet.', 'reunion-reg') . '</p>';
                        }
                        ?>
                        <hr>
                        <a href="<?php echo admin_url('admin.php?page=reunion-sms-logs'); ?>" class="button button-small">
                            <?php _e('View All Logs', 'reunion-reg'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Check Balance
    $('#check-balance-btn').on('click', function() {
        var btn = $(this);
        var spinner = $('#sms-balance-display .spinner');
        var balanceText = $('#balance-text');
        
        btn.prop('disabled', true);
        spinner.addClass('is-active');
        balanceText.text('Checking...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reunion_check_sms_balance',
                nonce: '<?php echo wp_create_nonce('reunion_sms_ajax'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    balanceText.html('<strong>Balance: ' + response.data.balance + '</strong>');
                } else {
                    balanceText.html('<span style="color: red;">Error: ' + response.data.message + '</span>');
                }
            },
            error: function() {
                balanceText.html('<span style="color: red;">Connection error</span>');
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.removeClass('is-active');
            }
        });
    });
    
    // Send Test SMS
    $('#send-test-sms-btn').on('click', function() {
        var btn = $(this);
        var phoneNumber = $('#test_sms_number').val();
        var resultDiv = $('#test-sms-result');
        
        if (!phoneNumber) {
            resultDiv.html('<div class="notice notice-error"><p>Please enter a phone number</p></div>');
            return;
        }
        
        btn.prop('disabled', true).text('Sending...');
        resultDiv.html('<div class="notice notice-info"><p>Sending SMS...</p></div>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reunion_send_test_sms',
                phone: phoneNumber,
                nonce: '<?php echo wp_create_nonce('reunion_sms_ajax'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                } else {
                    resultDiv.html('<div class="notice notice-error"><p>Error: ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="notice notice-error"><p>Connection error</p></div>');
            },
            complete: function() {
                btn.prop('disabled', false).text('Send Test SMS');
            }
        });
    });
});
</script>

<style>
#sms-balance-display {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.postbox {
    margin-bottom: 20px;
}
</style>