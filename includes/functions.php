<?php
/**
 * @author xzit.online <hallo@xzit.email>
 * @website https://github.com/basteyy
 * @website https://xzit.online
 */

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if the User Contact Mails plugin is enabled.
 *
 * @return bool True if the plugin is enabled, false otherwise.
 */
function xzit_ucm_is_enabled() : bool {
    return (bool) get_option('xzit_ucm_enabled');
}

/**
 * Get the registered settings if at least one email is required.
 *
 * @return bool
 */
function xzit_ucm_required_one_email() : bool {
    return (bool) get_option('xzit_ucm_require_one_email');
}

/**
 * Check if the plugin should set the primary email as the user's email.
 *
 * @return bool True if the primary email should be set as the user's email, false otherwise.
 */
function xzit_ucm_set_as_user_email() : bool {
    return (bool) get_option('xzit_ucm_set_primary_as_user_email');
}

/**
 * Check if email validation is enabled.
 *
 * @return bool True if email validation is enabled, false otherwise.
 */
function xzit_ucm_validate_emails() : bool {
    return (bool) get_option('xzit_ucm_validate_emails');
}

/**
 * Get the maximum number of emails a user can have.
 *
 * @return int The maximum number of emails per user.
 */
function xzit_ucm_max_emails_per_user() : int {
    return (int) get_option('xzit_ucm_max_emails_per_user');
}

/**
 * Check if users can choose their primary email.
 *
 * @return bool True if users can choose their primary email, false otherwise.
 */
function xzit_ucm_user_can_choose_primary() : bool {
    return (bool) get_option('xzit_ucm_user_can_choose_primary');
}

/**
 * Labels are enabled?
 *
 * @return bool
 */
function xzit_ucm_labels_enabled() : bool {
    return (bool) get_option('xzit_ucm_enable_labels');
}

/**
 * Check if labels are required.
 *
 * @see xzit_ucm_labels_enabled()
 * @return bool True if labels are required, false otherwise.
 */
function xzit_ucm_labels_required () : bool {
    return xzit_ucm_labels_enabled() && (bool) get_option('xzit_ucm_require_labels');
}

/**
 * Check if logging is enabled.
 *
 * @return bool True if logging is enabled, false otherwise.
 */
function xzit_ucm_logging_enabled() : bool {
    return (bool) get_option('xzit_ucm_logging_enabled');
}

/**
 * Get the roles that can view the user contact mails list
 *
 * @return array An array of role slugs that can view the user contact mails.
 */
function xzit_ucm_get_roles_can_view() : array {
    $roles = get_option('xzit_ucm_roles_can_view', ['administrator']);
    if ( ! is_array($roles) ) {
        $roles = ['administrator'];
    }
    return $roles;
}

/**
 * Log a message to the user contact mails database table.
 *
 * @param string $message The message to log.
 * @param string $level   The log level (e.g., 'info', 'error'). Default is 'info'.
 */
function xzit_ucm_log( string $message, string $level = 'info' ): void {
    xzit_ucm_enable_or_die();

    if ( ! xzit_ucm_logging_enabled() ) {
        return; // Logging is disabled, do not log the message
    }

    global $wpdb;
    $table = $wpdb->prefix . XZIT_UCM_TABLE_NAME;

    // Prefix level to message since table only has dtg and message columns
    $entry = sprintf( '[%s] %s', strtoupper( $level ), $message );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- using $wpdb->insert() is intentional and safe.
    $wpdb->insert(
        $table,
        [
            'dtg'     => current_time( 'mysql' ),
            'message' => $entry,
        ],
        [
            '%s',
            '%s',
        ]
    );
}

/**
 * Get log entries from the user contact mails database table.
 *
 * @param int $limit  The maximum number of log entries to retrieve. Default is 100.
 * @param int $offset The offset for pagination. Default is 0.
 * @return array An array of log entries, each entry is an associative array with 'id', 'dtg', and 'message'.
 */
function xzit_ucm_get_log_entries( int $limit = 100, int $offset = 0 ): array {
    xzit_ucm_enable_or_die();

    if ( ! xzit_ucm_logging_enabled() ) {
        return []; // Logging is disabled, return empty array
    }

    $cache_key   = sprintf( 'ucm_logs_%d_%d', $limit, $offset );
    $cache_group = 'xzit_ucm_logs';
    $entries     = wp_cache_get( $cache_key, $cache_group );
    if ( false !== $entries ) {
        return $entries;
    }

    global $wpdb;
    $table = $wpdb->prefix . XZIT_UCM_TABLE_NAME;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared -- using prepare() and get_results() is intentional.
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->ucm_logs} ORDER BY dtg DESC LIMIT %d OFFSET %d",
        $limit,
        $offset
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared  -- using prepare() and get_results() is intentional.
    $entries = $wpdb->get_results( $query, ARRAY_A );

    wp_cache_set( $cache_key, $entries, $cache_group, HOUR_IN_SECONDS );

    return $entries;
}

