<?php
/*
Plugin Name: Meetup Feed2Post
Plugin URI: 
Description: Grabs Meetup.com RSS and creates it as a post
Version:  1.5
Author:  Mark Parolisi
Author URI: http://markparolisi.com
*/

/*  Copyright 2009  Mark Parolisi  (email : mark@markparolisi.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('MEETUP_FEEDURL_OPT','meetup_feedurl');
define('MEETUP_AUTHOR_OPT', 'meetup_author');


register_activation_hook(__FILE__, 'meetup_activation');
add_action('meetup_schedule_event', 'meetup_insert_function');
add_action('admin_menu', 'add_meetup_menu');
add_action('admin_notices', 'activation_notice');

// Upon activation, schedule RSS fetching, create new category for posts, and add a DB field to store our url
function meetup_activation() {
    if(!has_action('meetup_schedule_event')){
        wp_schedule_event(time(), 'daily', 'meetup_schedule_event');
    }
    wp_create_category("meetup");
    add_option(MEETUP_FEEDURL_OPT, 'meetup feed URL');
    add_option(MEETUP_AUTHOR_OPT, '1');
}

//Alert Users to configure the plugin
function activation_notice() {
    if(get_option(MEETUP_FEEDURL_OPT) == 'meetup feed URL' || get_option(MEETUP_FEEDURL_OPT) == ''){
        echo '<div class="error fade"><p><strong>Please add your Meetup.com feed url to the admin page of this plugin located under the Settings category</strong></p></div>';
    }
}

//Create admin panel and new category for our new posts
function add_meetup_menu() {
    add_submenu_page('options-general.php', 'Meetup Feed2Post', 'Meetup Feed2Post', 8, __FILE__, 'meetup_admin');
}

//Admin Panel 
function meetup_admin() {
    //Updates the FEED URL Option
    if( isset($_POST["update"]) ){
        remove_action('admin_notices', 'activation_notice');
        $feedurl        = esc_url_raw($_POST["feedurl"]);
        $meetup_author  = $_POST["user"];
        update_option(MEETUP_FEEDURL_OPT, $feedurl );
        update_option(MEETUP_AUTHOR_OPT, $meetup_author);
        meetup_insert_function(); //running our insert/update method on settings update
        echo "<p><div id='message' class='updated'>
    	<p><strong>Your settings have been updated</strong></p>
    	</div></p>";
    }
    $current_feedurl = esc_url(get_option(MEETUP_FEEDURL_OPT));
    $current_author = get_option(MEETUP_AUTHOR_OPT);
    echo '
		<div class="wrap">
			<h2>Configure your Meetup.com options</h2>
			<p><em>example: http://meetup.orlandophp.org/calendar/rss/The+Orlando+PHP+Meetup+Group</em></p>
			<form method="POST" action="">
			<label>RSS Feed URL</label>
			<input type="text" name="feedurl" size="60" value="'.$current_feedurl,'"><br />
			<label>Assign to which author? </label>';
    wp_dropdown_users('selected='.$current_author);
    echo'	<br />
			<input type="submit" name="update" value="Update Options" class="button-primary" />
			</form>	
			</div>';

}

//Add or Update Posts
function meetup_insert_function() {
    global $wpdb;
    $feedurl        = get_option(MEETUP_FEEDURL_OPT);
    $current_author = get_option(MEETUP_AUTHOR_OPT);
    $category_id    = get_cat_id('meetup');
    //loop through the current posts and store our custom meta GUID's into an array for dupe checking
    $guid_r = array();
    $existing_metas = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'meetup_guid'");
    foreach($existing_metas as $existing_meta){
        $guid = $existing_meta->meta_value;
        array_push($guid_r, $guid);
    }
    //Create Object from Feed
    $meetup_rss = new SimpleXMLElement($feedurl, NULL, TRUE);
    //loop through each event from the feed
    foreach($meetup_rss->xpath('channel/item') as $item){
        //If we find a GUID match in our array, update the post
        if(in_array($item->guid, $guid_r, false)){
            $rss_guid           = $item->guid;
            $rss_description    = $item->description;
            $update_post_id     = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_value='$rss_guid'"));
            $updated_info       = array();
            $updated_info['ID']             = $update_post_id;
            $updated_info['post_author']    = $current_author;
            $updated_info['post_content']   = $rss_description;
            wp_update_post($updated_info);
        } else{ //make the new post
            $post = array();
            $post['post_title']     = $item->title;
            $post['post_content']   = $item->description;
            $post['post_status']    = 'publish';
            $post['post_author']    = $current_author;
            $post['post_category']  = array($category_id);
            $postID = wp_insert_post($post);
            //adding a unique custom meta field to check against duplicates
            add_post_meta($postID, 'meetup_guid', $item->guid);
        }
    }
}

//Stop scheduling events and remove Meetup Feed URL option on uninstall
register_deactivation_hook(__FILE__, 'meetup_deactivation');
function meetup_deactivation() {
    if(has_action('meetup_schedule_event')){
        wp_clear_scheduled_hook('meetup_schedule_event');
    }
    delete_option(MEETUP_FEEDURL_OPT);
}