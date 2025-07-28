<!-- Complete SMS Settings View File - views/admin-sms-settings-view.php -->
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
                                            <span class="spinner" style="float: none; margin: 0; display: none;"></span>
                                            <span id="balance-text" style="font-weight: bold; color: #0073aa;"><?php _e('Click to check balance', 'reunion-reg'); ?></span>
                                        </div>
                                        <div style="margin-top: 10px;">
                                            <button type="button" class="button" id="check-balance-btn"><?php _e('Check Balance', 'reunion-reg'); ?></button>
                                            <button type="button" class="button" id="refresh-balance-btn" style="display: none;"><?php _e('Refresh', 'reunion-reg'); ?></button>
                                        </div>
                                        <div id="balance-error" style="display: none; margin-top: 10px; padding: 10px; background: #ffeaea; border: 1px solid #d63638; border-radius: 3px;">
                                            <strong>Error:</strong> <span id="balance-error-message"></span>
                                        </div>
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
    // Check Balance - FIXED VERSION
    $('#check-balance-btn, #refresh-balance-btn').on('click', function() {
        var btn = $(this);
        var spinner = $('#sms-balance-display .spinner');
        var balanceText = $('#balance-text');
        var errorDiv = $('#balance-error');
        var errorMessage = $('#balance-error-message');
        var refreshBtn = $('#refresh-balance-btn');
        
        // Hide error div
        errorDiv.hide();
        
        // Disable buttons and show spinner
        $('#check-balance-btn, #refresh-balance-btn').prop('disabled', true);
        spinner.show();
        balanceText.text('Checking balance...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reunion_check_sms_balance',
                nonce: '<?php echo wp_create_nonce('reunion_sms_ajax'); ?>'
            },
            timeout: 30000, // 30 seconds timeout
            success: function(response) {
                if (response.success && response.data && response.data.balance !== undefined) {
                    var balance = response.data.balance;
                    
                    // Ensure balance is properly displayed
                    if (typeof balance === 'object') {
                        // If it's still an object, try to extract balance
                        if (balance.balance !== undefined) {
                            balance = balance.balance;
                        } else {
                            balance = 'Unable to parse balance';
                        }
                    }
                    
                    balanceText.html('<strong style="color: #00a32a;">Balance: ' + balance + ' SMS</strong>');
                    refreshBtn.show();
                } else {
                    var errorMsg = 'Unknown error occurred';
                    if (response.data && response.data.message) {
                        errorMsg = response.data.message;
                    }
                    balanceText.html('<span style="color: #d63638;">Failed to get balance</span>');
                    errorMessage.text(errorMsg);
                    errorDiv.show();
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Connection error';
                if (status === 'timeout') {
                    errorMsg = 'Request timeout. Please try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                } else if (error) {
                    errorMsg = error;
                }
                
                balanceText.html('<span style="color: #d63638;">Connection failed</span>');
                errorMessage.text(errorMsg);
                errorDiv.show();
            },
            complete: function() {
                // Re-enable buttons and hide spinner
                $('#check-balance-btn, #refresh-balance-btn').prop('disabled', false);
                spinner.hide();
            }
        });
    });
    
    // Send Test SMS - IMPROVED VERSION
    $('#send-test-sms-btn').on('click', function() {
        var btn = $(this);
        var phoneNumber = $('#test_sms_number').val();
        var resultDiv = $('#test-sms-result');
        
        if (!phoneNumber) {
            resultDiv.html('<div class="notice notice-error"><p>Please enter a phone number</p></div>');
            return;
        }
        
        // Validate phone number format
        var phoneRegex = /^(\+88)?01[3-9]\d{8}$/;
        if (!phoneRegex.test(phoneNumber.replace(/\s+/g, ''))) {
            resultDiv.html('<div class="notice notice-error"><p>Please enter a valid Bangladesh phone number (01XXXXXXXXX)</p></div>');
            return;
        }
        
        btn.prop('disabled', true).text('Sending...');
        resultDiv.html('<div class="notice notice-info"><p><span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>Sending test SMS...</p></div>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reunion_send_test_sms',
                phone: phoneNumber,
                nonce: '<?php echo wp_create_nonce('reunion_sms_ajax'); ?>'
            },
            timeout: 30000,
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="notice notice-success"><p><strong>✓ Success:</strong> ' + response.data.message + '</p></div>');
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : 'Unknown error occurred';
                    resultDiv.html('<div class="notice notice-error"><p><strong>✗ Error:</strong> ' + errorMsg + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Connection error';
                if (status === 'timeout') {
                    errorMsg = 'Request timeout. Please try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                }
                resultDiv.html('<div class="notice notice-error"><p><strong>✗ Connection Error:</strong> ' + errorMsg + '</p></div>');
            },
            complete: function() {
                btn.prop('disabled', false).text('Send Test SMS');
            }
        });
    });
    
    // Auto-format phone number
    $('#test_sms_number').on('input', function() {
        var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        $(this).val(value);
    });
    
    // Clear results when phone number changes
    $('#test_sms_number').on('input', function() {
        $('#test-sms-result').empty();
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

.spinner.is-active {
    visibility: visible;
}

#balance-error {
    border-left: 4px solid #d63638;
}

.notice {
    margin: 5px 0 15px 0;
    padding: 1px 12px;
}

.form-table th {
    width: 200px;
}

#test_sms_number {
    font-family: monospace;
}

#refresh-balance-btn {
    margin-left: 5px;
}
</style>