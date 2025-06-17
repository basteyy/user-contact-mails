<?php
/**
 * @author xzit.online <hallo@xzit.email>
 * @website https://github.com/basteyy
 * @website https://xzit.online
 */

declare(strict_types=1);

if ( ! xzit_ucm_user_can_view_log() ) {

    // Log the unauthorized access attempt
    xzit_ucm_log(
        sprintf(
            'User %s (%d) attempted to view the log but does not have permission.',
            wp_get_current_user()->user_login,
            get_current_user_id()
        ),
        'error'
    );

    wp_die(esc_html__('You are not allowed to view this page.', 'user-contact-mails'));
}

?>

<h2><?php echo esc_html__('Logfile', 'user-contact-mails'); ?></h2>

<p class="description">
    <?php echo esc_html__('This log file contains all actions performed by the plugin, including errors and warnings. It is useful for debugging and monitoring purposes.', 'user-contact-mails'); ?>
</p>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="xzit_ucm_clear_logfile">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wp_create_nonce('xzit_ucm_clear_logfile')); ?>">
    <input type="submit" class="button button-secondary" value="<?php echo esc_attr__('Clear Log File', 'user-contact-mails'); ?>">
</form>

<hr />

<style>
    .xzit-log-table th.timestamp, .xzit-log-table td.timestamp {
        width: 180px;
        white-space: nowrap;
    }
    .xzit-log-table th.message, .xzit-log-table td.message {
        width: auto;
    }
</style>

<table class="widefat fixed striped xzit-log-table">
    <thead>
    <tr>
        <th class="timestamp"><?php echo esc_html__('Timestamp', 'user-contact-mails'); ?></th>
        <th class="message"><?php echo esc_html__('Message', 'user-contact-mails'); ?></th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ( xzit_ucm_get_log_entries() as $log ) : ?>
        <tr>
            <td class="timestamp"><?php echo esc_html($log['dtg']); ?></td>
            <td class="message"><?php echo esc_html($log['message']); ?></td>
        </tr>

        <?php endforeach; ?>
    </tbody>
</table>