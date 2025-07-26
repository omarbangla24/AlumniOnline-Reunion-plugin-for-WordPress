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
.reunion-form input[type="text"],.reunion-form input[type="tel"],.reunion-form input[type="date"],.reunion-form select{width:100%;padding:10px;border:1px solid #ccc;border-radius:4px;box-sizing: border-box;}
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
</style>
<form class="reunion-form" method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('reunion_reg_nonce'); ?>
    <input type="hidden" name="action" value="reunion_register">
    <input type="hidden" name="event_year" value="<?php echo esc_attr($settings['current_event_year']); ?>">
    <input type="hidden" id="total_fee_input" name="total_fee" value="<?php echo esc_attr($settings['reg_fee']); ?>">

    <div id="step-1" class="form-step active">
        <h2>Step 1: Your Information</h2>
        <div class="form-group"><label>Name of the Student</label><input type="text" name="reg_name" required></div>
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
        <div class="form-group"><label>Batch</label><select name="reg_batch" required><?php $cy=date('Y');for($i=$cy;$i>=1997;$i--):?><option value="<?php echo$i;?>"><?php echo$i;?></option><?php endfor;?></select></div>
        <div class="form-group"><label for="tshirt_size">T-Shirt Size</label><select id="tshirt_size" name="tshirt_size" required><?php $sizes = explode(',', $settings['tshirt_sizes']); foreach($sizes as $size): $size = trim($size); ?><option value="<?php echo esc_attr($size); ?>"><?php echo esc_html($size); ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Spouse Attending?</label><div><label><input type="radio" name="spouse_status" value="Yes"> Yes</label><label><input type="radio" name="spouse_status" value="No" checked> No</label></div></div>
        <div id="spouse-details" class="form-group hidden"><label>Spouse Name</label><input type="text" name="spouse_name"></div>
        <div class="form-group"><label>Child Attending?</label><div><label><input type="radio" name="child_status" value="Yes"> Yes</label><label><input type="radio" name="child_status" value="No" checked> No</label></div></div>
        <div id="child-details" class="form-group hidden"><label>Child Details</label><div id="child-list"></div><button type="button" class="button" onclick="addChildEntry()">Add Child</button></div>
        <div class="form-group"><label>Mobile Number</label><input type="tel" name="mobile_number" required></div>
        <div class="form-group"><label>Profile Picture of the Student <span class="optional-label">(Optional)</span></label><input type="file" name="profile_picture" accept="image/*"></div>

        <!-- Fee Breakdown Display for Step 1 -->
        <div id="fee-breakdown-step1"></div>
        <div class="total-fee-display">Total Payable: <span id="total_fee_text_step1"><?php echo esc_html($settings['reg_fee']); ?></span> BDT</div>

        <div class="form-navigation">
            <span></span>
            <button type="button" id="next-btn" class="nav-btn">Continue to Payment &rarr;</button>
        </div>
    </div>

    <div id="step-2" class="form-step">
        <h2>Step 2: Payment</h2>
        <div class="payment-info-box">
            <h3>Payment Information</h3>
            <!-- Fee Breakdown Display for Step 2 -->
            <div id="fee-breakdown-step2"></div>
            <hr>
            <p>Please complete your payment using one of the methods below before submitting the form.</p>
            <ul>
                <?php if (!empty($settings['bkash_details'])): ?><li><strong>bKash (Send Money):</strong> <?php echo esc_html($settings['bkash_details']); ?></li><?php endif; ?>
                <?php if (!empty($settings['bank_details'])): ?><li><strong>Bank Transfer:</strong><br><?php echo nl2br(esc_html($settings['bank_details'])); ?></li><?php endif; ?>
            </ul>
        </div>
        <div class="form-group"><label>Your Payment Method</label><select name="payment_method" onchange="togglePaymentFields(this.value)" required><option value="">-- Select --</option><option value="bKash">bKash</option><option value="Bank">Bank Transfer</option></select></div>
        <div id="bkash-fields" class="payment-fields hidden"><div class="form-group"><label>Your bKash Number</label><input type="text" name="bkash_number"></div><div class="form-group"><label>Transaction ID</label><input type="text" name="transaction_id"></div></div>
        <div id="bank-fields" class="payment-fields hidden"><div class="form-group"><label>Your Bank Account Name</label><input type="text" name="bank_account_name"></div><div class="form-group"><label>Your Bank Account Number</label><input type="text" name="bank_account_number"></div></div>
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
        child: parseFloat('<?php echo esc_js($settings['child_fee']); ?>') || 0
    };

    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const nextBtn = document.getElementById('next-btn');
    const backBtn = document.getElementById('back-btn');
    const feeBreakdownStep1Div = document.getElementById('fee-breakdown-step1');
    const feeBreakdownStep2Div = document.getElementById('fee-breakdown-step2');

    function calculateAge(dobString) {
        if (!dobString) return 0;
        const dob = new Date(dobString);
        const today = new Date();
        if (isNaN(dob.getTime())) return 0; // Invalid date
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
        
        if (document.querySelector('input[name="spouse_status"]:checked').value === 'Yes') {
            total += feeConfig.spouse;
            breakdown += `<p>Spouse Fee: ${feeConfig.spouse.toFixed(2)} BDT</p>`;
        }

        let childFeeCount = 0;
        if (document.querySelector('input[name="child_status"]:checked').value === 'Yes') {
            document.querySelectorAll('.child-dob-input').forEach(input => {
                if (input.value && calculateAge(input.value) > 5) {
                    childFeeCount++;
                }
            });
        }

        if (childFeeCount > 0) {
            const totalChildFee = childFeeCount * feeConfig.child;
            total += totalChildFee;
            breakdown += `<p>Child Fee (x${childFeeCount} over 5 years): ${totalChildFee.toFixed(2)} BDT</p>`;
        }
        
        const finalBreakdown = breakdown + `<hr><p><strong>Total Payable: ${total.toFixed(2)} BDT</strong></p>`;

        // Update both breakdown divs
        feeBreakdownStep1Div.innerHTML = finalBreakdown;
        feeBreakdownStep2Div.innerHTML = finalBreakdown;

        document.getElementById('total_fee_text_step1').innerText = total.toFixed(2);
        document.getElementById('total_fee_input').value = total.toFixed(2);
    }
    
    // Initial calculation on page load
    calculateTotalFee();

    nextBtn.addEventListener('click', function() {
        let isValid = true;
        step1.querySelectorAll('input[required], select[required]').forEach(input => {
            if (!input.value) {
                input.style.borderColor = 'red';
                isValid = false;
            } else {
                input.style.borderColor = '#ccc';
            }
        });

        if (isValid) {
            calculateTotalFee(); // Ensure fee is up-to-date before going to next step
            step1.classList.remove('active');
            step2.classList.add('active');
        } else {
            alert('Please fill all required fields.');
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
        if (method === 'bKash') document.getElementById('bkash-fields').classList.remove('hidden');
        else if (method === 'Bank') document.getElementById('bank-fields').classList.remove('hidden');
    };

    window.addChildEntry = function() {
        const list = document.getElementById('child-list');
        const newEntry = document.createElement('div');
        newEntry.className = 'child-entry';
        newEntry.innerHTML = `
            <label>Child Name</label><input type="text" name="child_name[]" required> 
            <label>Date of Birth</label><input type="date" name="child_age[]" class="child-dob-input" required> 
            <span class="age-display" style="margin-left:10px; font-size: 0.9em; color: #555;"></span>
            <button type="button" style="margin-left: auto;" class="button" onclick="this.parentElement.remove(); calculateTotalFee();">X</button>`;
        list.appendChild(newEntry);
        // Add event listener to the new input
        newEntry.querySelector('.child-dob-input').addEventListener('change', event => {
            const age = calculateAge(event.target.value);
            event.target.parentElement.querySelector('.age-display').innerText = 'Age: ' + age + ' years';
            calculateTotalFee();
        });
    };

    document.querySelectorAll('input[name="spouse_status"], input[name="child_status"]').forEach(radio => {
        radio.addEventListener('change', calculateTotalFee);
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
        calculateTotalFee();
    });
});
</script>