#[NoReturn] function xzit_ucm_handle_clear_logfile(): void
{

    if ( ! xzit_ucm_user_can_view_log() ) {
        wp_die( esc_html__( 'You are not allowed to do this.', 'user-contact-mails' ) );
    }

    check_admin_referer( 'xzit_ucm_clear_logfile' );

    global $wpdb;

    $wpdb->query( "TRUNCATE TABLE {$wpdb->ucm_logs}" );

    $redirect = add_query_arg( 'ucm_log_cleared', '1', wp_get_referer() );
    wp_safe_redirect( $redirect );
    exit;
}

/**
 * Add an email to a user.
 *
 * @param WP_User|int $user The user object or user ID.
 * @param string $email The email address to add.
 * @param string|null $label Optional label for the email.
 * @param bool|null $overwrite Whether to overwrite an existing email with the same address. Default is false.
 * @return bool True if the email was added, false otherwise.
 */
function xzit_ucm_add_email_to_user(WP_User|int $user, string $email, ?string $label = null, ?bool $overwrite = false, ?bool $primary_email = false) : bool {
    xzit_ucm_enable_or_die();

    $user_id = xzit_ucm_get_user_id($user);

    $mails = xzit_ucm_get_user_emails($user);

    if ( in_array($email, $mails) && ! $overwrite ) {
        return false; // Email already exists and not allowed to overwrite
    }

    /**
     * I'm very unsure, if I use filter_var instead of is_email here. Because take a look at the code
     * @see is_email()
     * @see filter_var()
     */
    if ( xzit_ucm_validate_emails() && ! is_email($email) ) {
        return false; // Invalid email format
    }

    if ( xzit_ucm_labels_required() && ! $label ) {
        return false; // Label is required but not provided
    }

    if ( xzit_ucm_user_can_choose_primary() && $primary_email ) {
        // Remove primary flag from all other emails
        foreach ($mails as &$mail) {
            if (isset($mail['primary']) && $mail['primary']) {
                $mail['primary'] = false; // Unset primary flag for all other emails
            }
        }
        unset($mail); // Unset reference to avoid issues in the next loop
    } else {
        $primary_email = false; // No primary email if not allowed to choose
    }

    // add to array
    $mails[] = [
        'address' => $email,
        'label' => $label ?: '',
        'primary' => xzit_ucm_user_can_choose_primary() && $primary_email
    ];

    // save
    update_user_meta($user_id, 'xzit_ucm_emails', $mails);

    // log
    xzit_ucm_log(
        sprintf(
            'User %s (%d) added email %s with label %s.',
            wp_get_current_user()->user_login,
            get_current_user_id(),
            $email,
            $label ?: 'No label'
        )
    );

    // Set primary?
    if ( xzit_ucm_set_as_user_email() && $primary_email ) {
        // Set the primary email as the user's email
        wp_update_user([
            'ID' => $user_id,
            'user_email' => $email,
        ]);

        // log
        xzit_ucm_log(
            sprintf(
                'User %s (%d) set email %s as primary email for user %d.',
                wp_get_current_user()->user_login,
                get_current_user_id(),
                $email,
                $user_id
            )
        );
    }

    return true;
}

/**
 * Remove an email from a user.
 *
 * @param WP_User|int $user The user object or user ID.
 * @param string|null $email The email to remove. If null, all emails will be removed.
 * @return bool True if the email was removed, false otherwise.
 */
function xzit_ucm_remove_email_from_user(WP_User|int $user, ?string $email = null) : bool
{
    xzit_ucm_enable_or_die();

    $user_id = xzit_ucm_get_user_id($user);

    if ( ! $email ) {
        // remove all emails
        delete_user_meta($user_id, 'xzit_ucm_emails');
        return true;
    }

    $mails = xzit_ucm_get_user_emails($user_id);
    if ( empty($mails) ) {
        return false; // No emails to remove
    }

    // Filter out the email to remove
    $mails = array_filter($mails, function($mail) use ($email) {
        return $mail['address'] !== $email;
    });

    // Re-index the array
    $mails = array_values($mails);

    update_user_meta($user_id, 'xzit_ucm_emails', $mails);

    // log
    xzit_ucm_log(
        sprintf(
            'User %s (%d) removed email %s.',
            wp_get_current_user()->user_login,
            get_current_user_id(),
            $email
        )
    );

    return true;
}

