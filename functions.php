<?php

/**
 * Enqueue child styles.
 */
function child_enqueue_styles()
{
	wp_enqueue_style('swal-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css');
	wp_enqueue_style('product-cat-theme', get_stylesheet_directory_uri() . '/css/product_cat.css');
	wp_enqueue_style('header-theme', get_stylesheet_directory_uri() . '/css/header.css');
	wp_enqueue_style('dashboard-theme', get_stylesheet_directory_uri() . '/css/dashboard.css');
	wp_enqueue_style('auction-detail-theme', get_stylesheet_directory_uri() . '/auction-detail.css');
	wp_enqueue_style('custom-css-theme', get_stylesheet_directory_uri() . '/css/custom.css');
	wp_enqueue_style('edit-listing-css-theme', get_stylesheet_directory_uri() . '/css/edit-listing.css');
	wp_enqueue_style('child-theme', get_stylesheet_directory_uri() . '/style.css');
	wp_enqueue_script('swal-script', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js', array('jquery'));
	wp_enqueue_script('phone-mask', get_stylesheet_directory_uri() . '/js/phone_mask.js', array('jquery'), time(), true);
	wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), time(), true);
	wp_localize_script(
		'custom-script',
		'opt',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'noResults' => esc_html__('No data found', 'textdomain'),
			'home_url' => home_url(),
		)
	);
}

add_action('wp_enqueue_scripts', 'child_enqueue_styles'); // Remove the // from the beginning of this line if you want the child theme style.css file to load on the front end of your site.

// custom css and js
add_action('admin_enqueue_scripts', 'enqueue_script_in_admin');

function enqueue_script_in_admin($hook)
{
	global $parent_file;
	if ('edit.php?post_type=product' == $parent_file) {
		wp_enqueue_script('product-admin-custom', get_stylesheet_directory_uri() . '/js/product-admin.js');
	}
	wp_enqueue_style('admin-custom-theme', get_stylesheet_directory_uri() . '/css/admin-custom.css');
}

/**
 * Add custom functions here
 */
add_filter('gform_field_validation_12_45', 'sl_check_vin', 10, 4);
add_filter('gform_field_validation_13_45', 'sl_check_vin', 10, 4);
function sl_check_vin($result, $value, $form, $field)
{
	if ($result['is_valid']) {
		$vin = strtoupper($value);

		if (!ctype_alnum($vin)) {
			$result['is_valid'] = false;
			$result['message'] = "VIN should only have letters and numbers.";
		} else if (strlen($vin) > 17 || strlen($vin) <= 0) {
			$result['is_valid'] = false;
			$result['message'] = "VIN can only have 17 characters.";
		}
	}

	return $result;
}

// Repeater - Youtube or Vimeo Video Link Field
add_filter('gform_form_post_get_meta_13', 'sl_add_video_link_field2');
function sl_add_video_link_field2($form)
{

	$link = GF_Fields::create(array(
		'type'   => 'text',
		'id'     => 1001,
		'formId' => $form['id'],
		'label'  => 'Add link to YouTube/Vimeo video',
		'pageNumber'  => 4,
	));

	$repeater = GF_Fields::create(array(
		'type'             => 'repeater',
		'id'               => 1000,
		'formId'           => $form['id'],
		'pageNumber'       => 4, // Ensure this is correct
		'fields'           => array($link), // Add the fields here.
	));

	//$form['fields'][] = $repeater;

	array_splice($form['fields'], 84, 0, array($repeater));

	return $form;
}
add_filter('gform_form_post_get_meta_12', 'sl_add_video_link_field');
function sl_add_video_link_field($form)
{

	$link = GF_Fields::create(array(
		'type'   => 'text',
		'id'     => 1001,
		'formId' => $form['id'],
		'label'  => 'Add link to YouTube/Vimeo video',
		'pageNumber'  => 4,
	));

	$repeater = GF_Fields::create(array(
		'type'             => 'repeater',
		'id'               => 1000,
		'formId'           => $form['id'],
		'pageNumber'       => 4, // Ensure this is correct
		'fields'           => array($link), // Add the fields here.
	));

	//$form['fields'][] = $repeater;

	array_splice($form['fields'], 75, 0, array($repeater));

	return $form;
}

add_filter('gform_form_update_meta_13', 'sl_remove_video_link_field2', 10, 3);
function sl_remove_video_link_field2($form_meta, $form_id, $meta_name)
{
	if ($meta_name == 'display_meta') {
		$form_meta['fields'] = wp_list_filter($form_meta['fields'], array('id' => 1000), 'NOT');
	}
	return $form_meta;
}

add_filter('gform_form_update_meta_12', 'sl_remove_video_link_field', 10, 3);
function sl_remove_video_link_field($form_meta, $form_id, $meta_name)
{
	if ($meta_name == 'display_meta') {
		$form_meta['fields'] = wp_list_filter($form_meta['fields'], array('id' => 1000), 'NOT');
	}
	return $form_meta;
}

add_filter('gform_required_legend', '__return_empty_string');

add_filter("wpdiscuz_comment_author", function ($authorName, $comment) {
	$user_data = get_userdata($comment->user_id);
	$authorName = $user_data->user_login;

	return $authorName;
}, 10, 2);

add_action('gform_after_submission_12', 'submit_listing_submission', 10, 2);
add_action('gform_after_submission_13', 'submit_listing_submission', 10, 2);
function submit_listing_submission($entry, $form)
{
	$func_arr = array(
		"function"			=> "after_submission",
		"entry"				=> $entry,
		"form"				=> $form
	);

	submit_listing($func_arr);
}

function submit_listing($func_arr)
{

	$form 	= $func_arr['form'];
	$entry 	= $func_arr['entry'];

	$entry_id = $entry['id'];

	global $wpdb;

	$auction_id = rgar($entry, '130');

	$user_email = rgar($entry, '35');

	$wp_user = get_user_by('email', $user_email);
	$auther_id = $wp_user->ID;

	$submitListing = [];

	$submitListing['seller_email'] = rgar($entry, '1');

	$submitListing['private_party_or_dealer'] = rgar($entry, '10');
	$submitListing['is_the_title_in_your_name'] = rgar($entry, '11');

	$submitListing['street_address'] = rgar($entry, '51');
	$submitListing['suite_apartment_number'] = rgar($entry, '52');
	$submitListing['city'] = rgar($entry, '53');
	$submitListing['state'] = rgar($entry, '54');
	$submitListing['zip_code'] = rgar($entry, '56');
	$submitListing['contact_number'] = rgar($entry, '57');

	$submitListing['referred_status'] = rgar($entry, '13');

	$submitListing['referred_firstname'] = rgar($entry, '17');
	$submitListing['referred_lastname'] = rgar($entry, '18');
	$submitListing['referred_email'] = rgar($entry, '20');
	$submitListing['referred_phone'] = rgar($entry, '21');

	$submitListing['how_you_hear_about_vinhammer'] = rgar($entry, '22');
	$submitListing['how_you_hear_about_vinhammer_others'] = rgar($entry, '23');

	// $submitListing['pricing'] = rgar( $entry, '42' );
	// $submitListing['like_to_add_a_reserve'] = rgar( $entry, '25' );
	// $submitListing['reserve_price'] = rgar( $entry, '43' );

	$submitListing['vin'] = strtoupper(rgar($entry, '45'));
	$submitListing['make'] = rgar($entry, '46');
	$submitListing['model'] = rgar($entry, '47');
	$submitListing['year'] = rgar($entry, '49');
	$submitListing['indicated_mileage'] = rgar($entry, '50');
	$submitListing['true_mileage'] = rgar($entry, '59');
	$submitListing['actual_mileage'] = rgar($entry, '60');
	$submitListing['car_transmission'] = rgar($entry, '61');
	$submitListing['body_style'] = rgar($entry, '62');
	$submitListing['driveline'] = rgar($entry, '63');
	$submitListing['fuel_type'] = rgar($entry, '64');
	$submitListing['engine_size'] = rgar($entry, '65');
	$submitListing['exterior_color'] = rgar($entry, '66');
	$submitListing['interior_color'] = rgar($entry, '67');

	$features_field = GFFormsModel::get_field($form, 69);
	$features_field_value = is_object($features_field) ? $features_field->get_value_export($entry) : '';
	$submitListing['additional_features'] = preg_split("/\r\n|\n|\r|, |,/", $features_field_value);
	$submitListing['additional_unique_features'] = rgar($entry, '70');

	$submitListing['vehicle_history'] = rgar($entry, '75');

	$submitListing['sale_status'] = rgar($entry, '76');
	$submitListing['sale_status_description'] = rgar($entry, '77');

	$submitListing['services_status'] = rgar($entry, '79');
	$submitListing['services_status_description'] = rgar($entry, '80');

	$submitListing['modifications_status'] = rgar($entry, '81');
	$submitListing['modifications_status_description'] = rgar($entry, '82');

	$submitListing['pain_body_status'] = rgar($entry, '83');
	$submitListing['pain_body_status_description'] = rgar($entry, '84');

	$submitListing['known_issue'] = rgar($entry, '86');
	$submitListing['known_issue_description'] = rgar($entry, '87');

	$submitListing['accident_status'] = rgar($entry, '88');
	$submitListing['accident_status_description'] = rgar($entry, '89');

	$submitListing['rusts_status'] = rgar($entry, '90');
	$submitListing['rusts_description'] = rgar($entry, '91');

	$submitListing['addition_info'] = rgar($entry, '93');

	$photography_field = GFFormsModel::get_field($form, 94);
	$photography_field_value = is_object($photography_field) ? $photography_field->get_value_export($entry) : '';
	$submitListing['need_professional_photography'] = preg_split("/\r\n|\n|\r|, |,/", $photography_field_value);


	$photo_fields_data_arr = array(
		array(
			"id"		=> '98',
			"tag"		=> 'featured_videos'
		),
		array(
			"id"		=> '102',
			"tag"		=> 'interior_photos'
		),
		array(
			"id"		=> '104',
			"tag"		=> 'exterior_photos'
		),
		array(
			"id"		=> '106',
			"tag"		=> 'engine_photos'
		),
		array(
			"id"		=> '108',
			"tag"		=> 'undercarriage_photos'
		),
		array(
			"id"		=> '110',
			"tag"		=> 'other_photos'
		)
	);

	$photos_data_arr = [
		'featured_videos' 		=> [],
		'interior_photos'		=> [],
		'exterior_photos' 		=> [],
		'engine_photos' 		=> [],
		'undercarriage_photos' 	=> [],
		'other_photos' 			=> [],
	];

	foreach ($photo_fields_data_arr as $photo_field_data) {
		$photo_id 	= $photo_field_data["id"];
		$photo_tag	= $photo_field_data["tag"];

		$photo_field = GFAPI::get_field($form, $photo_id);
		$photo_files = json_decode(rgar($entry, $photo_id));

		$old_urls = [];
		if ($photo_tag == "featured_videos") {
			$featured_videos_old = get_field($photo_tag, $auction_id);
			foreach ($featured_videos_old as $f_v_key => $f_v_o) {
				array_push($old_urls,  $f_v_o["url"]);
			}
		} else {
			$old_urls = get_field($photo_tag, $auction_id);
		}
		if ($auction_id && $old_urls) {
			$photo_files = array_merge($old_urls, $photo_files);
		}

		foreach ($photo_files as $photo_file) {
			$attachment_id = upload_file_by_url($photo_file);
			if ($photo_tag == 'featured_videos') {
				$attachment_url = wp_get_attachment_url($attachment_id);
				array_push($photos_data_arr[$photo_tag],  ["url" => $attachment_url]);
			} else {
				array_push($photos_data_arr[$photo_tag],  $attachment_id);
			}
		}
		if ($auction_id && $old_urls) {
			foreach ($old_urls as $old_url) {
				$old_attachment_id = attachment_url_to_postid($old_url);
				wp_delete_attachment($old_attachment_id, true);
			}
		}
	}

	$submitListing['photos_data'] = $photos_data_arr;

	//  Videos - Youtube/Vimeo Links
	$field_rep_video_links = rgar($entry, '1000');
	$links_arr_video_links = array();
	foreach ($field_rep_video_links as $item) {
		if ($item['1001']) {
			preg_match('~(?:https?://)?(?:www.)?(?:youtube.com|youtu.be)/(?:watch\?v=)?([^\s]+)~', $item['1001'], $match_youtube);
			preg_match('~(?:https?://)?(?:www.)?(?:vimeo.com)/?([^\s]+)~', $item['1001'], $match_vimeo);

			if ($match_youtube) {
				array_push($links_arr_video_links, array("url_type" => "youtube", "url" => "https://youtube.com/watch?v=" . $match_youtube[1]));
			} else if ($match_vimeo) {
				array_push($links_arr_video_links, array("url_type" => "vimeo", "url" => "https://vimeo.com/" . $match_vimeo[1]));
			} else {
				// Invalid Youtube or Vimeo Video Link
			}
		}
	}

	$submitListing['youtube_vimeo_links'] = $links_arr_video_links;

	$auction_title = $submitListing['year'] . ' ' . $submitListing['make'] . ' ' . $submitListing['model'];

	$auction_data = array(
		'post_author'  => $auther_id,
		'post_status'  => "draft",
		'post_title'   => $auction_title,
		'post_parent'  => '',
		'post_type'    => "product",
	);

	if ($auction_id && !empty($auction_id)) {
		$auction_data['ID'] = $auction_id;
	}

	// Remove Gravity Folders Media Start
	$table_gf_entry_meta = $wpdb->prefix . 'gf_entry_meta';

	foreach ($photo_fields_data_arr as $tmp_pht) {
		$fu_id = $tmp_pht['id'];
		$sql_uploaded_files = $wpdb->prepare(
			"
			SELECT meta_value
			FROM $table_gf_entry_meta
			WHERE meta_key = %d AND entry_id = %d
			",
			$fu_id,
			$entry_id
		);

		$uploaded_files = $wpdb->get_var($sql_uploaded_files);
		$uploaded_files = json_decode($uploaded_files);

		if (count($uploaded_files) > 0) {
			foreach ($uploaded_files as $uploaded_file) {
				$uploaded_file_url = explode('uploads', $uploaded_file);
				$uploaded_file_name = end($uploaded_file_url);

				$upload_dir = wp_upload_dir();

				$uploaded_file_full_path = $upload_dir['basedir'] . $uploaded_file_name;

				wp_delete_file($uploaded_file_full_path);
			}
		}
	}
	// Remove Gravity Folders Media End
	if (empty($auction_id)) {
		$auction_id = wp_insert_post($auction_data, $wp_error);
	} else {
		$auction_id = wp_update_post($auction_data, $wp_error);
	}

	if ($auction_id) {
		$product_type = 'auction'; // <== Here define your product type slug
		$class_name   = WC_Product_Factory::get_product_classname($auction_id, $product_type);

		// If the product class exist for the defined product type
		if (!empty($class_name) && class_exists($class_name)) {
			$product = new $class_name($auction_id); // Get an empty instance of a grouped product Object
		}
		// For a custom product class (you may have to define the custom class name)
		else {
			$class_name = 'WC_Product_custom'; // <== Here define the Class name of your custom product type

			if (class_exists($class_name)) {
				$product = new $class_name($auction_id); // Get an empty instance of a custom class product Object
			} else {
				wp_send_json_error(array('message' => __('Wrong product class')), 409);
				return; // or exit;
			}
		}
		// $product->set_description($submitListing['description']);
		// $product->set_short_description($submitListing['highlights']);
		$auction_id = $product->save(); // Save to database 

		foreach ($submitListing as $key => $val) {
			if ($key == 'photos_data') {
				// set_post_thumbnail($auction_id, $val['photos_data'][0]);
				foreach ($val as $key1 => $res) {
					update_field($key1, $res, $auction_id);
				}
			} else {
				update_field($key, $val, $auction_id);
			}
		}

		update_post_meta($auction_id, 'woo_ua_opening_price', $submitListing['pricing']);
		if ($submitListing['like_to_add_a_reserve'] == 'Yes' || $submitListing['like_to_add_a_reserve'] == 'yes') {
			update_post_meta($auction_id, 'woo_ua_lowest_price', $submitListing['reserve_price']);
		} else {
			update_post_meta($auction_id, 'woo_ua_lowest_price', $submitListing['pricing']);
		}

		$referred_values = array(
			'referred_firstname'    =>   $submitListing['referred_firstname'], //THE 1st PART MATCHES YOUR FIELD NAMES, THE 2nd IS THE VALUE YOU WANT
			'referred_lastname'     =>   $submitListing['referred_lastname'],
			'referred_email'     =>   $submitListing['referred_email'],
			'referred_phone'     =>   $submitListing['referred_phone'],
		);
		update_field('referred_by', $referred_values, $auction_id);

		$address_values = array(
			'street_address'			=>   $submitListing['street_address'], //THE 1st PART MATCHES YOUR FIELD NAMES, THE 2nd IS THE VALUE YOU WANT
			'suite_apartment_number'    =>   $submitListing['suite_apartment_number'],
			'city'						=>   $submitListing['city'],
			'state'     				=>   $submitListing['state'],
			'zip_code'     				=>   $submitListing['zip_code'],
			'contact_number'     		=>   $submitListing['contact_number'],
		);
		update_field('city_state', $address_values, $auction_id);
	}
}

