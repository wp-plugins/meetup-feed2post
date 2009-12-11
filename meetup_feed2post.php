<?php
 /*
Plugin Name: Meetup Feed2Post
Plugin URI: 
Description: Grabs Meetup.com RSS and creates it as a post
Version:  1.1
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

//Setup Plugin...
register_activation_hook(__FILE__, 'rss_insert_activation');
add_action('daily_feed_event', 'rss_insert_function');
add_action('admin_menu', 'rss2post_menu');
add_action('admin_notices', 'activation_notice');
  


// Upon activation, schedule RSS fetching, create new category for posts, and add a DB field to store our url
function rss_insert_activation() {
	wp_schedule_event(time(), 'daily', 'daily_feed_event');
    wp_create_category("meetup");	
	add_option('rss2post_feedurl','','','');
}

//Alert Users to configure the plugin

function activation_notice(){
	if(!get_option(rss2post_feedurl)){
	echo '<div class="error fade"><p><strong>Please add your Meetup.com feed url to the admin page of this plugin located under the Settings category</strong></p></div>';
	}
	
}

//Create admin panel and new category for our new posts
function rss2post_menu(){
   add_options_page('rss2post_options', 'Meetup Feed2Post', 8, __FILE__, 'rss2post_options'); 
}


//Admin Panel 
function rss2post_options(){
	//Updates the FEED URL Option
	if( isset($_POST["submit"]) ):
	    remove_action('admin_notices', 'activation_notice');
		$feedurl = esc_url_raw($_POST["feedurl"]);
		update_option("rss2post_feedurl", $feedurl );
		rss_insert_function();
    	echo "<p><div id='message' class='updated'>
    	<p><strong>Your settings have been updated</strong></p>
    	</div></p>";
 	endif;
 	
	$feedurl = esc_url(get_option("rss2post_feedurl"));
	$self = $_SERVER['PHP SELF'];
	$plugin_file = plugin_basename(__FILE__);
	$optionsform = <<<optform
		<div class="wrap">
			<h2>Configure your Meetup.com options</h2>
			<p><em>example: http://meetup.orlandophp.org/calendar/rss/The+Orlando+PHP+Meetup+Group</em></p>
			<form method="post" action="$self?page=$plugin_file">
			<label>RSS Feed URL</label>
			<input type="text" name="feedurl" size="60" value=$feedurl><br />
			<input type="submit" name="submit" value="submit" />
			</form>	
			</div>
optform;

	echo $optionsform;
		
}

 
//Doing the Actual Work
function rss_insert_function(){
 	
	global $wpdb;

    //Declare Feed 
    $feedurl = get_option("rss2post_feedurl");

	//Create Object from Feed  
    $rss = new SimpleXMLElement($feedurl, null, true);
	
	//find our category's ID 
  	$category_id = get_cat_id('meetup'); 
 
    //loop through the posts store out custom meta GUID's into an array
 	$guid_r = array(); 
 	
 	$existing_meta = mysql_query("SELECT * FROM wp_postmeta WHERE meta_key = 'guid'");
	
	while($meta_exists = mysql_fetch_array($existing_meta)){

	  $guid = $meta_exists['meta_value']; 

      array_push($guid_r, $guid);
      
      unset($guid);
     };
 
  		 
  	//loop through each event from the feed
 	foreach($rss->xpath('channel/item') as $item)
 	{
 
 		//If we find a GUID match in our array, update the post
		if(in_array($item->guid, $guid_r, false)){
			
			$rss_guid = $item->guid;
			$rss_description = $item->description;
			
			$update_post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM wp_postmeta WHERE meta_value='$rss_guid'"));											
		
			$updated_info = array();
			$updated_info['ID'] = $update_post_id;
			$updated_info['post_content'] = $rss_description;
			
			wp_update_post($updated_info);
			
			unset($rss_guid);
			unset($rss_description);
			unset($update_post_id);
		
			break;
						 

		} else { //make the new post
		
			 $post = array();
			 $post['post_title'] = $item->title;
			 $post['post_content'] = $item->description;
 			 $post['post_status'] = 'publish';
             $post['post_author'] = 1;
		     $post['post_category'] = array($category_id);

 			 $postID = wp_insert_post($post);
 
 			//adding a unique custom meta field to check against duplicates
 			 add_post_meta($postID, guid, $item->guid); 
		
		}
		
		unset($item);		
 	}
 	
}
    
 
  //Stop scheduling events and remove custom option on uninstall
 
register_deactivation_hook(__FILE__, 'rss_insert_deactivation');

function rss_insert_deactivation() {
	wp_clear_scheduled_hook('daily_feed_event');
	delete_option("rss2post_feedurl");
}


?>