/**
 * Get all user emails from a WP_User object or an integer user ID.
 *
 * @param WP_User|int $user The user object or user ID.
 * @return array An array of user emails.
 */
function xzit_ucm_get_user_emails (WP_User|int $user) : array {
    
    xzit_ucm_enable_or_die();

    if ( is_int($user) ) {
        $user = get_user_by('id', $user);
    }

    return get_user_meta($user->ID, 'xzit_ucm_emails',true) ?: [];
}

/**
 * Get the user ID from a WP_User object or an integer user ID.
 *
 * @param WP_User|int $user The user object or user ID.
 * @return int The user ID, or 0 if the input is invalid.
 */
function xzit_ucm_get_user_id (WP_User|int $user) : int {
    if ( is_int($user) ) {
        return $user;
    }

    if ( ! $user instanceof WP_User ) {
        return 0; // Invalid user
    }

    return $user->ID;
}

/**
 * Get all user emails.
 *
 * @return array An array of all user emails.
 */
function xzit_ucm_get_all_user_emails() : array {
    xzit_ucm_enable_or_die();

    $users = get_users([
        'fields' => 'ID',
        'number' => -1, // Get all users
    ]);

    $emails = [];
    foreach ($users as $user_id) {

        $user = get_user_by('id', $user_id);

        $emails[$user->ID] = [
            'user_nicename' => $user->user_nicename,
            'user_email' => $user->user_email,
            'user_contact_emails' => xzit_ucm_get_user_emails($user->ID)
        ];
    }

    return $emails;
}

function xzit_ucm_user_can_view_list(): bool
{
    xzit_ucm_enable_or_die();

    $current_user_roles = wp_get_current_user()->roles;
    $allowed_roles = xzit_ucm_get_roles_can_view();

    if ( empty($allowed_roles) || ! array_intersect($current_user_roles, $allowed_roles) ) {
        return false; // User does not have permission to view the list
    }

    return true; // User has permission to view the list
}


function xzit_ucm_user_can_view_log(): bool
{
    xzit_ucm_enable_or_die();

    $current_user_roles = wp_get_current_user()->roles;
    $allowed_roles = xzit_ucm_roles_can_view_logs();

    if ( empty($allowed_roles) || ! array_intersect($current_user_roles, $allowed_roles) ) {
        return false; // User does not have permission to view the list
    }

    return true; // User has permission to view the list
}

function xzit_ucm_roles_can_manage() {
    $roles = get_option('xzit_ucm_roles_can_manage', ['administrator']);
    if ( ! is_array($roles) ) {
        $roles = ['administrator'];
    }
    return $roles;
}

function xzit_ucm_user_can_manage () : bool
{
    xzit_ucm_enable_or_die();

    $current_user_roles = wp_get_current_user()->roles;
    $allowed_roles = xzit_ucm_roles_can_manage();

    if ( empty($allowed_roles) || ! array_intersect($current_user_roles, $allowed_roles) ) {
        return false; // User does not have permission to manage
    }

    return true; // User has permission to manage
}

function xzit_ucm_roles_can_view_logs() {
    $roles = get_option('xzit_ucm_roles_can_view_logs', ['administrator']);
    if ( ! is_array($roles) ) {
        $roles = ['administrator'];
    }
    return $roles;
}


/**
 * View the user contact mails list table.
 *
 * This function checks if the current user has permission to view the list,
 * logs the access, and includes the HTML template for displaying the list.
 *
 * @return void
 */
function xzit_ucm_view_list_table(): void
{
    xzit_ucm_enable_or_die();

    $current_user_roles = wp_get_current_user()->roles;
    $allowed_roles = xzit_ucm_get_roles_can_view();

    if ( ! xzit_ucm_user_can_view_list() ) {

        // Log the unauthorized access attempt
        xzit_ucm_log(
            sprintf(
                'User %s (%d) attempted to view the user contact mails list but does not have permission.',
                wp_get_current_user()->user_login,
                get_current_user_id()
            ),
            'error'
        );

        wp_die(esc_html__('You are not allowed to view this page.', 'user-contact-mails'));
    }

    xzit_ucm_log(
        sprintf(
            'User %s (%d) viewed the user contact mails list.',
            wp_get_current_user()->user_login,
            get_current_user_id()
        )
    );

    include XZIT_UCM_PLUGIN_DIR . 'includes/html/view-list-template.php';
}