function upload_file_by_url($file_url)
{

	// it allows us to use download_url() and wp_handle_sideload() functions
	require_once(ABSPATH . 'wp-admin/includes/file.php');

	// download to temp dir
	$temp_file = download_url($file_url);

	if (is_wp_error($temp_file)) {
		return false;
	}

	// move the temp file into the uploads directory
	$file = array(
		'name'     => basename($file_url),
		'type'     => mime_content_type($temp_file),
		'tmp_name' => $temp_file,
		'size'     => filesize($temp_file),
	);
	$sideload = wp_handle_sideload(
		$file,
		array(
			'test_form'   => false // no needs to check 'action' parameter
		)
	);

	if (!empty($sideload['error'])) {
		// you may return error message if you want
		return false;
	}

	// it is time to add our uploaded image into WordPress media library
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $sideload['url'],
			'post_mime_type' => $sideload['type'],
			'post_title'     => basename($sideload['file']),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$sideload['file']
	);

	if (is_wp_error($attachment_id) || !$attachment_id) {
		return false;
	}

	if ($sideload['type'] == 'video/mp4' || $sideload['type'] == 'video/webm') {
		return $attachment_id;
	}
	// update medatata, regenerate image sizes
	require_once(ABSPATH . 'wp-admin/includes/image.php');

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata($attachment_id, $sideload['file'])
	);

	return $attachment_id;
}

function getCategoryThumbnail($images, $category)
{
	$thumbnail = get_site_url() . "/wp-content/uploads/2023/02/listing-default-img.png";

	foreach ($images as $key => $image) {
		if ($key == $category) {
			if ($category == "video") {
				foreach ($image as $img) {
					preg_match('~(?:https?://)?(?:www.)?(?:youtube.com|youtu.be)/(?:watch\?v=)?([^\s]+)~', $img, $match_youtube);
					preg_match('~(?:https?://)?(?:www.)?(?:vimeo.com)/?([^\s]+)~', $img, $match_vimeo);
					if ($match_youtube) {
						$thumbnail = "https://img.youtube.com/vi/" . $match_youtube[1] . "/hqdefault.jpg";
					} else if ($match_vimeo) {
						$thumbnail = getVimeoThumbnail($match_vimeo[1]);
					}
				}
			} else {
				if ($image[0]) {
					$thumbnail = $image[0];
				}
			}
		}
	}

	return $thumbnail;
}

function getListingThumbnail($listing_id)
{
	$thumbnail = get_site_url() . "/wp-content/uploads/2023/02/listing-default-img.png";

	$exterior_photos 		= get_field('exterior_photos', $listing_id);
	$interior_photos 		= get_field('interior_photos', $listing_id);
	$engine_photos 			= get_field('engine_photos', $listing_id);
	$undercarriage_photos 	= get_field('undercarriage_photos', $listing_id);
	$other_photos 			= get_field('other_photos', $listing_id);

	if (!empty($exterior_photos) && count($exterior_photos) > 0) {
		return $thumbnail = $exterior_photos[0];
	}
	if (!empty($interior_photos) && count($interior_photos) > 0) {
		return $thumbnail = $interior_photos[0];
	}
	if (!empty($engine_photos) && count($engine_photos) > 0) {
		return $thumbnail = $engine_photos[0];
	}
	if (!empty($$undercarriage_photos) && count($$undercarriage_photos) > 0) {
		return $thumbnail = $$undercarriage_photos[0];
	}
	if (!empty($other_photos) && count($other_photos) > 0) {
		return $thumbnail = $other_photos[0];
	}

	return $thumbnail;
}

function getVimeoThumbnail($videoID)
{
	$curl = curl_init();

	$api_url = 'http://vimeo.com/api/v2/video/' . $videoID . '.json';

	curl_setopt_array($curl, array(
		CURLOPT_URL => $api_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
	));

	$response = json_decode(curl_exec($curl));

	curl_close($curl);

	return $response[0]->thumbnail_large;
}

function woocommerce_product_loop_start()
{
	echo '<ul class="products all_products products-ul">';
}

function add_login_check()
{
	global $wp;
	$request = explode('/', $wp->request);
	if (!is_user_logged_in() && end($request) == "my-account") {
		wp_redirect(site_url() . "/sign-in");
		exit;
	}
}
add_action("wp", "add_login_check");

function auction_filter_func($atts = [])
{
	extract(shortcode_atts(array(
		'expired' => false,
		'hidescheduled' => 'yes',
	), $atts));

	if (!$expired) {
		$meta_query[] = array(
			'key'     => 'woo_ua_auction_closed',
			'compare' => 'NOT EXISTS',
		);

		if ($hidescheduled == 'yes') {
			$meta_query[] = array(
				'key'     => 'woo_ua_auction_started',
				'compare' => 'NOT EXISTS',
			);
		}
	} else {
		$meta_query[] = array(
			'key'     => 'woo_ua_auction_closed',
			'compare' => 'EXISTS',
		);
	}

	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => 'id',
		'order' => 'desc',
		'meta_query' => $meta_query,
		'posts_per_page' => -1,   // -1 is default for all results to display
		'tax_query' => array(array(
			'taxonomy' => 'product_type', 'field' => 'slug',
			'terms' => 'auction'
		)),
	);

	$filters = [
		'body_style' => [],
		'car_transmission' => [],
		'year' => [],
		'mileage' => [],
		'pricing' => [],
	];

	$products = new WP_Query($args);
	while ($products->have_posts()) : $products->the_post();
		global $product;

		array_push($filters['body_style'], get_field('body_style'));
		array_push($filters['car_transmission'], get_field('car_transmission')['label']);
		array_push($filters['year'], get_field('year'));
	// array_push($filters['mileage'], get_field('indicated_mileage'));
	// array_push($filters['pricing'], $product->get_uwa_auction_start_price());

	endwhile;
	$filters['body_style'] = array_count_values($filters['body_style']);
	$filters['car_transmission'] = array_count_values($filters['car_transmission']);

	sort($filters['year']);
	// sort($filters['mileage']);
	// sort($filters['pricing']);

	// $start_mileage = (int)current($filters['mileage']); 
	// $end_mileage = (int)end($filters['mileage']);
	// $new_mileage = [[$start_mileage, $start_mileage + 3]];

	// for ($i = $start_mileage; $i <= $end_mileage ; $i++) {
	// 	if($i <= 30){
	// 		if($i == $end_mileage){
	// 			continue;
	// 		}

	// 		$tmp_last_val = (($i+5) > $end_mileage) ? $end_mileage : $i + 5;
	// 		if($i >= 25){
	// 			$tmp_last_val = '100+';
	// 		}
	// 		if(end($new_mileage)[1] == $i){
	// 			array_push($new_mileage, [$i, $tmp_last_val]);
	// 		}
	// 	}
	// }

	ob_start();
