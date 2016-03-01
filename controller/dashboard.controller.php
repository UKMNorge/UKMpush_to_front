<?php
require_once('UKM/monstring.class.php');
$monstring = new monstring( get_option('pl_id') );

$show_box = $monstring->g('pl_start') - (14*24*3600);

if( time() > $show_box && !$monstring->ferdig() ) {
	require_once(dirname(__FILE__).'/push_to_front.controller.php');
	
	$dashboardPTF = TWIG('wp_dashboard.twig.html', $TWIGdata, dirname(dirname(__FILE__)));
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
	$TWIGdata['block_pre_messages'][] = $dashboardPTF;
}