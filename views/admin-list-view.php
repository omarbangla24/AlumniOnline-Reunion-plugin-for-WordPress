<div class="wrap">
    <h1><?php _e('Reunion Registrations', 'reunion-reg'); ?></h1>
    
    <form method="get">
        <input type="hidden" name="page" value="reunion-registrations">
        <p class="search-box">
            <label class="screen-reader-text" for="post-search-input">Search Registrations:</label>
            <input type="search" id="post-search-input" name="s" value="<?php echo esc_attr($search_term); ?>">
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
                    <?php foreach ($batches as $batch) : ?><option value="<?php echo esc_attr($batch); ?>" <?php selected($batch_filter, $batch); ?>><?php echo esc_html($batch); ?></option><?php endforeach; ?>
                </select>
                <select name="year_filter" onchange="this.form.submit()">
                    <option value=""><?php _e('All Event Years', 'reunion-reg'); ?></option>
                    <?php foreach ($event_years as $year) : ?><option value="<?php echo esc_attr($year); ?>" <?php selected($year_filter, $year); ?>><?php echo esc_html($year); ?></option><?php endforeach; ?>
                </select>
                <select name="status_filter" onchange="this.form.submit()">
                    <option value=""><?php _e('All Status', 'reunion-reg'); ?></option>
                    <?php foreach ($statuses as $status) : ?><option value="<?php echo esc_attr($status); ?>" <?php selected($status_filter, $status); ?>><?php echo esc_html($status); ?></option><?php endforeach; ?>
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
                    <a href="<?php echo esc_url($excel_url); ?>" class="button button-secondary">📊 Export to Excel</a>
                    <a href="<?php echo esc_url($pdf_url); ?>" class="button button-secondary" target="_blank">📄 Export to PDF</a>
                </div>
            </div>
            <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo $total_items; ?> items</span></div>
            <br class="clear">
        </div>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>
                    <th style="width: 40px;">#</th><th><?php _e('Registration ID', 'reunion-reg'); ?></th><th><?php _e('Name', 'reunion-reg'); ?></th>
                    <th><?php _e('Batch', 'reunion-reg'); ?></th><th><?php _e('Payment Details', 'reunion-reg'); ?></th>
                    <th><?php _e('Total Fee', 'reunion-reg'); ?></th>
                      <th><?php _e('Mobile Number', 'reunion-reg'); ?></th>
                    <th><?php _e('Reg. Date', 'reunion-reg'); ?></th><th><?php _e('Status', 'reunion-reg'); ?></th><th><?php _e('Actions', 'reunion-reg'); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php if ($results) : $i = ($current_page - 1) * $per_page; foreach ($results as $row) : $i++; ?>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" name="registration_ids[]" value="<?php echo $row->id; ?>"></th>
                        <td><?php echo $i; ?></td>
                        <td><a href="?page=reunion-registrations&view_id=<?php echo $row->id; ?>"><strong><?php echo esc_html($row->unique_id); ?></strong></a></td>
                        <td>
                            <?php echo esc_html($row->name); ?>
                            <?php if (!empty($row->profile_picture_url)): ?>
                                <br><small>📷 Has Photo</small>
                            <?php else: ?>
                                <br><small style="color: #d63638;">❌ No Photo</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($row->batch); ?></td>
                        <td>
                            <small>
                                <strong><?php echo esc_html($row->payment_method); ?></strong><br>
                                <?php
                                if ($row->payment_details) {
                                    $details = json_decode($row->payment_details, true);
                                    if (is_array($details)) {
                                        if ($row->payment_method === 'bKash') { echo '<strong>Number:</strong> ' . esc_html($details['bkash_number'] ?? '') . '<br><strong>TrxID:</strong> ' . esc_html($details['transaction_id'] ?? ''); } 
                                        elseif ($row->payment_method === 'Bank') { echo '<strong>A/C Name:</strong> ' . esc_html($details['bank_account_name'] ?? '') . '<br><strong>A/C No:</strong> ' . esc_html($details['bank_account_number'] ?? ''); }
                                    }
                                }
                                ?>
                            </small>
                        </td>
                   
                         <td><?php echo esc_html(number_format((float)$row->total_fee, 2)); ?> BDT</td>
                        <td><?php echo esc_html($row->mobile_number); ?></td>
                        <td><?php echo date('d M Y, h:i a', strtotime($row->registration_date)); ?></td>
                        <td>
                            <?php 
                                $current_status = $row->status;
                                $status_color = '';
                                switch($current_status) {
                                    case 'Paid': $status_color = 'color: green; font-weight: bold;'; break;
                                    case 'Pending': $status_color = 'color: orange; font-weight: bold;'; break;
                                    case 'Cancelled': $status_color = 'color: red; font-weight: bold;'; break;
                                }
                                echo "<span style='$status_color'>" . esc_html($current_status) . "</span>";
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
                                echo '<div class="row-actions">' . implode(' | ', $links) . '</div>';
                            ?>
                        </td>
                        <td>
                            <a href="?page=reunion-registrations&view_id=<?php echo $row->id; ?>" class="button" title="View"><span class="dashicons dashicons-visibility"></span></a>
                            <a href="?page=reunion-registrations&edit_id=<?php echo $row->id; ?>" class="button button-primary" title="Edit"><span class="dashicons dashicons-edit"></span></a>
                            <a href="?page=reunion-registrations&action=delete&id=<?php echo $row->id; ?>&_wpnonce=<?php echo wp_create_nonce('reunion_delete_nonce_' . $row->id); ?>" class="button button-danger" title="Delete" onclick="return confirm('<?php _e('Delete this registration permanently?', 'reunion-reg'); ?>')"><span class="dashicons dashicons-trash"></span></a>
                        </td>
                    </tr>
                <?php endforeach; else : ?><tr><td colspan="11"><?php _e('No registrations found.', 'reunion-reg'); ?></td></tr><?php endif; ?>
            </tbody>
        </table>
    </form>
    <div class="tablenav bottom"><div class="tablenav-pages reunion-pagination"><?php if (ceil($total_items / $per_page) > 1) { echo paginate_links(['base' => add_query_arg('paged', '%#%'), 'format' => '', 'current' => $current_page, 'total' => ceil($total_items / $per_page)]); } ?></div></div>

    <style>
    .export-actions a {
        margin-left: 5px;
    }
    .export-actions a:hover {
        background-color: #0073aa;
        color: white;
    }
    .tablenav .alignright {
        float: right;
    }
    .button-danger {
        background-color: #d63638;
        border-color: #d63638;
        color: #fff;
    }
    .button-danger:hover {
        background-color: #b32d2e;
        border-color: #b32d2e;
    }
    </style>

    <script>
    // Select all checkbox functionality
    document.getElementById('cb-select-all-1').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="registration_ids[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    </script>
</div>