?>
	<div class="filter_head d-flex justify-content-between align-items-center mb-1">
		<h5 class="f_heading top-heading d-flex gap-10 align-items-center"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/filter-icon.png" /> Filter By</h5>
		<a href="javascript:void(0)" class="clear_filter f_heading top-heading">Clear Filter</a>
	</div>
	<div class="main_filters gform_wrapper gravity-theme filter-row">
		<div class="filter-item faq-drawer body_style_container">
			<input class="faq-drawer__trigger" id="faq-drawer" type="checkbox" />
			<label class="faq-drawer__title f_heading" for="faq-drawer">Body Style</label>
			<div class="faq-drawer__content-wrapper">
				<?php
				$body_styles = acf_get_field('body_style');
				foreach ($body_styles["choices"] as $key => $body_style) {
					$query_body_style = json_decode(stripslashes($_GET['body_style']));
					$b_checked = (isset($_GET['body_style']) && in_array($body_style, $query_body_style)) ? 'checked' : '';
					echo '
						<div class="d-flex justify-content-between align-items-center mb-1">
							<div class="gchoice">
								<input type="checkbox" id="body_style' . $key . '" name="body_style" class="filter_change gfield-choice-input" ' . $b_checked . ' value="' . $body_style . '">
								<label for="body_style' . $key . '">
									<span>' . $body_style . '</span>
								</label>
							</div>
							<span class="count">' . ($filters['body_style'][$body_style] ? $filters['body_style'][$body_style] : 0) . '</span>
						</div>
						';
				}
				?>
			</div>
		</div>
		<div class="filter-item faq-drawer transmission_container">
			<input class="faq-drawer__trigger" id="faq-drawer-2" type="checkbox" />
			<label class="faq-drawer__title f_heading" for="faq-drawer-2">Transmission</label>
			<div class="faq-drawer__content-wrapper">
				<?php
				$car_transmission = acf_get_field('car_transmission');
				foreach ($car_transmission["choices"] as $key => $transmission) {
					$query_car_transmission = json_decode(stripslashes($_GET['car_transmission']));
					$c_checked = (isset($_GET['car_transmission']) && in_array($transmission, $query_car_transmission)) ? 'checked' : '';
					echo '
						<div class="d-flex justify-content-between align-items-center mb-1">
							<div class="gchoice">
								<input type="checkbox" id="transmission' . $key . '" name="car_transmission" class="filter_change gfield-choice-input" ' . $c_checked . ' value="' . $transmission . '">
								<label for="transmission' . $key . '">
									<span>' . $transmission . '</span>
								</label>
							</div>		
							<span class="count">' . ($filters['car_transmission'][$transmission] ? $filters['car_transmission'][$transmission] : 0) . '</span>
						</div>
						';
				}
				?>
			</div>
		</div>
		<div class="filter-item faq-drawer range_container">
			<?php
			$min_year = current($filters['year']);
			$param_min_year = isset($_GET['min_year']) ? json_decode(stripslashes($_GET['min_year'])) : null;
			$max_year = end($filters['year']);
			$param_max_year = isset($_GET['max_year']) ? json_decode(stripslashes($_GET['max_year'])) : null;
			?>
			<input class="faq-drawer__trigger" id="faq-drawer-3" type="checkbox" />
			<label class="faq-drawer__title f_heading" for="faq-drawer-3">Year</label>
			<div class="faq-drawer__content-wrapper">
				<section class="range-slider d-flex justify-content-between">
					<span class="full-range"></span>
					<span class="incl-range"></span>
					<span class="output outputOne"></span>
					<input name="min_year" value="<?php echo ($param_min_year && !empty($param_min_year) ? $param_min_year : $min_year); ?>" min="<?php echo $min_year; ?>" max="<?php echo $max_year; ?>" step="1" type="range" class="min-range filter_change">
					<input name="max_year" value="<?php echo ($param_max_year && !empty($param_max_year) ? $param_max_year : $max_year); ?>" min="<?php echo $min_year; ?>" max="<?php echo $max_year; ?>" step="1" type="range" class="max-range filter_change">
					<span class="output outputTwo"></span>
				</section>
			</div>
		</div>
		<!-- <div class="filter-item faq-drawer mileage_container">
			<input class="faq-drawer__trigger" id="faq-drawer-4" type="checkbox" />
			<label class="faq-drawer__title f_heading" for="faq-drawer-4">Mileage</label>
			<div class="faq-drawer__content-wrapper">
				<?php
				// foreach($new_mileage as $key => $mileage){
				// 	$m_checked =  (isset($_GET['mileage']) && $_GET['mileage'] == ($mileage[0].'-'.$mileage[1])) ? 'checked' : '';
				// 	echo '
				// 	<div class="d-flex justify-content-between align-items-center mb-1">
				// 		<div class="gchoice">
				// 			<input type="checkbox" id="transmission'.$key.'" name="mileage" class="filter_change gfield-choice-input" '.$m_checked.' value="'.$mileage[0].'-'.$mileage[1].'">
				// 			<label for="transmission'.$key.'">
				// 				<span>'.$mileage[0].' miles - '.$mileage[1].' miles</span>
				// 			</label>
				// 		</div>
				// 	</div>
				// 	';
				// }
				?>
			</div>
		</div>
		<div class="filter-item faq-drawer range_container">
			<?php
			// $auction_min_price = current($filters['pricing']);
			// $auction_max_price = end($filters['pricing']);
			?>
			<input class="faq-drawer__trigger" id="faq-drawer-5" type="checkbox" />
			<label class="faq-drawer__title f_heading" for="faq-drawer-5">Price</label>
			<div class="faq-drawer__content-wrapper">
				<section class="range-slider d-flex justify-content-between">
					<span class="full-range"></span>
					<span class="incl-range"></span>
					<span class="output outputOne"></span>
					<input name="min_price" value="<?php // echo isset($_GET['min_price']) ? $_GET['min_price'] : $auction_min_price; 
													?>" min="<?php // echo $auction_min_price; 
																																				?>" max="<?php // echo $auction_max_price; 
																																															?>" step="1" type="range" class="min-range filter_change">
					<input name="max_price" value="<?php // echo $auction_max_price; 
													?>" min="<?php // echo $auction_min_price; 
																								?>" max="<?php // echo $auction_max_price; 
																																			?>" step="1" type="range" class="max-range filter_change">
					<span class="output outputTwo"></span>
				</section>
			</div>
		</div> -->
		<?php if ($expired) { ?>
			<input type="hidden" name="expired" value="1">
		<?php } ?>

	</div>
<?php
	return '<form id="filter-form" class="search_filter">' . ob_get_clean() . '</form>';
}
add_shortcode('auction_filter', 'auction_filter_func');

function auction_search_field_func()
{
	ob_start();
	$query_value = isset($_GET['sort']) ? json_decode(stripslashes($_GET['sort'])) : '';
?>
	<div class="search-wrap mb-3">
		<i class="fa fa-search"></i>
		<input type="text" name="auction_search" placeholder="Search auctions..." value="<?php echo isset($_GET['auction_search']) ? json_decode(stripslashes($_GET['auction_search'])) : '' ?>" />
	</div>
	<div class="search-wrap sort-wrapper">
		<select name="sort" id="sort" class="sort">
			<option <?php echo $query_value == '' ? 'selected' : '' ?> value=""> </option>
			<option <?php echo $query_value == 'newly_listed' ? 'selected' : '' ?> value="newly_listed">Newly Listed</option>
			<option <?php echo $query_value == 'uwa_ending' ? 'selected' : '' ?> value="uwa_ending">Ending Soon</option>
			<option <?php echo $query_value == 'no_reserve' ? 'selected' : '' ?> value="no_reserve">No Reserve</option>
		</select>
		<?php if (empty($query_value)) : ?>
			<h5 class="sort-by-label">Sort By</h5>
		<?php endif; ?>
	</div>
	<?php
	return '<form id="auction-search-form" class="search_auction mt-2 mb-0">' . ob_get_clean() . '</form>';
}
add_shortcode('auction_search_field', 'auction_search_field_func');

function auction_list_func($atts = [])
{
	global $woocommerce_loop, $woocommerce;

	$filter_request 				= isset($_GET['filter_request']) ? json_decode(stripslashes($_GET['filter_request'])) : null;
	$filter['body_style'] 			= isset($_GET['body_style']) ? json_decode(stripslashes($_GET['body_style'])) : null;
	$filter['car_transmission'] 	= isset($_GET['car_transmission']) ? json_decode(stripslashes($_GET['car_transmission'])) : null;
	// $filter['mileage'] 				= isset($_GET['mileage']) ? json_decode(stripslashes($_GET['mileage'])) : null;
	$filter['min_year'] 			= isset($_GET['min_year']) ? json_decode(stripslashes($_GET['min_year'])) : null;
	$filter['max_year']				= isset($_GET['max_year']) ? json_decode(stripslashes($_GET['max_year'])) : null;
	// $filter['min_price'] 			= isset($_GET['min_price']) ? json_decode(stripslashes($_GET['min_price'])) : null;
	// $filter['max_price']			= isset($_GET['max_price']) ? json_decode(stripslashes($_GET['max_price'])) : null;
	$filter['expired']				= isset($_GET['expired']) ? json_decode(stripslashes($_GET['expired'])) : null;
	$filter['sort']					= isset($_GET['sort']) ? json_decode(stripslashes($_GET['sort'])) : null;
	$auction_search					= isset($_GET['auction_search']) ? json_decode(stripslashes($_GET['auction_search'])) : null;

	extract(shortcode_atts(array(
		'category'  => '',
		'columns' 	=> '4',
		'orderby'   => 'id',
		'order'     => 'desc',
		'hidescheduled' => 'yes',
		'paginate' => 'true',
		'limit' => 10,
		'expired' => false
	), $atts));

	$limit = (int)$limit;  // don't remove

	if (!$expired && !$filter['expired']) {
		$meta_query[] = array(
			'key'     => 'woo_ua_auction_closed',
			'compare' => 'NOT EXISTS',
		);

		if ($hidescheduled == 'yes') {
			$meta_query[] = array(
				'key'     => 'woo_ua_auction_started',
				'compare' => 'NOT EXISTS',
			);
		}
	} else {
		$meta_query[] = array(
			'key'     => 'woo_ua_auction_closed',
			'compare' => 'EXISTS',
		);
	}

	if (isset($_GET)) {
		if ($filter['sort']) {
			if ($filter['sort'] == 'uwa_ending') {

				$orderby = 'woo_ua_auction_end_date';
				$order = 'Asc';

				$time = current_time('Y-m-d h:i');
				$meta_query[] = array(
					'woo_ua_auction_end_date' => array(
						'key' => 'woo_ua_auction_end_date',
						'value' => $time,
						'type' => 'DATETIME',
						'compare' => '>=',
					)
				);
			}
			if ($filter['sort'] == 'no_reserve') {
				$meta_query[] = array(
					'key'       => 'like_to_add_a_reserve',
					'value'     => 'Yes',
					'compare'   => '!=',
				);
			}
		}

		if ($filter['body_style'] && count($filter['body_style']) > 0 && !empty($filter['body_style'][0])) {
			$meta_query[] = array(
				'key'       => 'body_style',
				'value'     => $filter['body_style'],
				'compare'   => 'IN',
			);
		}
		if ($filter['car_transmission'] && count($filter['car_transmission']) > 0 && !empty($filter['car_transmission'][0])) {
			$meta_query[] = array(
				'key'       => 'car_transmission',
				'value'     => $filter['car_transmission'],
				'compare'   => 'IN',
			);
		}
		if ($filter['min_year'] && $filter['max_year']) {
			$meta_query[] = array(
				'key'       => 'year',
				'value'     => array((int)$filter['min_year'], (int)$filter['max_year']),
				'compare'   => 'BETWEEN',
			);
		}
		// if($filter['mileage']){
		// 	$endMileage = end($filter['mileage']) == '100+' ? 999 * 999 : (+end($filter['mileage']));
		// 	$meta_query[] = array(
		// 		'key'       => 'indicated_mileage',
		// 		'value'     => array($filter['mileage'][0], $endMileage),
		// 		'compare'   => 'BETWEEN',
		// 		'type'    	=> 'numeric',
		// 	);
		// }
		// if($filter['min_price'] && $filter['max_price']){
		// 	$meta_query[] = array(
		// 		'key'       => 'woo_ua_opening_price',
		// 		'value'     => array( (int)$filter['min_price'], (int)$filter['max_price'] ),
		// 		'compare'   => 'BETWEEN',
		// 		'type'    	=> 'numeric',
		// 	);
		// }
	}

	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => $orderby,
		'order' => $order,
		'meta_query' => $meta_query,
		//'posts_per_page' => -1,   // -1 is default for all results to display
		'posts_per_page' => $limit,
		'tax_query' => array(array(
			'taxonomy' => 'product_type', 'field' => 'slug',
			'terms' => 'auction'
		)),
		'auction_arhive' => TRUE,
	);

	if ($auction_search && !empty($auction_search)) {
		$args['s'] = $auction_search;
	}

	/* Set Pagination Variable */
	if ($paginate === "true") {
		$paged = get_query_var('paged') ? get_query_var('paged') : 1;
		$args['paged'] = $paged;
		//$woocommerce_loop['paged'] = $paged;
	}
	ob_start();
	$products = new WP_Query($args);

	// $woocommerce_loop['columns'] = $columns;

	if ($products->have_posts()) : ?>

		<?php

		/* Pagination Top Text */
		if ($paginate === "true" && ($limit >= 1 || $limit === -1)) {
			$args_toptext = array(
				'total'    => $products->found_posts,
				//'per_page' => $products->get( 'posts_per_page' ),
				'per_page' => $limit,
				'current'  => max(1, get_query_var('paged')),
			);
			wc_get_template('loop/result-count.php', $args_toptext);
		}
		?>

		<?php woocommerce_product_loop_start(); ?>

		<?php while ($products->have_posts()) : $products->the_post(); ?>

			<?php wc_get_template_part('content', 'product'); ?>

		<?php endwhile; // end of the loop. 
		?>

		<?php woocommerce_product_loop_end(); ?>
	<?php else : ?>

		<?php wc_get_template('loop/no-products-found.php'); ?>

	<?php endif;

	wp_reset_postdata();


	/* ---  Display Pagination ---  */

	if ($paginate === "true" && $limit >= 1  && $limit < $products->found_posts) { // don't change condition else design conflicts

		$big = 999999999;
		$current = max(1, get_query_var('paged'));
		$total   = $products->max_num_pages;
		$base    = esc_url_raw(str_replace($big, '%#%', remove_query_arg('add-to-cart', get_pagenum_link($big, false))));
		$format  = '?paged=%#%';

		if ($total <= 1) {
			return;
		}

		$display_data = '<nav class="woocommerce-pagination">';
		$display_data .= paginate_links(
			apply_filters(
				'woocommerce_pagination_args',
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
			)
		);
		$display_data .= '</nav>';
		echo $display_data;
	} /* end of if - paginate */
	// $auction_html = '<div class="product_listing">' . ob_get_clean() . '</div>';
	// if($filter_request){
	// 	echo json_encode(['html'=> ob_get_clean(), 'args'=> $args ]);
	// 	die();
	// }else{
	// }
	return '<div class="product_listing">' . ob_get_clean() . '</div>';
}
add_shortcode('auction_list', 'auction_list_func');
add_action('wp_ajax_product_filters_action', 'auction_list_func');
add_action('wp_ajax_nopriv_product_filters_action', 'auction_list_func');

