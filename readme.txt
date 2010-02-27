=== Meetup Feed2Post ===
Contributors: Mark Parolisi
Donate link: http://markparolisi.com
Tags: meetup, meet up, rss
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: 1.5

Fetches a Meetup.com Feed and creates it as a post.
 
== Description ==

Upon activation, a new category called 'Meetup' is created.
At a regular interval, currently once a day, this plugin fetches a group's Meetup.com RSS feed and parses the data into a new WordPress post.
Every interval it will check the feed, update the previous posts and enter new ones as it finds them.

== Screenshots ==

-none

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

== Changelog ==

= 1.5 =

Added the option to change the author assignment. 
Bug Fixes

== Upgrade Notice ==

= 1.5 =

Added the option to change the author assignment.Bug Fixes.

