<?php
/**
 * @author xzit.online <hallo@xzit.email>
 * @website https://github.com/basteyy
 * @website https://xzit.online
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$data = xzit_ucm_get_all_user_emails();

?>
<h2><?php echo esc_html__('Export users', 'user-contact-mails'); ?></h2>

<div class="notice notice-info inline">
    <?php echo esc_html__('Export all users and there contact-data or a selection of the users. ', 'user-contact-mails'); ?>
</div>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="xzit-ucm-export-form">
    <input type="hidden" name="action" value="xzit_ucm_export">
    <?php wp_nonce_field( 'xzit_ucm_export_users', 'xzit_ucm_export_users_nonce' ); ?>
<table class="widefat fixed striped xzit-ucm-table">
    <thead>
        <tr>
            <th colspan="2" style="width: 20%;"><?php esc_html_e( 'User', 'user-contact-mails' ); ?></th>
            <th style="width: 20%;"><?php esc_html_e( 'Primary User E-Mail', 'user-contact-mails' ); ?></th>
            <th style="width: auto:"><?php esc_html_e( 'Additional Contacts', 'user-contact-mails' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $data as $user_id => $user ) : ?>
            <tr>
                <td><input type="checkbox" name="users[]" id="user_<?php echo esc_attr($user_id); ?>" value="<?php echo esc_attr($user_id)  ?>"></td>
                <td><label for="user_<?php echo esc_attr($user_id); ?>"><?php echo esc_html( $user['user_nicename'] ); ?></label></td>
                <td><?php echo esc_html( $user['user_email'] ); ?></td>
                <td>
                    <ul id="ucm-email-list" style="list-style:none;margin:0;padding:0;">
                        <?php
                        foreach ( $user['user_contact_emails'] as $contact ) {
                            $address = $contact['address'];

                            printf('<li><a href="mailto:%s">%s</a> %s</li>',
                                esc_html( $address ),
                                esc_html( $address ),
                                xzit_ucm_labels_enabled() ? (! empty( $contact['label'] ) ? ' (' . esc_html( $contact['label'] ) . ')' : '') : ''
                            );
                        }
                        ?>
                    </ul></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan="3">
            <button type="button" id="select-all" class="button button-secondary"><?php esc_html_e( 'All', 'user-contact-mails' ); ?></button>
            <button type="button" id="invert-selection" class="button button-secondary"><?php esc_html_e( 'Invert', 'user-contact-mails' ); ?></button>
            <span id="xzit-ucm-select-counter"></span></th>
        <th style="text-align: right;">

            <input type="checkbox" name="include_primary_email" id="include_primary_email" checked>
            <label for="include_primary_email"><?php esc_html_e( 'Include primary email', 'user-contact-mails' ); ?></label>

            <input type="checkbox" name="include_labels" id="include_labels" checked>
            <label for="include_labels"><?php esc_html_e( 'Include labels', 'user-contact-mails' ); ?></label>

            <select name="export_format" id="export_format">
                <optgroup label="<?php esc_html_e( 'Export as', 'user-contact-mails' ); ?>">
                    <option value="html"><?php esc_html_e( 'HTML', 'user-contact-mails' ); ?></option>
                    <option value="json"><?php esc_html_e( 'JSON', 'user-contact-mails' ); ?></option>
                    <option value="csv"><?php esc_html_e( 'CSV', 'user-contact-mails' ); ?></option>
                    <option value="text"><?php esc_html_e( 'TEXT', 'user-contact-mails' ); ?></option>
                </optgroup>
            </select>
            <button type="submit" class="button button-primary" id="xzit-ucm-export-button"><?php esc_html_e( 'Export', 'user-contact-mails' ); ?></button>
        </th>
    </tr>
    </tfoot>
</table>
</form>
<script type="text/javascript">
    // Trigger selecting and increase/decrease counter
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form#xzit-ucm-export-form');
        const checkboxes = form.querySelectorAll('input[name="users[]"]');
        const counter = form.querySelector('#xzit-ucm-select-counter');
        const exportButton = form.querySelector('#xzit-ucm-export-button');
        const exportFormat = form.querySelector('#export_format');
        const selectAllButton = form.querySelector('#select-all');
        const invertSelectionButton = form.querySelector('#invert-selection');

        selectAllButton.addEventListener('click', function () {
            checkboxes.forEach(checkbox => checkbox.checked = true);
            updateCounter();
        });

        invertSelectionButton.addEventListener('click', function () {
            checkboxes.forEach(checkbox => checkbox.checked = !checkbox.checked);
            updateCounter();
        });

        function updateCounter() {
            const selectedCount = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;
            counter.textContent = selectedCount > 0 ? `${selectedCount} ${selectedCount === 1 ? 'user selected' : 'users selected'}` : '';
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateCounter);
        });

        exportButton.addEventListener('click', function (event) {
            if ( ! exportFormat.value ) {
                event.preventDefault();
                alert('<?php esc_js( esc_html__( 'Please select an export format.', 'user-contact-mails' ) ); ?>');
                return false;
            }

            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            if ( ! anyChecked ) {
                event.preventDefault();
                alert('<?php esc_js( esc_html__( 'Please select at least one user to export.', 'user-contact-mails' ) ); ?>');
                return false;
            }
        });
    });
</script>