function home_featured_action_func()
{
	global $woocommerce_loop, $woocommerce;

	$meta_query[] = array(
		'key'     => 'woo_ua_auction_closed',
		'compare' => 'NOT EXISTS',
	);

	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'orderby' => 'id',
		'order' => 'desc',
		'tax_query' => array(array('taxonomy' => 'product_type', 'field' => 'slug', 'terms' => 'auction')),
		'meta_query' => $meta_query,
		'auction_arhive' => TRUE,
	);

	$args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'name',
		'terms'    => 'featured',
	);


	ob_start();
	$products = new WP_Query($args);
	$woocommerce_loop['columns'] = $columns;
	if ($products->have_posts()) : ?>

		<div class="featured-list">

			<?php while ($products->have_posts()) : $products->the_post();
				global $product;
				$address = get_field('city_state');
			?>

				<div class="featured-product d-flex product justify-content-between align-items-center">
					<div class="auction-details">
						<h3>Featured Auction</h3>
						<div class="timer-wrapper">
							<?php echo do_shortcode('[countdown id="' . get_the_ID() . '"]') ?>
						</div>
						<h3 class="woocommerce-loop-product__title m-0"><?php the_title() ?></h3>
						<div class="tag-row d-flex align-items-center gap-10 mb-2">
							<?php if (!empty($address['city']) && !empty($address['state'])) : ?>
								<span class="tag"><?php echo $address['city'] . ', ' . $address['state'] ?></span>
							<?php endif; ?>
						</div>
						<div class="center-tab d-flex align-items-center mb-2">
							<div class="tag-row d-flex flex-direction-column">
								<span class="text"><?php printf(__('%s', 'woo_ua'), wc_price($product->get_uwa_auction_start_price())); ?></span>
								<span class="tag">Opening Bid</span>
							</div>
							<div class="featured-sep"></div>
							<div class="tag-row d-flex flex-direction-column">
								<span class="text" style="color: #EC1B34;"><?php printf(__('%s', 'woo_ua'), wc_price($product->get_uwa_auction_current_bid())); ?></span>
								<span class="tag"><?php echo count($product->uwa_auction_log_history()); ?> Bid<?php echo count($product->uwa_auction_log_history()) > 1 ? 's' : ''; ?></span>
							</div>
						</div>
						<a href="<?php echo get_the_permalink() ?>" class="wp-element-button place-bid">Bid Now</a>
					</div>
					<div class="featured-img">
						<img src="<?php echo getListingThumbnail(get_the_ID()); ?>" class="auction-img" />
					</div>
				</div>

			<?php endwhile; // end of the loop. 
			?>

		</div>

	<?php endif;

	wp_reset_postdata();


	return '<div class="product_listing">' . ob_get_clean() . '</div>';
}
add_shortcode('home_featured_action', 'home_featured_action_func');


function auction_home_filter_func()
{
	$meta_query[] = array('key' => 'woo_ua_auction_closed', 'compare' => 'NOT EXISTS');

	$args = array(
		'post_type'	=> 'product',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => 'id',
		'order' => 'desc',
		'meta_query' => $meta_query,
		'posts_per_page' => -1,   // -1 is default for all results to display
		'tax_query' => array(array(
			'taxonomy' => 'product_type', 'field' => 'slug',
			'terms' => 'auction'
		)),
		'auction_arhive' => TRUE,
	);
	$filters = [
		'body_style' => [],
		'car_transmission' => [],
		'year' => [],
		'mileage' => [],
		'pricing' => [],
	];

	$products = new WP_Query($args);
	while ($products->have_posts()) : $products->the_post();
		global $product;

		array_push($filters['body_style'], get_field('body_style'));
		array_push($filters['car_transmission'], get_field('car_transmission')['label']);
		array_push($filters['year'], get_field('year'));
		array_push($filters['mileage'], get_field('indicated_mileage'));
		array_push($filters['pricing'], $product->get_uwa_auction_start_price());

	endwhile;

	$filters['body_style'] = array_count_values($filters['body_style']);
	$filters['car_transmission'] = array_count_values($filters['car_transmission']);

	sort($filters['mileage']);
	sort($filters['year']);
	sort($filters['pricing']);

	$start_mileage = (int)current($filters['mileage']);
	$end_mileage = (int)end($filters['mileage']);
	$new_mileage = [[$start_mileage, $start_mileage + 3]];

	for ($i = $start_mileage; $i <= $end_mileage; $i++) {
		if ($i == $end_mileage) {
			continue;
		}
		$tmp_last_val = (($i + 3) > $end_mileage) ? $end_mileage : $i + 3;
		if (end($new_mileage)[1] == $i) {
			array_push($new_mileage, [$i, $tmp_last_val]);
		}
	}
	ob_start();
	?>
	<div class="select-box">
		<div class="select-rect d-flex flex-wrap justify-content-between align-items-center">
			<div class="select-wrapper first-select">
				<select name="body_style" class="empty">
					<option value="" disabled selected>Select</option>
					<?php
					$body_styles = acf_get_field('body_style');
					foreach ($body_styles["choices"] as $body_style) {
						echo '<option value="' . $body_style . '">' . $body_style . '</option>';
					}
					?>
				</select>
				<h5>Body Style</h5>
			</div>
			<div class="select-wrapper third-select">
				<select name="car_transmission" class="empty">
					<option value="" disabled selected>Select</option>
					<?php
					$car_transmission = acf_get_field('car_transmission');
					foreach ($car_transmission["choices"] as $transmission) {
						echo '<option value="' . $transmission . '">' . $transmission . '</option>';
					}
					?>
				</select>
				<h5>Transmission</h5>
			</div>
			<div class="select-wrapper last-select">
				<select name="min_year" class="empty">
					<option value="" disabled selected>Year</option>
					<?php
					foreach ($filters['year'] as $year) {
						echo '<option value="' . $year . '">' . $year . '</option>';
					}
					?>
				</select>
				<h5>Year</h5>
			</div>
			<!-- <div class="select-wrapper fifth-select">
			<select name="mileage" class="empty">
				<option value="" disabled selected>Mileage</option>
				<?php
				// foreach($new_mileage as $mileage){
				// 	echo '<option value="'.$mileage[0].'-'.$mileage[1].'">'.$mileage[0].' miles - '.$mileage[1].' miles</option>';
				// }
				?>
			</select>
			<h5>Mileage</h5>
		</div> -->
			<!-- <div class="select-wrapper sixth-select">
			<select name="min_price" class="empty">
				<option value="" disabled selected>Price</option>
				<?php
				// foreach($filters['pricing'] as $pricing){
				// 	echo '<option value="'.$pricing.'">'.$pricing.'</option>';
				// }
				?>
			</select>
			<h5>Price</h5>
		</div> -->
			<div class="submit-wrapper">
				<button>
					<img src="<?php echo get_stylesheet_directory_uri() ?>/images/search.png" class="search-icon">
					<span>Search</span>
				</button>
			</div>
		</div>
	</div>
<?php
	return '<form id="home-filter-form" class="search_filter">' . ob_get_clean() . '</form>';
}
add_shortcode('auction_home_filter', 'auction_home_filter_func');


add_filter('woocommerce_account_menu_items', 'QuadLayers_remove_acc_address', 9999);
function QuadLayers_remove_acc_address($items)
{
	// $items['uwa-auctions'] = 'Your Bids & Watchlist';
	// $items['orders'] = 'Your Auctions';
	// $items['messages'] = 'Messages';
	// var_dump($items);die;
	// unset( $items['downloads'] );
	$items = array(
		"dashboard" => "Dashboard",
		"uwa-auctions" => "Your Bids & Watchlist",
		"orders" => "Your Auctions",
		"messages" => "Messages",
		"edit-address" => "Addresses",
		"payment-methods" => "Payment methods",
		"edit-account" => "Account Details",
		"customer-logout" => "Log Out",
	);
	return $items;
}

// 1. Register new endpoint
// Note: Resave Permalinks or it will give 404 error  
function QuadLayers_add_messages_endpoint()
{
	add_rewrite_endpoint('messages', EP_ROOT | EP_PAGES);
}
add_action('init', 'QuadLayers_add_messages_endpoint');
// ------------------
// 2. Add new query
function QuadLayers_messages_query_vars($vars)
{
	$vars[] = 'messages';
	return $vars;
}
add_filter('query_vars', 'QuadLayers_messages_query_vars', 0);
// // ------------------
// 3. Add content to the new endpoint  
function QuadLayers_messages_content()
{
	echo '<h2>Messages</h2>';
	// echo do_shortcode('[fep_shortcode_new_announcement_count show_bracket="1"]');
	echo do_shortcode('[front-end-pm fepaction="messagebox" fep-filter="show-all"]');
	// echo do_shortcode('[fep_shortcode_message_to to="testdev12345" subject="test dev 101" text="Contact"]');
}
add_action('woocommerce_account_messages_endpoint', 'QuadLayers_messages_content');


add_action("wp", "get_action_data");

function get_action_data()
{
	global $wp;
	$request = explode('/', $wp->request);
	if (end($request) == "edit-auction") {
		$auction_id = isset($_GET['auction_id']) ? $_GET['auction_id'] : null;
		$product = wc_get_product($auction_id);
		if ($product->status != "draft" || empty($auction_id)) {
			wp_redirect(site_url() . "/my-account/orders");
			exit;
		}
	}
}

add_filter('gform_pre_render_13', function ($g_form) {
	global $wp;
	$request = explode('/', $wp->request);
	if (end($request) == "edit-auction") {
		$submitListing = [];
		$auction_id = isset($_GET['auction_id']) ? $_GET['auction_id'] : null;
		$product = wc_get_product($auction_id);
		if ($product->status == "draft") {
			$fields_names = [
				10 => 'private_party_or_dealer',
				11 => 'is_the_title_in_your_name',
				51 => 'street_address',
				52 => 'suite_apartment_number',
				53 => 'city',
				54 => 'state',
				56 => 'zip_code',
				57 => 'contact_number',
				13 => 'referred_status',
				17 => 'referred_firstname',
				18 => 'referred_lastname',
				20 => 'referred_email',
				21 => 'referred_phone',
				22 => 'how_you_hear_about_vinhammer',
				23 => 'how_you_hear_about_vinhammer_others',
				42 => 'pricing',
				25 => 'like_to_add_a_reserve',
				43 => 'reserve_price',
				45 => 'vin',
				46 => 'make',
				47 => 'model',
				49 => 'year',
				50 => 'indicated_mileage',
				59 => 'true_mileage',
				60 => 'actual_mileage',
				61 => 'car_transmission',
				62 => 'body_style',
				63 => 'driveline',
				64 => 'fuel_type',
				65 => 'engine_size',
				66 => 'exterior_color',
				67 => 'interior_color',
				69 => 'additional_features',
				70 => 'additional_unique_features',
				75 => 'vehicle_history',
				76 => 'sale_status',
				77 => 'sale_status_description',
				79 => 'services_status',
				80 => 'services_status_description',
				81 => 'modifications_status',
				82 => 'modifications_status_description',
				83 => 'pain_body_status',
				84 => 'pain_body_status_description',
				86 => 'known_issue',
				87 => 'known_issue_description',
				88 => 'accident_status',
				89 => 'accident_status_description',
				90 => 'rusts_status',
				91 => 'rusts_description',
				93 => 'addition_info',
				94 => 'need_professional_photography',
				1000 => 'youtube_vimeo_links'
			];

			foreach ($g_form['fields'] as &$g_field) {
				if ($g_field['id'] == 111) {
					foreach ($g_field->choices as &$choice) {
						$choice['isSelected'] = true;
					}
				}
				if ($g_field['id'] == 124) {
					$g_field->defaultValue = $product->get_short_description();
				}
				if ($g_field['id'] == 125) {
					$g_field->defaultValue = $product->get_description();
				}
				if ($g_field['id'] == 128) {
					$g_field->defaultValue = get_post_meta($product->id, 'woo_ua_auction_start_date', true);
				}
				if ($g_field['id'] == 129) {
					$g_field->defaultValue = get_post_meta($product->id, 'woo_ua_auction_end_date', true);
				}
				if ($g_field['id'] == 130) {
					$g_field->defaultValue = $product->id;
				}

				// if($g_field['id'] == 102){
				// 	// Replace 'default_value' with the URL or path to your default file
				// 	$field_values = array('https://deleoye.ng/wp-content/uploads/2016/11/Dummy-image.jpg','https://deleoye.ng/wp-content/uploads/2016/11/Dummy-image.jpg','https://deleoye.ng/wp-content/uploads/2016/11/Dummy-image.jpg');

				// 	// Get a reference to the file upload field object
				// 	// $file_upload_field = GFFormsModel::get_field( $g_form['form_id'], $g_field['id']);

				// 	// Set the default value for the file upload field

				// 	$g_field->defaultValue = implode( ',', $field_values );
				// 	// $g_field->content = '<img src="https://deleoye.ng/wp-content/uploads/2016/11/Dummy-image.jpg"/>';

				// 	// var_dump($g_field);die;
				// }

				foreach ($fields_names as $f_id => $f_name) {
					if ($g_field['id'] != $f_id) {
						continue;
					}

					$val = get_field($f_name, $product->id);

					if ($g_field['id'] == 69 || $g_field['id'] == 94) {
						foreach ($g_field->choices as &$choice) {
							if (!empty($val)) {
								foreach ($val as $ck) {
									if (!empty($ck)) {
										if ($choice['value'] == $ck['value']) {
											$choice['isSelected'] = true;
										}
									}
								}
							}
						}
					}

					if ($g_field['id'] == $f_id) {
						if ($g_field->type == 'repeater') {
							$videos = [];
							foreach (get_field('youtube_vimeo_links', $product->id) as $key => $youtube_vimeo_links) {
								$g_field->fields[0]->defaultValue = $youtube_vimeo_links["url"];
							}
						}

						if (is_array($val)) {
							$val = $val["label"];
						}

						$g_field->defaultValue = $val;
					}
				}
			}
		}
	}
	return $g_form;
});

