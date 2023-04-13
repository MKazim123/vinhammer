jQuery(function($){
    $(document).ready(function(){
		
		$(".wpd-login-to-comment").html(`Please <a href="${site_url}/sign-in">login</a> to comment`);
		
        // change table text from product to listing start
        $(".toptable:nth-child(2)").text("Listing");

        // Shayan Code Start
        if($("#gform_fields_13").length > 0){
            let auction_id = $("#input_13_130").val();
            let formdata = new FormData();
            formdata.append("action", "get_uploaded_images_urls");
            formdata.append("auction_id", auction_id);
            jQuery.ajax({
                type: "post",
                data: formdata,
                url: opt.ajaxUrl,
                success: function(msg) {
                    msg = JSON.parse(msg)
                    const urls = msg.auction_urls;
                    
                    let auction_medias = Object.keys(urls);
                    auction_medias.forEach(res => {
                    let field_id = 98;
                    switch(res){
                        case 'interior_photos':
                            field_id = 102;
                        break;
                        case 'exterior_photos':
                            field_id = 104;
                        break;
                        case 'engine_photos':
                            field_id = 106;
                        break;
                        case 'undercarriage_photos':
                            field_id = 108;
                        break;
                        case 'other_photos':
                            field_id = 110;
                        break;
                    }
                    const $mediaContainer = jQuery(`#gform_multifile_upload_13_${field_id} .gpfup`);
                    
                    urls[res].forEach(url => {
                        let media_html = `<img src="${url}">`;
                        if(res == 'featured_videos'){
                            url = url.url;
                            media_html = `<svg width="48" height="48" version="1.1" viewBox="0 0 100 100" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M82.4,25.6l-20-20C62,5.2,61.5,5,61,5H23c-3.3,0-6,2.7-6,6v78c0,3.3,2.7,6,6,6h54c3.3,0,6-2.7,6-6V27  C83,26.5,82.8,26,82.4,25.6z M63,11.8L76.2,25H65c-1.1,0-2-0.9-2-2V11.8z M77,91H23c-1.1,0-2-0.9-2-2V11c0-1.1,0.9-2,2-2h36v14  c0,3.3,2.7,6,6,6h14v60C79,90.1,78.1,91,77,91z"></path></svg>`;
                        }

                        let index = url.lastIndexOf("/") + 1;
                        let image_name = url.substr(index);
                        let html = `<ul class="gpfup__files">
                                        <li class="gpfup__file">
                                            <div class="gpfup__preview">
                                                ${media_html}
                                            </div>
                                            <div class="gpfup__file-info">
                                                <div class="gpfup__filename">${image_name}</div>
                                            </div>
                                            <div class="gpfup__file-actions">
                                                <button class="gpfup__delete remove_auction_media" data-mediaurl="${url}">
                                                    <svg width="100%"
                                                        height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 16 16" role="img" aria-hidden="true"
                                                        focusable="false">
                                                        <path
                                                            d="M11.55,1.65,7.42,5.78,11.55,9.9,9.9,11.55,5.77,7.43,1.66,11.55,0,9.89,4.12,5.78,0,1.66,1.66,0,5.77,4.12,9.9,0Z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </li>
                                    </ul>`;
                        $mediaContainer.prepend(html);
                    });
                    })
                    // For Other Photos

                    ajaxSuccess = true;
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        }

        $(document).on('click', '.remove_auction_media', function(e) {
            e.preventDefault();
            var ___this = $(this);
            ___this.parent().parent().parent().remove();
            let auction_url = ___this.data('mediaurl');
            let formdata = new FormData();
            formdata.append("action", "remove_uploaded_images");
            formdata.append("url", auction_url);
            jQuery.ajax({
                type: "post",
                data: formdata,
                url: opt.ajaxUrl,
                success: function(response) {
                    response = JSON.parse(response);
                    ajaxSuccess = true;
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        });
        // Shayan Code End

        $(document).on("change",".fep-attachment-field-input", function(){
            let input = $(this);
            console.log('1',input);
			let file_path = $(this).val().slice(($(this).val().lastIndexOf(".") - 1 >>> 0) + 2);
            if (input[0].files && input[0].files[0]) {
                console.log('2',input);
                var reader = new FileReader();
                reader.onload = function (e) {
					console.log(e.target);
                    input.next().show()
					if(file_path == 'jpg' || file_path == 'jpeg' || file_path == 'png' || file_path == 'svg'){
					   input.after(`<img src="${e.target.result}" class="fep-attachment-preview-custom"/>`);
					}else{
					   input.after(`<img src="${site_url}/wp-content/uploads/2023/04/document.png" class="fep-attachment-preview-custom" style="border-radius: 33%;"/>`);
					}
                    
                    input.hide();
                };
                reader.readAsDataURL(input[0].files[0]);
            }
        })

        $(document).on('click','.clear_filter', function(){
            var u = new URL(window.location.href);
            u.hash = ''
            u.search = ''
            window.location.href = u.toString();
        })

        // Range script Start
        updateView = function () {
            if (jQuery(this).hasClass('min-range')) {
                jQuery(this).prev().html(`${this.value}`);
                console.log(jQuery(this).parent().find('.incl-range'));
            } else {
                jQuery(this).next().html(`${this.value}`);
            }
        };
    
        jQuery('input[type="range"]').each(function() {
            updateView.call(this);
            jQuery(this).on('mouseup', function() {
                this.blur();
            }).on('mousedown input', function () {
                updateView.call(this);
            });
        });
    
        // Range script End
        jQuery(document).on('change', '.filter_change', function(){
            $('#filter-form').trigger('submit');
        })
        jQuery(document).on('change', '#sort', function(){
            $('#auction-search-form').trigger('submit');
        })

        jQuery(document).on('submit', '#auction-search-form', function(e){
            e.preventDefault();
            jQuery('.all_products').html("<h3>Please Wait...</h3>");
            jQuery('.pagination-wrap').css('display', 'none');
            jQuery('.total_results').css('display', 'none');
            let auction_search       = $('[name="auction_search"]').val();
            auction_search           = JSON.stringify(auction_search);
            let expired              = false;
            
            let sort       = $('#sort').val();
            sort           = JSON.stringify(sort);

            if($('[name="expired"]').length > 0){
                expired = true;
            }

            let __url = window.location.href;
            let querystring = `?auction_search=${auction_search}&sort=${sort}&filter_request=true&expired=${expired}`;
            let currentUrl = res = __url.substring(0, __url.indexOf("?")) + querystring;
            window.location.href = currentUrl;

            // let formdata = new FormData();
            // formdata.append("action", "product_filters_action");
            // formdata.append("auction_search", auction_search);
            // formdata.append("sort", sort);
            // formdata.append("filter_request", true);
            // formdata.append("expired", expired);
            // jQuery.ajax({
            //     type: "post",
            //     data: formdata,
            //     // dataType:"json",
            //     url: opt.ajaxUrl,
            //     success: function (msg) {
            //         msg = JSON.parse(msg);
            //         jQuery('.product_listing').html(msg.html);
            //         ajaxSuccess = true;
            //     },
            //     cache: false,
            //     contentType: false,
            //     processData: false,
            // });
            
        })
        jQuery(document).on('submit', '#filter-form', function(e){
            e.preventDefault();
            jQuery('.all_products').html("<h3>Please Wait...</h3>");
            jQuery('.pagination-wrap').css('display', 'none');
            jQuery('.total_results').css('display', 'none');
            
            let body_style   = [];
            let car_transmission = [];
            let min_year             = $('[name="min_year"]').val();
            let max_year             = $('[name="max_year"]').val();
            let expired              = false;
            if($('[name="expired"]').length > 0){
                expired = true;
            }
           
            jQuery("input:checkbox[name=body_style]:checked").each(function(){
                body_style.push(jQuery(this).val());
            });
            jQuery("input:checkbox[name=car_transmission]:checked").each(function(){
                car_transmission.push(jQuery(this).val());
            });

            body_style              = JSON.stringify(body_style);
            car_transmission        = JSON.stringify(car_transmission);
            min_year                = JSON.stringify(min_year);
            max_year                = JSON.stringify(max_year);

            let __url = window.location.href;
            let querystring = `?body_style=${body_style}&car_transmission=${car_transmission}&min_year=${min_year}&max_year=${max_year}&filter_request=true&expired=${expired}`;
            let currentUrl = res = __url.substring(0, __url.indexOf("?")) + querystring;
            window.location.href = currentUrl;

            // let formdata = new FormData();
            // formdata.append("action", "product_filters_action");
            // formdata.append("body_style", body_style);
            // formdata.append("car_transmission", car_transmission);
            // // formdata.append("mileage", mileage);
            // formdata.append("min_year", min_year);
            // formdata.append("max_year", max_year);
            // // formdata.append("min_price", min_price);
            // // formdata.append("max_price", max_price);
            // formdata.append("filter_request", true);
            // formdata.append("expired", expired);
            // jQuery.ajax({
            //     type: "post",
            //     data: formdata,
            //     // dataType:"json",
            //     url: opt.ajaxUrl,
            //     success: function (msg) {
            //         msg = JSON.parse(msg);
            //         jQuery('.product_listing').html(msg.html);
            //         ajaxSuccess = true;
            //     },
            //     cache: false,
            //     contentType: false,
            //     processData: false,
            // });
        });
        jQuery(document).on('submit', '#home-filter-form', function(e){
            e.preventDefault();
            
            let body_style   = [];
            let car_transmission = [];
            let min_year             = $('[name="min_year"]').val();
            let max_year             = $('[name="min_year"] option:last-child').val();
            let expired              = false;
            if($('[name="expired"]').length > 0){
                expired = true;
            }
           
            jQuery("select[name=body_style] option:selected").each(function(){
                body_style.push(jQuery(this).val());
            });
            jQuery("select[name=car_transmission] option:selected").each(function(){
                car_transmission.push(jQuery(this).val());
            });
            body_style              = JSON.stringify(body_style);
            car_transmission        = JSON.stringify(car_transmission);
            min_year                = JSON.stringify(min_year);
            max_year                = JSON.stringify(max_year);
            
            let querystring = `?body_style=${body_style}&car_transmission=${car_transmission}&min_year=${min_year}&max_year=${max_year}&filter_request=true&expired=${expired}`;
            let currentUrl = window.location.href +'/active-auctions/'+ querystring;
            window.location.href = currentUrl;
        });
        jQuery('.disabled').each(function(){
            $(this).find('input').attr('disabled','disabled');
            $(this).find('textarea').attr('disabled','disabled');
        });
        jQuery(document).on('click','.submit-edit-auction', function(){
            Swal.fire({
                title: 'Confirm Edit',
                html: `Are you sure you want to edit this submission?`,
                confirmButtonText: 'Edit Listing',
                showCancelButton: true,
                showCloseButton: true,
                focusConfirm: false,
                focusCancel: false,
                focusClose: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    jQuery('#gform_submit_button_13').trigger('click');
                }
            });
        });
        jQuery(document).on('click','.delete-btn', function(){
            const urlSearchParams = new URLSearchParams(window.location.search);
            const params = Object.fromEntries(urlSearchParams.entries());
            let auction_id = params.auction_id;
            if($(this).data('auctionid')){
                auction_id = $(this).data('auctionid')
            }
            Swal.fire({
                title: 'Confirm Delete',
                html: `Are you sure you want to delete this submission? 
                    <div class="d-flex gap-20 mt-2 warning-wrap">
                        <img src="${get_stylesheet_directory_uri}/images/warning.svg" class="warning-icon"/>
                        <div>
                            <p class="warning-title">Warning</p>
                            <p class="warning-text">This process cannot be undone.</p>
                        </div>
                    </div>
                    `,
                confirmButtonText: 'Delete Listing',
                showCancelButton: true,
                showCloseButton: true,
                focusConfirm: false,
                focusCancel: false,
                focusClose: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    let formdata = new FormData();
                    formdata.append("action", "auction_delete_listing");
                    formdata.append("auction_id", auction_id);
                    jQuery.ajax({
                        type: "post",
                        data: formdata,
                        // dataType:"json",
                        url: opt.ajaxUrl,
                        success: function (res) {
                            // res = JSON.parse(res);
                            // console.log(res);
                            window.location.href = site_url+'/my-account/orders/'
                        },
                        cache: false,
                        contentType: false,
                        processData: false,
                    });
                }
            });
        });

        jQuery('.um-form input[type="password"]').each(function(){
            jQuery(this).before('<i class="fa fa-eye pass-icon show"></i>');
            jQuery(this).parent().css('position', 'relative');
            jQuery(this).attr('style', 'padding-right: 30px !important');

        });
        jQuery(document).on('click','.um-form .fa', function(){
            let type = $(this).next().attr('type');
            if(type == 'password'){
                $(this).next().attr('type','text');
                $(this).removeClass('fa-eye')
                $(this).addClass('fa-eye-slash')
            }else{
                $(this).next().attr('type','password');
                $(this).addClass('fa-eye')
                $(this).removeClass('fa-eye-slash')
            }
        });

        jQuery(document).on('click','.uwa-watchlist-action', function(){
            let __this = $(this)
            __this.addClass('btn-loader');
            setTimeout(() => __this.removeClass('btn-loader'), 3000);
        });
        jQuery(document).on('change','#sort', function(){
            let sort_by_val = $(this).val();
            if(sort_by_val != ""){
                $(".sort-by-label").hide();
            }else{
                $(".sort-by-label").show();
            }
        });
        jQuery(document).on('change','.select-rect .select-wrapper select', function(){
            let select_filter = $(this).val();
            if(select_filter != ""){
                $(this).removeClass('empty');
                $(this).addClass('notempty');
            }else{
                $(this).addClass('empty');
                $(this).removeClass('notempty');
            }
        });
        jQuery('#um_field_177_terms_and_conditions .um-field-checkbox-option').append(`<a href="${site_url}/terms-of-use" style="margin-left: 5px"> Terms & Conditions</a>`);
        jQuery(document).on('change','#input_12_13 input', function(){
            let refer_val = $(this).val();
            console.log(refer_val)
            if(refer_val == "Yes"){
                $(".field-gray-wrapper.referred-by").css('display','flex');
            }else{
                $(".field-gray-wrapper.referred-by").hide();
            }
        })
    })
})