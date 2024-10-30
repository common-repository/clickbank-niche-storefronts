function cns_loadScript(url, callback)
{
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;
    if (typeof callback != 'undefined') {
        // bind the event to the callback function 
        // there are several events for cross browser compatibility
        script.onreadystatechange = callback;
        script.onload = callback;
    }
    
    // adding the script tag to the head as suggested before
    var head = document.getElementsByTagName('head')[0];
    // fire the loading
    head.appendChild(script);
}

function cns_imagePreviewLoad()
{
    if (typeof cns_imagePreview == 'undefined') {
        cns_loadScript(cns_pluginURL+'/image_preview.js');
    }
}


function cns_show_page(user_id, niche, page)

{
   // alert(niche+ user_id+ page);
    
    //console.log(cns_pluginURL+'/ajax_request.php'
    //    + '?user_id='+user_id
    //    + '&niche='+niche
     //   + '&page='+page);
        
    page_top = jQuery('#cns_product_list').offset().top - 10;
    if (page_top < 0) page_top = 0;
    
    jQuery('#cns_product_list')
        .css({opacity: 0.2});
	
    jQuery('#cns_loading_label').css({
        display: 'inline',
        top: jQuery(window).height()/2 - jQuery('#cns_loading_label').height()/2,
        left: jQuery(document).width()/2 - jQuery('#cns_loading_label').width()/2}); 
   
	// This does the ajax request (The Call).
    jQuery.ajax({
        url: cbpro_niche_paging_ajax_object.ajax_url, 
		
        data: {
            'action':'cs_niche_pagination_ajax_request', // This is our PHP function below
			'cbpro_niche_nonce' : 'cbpro_niche_paging_ajax_object.global_cbpro_niche_nonce',
            'niche': niche,
        	'user_id': user_id,
        	'page': page
        },
        success:function(data) {
						jQuery('#cns_loading_label').css({display: 'none'});
						jQuery('#cns_product_list')
							.html(data)
							.css({opacity: 1});
						cns_imagePreview(); // Enable preview function

						FB.XFBML.parse(jQuery('#cns_product_list').get(0));
						twttr.widgets.load(jQuery('#cns_product_list').get(0));
        },  
        error: function (jQXHR, textStatus, errorThrown) {
			alert("An error occurred whilst trying to contact the server: " + jQXHR.status + " " + textStatus + " " + errorThrown);
		}
    });   
	jQuery('html, body').animate({scrollTop: page_top}, 1000);
}



/*
function cns_show_page(user_id, niche, page)

{
    //alert('gg');
    
    //console.log(cns_pluginURL+'/ajax_request.php'
    //    + '?user_id='+user_id
    //    + '&niche='+niche
     //   + '&page='+page);
        
    page_top = jQuery('#cns_product_list').offset().top - 10;
    if (page_top < 0) page_top = 0;
    
    jQuery('#cns_product_list')
        .css({opacity: 0.2});
    jQuery('#cns_loading_label').css({
        display: 'inline',
        top: jQuery(window).height()/2 - jQuery('#cns_loading_label').height()/2,
        left: jQuery(document).width()/2 - jQuery('#cns_loading_label').width()/2});
    
    jQuery.get(
        cns_pluginURL+'/ajax_request.php'
        + '?user_id='+user_id
        + '&niche='+niche
        + '&page='+page,
        function(data) {
            jQuery('#cns_loading_label').css({display: 'none'});
            jQuery('#cns_product_list')
                .html(data)
                .css({opacity: 1});
            cns_imagePreview(); // Enable preview function
            
            FB.XFBML.parse(jQuery('#cns_product_list').get(0));
            twttr.widgets.load(jQuery('#cns_product_list').get(0));
        });
    
    // Scroll to the top of the list
    jQuery('html, body').animate({scrollTop: page_top}, 1000);
}
*/



var cns_pluginURL = jQuery("script[src]")
    .last()
    .attr("src").split('?')[0].split('/').slice(0, -1).join('/');

if (typeof jQuery == 'undefined') {
    cns_loadScript('http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js',
        cns_imagePreviewLoad);
} else {
    cns_imagePreviewLoad();
}