// 'https://deleoye.ng/wp-content/uploads/2016/11/Dummy-image.jpg';

add_action('wp_ajax_auction_delete_listing', 'delete_listing_func');
add_action('wp_ajax_nopriv_auction_delete_listing', 'delete_listing_func');
function delete_listing_func()
{
	$auction_id = isset($_POST['auction_id']) ? json_decode(stripslashes($_POST['auction_id'])) : null;
	$delete_id = wp_delete_post($auction_id);
	echo json_encode(['msg' => 'Delete Successfully.', 'delete_id' => $delete_id]);
	die();
}

// Validate
function action_woocommerce_save_account_details_errors($args)
{
	if (isset($_POST['image']) && empty($_POST['image'])) {
		$args->add('image_error', __('Please provide a valid image', 'woocommerce'));
	}
	if (isset($_POST['billing_phone']) && empty($_POST['billing_phone'])) {
		$args->add('billing_phone_error', __('Please provide a valid phone number', 'woocommerce'));
	}
}
add_action('woocommerce_save_account_details_errors', 'action_woocommerce_save_account_details_errors', 10, 1);

// Save
function action_woocommerce_save_account_details($user_id)
{
	if (isset($_POST['billing_phone'])) {
		update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
	}

	// For Billing email (added related to your comment)
	if (isset($_POST['billing_company'])) {
		update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['billing_company']));
	}

	if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {

		$old_attachment_id = get_user_meta($user_id, 'image', true);
		wp_delete_attachment($old_attachment_id);

		require_once(ABSPATH . 'wp-admin/includes/image.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/media.php');

		$attachment_id = media_handle_upload('image', 0);

		if (is_wp_error($attachment_id)) {
			update_user_meta($user_id, 'image', $_FILES['image'] . ": " . $attachment_id->get_error_message());
		} else {
			update_user_meta($user_id, 'image', $attachment_id);
		}
	}
}
add_action('woocommerce_save_account_details', 'action_woocommerce_save_account_details', 10, 1);

// Add enctype to form to allow image upload
function action_woocommerce_edit_account_form_tag()
{
	echo 'enctype="multipart/form-data"';
}
add_action('woocommerce_edit_account_form_tag', 'action_woocommerce_edit_account_form_tag');


function um_get_avatar_1($avatar = '', $id_or_email = '', $size = '96', $avatar_class = '', $default = '', $alt = '')
{
	if (is_numeric($id_or_email))
		$user_id = (int) $id_or_email;
	elseif (is_string($id_or_email) && ($user = get_user_by('email', $id_or_email)))
		$user_id = $user->ID;
	elseif (is_object($id_or_email) && !empty($id_or_email->user_id))
		$user_id = (int) $id_or_email->user_id;
	if (empty($user_id))
		return $avatar;

	$attachment_id = get_user_meta($user_id, 'image', true);

	// True
	if ($attachment_id) {
		$original_image_url = wp_get_attachment_url($attachment_id);
		return wp_get_attachment_image($attachment_id, "thumbnail", "", array("class" => "avatar img-responsive"));
	} else {
		return '<img src="' . get_stylesheet_directory_uri() . '/images/profile-img.png" class="avatar"/>';
	}

	return $avatar;
}
add_filter('get_avatar', 'um_get_avatar_1', 999999, 5);

function get_profile_pic($user_id)
{
	$attachment_id = get_user_meta($user_id, 'image', true);
	$attachment = wp_get_attachment_image($attachment_id);
	if (empty($attachment)) {
		$attachment = '<img src="' . get_stylesheet_directory_uri() . '/images/profile-img.png" class="avatar"/>';
	}
	return $attachment;
}

function get_auction_title_by_id_func()
{
	$auction_id = isset($_GET['auction_id']) ? $_GET['auction_id'] : null;
	$html = '';
	if ($auction_id) {
		$html .= '<div class="edit-top-header">';
		$html .= '<div class="site-container d-flex flex-wrap align-items-center gap-20">';
		$html .= '<a class="back-wrap" href="javascript:history.back()"><img src="' . get_stylesheet_directory_uri() . '/images/back.svg" class="back"/></a>';
		$html .= '<a class="image-wrap" href="' . get_the_permalink($auction_id) . '"><img src="' . getListingThumbnail($auction_id) . '" class="w-100"/></a>';
		$html .= '<p class="post-title d-flex flex-direction-column m-0"><span class="tag">You are editing:</span><a href="' . get_the_permalink($auction_id) . '">' . get_the_title($auction_id) . '</a></p>';
		$html .= '</div>';
		$html .= '</div>';
	}
	return $html;
	// exit;
}
add_shortcode('get_auction_title_by_id_func', 'get_auction_title_by_id_func');


add_filter('wp_nav_menu_items', 'do_shortcode');


function send_message_to_auction_specialist_func($atts = [])
{
	extract(shortcode_atts(array(
		'specialist_name' => 'admin.misbah',
	), $atts));
?>
	<script>
		jQuery(function($) {
			$(document).on('click', '.message-specialist', function(event) {
				event.preventDefault();

				var seller_html = `<div class="text-left gform_wrapper gravity-theme"><?php echo do_shortcode('[fep_shortcode_new_message_form to=' . $specialist_name . ' subject="{current-post-title}" heading=""]'); ?></div>`;
				Swal.fire({
					title: 'Contact VINHammer Specialist',
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
						console.log("bid success full");
						result_conf = true;
						$('#uwa_auction_form').submit();
					} else {
						result_conf = false;
					}
				});
			})

		})
	</script>
	<?php
}
add_shortcode('send_message_to_auction_specialist', 'send_message_to_auction_specialist_func');


function get_loggedin_user_name_func()
{
	$current_user = wp_get_current_user();
	$html = '<div class="logged-user-details d-flex gap-10 align-items-center">';
	$html .= get_avatar($current_user->ID);
	$html .= '<p class="m-0">' . $current_user->display_name . '</p>';
	$html .= '</div>';
	return $html;
}
add_shortcode('get_loggedin_user_name', 'get_loggedin_user_name_func');

// Notifications Start

// place bid
add_action('ultimate_woocommerce_auction_place_bid', 'add_place_bid_record_func', 1);
function add_place_bid_record_func($data)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'auction_notifications';
	$title = 'place_bid';
	$content = "You have recently placed a bid on";
	$notification_icon = NULL;
	$user_id = NULL;
	$auction_id = $data['product_id'];
	$log_id = $data['log_id'];
	$sql = "SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE `id`=" . $log_id . " ";
	$results = $wpdb->get_results($sql);
	if (!empty($results)) {
		$user_id = $results[0]->userid;
	}
	$wpdb->query("INSERT INTO $table_name(title, content, notification_icon, user_id, auction_id, log_id) VALUES('$title', '$content', '$notification_icon', '$user_id', '$auction_id', '$log_id')");

	$auc_loss = "SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE `auction_id`=" . $auction_id . " AND userid != " . $user_id . " ORDER BY `bid` DESC LIMIT 1";
	$loss_results = $wpdb->get_results($auc_loss);
	if (!empty($loss_results)) {
		foreach ($loss_results as $result) {
			$wpdb->insert($table_name, array(
				'title' => 'outbid',
				'content' => 'You have been outbid on',
				'user_id' => $result->userid,
				'auction_id' => $auction_id,
			));
		}
	}

	$watchers = get_post_meta($auction_id, "woo_ua_auction_watch");
	foreach ($watchers as $watcher) {
		if ($watcher != $user_id) {
			$title = 'place_bid_watcher';
			$content = "Bid placed on";
			$notification_icon = NULL;
			$user_id = NULL;
			$auction_id = $data['product_id'];
			$log_id = $data['log_id'];
			$wpdb->query("INSERT INTO $table_name(title, content, notification_icon, user_id, auction_id, log_id) VALUES('$title', '$content', '$notification_icon', '$watcher', '$auction_id', '$log_id')");
		}
	}
}
// auction started
add_action('ultimate_woocommerce_auction_started', 'auction_started_function', 10, 1);
function auction_started_function($auction_id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'auction_notifications';
	$title = 'auction_started';
	$content = "Biding Started on";
	$notification_icon = NULL;
	$watchers = get_post_meta($auction_id, "woo_ua_auction_watch");
	foreach ($watchers as $watcher) {
		$wpdb->query("INSERT INTO $table_name(title, content, notification_icon, user_id, auction_id, log_id) VALUES('$title', '$content', '$notification_icon', '$watcher', '$auction_id', null)");
	}
}
// auction won/loss/close nofication
add_action('ultimate_woocommerce_auction_close', 'auction_won_loss_nofication', 50);
function auction_won_loss_nofication($auction_id)
{
	global $wpdb;
	$product = wc_get_product($auction_id);
	$table_name = $wpdb->prefix . 'auction_notifications';

	$watchers = get_post_meta($auction_id, "woo_ua_auction_watch");
	foreach ($watchers as $watcher) {
		if ($watcher != $user_id) {
			$title = 'auction_closed';
			$content = "Bidding has been closed on";
			$notification_icon = NULL;
			$user_id = NULL;
			$auction_id = $auction_id;
			$log_id = $data['log_id'];
			$wpdb->query("INSERT INTO $table_name(title, content, notification_icon, user_id, auction_id, log_id) VALUES('$title', '$content', '$notification_icon', '$watcher', '$auction_id', '$log_id')");
		}
	}

	$sql_won = "SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE `auction_id`=" . $auction_id . " ORDER BY `bid` DESC LIMIT 1";
	$results = $wpdb->get_results($sql_won);
	if (!empty($results)) {
		$wpdb->insert($table_name, array(
			'title' => 'auction_won',
			'content' => 'Congratulations! You have won the bidding on',
			'user_id' => $results[0]->userid,
			'auction_id' => $auction_id,
		));

		$auc_loss = "SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE `auction_id`=" . $auction_id . " AND userid != " . $results[0]->userid . " ORDER BY `bid` DESC LIMIT 1";
		$loss_results = $wpdb->get_results($auc_loss);
		if (!empty($loss_results)) {
			foreach ($loss_results as $result) {
				$wpdb->insert($table_name, array(
					'title' => 'auction_loss',
					'content' => 'Sorry! You have lost the bidding on',
					'user_id' => $result->userid,
					'auction_id' => $auction_id,
				));
			}
		}
	}
}

// bid deleted
add_action('ultimate_woocommerce_auction_delete_bid', 'bid_deleted_nofication', 50);
function bid_deleted_nofication($data)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'auction_notifications';
	$auction_id = $data['product_id'];
	$user_id = $data['delete_user_id'];
	$title = 'bid_deleted';
	$content = "Your bid has been deleted on";
	$notification_icon = NULL;
	$wpdb->insert($table_name, array(
		'title' => $title,
		'content' => $content,
		'user_id' => $user_id,
		'auction_id' => $auction_id,
	));
}

