<?php
require_once('WPOO/WPOO/Post.php');
require_once('WPOO/WPOO/Author.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	if( isset( $_POST['post_1'] ) && isset( $_POST['post_2'] ) && isset( $_POST['post_3'] ) ) {
		for( $i=1; $i<4; $i++ ) {
			update_option('UKMpush_to_front_post_'.$i, $_POST['post_'.$i]);
			if( !empty( $_POST['post_'.$i] ) && (int) $_POST['post_'.$i] != 0 ) {
				$save[] = $_POST['post_'.$i].',';
			}
		}
		update_option('UKMpush_to_front_post_array', $save);
	}
	
	if( isset( $_POST['cover_portrait'] ) ) {
		update_option('UKMpush_to_front_cover_portrait', $_POST['cover_portrait']);
	}
	
	if( isset( $_POST['cover_landscape'] ) ) {
		update_option('UKMpush_to_front_cover_landscape', $_POST['cover_landscape']);
	}
	
	do_action('UKMpush_to_front_generate_object');
}


$posts = query_posts( 'post_status=publish' );

global $post;
foreach( $posts as $key => $post ) {
	the_post();
	$TWIGdata['my_posts'][] = new WPOO_Post( $post );
}

// Load data for selectlist default value
for( $i=1; $i<4; $i++ ) {
	$TWIGdata['UKMpush_to_front_post'][$i] = (int) get_option('UKMpush_to_front_post_'.$i);
}
$TWIGdata['UKMpush_to_front_cover_portrait'] = get_option('UKMpush_to_front_cover_portrait');
$TWIGdata['UKMpush_to_front_cover_landscape'] = get_option('UKMpush_to_front_cover_landscape');