<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


global $woocommerce, $product, $post;

get_header( 'shop' );

while ( have_posts() ) :
	the_post();

	$medias = array(
		"video"					=> array(),
		"interior"				=> array(),
		"exterior"				=> array(),
		"engine"				=> array(),
		"undercarriage"			=> array(),
		"other"					=> array(),
	);
	foreach(get_field('featured_videos') as $featured_videos){
		array_push($medias["video"], $featured_videos["url"]);
	}
	foreach(get_field('youtube_vimeo_links') as $youtube_vimeo_links){
		array_push($medias["video"], $youtube_vimeo_links["url"]);
	}
	foreach(get_field('interior_photos') as $interior_photos){
		array_push($medias["interior"], $interior_photos);
	}
	foreach(get_field('exterior_photos') as $exterior_photos){
		array_push($medias["exterior"], $exterior_photos);
	}
	foreach(get_field('engine_photos') as $engine_photos){
		array_push($medias["engine"], $engine_photos);
	}
	foreach(get_field('undercarriage_photos') as $undercarriage_photos){
		array_push($medias["undercarriage"], $undercarriage_photos);
	}
	foreach(get_field('other_photos') as $other_photos){
		array_push($medias["other"], $other_photos);
	}

	// Carfax
	$carfax_options = get_field( 'carfax_options');
	if ($carfax_options == "Upload") {
		$carfax = get_field( 'carfax_file') !== NULL ? get_field( 'carfax_file') : '';
	}
	else if ($carfax_options == "External Link") {
		$carfax = get_field( 'carfax_link') !== NULL ? get_field( 'carfax_link') : '';
	}
	echo do_shortcode('[shop_messages]');
	?>

	<div class="row-landing">
		<div class="site-container">
			<div class="product">
				<div class="top-back">
					<a href="javascript:history.back()" class="back">Back</a>
				</div>
				<div class="title-wrapper d-flex gap-20 justify-content-between align-items-center">
					<div class="heading">
						<h1><?php echo get_the_title(); ?></h1>
					</div>
					<div class="auction-action-area">
						<?php 
						if( get_option( 'woo_ua_auctions_watchlists' ) == 'yes' && get_the_author_meta('email') !== wp_get_current_user()->user_email) {	
							/* for Single page */ 
							do_action('ultimate_woocommerce_auction_before_bid_form');			
						}
						?>
						<div class="auction_share-wrapper">
							<a href="javascript:void(0)" class="share-btn">
								<svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.25 10.5C13.6562 10.5 14 10.8438 14 11.25V13.5C14 15.1875 12.6562 16.5 11 16.5H3C1.3125 16.5 0 15.1875 0 13.5V11.25C0 10.8438 0.3125 10.5 0.75 10.5C1.15625 10.5 1.5 10.8438 1.5 11.25V13.5C1.5 14.3438 2.15625 15 3 15H11C11.8125 15 12.5 14.3438 12.5 13.5V11.25C12.5 10.8438 12.8125 10.5 13.25 10.5ZM6.46875 0.71875L2.21875 4.6875C1.90625 5 1.90625 5.46875 2.1875 5.78125C2.46875 6.09375 2.9375 6.09375 3.25 5.8125L6.25 3V10.75C6.25 11.1875 6.5625 11.5 7 11.5C7.40625 11.5 7.75 11.1875 7.75 10.7812V3L10.7188 5.8125C11.0312 6.09375 11.5 6.09375 11.7812 5.78125C11.9062 5.625 12 5.4375 12 5.25C12 5.0625 11.9062 4.875 11.75 4.71875L7.5 0.75C7.21875 0.4375 6.75 0.4375 6.46875 0.71875Z" fill="#9A9EA7"/></svg>
								 Share this listing
							</a>
						</div>
					</div>
				</div>
			</div>

			<div class="gallery-items">
				<?php
				foreach ($medias as $cat => $assets) {
					?>
					<div class="gallery-item" gallery-cat="<?php echo $cat; ?>" gallery-enabled="<?php echo count($assets) > 0 ? "true" : "false"; ?>" gallery-<?php echo $cat; ?>>
						<div class="gallery-item-wrapper">
							<div class="gallery-item-thumbnail <?php echo $cat; ?>">
								<?php if ($cat == "video") { ?>
								<div class="gallery-item-thumbnail-icon">
									<svg width="29" height="33" viewBox="0 0 29 33" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M27.7145 14.253L3.85626 0.351456C2.14232 -0.647139 0 0.601105 0 2.59841V30.4017C0 32.3988 2.14232 33.6472 3.85626 32.6485L27.7145 18.7469C29.4285 17.7483 29.4285 15.2517 27.7145 14.253Z" fill="#FFFFFE"/>
									</svg>
								</div>
								<?php } ?>
								<?php
								if($cat == 'video'){
									if (!str_contains($assets[0], 'youtube') && !str_contains($assets[0], 'vimeo')) {
										?>
										<video>
											<source src="<?php echo $assets[0]; ?>#t=5,6" type="video/mp4">
											Your browser does not support the video tag.
										</video>
										<?php
									} 
									else {
										?>
											<img src="<?php echo getCategoryThumbnail($medias, $cat); ?>" />
										<?php
									}
									
								}
								else{
									?>
										<img src="<?php echo getCategoryThumbnail($medias, $cat); ?>" />
									<?php
								}
								?>
							</div>
							<div class="gallery-item-tag">
								<?php
								$tag_text = '';
								if (count($assets) > 0) {
									if (count($assets) > 1) {
										$tag_text = "+" . (count($assets) - 1) . " " . $cat;

										if ($cat == "video" || $cat == "other") {
											$tag_text .= "s";
										}
									}
									else {
										$tag_text = $cat;
									}
								}
								else {
									$tag_text .= "No " . $cat;

									if ($cat == "video") {
										$tag_text .= "s";
									}

									$tag_text .= " found";
								}
								?>
								<span><?php echo $tag_text; ?></span>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<!-- Auction Sticky Bar Start -->
			<?php 
				/* place bid using page load */
				if(method_exists( $product, 'get_type') && $product->get_type() == 'auction'){
					wc_get_template( 'single-product/uwa-bid.php' );
				}			
			?>
			<!-- Auction Sticky Bar End -->
			
			<div class="info-list">
				<div class="info-col">
					<div class="item">
						<div class="column">
							<span>Seller</span>
						</div>
						<div class="column">
							<span class="profile-name"><?php echo get_profile_pic(get_the_author_meta('ID')); ?> <?php echo get_the_author_meta('display_name'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Auction End Date</span>
						</div>
						<div class="column">
							<span>
								<?php echo date_i18n( get_option( 'date_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>  
								<?php echo date_i18n( get_option( 'time_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>
							</span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Vehicle Location</span>
						</div>
						<div class="column">
							<span><?php echo get_field('city') .', '. get_field('state'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>VIN Code</span>
						</div>
						<div class="column">
							<span><?php echo get_field('vin'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Year</span>
						</div>
						<div class="column">
							<span><?php echo get_field('year'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Make</span>
						</div>
						<div class="column">
							<span><?php echo get_field('make'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Model</span>
						</div>
						<div class="column">
							<span><?php echo get_field('model'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Body Style</span>
						</div>
						<div class="column">
							<span><?php echo get_field('body_style'); ?></span>
						</div>
					</div>
				</div>
				<div class="info-col">
					<div class="item">
						<div class="column">
							<span>Mileage</span>
						</div>
						<div class="column">
							<span><?php echo get_field('true_mileage') == 'No' ? get_field('actual_mileage') : get_field('indicated_mileage'); ?> miles</span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Engine Size</span>
						</div>
						<div class="column">
							<span><?php echo get_field('engine_size'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Transmission</span>
						</div>
						<div class="column">
							<span><?php echo get_field('car_transmission')['label']; ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Driveline</span>
						</div>
						<div class="column">
							<span><?php echo get_field('driveline'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Exterior Color</span>
						</div>
						<div class="column">
							<span><?php echo get_field('exterior_color'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Interior Color</span>
						</div>
						<div class="column">
							<span><?php echo get_field('interior_color'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Fuel Type</span>
						</div>
						<div class="column">
							<span><?php echo get_field('fuel_type'); ?></span>
						</div>
					</div>
					<div class="item">
						<div class="column">
							<span>Seller Type</span>
						</div>
						<div class="column">
							<span><?php echo get_field('private_party_or_dealer')['label']; ?></span>
						</div>
					</div>
				</div>
			</div>

			<div class="auction-description d-flex flex-wrap gap-20 justify-content-between">
				<div class="left-area gform_wrapper">
					<?php if(!empty($product->get_short_description())): ?>
						<div class="field-gray-wrapper disclaimer-box d-flex gap-20 align-item-start mb-3">
							<img src="<?php echo site_url()?>/wp-content/uploads/2023/02/favicon.png" alt="Vin Logo" style="max-width: 100px">
							<div>
								<h4 class="mb-1">Perspective</h4>
								<p class="m-0"><?php echo $product->get_short_description();?></p>
							</div>
						</div>
					<?php endif; ?>
					<?php if(!empty($product->get_description())): ?>
						<div class="highlights mb-3">
							<h4 class="mb-1">Highlights</h4>
							<p><?php echo $product->get_description();?></p>
						</div>
					<?php endif; ?>
					<div class="additional-features mb-3">
						<h4 class="mb-1">Additional Features</h4>
						<ul>
							<?php foreach(get_field('additional_features') as $val):?>
								<li><?php echo $val['label']?></li>
							<?php endforeach;?>
						</ul>
						<p><?php echo get_field('additional_unique_features');?></p>
					</div>
					<div class="history mb-3">
						<h4 class="mb-1">Vehicle History</h4>
						<p><?php echo get_field('vehicle_history');?></p>
					</div>
					<div class="service-modifications mb-3">
						<h4 class="mb-1">Services & Modifications</h4>
						<div class="field-gray-wrapper">
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">How extensive are your service records?</p>
								<p class="desc"><?php echo get_field('services_status');?></p>
							</div>
							<?php if(!empty(get_field('services_status_description'))):?>
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">What was done during recent servicing and how long ago was it performed?</p>
								<p class="desc"><?php echo get_field('services_status_description');?></p>
							</div>
							<?php endif;?>
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">Are there any modifications from stock?</p>
								<?php $modifications_status = get_field('modifications_status')['label']?>
								<p class="desc"><?php echo str_contains($modifications_status, 'Yes') ? ($modifications_status . '. ' . get_field('modifications_status_description')): $modifications_status;?></p>
							</div>
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">Any history of paintwork or bodywork?</p>
								<?php $pain_body_status = get_field('pain_body_status')['label']?>
								<p class="desc"><?php echo str_contains($pain_body_status, 'Yes') ? ($pain_body_status . ', ' . get_field('pain_body_status_description')) : $pain_body_status;?></p>
							</div>
						</div>
					</div>
					<div class="known-issues mb-3">
						<h4 class="mb-1">Known Issues</h4>
						<div class="field-gray-wrapper">
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">Are there known issue/s?</p>
								<?php $known_issue = get_field('known_issue')['label']?>
								<p class="desc"><?php echo str_contains($known_issue, 'Yes') ? ($known_issue . '. ' . get_field('known_issue_description')): $known_issue;?></p>
							</div>
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">Has it been in any accident/s?</p>
								<?php $accident_status = get_field('accident_status')['label']?>
								<p class="desc"><?php echo str_contains($accident_status, 'Yes') ? ($accident_status . '. ' . get_field('accident_status_description')): $accident_status;?></p>
							</div>
							<div class="d-flex auction-flex-row flex-wrap gap-40">
								<p class="title strong">Is there any rust present?</p>
								<?php $rusts_status = get_field('rusts_status')['label']?>
								<p class="desc"><?php echo str_contains($rusts_status, 'Yes') ? ($rusts_status . ', ' . get_field('rusts_description')) : $rusts_status;?></p>
							</div>
						</div>
					</div>
					<div class="others mb-4">
						<h4 class="mb-1">Others</h4>
						<p><?php echo get_field('addition_info');?></p>
					</div>
					<?php if(!empty(get_field( 'classic_embed_id'))):?>
						<div class="classic-embed mb-4">
							<iframe src="https://www.classic.com/widget/<?php echo get_field( 'classic_embed_id'); ?>/" width="100%" style="border:0;"></iframe>
						</div>
					<?php endif;?>
					<div class="auction-comments mb-3">
						<div class="heading">
							<h4>Recent Comments</h4>
						</div>
						<div class="vehicle-comments">
							<?php
							if (file_exists(ABSPATH . "wp-content/plugins/wpdiscuz/themes/default/comment-form.php")) {
								include_once ABSPATH . "wp-content/plugins/wpdiscuz/themes/default/comment-form.php";
							}
							?>
						</div>
					</div>
				</div>
				<div class="right-area">
					<div class="bid-wrapper mb-2">
						<div class="bid-inner">
							<?php wc_get_template('single-product/uwa-bids-history.php'); ?>
						</div>
					</div>
					<?php if($product->is_uwa_expired() == false && is_user_logged_in() && get_the_author_meta('email') !== wp_get_current_user()->user_email) :?>
						<div class="icon-text-button-component grey-bg mb-2">
							<div class="kt-inside-inner-col">
								<figure class="wp-block-image size-full">
									<img decoding="async" loading="lazy" width="38" height="31" src="<?php echo get_stylesheet_directory_uri()?>/images/messages-top.png" alt="messages-top" class="wp-image-3533">
								</figure>
								<h6 class="kt-adv-heading_910637-0e wp-block-kadence-advancedheading" data-kb-block="kb-adv-heading_910637-0e">Have more questions about this auction?</h6>
								<p>Message the seller directly for a fast response.</p>
								<div class="is-layout-flex wp-block-buttons">
									<div class="wp-block-button has-custom-width wp-block-button__width-100 message-btn message-btn1" data-title="Contact Seller">
										<a class="wp-block-button__link wp-element-button" style="padding:14px;">Send a Message</a>
									</div>
								</div>
							</div>
						</div>
					<?php endif;?>
					
					<?php if($carfax):?>
						<div class="bid-wrapper carfax-report mb-2">
							<h4 class="mb-2">Vehicle History Report</h4>
							<div class="d-flex gap-15">
								<img src="<?php echo get_stylesheet_directory_uri()?>/images/pdf.png" alt="pdf icon">
								<p class="m-0 d-flex flex-direction-column"> 
									<span>CARFAX (20230226)</span>
									<a href="<?php echo $carfax; ?>" target="_blank">View PDF <i class="fa fa-external-link" aria-hidden="true"></i></a>
								</p>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<?php
	$fancy_videos = []; 
	$fancy_interior = []; 
	$fancy_exterior = []; 
	$fancy_engine = []; 
	$fancy_undercarriage = []; 
	$fancy_other = []; 
	$all_count = 0;

	foreach ($medias as $cat => $assets) {
		$all_count = $all_count + count($assets);
		foreach ($assets as $key => $image) {
			switch ($cat) {
				case 'video':
					array_push($fancy_videos, $img_data);
					break;
				case 'interior':
					array_push($fancy_interior, $img_data);
					break;
				case 'exterior':
					array_push($fancy_exterior, $img_data);
					break;
				case 'engine':
					array_push($fancy_engine, $img_data);
					break;
				case 'undercarriage':
					array_push($fancy_undercarriage, $img_data);
					break;
				case 'other':
					array_push($fancy_other, $img_data);
					break;
			}
		}
	}

	?>
	<div class="popup-gallery" style="display: none">
		<a href="javascript:;" class="close-custom-gallery">
			<i class="fa fa-xmark"></i>
		</a>
		<div class="site-container">
			<div class="custom-gallery">
				<div class="categories">
					<button class="gal-category" data-category="all">All Photos (<?php echo $all_count;?>)</button>
					<?php foreach ($medias as $cat => $assets) : ?>
						<button class="gal-category" data-category="<?php echo $cat; ?>"><?php echo $cat . ' (' . count($assets) . ')' ; ?> </button>
					<?php endforeach; ?>
				</div>
				<div id="gal-images-wrapper">
				    
				</div>
			</div>
		</div>
	</div>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

<script>
	jQuery(function($){
		jQuery(document).on('click', '.close-custom-gallery', function() {
			$('.popup-gallery').hide();
		});
		jQuery(document).on('click', '.gal-category', function() {
			let gal_category = jQuery(this).data('category');
			$('.gal-category').removeClass('active');
			jQuery(this).addClass('active');
			let loop_category = '';
			let html = "";
			<?php foreach ($medias as $cat => $assets) : ?>
				loop_category = "<?php echo $cat;?>";
				<?php foreach ($assets as $key => $image) :
					$thumbnail_url = $src_url = !empty($image) ? $image : get_site_url() . "/wp-content/uploads/2023/02/listing-default-img.png";

					if($cat == 'video'){
						$thumbnail_url = get_site_url() . "/wp-content/uploads/2023/02/listing-default-img.png";

						preg_match('~(?:https?://)?(?:www.)?(?:youtube.com|youtu.be)/(?:watch\?v=)?([^\s]+)~', $image, $match_youtube);
						preg_match('~(?:https?://)?(?:www.)?(?:vimeo.com)/?([^\s]+)~', $image, $match_vimeo);
						if ($match_youtube) {
							$thumbnail_url = "https://img.youtube.com/vi/" . $match_youtube[1] . "/hqdefault.jpg";
						}
						else if ($match_vimeo) {
							$thumbnail_url = getVimeoThumbnail($match_vimeo[1]);
						}
						
						$src_url =  $image;
						$gal_file_ext = pathinfo(parse_url($src_url, PHP_URL_PATH), PATHINFO_EXTENSION);
						
					}?>
					var gal_file_ext = `<?php echo $gal_file_ext; ?>`;
					
					if(gal_category == loop_category){
						html += `<a data-fancybox="<?php echo $cat; ?>" data-src="<?php echo $src_url; ?>">`;
						if(gal_file_ext == "mp4" && loop_category =="video" ){
							html += 
							`<video>
								<source src="<?php echo $src_url; ?>#t=5,6" type="video/mp4">
							</video>`;
						}
						else {
							html += `<img src="<?php echo $thumbnail_url; ?>" class="<?php echo $gal_file_ext; ?>">`;
						}
							
						html += `</a>`;
					}
					if(gal_category == "all"){
						html += `<a data-fancybox="all" class="gallery-<?php echo $cat; ?>" data-src="<?php echo $src_url; ?>">`;
							html += `<img src="<?php echo $thumbnail_url; ?>">`;
						html += `</a>`;
					}
				<?php endforeach;
			endforeach; ?>
			
			jQuery('#gal-images-wrapper').html("");
			jQuery('#gal-images-wrapper').append(html);
		});

			$(document).on('click', '.gallery-item', function(event) {
				let __cat = $(this).attr('gallery-cat');
				$('.popup-gallery').show();
				$(`button[data-category="${__cat}"]`).trigger('click');
			});

		$(document).on('click', '.message-btn1', function(event){
			event.preventDefault();
			let btn_title = $(this).data('title');
			
			var seller_html = `<div class="text-left single-message-popup"><?php echo do_shortcode( '[fep_shortcode_new_message_form subject="{current-post-title}" heading=""]'); ?></div>`;
			Swal.fire({
				title: btn_title,
				html: seller_html,
				confirmButtonText: 'Yes, place bid',
				showConfirmButton: false,
				showCancelButton: false,
				showCloseButton: true,
				focusConfirm: false,
				focusCancel: false,
				focusClose: false,
			}).then((result) => {
				/* Read more about isConfirmed, isDenied below */
				if (result.isConfirmed) {
				// 	console.log("bid success full");
				// 	result_conf = true;
				// 	$('#uwa_auction_form').submit();
				} else{
					result_conf = false;
				}
			});	
		})

		$(document).on('click', '.share-btn', function(event){
			event.preventDefault();
			
			var share_html = `<div class="share-popup-wrap"><?php echo do_shortcode( '[Sassy_Social_Share]'); ?></div>`;
			Swal.fire({
				title: "Share This Listing",
				html: share_html,
				showConfirmButton: false,
				showCancelButton: false,
				showCloseButton: false,
				focusConfirm: false,
				focusCancel: false,
				focusClose: false,
			}).then((result) => {
				/* Read more about isConfirmed, isDenied below */
				if (result.isConfirmed) {
					console.log("bid success full");
					result_conf = true;
					$('#uwa_auction_form').submit();
				} else{
					result_conf = false;
				}
			});	
		})
		
	})
</script>
<?php

endwhile;
get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
