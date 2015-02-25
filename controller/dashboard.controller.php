<?php
require_once(dirname(__FILE__).'/push_to_front.controller.php');

$dashboardPTF = TWIG('wp_dashboard.twig.html', $TWIGdata, dirname(dirname(__FILE__)));
$TWIGdata['block_pre_messages'][] = $dashboardPTF;