<?php
/**
 * Plugin Name:     User Contact Mails
 * Plugin URI:      https://github.com/basteyy/user-contact-mails
 * Description:     Allow users to store additional contact emails and provide admins a management interface.
 * Version:         1.0.0
 * Author:          basteyy
 * Author URI:      https://github.com/basteyy
 * Text Domain:     user-contact-mails
 * Domain Path:     /languages
 * License:         CC0-1.0
 * License URI:     https://creativecommons.org/publicdomain/zero/1.0/
 *
 * @package         Xzit_UCM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

const XZIT_UCM_VERSION = '1.0.0';
const XZIT_UCM_TABLE_NAME = 'xzit_ucm_logs';

global $wpdb;
$wpdb->ucm_logs = $wpdb->prefix . XZIT_UCM_TABLE_NAME;

define( 'XZIT_UCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include required files.
require_once XZIT_UCM_PLUGIN_DIR . 'includes/functions.php';
require_once XZIT_UCM_PLUGIN_DIR . 'includes/user-manage-emails.php';
require_once XZIT_UCM_PLUGIN_DIR . 'includes/admin-settings.php';

// Load plugin textdomain.
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain(
        'user-contact-mails',     // your text-domain
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/' // note the trailing slash
    );
} );

// Enqueue admin styles.
add_action(
    'admin_enqueue_scripts',
    function() {
        wp_enqueue_style( 'wp-components' );
    }
);

// Register plugin activation hook for default settings.
register_activation_hook(
    __FILE__,
    function() {
        add_option(
            'xzit_ucm_settings',
            array(
                'enabled'             => 1,
                'roles_can_view'      => array( 'administrator' ),
                'max_emails_per_user' => 5,
                'require_one_email'   => 1,
                'logging_enabled'     => 0,
            )
        );
    }
);

// Setup activation routines.
register_activation_hook( __FILE__, 'xzit_ucm_activate' );
register_activation_hook( __FILE__, 'xzit_ucm_set_activation_flag' );

// Show activation popup if needed.
add_action( 'admin_footer', 'xzit_ucm_maybe_show_activation_popup' );

// Register admin menu and settings.
add_action( 'admin_menu', 'xzit_ucm_admin_menu' );
add_action( 'admin_init', 'xzit_ucm_register_settings' );

// Add user profile fields.
add_action( 'show_user_profile', 'xzit_ucm_render_profile_fields' );
add_action( 'edit_user_profile', 'xzit_ucm_render_profile_fields' );

// Save user profile fields.
add_action( 'personal_options_update', 'xzit_save_profile_fields' );
add_action( 'edit_user_profile_update', 'xzit_save_profile_fields' );

// Handle export action.
add_action( 'admin_post_xzit_ucm_export', 'xzit_ucm_export' );

// Handle plugin deactivation.
register_deactivation_hook( __FILE__, 'xzit_ucm_on_deactivation' );

// Handle clear log action.
add_action(
    'admin_post_xzit_ucm_clear_logfile',
    'xzit_ucm_handle_clear_logfile'
);
