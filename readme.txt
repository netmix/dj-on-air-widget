=== DJ On Air Widget ===
Contributors: kionae
Tags: dj, music, radio, scheduling
Requires at least: 3.2.0
Tested up to: 3.2.1
Stable tag: trunk

Sidebar widget that displays the name, avatar, and profile link of a user scheduled to be "on-air" during the current hour.

== Description ==

The DJ On-Air Widget adds a "Dj Shifts" field to user profiles that allows the to be scheduled for on-air shifts on an hourly basis and provides a sidebar widget that displays any user(s) scheduled for the current hour.

== Installation ==

1. Upload plugin .zip file to the `/wp-content/plugins/` directory and unzip.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. When editing users, you will now find a "DJ shifts" field at the bottom of the form.  Use this to set up your on-air schedule for any user.  
4. Add the DJ On-Air widget to your site's sidebar and set it's options. 

== Frequently Asked Questions ==

= How do I use the widget in my template or individual posts? =

The widget can be embedded into posts usings the following shortcode:

[dj-widget title="Current DJ On-Air" show_avatar="1" show_link="1" default_name="Some DJ"]

title = The title that will appear above the name(s) of the current DJ(s) - (optional)
show_avatar = Whether or not to display avatars, 1 for yes, 0 for no (optional - default is 0)
show_link = Whether or not to link to profile page, 1 for yes, 0 for no (optional - default is 0)
default_name = The name or text to display if no DJ is currently scheduled

To use it in a template file include the following PHP code:
<?php echo do_shortcode('[dj-widget title="Current DJ On-Air" show_avatar="1" show_link="1" default_name="Some DJ"]'); ?>

or
 
<?php
$args = array("title" => "Current DJ On-Air", "show_avatar" => 1, "show_link" => 1, "default_name" => "Some DJ");
echo dj_show_widget($args);
?>

= Can I set it so that only certain roles can add schedules to user accounts?  I don't want Subscribers to start adding themselves to the widget. =

Yes, as of version 0.2.  The options page is under the Settings tab.

= I don't like the way the sidebar widget is styled.  Can I change it? =

Yes.  Just edit the /dj-on-air/styles/djonair.css file and change whatever you want.

= How do I change the DJ's avatar? =

In the user profile.  It's the same avatar that's assigned to the user's account.

= Can I have more than one DJ on air during the same hour? =

Yes.  If two users are scheduled for the same hour on the same day, they will both show up in the widget.

== Changelog ==

= 0.2.2 =
* Added shortcode so that the widget can be used outside of the sidebar

= 0.2.1 =
* Fixed a bug that was causing problems with schedules before 10am

= 0.2 =
* Added the ability to limit addition/removal of DJ schedules by role.

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.2.2 =
* Added shortcode so that the widget can be used outside of the sidebar

= 0.2.1 =
* Fixed a bug that was causing problems with schedules before 10am

= 0.1 =
* Initial release
