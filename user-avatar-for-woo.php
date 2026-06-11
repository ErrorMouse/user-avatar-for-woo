<?php
/**
 * Plugin Name:         User Avatar for Woo
 * Description:         Allows users to upload their own profile picture on the WooCommerce My Account page.
 * Version:             1.0
 * Requires at least:   5.2
 * Requires PHP:        7.4
 * Requires Plugins:    woocommerce
 * Author:              Chout
 * Author URI:          https://profiles.wordpress.org/nmtnguyen56/
 * License:             GPLv2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         user-avatar-for-woo
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin Update Checker
require __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://raw.githubusercontent.com/ErrorMouse/user-avatar-for-woo/refs/heads/main/user-avatar-for-woo.json',
	__FILE__,
	'user-avatar-for-woo'
);
// End

// REWRITTEN: Use a unique and longer prefix for all constants.
define( 'ERRUAFW_VERSION', '1.0' );

add_action( 'plugins_loaded', 'errplugin_user_avatar_for_woo_check_dependencies' );
function errplugin_user_avatar_for_woo_check_dependencies() {
    if ( class_exists( 'WooCommerce' ) ) {
        errplugin_user_avatar_for_woo_init();
    } else {
        add_action( 'admin_notices', 'errplugin_user_avatar_for_woo_missing_wc_notice' );
    }
}

function errplugin_user_avatar_for_woo_init() {

    // Enqueue scripts and styles.
    add_action( 'wp_enqueue_scripts', 'errplugin_user_avatar_for_woo_assets' );

    // Add enctype to edit account form.
    add_action( 'woocommerce_edit_account_form_tag', 'errplugin_user_avatar_for_woo_enctype' );

    // Add avatar field to edit account form.
    add_action( 'woocommerce_edit_account_form_start', 'errplugin_user_avatar_for_woo_form' );

    // Handles uploading, saving, and removing of the avatar.
    add_action( 'woocommerce_save_account_details', 'errplugin_user_avatar_for_woo_save', 10, 1 );

    // Filter get_avatar_data to replace the default avatar with the custom one.
    add_filter( 'get_avatar_data', 'errplugin_user_avatar_for_woo_replace', 100, 2 );
}

function errplugin_user_avatar_for_woo_missing_wc_notice() { ?>

    <div class="notice notice-error is-dismissible">
        <p>
            <?php
            echo wp_kses_post(
                sprintf(
                    /* translators: %s: Plugin name. */
                    __( 'The %s plugin requires WooCommerce to be installed and activated. Please install or activate WooCommerce.', 'user-avatar-for-woo' ),
                    '<strong>User Avatar for Woocommerce</strong>'
                )
            );
            ?>
        </p>
    </div>

<?php }

/**
 * Enqueue scripts and styles.
 */
function errplugin_user_avatar_for_woo_assets($hook_suffix) {

    if ( is_account_page() ) {
        wp_enqueue_style(
            'user-avatar-for-woo-css',
            plugins_url('assets/css/user-avatar-for-woo.css', __FILE__),
            array(),
            ERRUAFW_VERSION
        );
        wp_enqueue_script(
            'user-avatar-for-woo-js',
            plugins_url('assets/js/user-avatar-for-woo.js', __FILE__),
            array( 'jquery' ),
            ERRUAFW_VERSION,
            true
        );

        wp_localize_script(
            'user-avatar-for-woo-js',
            'errAvatarL10n',
            array(
                'selectedText' => __( 'Selected: ', 'user-avatar-for-woo' ),
            )
        );
    }
}

/**
 * Add enctype to edit account form.
 */
function errplugin_user_avatar_for_woo_enctype() {
    echo 'enctype="multipart/form-data"';
}

/**
 * Add avatar field to edit account form.
 */
