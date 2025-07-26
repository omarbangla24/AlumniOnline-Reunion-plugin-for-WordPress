<div class="wrap">
    <h1><?php _e('Reunion Reports', 'reunion-reg'); ?></h1>
    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="reunion-reports">
        <label for="event_year_filter"><b>Select Event Year:</b></label>
        <select id="event_year_filter" name="event_year">
            <?php if (empty($all_event_years)) { echo '<option>' . __('No events found', 'reunion-reg') . '</option>'; } ?>
            <?php foreach ($all_event_years as $year): ?>
                <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year, $year); ?>><?php echo esc_html($year); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Filter" class="button">
    </form>
    <hr/>
    <h2><?php printf(__('Report for Year: %s', 'reunion-reg'), '<strong>' . esc_html($selected_year) . '</strong>'); ?></h2>
    <div id="dashboard-widgets-wrap"><div id="dashboard-widgets" class="metabox-holder">
        <div class="postbox-container" style="width: 50%;"><div class="meta-box-sortables"><div class="postbox">
            <h2 class="hndle"><span>Registration Summary</span></h2>
            <div class="inside">
                <p><strong>Total Collected Amount (Paid):</strong> <span style="color:green; font-weight: bold;"><?php echo number_format($total_paid_amount, 2); ?></span></p>
                <p><strong>Total Paid Registrations:</strong> <?php echo $status_counts['Paid']; ?></p>
                <p><strong>Total Attending Spouses:</strong> <?php echo $spouse_count; ?></p>
                <p><strong>Total Attending Children:</strong> <?php echo $child_count; ?></p>
                <hr>
                <p><strong>Total Registrations for this year:</strong> <?php echo $total_registrations; ?></p>
                <p><strong>Pending Registrations:</strong> <?php echo $status_counts['Pending']; ?></p>
                <p><strong>Cancelled Registrations:</strong> <?php echo $status_counts['Cancelled']; ?></p>
            </div>
        </div></div></div>
        <div class="postbox-container" style="width: 50%;"><div class="meta-box-sortables"><div class="postbox">
            <h2 class="hndle"><span>Registrations by Batch</span></h2><div class="inside"><table class="widefat">
            <thead><tr><th>Batch</th><th>Count</th></tr></thead><tbody>
                <?php if ($batch_counts): foreach ($batch_counts as $row): ?><tr><td><?php echo esc_html($row->batch); ?></td><td><?php echo esc_html($row->count); ?></td></tr><?php endforeach; else: ?><tr><td colspan="2">No data.</td></tr><?php endif; ?>
            </tbody></table></div>
        </div></div></div>
    </div></div>
</div>
