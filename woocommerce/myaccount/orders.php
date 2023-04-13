<?php
/**
 * My Orders - Deprecated
 *
 * @deprecated 2.6.0 this template file is no longer used. My Account shortcode uses orders.php.
 * @package WooCommerce\Templates
 */

defined( 'ABSPATH' ) || exit;

$status = $_GET['status'] ? $_GET['status'] : null;
$publish_status = 'draft';
$is_order_page = true;
$limit = 5;

if($status == 'ended'){
    $meta_query[] = array(
        'key'     => 'woo_ua_auction_closed',
        'compare' => 'EXISTS',
    );
}else{
    $meta_query[] = array(
        'key'     => 'woo_ua_auction_closed',
        'compare' => 'NOT EXISTS',
    );
}

if($status == 'active' || $status == 'ended'){
    $publish_status = 'publish';
}

global $wp;
$request = explode( '/', $wp->request );
// If NOT in My account dashboard page
if((end($request) == 'my-account' && is_account_page())){
    $is_order_page = false;
    $limit = 3;
    $publish_status = 'any';
}

$args = array(
    'author' =>  get_current_user_id(),
    'post_type'	=> 'product',
    'ignore_sticky_posts'	=> 1,
    'orderby' => 'id',
    'order' => 'desc',
    'meta_query' => $meta_query,
    'posts_per_page' => $limit,
    'tax_query' => array(array('taxonomy' => 'product_type' , 'field' => 'slug', 
        'terms' => 'auction')),
    'auction_arhive' => TRUE,
    'post_status' => $publish_status,
);

$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$args['paged'] = $paged;
ob_start();
$products = new WP_Query( $args );
// var_dump($products);die;
    $my_auction_page_url = wc_get_endpoint_url('orders');
    $pending_auction_url= esc_attr(add_query_arg(array('status' =>'pending'), $my_auction_page_url ));
    $active_auction_url= esc_attr(add_query_arg(array('status' =>'active'), $my_auction_page_url ));
    $ended_auction_url= esc_attr(add_query_arg(array('status' =>'ended'), $my_auction_page_url ));
