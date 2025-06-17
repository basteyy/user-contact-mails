<?php
/**
 * @author xzit.online <hallo@xzit.email>
 * @website https://github.com/basteyy
 * @website https://xzit.online
 * @package Xzit_UCM
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Save the user profile fields for managing user contact emails.
 *
 * @param int $user_id The ID of the user being edited.
 * @return void
 */
function xzit_save_profile_fields( int $user_id ): void {
    if ( ! xzit_ucm_is_enabled() ) {
        wp_die( esc_html__( 'Plugin is not enabled.', 'user-contact-mails' ) );
    }

    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        wp_die( esc_html__( 'You are not allowed to edit the selected user.', 'user-contact-mails' ) );
    }

    if (
        ! isset( $_POST['ucm_profile_nonce'] ) ||
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- isset is safe and fore second step, wp_unslash is used to ensure the data is not tampered with.
        ! wp_verify_nonce( wp_unslash( $_POST['ucm_profile_nonce'] ), 'xzit_ucm_save_profile' )
    ) {
        wp_die( esc_html__( 'Security check failed.', 'user-contact-mails' ) );
    }

    $raw = filter_input( INPUT_POST, 'ucm_emails', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?: [];
    $raw = array_slice( wp_unslash( $raw ), 0, xzit_ucm_max_emails_per_user() );
    $emails = array_filter( array_map( 'sanitize_email', $raw ) );

    $labels = [];
    if ( xzit_ucm_labels_enabled() ) {
        $labels = array_map(
            'sanitize_text_field',
            array_slice(
                wp_unslash(
                    filter_input( INPUT_POST, 'ucm_labels', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) ?: []
                ),
                0,
                xzit_ucm_max_emails_per_user()
            )
        );

        if ( xzit_ucm_labels_required() ) {
            $labels = array_map( 'sanitize_text_field', $labels );
            $labels = array_filter(
                $labels,
                static function( $label ) {
                    return '' !== trim( $label );
                }
            );

            if ( count( $labels ) !== count( $emails ) ) {
                wp_die( esc_html__( 'Please provide labels for all emails.', 'user-contact-mails' ) );
            }
        }
    }

    $primary_index = null;
    if ( xzit_ucm_user_can_choose_primary() && isset( $_POST['ucm_primary_email'] ) ) {
        $primary_index = (int) filter_input( INPUT_POST, 'ucm_primary_email', FILTER_VALIDATE_INT );

        if ( ! isset( $emails[ $primary_index ] ) ) {
            wp_die( esc_html__( 'Invalid primary email selection.', 'user-contact-mails' ) );
        }
    }

    // Sanitize and filter emails.
    $emails = array_map( 'sanitize_email', $emails );
    $emails = array_filter( $emails );

    // Remove existing emails before re-adding.
    xzit_ucm_remove_email_from_user( $user_id );

    foreach ( $emails as $index => $email ) {
        $label      = $labels[ $index ] ?? $email;
        $is_primary = isset( $primary_index ) && $primary_index === $index;

        xzit_ucm_add_email_to_user(
            $user_id,
            $email,
            $label,
            overwrite: true,
            primary_email: $is_primary
        );
    }
}

/**
 * Render the profile fields for managing user contact emails.
 *
 * @param WP_User|int $user The user object or user ID.
 * @return void
 */
function xzit_ucm_render_profile_fields( WP_User|int $user ): void {
    xzit_ucm_enable_or_die();

    $user_id = xzit_ucm_get_user_id( $user );

    include XZIT_UCM_PLUGIN_DIR . 'includes/html/user-profile-template.php';
}