function errplugin_user_avatar_for_woo_form() {
    $user_id = get_current_user_id();
    // Security: Ensure avatar ID is an integer.
    $avatar_id = (int) get_user_meta( $user_id, '_user_avatar', true );
    ?>
    <fieldset id="errplugin_user_avatar_for_woo">
        <legend><?php esc_html_e( 'Profile Picture', 'user-avatar-for-woo' ); ?></legend>

        <div class="current-avatar">
            <?php
            if ( $avatar_id ) {
                echo wp_get_attachment_image( $avatar_id, 'full' );
            } else {
                echo get_avatar( $user_id, 96 );
            }
            ?>
        </div>

        <div class="upload-avatar">
            <input type="file" name="user_avatar" id="user_avatar" accept="image/*" class="input-file-hidden">
            <label for="user_avatar" class="btn-upload-avatar woocommerce-Button button">
                <?php esc_html_e( 'Select photo', 'user-avatar-for-woo' ); ?>
            </label>
            <p class="selected-file-name"></p>
            <p class="description">
                <?php esc_html_e( '(Upload an image file: JPG, PNG, GIF. Maximum size: 1MB.)', 'user-avatar-for-woo' ); ?>
            </p>
        </div>

        <?php if ( $avatar_id ) { ?>
            <div class="remove-avatar-wrapper">
                <input type="checkbox" name="errplugin_remove_user_avatar" id="errplugin_remove_user_avatar" value="true">
                <label for="errplugin_remove_user_avatar" class="remove-avatar-label">
                    <?php esc_html_e( 'Delete current profile picture.', 'user-avatar-for-woo' ); ?>
                </label>
            </div>
        <?php } ?>
    </fieldset>
    <div class="clear"></div>
    <?php
}

/**
 * Handles uploading, saving, and removing of the avatar.
 */
function errplugin_user_avatar_for_woo_save( $user_id ) {
    // Security: Check if the current user can edit this profile.
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return;
    }

    // This fixes the 'not unslashed' and 'non-sanitized' warnings for the nonce.
    $nonce_value = isset( $_POST['save-account-details-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['save-account-details-nonce'] ) ) : '';

    // Verify the sanitized nonce.
    if ( ! wp_verify_nonce( $nonce_value, 'save_account_details' ) ) {
        return;
    }

    // Handle explicit avatar removal via checkbox.
    if ( ! empty( $_POST['errplugin_remove_user_avatar'] ) ) {
        $avatar_id_to_delete = (int) get_user_meta( $user_id, '_user_avatar', true );
        delete_user_meta( $user_id, '_user_avatar' );
        if ( $avatar_id_to_delete > 0 ) {
            // Security: Only delete the file if it was actually uploaded by this user.
            // This prevents deleting the site logo or default avatars if an admin assigned them manually.
            $old_avatar_post = get_post( $avatar_id_to_delete );
            if ( $old_avatar_post && (int) $old_avatar_post->post_author === $user_id ) {
                wp_delete_attachment( $avatar_id_to_delete, true );
            }
        }
        wc_add_notice( __( 'Profile picture has been deleted.', 'user-avatar-for-woo' ), 'success' );
        return;
    }

    // Handle avatar upload.
    if ( ! empty( $_FILES['user_avatar'] ) && isset( $_FILES['user_avatar']['error'] ) && $_FILES['user_avatar']['error'] === UPLOAD_ERR_OK ) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $uploaded_file = isset( $_FILES['user_avatar'] ) ? $_FILES['user_avatar'] : null;

        // --- CHECK FILE SIZE ---
        $max_size_in_bytes = 1 * 1024 * 1024; // 1MB
        if ( $uploaded_file['size'] > $max_size_in_bytes ) {
            wc_add_notice(
                sprintf(
                    /* translators: %d: Maximum file size in megabytes. */
                    esc_html__( 'Error: Image file is too large. Please upload a file smaller than %dMB.', 'user-avatar-for-woo' ),
                    $max_size_in_bytes / 1024 / 1024
                ),
                'error'
            );
            return;
        }

        // --- FILE TYPE CHECK ---
        $allowed_mime_types = [ 'image/jpeg', 'image/png', 'image/gif' ];
        $file_tmp_name      = $uploaded_file['tmp_name'];
        
        if ( function_exists( 'finfo_open' ) ) {
            $file_info          = finfo_open( FILEINFO_MIME_TYPE );
            $uploaded_mime_type = finfo_file( $file_info, $file_tmp_name );
            finfo_close( $file_info );

            if ( ! in_array( $uploaded_mime_type, $allowed_mime_types, true ) ) {
                wc_add_notice( __( 'Error: Invalid file format. Please upload only JPG, PNG, or GIF image files.', 'user-avatar-for-woo' ), 'error' );
                return;
            }
        }

        // --- PROCESS UPLOAD ---
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Passed 0 as post_id to prevent attaching the image to a random post with the same ID as the user.
        $attachment_id = media_handle_upload( 'user_avatar', 0 );

        if ( is_wp_error( $attachment_id ) ) {
            wc_add_notice( $attachment_id->get_error_message(), 'error' );
        } else {
            // Delete the old avatar if it exists and is not the same as the new one.
            $old_avatar_id = (int) get_user_meta( $user_id, '_user_avatar', true );
            if ( $old_avatar_id > 0 && $old_avatar_id !== $attachment_id ) {
                // Security: Only delete the file if it was actually uploaded by this user.
                $old_avatar_post = get_post( $old_avatar_id );
                if ( $old_avatar_post && (int) $old_avatar_post->post_author === $user_id ) {
                    wp_delete_attachment( $old_avatar_id, true );
                }
            }
            update_user_meta( $user_id, '_user_avatar', $attachment_id );
            wc_add_notice( __( 'Profile picture updated successfully.', 'user-avatar-for-woo' ), 'success' );
        }
    }
}


