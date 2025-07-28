<?php
/**
 * Complete Updated Admin List View
 * File: views/admin-list-view.php
 */

// Don't access this file directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Reunion Registrations', 'reunion-reg'); ?></h1>
    
    <form method="get">
        <input type="hidden" name="page" value="reunion-registrations">
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input">Search Registrations:</label>
            <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="Search by name, ID, or mobile...">
            <input type="submit" class="button" value="Search Registrations">
        </p>
    </form>

    <form method="post">
        <?php wp_nonce_field('reunion_bulk_action_nonce'); ?>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="reunion_bulk_action" id="bulk-action-selector-top">
                    <option value="-1">Bulk Actions</option>
                    <option value="bulk-delete">Delete</option>
                    <option value="bulk-mark-paid">Mark as Paid</option>
                    <option value="bulk-mark-pending">Mark as Pending</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <div class="alignleft actions">
                <select name="batch_filter" onchange="this.form.submit()">
                    <option value=""><?php _e('All Batches', 'reunion-reg'); ?></option>
                    <?php foreach ($batches as $batch) : ?>
                        <option value="<?php echo esc_attr($batch); ?>" <?php selected($batch_filter, $batch); ?>>
                            <?php echo esc_html($batch); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="year_filter" onchange="this.form.submit()">
                    <option value=""><?php _e('All Event Years', 'reunion-reg'); ?></option>
                    <?php foreach ($event_years as $year) : ?>
                        <option value="<?php echo esc_attr($year); ?>" <?php selected($year_filter, $year); ?>>
                            <?php echo esc_html($year); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status_filter" onchange="this.form.submit()">
                    <option value=""><?php _e('All Status', 'reunion-reg'); ?></option>
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?php echo esc_attr($status); ?>" <?php selected($status_filter, $status); ?>>
                            <?php echo esc_html($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="alignright actions">
                <div class="export-actions" style="display: inline-block; margin-left: 10px;">
                    <?php
                    // Build export URL with current filters
                    $export_params = array_filter([
                        'page' => 'reunion-registrations',
                        'export' => 'excel',
                        's' => $search_term,
                        'batch_filter' => $batch_filter,
                        'year_filter' => $year_filter,
                        'status_filter' => $status_filter
                    ]);
                    $excel_url = admin_url('admin.php?' . http_build_query($export_params));
                    
                    $export_params['export'] = 'pdf';
                    $pdf_url = admin_url('admin.php?' . http_build_query($export_params));
                    ?>
                    <a href="<?php echo esc_url($excel_url); ?>" class="button button-secondary export-button excel">📊 Export to Excel</a>
                    <a href="<?php echo esc_url($pdf_url); ?>" class="button button-secondary export-button pdf" target="_blank">📄 Export to PDF</a>
                </div>
            </div>
            <div class="tablenav-pages one-page">
                <span class="displaying-num"><?php echo $total_items; ?> items</span>
            </div>
            <br class="clear">
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th style="width: 40px;">#</th>
                    <th><?php _e('Registration ID', 'reunion-reg'); ?></th>
                    <th><?php _e('Name', 'reunion-reg'); ?></th>
                    <th><?php _e('Batch', 'reunion-reg'); ?></th>
                    <th><?php _e('Payment Details', 'reunion-reg'); ?></th>
                    <th><?php _e('Total Fee', 'reunion-reg'); ?></th>
                    <th><?php _e('Mobile Number', 'reunion-reg'); ?></th>
                    <th><?php _e('Reg. Date', 'reunion-reg'); ?></th>
                    <th><?php _e('Status', 'reunion-reg'); ?></th>
                    <th><?php _e('Actions', 'reunion-reg'); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php if ($results) : 
                    $i = ($current_page - 1) * $per_page; 
                    foreach ($results as $row) : 
                        $i++; 
                ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="registration_ids[]" value="<?php echo $row->id; ?>">
                        </th>
                        <td><?php echo $i; ?></td>
                        <td>
                            <a href="?page=reunion-registrations&view_id=<?php echo $row->id; ?>">
                                <strong class="registration-id"><?php echo esc_html($row->unique_id); ?></strong>
                            </a>
                        </td>
                        <td>
                            <strong><?php echo esc_html($row->name); ?></strong>
                            <div class="row-actions">
                                <?php if (!empty($row->profile_picture_url)): ?>
                                    <span class="has-photo">📷 Has Photo</span>
                                <?php else: ?>
                                    <span class="no-photo">❌ No Photo</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo esc_html($row->batch); ?></strong>
                            <br><small>Event: <?php echo esc_html($row->event_year); ?></small>
                        </td>
                        <td>
                            <div class="payment-info">
                                <?php
                                $payment_method_class = '';
                                if ($row->payment_method === 'bKash') {
                                    $payment_method_class = 'payment-method-bkash';
                                } elseif ($row->payment_method === 'Bank') {
                                    $payment_method_class = 'payment-method-bank';
                                }
                                ?>
                                <span class="<?php echo $payment_method_class; ?>">
                                    <?php echo esc_html($row->payment_method); ?>
                                </span>
                                <br>
                                <small>
                                    <?php
                                    if ($row->payment_details) {
                                        $details = json_decode($row->payment_details, true);
                                        if (is_array($details)) {
                                            if ($row->payment_method === 'bKash') { 
                                                echo '<strong>Number:</strong> ' . esc_html($details['bkash_number'] ?? '') . '<br>';
                                                echo '<strong>TrxID:</strong> ' . esc_html($details['transaction_id'] ?? ''); 
                                            } elseif ($row->payment_method === 'Bank') { 
                                                echo '<strong>A/C Name:</strong> ' . esc_html($details['bank_account_name'] ?? '') . '<br>';
                                                echo '<strong>A/C No:</strong> ' . esc_html($details['bank_account_number'] ?? ''); 
                                            }
                                        }
                                    }
                                    ?>
                                </small>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo esc_html(number_format((float)$row->total_fee, 2)); ?> BDT</strong>
                        </td>
                        <td>
                            <a href="tel:<?php echo esc_attr($row->mobile_number); ?>">
                                <?php echo esc_html($row->mobile_number); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo date('d M Y', strtotime($row->registration_date)); ?>
                            <br><small><?php echo date('h:i a', strtotime($row->registration_date)); ?></small>
                        </td>
                        <td>
                            <?php 
                                $current_status = $row->status;
                                $status_class = '';
                                switch($current_status) {
                                    case 'Paid': $status_class = 'status-paid'; break;
                                    case 'Pending': $status_class = 'status-pending'; break;
                                    case 'Cancelled': $status_class = 'status-cancelled'; break;
                                }
                                echo "<span class='$status_class'>" . esc_html($current_status) . "</span>";
                                
                                // Status update links
                                $statuses_options = ['Pending', 'Paid', 'Cancelled'];
                                $links = [];
                                foreach ($statuses_options as $status) {
                                    if ($status !== $current_status) {
                                        $url = wp_nonce_url(
                                            admin_url('admin.php?page=reunion-registrations&action=update_status&id=' . $row->id . '&new_status=' . $status),
                                            'reunion_status_update_' . $row->id
                                        );
                                        $links[] = '<a href="' . esc_url($url) . '">Mark ' . esc_html($status) . '</a>';
                                    }
                                }
                                if (!empty($links)) {
                                    echo '<div class="row-actions">' . implode(' | ', $links) . '</div>';
                                }
                            ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="?page=reunion-registrations&view_id=<?php echo $row->id; ?>" 
                                   class="button button-small" 
                                   title="View Details">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                                <a href="?page=reunion-registrations&edit_id=<?php echo $row->id; ?>" 
                                   class="button button-primary button-small" 
                                   title="Edit Registration">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <a href="?page=reunion-registrations&action=delete&id=<?php echo $row->id; ?>&_wpnonce=<?php echo wp_create_nonce('reunion_delete_nonce_' . $row->id); ?>" 
                                   class="button button-danger button-small" 
                                   title="Delete Registration" 
                                   onclick="return confirm('<?php _e('Delete this registration permanently?', 'reunion-reg'); ?>')">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else : ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px; color: #666;">
                            <div style="font-size: 16px; margin-bottom: 10px;">
                                <?php if (!empty($search_term) || !empty($batch_filter) || !empty($year_filter) || !empty($status_filter)): ?>
                                    <strong><?php _e('No registrations found matching your criteria.', 'reunion-reg'); ?></strong>
                                    <br><small><?php _e('Try adjusting your filters or search term.', 'reunion-reg'); ?></small>
                                <?php else: ?>
                                    <strong><?php _e('No registrations found.', 'reunion-reg'); ?></strong>
                                    <br><small><?php _e('Registrations will appear here once people start signing up.', 'reunion-reg'); ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    
    <div class="tablenav bottom">
        <div class="tablenav-pages reunion-pagination">
            <?php 
            if (ceil($total_items / $per_page) > 1) {
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $current_page,
                    'total' => ceil($total_items / $per_page),
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;'
                ]);
            }
            ?>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <?php if ($results): ?>
    <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
        <h3 style="margin-top: 0;">Quick Summary</h3>
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <?php
            $stats = ['Pending' => 0, 'Paid' => 0, 'Cancelled' => 0];
            $total_amount = 0;
            foreach ($results as $reg) {
                if (isset($stats[$reg->status])) {
                    $stats[$reg->status]++;
                }
                if ($reg->status === 'Paid') {
                    $total_amount += (float)$reg->total_fee;
                }
            }
            ?>
            <div><strong>Pending:</strong> <span style="color: #ff8c00;"><?php echo $stats['Pending']; ?></span></div>
            <div><strong>Paid:</strong> <span style="color: #008000;"><?php echo $stats['Paid']; ?></span></div>
            <div><strong>Cancelled:</strong> <span style="color: #d63638;"><?php echo $stats['Cancelled']; ?></span></div>
            <div><strong>Total Collected:</strong> <span style="color: #008000; font-weight: bold;"><?php echo number_format($total_amount, 2); ?> BDT</span></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.export-actions {
    display: inline-block;
    margin-left: 10px;
}

