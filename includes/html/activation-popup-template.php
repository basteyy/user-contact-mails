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

?><script type="text/javascript">
    /* <![CDATA[ */
    jQuery(function($){
        // Erstelle das Dialog-Content-Element
        var $dialogContent = $(
            '<div id="ucm-activated-dialog" title="<?php echo esc_js( __( 'User Contact Mails Activated!', 'user-contact-mails' ) ); ?>">'
            +   '<p><?php echo esc_js( __( 'Thank you for activating and trusting our plugin.', 'user-contact-mails' ) ); ?></p>'
            +   '<p><?php echo esc_js( __( 'With this plugin, users can add additional contact information via their profile page.', 'user-contact-mails' ) ); ?></p>'
            +   '<p><?php echo esc_js( __( 'Administration and export are available under Tools > User Contact Mails.', 'user-contact-mails' ) ); ?></p>'
            +   '<p><?php echo esc_js( __( 'If you find any errors, please report them to me (basteyy).', 'user-contact-mails' ) ); ?></p>'
            + '</div>'
        );

        // Initialisiere den Dialog mit zwei Buttons
        $dialogContent.dialog({
            modal: true,
            width: 450,
            buttons: [
                {
                    text: '<?php echo esc_js( __('Go to Tools » User Contact Mails', 'user-contact-mails') ); ?>',
                    class: 'button button-primary',
                    click: function() {
                        window.location.href = '<?php echo esc_js( admin_url( 'tools.php?page=xzit_ucm_settings' ) ); ?>';
                    }
                },
                {
                    text: '<?php echo esc_js( __('Close', 'user-contact-mails' ) ); ?>',
                    click: function() {
                        $(this).dialog('close');
                    }
                }
            ]
        });
    });
    /* ]]> */
</script>

