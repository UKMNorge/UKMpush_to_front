jQuery(document).on('click', '#insertMedia', function() {
    if ( typeof wp !== 'undefined' && wp.media && wp.media.editor ) {
	    var target = jQuery(this).attr('data-target');
        wp.media.editor.open( '#UKMptfSelectImage'+target );
		
		original_send = wp.media.editor.send.attachment;
		wp.media.editor.send.attachment = function( a, b) {
			setImage(target,b)
		};
		window.original_send_to_editor = window.send_to_editor; 
	}
});


function setImage(target, b) {
//	console.log(b);
	jQuery('#'+target+'_id').val( b.id );
	jQuery('#'+target+'_url').val( b.url );
	jQuery('#'+target+'_image').attr('src', b.url );
}