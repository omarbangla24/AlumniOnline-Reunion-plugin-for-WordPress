<?php
/**
 * Fixed Admin Settings View with All Original Features
 * File: views/admin-settings-view.php
 */

// Don't access this file directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Reunion Settings</h1>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Main Settings', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <form method="post">
                                <?php wp_nonce_field('reunion_settings_nonce'); ?>
                                <input type="hidden" name="reunion_save_settings" value="1">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="reunion_current_event_year">Current Event Year</label></th>
                                        <td>
                                            <input type="number" id="reunion_current_event_year" name="reunion_current_event_year" value="<?php echo esc_attr($settings['event_year']); ?>" class="regular-text">
                                            <p class="description">Set the year for new registrations.</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_registration_id_format">Registration ID Format</label></th>
                                        <td>
                                            <input type="text" id="reunion_registration_id_format" name="reunion_registration_id_format" value="<?php echo esc_attr($settings['registration_id_format']); ?>" class="regular-text">
                                            <p class="description">Format for registration ID. Use "YEAR" for year and "0001" for auto-incrementing number. Example: YEAR0001 will generate 20250001, 20250002, etc.</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_registration_fee">Registration Fee</label></th>
                                        <td>
                                            <input type="text" id="reunion_registration_fee" name="reunion_registration_fee" value="<?php echo esc_attr($settings['reg_fee']); ?>" class="regular-text">
                                            <p class="description">Base fee per person (in BDT).</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_spouse_fee">Spouse Fee</label></th>
                                        <td>
                                            <input type="text" id="reunion_spouse_fee" name="reunion_spouse_fee" value="<?php echo esc_attr($settings['spouse_fee']); ?>" class="regular-text">
                                            <p class="description">Additional fee if spouse attends (in BDT).</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_child_fee">Child Fee (Over 5 years)</label></th>
                                        <td>
                                            <input type="text" id="reunion_child_fee" name="reunion_child_fee" value="<?php echo esc_attr($settings['child_fee']); ?>" class="regular-text">
                                            <p class="description">Additional fee for each child over 5 years old (in BDT).</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row" colspan="2">
                                            <h3 style="margin-top: 30px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">bKash Additional Charges</h3>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_bkash_charge_registration">bKash Charge - Registration Fee</label></th>
                                        <td>
                                            <input type="text" id="reunion_bkash_charge_registration" name="reunion_bkash_charge_registration" value="<?php echo esc_attr($settings['bkash_charge_registration']); ?>" class="regular-text">
                                            <p class="description">Additional bKash service charge for registration fee (in BDT). Set 0 to disable.</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_bkash_charge_spouse">bKash Charge - Spouse Fee</label></th>
                                        <td>
                                            <input type="text" id="reunion_bkash_charge_spouse" name="reunion_bkash_charge_spouse" value="<?php echo esc_attr($settings['bkash_charge_spouse']); ?>" class="regular-text">
                                            <p class="description">Additional bKash service charge for spouse fee (in BDT). Set 0 to disable.</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_bkash_charge_child">bKash Charge - Child Fee</label></th>
                                        <td>
                                            <input type="text" id="reunion_bkash_charge_child" name="reunion_bkash_charge_child" value="<?php echo esc_attr($settings['bkash_charge_child']); ?>" class="regular-text">
                                            <p class="description">Additional bKash service charge per child fee (in BDT). Set 0 to disable.</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row" colspan="2">
                                            <h3 style="margin-top: 30px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">General Settings</h3>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_tshirt_sizes">T-Shirt Sizes</label></th>
                                        <td>
                                            <input type="text" id="reunion_tshirt_sizes" name="reunion_tshirt_sizes" value="<?php echo esc_attr($settings['tshirt_sizes']); ?>" class="regular-text">
                                            <p class="description">Comma-separated sizes (e.g., S,M,L,XL,XXL).</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_bkash_details">bKash Payment Number</label></th>
                                        <td>
                                            <input type="text" id="reunion_bkash_details" name="reunion_bkash_details" value="<?php echo esc_attr($settings['bkash_details']); ?>" class="regular-text">
                                            <p class="description">bKash number for receiving payments (e.g., 01712345678).</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_bank_details">Bank Account Details</label></th>
                                        <td>
                                            <textarea id="reunion_bank_details" name="reunion_bank_details" rows="5" class="large-text"><?php echo esc_textarea($settings['bank_details']); ?></textarea>
                                            <p class="description">Bank account details for bank transfer payments. This will be displayed to users during registration.</p>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <th scope="row"><label for="reunion_logo_url">Logo URL</label></th>
                                        <td>
                                            <input type="url" id="reunion_logo_url" name="reunion_logo_url" value="<?php echo esc_url($settings['logo_url']); ?>" class="regular-text">
                                            <p class="description">Logo URL for acknowledgement slip and forms. Use a direct link to your logo image.</p>
                                            <?php if (!empty($settings['logo_url'])): ?>
                                                <p class="description">
                                                    <strong>Current Logo Preview:</strong><br>
                                                    <img src="<?php echo esc_url($settings['logo_url']); ?>" alt="Logo Preview" style="max-width: 200px; max-height: 100px; border: 1px solid #ddd; margin-top: 10px;">
                                                </p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                                <p class="submit">
                                    <input type="submit" name="reunion_save_settings" class="button button-primary" value="Save Settings">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('How to Use (Shortcodes)', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <p><strong><?php _e('Registration Form:', 'reunion-reg'); ?></strong></p>
                            <p><?php _e('To display the registration form, add this shortcode to any page or post:', 'reunion-reg'); ?></p>
                            <code>[reunion_registration_form]</code>
                            <hr>
                            <p><strong><?php _e('Acknowledgement Slip:', 'reunion-reg'); ?></strong></p>
                            <p><?php _e('To create a page where users can search for and download their acknowledgement slip, use this shortcode:', 'reunion-reg'); ?></p>
                            <code>[reunion_acknowledgement_slip]</code>
                            <hr>
                            <p><strong><?php _e('New Features:', 'reunion-reg'); ?></strong></p>
                            <ul>
                                <li>Profile picture is now mandatory</li>
                                <li>Custom registration ID format</li>
                                <li>bKash extra charge option</li>
                                <li>Status filter in admin panel</li>
                                <li>Excel/PDF export options</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Registration ID Examples', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <p><strong>Format Examples:</strong></p>
                            <ul style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
                                <li><code>YEAR0001</code> → 20250001, 20250002, 20250003...</li>
                                <li><code>REU-YEAR-0001</code> → REU-2025-0001, REU-2025-0002...</li>
                                <li><code>YEAR</code> → 2025001, 2025002, 2025003...</li>
                            </ul>
                            <p><small><strong>Note:</strong> Use "YEAR" placeholder for the event year and "0001" for 4-digit auto-increment numbers.</small></p>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e('Fee Calculation Example', 'reunion-reg'); ?></span></h2>
                        <div class="inside">
                            <p><strong>Example Calculation:</strong></p>
                            <ul style="list-style: none; background: #f9f9f9; padding: 15px; border-radius: 5px;">
                                <li>• Registration Fee: <?php echo esc_html($settings['reg_fee']); ?> BDT</li>
                                <li>• Spouse Fee: <?php echo esc_html($settings['spouse_fee']); ?> BDT</li>
                                <li>• Child Fee (over 5): <?php echo esc_html($settings['child_fee']); ?> BDT each</li>
                                <hr style="margin: 10px 0;">
                                <li><strong>bKash Additional Charges:</strong></li>
                                <li>• Registration: +<?php echo esc_html($settings['bkash_charge_registration']); ?> BDT</li>
                                <li>• Spouse: +<?php echo esc_html($settings['bkash_charge_spouse']); ?> BDT</li>
                                <li>• Child: +<?php echo esc_html($settings['bkash_charge_child']); ?> BDT each</li>
                            </ul>
                            <p><small>bKash charges are only applied when bKash is selected as payment method.</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 5px;">
        <h2><?php _e('Quick Setup Guide', 'reunion-reg'); ?></h2>
        <ol>
            <li><strong>Configure Basic Settings:</strong> Set event year, fees, and registration ID format</li>
            <li><strong>Setup Payment Methods:</strong> Add bKash number and bank details</li>
            <li><strong>Configure SMS:</strong> Go to SMS Settings to enable notifications</li>
            <li><strong>Add Registration Form:</strong> Use <code>[reunion_registration_form]</code> shortcode on a page</li>
            <li><strong>Add Slip Download:</strong> Use <code>[reunion_acknowledgement_slip]</code> shortcode on another page</li>
        </ol>
        
        <h3 style="margin-top: 25px;"><?php _e('Support', 'reunion-reg'); ?></h3>
        <p><?php _e('If you need help or have questions, please visit the plugin support page:', 'reunion-reg'); ?></p>
        <p><a href="https://logicean.com/contact" target="_blank" class="button button-secondary"><?php _e('Visit Support Page', 'reunion-reg'); ?></a></p>
    </div>
</div>

<style>
.form-table th {
    width: 200px;
    padding: 20px 10px 20px 0;
}

.form-table .description {
    font-style: italic;
    color: #666;
    margin-top: 5px;
}

.form-table h3 {
    color: #23282d;
    font-size: 16px;
}

.postbox .inside ul {
    margin: 10px 0;
}

.postbox .inside ul li {
    margin-bottom: 5px;
}

#postbox-container-1 {
    margin-top: 0;
}

.button-primary {
    font-size: 14px !important;
    height: auto !important;
    padding: 8px 16px !important;
}
</style>