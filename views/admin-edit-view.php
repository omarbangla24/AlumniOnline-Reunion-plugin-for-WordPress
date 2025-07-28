<?php
/**
 * Complete Admin Edit View File
 * File: views/admin-edit-view.php
 */

// Don't access this file directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Edit Registration', 'reunion-reg'); ?></h1>
    <a href="?page=reunion-registrations" class="button" style="margin-bottom: 20px;">&larr; Back to Registrations</a>
    
    <?php if (!empty($record->profile_picture_url)): ?>
        <div style="margin-bottom: 20px; text-align: center;">
            <h3>Current Profile Picture</h3>
            <img src="<?php echo esc_url($record->profile_picture_url); ?>" alt="Profile Picture" style="max-width: 150px; max-height: 150px; border: 2px solid #ddd; border-radius: 8px;">
        </div>
    <?php endif; ?>
    
    <form method="post" action="?page=reunion-registrations" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_registration">
        <input type="hidden" name="registration_id" value="<?php echo esc_attr($record->id); ?>">
        <?php wp_nonce_field('reunion_edit_nonce_' . $record->id); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="unique_id">Registration ID <span style="color: red;">*</span></label></th>
                <td>
                    <input type="text" name="unique_id" id="unique_id" value="<?php echo esc_attr($record->unique_id); ?>" class="regular-text" required>
                    <p class="description">You can change the registration ID here. Make sure it's unique.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="event_year">Event Year</label></th>
                <td>
                    <input type="number" name="event_year" id="event_year" value="<?php echo esc_attr($record->event_year); ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="reg-name">Name <span style="color: red;">*</span></label></th>
                <td>
                    <input type="text" name="reg_name" id="reg-name" value="<?php echo esc_attr($record->name); ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="father_name">Father's Name <small style="color:#666;">(Optional)</small></label></th>
                <td>
                    <input type="text" name="father_name" id="father_name" value="<?php echo esc_attr($record->father_name); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="mother_name">Mother's Name <small style="color:#666;">(Optional)</small></label></th>
                <td>
                    <input type="text" name="mother_name" id="mother_name" value="<?php echo esc_attr($record->mother_name); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="profession">Profession <small style="color:#666;">(Optional)</small></label></th>
                <td>
                    <input type="text" name="profession" id="profession" value="<?php echo esc_attr($record->profession); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="blood_group">Blood Group <small style="color:#666;">(Optional)</small></label></th>
                <td>
                    <select name="blood_group" id="blood_group" class="regular-text">
                        <option value="">-- Select Blood Group --</option>
                        <option value="A+" <?php selected($record->blood_group, 'A+'); ?>>A+</option>
                        <option value="A-" <?php selected($record->blood_group, 'A-'); ?>>A-</option>
                        <option value="B+" <?php selected($record->blood_group, 'B+'); ?>>B+</option>
                        <option value="B-" <?php selected($record->blood_group, 'B-'); ?>>B-</option>
                        <option value="AB+" <?php selected($record->blood_group, 'AB+'); ?>>AB+</option>
                        <option value="AB-" <?php selected($record->blood_group, 'AB-'); ?>>AB-</option>
                        <option value="O+" <?php selected($record->blood_group, 'O+'); ?>>O+</option>
                        <option value="O-" <?php selected($record->blood_group, 'O-'); ?>>O-</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="reg-batch">Batch <span style="color: red;">*</span></label></th>
                <td>
                    <select name="reg_batch" id="reg-batch" required>
                        <?php $cy = date('Y'); for($i = $cy; $i >= 1997; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php selected($record->batch, $i); ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="tshirt_size">T-Shirt Size <span style="color: red;">*</span></label></th>
                <td>
                    <input type="text" name="tshirt_size" id="tshirt_size" value="<?php echo esc_attr($record->tshirt_size); ?>" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="mobile-number">Mobile Number <span style="color: red;">*</span></label></th>
                <td>
                    <input type="tel" name="mobile_number" id="mobile-number" value="<?php echo esc_attr($record->mobile_number); ?>" class="regular-text" required>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="profile_picture_url">Profile Picture URL</label></th>
                <td>
                    <input type="url" name="profile_picture_url" id="profile_picture_url" value="<?php echo esc_attr($record->profile_picture_url); ?>" class="regular-text">
                    <p class="description">You can change the profile picture URL here.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="new_profile_picture">Upload New Profile Picture</label></th>
                <td>
                    <input type="file" name="new_profile_picture" id="new_profile_picture" accept="image/*">
                    <p class="description">Upload a new profile picture to replace the current one.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Spouse Attending? <span style="color: red;">*</span></th>
                <td>
                    <label>
                        <input type="radio" name="spouse_status" value="Yes" <?php checked($record->spouse_status, 'Yes'); ?> onclick="toggleVisibility('spouse-details', true)"> Yes
                    </label>
                    <label>
                        <input type="radio" name="spouse_status" value="No" <?php checked($record->spouse_status, 'No'); ?> onclick="toggleVisibility('spouse-details', false)"> No
                    </label>
                </td>
            </tr>
            <tr id="spouse-details" class="<?php if($record->spouse_status !== 'Yes') echo 'hidden'; ?>">
                <th scope="row"><label for="spouse-name">Spouse Name</label></th>
                <td>
                    <input type="text" name="spouse_name" id="spouse-name" value="<?php echo esc_attr($record->spouse_name); ?>" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row">Child Attending? <span style="color: red;">*</span></th>
                <td>
                    <label>
                        <input type="radio" name="child_status" value="Yes" <?php checked($record->child_status, 'Yes'); ?> onclick="toggleVisibility('child-details', true)"> Yes
                    </label>
                    <label>
                        <input type="radio" name="child_status" value="No" <?php checked($record->child_status, 'No'); ?> onclick="toggleVisibility('child-details', false)"> No
                    </label>
                </td>
            </tr>
            <tr id="child-details" class="<?php if($record->child_status !== 'Yes') echo 'hidden'; ?>">
                <th scope="row">Child Details</th>
                <td>
                    <div id="child-list">
                        <?php 
                        if (!empty($child_details) && is_array($child_details)) {
                            foreach($child_details as $child) {
                                echo '<div class="child-entry">';
                                echo '<label>Name:</label> <input type="text" name="child_name[]" value="' . esc_attr($child['name']) . '" required> ';
                                echo '<label>Date of Birth:</label> <input type="date" name="child_age[]" value="' . esc_attr($child['dob']) . '" required> ';
                                echo '<button type="button" class="button" onclick="this.parentElement.remove()">Remove</button>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button" onclick="addChildEntry()">Add Child</button>
                </td>
            </tr>
            
            <tr>
                <th scope="row">Payment Option <span style="color: red;">*</span></th>
                <td>
                    <select name="payment_method" onchange="togglePaymentFields(this.value)" required>
                        <option value="bKash" <?php selected($record->payment_method, 'bKash'); ?>>bKash</option>
                        <option value="Bank" <?php selected($record->payment_method, 'Bank'); ?>>Bank Transfer</option>
                    </select>
                </td>
            </tr>
            
            <tr id="bkash-fields" class="<?php if($record->payment_method !== 'bKash') echo 'hidden'; ?>">
                <th scope="row">bKash Details</th>
                <td>
                    <label>Number: 
                        <input type="text" name="bkash_number" value="<?php echo esc_attr($payment_details['bkash_number'] ?? ''); ?>">
                    </label><br>
                    <label>TrxID: 
                        <input type="text" name="transaction_id" value="<?php echo esc_attr($payment_details['transaction_id'] ?? ''); ?>">
                    </label>
                </td>
            </tr>
            
            <tr id="bank-fields" class="<?php if($record->payment_method !== 'Bank') echo 'hidden'; ?>">
                <th scope="row">Bank Details</th>
                <td>
                    <label>A/C Name: 
                        <input type="text" name="bank_account_name" value="<?php echo esc_attr($payment_details['bank_account_name'] ?? ''); ?>">
                    </label><br>
                    <label>A/C Number: 
                        <input type="text" name="bank_account_number" value="<?php echo esc_attr($payment_details['bank_account_number'] ?? ''); ?>">
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="total_fee">Total Fee <span style="color: red;">*</span></label></th>
                <td>
                    <input type="number" step="0.01" name="total_fee" id="total_fee" value="<?php echo esc_attr($record->total_fee); ?>" class="regular-text" required> BDT
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="status">Status</label></th>
                <td>
                    <select name="status" id="status" class="regular-text">
                        <option value="Pending" <?php selected($record->status, 'Pending'); ?>>Pending</option>
                        <option value="Paid" <?php selected($record->status, 'Paid'); ?>>Paid</option>
                        <option value="Cancelled" <?php selected($record->status, 'Cancelled'); ?>>Cancelled</option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary">Save Changes</button>
            <a href="?page=reunion-registrations" class="button">Cancel</a>
        </p>
    </form>
</div>

<style>
.hidden { display: none !important; }
.child-entry {
    margin-bottom: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f9f9f9;
}
.child-entry label {
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 5px;
    font-weight: bold;
}
.child-entry input {
    margin-right: 10px;
    margin-bottom: 5px;
}
</style>

<script>
function toggleVisibility(id, show) {
    const el = document.getElementById(id);
    if (show) {
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function togglePaymentFields(method) {
    document.getElementById('bkash-fields').classList.add('hidden');
    document.getElementById('bank-fields').classList.add('hidden');
    
    if (method === 'bKash') {
        document.getElementById('bkash-fields').classList.remove('hidden');
    } else if (method === 'Bank') {
        document.getElementById('bank-fields').classList.remove('hidden');
    }
}

function addChildEntry() {
    const list = document.getElementById('child-list');
    const newEntry = document.createElement('div');
    newEntry.className = 'child-entry';
    newEntry.innerHTML = `
        <label>Name:</label> <input type="text" name="child_name[]" required> 
        <label>Date of Birth:</label> <input type="date" name="child_age[]" required> 
        <button type="button" class="button" onclick="this.parentElement.remove()">Remove</button>
    `;
    list.appendChild(newEntry);
}
</script>