/**
 * Export user contact emails.
 *
 * This function handles the export of user contact emails in various formats
 * (JSON, CSV, HTML, or plain text) based on the user's selection.
 *
 * @return void
 */
#[NoReturn] function xzit_ucm_export(): void
{
    xzit_ucm_enable_or_die();

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Isset is safe in this context, for further checks the wp_unslash() function is used.
    if ( ! isset($_POST['xzit_ucm_export_users_nonce']) || ! wp_verify_nonce(wp_unslash($_POST['xzit_ucm_export_users_nonce']), 'xzit_ucm_export_users') ) {

        // log
        xzit_ucm_log(
            sprintf(
                'User %s (%d) attempted to export user contact emails but the nonce is invalid.',
                wp_get_current_user()->user_login,
                get_current_user_id()
            ),
            'error'
        );

        wp_die(esc_html__('Invalid nonce.', 'user-contact-mails'));
    }

    if ( ! isset($_POST['export_format']) || ! in_array($_POST['export_format'], ['json','csv','html','text'], true) ) {

        // log
        xzit_ucm_log(
            sprintf(
                'User %s (%d) attempted to export user contact emails but the export format is invalid.',
                wp_get_current_user()->user_login,
                get_current_user_id()
            ),
            'error'
        );

        wp_die( esc_html__( 'Invalid export format.', 'user-contact-mails' ) );
    }

    if ( ! isset($_POST['users']) || ! is_array($_POST['users']) || empty($_POST['users']) ) {

        // log
        xzit_ucm_log(
            sprintf(
                'User %s (%d) attempted to export user contact emails but no users were selected.',
                wp_get_current_user()->user_login,
                get_current_user_id()
            ),
            'error'
        );

        wp_die(esc_html__('No users selected for export.', 'user-contact-mails'));
    }

    $users = array_map('intval', wp_unslash($_POST['users']));
    $export_format = sanitize_text_field(wp_unslash($_POST['export_format']));
    $data = [];

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Isset is safe in this context, for further checks the wp_unslash() function is used.
    $include_primary_email = isset($_POST['include_primary_email']) && wp_unslash($_POST['include_primary_email']);
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Isset is safe in this context, for further checks the wp_unslash() function is used.
    $include_labels = isset($_POST['include_labels']) && wp_unslash($_POST['include_labels']);

    $text_output = '';
    $array_output = [];
    $csv_output = [];

    foreach ($users as $user_id) {
        $user = get_user_by('id', $user_id);
        if ( ! $user ) {
            continue; // Skip if user does not exist
        }

        if ( $include_primary_email ) {
            $build = ($include_labels ? sprintf('"%s" <%s>',
                    $user->user_nicename, $user->user_email
                ) : $user->user_email);
            $text_output .= $build .'; ';
            $array_output[] = $build;
            $csv_output[] = [
                'email' => $user->user_email,
                'label' => $user->user_nicename,
            ];
        }

        foreach ( xzit_ucm_get_user_emails($user_id) as $additional ) {
            $address = $additional['address'] ?? '';
            $label = $additional['label'] ?? '';
            $build = ($include_labels && $label ? sprintf('"%s" <%s>', $label, $address) : $address);
            $text_output .= $build .'; ';
            $array_output[] = $build;
            $csv_output[] = [
                'email' => $address,
                'label' => $label,
            ];
        }
    }

    // log
    xzit_ucm_log(
        sprintf(
            'User %s (%d) exported user contact emails in %s format.',
            wp_get_current_user()->user_login,
            get_current_user_id(),
            $export_format
        )
    );

    header('Content-Disposition: attachment; filename="user-contact-emails' . gmdate('dmyhis') . '.' . $export_format.'"');

    if ( $export_format === 'json' ) {
        header('Content-Type: application/json');
        echo json_encode($array_output, JSON_PRETTY_PRINT);
    } elseif ( $export_format === 'csv' ) {
        header('Content-Type: text/csv');
        $output = fopen('php://output', 'w');

        fputcsv($output, $include_labels ? ['Email', 'Label'] : ['Email']);
        foreach ($csv_output as $row) {
            fputcsv($output, $include_labels ? [$row['email'], $row['label']] : [$row['email']]);
        }
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- closing php://output stream is intentional and safe.
        fclose($output);

    } elseif ( $export_format === 'html' ) {
        header('Content-Type: text/html');
        echo '<!DOCTYPE html><html lang="en"><head><title>User Contact Emails</title></head><body>';
        echo '<h1>User Contact Emails</h1>';
        echo '<table><tr><th>Email</th>';

        if ( $include_labels ) {
            echo '<th>Label</th>';
        }

        echo '</tr>';

        foreach ($csv_output as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['email']) . '</td>';
            if ( $include_labels ) {
                echo '<td>' . esc_html($row['label']) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table></body></html>';
    } elseif ( $export_format === 'text' ) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="user-contact-emails.txt"');
        echo esc_html($text_output);

    } else {
        wp_die( esc_html__( 'Unknown export format.', 'user-contact-mails' ) );
    }

    exit; // Terminate script after outputting the data

}

