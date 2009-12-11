=== Meetup Feed2Post ===
Contributors: markparolisi
Tags: RSS, meetup
Requires at least: 2.7
Tested up to: 2.8.6

Fetches a Meetup.com Feed and creates it as a post.

== Description ==

This plugin will automatically update your WordPress site with calendar posts from your Meetup.com account.

Upon activation, a new category called 'Meetup' is created.
At a regular interval, currently once a day, this plugin fetches a group's Meetup.com RSS feed provided in the admin option panel and parses the data into a new WordPress post.
It stores a custom field called 'GUID' into the post with its value being the unique identifier of that particular RSS entry.
Every interval it will check the feed, update the previous posts and enter new ones as it finds them.

== Screenshots ==

== Installation ==

Requirements:

* PHP 5

Steps:

1. Upload `meetup_feed2post.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter the Meetup.com feed url into the newly created admin menu.

==Frequently Asked Questions ==

= Can I use this for feeds other than Meetup.com? =

Possibly. Duplicate checking and data entry is based on Meetup.com's XML structure, so your new feed has a matching structure on the GUID and description fields then it should work. Also, all new posts enter into a new post category called 'Meetup', so you'd have to change that. It's not an impossibility, so feel free to try, but don't blame me if you have 500 new duplicate posts, or your body content is the incorrect field. 
== Contact Info ==

Report problems to mark@markparolisi.com
