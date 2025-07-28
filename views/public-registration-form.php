<?php
/**
 * The template for displaying the public registration form.
 *
 * This template is used by the 'reunion_registration_form' shortcode.
 *
 * @package Reunion_Registration
 */

// Don't access this file directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<style>
.reunion-form{max-width:700px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:8px;background-color:#f9f9f9;}
.reunion-form .payment-info-box{background-color:#eef7ff;border:1px solid #b3d7ff;border-radius:5px;padding:15px;margin-bottom:20px;}
.reunion-form .payment-info-box h3{margin-top:0;color:#005a9c;}
.reunion-form .form-group{margin-bottom:15px;}
.reunion-form label{display:block;font-weight:bold;margin-bottom:5px;}
.reunion-form input[type="text"],.reunion-form input[type="tel"],.reunion-form input[type="date"],.reunion-form input[type="file"],.reunion-form select{width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;box-sizing: border-box;}
.reunion-form input[type="file"]{padding:5px;}
.hidden{display:none;}
.reunion-form #child-list .child-entry{display:flex;gap:10px;align-items:center;margin-bottom:10px; flex-wrap: wrap;}
.reunion-form #child-list .child-entry label { width: 100%; margin-top: 5px; }
.reunion-form .submit-btn, .reunion-form .nav-btn { background-color:#0073aa;color:white;padding:12px 20px;border:none;border-radius:4px;cursor:pointer;font-size:16px;width:auto; }
.reunion-form .form-message.success{background-color:#d4edda;color:#155724;padding:15px;border:1px solid #c3e6cb; border-radius: 5px;}
.reunion-form .form-message.error{background-color:#f8d7da;color:#721c24;padding:15px;border:1px solid #f5c6cb; border-radius: 5px;}
.total-fee-display { font-size: 1.5em; font-weight: bold; color: #d63638; text-align: center; margin: 20px 0; padding: 10px; background: #fff; border-radius: 5px; border: 1px solid #ddd; }
#fee-breakdown-step1, #fee-breakdown-step2 { padding: 10px; border: 1px dashed #ccc; margin-bottom: 20px; border-radius: 5px; background-color: #f0f8ff; }
#fee-breakdown-step1 p, #fee-breakdown-step2 p { margin: 5px 0; }
.form-step { display: none; }
.form-step.active { display: block; }
.form-navigation { display: flex; justify-content: space-between; margin-top: 20px; }
.optional-label { color: #666; font-weight: normal; font-size: 0.9em; }
.required-label { color: #d63638; font-weight: bold; }
.bkash-charge-notice { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; font-size: 14px; }
</style>
<form class="reunion-form" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('reunion_reg_nonce'); ?>
    <input type="hidden" name="action" value="reunion_register">
    <input type="hidden" name="event_year" value="<?php echo esc_attr($settings['current_event_year']); ?>">
    <input type="hidden" id="total_fee_input" name="total_fee" value="<?php echo esc_attr($settings['reg_fee']); ?>">

    <div id="step-1" class="form-step active">
        <h2>Step 1: Your Information</h2>
        <div class="form-group"><label>Name of the Student <span class="required-label">*</span></label><input type="text" name="reg_name" required></div>
        <div class="form-group"><label>Father's Name <span class="optional-label">(Optional)</span></label><input type="text" name="father_name"></div>
        <div class="form-group"><label>Mother's Name <span class="optional-label">(Optional)</span></label><input type="text" name="mother_name"></div>
        <div class="form-group"><label>Profession <span class="optional-label">(Optional)</span></label><input type="text" name="profession"></div>
        <div class="form-group">
            <label>Blood Group <span class="optional-label">(Optional)</span></label>
            <select name="blood_group">
                <option value="">-- Select Blood Group --</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>
        <div class="form-group"><label>Batch <span class="required-label">*</span></label><select name="reg_batch" required><?php $cy=date('Y');for($i=$cy;$i>=1997;$i--):?><option value="<?php echo$i;?>"><?php echo$i;?></option><?php endfor;?></select></div>
        <div class="form-group"><label for="tshirt_size">T-Shirt Size <span class="required-label">*</span></label><select id="tshirt_size" name="tshirt_size" required><?php $sizes = explode(',', $settings['tshirt_sizes']); foreach($sizes as $size): $size = trim($size); ?><option value="<?php echo esc_attr($size); ?>"><?php echo esc_html($size); ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Spouse Attending? <span class="required-label">*</span></label><div><label><input type="radio" name="spouse_status" value="Yes"> Yes</label><label><input type="radio" name="spouse_status" value="No" checked> No</label></div></div>
        <div id="spouse-details" class="form-group hidden"><label>Spouse Name <span class="required-label">*</span></label><input type="text" name="spouse_name"></div>
        <div class="form-group"><label>Child Attending? <span class="required-label">*</span></label><div><label><input type="radio" name="child_status" value="Yes"> Yes</label><label><input type="radio" name="child_status" value="No" checked> No</label></div></div>
        <div id="child-details" class="form-group hidden"><label>Child Details</label><div id="child-list"></div><button type="button" class="button" onclick="addChildEntry()">Add Child</button></div>
        <div class="form-group"><label>Mobile Number <span class="required-label">*</span></label><input type="tel" name="mobile_number" required></div>
        <div class="form-group"><label>Profile Picture of the Student <span class="required-label">*</span></label><input type="file" name="profile_picture" accept="image/*" required><p style="color:#666;font-size:12px;margin-top:5px;">Profile picture is mandatory. Please upload a clear photo.</p></div>

        <div class="form-navigation">
            <span></span>
            <button type="button" id="next-btn" class="nav-btn">Continue to Payment &rarr;</button>
        </div>
    </div>

    <div id="step-2" class="form-step">
        <h2>Step 2: Payment</h2>
        <div class="payment-info-box">
            <h3>Payment Information</h3>
            
            <!-- Registration Fee Display -->
            <div style="background: #f0f8ff; padding: 15px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #b3d7ff;">
                <h4 style="margin: 0 0 10px 0; color: #005a9c;">Registration Fee:</h4>
                <div id="fee-breakdown-step2"></div>
            </div>
            
            <!-- Total Payable Display -->
            <div class="total-fee-display" style="margin: 15px 0;">
                Total Payable: <span id="total_fee_text_step2"><?php echo esc_html($settings['reg_fee']); ?></span> BDT
            </div>
            
            <hr>
            <p>Please complete your payment using one of the methods below before submitting the form.</p>
            <ul>
                <?php if (!empty($settings['bkash_details'])): ?><li><strong>bKash (Send Money):</strong> <?php echo esc_html($settings['bkash_details']); ?></li><?php endif; ?>
                <?php if (!empty($settings['bank_details'])): ?><li><strong>Bank Transfer:</strong><br><?php echo nl2br(esc_html($settings['bank_details'])); ?></li><?php endif; ?>
            </ul>
        </div>
        
        <div id="bkash-charge-notice" class="bkash-charge-notice hidden">
            <strong>Note:</strong> Additional bKash charges will be applied:
            <ul>
                <?php if ($settings['bkash_charge_registration'] > 0): ?>
                <li>Registration Fee: +<?php echo esc_html($settings['bkash_charge_registration']); ?> BDT</li>
                <?php endif; ?>
                <?php if ($settings['bkash_charge_spouse'] > 0): ?>
                <li>Spouse Fee: +<?php echo esc_html($settings['bkash_charge_spouse']); ?> BDT</li>
                <?php endif; ?>
                <?php if ($settings['bkash_charge_child'] > 0): ?>
                <li>Child Fee: +<?php echo esc_html($settings['bkash_charge_child']); ?> BDT per child</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="form-group"><label>Your Payment Method <span class="required-label">*</span></label><select name="payment_method" onchange="togglePaymentFields(this.value)" required><option value="">-- Select --</option><option value="bKash">bKash</option><option value="Bank">Bank Transfer</option></select></div>
        <div id="bkash-fields" class="payment-fields hidden"><div class="form-group"><label>Your bKash Number <span class="required-label">*</span></label><input type="text" name="bkash_number"></div><div class="form-group"><label>Transaction ID <span class="required-label">*</span></label><input type="text" name="transaction_id"></div></div>
        <div id="bank-fields" class="payment-fields hidden"><div class="form-group"><label>Your Bank Account Name <span class="required-label">*</span></label><input type="text" name="bank_account_name"></div><div class="form-group"><label>Your Bank Account Number <span class="required-label">*</span></label><input type="text" name="bank_account_number"></div></div>
        <div class="form-navigation">
            <button type="button" id="back-btn" class="nav-btn">&larr; Back</button>
            <button type="submit" class="submit-btn">Submit Registration</button>
        </div>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const feeConfig = {
        base: parseFloat('<?php echo esc_js($settings['reg_fee']); ?>') || 0,
        spouse: parseFloat('<?php echo esc_js($settings['spouse_fee']); ?>') || 0,
        child: parseFloat('<?php echo esc_js($settings['child_fee']); ?>') || 0,
        bkashChargeRegistration: parseFloat('<?php echo esc_js($settings['bkash_charge_registration']); ?>') || 0,
        bkashChargeSpouse: parseFloat('<?php echo esc_js($settings['bkash_charge_spouse']); ?>') || 0,
        bkashChargeChild: parseFloat('<?php echo esc_js($settings['bkash_charge_child']); ?>') || 0
    };

    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const nextBtn = document.getElementById('next-btn');
    const backBtn = document.getElementById('back-btn');
    const feeBreakdownStep2Div = document.getElementById('fee-breakdown-step2');
    const bkashChargeNotice = document.getElementById('bkash-charge-notice');

    function calculateAge(dobString) {
        if (!dobString) return 0;
        const dob = new Date(dobString);
        const today = new Date();
        if (isNaN(dob.getTime())) return 0;
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        return age;
    }

    function calculateTotalFee() {
        let total = feeConfig.base;
        let breakdown = `<p>Registration Fee: ${feeConfig.base.toFixed(2)} BDT</p>`;
        
        let spouseFee = 0;
        let childFeeCount = 0;
        let totalChildFee = 0;
        
        if (document.querySelector('input[name="spouse_status"]:checked').value === 'Yes') {
            spouseFee = feeConfig.spouse;
            total += spouseFee;
            breakdown += `<p>Spouse Fee: ${spouseFee.toFixed(2)} BDT</p>`;
        }

        if (document.querySelector('input[name="child_status"]:checked').value === 'Yes') {
            document.querySelectorAll('.child-dob-input').forEach(input => {
                if (input.value && calculateAge(input.value) > 5) {
                    childFeeCount++;
                }
            });
        }

        if (childFeeCount > 0) {
            totalChildFee = childFeeCount * feeConfig.child;
            total += totalChildFee;
            breakdown += `<p>Child Fee (x${childFeeCount} over 5 years): ${totalChildFee.toFixed(2)} BDT</p>`;
        }

        // Check if bKash is selected and add separate charges
        const paymentMethod = document.querySelector('select[name="payment_method"]');
        let totalBkashCharge = 0;
        if (paymentMethod && paymentMethod.value === 'bKash') {
            // Registration fee bKash charge
            if (feeConfig.bkashChargeRegistration > 0) {
                totalBkashCharge += feeConfig.bkashChargeRegistration;
                breakdown += `<p>bKash Charge (Registration): ${feeConfig.bkashChargeRegistration.toFixed(2)} BDT</p>`;
            }
            
            // Spouse fee bKash charge
            if (spouseFee > 0 && feeConfig.bkashChargeSpouse > 0) {
                totalBkashCharge += feeConfig.bkashChargeSpouse;
                breakdown += `<p>bKash Charge (Spouse): ${feeConfig.bkashChargeSpouse.toFixed(2)} BDT</p>`;
            }
            
            // Child fee bKash charge
            if (childFeeCount > 0 && feeConfig.bkashChargeChild > 0) {
                const childBkashCharge = childFeeCount * feeConfig.bkashChargeChild;
                totalBkashCharge += childBkashCharge;
                breakdown += `<p>bKash Charge (Child x${childFeeCount}): ${childBkashCharge.toFixed(2)} BDT</p>`;
            }
            
            total += totalBkashCharge;
        }
        
        const finalBreakdown = breakdown + `<hr><p><strong>Total Payable: ${total.toFixed(2)} BDT</strong></p>`;

        // Update only step 2 breakdown
        feeBreakdownStep2Div.innerHTML = finalBreakdown;

        document.getElementById('total_fee_text_step2').innerText = total.toFixed(2);
        document.getElementById('total_fee_input').value = total.toFixed(2);
    }
    
    // No initial calculation on page load for step 1

    nextBtn.addEventListener('click', function() {
        let isValid = true;
        
        // Check required fields
        step1.querySelectorAll('input[required], select[required]').forEach(input => {
            if (!input.value) {
                input.style.borderColor = 'red';
                isValid = false;
            } else {
                input.style.borderColor = '#ccc';
            }
        });
        
        // Check profile picture specifically
        const profilePic = document.querySelector('input[name="profile_picture"]');
        if (!profilePic.files || profilePic.files.length === 0) {
            profilePic.style.borderColor = 'red';
            alert('Profile picture is required. Please upload a profile picture.');
            isValid = false;
        } else {
            profilePic.style.borderColor = '#ccc';
        }

        if (isValid) {
            calculateTotalFee(); // Calculate fee when going to payment step
            step1.classList.remove('active');
            step2.classList.add('active');
        } else {
            alert('Please fill all required fields including profile picture.');
        }
    });

    backBtn.addEventListener('click', function() {
        step2.classList.remove('active');
        step1.classList.add('active');
    });

    function toggleVisibility(id, show) {
        const el = document.getElementById(id);
        if (show) el.classList.remove('hidden');
        else el.classList.add('hidden');
    }

    window.togglePaymentFields = function(method) {
        document.getElementById('bkash-fields').classList.add('hidden');
        document.getElementById('bank-fields').classList.add('hidden');
        bkashChargeNotice.classList.add('hidden');
        
        if (method === 'bKash') {
            document.getElementById('bkash-fields').classList.remove('hidden');
            // Show bKash charge notice if any charges are set
            if (feeConfig.bkashChargeRegistration > 0 || feeConfig.bkashChargeSpouse > 0 || feeConfig.bkashChargeChild > 0) {
                bkashChargeNotice.classList.remove('hidden');
            }
        } else if (method === 'Bank') {
            document.getElementById('bank-fields').classList.remove('hidden');
        }
        
        // Recalculate fee when payment method changes
        calculateTotalFee();
    };

    window.addChildEntry = function() {
        const list = document.getElementById('child-list');
        const newEntry = document.createElement('div');
        newEntry.className = 'child-entry';
        newEntry.innerHTML = `
            <label>Child Name</label><input type="text" name="child_name[]" required> 
            <label>Date of Birth</label><input type="date" name="child_age[]" class="child-dob-input" required> 
            <span class="age-display" style="margin-left:10px; font-size: 0.9em; color: #555;"></span>
            <button type="button" style="margin-left: auto;" class="button" onclick="this.parentElement.remove(); if(document.getElementById('step-2').classList.contains('active')) calculateTotalFee();">X</button>`;
        list.appendChild(newEntry);
        // Add event listener to the new input (only calculate if in step 2)
        newEntry.querySelector('.child-dob-input').addEventListener('change', event => {
            const age = calculateAge(event.target.value);
            event.target.parentElement.querySelector('.age-display').innerText = 'Age: ' + age + ' years';
            // Only calculate if we're in step 2
            if (step2.classList.contains('active')) {
                calculateTotalFee();
            }
        });
    };

    // Event listeners for data input only (no calculation in step 1)
    document.querySelectorAll('input[name="spouse_status"], input[name="child_status"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Only trigger calculation if we're in step 2
            if (!step2.classList.contains('active')) return;
            calculateTotalFee();
        });
    });
    
    document.querySelector('input[name="spouse_status"][value="Yes"]').addEventListener('change', () => toggleVisibility('spouse-details', true));
    document.querySelector('input[name="spouse_status"][value="No"]').addEventListener('change', () => toggleVisibility('spouse-details', false));

    document.querySelector('input[name="child_status"][value="Yes"]').addEventListener('change', () => {
        toggleVisibility('child-details', true);
        if (document.getElementById('child-list').children.length === 0) {
            addChildEntry();
        }
    });
     document.querySelector('input[name="child_status"][value="No"]').addEventListener('change', () => {
        toggleVisibility('child-details', false);
        document.getElementById('child-list').innerHTML = '';
        // Only calculate if we're in step 2
        if (step2.classList.contains('active')) {
            calculateTotalFee();
        }
    });
});
</script>