/**
 * Set the activation flag for the User Contact Mails plugin.
 *
 * This function adds an option to the database to indicate that the plugin has been activated.
 * The flag is used to show an activation notice only once after activation.
 *
 * @return void
 */
function xzit_ucm_set_activation_flag(): void
{
    add_option( 'xzit_ucm_activation_notice', true );
}


/**
 * Maybe show the activation popup for the User Contact Mails plugin.
 *
 * This function checks if the activation notice flag is set and, if so, includes the HTML template
 * for the activation popup. The flag is then deleted to ensure the popup is only shown once.
 *
 * @return void
 */
function xzit_ucm_maybe_show_activation_popup(): void
{
    // Flag prüfen
    if ( ! get_option( 'xzit_ucm_activation_notice' ) ) {
        return;
    }
    // Flag löschen, damit es nur einmal ausgegeben wird
    delete_option( 'xzit_ucm_activation_notice' );
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');

    include XZIT_UCM_PLUGIN_DIR . 'includes/html/activation-popup-template.php';
}

/**
 * Ensure the User Contact Mails plugin is enabled, otherwise terminate execution.
 *
 * @return void
 */
function xzit_ucm_enable_or_die () : void {
    if ( ! xzit_ucm_is_enabled() ) {
        wp_die( esc_html__( 'Plugin is not enabled.', 'user-contact-mails' ) );
    }
}

/**
 * Uninstall the User Contact Mails plugin.
 *
 * This function deletes all plugin options, user meta data related to emails,
 * and optionally removes the database table used for logging.
 *
 * @param bool $remove_database Whether to remove the database table. Default is false.
 * @return void
 */
function xzit_ucm_uninstall(?bool $remove_database = false) : void {

    $options = [
        'xzit_ucm_enabled',
        'xzit_ucm_logging_enabled',
        'xzit_ucm_max_emails_per_user',
        'xzit_ucm_require_one_email',
        'xzit_ucm_set_primary_as_user_email',
        'xzit_ucm_validate_emails',
        'xzit_ucm_roles_can_view',
        'xzit_ucm_enable_labels',
        'xzit_ucm_require_labels',
        'xzit_ucm_activation_notice',
        'xzit_ucm_roles_can_manage',
        'xzit_ucm_roles_can_view_logs',
        'xzit_ucm_user_can_choose_primary'
    ];

    foreach ( $options as $opt ) {
        delete_option( $opt );
    }

    $meta_keys = [
        'xzit_ucm_emails',
        'xzit_ucm_labels',
        'xzit_ucm_primary_email',
    ];

    $users = get_users( [ 'fields' => 'ID' ] );
    foreach ( $users as $user_id ) {
        foreach ( $meta_keys as $meta ) {
            delete_user_meta( $user_id, $meta );
        }
    }

    if ( $remove_database ) {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- using $wpdb->query() is intentional and safe, dbDelta does not support DROP TABLE IF EXISTS, safe schema teardown on deactivation.
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->ucm_logs};" );
    }
}

/**
 * Activate the User Contact Mails plugin.
 *
 * This function is called when the plugin is activated. It creates the log table
 * and sets the activation flag to show the activation notice.
 *
 * @return void
 */
function xzit_ucm_activate(): void
{
    xzit_ucm_create_log_table();
    xzit_ucm_set_activation_flag();
}

/**
 * Create the log table for the User Contact Mails plugin.
 *
 * This function creates a database table to store logs related to user contact mails.
 * It uses the dbDelta function to create or update the table structure.
 *
 * @return void
 */
function xzit_ucm_create_log_table(): void
{
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $sql     = "CREATE TABLE {$wpdb->ucm_logs} (
        id      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        dtg     DATETIME        NOT NULL,
        message TEXT            NOT NULL,
        PRIMARY KEY (id)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

/**
 * Deactivate the User Contact Mails plugin.
 *
 * This function is called when the plugin is deactivated. It does not perform any actions
 * but can be used to clean up resources or settings if needed in the future.
 *
 * @return void
 */
function xzit_ucm_on_deactivation(): void
{
    xzit_ucm_uninstall(false);
}