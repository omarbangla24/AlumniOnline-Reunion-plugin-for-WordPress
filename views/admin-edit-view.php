<div class="wrap">
    <h1><?php _e('Edit Registration', 'reunion-reg'); ?></h1>
    <a href="?page=reunion-registrations" class="button" style="margin-bottom: 20px;">&larr; Back to Registrations</a>
    <form method="post" action="?page=reunion-registrations">
        <input type="hidden" name="action" value="edit_registration">
        <input type="hidden" name="registration_id" value="<?php echo esc_attr($record->id); ?>">
        <?php wp_nonce_field('reunion_edit_nonce_' . $record->id); ?>
        <table class="form-table">
            <tr><th scope="row"><label for="event_year">Event Year</label></th><td><input type="number" name="event_year" value="<?php echo esc_attr($record->event_year); ?>" class="regular-text" required></td></tr>
            <tr><th scope="row"><label for="reg-name">Name</label></th><td><input type="text" name="reg_name" value="<?php echo esc_attr($record->name); ?>" class="regular-text" required></td></tr>
            <tr><th scope="row"><label for="father_name">Father's Name <small style="color:#666;">(Optional)</small></label></th><td><input type="text" name="father_name" value="<?php echo esc_attr($record->father_name); ?>" class="regular-text"></td></tr>
            <tr><th scope="row"><label for="mother_name">Mother's Name <small style="color:#666;">(Optional)</small></label></th><td><input type="text" name="mother_name" value="<?php echo esc_attr($record->mother_name); ?>" class="regular-text"></td></tr>
            <tr><th scope="row"><label for="profession">Profession <small style="color:#666;">(Optional)</small></label></th><td><input type="text" name="profession" value="<?php echo esc_attr($record->profession); ?>" class="regular-text"></td></tr>
            <tr>
                <th scope="row"><label for="blood_group">Blood Group <small style="color:#666;">(Optional)</small></label></th>
                <td>
                    <select name="blood_group" class="regular-text">
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
            <tr><th scope="row"><label for="reg-batch">Batch</label></th><td><select name="reg_batch" required><?php $cy=date('Y');for($i=$cy;$i>=1997;$i--):?><option value="<?php echo $i;?>" <?php selected($record->batch, $i);?>><?php echo $i;?></option><?php endfor;?></select></td></tr>
            <tr><th scope="row"><label for="mobile-number">Mobile Number</label></th><td><input type="tel" name="mobile_number" value="<?php echo esc_attr($record->mobile_number); ?>" class="regular-text" required></td></tr>
            <tr><th scope="row">Spouse Attending?</th><td><label><input type="radio" name="spouse_status" value="Yes" <?php checked($record->spouse_status, 'Yes'); ?> onclick="toggleVisibility('spouse-details', true)"> Yes</label> <label><input type="radio" name="spouse_status" value="No" <?php checked($record->spouse_status, 'No'); ?> onclick="toggleVisibility('spouse-details', false)"> No</label></td></tr>
            <tr id="spouse-details" class="<?php if($record->spouse_status!=='Yes') echo 'hidden';?>"><th scope="row"><label for="spouse-name">Spouse Name</label></th><td><input type="text" name="spouse_name" value="<?php echo esc_attr($record->spouse_name); ?>" class="regular-text"></td></tr>
            <tr><th scope="row">Child Attending?</th><td><label><input type="radio" name="child_status" value="Yes" <?php checked($record->child_status, 'Yes'); ?> onclick="toggleVisibility('child-details', true)"> Yes</label> <label><input type="radio" name="child_status" value="No" <?php checked($record->child_status, 'No'); ?> onclick="toggleVisibility('child-details', false)"> No</label></td></tr>
            <tr id="child-details" class="<?php if($record->child_status!=='Yes') echo 'hidden';?>"><th scope="row">Child Details</th><td><div id="child-list"><?php if(!empty($child_details)){foreach($child_details as $child){echo '<div><label>Name:</label> <input type="text" name="child_name[]" value="'.esc_attr($child['name']).'" required> <label>Date of Birth:</label> <input type="date" name="child_age[]" value="'.esc_attr($child['dob']).'" required> <button type="button" class="button" onclick="this.parentElement.remove()">X</button></div>';}}?></div><button type="button" class="button" onclick="addChildEntry()">Add Child</button></td></tr>
            <tr><th scope="row">Payment Option</th><td><select name="payment_method" onchange="togglePaymentFields(this.value)" required><option value="bKash" <?php selected($record->payment_method,'bKash');?>>bKash</option><option value="Bank" <?php selected($record->payment_method,'Bank');?>>Bank Transfer</option></select></td></tr>
            <tr id="bkash-fields" class="<?php if($record->payment_method!=='bKash') echo 'hidden';?>"><th scope="row">bKash Details</th><td><label>Number: <input type="text" name="bkash_number" value="<?php echo esc_attr($payment_details['bkash_number']??'');?>"></label><br><label>TrxID: <input type="text" name="transaction_id" value="<?php echo esc_attr($payment_details['transaction_id']??'');?>"></label></td></tr>
            <tr id="bank-fields" class="<?php if($record->payment_method!=='Bank') echo 'hidden';?>"><th scope="row">Bank Details</th><td><label>A/C Name: <input type="text" name="bank_account_name" value="<?php echo esc_attr($payment_details['bank_account_name']??'');?>"></label><br><label>A/C Number: <input type="text" name="bank_account_number" value="<?php echo esc_attr($payment_details['bank_account_number']??'');?>"></label></td></tr>
        </table>
        <p class="submit"><button type="submit" class="button button-primary">Save Changes</button></p>
    </form>
    <style>.hidden{display:none !important;}</style><script>function toggleVisibility(id,show){const el=document.getElementById(id);if(show)el.classList.remove('hidden');else el.classList.add('hidden');} function togglePaymentFields(method){document.getElementById('bkash-fields').classList.add('hidden');document.getElementById('bank-fields').classList.add('hidden');if(method==='bKash')document.getElementById('bkash-fields').classList.remove('hidden');else if(method==='Bank')document.getElementById('bank-fields').classList.remove('hidden');} function addChildEntry(){const list=document.getElementById('child-list');const newEntry=document.createElement('div');newEntry.innerHTML='<label>Name:</label> <input type="text" name="child_name[]" required> <label>Date of Birth:</label> <input type="date" name="child_age[]" required> <button type="button" class="button" onclick="this.parentElement.remove()">X</button>';list.appendChild(newEntry);}</script>
</div>