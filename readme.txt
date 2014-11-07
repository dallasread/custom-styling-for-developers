=== Plugin Name ===
Contributors: itsalaska
Donate link: 
Tags: css, js, jq, update, lost, over-written, overwritten, hosed, theme, plugin, style, stylesheet, scripting, script, independant, custom, styling, for, developers
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin simply holds custom CSS and JS/JQ that's independent from everything else.

== Description ==

Ever want a place where you can put your custom CSS/JS/JQ that would never get over-written? That's exactly what this plugin does. Even if your theme has a designated area for saving custom CSS/JS/JQ, some other reason you should consider this plugin are:

 * Input areas allow for tab indentation.
 * Doesn't force you to pay for anything, doesn't ask for donations, and doesn't generate any "nags". User-experience comes first.
 * Ability to save a second, minified version of your CSS/JS/JQ that's only loaded by the browser and doesn't replace your human-readable code.
 * Ability to enable Browser caching. This means that the user's browser will always pull their copy of this CSS/JS/JQ file from its cache instead of asking the server each time.
 * The dynamically-generated filename includes a timestamp of the last time the code was updated. This is done so that updated code is always displayed immediately, even with browser caching enabled.
 * Allows for an alternative domain so that you may use a CDN or Cookieless domain.
 * Uses PHP namespaces to ensure that there are no plugin conflicts.
 
I also want to give a special "Thank You" to Joe Scylla (for creating an open-source CSS minifier in PHP) and Robert Hafner (for creating an open-source JS minifier in PHP).

== Installation ==

Via WordPress:

1. From your website, go to "add new plugins".
1. Find this plugin, and click install.
1. Activate this plugin.

Via FTP:

1. Download a copy of this plugin from WordPress.org.
1. Unzip the zip file
1. Upload the unzipped directory to your website's plugin directory (/wp-content/plugins/ is the default).
1. Log into your WordPress site.
1. Activate this plugin.

== Frequently Asked Questions ==

Q. Will you support language localization in the future?

A. Yes. The only reason I don't already is because I don't know how. If you know how and have the time, please send me an email.


Q. Can you add a feature that gives me more than one text area for my CSS/JS/JQ so I can organize it better?

A. No. I've put a lot of thought into this and have decided that if your code is unorganized, adding multiple textareas will not help organize it. Use comments to keep your code maintainable.

== Screenshots ==

1. CSS page.
2. JS/JQ page.
3. Setting page. Note that the CDN options on are blank and disabled by default.

== Changelog ==

= 1.0.2 =
 * Fixed an issue where activating this plugin with other plugins throws an error.

= 1.0.1 =
 * Fixed the permalinks issue. Permalinks no longer need to be refreshed after installing this plugin.

= 1.0 =
 * Initial Launch
 * Language Localization is a big to-do. Will do when I figure out how and find the time.

== Upgrade Notice ==

None