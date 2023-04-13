<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$uwa_proxy = $product->get_uwa_auction_proxy();
$address = get_field('city_state');
$uwa_expired = $product->is_uwa_expired();
$uwa_started = $product->is_uwa_live();
$total_bids = count($product->uwa_auction_log_history());
if($uwa_proxy == 'yes'){
	$winner_name = $product->get_uwa_proxy_winner_name();
	$winner_id = $product->get_uwa_auction_current_bider();
} else {
	$winner_name = $product->get_uwa_winner_name();
	$winner_id = $product->get_uwa_auction_current_bider();
}
?>
<li <?php wc_product_class( 'd-flex flex-direction-column ', $product ); ?>>
	<?php 
	$thumbnail = getListingThumbnail($product->get_id());
	?>
	<a href="<?php echo get_the_permalink();?>">
		<img src="<?php echo $thumbnail?>" class="auction-img mb-2" />
	</a>
	<!-- Title -->
	<?php do_action( 'woocommerce_shop_loop_item_title' );?>

	<div class="tag-row d-flex align-items-center gap-10 mb-1">
		<?php if (($uwa_expired === FALSE )):?>
			<span class="tag reserve-tag"><?php echo (get_field('like_to_add_a_reserve')['value'] == "Yes") ? 'Reserve' : 'No Reserve' ?></span>
		<?php endif;?>
		<span class="tag"><?php the_field('indicated_mileage') ?> miles</span>
		
		<?php if(!empty($address['city']) && !empty($address['state'])):?>
			<span class="tag"><?php echo $address['city'].', '.$address['state'] ?></span>
		<?php endif;?>

	</div>

	<?php if (($uwa_expired === FALSE )):?>
		<div class="divider"></div>
		
		<?php if($uwa_started === FALSE):?>
			<div class="tag-row d-flex justify-content-between align-items-center mb-1 auction-desc">
				<span class="tag">Auction Starting</span>
				<span class="text">
					<?php echo date_i18n( get_option( 'date_format' ),  strtotime( $product->get_uwa_auction_start_time() ));  ?>  
					<?php echo date_i18n( get_option( 'time_format' ),  strtotime( $product->get_uwa_auction_start_time() ));  ?>
				</span>
			</div>
		<?php endif;?>

		<div class="tag-row d-flex justify-content-between align-items-center mb-1 auction-desc">
			<span class="tag">Auction Ending</span>
			<span class="text">
				<?php echo date_i18n( get_option( 'date_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>  
				<?php echo date_i18n( get_option( 'time_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>
			</span>
		</div>
		<?php if($uwa_started === TRUE):?>
			<div class="tag-row d-flex justify-content-between align-items-center mb-1 auction-desc">
				<span class="tag time-tag">Time Left</span>
				<span class="text"><?php echo do_shortcode('[countdown id="'. $product->get_id() .'"]') ?></span>
			</div>
		<?php endif;?>

		<div class="divider"></div>
		
		<div class="tag-row d-flex justify-content-between align-items-center mb-2">
			<span class="tag">Current Bid (<?php echo $total_bids; ?> Bid<?php echo $total_bids > 1 ? 's' : ''; ?>)</span>
			<span class="text active"><?php echo ( ($uwa_started === TRUE && !empty($product->get_uwa_auction_current_bid())) ? wc_price($product->get_uwa_auction_current_bid()) : wc_price($product->get_uwa_auction_start_price()) );?></span>
		</div>
		
		<div class="tag-row d-flex justify-content-between align-items-center gap-20">
			<?php 
			
			if( get_option( 'woo_ua_auctions_watchlists' ) == 'yes' ) {	
				/* for Single page */ 
				do_action('ultimate_woocommerce_auction_before_bid_form');			
			}
		
			?>
			<a href="<?php echo get_the_permalink()?>" class="wp-element-button place-bid">Place Bid</a>
		</div>
	<?php elseif (($uwa_expired === TRUE ) && empty($product->get_uwa_auction_fail_reason())):?>
		<div class="divider"></div>
		<div class="tag-row d-flex justify-content-between align-items-center mb-1">
			<span class="tag">Winning Bid</span>
			<span class="text"><?php echo wc_price($product->get_uwa_auction_current_bid());?></span>
		</div>
		<div class="tag-row d-flex justify-content-between align-items-center mb-2">
			<span class="tag">Sold to</span>
			<span class="text profile-name"><?php echo get_profile_pic($winner_id); ?> <?php echo $winner_name; ?></span>
		</div>
		<div class="expired-row ended">
			<p class="expired-title m-0">Ended</p>
		</div>
	<?php else:?>
		<div style="min-height: 50px"></div>
		<div class="expired-row">
			<p class="expired-title m-0">Expired</p>
			<p class="expired-desc">Auction expired because there were no bids.</p>
		</div>
	<?php endif;?>
</li>
