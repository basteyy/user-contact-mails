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

function xzit_ucm_admin_menu(): void
{
    add_submenu_page(
        'tools.php',                 // oder 'tools.php'
        __( 'User Contact Mails', 'user-contact-mails' ),
        __( 'User Contact Mails', 'user-contact-mails' ),
        'manage_options',
        'xzit_ucm_settings',                   // <- Seite/Slug
        'xzit_ucm_render_settings'
    );
}
function xzit_ucm_register_settings(): void
{
    add_settings_section(
    'xzit_ucm_section_general',
    __( 'General Settings', 'user-contact-mails' ),
    null,
    'xzit_ucm_settings'
);

    // Enable
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_enabled',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ]
    );

    add_settings_field(
        'xzit_ucm_enabled',
        __( 'Enable Plugin?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_enabled', 1 );
            printf(
                '<input type="checkbox" id="xzit_ucm_enabled" name="xzit_ucm_enabled" value="1" %s />',
                checked( 1, $value, false )
            );
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Activate logging
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_logging_enabled',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ]
    );

    add_settings_field(
        'xzit_ucm_logging_enabled',
        __( 'Enable Logging?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_logging_enabled', 0 );
            printf(
                '<input type="checkbox" id="xzit_ucm_logging_enabled" name="xzit_ucm_logging_enabled" value="1" %s />',
                checked( 1, $value, false )
            );
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Maximum emails per user (default 5)
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_max_emails_per_user',
        [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 5,
        ]
    );

    add_settings_field(
        'xzit_ucm_max_emails_per_user',
        __( 'Maximum Emails per User', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_max_emails_per_user', 5 );
            printf(
                '<input type="number" id="xzit_ucm_max_emails_per_user" name="xzit_ucm_max_emails_per_user" value="%d" min="1" />',
                esc_attr( $value )
            );
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Require at least one email
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_require_one_email',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ]
    );

    add_settings_field(
        'xzit_ucm_require_one_email',
        __( 'Require at least one email?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_require_one_email', 1 );
            printf(
                '<input type="checkbox" id="xzit_ucm_require_one_email" name="xzit_ucm_require_one_email" value="1" %s />',
                checked( 1, $value, false )
            );
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // user can choose primary email?
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_user_can_choose_primary',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ]
    );

    add_settings_field(
        'xzit_ucm_user_can_choose_primary',
        __( 'User can choose primary email?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_user_can_choose_primary', 1 );
            printf(
                '<input type="checkbox" id="xzit_ucm_user_can_choose_primary" name="xzit_ucm_user_can_choose_primary" value="1" %s />',
                checked( 1, $value, false )
            );
            echo '<p class="description">' . esc_html__( 'Enable this to allow users to choose their primary email address.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Set primary mail as user_email in wp_users
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_set_primary_as_user_email',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ]
    );

    add_settings_field(
        'xzit_ucm_set_primary_as_user_email',
        __( 'Set primary email as user_email in wp_users?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_set_primary_as_user_email', 0 );
            printf(
                '<input type="checkbox" id="xzit_ucm_set_primary_as_user_email" name="xzit_ucm_set_primary_as_user_email" value="1" %s />',
                checked( 1, $value, false )
            );
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Validate emails (only by filter_var)
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_validate_emails',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 1,
        ]
    );

    add_settings_field(
        'xzit_ucm_validate_emails',
        __( 'Validate emails?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_validate_emails', 1 );
            printf(
                '<input type="checkbox" id="xzit_ucm_validate_emails" name="xzit_ucm_validate_emails" value="1" %s />',
                checked( 1, $value, false )
            );
            echo '<p class="description">' . esc_html__( 'Enable this to validate email addresses. This validates only the structure, not the existing of any domain/address.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Select roles that can manage plugin settings
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_roles_can_manage',
        [
            'type'              => 'array',
            'sanitize_callback' => function( $input ) {
                $allowed_roles = array_keys( wp_roles()->roles );
                $roles         = array_intersect( (array) $input, $allowed_roles );
                return array_values( $roles );
            },
            'default'           => ['administrator'],
        ]
    );

    add_settings_field(
        'xzit_ucm_roles_can_manage',
        __( 'Roles that can manage settings', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_roles_can_manage', ['administrator'] );
            $roles = get_editable_roles();
            echo '<select id="xzit_ucm_roles_can_manage" name="xzit_ucm_roles_can_manage[]" multiple>';
            foreach ( $roles as $role => $details ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $role ),
                    selected( in_array( $role, $value, true ), true, false ),
                    esc_html( $details['name'] )
                );
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__( 'Select the roles that can manage the plugin settings.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // Select roles that can view the emails
    register_setting(
        'xzit_ucm_settings',            // Option-Group
        'xzit_ucm_roles_can_view',      // Option-Name
        [
            'type'              => 'array',
            'sanitize_callback' => function( $input ) {
                $allowed_roles = array_keys( wp_roles()->roles );
                $roles         = array_intersect( (array) $input, $allowed_roles );
                return array_values( $roles );
            },
            'default'           => ['administrator'],
        ]
    );

    add_settings_field(
        'xzit_ucm_roles_can_view',
        __( 'Roles that can view emails', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_roles_can_view', ['administrator'] );
            $roles = get_editable_roles();
            echo '<select id="xzit_ucm_roles_can_view" name="xzit_ucm_roles_can_view[]" multiple>';
            foreach ( $roles as $role => $details ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $role ),
                    selected( in_array( $role, $value, true ), true, false ),
                    esc_html( $details['name'] )
                );
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__( 'Select the roles that can view the emails.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',           // Page-Slug
        'xzit_ucm_section_general'     // Section-ID
    );

    // select roles which can view logs
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_roles_can_view_logs',
        [
            'type'              => 'array',
            'sanitize_callback' => function( $input ) {
                $allowed_roles = array_keys( wp_roles()->roles );
                $roles         = array_intersect( (array) $input, $allowed_roles );
                return array_values( $roles );
            },
            'default'           => ['administrator'],
        ]
    );

    add_settings_field(
        'xzit_ucm_roles_can_view_logs',
        __( 'Roles that can view logs', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_roles_can_view_logs', ['administrator'] );
            $roles = get_editable_roles();
            echo '<select id="xzit_ucm_roles_can_view_logs" name="xzit_ucm_roles_can_view_logs[]" multiple>';
            foreach ( $roles as $role => $details ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $role ),
                    selected( in_array( $role, $value, true ), true, false ),
                    esc_html( $details['name'] )
                );
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__( 'Select the roles that can view the logs.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // enable labels (e.g. names) for every address
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_enable_labels',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ]
    );

    add_settings_field(
        'xzit_ucm_enable_labels',
        __( 'Enable Labels for Emails?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_enable_labels', 0 );
            printf(
                '<input type="checkbox" id="xzit_ucm_enable_labels" name="xzit_ucm_enable_labels" value="1" %s />',
                checked( 1, $value, false )
            );
            echo '<p class="description">' . esc_html__( 'Enable this to allow users to set labels for their emails.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );

    // require labels (only if enabled)
    register_setting(
        'xzit_ucm_settings',
        'xzit_ucm_require_labels',
        [
            'type'              => 'boolean',
            'sanitize_callback' => 'absint',
            'default'           => 0,
        ]
    );

    add_settings_field(
        'xzit_ucm_require_labels',
        __( 'Require Labels for Emails?', 'user-contact-mails' ),
        function() {
            $value = get_option( 'xzit_ucm_require_labels', 0 );
            printf(
                '<input type="checkbox" id="xzit_ucm_require_labels" name="xzit_ucm_require_labels" value="1" %s />',
                checked( 1, $value, false )
            );
            echo '<p class="description">' . esc_html__( 'Enable this to require labels for emails. This only works if labels are enabled.', 'user-contact-mails' ) . '</p>';
        },
        'xzit_ucm_settings',
        'xzit_ucm_section_general'
    );
}

/**
 * Render the settings page for User Contact Mails.
 *
 * @return void
 */
function xzit_ucm_render_settings(): void {

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Isset is safe in this context, for further checks the wp_unslash() function is used.
    $tab = isset( $_GET['tab'] )
        ? sanitize_key( wp_unslash( $_GET['tab'] ) )
        : 'settings';

    if ( isset( $_GET['tab'] ) && isset( $_GET['ucm_nonce'] ) ) {
        check_admin_referer( 'xzit_ucm_settings', 'ucm_nonce' );
    }

    if ( isset( $_GET['ucm_log_cleared'] ) ) {
        echo '<div class="updated notice is-dismissible"><p>'
            . esc_html__( 'Log file has been cleared.', 'user-contact-mails' )
            . '</p></div>';
    }

    echo '<div class="wrap">';
    echo '<h1>'
        . esc_html__( 'User Contact Mails', 'user-contact-mails' )
        . ' <span class="components-badge is-info">'
        . esc_html( XZIT_UCM_VERSION )
        . '</span></h1>';
    echo '<p class="description">'
        . esc_html__( 'Manage user contact emails and settings.', 'user-contact-mails' )
        . '</p>';

    // Base URL for tab links.;
    $base_url = add_query_arg(
        array(
            'page'      => 'xzit_ucm_settings',
            'ucm_nonce' => wp_create_nonce( 'xzit_ucm_settings' ),
        ),
        admin_url( 'tools.php' )
    );

    // Tab navigation.
    echo '<nav class="nav-tab-wrapper">';
    if ( xzit_ucm_user_can_view_list() ) {
        printf(
            '<a href="%s" class="nav-tab%s">%s</a>',
            esc_url( add_query_arg( 'tab', 'view', $base_url ) ),
            esc_attr( $tab === 'view' ? ' nav-tab-active' : ''),
            esc_html__( 'Show List', 'user-contact-mails' )
        );
    }
    if ( xzit_ucm_user_can_manage() ) {
        printf(
            '<a href="%s" class="nav-tab%s">%s</a>',
            esc_url( add_query_arg( 'tab', 'settings', $base_url ) ),
            esc_attr($tab === 'settings' ? ' nav-tab-active' : ''),
            esc_html__( 'Settings', 'user-contact-mails' )
        );
    }
    if ( xzit_ucm_user_can_view_log() ) {
        printf(
            '<a href="%s" class="nav-tab%s">%s</a>',
            esc_url( add_query_arg( 'tab', 'logs', $base_url ) ),
            esc_attr($tab === 'logs' ? ' nav-tab-active' : ''),
            esc_html__( 'View Logs', 'user-contact-mails' )
        );
    }
    echo '</nav>';

    // Render content based on the active tab.
    switch ( $tab ) {
        case 'logs':
            include XZIT_UCM_PLUGIN_DIR . 'includes/html/view-log-template.php';
            break;

        case 'settings':
            if ( ! xzit_ucm_user_can_manage() ) {
                xzit_ucm_log(
                    sprintf(
                        'User %s (%d) attempted to access the settings page without permission.',
                        wp_get_current_user()->user_login,
                        get_current_user_id()
                    ),
                    'error'
                );
                wp_die( esc_html__( 'You do not have permission to manage these settings.', 'user-contact-mails' ) );
            }

            echo '<form method="post" action="options.php">';
            settings_fields( 'xzit_ucm_settings' );
            do_settings_sections( 'xzit_ucm_settings' );
            submit_button();
            echo '</form>';
            break;

        default:
            xzit_ucm_view_list_table();
            break;
    }

    echo '</div>';
}
