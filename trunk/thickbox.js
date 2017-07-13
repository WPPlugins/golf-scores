jQuery(document).ready(function($) {
	// Iterate over each link in the sidebar without a thickbox class
	$('div#sidebar a:not([class*="thickbox"])').each( function() {
		// If the link has an image as a child
		if( $(this).has('img') )
			// Add the class
			$(this).addClass('thickbox');
	});
});