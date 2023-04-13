<?php
/**
 * My Account Dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$user_id  = get_current_user_id();
?>

<div class="dashboard-section">
	<h2>Dashboard</h2>
	<div class="top-content top-banner mb-3">
		<h4 class="hello-msg">Hello <?php echo $current_user->display_name; ?>!</h4>
		<p>From your account dashboard you can view your active bids, auctions, messages, manage your shipping and billing addresses, and edit your password and account details.</p>
	</div>
	<div class="dashboard-auction-wrapper mb-3">
		<p class="m-0 mb-2"><strong>Your Auctions</strong></p>
		<?php wc_get_template('myaccount/orders.php'); ?>
	</div>
	<div class="bid-wrapper">
		<p class="m-0 mb-2"><strong>Your Active Bids</strong></p>
		<?php echo uwa_front_user_bid_list_custom($user_id, "active");?>
	</div>
</div>
