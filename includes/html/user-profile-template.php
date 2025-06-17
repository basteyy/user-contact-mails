<?php
/**
 * @author xzit.online <hallo@xzit.email>
 * @website https://github.com/basteyy
 * @website https://xzit.online
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** @var int $user_id */
if (!isset($user_id)) {
    wp_die(esc_html__('User is missing', 'user-contact-mails'));
}

/** @var array $emails Array of mails and labels */
$emails = xzit_ucm_get_user_emails(get_current_user_id());

/** @var int $primary_helper_int A dirty solution to connect selection and mail */
$primary_helper_int = 0;

wp_nonce_field( 'xzit_ucm_save_profile', 'ucm_profile_nonce', true, true );
?>

<h2><?php echo esc_html__('Contact Emails', 'user-contact-mails'); ?></h2>

<?php
if (empty($emails)) {
    printf(
        '<div class="notice notice-info inline"><p>%s</p></div>',
        esc_html__('No additional emails added yet. Use the button to add one.', 'user-contact-mails')
    );
}

if (xzit_ucm_labels_enabled() && xzit_ucm_labels_required()) {
    printf(
        '<div class="notice notice-info inline"><p>%s</p></div>',
        esc_html__('Name your address (for example recipients name or branch name).', 'user-contact-mails')
    );
}
?>

<div class="notice notice-error ucm-notice-submit-form inline hidden">
    <p><?php
        esc_html_e('Dont forget to submit the form, in order to save changes.', 'user-contact-mails'); ?></p>
</div>

<ul id="ucm-email-list" style="list-style:none;margin:0;padding:0;">
    <?php
    foreach ($emails as $data) {

        if (!is_array($data) || !isset($data['address'])) {
            continue; // Skip if data is not an array or address is missing
        }

        $address = esc_attr($data['address']);
        $label = $data['label'] ?? $address;
        $primary = isset($data['primary']) ? (bool)$data['primary'] : false;

        ?>
        <li class="ucm-email-item" style="margin-bottom:.5em;">
            <input
                    type="email"
                    name="ucm_emails[]"
                    class="regular-text"
                    value="<?php echo esc_attr( $address ); ?>"
                    placeholder="<?php echo esc_attr( $label ); ?>"
                <?php if ( xzit_ucm_validate_emails() ) : ?>
                    required
                    pattern="<?php echo esc_attr( '[^@]+@[^@]+\.[^@]+' ); ?>"
                    title="<?php echo esc_attr__( 'Invalid email', 'user-contact-mails' ); ?>"
                <?php endif; ?>
            />
            <?php
            if (xzit_ucm_labels_enabled()) : ?>
                <input type="text" name="ucm_labels[]" class="regular-text ucm-email-label" value="<?php echo esc_attr($label); ?>"
                       placeholder="<?php echo esc_attr__('Label (e.g. Work)', 'user-contact-mails'); ?>" <?php echo esc_attr(xzit_ucm_labels_required() ? 'required' : ''); ?> />
            <?php
            endif; ?>

            <?php
            if (xzit_ucm_user_can_choose_primary()) : ?>
                <label>
                    <input type="radio" name="ucm_primary_email" value="<?php echo esc_attr($primary_helper_int) ?>" <?php checked($primary); ?> />
                    <?php echo esc_html__('Primary Email', 'user-contact-mails'); ?>
                </label>
                <?php
                $primary_helper_int++;
            endif;

            if (count($emails) > 1) : ?>
                <button type="button" class="button-link ucm-remove-email"><?php echo esc_html__('Remove', 'user-contact-mails'); ?></button>
            <?php
            endif; ?>
        </li>

    <?php
    } ?>

</ul>

