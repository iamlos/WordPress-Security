<?php
/**
 * @package WP_SEC
 * @version 1.0
 */
/*
Plugin Name: WP Security by Juno_okyo
Plugin URI: http://junookyo.blogspot.com/
Description: Hide version and RSS Feeds of WordPress.
Author: Juno_okyo
Version: 1.0
Author URI: http://junookyo.blogspot.com/
*/

/**
 * Hide WordPress Version
 * 
 * File: wp-includes/general-template.php
 * Function: get_the_generator()
 * 
 * @author Juno_okyo <junookyo@gmail.com>
 */
$items = array('html', 'xhtml', 'atom', 'rss0', 'rdf', 'comment', 'export');

foreach ($items as $type) {
	add_filter('get_the_generator_' . $type, function() {}, 10, 0);
}

/**
 * Remove all feeds from WordPress
 * Author: Christopher Davis
 * Link: http://wordpress.stackexchange.com/a/47087
 */
add_action( 'wp_head', 'wpse33072_wp_head', 1 );

// Remove feed links from wp_head
function wpse33072_wp_head()
{
    remove_action( 'wp_head', 'feed_links', 2 );
    remove_action( 'wp_head', 'feed_links_extra', 3 );
}

foreach( array( 'rdf', 'rss', 'rss2', 'atom' ) as $feed )
{
    add_action( 'do_feed_' . $feed, 'wpse33072_remove_feeds', 1 );
}
unset( $feed );

// prefect actions from firing on feeds when the `do_feed` function is called
function wpse33072_remove_feeds()
{
    // redirect the feeds! don't just kill them
    wp_redirect( home_url(), 302 );
    exit();
}

add_action( 'init', 'wpse33072_kill_feed_endpoint', 99 );

// Remove the `feed` endpoint
function wpse33072_kill_feed_endpoint()
{
    // This is extremely brittle.
    // $wp_rewrite->feeds is public right now, but later versions of WP
    // might change that
    global $wp_rewrite;
    $wp_rewrite->feeds = array();
}

register_activation_hook( __FILE__, 'wpse33072_activation' );

// Activation hook
function wpse33072_activation()
{
    wpse33072_kill_feed_endpoint();
    flush_rewrite_rules();
}
