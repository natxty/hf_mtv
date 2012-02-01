/* Put this in another file too... */

Davis.App.prototype.customRoute = function(method, path, params, callback)  {
	var route	 		= this.route(method,'', null);
	route.path 			= path;
	route.paramNames 	= params;
	route.callback 		= callback;
}



var app = Davis(function () {

	/* Gallery Single Image View */
	this.customRoute('get',/^\/caldera-prototype\/([\w-\/]+)\/gallery\/([\w-\/]+)\/image\/([\w-]+)\/$/gi, ['slug', 'album_slug', 'image_slug'], function(req) {
		var data = {slug:req.params.slug, album_slug:req.params.album_slug, image_slug:req.params.image_slug, fullPath:req.fullPath};
		console.log(data);
		return;
		
		MTV.do_ajax('/ajax_gallery/', data, function(json) {			
			do_page(json.pages);
		});    
	});

	/* Gallery Album View */
	this.customRoute('get',/^\/caldera-prototype\/([\w-\/]+)\/gallery\/([\w-\/]+)\/$/gi, ['slug', 'album_slug'], function(req) {
		var data = {slug:req.params.slug, album_slug:req.params.album_slug, fullPath:req.fullPath};
		console.log(data);
		return;
				
		MTV.do_ajax('/ajax_gallery/', data, function(json) {			
			do_page(json.pages);
		});    
	});
	
	this.customRoute('get',/^\/caldera-prototype\/([\w-\/]+)\/$/gi, ['slug'], function(req) {
		var data = {slug:req.params.slug, fullPath:req.fullPath};
	
		MTV.do_ajax('/ajax_page/', data, function(json) {			
			do_page(json);
		});    
	});
	
	
	
});

var MustachePartials;

jQuery(function() {
	/* Get our templates from the server */
	MTV.do_ajax('/ajax_app_setup/', null, function(json) { 
		MustachePartials = json;
		/* We have our templates, start up the app... */
		app.start()
	});	
});

/*##### UTILITY FUNCTIONS (move these somewhere else eventually) ####*/

/**
 * do_page function.
 * 
 * Used inside of an ajax call where pages is an array of page posts 
 *
 * @access public
 * @param mixed json
 * @return void
 */
function do_page(pages) {
	while(jQuery('.page:not(.remove)').length > pages.length-1) {
		var $elem = jQuery('.page:not(.remove):last');
		$elem.addClass('remove');
	}
	
	jQuery.each(pages, function(index,page) {
		var page_id_selector = '#'+page.post_name;
		
		/* General check if this page already exists */
		if ( jQuery(page_id_selector).length ) {
			jQuery(page_id_selector).removeClass('remove');
			return;
		}
		
		/* Render our page template */
		var html = Mustache.to_html(MustachePartials['page'],page,MustachePartials);
		
		/* Check if there's already a page that's a sibling of this page */
		var sibling_selector = '.page[data-parent='+page.post_parent+']';
						
		if ( jQuery(sibling_selector).length ) {
			/* 
				If so, we want to position this page in the same place as that one
				so we get a smooth transition
			 */
			jQuery(sibling_selector).wrap('<div class="page-swap-container" style="position:relative"></div>');
			jQuery(sibling_selector).parent('.page-swap-container').append(html)
			
			var height	= jQuery(page_id_selector).height();
			var width	= jQuery(page_id_selector).width(); 
			
			jQuery(sibling_selector).parent('.page-swap-container').animate({
				height:height,
				width:width
			});				
			
			jQuery(page_id_selector).css({
				position:'absolute',
				top:0,
				left:0
			});
			
			var callback = function() {
				jQuery(this).unwrap();
				jQuery(this).css({
					position:'relative'
				});
			}
			
		} else {
			jQuery('body').append(html);
			var callback = function() {};
		}
		
		jQuery(page_id_selector).hide().fadeIn(callback);
	});
	
	/* Fade out and remove all items marked to be removed */
	jQuery('.page.remove').fadeOut(function() {
		jQuery(this).remove();
	});
}