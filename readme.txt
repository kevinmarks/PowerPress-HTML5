=== Blubrry Powerpress Podcasting plugin ===
Contributors: Angelo Mandato, Blubrry.com
Tags: podcast, podcasting, itunes, enclosure, zune, iphone, audio, video, rss2, feed, player, media, rss
Requires at least: 2.5.0
Tested up to: 2.7.1
Stable tag: 0.6.3

Add podcasting support to your blog.

== Description ==
The Blubrry Powerpress Podcast Plugin has all of the essential features needed to provide podcasting support in a Wordpress blog.

The Blubrry Powerpress interface allows you to easily add/modify/remove podcast episodes from blog posts and includes a simple on-line media player, iTunes compatibile RSS feed tags, ability to upload cover art images, ping iTunes, detect media size, detect time duration (mp3's only) and add 3rd party media statistics.

Features:

* Easily add/modify/remove podcast episodes from blog posts and pages
* Integrated media player
* iTunes RSS tags
* iTunes album/cover art
* Upload new iTunes/RSS cover art
* Ping iTunes
* Media size detection
* Duration detection (mp3 only)
* 3rd party statistics integration

For the latest information visit the website.

http://www.blubrry.com/powerpress/

Documentation available on the blubrry help site.

http://help.blubrry.com/blubrry-powerpress/

== Frequently Asked Questions ==

 = Why doesn't Blubrry Powerpress support multiple enclosures? =
 Blubrry Powerpress does not support multiple enclosures in one blog post. This is because each podcatching application handles multiple enclosures differently. iTunes will download the first enclosure that it sees in the feed ignoring the rest. Other podcatchers and podcasting directories either pick up the first enclosure or the last in each post item. This inconsistency combined with the fact that [Dave Winer does not recommend multiple enclosures](http://www.reallysimplesyndication.com/2004/12/21) and the [FeedValidator.org recommendation against it](http://www.feedvalidator.org/docs/warning/DuplicateEnclosure.html) is why the Blubrry Powerpress does not support them.

 = Why doesn't Blubrry Powerpress include media statistics? =
 Blubrry Powerpress does not include media statistics. This is not because Blubrry has its own statistics service, although that's a good reason by itself. Maintaining and calculating statistics is a resource and server intensive task that would add bloat to an otherwise lightweight Wordpress podcasting plugin. We recommend you use your media hosting's statistics and you're more than welcome to use the [Blubrry Statistics service](http://www.blubrry.com/podcast_statistics/) as well.

 = Looking for a better Audio Player? =
 Check out the <a href="http://wpaudioplayer.com" target="_blank" title="WP Audio Player 2.0">WP Audio Player 2.0</a>. The WP Audio Player 2.0 options include theme colors, initial volume, player width and more.
	
== Installation ==
1. Copy the entire directory from the downloaded zip file into the /wp-content/plugins/ folder.
2. Activate the "Blubrry Powerpress" plugin in the Plugin Management page.
3. Configure your Blubrry Powerpress by going to the **Settings** > **Blubrry Powerpress** page.
		
== Screenshots ==
1. Add podcast episode, found within the edit post screen
2. Cross section of Blubrry Powerpress settings page (Feed settings).
3. Cross section of Blubrry Powerpress settings page (Basic settings).

== Changelog ==

0.2 released on 08/11/2008
Initial release of Blubrry Powerpress

0.2.1 released on 09/17/2008
Fixed bugs: itunes:subtitle bug, itunes:summary is now enabled by default, add ending trailing slash to media url if missing, and copy blubrry keyword from podpress fix.

0.3.0 released on 09/24/2008
New features: Added important feeds list in feed settings, logic to prevent stats redirect duplication and added podcast only feed.

0.3.1 released on 10/02/2008
Fixed bug and added enhancements: iTunes subtitle, keywords and summary values now properly escape html special characters such as &nbsp; added define for adding itunes:new-feed-url tag, added define to display player for legacy Podpress episodes only.

0.3.2 released on 10/05/2008
Added alternative logic for those who host their blogs on servers with allow _ url _ fopen turned off.

0.4.0 released on 10/21/2008
Added two new play options adding 'play on page' links with and without play in new window links and now use a customizable play image for quicktime formatted media. Image may be customized by adding a define('POWERPRESS _ PLAY _ IMAGE', 'URL to image') to wp config file.

0.4.1 released on 10/24/2008
Fixed auto-play bug found in last update, only affected quicktime files with the play on page option.

0.4.2 released on 11/02/2008
Fixed quicktime in-page player bug, fixed bug which caused itunes keywords and subtitle to be blank and incremented version number.

0.5.0 released on 11/26/2008
Added options to report media duration and file size next to download links. Removed optional defines POWERPRESS _ PLUGIN _ PATH and POWERPRESS _ ITEM _ SUMMARY, defines no longer necessary. Added itunes:author and itunes:subtitle to header portion of podcast feeds and itunes:summary to post items. No longer removing quotes or extra spaces from itunes summary. Player auto-play bug fixed when quicktime files mixed with mp3s. Added new option to ping iTunes in a new browser window. Verify flash player check added, Wordpress auto plugin update will corrupt the flash player. This bug is fixed in Wordpress 2.7 beta 2 and newer. Media URL now displays a warning if the value contains characters which may cause problems.

0.5.1 released on 12/10/2008
Added podcast to pages option (Thanks @Frumph), added code to make sure the itunes:subtitle, keywords and summary feed tags never exceed their size limits.

0.5.2 released on 12/14/2008
Fixed bug with the feed channel itunes:summary being limited to 255 characters, the limit is now set to 4,000.

0.6.0 released on 12/17/2008
Fixed bug with podcast feed in Wordpress 2.7, added defaults for file size and duration, and added iTunes New Feed URL option.

0.6.1 released on 01/20/2009
Player now handles Windows Media (wmv) in Firefox, offering link to preferred Firefox plugin, now using the wp_specialchars() function for adding entities to feed values, fix problem with themes using excerpts not displaying the player correctly (Thanks @wayofthegeek for your help), and a number of other syntactical changes.

0.6.2 released on 01/26/2009
Added option to reset rewrite rules when settings saved to fix problem with podcast feed returning 404, logic added to prevent FeedSmith plugin from redirecting podcast feed, and added support for the Kimili Flash Embed plugin

0.6.3 released on 02/16/2009
Added new options to load all javascript for players in the wp_footer() function. Options are available as defines to add to the wp-config.php and are documented near the top of the powerpress.php. Added option in settings to display player in excerpts. Added code to repair corrupted Podpress data for displaying previously created podpress episodes. Note: Podpress data corruption was originally caused by previous versions of Podpress. Added code to prevent Wordpress from auto adding enclosures created from links to media found in the blog post.

0.6.4 released on 02/18/2009
Added enhanced processing of the <itunes:description> values, links and images are now hotlinked in the posts. Fixed bug where post_password_required() function does not exist. 


== Contributors ==
Angelo Mandato, CIO [RawVoice](http://www.rawvoice.com) - Plugin founder, architect and lead developer

Pat McSweeny, Developer for [RawVoice](http://www.rawvoice.com) - Developed initial version (v0.1.0) of plugin

Jerry Stephens, Way of the Geek [wayofthegeek.org](http://wayofthegeek.org) - Contributed initial code fix for excerpt bug resolved in v0.6.1


== Feedback == 
 http://www.blubrry.com/powerpress/

== Support == 
 http://help.blubrry.com/blubrry-powerpress/

== Twitter == 
 http://twitter.com/blubrry
