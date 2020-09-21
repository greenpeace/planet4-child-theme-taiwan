<?php
/**
 * Additional code for the child theme goes in here.
 *
 * @package P4CT
 */

require_once( __DIR__ . '/classes/class-p4ct-site.php' );

$services = [
	'P4CT_Lang',
	'P4CT_Metabox_Register',
	'P4CT_Custom_Post_Type_Register',
	'P4CT_AJAX_Handler',
	'P4CT_ElasticSearch',
	'P4CT_Shortcode',
];

new P4CT_Site( $services );

remove_action( 'do_feed_rss2', 'do_feed_rss2', 10 );

function taiwan_do_feed_rss2() {
	load_template( __DIR__ . '/rss.php' ); 
}

add_action( 'do_feed_rss2', 'taiwan_do_feed_rss2' );