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
                                <table class="form-table">
                                    <tr><th scope="row"><label for="reunion_current_event_year">Current Event Year</label></th><td><input type="number" id="reunion_current_event_year" name="reunion_current_event_year" value="<?php echo esc_attr($settings['event_year']); ?>" class="regular-text"><p class="description">Set the year for new registrations.</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_registration_fee">Registration Fee</label></th><td><input type="text" id="reunion_registration_fee" name="reunion_registration_fee" value="<?php echo esc_attr($settings['reg_fee']); ?>" class="regular-text"><p class="description">Base fee per person.</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_spouse_fee">Spouse Fee</label></th><td><input type="text" id="reunion_spouse_fee" name="reunion_spouse_fee" value="<?php echo esc_attr($settings['spouse_fee']); ?>" class="regular-text"><p class="description">Additional fee if spouse attends.</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_child_fee">Child Fee (Over 5 years)</label></th><td><input type="text" id="reunion_child_fee" name="reunion_child_fee" value="<?php echo esc_attr($settings['child_fee']); ?>" class="regular-text"><p class="description">Additional fee for each child over 5 years old.</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_tshirt_sizes">T-Shirt Sizes</label></th><td><input type="text" id="reunion_tshirt_sizes" name="reunion_tshirt_sizes" value="<?php echo esc_attr($settings['tshirt_sizes']); ?>" class="regular-text"><p class="description">Comma-separated sizes (e.g., S,M,L,XL,XXL).</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_bkash_details">bKash Payment Number</label></th><td><input type="text" id="reunion_bkash_details" name="reunion_bkash_details" value="<?php echo esc_attr($settings['bkash_details']); ?>" class="regular-text"><p class="description">bKash number for payment.</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_bank_details">Bank Account Details</label></th><td><textarea id="reunion_bank_details" name="reunion_bank_details" rows="5" class="large-text"><?php echo esc_textarea($settings['bank_details']); ?></textarea><p class="description">Bank account details.</p></td></tr>
                                    <tr><th scope="row"><label for="reunion_logo_url">Logo URL</label></th><td><input type="url" id="reunion_logo_url" name="reunion_logo_url" value="<?php echo esc_url($settings['logo_url']); ?>" class="regular-text"><p class="description">Logo URL for acknowledgement slip.</p></td></tr>
                                </table>
                                <p class="submit"><input type="submit" name="reunion_save_settings" class="button button-primary" value="Save Settings"></p>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>

    <div>
             <h2>ShortCode</h2>
        <p><?php _e('You can use the following shortcode to display the registration form on any page:', 'reunion-reg'); ?></p>
        <code>[reunion_registration_form]</code>
        <p><?php _e('To display the acknowledgement slip generation form, use:', 'reunion-reg'); ?></p>
        <code>[reunion_acknowledgement_slip]</code>
        <p><?php _e('To display the admin registration list, use:', 'reunion-reg'); ?></p>
        <code>[reunion_admin_registration_list]</code>
        <p><?php _e('To display the admin reports, use:', 'reunion
-reg'); ?></p>
        <code>[reunion_admin_reports]</code>
        <h2><?php _e('Support', 'reunion-reg'); ?></h2>
        <p><?php _e('If you need help or have questions, please visit the plugin support page:', 'reunion-reg'); ?></p>
        <p><a href="https://logicean.com/contact" target="_blank" class="button button-secondary"><?php _e('Visit Support Page', 'reunion-reg'); ?></a></p>
    </div>
</div>
