<?php
/**
 * SMS Logs Admin Page
 * File: views/admin-sms-logs-view.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('SMS Logs', 'reunion-reg'); ?></h1>
    
    <div class="tablenav top">
        <div class="tablenav-pages">
            <span class="displaying-num"><?php echo $total_items; ?> items</span>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 50px;"><?php _e('ID', 'reunion-reg'); ?></th>
                <th><?php _e('Registration', 'reunion-reg'); ?></th>
                <th><?php _e('Type', 'reunion-reg'); ?></th>
                <th><?php _e('Message', 'reunion-reg'); ?></th>
                <th style="width: 100px;"><?php _e('Status', 'reunion-reg'); ?></th>
                <th><?php _e('Response', 'reunion-reg'); ?></th>
                <th style="width: 150px;"><?php _e('Sent At', 'reunion-reg'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log->id; ?></td>
                        <td>
                            <?php if ($log->name && $log->unique_id): ?>
                                <a href="?page=reunion-registrations&view_id=<?php echo $log->registration_id; ?>">
                                    <?php echo esc_html($log->name); ?><br>
                                    <small>ID: <?php echo esc_html($log->unique_id); ?></small>
                                </a>
                            <?php else: ?>
                                <em><?php _e('Registration not found', 'reunion-reg'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $type_labels = [
                                'registration' => __('New Registration', 'reunion-reg'),
                                'payment_confirmed' => __('Payment Confirmed', 'reunion-reg'),
                                'test' => __('Test SMS', 'reunion-reg')
                            ];
                            echo $type_labels[$log->sms_type] ?? $log->sms_type;
                            ?>
                        </td>
                        <td>
                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo esc_html($log->message); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($log->status === 'success'): ?>
                                <span style="color: green; font-weight: bold;">✓ <?php _e('Success', 'reunion-reg'); ?></span>
                            <?php else: ?>
                                <span style="color: red; font-weight: bold;">✗ <?php _e('Failed', 'reunion-reg'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $response = json_decode($log->response, true);
                            if (isset($response['message'])) {
                                echo '<small>' . esc_html($response['message']) . '</small>';
                            } else {
                                echo '<small><a href="#" onclick="showFullResponse(' . $log->id . '); return false;">View Details</a></small>';
                                echo '<div id="response-' . $log->id . '" style="display:none;">' . esc_html($log->response) . '</div>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo date('d M Y', strtotime($log->sent_at)); ?><br>
                            <small><?php echo date('h:i A', strtotime($log->sent_at)); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">
                        <?php _e('No SMS logs found.', 'reunion-reg'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="tablenav bottom">
        <div class="tablenav-pages reunion-pagination">
            <?php 
            if (ceil($total_items / $per_page) > 1) {
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $current_page,
                    'total' => ceil($total_items / $per_page)
                ]);
            }
            ?>
        </div>
    </div>
</div>

<script>
function showFullResponse(id) {
    var responseDiv = document.getElementById('response-' + id);
    if (responseDiv) {
        alert(responseDiv.innerText);
    }
}
</script>

<style>
.wp-list-table td {
    vertical-align: middle;
}
</style>