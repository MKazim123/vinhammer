<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' ); 

?>
<h2>Account Details</h2>

<form class="woocommerce-EditAccountForm edit-account woocommerce-form-fields-wrapper gform_wrapper" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

	<?php do_action( 'woocommerce_edit_account_form_start' ); 
	
	$attachment_id = get_user_meta( $user_id, 'image', true );

	// Remove Profile Image
	if (isset($_GET['rm_profile_image_id']) && $attachment_id == $_GET['rm_profile_image_id']) {
		wp_delete_attachment($attachment_id);
		
		if (delete_user_meta($user_id, 'image')) {
		wp_delete_attachment($attachment_id);
		}
		?><script>window.location='<?php echo wc_get_account_endpoint_url('edit-account') ?>';</script><?php
		exit();
	}

	$img_field_text = "Upload photo";

    // True
    if ( $attachment_id ) {
		$img_field_text = "Change photo";
	}
	?>
	<div class="profile-photo-wrapper d-flex gap-20 mb-2 w-100 flex-wrap">
		<?php echo get_avatar($user->user_email);?>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide upload-field-wrap">
			<label for="image" class="mb-1 profile-pic-title"><?php esc_html_e( "Upload your photo", 'woocommerce' ); ?></label>
			<label for="image" class="mb-1 profile-pic-label"><img src="<?php echo get_stylesheet_directory_uri()?>/images/profile-camera.svg" /> <?php esc_html_e( $img_field_text, 'woocommerce' ); ?>
			<input type="file" onchange="readURL(this);" class="woocommerce-Input woocommerce-Input--image input-image profile-pic" id="image" name="image" accept="image/*"></label>
		</p>
	</div>


	<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
		<label for="account_first_name"><?php esc_html_e( 'First Name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
	</p>
	<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
		<label for="account_last_name"><?php esc_html_e( 'Last Name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide w-100">
		<label for="account_display_name"><?php esc_html_e( 'Display Name', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" /> <span><em><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'woocommerce' ); ?></em></span>
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="account_email"><?php esc_html_e( 'Email', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
		<label for="billing_phone"><?php esc_html_e( 'Phone Number', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--phone input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_phone', true) ); ?>" class="regular-text">
	</p>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide w-100">
		<label for="billing_company"><?php esc_html_e( 'Company', 'woocommerce' ); ?></label>
		<input type="text" class="woocommerce-Input woocommerce-Input--phone input-text" name="billing_company" id="billing_company" value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_company', true) ); ?>" class="regular-text">
	</p>

	<div class="w-100 field-gray-wrapper">
		<p class="mt-2 mb-1"><strong><?php esc_html_e( 'Password change', 'woocommerce' ); ?></strong></p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide w-100">
			<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide w-100">
			<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide w-100">
			<label for="password_2"><?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
		</p>
	</div>
	<div class="clear"></div>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<p>
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>
<script>
function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			jQuery('img.avatar').attr('src', e.target.result);
			jQuery('img.avatar').removeAttr('srcset');
		};
		reader.readAsDataURL(input.files[0]);
	}
}
</script>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