/**
 * Filter get_avatar_data to replace the default avatar with the custom one.
 */
function errplugin_user_avatar_for_woo_replace( $args, $id_or_email ) {
    $user_id = 0;
    if ( is_numeric( $id_or_email ) ) {
        $user_id = (int) $id_or_email;
    } elseif ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
        $user_id = (int) $id_or_email->user_id;
    } elseif ( is_string( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        if ( $user ) {
            $user_id = $user->ID;
        }
    }

    if ( $user_id > 0 ) {
        $avatar_id = (int) get_user_meta( $user_id, '_user_avatar', true );
        if ( $avatar_id ) {
            $avatar_url = wp_get_attachment_image_url( $avatar_id, array( $args['width'], $args['height'] ) );
            if ( $avatar_url ) {
                $args['url'] = $avatar_url;
                $args['found_avatar'] = true;
            }
        }
    }
    return $args;
}

/* Donate */
add_action('admin_enqueue_scripts', 'errplugin_user_avatar_for_woo_admin_settings_scripts');
function errplugin_user_avatar_for_woo_admin_settings_scripts($hook_suffix) {

    $is_plugins_page  = ( 'plugins.php' === $hook_suffix );

    // Styles for the donate link on the plugins page.
    if ( $is_plugins_page ) {
        $donate_css = "
            .err-donate-link {
                font-weight: bold;
                background: linear-gradient(90deg, #0066ff, #00a1ff, rgb(255, 0, 179), #0066ff);
                background-size: 200% auto;
                color: #fff;
                -webkit-background-clip: text;
                -moz-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
                animation: errGradientText 2s linear infinite;
            }
            @keyframes errGradientText {
                to { background-position: -200% center; }
            }";
        wp_add_inline_style( 'wp-admin', $donate_css );
    }
}

function erruafw_donate_link_html() {
	$donate_url = 'https://err-mouse.id.vn/donate';
	printf(
		'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="err-donate-link" aria-label="%2$s"><span>%3$s 🚀</span></a>',
		esc_url( $donate_url ),
		esc_attr__( 'Donate to support this plugin', 'user-avatar-for-woo' ),
		esc_html__( 'Donate', 'user-avatar-for-woo' )
	);
}

add_filter( 'plugin_row_meta', 'erruafw_row_meta', 10, 2 );
function erruafw_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		ob_start();
		erruafw_donate_link_html();
		$links['donate'] = ob_get_clean();
	}
	return $links;
}