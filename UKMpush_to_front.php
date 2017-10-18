<?php  
/* 
Plugin Name: UKM Push to Front
Plugin URI: http://www.ukm-norge.no
Description: Diverse muligheter for å pushe nyhetssaker opp til forsiden av UKM.no
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://www.ukm-norge.no
*/

if(is_admin()) {
	if( get_option('site_type') == 'fylke' && date("n") > 2 && date("n") < 5) {
		add_action('UKM_admin_menu', 'UKMpush_to_front_menu');
	}
#	add_action('network_admin_menu', 'UKMpush_to_front_menu_network');	
	add_action('UKMwp_dashboard_load_controllers', 'UKMpush_to_front_dash_hook');
	add_action('UKMpush_to_front_generate_object', 'UKMpush_to_front_generate_object');
}

// Regular menu
function UKMpush_to_front_menu() {
	UKM_add_menu_page('content','Push to Front', 'Push to Front', 'publish_posts', 'UKMpush_to_front','UKMpush_to_front', '//ico.ukm.no/bump-top-menu.png', 8);
	UKM_add_scripts_and_styles('UKMpush_to_front', 'UKMpush_to_front_scripts_and_styles' );
}

// Network admin menu
function UKMpush_to_front_menu_network() {
	$page = add_menu_page('Push to Front', 'Push to Front', 'publish_posts', 'UKMpush_to_front_network','UKMpush_to_front_network', '//ico.ukm.no/bump-top-menu.png', 500);
	add_action( 'admin_print_styles-' . $page, 'UKMvideo_scripts_and_styles' );
}


// GUI Regular admin
function UKMpush_to_front() {
	$TWIGdata = array('site_type' => get_option('site_type'));
	
	require_once(dirname(__FILE__).'/controller/push_to_front.controller.php');

	echo TWIG('push_to_front.twig.html', $TWIGdata, dirname(__FILE__));
}


// PRE-render and hook inn dashboard widget before GUI
function UKMpush_to_front_dash_hook( $TWIGdata ) {
	if( get_option('site_type') == 'fylke' ) {
		require_once(dirname( __FILE__ ) .'/controller/dashboard.controller.php');
	}
	return $TWIGdata;
}

function UKMpush_to_front_scripts_and_styles(){
	wp_enqueue_media();
	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
	wp_enqueue_style('UKMresources_tabs');
	wp_enqueue_script( 'UKMptf_js', plugin_dir_url( __FILE__ ) . 'UKMpush_to_front.js');
}




function UKMpush_to_front_generate_object() {
	if( get_option('site_type') != 'fylke' ) {
		return;
	}
	global $blog_id;
	$m = new monstring( get_option('pl_id') );
	$fylke = new stdClass();
	$fylke->ID = $m->g('pl_fylke');
	$fylke->title = $m->g('pl_name');
	$fylke->place = $m->g('pl_place');
	$fylke->blog_id = $blog_id;
	$fylke->link	= $m->g('link');
	$fylke->start = $m->g('pl_start');
	$fylke->stopp = $m->g('pl_stop');
	$fylke->uke = date('W', $fylke->start);
	
	$fylke->posts = get_option('UKMpush_to_front_post_array');
	
	$fylke->cover = get_option('UKMpush_to_front_cover');
	
	$fylke->live = new stdClass();
	$fylke->live->link = get_option('ukm_live_link');
	$fylke->live->embed = get_option('ukm_live_embedcode');
	$fylke->live->archive = "http://tv.".UKM_HOSTNAME."/fylke/".$m->info['url']."/".$m->g('season')."/";

	// Lagre fylke-objektet på hovedbloggen (hvor det skal vises)
	update_site_option('UKMpush_to_front_fylke_'. $fylke->ID, $fylke);
	
	// Lagre et array med fylker som har mønstring denne uka
	// For å få PTF til å funke i dev-mode, må vi lagre årstall, ikke sesong. Dev-mode tror vi er i 2014-sesongen by default.
	$year = $m->g('season');
	if( 'ukm.dev' == UKM_HOSTNAME ) {
		$year = date('Y');
	}
	$uke = get_site_option('UKMpush_to_front_uke_'. $year .'_'. (int)$fylke->uke);
	if( !$uke ) {
		$uke = array();
	}
	$uke[] = $fylke->ID;
	
	$uke = array_unique( $uke );
	
	update_site_option('UKMpush_to_front_uke_'. $year .'_'. (int)$fylke->uke, $uke);
}

function UKMpush_to_front_load_all_fm_data( $year, $week ) {
	$fylkesmonstringer = get_site_option('UKMpush_to_front_uke_'. $year .'_'. $week);
	$return_monstringer = array();
	if( is_array( $fylkesmonstringer ) ) {
		foreach( $fylkesmonstringer as $fm ) {
			$return_monstringer[] = UKMpush_to_front_load_fm_data( $fm );
		}
	}
	return $return_monstringer;
}

function UKMpush_to_front_load_fm_data( $fm ) {
	$fylke = get_site_option('UKMpush_to_front_fylke_'.$fm);
	// Load all posts
	if( is_array( $fylke->posts ) ) {
		switch_to_blog( $fylke->blog_id );
		foreach( $fylke->posts as $post_id ) {
			$post	= get_post( $post_id );
			@$WPOO_post	= new WPOO_Post( $post );
	
			$fylke->postdata[] = $WPOO_post;
		}
		restore_current_blog();
	}
	
	// is live now?
	$fylke->live->now = false;
	$perioder = get_blog_option($fylke->blog_id, 'ukm_hendelser_perioder');
	$embedcode = get_blog_option($fylke->blog_id, 'ukm_live_embedcode');
	if( $embedcode && is_array( $perioder ) ) {
		foreach( $perioder as $p ) {
			if( $p->start < time() && $p->stop > time() ) {
				$fylke->live->now = true;
				break;
			}
		}
	}
	
#	if( time() > $fylke->stop ) {
#		$fylke->live->link = false;
#		$fylke->live->now = false;
#	}
	
	// Does it have cover photos?
	if( empty ( $fylke->cover->portrait->url ) ) {
		$fylke->cover->portrait = 'https://grafikk.ukm.no/placeholder/fylkesmonstring_on_front/default_portrait.jpg';
	}
	if( empty ( $fylke->cover->landscape->url ) ) {
		$fylke->cover->landscape = 'https://grafikk.ukm.no/placeholder/fylkesmonstring_on_front/default_landscape.jpg';
	}
	
	return $fylke;
}