// frontend page notifications
function auction_notifications_func($atts = [])
{

	$current_id = get_current_user_id();
	global $wpdb;
	$table_name = $wpdb->prefix . 'auction_notifications';

	$pending_reservations = $wpdb->get_results(' 
	SELECT *
    FROM ' . $wpdb->prefix . 'auction_notifications AS ac_noti 
    WHERE ac_noti.user_id = ' . $current_id . ' ORDER BY ac_noti.id DESC');

	$html .= '<div class="notification-list">';
	foreach ($pending_reservations as $notification) {
		$statusClass = $notification->status == 0 ? "active " : "seen";
		// var_dump($notification->title);
		$notification_content = $notification->content . ' <a href="' . get_the_permalink($notification->auction_id) . '"><strong>' . get_the_title($notification->auction_id) . '</strong></a>';
		$notification_icon = '<svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.52344 12.6797L12.6797 17.8789L10.1016 20.457L4.90234 15.3008L7.52344 12.6797ZM15.3008 4.90234L20.457 10.1016L17.8789 12.6797L12.6797 7.52344L15.3008 4.90234ZM8.8125 11.3906L11.3906 8.8125L24.3672 21.7891L21.7891 24.3672L8.8125 11.3906ZM4.90234 23.25H15.9023V25.0977H4.90234V23.25Z" fill="#EC1B34"/></svg>';
		switch ($notification->title) {
			case 'auction_won':
				$notification_icon = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.2852 4.39453C11.4492 4.23047 11.5312 4.01953 11.5312 3.76172C11.5312 3.50391 11.4492 3.28125 11.2852 3.09375L10.0898 1.89844L10.8984 1.08984L12.0938 2.28516C12.4922 2.73047 12.6914 3.22266 12.6914 3.76172C12.6914 4.27734 12.4922 4.75781 12.0938 5.20312L9.38672 7.91016L8.61328 7.10156L11.2852 4.39453ZM12.7969 8.89453C13.2422 8.49609 13.7227 8.29688 14.2383 8.29688C14.7773 8.29688 15.2695 8.49609 15.7148 8.89453L16.9102 10.125L16.1367 10.8984L14.9062 9.70312C14.7188 9.51562 14.4961 9.42188 14.2383 9.42188C14.0039 9.42188 13.793 9.51562 13.6055 9.70312L12.4102 10.8984L11.6016 10.0898L12.7969 8.89453ZM7.55859 5.16797C7.72266 5.00391 7.80469 4.78125 7.80469 4.5C7.80469 4.21875 7.72266 3.99609 7.55859 3.83203L7.10156 3.41016L7.91016 2.60156L8.33203 3.02344C8.73047 3.46875 8.92969 3.96094 8.92969 4.5C8.92969 5.01563 8.73047 5.49609 8.33203 5.94141L7.91016 6.39844L7.10156 5.58984L7.55859 5.16797ZM10.8984 9.38672L10.0898 8.61328L14.3086 4.39453C14.7539 3.99609 15.2344 3.79688 15.75 3.79688C16.2891 3.79688 16.7812 3.99609 17.2266 4.39453L17.6484 4.85156L16.875 5.66016L16.418 5.20312C16.2305 5.01563 16.0078 4.92188 15.75 4.92188C15.4922 4.92188 15.2695 5.01563 15.082 5.20312L10.8984 9.38672ZM1.51172 16.4883L5.23828 6.01172L11.9883 12.7617L1.51172 16.4883Z" fill="#908E8E"/></svg>';
				break;
			case 'auction_loss':
			case 'bid_deleted':
			case 'auction_closed':
				$notification_icon = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.74609 13.2539C5.94141 14.4258 7.35938 15.0117 9 15.0117C10.6406 15.0117 12.0469 14.4258 13.2188 13.2539C14.4141 12.0586 15.0117 10.6406 15.0117 9C15.0117 7.35937 14.4141 5.95312 13.2188 4.78125C12.0469 3.58594 10.6406 2.98828 9 2.98828C7.35938 2.98828 5.94141 3.58594 4.74609 4.78125C3.57422 5.95312 2.98828 7.35937 2.98828 9C2.98828 10.6406 3.57422 12.0586 4.74609 13.2539ZM3.69141 3.72656C5.16797 2.25 6.9375 1.51172 9 1.51172C11.0625 1.51172 12.8203 2.25 14.2734 3.72656C15.75 5.17969 16.4883 6.9375 16.4883 9C16.4883 11.0625 15.75 12.832 14.2734 14.3086C12.8203 15.7617 11.0625 16.4883 9 16.4883C6.9375 16.4883 5.16797 15.7617 3.69141 14.3086C2.23828 12.832 1.51172 11.0625 1.51172 9C1.51172 6.9375 2.23828 5.17969 3.69141 3.72656ZM8.26172 5.23828H9.73828V9.73828H8.26172V5.23828ZM8.26172 11.25H9.73828V12.7617H8.26172V11.25Z" fill="#908E8E"/></svg>';
				break;
			case 'auction_expire_soon':
			case 'auction_published':
				$notification_icon = '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.74609 13.2539C5.94141 14.4258 7.35938 15.0117 9 15.0117C10.6406 15.0117 12.0469 14.4258 13.2188 13.2539C14.4141 12.0586 15.0117 10.6406 15.0117 9C15.0117 7.35937 14.4141 5.95312 13.2188 4.78125C12.0469 3.58594 10.6406 2.98828 9 2.98828C7.35938 2.98828 5.94141 3.58594 4.74609 4.78125C3.57422 5.95312 2.98828 7.35937 2.98828 9C2.98828 10.6406 3.57422 12.0586 4.74609 13.2539ZM3.69141 3.72656C5.16797 2.25 6.9375 1.51172 9 1.51172C11.0625 1.51172 12.8203 2.25 14.2734 3.72656C15.75 5.17969 16.4883 6.9375 16.4883 9C16.4883 11.0625 15.75 12.832 14.2734 14.3086C12.8203 15.7617 11.0625 16.4883 9 16.4883C6.9375 16.4883 5.16797 15.7617 3.69141 14.3086C2.23828 12.832 1.51172 11.0625 1.51172 9C1.51172 6.9375 2.23828 5.17969 3.69141 3.72656ZM8.26172 5.23828H9.73828V9.73828H8.26172V5.23828ZM8.26172 11.25H9.73828V12.7617H8.26172V11.25Z" fill="#908E8E"/></svg>';
				$notification_content = $notification->content;
				break;
			case 'place_bid_watcher':
				$notification_icon = '<svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.9844 15.5156V9.51562C12.9844 8.23438 12.625 7.17188 11.9062 6.32812C11.1875 5.45312 10.2188 5.01562 9 5.01562C7.78125 5.01562 6.8125 5.45312 6.09375 6.32812C5.375 7.17188 5.01562 8.23438 5.01562 9.51562V15.5156H12.9844ZM15 14.4844L17.0156 16.5V17.4844H0.984375V16.5L3 14.4844V9.51562C3 7.95312 3.39062 6.59375 4.17188 5.4375C4.98438 4.28125 6.09375 3.53125 7.5 3.1875V2.48438C7.5 2.07812 7.64062 1.73438 7.92188 1.45312C8.20312 1.14062 8.5625 0.984375 9 0.984375C9.4375 0.984375 9.79688 1.14062 10.0781 1.45312C10.3594 1.73438 10.5 2.07812 10.5 2.48438V3.1875C11.9062 3.53125 13 4.28125 13.7812 5.4375C14.5938 6.59375 15 7.95312 15 9.51562V14.4844ZM10.4062 19.9219C10 20.2969 9.53125 20.4844 9 20.4844C8.46875 20.4844 8 20.2969 7.59375 19.9219C7.1875 19.5156 6.98438 19.0469 6.98438 18.5156H11.0156C11.0156 19.0469 10.8125 19.5156 10.4062 19.9219Z" fill="#908E8E"/></svg>';
				$notification_content = 'A bid was placed on an <a href="' . get_the_permalink($notification->auction_id) . '"><strong>' . get_the_title($notification->auction_id) . '</strong></a> which was in your watchlist.';
				break;
			case 'outbid':
				$notification_icon = '<svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.9844 15.5156V9.51562C12.9844 8.23438 12.625 7.17188 11.9062 6.32812C11.1875 5.45312 10.2188 5.01562 9 5.01562C7.78125 5.01562 6.8125 5.45312 6.09375 6.32812C5.375 7.17188 5.01562 8.23438 5.01562 9.51562V15.5156H12.9844ZM15 14.4844L17.0156 16.5V17.4844H0.984375V16.5L3 14.4844V9.51562C3 7.95312 3.39062 6.59375 4.17188 5.4375C4.98438 4.28125 6.09375 3.53125 7.5 3.1875V2.48438C7.5 2.07812 7.64062 1.73438 7.92188 1.45312C8.20312 1.14062 8.5625 0.984375 9 0.984375C9.4375 0.984375 9.79688 1.14062 10.0781 1.45312C10.3594 1.73438 10.5 2.07812 10.5 2.48438V3.1875C11.9062 3.53125 13 4.28125 13.7812 5.4375C14.5938 6.59375 15 7.95312 15 9.51562V14.4844ZM10.4062 19.9219C10 20.2969 9.53125 20.4844 9 20.4844C8.46875 20.4844 8 20.2969 7.59375 19.9219C7.1875 19.5156 6.98438 19.0469 6.98438 18.5156H11.0156C11.0156 19.0469 10.8125 19.5156 10.4062 19.9219Z" fill="#908E8E"/></svg>';
				$notification_content = 'You have been outbid on <a href="' . get_the_permalink($notification->auction_id) . '"><strong>' . get_the_title($notification->auction_id) . '</strong></a>';
				break;
			case 'auction_antisniping':
				$notification_icon = '<svg width="18" height="21" viewBox="0 0 18 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12.9844 15.5156V9.51562C12.9844 8.23438 12.625 7.17188 11.9062 6.32812C11.1875 5.45312 10.2188 5.01562 9 5.01562C7.78125 5.01562 6.8125 5.45312 6.09375 6.32812C5.375 7.17188 5.01562 8.23438 5.01562 9.51562V15.5156H12.9844ZM15 14.4844L17.0156 16.5V17.4844H0.984375V16.5L3 14.4844V9.51562C3 7.95312 3.39062 6.59375 4.17188 5.4375C4.98438 4.28125 6.09375 3.53125 7.5 3.1875V2.48438C7.5 2.07812 7.64062 1.73438 7.92188 1.45312C8.20312 1.14062 8.5625 0.984375 9 0.984375C9.4375 0.984375 9.79688 1.14062 10.0781 1.45312C10.3594 1.73438 10.5 2.07812 10.5 2.48438V3.1875C11.9062 3.53125 13 4.28125 13.7812 5.4375C14.5938 6.59375 15 7.95312 15 9.51562V14.4844ZM10.4062 19.9219C10 20.2969 9.53125 20.4844 9 20.4844C8.46875 20.4844 8 20.2969 7.59375 19.9219C7.1875 19.5156 6.98438 19.0469 6.98438 18.5156H11.0156C11.0156 19.0469 10.8125 19.5156 10.4062 19.9219Z" fill="#908E8E"/></svg>';
				$notification_content = 'Auction time has been extended for <a href="' . get_the_permalink($notification->auction_id) . '"><strong>' . get_the_title($notification->auction_id) . '</strong></a>';
				break;
		}
		$html .= '<p class="notification-text ' . $statusClass . '"><span class="icon-wrap align-items-center justify-content-center">' . $notification_icon . '</span> ' . $notification_content . '</p>';
	}
	$html .= '</div>';

	$wpdb->update(
		$wpdb->prefix . "auction_notifications",
		array(
			'status' => 1
		),
		array(
			'user_id' => $current_id,
		)
	);

	return $html;
}
add_shortcode('auction_notifications', 'auction_notifications_func');

add_shortcode('get_notification_count', 'get_notification_count_func');
function get_notification_count_func()
{
	$current_id = get_current_user_id();
	global $wpdb;
	$sql = "SELECT count(id) as unread_msg FROM " . $wpdb->prefix . "auction_notifications WHERE `status`= 0 AND user_id = " . $current_id . " ";
	$results = $wpdb->get_results($sql);
	if (!empty($results) && $results[0]->unread_msg > 0) {
		return '<span class="notification-count">' . $results[0]->unread_msg . '<span>';
	} else {
		return '';
	}
}
// Notifications End


// custom bid function start

function uwa_front_user_bid_list_custom($user_id, $bid_status)
{

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;


	$table = $wpdb->prefix . "woo_ua_auction_log";
	$query   = $wpdb->prepare("SELECT auction_id, MAX(bid) as max_userbid FROM $table  WHERE userid = %d GROUP by auction_id ORDER by date DESC", $user_id);
	$my_auctions = $wpdb->get_results($query);

	$active_bids_count = 0;
	$lost_bids_count = 0;
	$won_bids_count = 0;
	$won_bids_products_ids = array();

	// var_dump($my_auctions);die;

	if (count($my_auctions) > 0) {
		$aelia_addon = "";
		$addons = uwa_enabled_addons();
		if (is_array($addons) && in_array('uwa_currency_switcher', $addons)) {
			$aelia_addon = true;
		}

	?>
		<table class="shop_table shop_table_responsive tbl_bidauc_list">
			<tr class="bidauc_heading">
				<th class="toptable"><?php echo __('Image', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Product', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Your bid', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Current bid', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('End date', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Status', 'woo_ua'); ?></th>
			</tr>

			<?php
			foreach ($my_auctions as $my_auction) {

				$product_id =  $my_auction->auction_id;

				if (function_exists('icl_object_id') && is_object($sitepress) && method_exists($sitepress, 'get_current_language')) {

					$product_id = icl_object_id($my_auction->auction_id, 'product', false, $sitepress->get_current_language());
				}


				$product = wc_get_product($product_id);

				if (is_object($product)) {



					if (method_exists($product, 'get_type') && $product->get_type() == 'auction') {

						if ($aelia_addon == true) {
							if ($product->uwa_aelia_is_configure() == TRUE) {
								$my_auction->max_userbid = $product->uwa_aelia_base_to_active($my_auction->max_userbid);
							}
						}

						$product_name = get_the_title($product_id);
						$product_url  = get_the_permalink($product_id);
						$a            = '<img src="' . getListingThumbnail($product->get_id()) . '"/>';

						if ($bid_status == "won" && $user_id == $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2') {
							$won_bids_count++;
			?>
							<tr class="bidauc_won">
								<td class="bidauc_img"><?php echo $a; ?></td>
								<td class="bidauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
								<td class="bidauc_bid"><?php echo wc_price($my_auction->max_userbid); ?></td>
								<td class="bidauc_curbid"><?php echo $product->get_price_html(); ?></td>
								<td class="bidauc_enddate"><?php echo $product->get_uwa_auction_end_dates(); ?></td>

								<?php

								/* -----  Pay now button for winner ----- */
								if (($user_id == $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2' && !$product->get_uwa_auction_payed())) {

									$won_bids_products_ids[] = $product->get_id();
									$checkout_url = esc_attr(add_query_arg("pay-uwa-auction", $product->get_id(), uwa_auction_get_checkout_url()));

									/* change payment url for auto order */
									$uwa_auto_order_enable = get_option('uwa_auto_order_enable');
									if ($uwa_auto_order_enable == "yes") {

										$productid = $product->get_id();
										$uwa_order_id = get_post_meta($productid, 'woo_ua_order_id', true);
										if ($uwa_order_id > 0) {
											$order = wc_get_order($uwa_order_id);
											$checkout_url = $order->get_checkout_payment_url();
										}
									}

								?>
									<td class="bidauc_status">

										<?php

										/* --- when offline_addon is active --- */

										$addons = uwa_enabled_addons();
										if (is_array($addons) && in_array(
											'uwa_offline_dealing_addon',
											$addons
										)) {

											// buyers and stripe both deactive
											if (
												!in_array(
													'uwa_buyers_premium_addon',
													$addons
												) &&
												!in_array('uwa_stripe_auto_debit_addon', $addons)
											) {
												//echo "in 1";

											}	// buyers active only
											elseif (
												in_array(
													'uwa_buyers_premium_addon',
													$addons
												) &&
												!in_array('uwa_stripe_auto_debit_addon', $addons)
											) {
												//echo "in 2";	

										?>
												<strong>Paid</strong>
											<?php

											}  // buyers and stripe both active
											elseif (
												in_array(
													'uwa_buyers_premium_addon',
													$addons
												) &&
												in_array('uwa_stripe_auto_debit_addon', $addons)
											) {
												//echo "in 3";				
											}
										} else {

											?>
											<strong>Paid</strong>
										<?php

										} /* end of else */

										?>

									</td>

								<?php
								} else { ?>
									<td class="bidauc_status"><?php echo __('Closed', 'woo_ua'); ?></td>
								<?php
								}  ?>

							</tr>
						<?php } /* end of if of won  */

						/* ------------------------ For Lost bids  ---------------------- */ elseif ($bid_status == "lost" && $user_id != $product->get_uwa_auction_current_bider() && $product->get_uwa_auction_expired() == '2') {
							$lost_bids_count++;
						?>
							<tr class="bidauc_lost">
								<td class="bidauc_img"><?php echo $a; ?></td>
								<td class="bidauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
								<td class="bidauc_bid"><?php echo wc_price($my_auction->max_userbid); ?></td>
								<td class="bidauc_curbid"><?php echo $product->get_price_html(); ?></td>
								<td class="bidauc_enddate"><?php echo $product->get_uwa_auction_end_dates(); ?></td>
								<td class="bidauc_status"><?php echo __('Closed', 'woo_ua'); ?></td>
							</tr>
						<?php } /* end of if of lost */

						/* ------------------------ For active bids  ---------------------- */ elseif ($bid_status == "active" && $product->get_uwa_auction_expired() == false) {
							$active_bids_count++;
						?>
							<tr class="bidauc_active">
								<td class="bidauc_img"><?php echo $a; ?></td>
								<td class="bidauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
								<td class="bidauc_bid"><?php echo wc_price($my_auction->max_userbid); ?></td>
								<td class="bidauc_curbid"><?php echo $product->get_price_html(); ?></td>
								<td class="bidauc_enddate"><?php echo $product->get_uwa_auction_end_dates(); ?></td>
								<td class="bidauc_status"><?php echo __('Started', 'woo_ua'); ?></td>
							</tr>
				<?php
						}
					}  /* end of if method exists  */
				}
			} /* end of foreach */

			if ($bid_status == "won" && $won_bids_count == 0) { ?>
				<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
					<?php _e('No bids available yet.', 'woo_ua') ?>
				</div>
				<td class="bidauc_img" colspan="6">
					<div class="not-found-wrapper">
						<img src="<?php echo get_stylesheet_directory_uri() ?>/images/no-auction.svg" class="not-found-img" />
						<p class="not-found-text">No <?php echo $bid_status; ?> bids found</p>
					</div>
				</td>
			<?php
			} elseif ($bid_status == "lost" && $lost_bids_count == 0) { ?>
				<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
					<?php _e('No bids available yet.', 'woo_ua') ?>
				</div>
				<td class="bidauc_img" colspan="6">
					<div class="not-found-wrapper">
						<img src="<?php echo get_stylesheet_directory_uri() ?>/images/no-auction.svg" class="not-found-img" />
						<p class="not-found-text">No <?php echo $bid_status; ?> bids found</p>
					</div>
				</td>

			<?php
			} elseif ($bid_status == "active" && $active_bids_count == 0) { ?>
				<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
					<?php _e('No bids available yet.', 'woo_ua') ?>
				</div>
				<td class="bidauc_img" colspan="6">
					<div class="not-found-wrapper">
						<img src="<?php echo get_stylesheet_directory_uri() ?>/images/no-auction.svg" class="not-found-img" />
						<p class="not-found-text">No <?php echo $bid_status; ?> bids found</p>
					</div>
				</td>
			<?php
			}
			?>
		</table>

	<?php
	} /* end of if - count */ else {
		$shop_page_id = wc_get_page_id('shop');
		$shop_page_url = $shop_page_id ? get_permalink($shop_page_id) : '';
	?>
		<table class="shop_table shop_table_responsive tbl_bidauc_list">
			<tr class="bidauc_heading">
				<th class="toptable"><?php echo __('Image', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Product', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Your bid', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Current bid', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('End date', 'woo_ua'); ?></th>
				<th class="toptable"><?php echo __('Status', 'woo_ua'); ?></th>
			</tr>
			<tr>
				<td class="bidauc_img" colspan="6">
					<div class="not-found-wrapper">
						<img src="<?php echo get_stylesheet_directory_uri() ?>/images/no-auction.svg" class="not-found-img" />
						<p class="not-found-text">No <?php echo $bid_status; ?> bids found</p>
					</div>
				</td>
			</tr>
		</table>

	<?php } /* end of else */
}

// custom bid function end

// custom my wachtlist function start
function uwa_front_user_watchlist_custom($user_id)
{

	global $wpdb, $woocommerce;
	global $product;
	global $sitepress;

	$my_auctions_watchlist = get_uwa_auction_watchlist_by_user($user_id);
	$my_auctions_watchlist_count = uwa_front_user_watchlist_count($user_id);

	if ($my_auctions_watchlist_count > 0) {
	?>
		<table class="shop_table shop_table_responsive tbl_watchauc_list">
			<tr class="watchauc_heading">
				<th class="toptable"><?php echo __('Image', 'woo_ua'); ?></td>
				<th class="toptable"><?php echo __('Product', 'woo_ua'); ?></td>
				<th class="toptable"><?php echo __('Current bid', 'woo_ua'); ?></td>
				<th class="toptable"><?php echo __('Status', 'woo_ua'); ?></td>
				<th class="toptable">
					</td>
			</tr>
			<?php
			foreach ($my_auctions_watchlist as $key => $value) {

				$product      = wc_get_product($value);
				if (!$product)
					continue;

				if (is_object($product) && method_exists($product, 'get_type') && $product->get_type() == 'auction') {

					$product_name = get_the_title($value);
					$product_url  = get_the_permalink($value);
					$a            = '<img src="' . getListingThumbnail($product->get_id()) . '"/>';
					$checkout_url = esc_attr(add_query_arg("pay-uwa-auction", $product->get_id(), uwa_auction_get_checkout_url()));

					/* change payment url for auto order */
					$uwa_auto_order_enable = get_option('uwa_auto_order_enable');
					if ($uwa_auto_order_enable == "yes") {

						$productid = $product->get_id();
						$uwa_order_id = get_post_meta($productid, 'woo_ua_order_id', true);
						if ($uwa_order_id > 0) {
							$order = wc_get_order($uwa_order_id);
							$checkout_url = $order->get_checkout_payment_url();
						}
					}

			?>
					<tr class="watchauc_list">
						<td class="watchauc_img"><?php echo $a ?></td>
						<td class="watchauc_name"><a href="<?php echo $product_url; ?>"><?php echo $product_name ?></a></td>
						<td class="watchauc_curbid"><?php echo $product->get_price_html(); ?></td>
						<?php

						/* -----  Pay now button for winner ----- */

						if (($user_id == $product->get_uwa_auction_current_bider() &&
							$product->get_uwa_auction_expired() == '2' && !$product->get_uwa_auction_payed())) {

						?>
							<td class="watchauc_status">
								<?php

								/* --- when offline_addon is active --- */

								$addons = uwa_enabled_addons();
								if (is_array($addons) && in_array(
									'uwa_offline_dealing_addon',
									$addons
								)) {

									// buyers and stripe both deactive
									if (
										!in_array(
											'uwa_buyers_premium_addon',
											$addons
										) &&
										!in_array('uwa_stripe_auto_debit_addon', $addons)
									) {
										//echo "in 1";

									}	// buyers active only
									elseif (
										in_array(
											'uwa_buyers_premium_addon',
											$addons
										) &&
										!in_array('uwa_stripe_auto_debit_addon', $addons)
									) {
										//echo "in 2";
								?>
										<!--<a href="<?php // echo $checkout_url; 
														?>" -->
										<!--	class="button alt">-->
										<!--	<?php /* echo apply_filters('ultimate_woocommerce_auction_pay_now_button_text', __( -->
											<!--		"Pay Buyer's Premium", -->
											<!--		'woo_ua' ), $product); */ ?>-->
										<!--</a>-->
										<strong>Paid</strong>
									<?php

									}  // buyers and stripe both active
									elseif (
										in_array(
											'uwa_buyers_premium_addon',
											$addons
										) &&
										in_array('uwa_stripe_auto_debit_addon', $addons)
									) {
										//echo "in 3";				
									}
								} else {
									?>
									<!--<a href="<?php // echo $checkout_url; 
													?>" -->
									<!--	class="button alt">-->
									<!--	<?php /* echo apply_filters('ultimate_woocommerce_auction_pay_now_button_text', __( 'Pay Now', -->
									<!--		'woo_ua' ), $product); */ ?>-->
									<!--</a>-->
									<strong>Paid</strong>
								<?php

								} /* end of else */

								?>

							</td>
						<?php
						} elseif ($product->is_uwa_expired()) { ?>

							<td class="watchauc_status"><?php echo __('Closed', 'woo_ua'); ?></td>

						<?php } else { ?>
							<td class="watchauc_status"><?php echo __('Started', 'woo_ua'); ?></td>
						<?php
						}
						?>
						<td class="product-remove">
							<a href="javascript:void(0)" data-auction-id="<?php echo esc_attr($product->get_id()); ?>" class="remove-uwa uwa-watchlist-action remove" aria-label="Remove this item">×</a>
						</td>

					</tr>
			<?php
				}
			} ?>

		</table>

	<?php
	} else {
		$shop_page_id = wc_get_page_id('shop');
		$shop_page_url = $shop_page_id ? get_permalink($shop_page_id) : '';
	?>
		<div class="not-found-wrapper">
			<img src="<?php echo get_stylesheet_directory_uri() ?>/images/no-auction.svg" class="not-found-img" />
			<p class="not-found-text">No watchlist found</p>
		</div>

<?php } /* end of else */
}
// custom my wachtlist function end


function get_my_auctions_count_by_status($status)
{
	$publish_status = 'draft';
	if ($status == 'ended') {
		$meta_query[] = array(
			'key'     => 'woo_ua_auction_closed',
			'compare' => 'EXISTS',
		);
	} else {
		$meta_query[] = array(
			'key'     => 'woo_ua_auction_closed',
			'compare' => 'NOT EXISTS',
		);
	}

	if ($status == 'active' || $status == 'ended') {
		$publish_status = 'publish';
	}

	$args = array(
		'author' =>  get_current_user_id(),
		'post_type'	=> 'product',
		'ignore_sticky_posts'	=> 1,
		'orderby' => 'id',
		'order' => 'desc',
		'meta_query' => $meta_query,
		'posts_per_page' => -1,   // -1 is default for all results to display
		'tax_query' => array(array(
			'taxonomy' => 'product_type', 'field' => 'slug',
			'terms' => 'auction'
		)),
		'auction_arhive' => TRUE,
		'post_status' => $publish_status,
	);

	$products = new WP_Query($args);

	return $products->found_posts;
}


add_action('init', 'ending_soon_auction_check_func');
function ending_soon_auction_check_func()
{
	// Cron Code Start
	$args = array(
		'post_type'          => 'product',
		'posts_per_page'     => '100',
		'tax_query'          => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'auction',
			),
		),
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'     => 'woo_ua_auction_has_started',
				'value' => '1',
			),
			array(
				'key'     => 'woo_ua_auction_closed',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => 'in_app_auction_sent_ending_soon',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	$the_query = new WP_Query($args);

	if ($the_query->have_posts()) {
		while ($the_query->have_posts()) :
			$the_query->the_post();

			global $product;

			$product_data = wc_get_product($the_query->post->ID);
			$now_timestamp = current_time("timestamp");

			$current_date = gmdate("Y-m-d H:i", strtotime('+5 hours'));
			$auction_expire_date = get_post_meta($the_query->post->ID, 'woo_ua_auction_end_date', true);

			$start_datetime = new DateTime($current_date);
			$diff = $start_datetime->diff(new DateTime($auction_expire_date));
			
			if ($diff->h <= 24) {

				$current_id = get_current_user_id();

				global $wpdb;
				$table_name = $wpdb->prefix . 'auction_notifications';
				$watchers = get_post_meta($the_query->post->ID, "woo_ua_auction_watch");
				$uwa_started = $product->is_uwa_live();
				$expire_date = date_i18n(get_option('date_format'),  strtotime($product->get_uwa_auctions_end_time())) . ' ' . date_i18n(get_option('time_format'),  strtotime($product->get_uwa_auctions_end_time()));
				$current_bid = (($uwa_started === TRUE && !empty($product->get_uwa_auction_current_bid())) ? wc_price($product->get_uwa_auction_current_bid()) : wc_price($product->get_uwa_auction_start_price()));
				$product_title = get_the_title($the_query->post->ID);
				$product_permalink = get_permalink($the_query->post->ID);
				$expired_content = "Auction <b> <a href='$product_permalink'> $product_title </a> </b> is going to expire at $expire_date. Current bid is $current_bid.";
				
				$title = 'auction_expire_soon';
				$content = $expired_content;
				$notification_icon = NULL;
				$auction_id = $the_query->post->ID;
				$log_id = NULL;

				$auc_loss = "SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE `auction_id`=" . $the_query->post->ID . " GROUP BY userid ORDER BY `bid` DESC";
				$loss_results = $wpdb->get_results($auc_loss);
				
				if (!empty($loss_results)) {
					foreach ($loss_results as $result) {
						array_push($watchers, $result->userid);
					}
				}
				$watchers = array_unique($watchers);
				
				foreach ($watchers as $watcher) {
					$wpdb->insert($table_name, array(
						'title' => $title,
						'content' => $content,
						'user_id' => $watcher,
						'auction_id' => $auction_id,
					)); 
				}
				
				add_post_meta($the_query->post->ID, 'in_app_auction_sent_ending_soon', $now_timestamp, true);
			}

		endwhile;

		wp_reset_postdata();
	}
	// Cron Code End
}



// Edia Media Code Start
add_action('wp_ajax_get_uploaded_images_urls', 'get_uploaded_images_urls_func');
add_action('wp_ajax_nopriv_get_uploaded_images_urls', 'get_uploaded_images_urls_func');
function get_uploaded_images_urls_func()
{
	$auction_id = isset($_POST['auction_id']) ? $_POST['auction_id'] : null;

	$auction_urls = [];
	$auction_urls['featured_videos'] = is_array(get_field('featured_videos', $auction_id)) ? get_field('featured_videos', $auction_id) : [];
	$auction_urls['exterior_photos'] = is_array(get_field('exterior_photos', $auction_id)) ? get_field('exterior_photos', $auction_id) : [];
	$auction_urls['interior_photos'] = is_array(get_field('interior_photos', $auction_id)) ? get_field('interior_photos', $auction_id) : [];
	$auction_urls['engine_photos'] = is_array(get_field('engine_photos', $auction_id)) ? get_field('engine_photos', $auction_id) : [];
	$auction_urls['undercarriage_photos'] = is_array(get_field('undercarriage_photos', $auction_id)) ? get_field('undercarriage_photos', $auction_id) : [];
	$auction_urls['other_photos'] = is_array(get_field('other_photos', $auction_id)) ? get_field('other_photos', $auction_id) : [];

	echo json_encode(['msg' => 'Urls Get Successfully.', 'auction_urls' => $auction_urls]);
	die();
}

// Remove Uploaded Images
add_action('wp_ajax_remove_uploaded_images', 'remove_uploaded_images_func');
add_action('wp_ajax_nopriv_remove_uploaded_images', 'remove_uploaded_images_func');
function remove_uploaded_images_func()
{
	$url = isset($_POST['url']) ? $_POST['url'] : null;
	if (!empty($url) && $url) {
		$attachment_id = attachment_url_to_postid($url);
		if ($attachment_id) {
			wp_delete_attachment($attachment_id, true);
			echo json_encode(['success' => true, 'msg' => 'Media removed.']);
		} else {
			echo json_encode(['success' => false, 'msg' => 'Something went wrong.']);
		}
	} else {
		echo json_encode(['success' => false, 'msg' => 'Media URL not provided']);
	}
	wp_die();
}

add_action('publish_product', 'send_product_publish_email_notification', 10, 2);

function send_product_publish_email_notification($post_id, $post)
{
	if ('product' !== $post->post_type) {
		return;
	}

	$seller_id = get_post_field('post_author', $post_id);

	$seller_email = get_the_author_meta('user_email', $seller_id);

	$product_title = get_the_title($post_id);
	$product_link = get_permalink($post_id);

	$headers = array('Content-Type: text/html; charset=UTF-8');

	$subject = 'Your Auction has been published!';

	$message = '<div style="max-width: 700px; margin-left: auto; margin-right: auto; border: 1px solid #F5F6F7; font-family: Arial;">';
	$message .= '<div style="padding: 40px 60px; display: block; width: 100%; box-sizing: border-box;">';
	$message .= '<div style="text-align: center; margin-bottom: 20px;"><a style="text-decoration: none; width: 200px;" href="https://vinhammer.com" target="_blank" rel="noopener">';
	$message .= '<img src="https://vinhammer.com/wp-content/uploads/2022/06/et-VH_logo.png" width="200px" height="auto" />';
	$message .= '</a></div>';
	$message .= '<div style="text-align: center;"><a style="display: inline-block; text-decoration: none; margin-right: 20px; vertical-align: middle;" href="https://www.facebook.com/vinhammer.auctions" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/06/et-fb-icon-red.png" height="20px" />';
	$message .= '</a>';
	$message .= '<a style="display: inline-block; text-decoration: none; margin-right: 20px; vertical-align: middle;" href="https://twitter.com/vin_hammer" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/06/et-twitter-icon-red.png" height="20px" />';
	$message .= '</a>';
	$message .= '<a style="display: inline-block; text-decoration: none; margin-right: 20px; vertical-align: middle;" href="https://www.instagram.com/vin_hammer/" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/07/et-instagram-icon-red.png" height="20px" />';
	$message .= '</a>';
	$message .= '<a style="display: inline-block; text-decoration: none; vertical-align: middle;" href="https://www.linkedin.com/company/vinhammer/" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/06/et-linkedin-icon-red.png" height="20px" />';
	$message .= '</a></div>';
	$message .= '</div>';
	$message .= '<div>';
	$message .= '<div style="background-color: #ec1b34; background-size: cover; background-position: center center; background-repeat: no-repeat; text-align: center; padding-top: 68px; padding-bottom: 68px;">';
	$message .= '<h1 style="color: #ffffff; margin: 0px; font-weight: bold;">Your auction is now live!</h1>';
	$message .= '</div>';
	$message .= '<div style="min-height: 300px; background-color: #ffffff; color: #0d0a19; padding: 50px 60px; font-size: 16px; font-weight: 400;">';
	$message .= '<span style="color: #333333;">Hello ' . $seller_email . ',</span><br><br style="color: #333333;" />Your auction for <strong>' . $product_title . '</strong> has been published and is now live on our website.<br>';
	$message .= '<br style="color: #333333;" /><span style="color: #333333;">You can view your auction and make any necessary updates by visiting the following link:<a href="' . $product_link . '">' . $product_link . '</a></span>';
	$message .= '</div>';
	$message .= '</div>';
	$message .= '<div style="background-color: #f5f6f7; color: #0d0a19; padding: 60px 60px; text-align: center;"><a style="display: block; margin-bottom: 20px; text-decoration: none;" href="https://vinhammer.com" target="_blank" rel="noopener">';
	$message .= '<img src="https://vinhammer.com/wp-content/uploads/2022/06/et-VH_logo.png" width="250px" height="auto" />';
	$message .= '</a>';
	$message .= '<a style="display: block; margin-bottom: 30px; color: #0d0a19; text-decoration: none; font-size: 18px; font-weight: bold;" href="mailto:support@vinhammer.com">support@vinhammer.com</a>';
	$message .= '<div style="text-align: center; display: flex; justify-content: center; width: 100%;">';
	$message .= '<div style="width: 100%; text-align: center; display: inline-block; flex: 0 0 100%; max-width: 100%;"><a style="display: inline-block; text-decoration: none; vertical-align: middle; margin-right: 20px;" href="https://www.facebook.com/vinhammer.auctions" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/06/et-fb-icon-gray.png" width="auto" height="24px" /></a><a style="display: inline-block; text-decoration: none; vertical-align: middle; margin-right: 20px;" href="https://twitter.com/vin_hammer" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/06/et-twitter-icon-gray.png" width="auto" height="24px" /></a><a style="display: inline-block; text-decoration: none; vertical-align: middle; margin-right: 20px;" href="https://www.instagram.com/vin_hammer/" target="_blank" rel="noopener">';
	$message .= '<img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/07/et-instagram-icon-gray.png" width="auto" height="24px" /></a><a style="display: inline-block; text-decoration: none; vertical-align: middle;" href="https://www.linkedin.com/company/vinhammer/" target="_blank" rel="noopener">';
	$message .= '<span style="vertical-align: middle;"><img style="display: block;" src="https://vinhammer.com/wp-content/uploads/2022/06/et-linkedin-icon-gray.png" width="auto" height="24px" /></span></a></div>';
	$message .= '</div>';
	$message .= '</div>';
	$message .= '</div>';

	// In-App Notification Start
	global $wpdb;
	$table_name = $wpdb->prefix . 'auction_notifications';
	$auction_id = $post_id;
	$user_id = $seller_id;
	$title = 'auction_published';
	$content = 'Your <a href="' . $product_link . '"><strong>' . $product_title . '</strong></a> auction has been published by VHS';
	$notification_icon = NULL;
	$wpdb->insert($table_name, array(
		'title' => $title,
		'content' => $content,
		'user_id' => $user_id,
		'auction_id' => $auction_id,
	));
	// In-App Notification End

	wp_mail($seller_email, $subject, $message, $headers);
}

// Anti sniping notification
add_action('uwa_extend_auction_time', 'uwa_extend_auction_time_antisnipping_notification', 10, 1);
function uwa_extend_auction_time_antisnipping_notification($auID)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'auction_notifications';
	$watchers = get_post_meta($auID, "woo_ua_auction_watch");

	$title = 'auction_antisniping';
	$content = 'Auction time has been extended for';
	$notification_icon = NULL;
	$auction_id = $auID;
	$log_id = NULL;

	$auc_loss = "SELECT * FROM " . $wpdb->prefix . "woo_ua_auction_log WHERE `auction_id`=" . $auID . " GROUP BY userid ORDER BY `bid` DESC";
	$results = $wpdb->get_results($auc_loss);

	if (!empty($results)) {
		foreach ($results as $result) {
			array_push($watchers, $result->userid);
		}
	}
	$watchers = array_unique($watchers);

	foreach ($watchers as $watcher) {
		$wpdb->query("INSERT INTO $table_name(title, content, notification_icon, user_id, auction_id, log_id) VALUES('$title', '$content', '$notification_icon', '$watcher', '$auction_id', '$log_id')");
	}
	add_post_meta($the_query->post->ID, 'in_app_auction_sent_ending_soon', $now_timestamp, true);
}


function custom_um_authenticate($user, $username, $password)
{
	// Check if the username is a valid email address
	if (is_email($username)) {
		// Get the user with the specified email address
		$user = get_user_by('email', $username);

		// If the email address is not associated with any user, return an error
		if (!$user) {
			UM()->form()->errors = array();
			UM()->form()->add_error('useremail', __('Email is incorrect. Please try againnnnnnn.', 'ultimate-member'));
		}
	} else {
		// Get the user with the specified username
		$user = get_user_by('login', $username);

		// If the username is not associated with any user, return an error
		if (!$user) {
			UM()->form()->errors = array();
			UM()->form()->add_error('useremail', __('Username is incorrect. Please try again.', 'ultimate-member'));
			// return new WP_Error( 'invalid_username', __( 'Invalid username', 'um' ) );
		}
	}
	return $user;
}
add_filter('authenticate', 'custom_um_authenticate', 10, 3);