?>
    <?php if($is_order_page):?>
        <h2>Your Auctions</h2>
        <ul class="uwa-user-bid-counts subsubsub">
            <li class="<?php echo $publish_status == 'draft' ? 'active' : '';?>">
                <a href="<?php echo $pending_auction_url;?>"> 
                    Pending Auctions (<?php echo get_my_auctions_count_by_status('draft'); ?>)
                </a>
            </li>
            <li class="<?php echo $status == 'active' ? 'active' : '';?>">
                <a href="<?php echo $active_auction_url;?>"> 
                    Live Auctions (<?php echo get_my_auctions_count_by_status('active'); ?>)
                </a>
            </li>
            <li class="<?php echo $status == 'ended' ? 'active' : '';?>">
                <a href="<?php echo $ended_auction_url;?>"> 
                    Ended Auctions (<?php echo get_my_auctions_count_by_status('ended'); ?>)
                </a>
            </li>
        </ul>
    <?php endif;?>

    <div class="auction-wrapper mb-3">

        <?php 
        if ( $products->have_posts() ) : 
            while ( $products->have_posts() ) : $products->the_post(); 
                global $product; 
                $uwa_expired = $product->is_uwa_expired();
                $uwa_started = $product->is_uwa_live();
                $winner_name = $product->get_uwa_winner_name();
                $winner_id = $product->get_uwa_auction_current_bider();
                if($winner_id){
                    $to_msg = get_user_meta($winner_id, 'nickname', true);?> 

                    <script type="text/javascript">

                    jQuery(document).on('click', '.message-btn3', function(event){
                        event.preventDefault();
                        let btn_title = jQuery(this).data('title');
                        
                        var seller_html = `<div class="text-left single-message-popup"><?php echo do_shortcode( '[fep_shortcode_new_message_form to='.$to_msg.' subject="{current-post-title}" heading=""] '); ?></div>`;
                        Swal.fire({
                            title: btn_title,
                            html: seller_html,
                            showConfirmButton: false,
                            showCancelButton: false,
                            showCloseButton: true,
                            focusConfirm: false,
                            focusCancel: false,
                            focusClose: false,
                        }).then((result) => {
                            /* Read more about isConfirmed, isDenied below */
                            if (result.isConfirmed) {
                                result_conf = true;
                                jQuery('#uwa_auction_form').submit();
                            } else{
                                result_conf = false;
                            }
                        });	
                    });
                    </script>
                <?php } ?>
                <div class="auction d-flex gap-20">   
                    <div class="img-details">
                        <?php $thumbnail = getListingThumbnail($product->get_id()); ?>
                        <a href="<?php echo ($product->status != 'draft' ? get_the_permalink() : 'javascript:;') ;?>">
                            <img src="<?php echo $thumbnail?>" class="auction-img" />
                        </a>
                    </div>
                    <div class="content-details">
                        <div class="top-area d-flex justify-content-between align-items-center">    
                            <h6><a href="<?php echo ($product->status != 'draft' ? get_the_permalink() : 'javascript:;') ;?>"><?php echo get_the_title(); ?></a></h6>
                            <?php if($uwa_expired === FALSE && $product->status == "publish"):?>
                                <div class="heading d-flex gap-10 align-items-center">
                                    <p class="m-0 label-tag">Current Bid:</p>
                                    <h4 class="price"><?php echo ( ($uwa_started === TRUE && !empty($product->get_uwa_auction_current_bid())) ? wc_price($product->get_uwa_auction_current_bid()) : wc_price($product->get_uwa_auction_start_price()) );?></h4>
                                </div>
                            <?php elseif($product->status == "publish" && ($uwa_expired === TRUE && empty($product->get_uwa_auction_fail_reason()))):?>
                                <div class="heading d-flex gap-10 align-items-center">
                                    <p class="m-0 label-tag">Winning Bid:</p>
                                    <h4 class="price"><?php echo wc_price($product->get_uwa_auction_current_bid());?></h4>
                                </div>
                            <?php endif;?>
                        </div>
                        <div class="descriptions d-flex justify-content-between align-items-center">
                            <?php if($uwa_expired === FALSE && $product->status == "publish"): ?>
                                <div class="auction-box d-flex flex-direction-column">
                                    <div class="d-flex gap-40 auction-box-inner mb-1">
                                        <div>
                                            <div class="text-wrap d-flex gap-10 justify-content-between align-items-center">
                                                <p class="m-0 label-tag">Auction Ending:</p>
                                                <p class="m-0">
                                                    <?php echo date_i18n( get_option( 'date_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>  
                                                    <?php echo date_i18n( get_option( 'time_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>
                                                </p>
                                            </div>
                                            <div class="text-wrap d-flex gap-10 justify-content-between align-items-center">
                                                <p class="m-0 label-tag">Time Left:</p>
                                                <div class="m-0 product d-flex"><?php echo do_shortcode('[countdown id="'. $product->get_id() .'"]') ?></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-wrap d-flex gap-10 justify-content-between align-items-center">
                                                <p class="m-0 label-tag">No. of Bids:</p>
                                                <p class="m-0"><?php echo count($product->uwa_auction_log_history()); ?></p>
                                            </div>
                                            <div class="text-wrap d-flex gap-10 justify-content-between align-items-center">
                                                <p class="m-0 label-tag">No. of Watchers:</p>
                                                <p class="m-0"><?php
                                                $watchers = get_post_meta( $product->get_id(), "woo_ua_auction_watch");       
                                                echo count($watchers); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="status-tag live">LIVE</button>
                                </div>
                            <?php elseif(($uwa_expired === TRUE ) && $product->status == "publish"): ?>
                                <div class="auction-box d-flex flex-direction-column">
                                    <div class="text-wrap d-flex gap-10 justify-content-between align-items-center">
                                        <p class="m-0 label-tag">Auction Ended:</p>
                                        <p class="m-0">
                                            <?php echo date_i18n( get_option( 'date_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>  
                                            <?php echo date_i18n( get_option( 'time_format' ),  strtotime( $product->get_uwa_auctions_end_time() ));  ?>
                                        </p>
                                    </div>
                                    <?php if(($uwa_expired === TRUE && empty($product->get_uwa_auction_fail_reason()))):?>
                                        <div class="text-wrap d-flex gap-10 justify-content-between align-items-center">
                                            <p class="m-0 label-tag">Sold to:</p>
                                            <p class="text profile-name"><?php echo get_profile_pic($winner_id); ?> <?php echo $winner_name; ?></p>
                                        </div>
                                    <?php endif;?>
                                    <button class="status-tag ended mt-1"><?php echo empty($product->get_uwa_auction_fail_reason()) ? 'ENDED' : 'EXPIRED'?></button>
                                </div>
                                <?php if(($uwa_expired === TRUE && empty($product->get_uwa_auction_fail_reason()))):?>
                                <div class="edit-and-delete">
                                    <button class="d-flex gap-10 align-items-center edit message message-btn3" data-winner="<?php echo $winner_name; ?>" data-title="Contact Buyer">
                                        <img src="<?php echo get_stylesheet_directory_uri()?>/images/message.png">
                                        Message Winner
                                    </button>
                                </div>
                                <?php endif;?>
                            <?php else: ?>  
                                <div class="auction-box d-flex flex-direction-column">
                                    <div class="auction-box-inner mb-1">
                                        <div class="text-wrap d-flex justify-content-between gap-10">
                                            <p class="m-0 label-tag">Submitted:</p>
                                            <p class="m-0"><?php echo get_the_date('F d, Y H:i a');?></p>
                                        </div>
                                        <div class="text-wrap d-flex justify-content-between gap-10">
                                            <p class="m-0 label-tag">VIN #:</p>
                                            <p class="m-0"><?php echo get_field('vin');?></p>
                                        </div>
                                    </div>
                                    <button class="status-tag">PENDING</button>
                                </div>
                                <div class="edit-and-delete">
                                    <a href="<?php echo site_url().'/edit-auction?auction_id='.$product->id; ?>">
                                        <button class="d-flex gap-10 align-items-center edit">
                                            <img src="<?php echo get_stylesheet_directory_uri()?>/images/edit.png">
                                            Edit Listing
                                        </button>
                                    </a>
                                    <button class="d-flex gap-10 align-items-center delete delete-btn" data-auctionid="<?php echo $product->id?>">
                                        <img src="<?php echo get_stylesheet_directory_uri()?>/images/delete.png">
                                        Delete
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            <?php endwhile; // end of the loop. ?>
        <?php else : ?>
            <div class="not-found-wrapper">
                <img src="<?php echo get_stylesheet_directory_uri().'/images/no-auction.svg' ; ?>" class="not-found-img"/>
                <p class="not-found-text">No auctions found</p>
            </div>

        <?php endif; ?>
    </div>

<?php
wp_reset_postdata();


/* ---  Display Pagination ---  */

if ($is_order_page && $limit < $products->found_posts ) { // don't change condition else design conflicts
    $big = 999999999;
    $current = max(1, get_query_var('paged'));
    $total   = $products->max_num_pages;
    $base    = esc_url_raw( str_replace( $big, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( $big, false ))));
    $format  = '?paged=%#%';			

    if ( $total <= 1 ) {
        return;
    }
    $display_data = '<nav class="woocommerce-pagination">';
    $display_data .= paginate_links( 
        apply_filters( 'woocommerce_pagination_args', 
            array( 
                'base'         => $base,
                'format'       => $format,						
                'add_args'     => false,						
                'current'      => $current,
                'total'        => $total,
                'prev_text'    => '&larr;',
                'next_text'    => '&rarr;',
                //'type'         => 'list',
                'end_size'     => 3,
                'mid_size'     => 3,
            ) 
        ));			
    $display_data .= '</nav>';
    echo $display_data;
    
} /* end of if - paginate */

echo '<div class="my-auctions">' . ob_get_clean() . '</div>';