<?php
printf(
    '<p><button type="button" id="ucm-add-email" class="button">%s</button></p>',
    esc_html__('Add another email', 'user-contact-mails')
);
?>
<script type="text/html" id="ucm-email-template">
    <li class="ucm-email-item" style="margin-bottom:.5em;">
        <label><input type="email" name="ucm_emails[]" class="regular-text"
                      placeholder="user@example.com" <?php echo xzit_ucm_validate_emails() ? 'required pattern="[^@]+@[^@]+\.[^@]+" title="Invalid email"' : '' ?> /></label>
        <?php
        if ( xzit_ucm_labels_enabled() ): ?>
            <label><input type="text" name="ucm_labels[]" class="regular-text ucm-email-label" placeholder="<?php
                esc_attr_e('Label (e.g. Work)', 'user-contact-mails'); ?>" <?php echo esc_attr(xzit_ucm_labels_required() ? 'required' : '') ?> /></label>
        <?php
        endif;

        if ( xzit_ucm_user_can_choose_primary() ) : ?>
            <label>
                <input type="radio" name="ucm_primary_email"  />
                <?php echo esc_html__('Primary Email', 'user-contact-mails'); ?>
            </label>
        <?php endif; ?>
        <button type="button" class="button-link ucm-remove-email"><?php
            esc_html_e('Remove', 'user-contact-mails'); ?></button>
    </li>
</script>
<script>
    (function ($) {
        $(function () {

            $('.ucm-remove-email').on('click', function () {
                $(this).closest('.ucm-email-item').remove();
                toggleAddButton();
                // Show notice if no emails left
                $('.ucm-notice-submit-form').removeClass('hidden');
            });

            var maxEmails = <?php echo esc_js(xzit_ucm_max_emails_per_user()); ?>,
                $list = $('#ucm-email-list'),
                $addButton = $('#ucm-add-email'),
                validateOn = <?php echo xzit_ucm_validate_emails() ? 'true' : 'false'; ?>,
                labelsOn = <?php echo xzit_ucm_labels_enabled() ? 'true' : 'false'; ?>,
                labelsReq = <?php echo xzit_ucm_labels_required() ? 'true' : 'false'; ?>;

            function toggleAddButton() {
                $addButton.prop('disabled', $list.children().length >= maxEmails);
            }

            function primaryWalker () {
                // Walks through the list and sets the primary fields value to number of the item
                $list.find('input[type="radio"]').each(function (index) {
                    $(this).val(index);
                });
            }

            toggleAddButton();

            $addButton.on('click', function () {
                if ($list.children().length < maxEmails) {
                    var $item = $($('#ucm-email-template').html());
                    $list.append($item);
                    toggleAddButton();
                    <?php
                    if (WP_DEBUG) {
                    // Auto fill with random email for testing
                    ?>
                    let random_number = Math.floor(Math.random() * 10000);
                    $item.find('input[type="email"]').val('test' + random_number + '@example.com');
                    <?php
                    if ( xzit_ucm_labels_enabled() ) {
                    ?>
                    $item.find('.ucm-email-label').val('Test ' + random_number);
                    <?php
                    }
                    }
                    ?>


                    $('.ucm-notice-submit-form').removeClass('hidden');

                    primaryWalker();
                }
            });

            $list.on('click', '.ucm-remove-email', function () {
                $(this).closest('.ucm-email-item').remove();
                toggleAddButton();
                primaryWalker();
            });

            if (validateOn) {
                $('#your-profile').on('submit', function (e) {
                    var invalid = false;
                    $list.find('input[type="email"]').each(function () {
                        if (this.validity && !this.validity.valid) {
                            invalid = true;
                            console.log('Invalid email:', $(this).val());
                            $(this).css('border-color', 'red');
                        }
                    });
                    if (labelsOn && labelsReq) {
                        $list.find('.ucm-email-label').each(function () {
                            if (!$(this).val().trim()) {
                                invalid = true;
                                console.log('Invalid label:', $(this).val());
                                $(this).css('border-color', 'red');
                            }
                        });
                    }
                    if (invalid) {
                        alert('<?php echo esc_js(__('Please fix highlighted fields.', 'user-contact-mails')); ?>');
                        e.preventDefault();
                    }
                });
            }
        });
    })(jQuery);
</script>