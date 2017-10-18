<?php
require_once('WPOO/WPOO/Post.php');
require_once('WPOO/WPOO/Author.php');

define('COVER_DEFAULT', 'https://grafikk.ukm.no/placeholder/post_placeholder.png');
// COVER PHOTOS
$cover = get_option('UKMpush_to_front_cover');
if( !is_object( $cover ) ) {
	$cover = new stdClass();
}
if( !is_object( $cover->portrait ) ) {
	$cover->portrait = new stdClass();
	$cover->portrait->url = COVER_DEFAULT;
} else if( empty( $cover->portrait->url ) ) {
	$cover->portrait->url = COVER_DEFAULT;
}
if( !is_object( $cover->landscape ) ) {
	$cover->landscape = new stdClass();
	$cover->landscape->url = COVER_DEFAULT;
} else if( empty( $cover->landscape->url ) ) {
	$cover->landscape->url = COVER_DEFAULT;
}


// DO SAVE
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
	

	if( isset( $_POST['cover_portrait_url'] ) && isset( $_POST['cover_portrait_id'] ) ) {
		
		$image = wp_get_attachment_image_src( $_POST['cover_portrait_id'], 'large' );
		$url = is_array( $image ) ? $image[0] : $_POST['cover_portrait_url'];		
		$cover->portrait->url = $url;
		$cover->portrait->id = $_POST['cover_portrait_id'];
	}
	
	if( isset( $_POST['cover_landscape_url'] ) && isset( $_POST['cover_landscape_id'] ) ) {
		$image = wp_get_attachment_image_src( $_POST['cover_landscape_id'], 'large' );
		$url = is_array( $image ) ? $image[0] : $_POST['cover_landscape_url'];		
		$cover->landscape->url = $url;
		$cover->landscape->id = $_POST['cover_landscape_id'];
	}
	
	update_option('UKMpush_to_front_cover', $cover);
	
	do_action('UKMpush_to_front_generate_object');
}


// FETCH POSTS
$posts = query_posts( 'post_status=publish&posts_per_page=-1' );
global $post;
foreach( $posts as $key => $post ) {
	the_post();
	$TWIGdata['my_posts'][] = new WPOO_Post( $post );
}
// Load data for selectlist default value
for( $i=1; $i<4; $i++ ) {
	$TWIGdata['UKMpush_to_front_post'][$i] = (int) get_option('UKMpush_to_front_post_'.$i);
}


$TWIGdata['UKMpush_to_front_cover'] = $cover;
