$(document).ready(function() {

	// Simple Lightbox
	$('.lightbox').click(function(e) {
		e.preventDefault();
		var image_href = $(this).attr("href");
		if ($('#lightbox').length > 0) {
			$('#lightbox').html('<img src="' + image_href + '">');
			$('#lightbox').fadeIn(400);
		} else {
			var lightbox = 
			'<div id="lightbox">' +
				'<img src="' + image_href +'">' +
			'</div>';
			$('body').append(lightbox);
			$('#lightbox').fadeIn(400);
		}	
	});
	
	//Click anywhere to close lightbox window
	$('body').on('click', '#lightbox', function() {
		$('#lightbox').fadeOut(300);
	});
	
	// Mobile menu
	$("#menu-icon").click(function () {
		if($('#header ul').css('display')=='none'){
			$('#header ul').show();
		}else{
			$('#header ul').hide();
		}
	});
	
	// Albums link on a div
	$(".detailsquare-bottom").click(function() {
	  window.location = $(this).prev("a").attr("href");
	  return false;
	});
	
	// Hide
	$('.detailsquare').hide();
	$('.thumbnailsquare img').hide();
	$('.stream-box img').hide();
	$('#photo-box img').hide();
	$('#pagination').hide();
	
	// On image load
	$('.thumbnailsquare').imagesLoaded().progress( function( instance, image ) {
		$(image.img).fadeIn();
	});
	
	$('.stream-box').imagesLoaded().progress( function( instance, image ) {
		$(image.img).fadeIn();
	});
	
	$('#photo-box').imagesLoaded().progress( function( instance, image ) {
		$(image.img).fadeIn();
	});
	
	// After all images have loaded
	$('#main').imagesLoaded().always( function( instance ) {
		$('.detailsquare').fadeIn();
		$('#pagination').fadeIn();
	});

	var $container = $('#masonry');
	// hide initial items
	var $initialItems = $container.find('.item').hide();
  
	var $container = $container.masonry({
      // do not select initial items
      itemSelector: 'none',
      columnWidth: '.grid-sizer',
      gutter: '.gutter-sizer',
    })
    // set option
    .masonry( 'option', { itemSelector: '.item' } )
    .masonryImagesReveal( $initialItems );

    var $items = $('#images').find('.item');
    $container.masonryImagesReveal( $items );
});

// Reveals items iteratively after each item has loaded its images
$.fn.masonryImagesReveal = function( $items ) {
  var msnry = this.data('masonry');
  var itemSelector = msnry.options.itemSelector;
  // Hide by default
  $items.hide();
  // Append to container
  this.append( $items );
  $items.imagesLoaded().progress( function( imgLoad, image ) {
    // Get item
    var $item = $( image.img ).parents( itemSelector );
    // Un-hide item
    $item.show();
    // Masonry
    msnry.appended( $item );
  });
  return this;
};

