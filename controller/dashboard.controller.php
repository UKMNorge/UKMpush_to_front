<?php
require_once('UKM/monstring.class.php');
$monstring = new monstring( get_option('pl_id') );

$show_box = $monstring->g('pl_start') - (14*24*3600);

if( time() > $show_box && !$monstring->ferdig() ) {
	require_once(dirname(__FILE__).'/push_to_front.controller.php');
	
	$dashboardPTF = TWIG('wp_dashboard.twig.html', $TWIGdata, dirname(dirname(__FILE__)));
	$TWIGdata['block_pre_messages'][] = $dashboardPTF;
}