.export-actions a {
    margin-left: 5px;
    text-decoration: none;
}

.export-actions a:hover {
    background-color: #0073aa;
    color: white;
}

.tablenav .alignright {
    float: right;
}

.tablenav .alignleft.actions select {
    margin-right: 5px;
}

.wp-list-table th,
.wp-list-table td {
    vertical-align: middle;
}

.wp-list-table .column-cb {
    width: 30px;
}

.has-photo {
    color: #008000;
    font-size: 12px;
}

.no-photo {
    color: #d63638;
    font-size: 12px;
}

.status-paid {
    color: #008000;
    font-weight: bold;
}

.status-pending {
    color: #ff8c00;
    font-weight: bold;
}

.status-cancelled {
    color: #d63638;
    font-weight: bold;
}

.button-danger {
    background-color: #d63638 !important;
    border-color: #d63638 !important;
    color: #fff !important;
}

.button-danger:hover {
    background-color: #b32d2e !important;
    border-color: #b32d2e !important;
}

.registration-id {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #ddd;
}

.payment-method-bkash {
    background: #e91e63;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.payment-method-bank {
    background: #2196f3;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.action-buttons {
    display: flex;
    gap: 3px;
    flex-wrap: wrap;
}

.action-buttons .button {
    min-width: 30px;
    padding: 4px 8px;
    text-align: center;
}

.row-actions {
    margin-top: 5px;
}

.row-actions a {
    color: #0073aa;
    text-decoration: none;
    font-size: 12px;
}

.row-actions a:hover {
    color: #d63638;
}

.payment-info {
    font-size: 13px;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .export-actions {
        display: block;
        margin: 10px 0;
    }
    
    .export-actions a {
        display: block;
        margin: 5px 0;
        text-align: center;
    }
    
    .tablenav .alignright {
        float: none;
        text-align: center;
    }
    
    .wp-list-table {
        font-size: 14px;
    }
    
    .wp-list-table td,
    .wp-list-table th {
        padding: 8px 4px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

/* Print styles */
@media print {
    .tablenav,
    .search-box,
    .action-buttons,
    .row-actions {
        display: none !important;
    }
}
</style>

<script>
// Select all checkbox functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('cb-select-all-1');
    const individualCheckboxes = document.querySelectorAll('input[name="registration_ids[]"]');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            individualCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Update select all checkbox based on individual selections
    individualCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedCount = document.querySelectorAll('input[name="registration_ids[]"]:checked').length;
            const totalCount = individualCheckboxes.length;
            
            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === totalCount) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        });
    });
});
</script>