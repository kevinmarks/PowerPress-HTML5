<?php
/*
Plugin Name: Blubrry Powerpress
Plugin URI: http://www.blubrry.com/powerpress/
Description: <a href="http://www.blubrry.com/powerpress/" target="_blank">Blubrry Powerpress</a> adds podcasting support to your blog. Features include: media player, 3rd party statistics and iTunes integration.
Version: 0.5.0
Author: Blubrry
Author URI: http://www.blubrry.com/
Change Log:
	2008-11-26 - v0.5.0: Added options to report media duration and file size next to download links, new Media URL check performed when adding episode to new post and fixed a number of bugs including the player auto play bug caused by version v0.4.2.
	2008-10-24 - v0.4.2: Fixed quicktime in-page player bug, fixed bug which caused itunes keywords and subtitle to be blank and incremented version number.
	2008-10-24 - v0.4.1: Fixed auto-play bug found in last update, only affected quicktime files with the play on page option.
	2008-10-21 - v0.4.0: Added two new play options adding 'play on page' links with and without play in new window links and now use a customizable play image for quicktime media.
	2008-10-05 - v0.3.2: Added alternative logic for those who host their blogs on servers with allow_url_fopen turned off.
	2008-10-02 - v0.3.1: iTunes subtitle, keywords and summary values now properly escape html special characters such as &nbsp; added define for adding itunes:new-feed-url tag, added define to display player for legacy Podpress episodes only.
	2008-09-24 - v0.3.0: Added important feeds list in feed settings, logic to prevent stats redirect duplication and added podcast only feed.
	2008-09-17 - v0.2.1: Fixed itunes:subtitle bug, itunes:summary is now enabled by default, add ending trailing slash to media url if missing, and copy blubrry keyword from podpress fix.
	2008-08-05 - v0.2.0: First beta release of Blubrry Powerpress plugin.
	2008-08-05 - v0.1.2: Fixed minor bugs, trimming empty hour values in duration.
	2008-08-04 - v0.1.1: Fixed minor bugs, PHP_EOL define, check if function exists for older versions of wordpress and more.
	2008-08-04 - v0.1.0: Tentative initial release of Blubrry Powerpress plugin.

Contributors:
	Angelo Mandato, CIO RawVoice - Plugin founder and architect, mp3info class and javascript media player
	Pat McSweeny, Developer for RawVoice - powerpress.php and powperpressoptions.php
	
Credits:
	getID3(), License: GPL 2.0+ by James Heinrich <info [at] getid3.org> http://www.getid3.org
		Note: getid3.php analyze() function modified to prevent redundant filesize() function call.
	FlowPlayer, License: GPL 3.0+ http://flowplayer.org/; source: http://flowplayer.org/download.html
	flashembed(), License: MIT by Tero Piirainen (tipiirai [at] gmail.com)
		Note: code found at bottom of player.js
	
Copyright 2008 RawVoice Inc. (http://www.rawvoice.com)

License: Apache License version 2.0 (http://www.apache.org/licenses/)

	This project uses source that is GPL licensed that, for the sake of this plugin,
	is interpreted as GPL version 3.0 for compatibility with Apache 2.0 license.
*/

define('POWERPRESS_VERSION', '0.5.0' );

// include <itunes:new-feed-url> tag in Main RSS feed:
//define('POWERPRESS_NEW_FEED_URL', 'http://www.your-site.com/path/to/feed/');

// include <itunes:new-feed-url> tag in Podcast specific RSS feed:
//define('POWERPRESS_NEW_FEED_URL_PODCAST', 'http://www.your-site.com/path/to/podcast/feed/');

// Display Powerpress player only for previously created Podpress episodes.
// define('POWERPRESS_USE_PLAYER_FOR_PODPRESS_EPISODES', true);

// Display custom play image for quicktime media. Applies to on page player only.
// define('POWERPRESS_PLAY_IMAGE', 'http://www.blubrry.com/themes/blubrry/images/player/PlayerBadge150x50NoBorder.jpg');

// Define variables, advanced users could define these in their own wp-config.php so lets not try to re-define
if( !defined('POWERPRESS_LINK_SEPARATOR') )
	define('POWERPRESS_LINK_SEPARATOR', '|');
if( !defined('POWERPRESS_PLAY_IMAGE') )
	define('POWERPRESS_PLAY_IMAGE', 'play_video_default.jpg');
if( !defined('PHP_EOL') )
	define('PHP_EOL', "\n"); // We need this variable defined for new lines.

function powerpress_content($content)
{
	global $post;
	
	if( is_feed() )
		return $content; // We don't want to do anything to the feed
	
	// Powerpress settings:
	$Powerpress = get_option('powerpress_general');
	
	// Get the enclosure data
	$enclosureData = get_post_meta($post->ID, 'enclosure', true);
	
	if( !$enclosureData )
	{
		$EnclosureURL = '';
		if( $Powerpress['process_podpress'] )
		{
			//$Settings = get_option('powerpress_general');
			$podPressMedia = get_post_meta($post->ID, 'podPressMedia', true);
			if( $podPressMedia )
			{
				$EnclosureURL = $podPressMedia[0]['URI'];
				$EnclosureSize = $podPressMedia[0]['size'];
				$EnclosureType = '';
			}
			if( !$EnclosureURL )
				return $content;
			if( strpos($EnclosureURL, 'http://' ) !== 0 )
				$EnclosureURL = rtrim($Powerpress['default_url'], '/') .'/'. $EnclosureURL;
		}
	}
	else
	{
		list($EnclosureURL, $EnclosureSize, $EnclosureType) = split("\n", $enclosureData);
		$EnclosureURL = trim($EnclosureURL);
	}
	
	// Just in case, if there's no URL lets escape!
	if( !$EnclosureURL )
		return $content;
		
	
	if( !isset($Powerpress['display_player']) )
		$Powerpress['display_player'] = 1;
	if( !isset($Powerpress['player_function']) )
		$Powerpress['player_function'] = 1;
	if( !isset($Powerpress['podcast_link']) )
		$Powerpress['podcast_link'] = 1;
		
	if( defined('POWERPRESS_USE_PLAYER_FOR_PODPRESS_EPISODES') )
	{
		if( !isset($podPressMedia) )
			return $content;
	}
		
	// The blog owner doesn't want anything displayed, so don't bother wasting anymore CPU cycles
	if( $Powerpress['display_player'] == 0 )
		return $content;
		
	// Add redirects to Media URL
	$EnclosureURL = powerpress_add_redirect_url($EnclosureURL, $Powerpress);
	
	// Build links for player
	$player_links = '';
	switch( $Powerpress['player_function'] )
	{
		case 1: { // On page and new window
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Play in new window\" onclick=\"return powerpress_play_window(this.href);\">Play in new window</a>".PHP_EOL;
		}; break;
		case 2: { // Play in page only
		}; break;
		case 3: { //Play in new window only
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Play in new window\" onclick=\"return powerpress_play_window(this.href);\">Play in new window</a>".PHP_EOL;
		}; break;
		case 4: { // Play on page link only
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Play on page\" onclick=\"return powerpress_play_page(this.href, 'powerpress_player_{$post->ID}','true');\">Play on page</a>".PHP_EOL;
		}; break;
		case 5: { //Play on page link and new window
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Play on page\" onclick=\"return powerpress_play_page(this.href, 'powerpress_player_{$post->ID}','true');\">Play on page</a>".PHP_EOL;
			$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Play in new window\" onclick=\"return powerpress_play_window(this.href);\">Play in new window</a>".PHP_EOL;
		}; break;
	}//end switch	
	
	if( $Powerpress['podcast_link'] == 1 )
	{
		if( $player_links )
			$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
		$player_links .= "<a href=\"$EnclosureURL\" title=\"Download\">Download</a>".PHP_EOL;
	}
	else if( $Powerpress['podcast_link'] == 2 )
	{
		if( $player_links )
			$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
		$player_links .= "<a href=\"$EnclosureURL\" title=\"Download\">Download</a> (".powerpress_byte_size($EnclosureSize).") ".PHP_EOL;
	}
	else if( $Powerpress['podcast_link'] == 3 )
	{
		$duration = get_post_meta($post->ID, 'itunes:duration', true);
		if( $player_links )
			$player_links .= ' '. POWERPRESS_LINK_SEPARATOR .' ';
		if( $duration && ltrim($duration, '0:') != '' )
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Download\">Download</a> (duration: " . ltrim($duration, '0:') ." &#8212; ".powerpress_byte_size($EnclosureSize).")".PHP_EOL;
		else
			$player_links .= "<a href=\"$EnclosureURL\" title=\"Download\">Download</a> (".powerpress_byte_size($EnclosureSize).")".PHP_EOL;
	}
	
	$new_content = '';
	if( $Powerpress['player_function'] == 1 || $Powerpress['player_function'] == 2 ) // We have some kind of on-line player
	{
		$new_content .= '<div class="powerpress_player" id="powerpress_player_'. $post->ID .'"></div>'.PHP_EOL;
		$new_content .= '<script type="text/javascript">'.PHP_EOL;
		$new_content .= "powerpress_play_page('$EnclosureURL', 'powerpress_player_{$post->ID}');\n";
		$new_content .= '</script>'.PHP_EOL;
	}
	else if( $Powerpress['player_function'] == 4 || $Powerpress['player_function'] == 5 )
	{
		$new_content .= '<div class="powerpress_player" id="powerpress_player_'. $post->ID .'"></div>'.PHP_EOL;
	}
	if( $player_links )
		$new_content .= '<p class="powerpress_links">Podcast: ' . $player_links . '</p>'.PHP_EOL;
	
	if( $new_content == '' )
		return $content;
		
	switch( $Powerpress['display_player'] )
	{
		case 1: { // Below posts
			return $content.$new_content;
		}; break;
		case 2: { // Above posts
			return $new_content.$content;
		}; break;
	}
}//end function

add_action('the_content', 'powerpress_content');

function powerpress_header()
{
	// Powerpress settings:
	$Powerpress = get_option('powerpress_general');
	
	$PowerpressPluginURL = powerpress_get_root_url();
?>
<script type="text/javascript" src="<?php echo $PowerpressPluginURL; ?>player.js"></script>
<script type="text/javascript">
<?php
$player_image_url = POWERPRESS_PLAY_IMAGE;
if( strstr($player_image_url, 'http://') !== $player_image_url )
	$player_image_url = powerpress_get_root_url().$player_image_url;

if( $Powerpress['player_function'] == 4 || $Powerpress['player_function'] == 5 ) // Links would imply only one player in the page
	echo 'powerpress_player_init(\''. $PowerpressPluginURL .'\',\''. $player_image_url .'\',true);'.PHP_EOL;
else
	echo 'powerpress_player_init(\''. $PowerpressPluginURL .'\',\''. $player_image_url .'\');'.PHP_EOL;
?>
</script>
<style type="text/css">
.powerpress_player_old {
	margin-bottom: 3px;
	margin-top: 10px;
}
</style>
<?php
}

add_action('wp_head', 'powerpress_header');

function powerpress_rss2_ns(){
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) )
		return; // Another podcasting plugin is enabled...
	
	// Okay, lets add the namespace
	echo 'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"'."\n";
}

add_action('rss2_ns', 'powerpress_rss2_ns');


function powerpress_rss2_head()
{
	global $powerpress_feed, $powerpress_itunes_explicit, $powerpress_itunes_talent_name, $powerpress_default_url, $powerpress_process_podpress;
	$powerpress_feed = false; // By default, lets not apply the feed settings...
	
	if( defined('PODPRESS_VERSION') || isset($GLOBALS['podcasting_player_id']) )
		return; // Another podcasting plugin is enabled...
	
	
	$feed = get_query_var( 'feed' );
	if( $feed != 'feed' && $feed != 'podcast' && $feed != 'rss2' )
		return; // This is definitely not our kind of feed
	
	$GeneralSettings = get_option('powerpress_general');
	$powerpress_default_url = rtrim($GeneralSettings['default_url'], '/') .'/';
	$powerpress_process_podpress = $GeneralSettings['process_podpress'];
	
	$Feed = get_option('powerpress_feed');
	
	// First, determine if powerpress should even be rewriting this feed...
	if( $Feed['apply_to'] == '0' )
		return; // Okay, we're not suppoed to touch this feed, it's bad enough we added the iTunes namespace, escape!!!
	
	if( $Feed['apply_to'] == '3' && $feed != 'podcast' )
		return; // This is definitely not our kind of feed
	
	if( $Feed['apply_to'] == '2' && $feed != 'podcast' )
	{
		// If there's anything else in the query string, then something's going on and we shouldn't touch the feed
		if( isset($GLOBALS['wp_query']->query_vars) && count($GLOBALS['wp_query']->query_vars) > 1 )
			return;
	}
	
	// if( $Feed['apply_to'] == '1' ) { } // continue...
	// Case 1 (default, we apply to all of the rss2 feeds...)
	
	// We made it this far, lets write stuff to the feed!
	$powerpress_feed = true; // Okay, lets continue...
	if( $Feed )
	{
		while( list($key,$value) = each($Feed) )
			$Feed[$key] = htmlentities($value, ENT_NOQUOTES, 'UTF-8');
		reset($Feed);
	}
	
	echo '<!-- podcast_generator="Blubrry Powerpress/'. POWERPRESS_VERSION .'" -->'.PHP_EOL;
	
	// add the itunes:new-feed-url tag to feed
	if( defined('POWERPRESS_NEW_FEED_URL') && ($feed == 'feed' || $feed == 'rss2') )
		echo "\t<itunes:new-feed-url>". constant('POWERPRESS_NEW_FEED_URL') .'</itunes:new-feed-url>'.PHP_EOL;
	else if( defined('POWERPRESS_NEW_FEED_URL_'.strtoupper($feed) ) )
		echo "\t<itunes:new-feed-url>". constant( 'POWERPRESS_NEW_FEED_URL_'.strtoupper($feed) ) .'</itunes:new-feed-url>'.PHP_EOL;
	
	if( $Feed['itunes_summary'] )
		echo "\t".'<itunes:summary>'. htmlentities( $Feed['itunes_summary'], ENT_NOQUOTES, 'UTF-8') .'</itunes:summary>'.PHP_EOL;
	else
		echo "\t".'<itunes:summary>'.  htmlentities( get_bloginfo('description'), ENT_NOQUOTES, 'UTF-8') .'</itunes:summary>'.PHP_EOL;
	
	// explicit options:
	$explicit = array("no", "yes", "clean");
	
	$powerpress_itunes_explicit = $explicit[$Feed['itunes_explicit']];
	$powerpress_itunes_talent_name = $Feed['itunes_talent_name'];
	if( $powerpress_itunes_talent_name )
		echo "\t\t<itunes:author>" . $powerpress_itunes_talent_name . '</itunes:author>'.PHP_EOL;
	
	if( $powerpress_itunes_explicit )
		echo "\t".'<itunes:explicit>' . $powerpress_itunes_explicit . '</itunes:explicit>'.PHP_EOL;
	if( $Feed['itunes_image'] )
	{
		echo "\t".'<itunes:image href="' . $Feed['itunes_image'] . '" />'.PHP_EOL;
	}
	else
	{
		echo "\t".'<itunes:image href="' . powerpress_get_root_url() . 'itunes_default.jpg" />'.PHP_EOL;
	}
	
	if( $Feed['itunes_talent_name'] && $Feed['email'] )
	{
		echo "\t".'<itunes:owner>'.PHP_EOL;
		echo "\t\t".'<itunes:name>' . $Feed['itunes_talent_name'] . '</itunes:name>'.PHP_EOL;
		echo "\t\t".'<itunes:email>' . $Feed['email'] . '</itunes:email>'.PHP_EOL;
		echo "\t".'</itunes:owner>'.PHP_EOL;
	}
	if( $Feed['copyright'] )
		echo "\t".'<copyright>'. str_replace( array('&copy;', '(c)', '(C)'), '&#xA9;', htmlentities($Feed['copyright'], ENT_NOQUOTES, 'UTF-8')) . '</copyright>'.PHP_EOL;
	if( trim($Feed['itunes_subtitle']) )
		echo "\t".'<itunes:subtitle>' . powerpress_format_itunes_value($Feed['itunes_subtitle']) . '</itunes:subtitle>'.PHP_EOL;
	if( trim($Feed['itunes_keywords']) )
		echo "\t".'<itunes:keywords>' . powerpress_format_itunes_value($Feed['itunes_keywords']) . '</itunes:keywords>'.PHP_EOL;
	if( $Feed['itunes_talent_name'] && $Feed['email'] )
		echo "\t".'<managingEditor>'. $Feed['email'] .' ('. htmlentities($Feed['itunes_talent_name'], ENT_NOQUOTES, 'UTF-8') .')</managingEditor>'.PHP_EOL;
	if( $Feed['rss2_image'] )
	{
		echo"\t". '<image>' .PHP_EOL;
		echo "\t\t".'<title>' . htmlentities(get_bloginfo('name'), ENT_NOQUOTES, 'UTF-8') . '</title>'.PHP_EOL;
		echo "\t\t".'<url>' . $Feed['rss2_image'] . '</url>'.PHP_EOL;
		echo "\t\t".'<link>'. get_bloginfo('url') . '</link>' . PHP_EOL;
		echo "\t".'</image>' . PHP_EOL;
	}
	else // Use the default image
	{
		echo"\t". '<image>' .PHP_EOL;
		echo "\t\t".'<title>' . htmlentities(get_bloginfo('name'), ENT_NOQUOTES, 'UTF-8') . '</title>'.PHP_EOL;
		echo "\t\t".'<url>' . powerpress_get_root_url() . 'rss_default.jpg</url>'.PHP_EOL;
		echo "\t\t".'<link>'. get_bloginfo('url') . '</link>' . PHP_EOL;
		echo "\t".'</image>' . PHP_EOL;
	}
	
	$Categories = powerpress_itunes_categories();
	$Cat1 = false; $Cat2 = false; $Cat3 = false;
	if( $Feed['itunes_cat_1'] != '' )
			list($Cat1, $SubCat1) = split('-', $Feed['itunes_cat_1']);
	if( $Feed['itunes_cat_2'] != '' )
			list($Cat2, $SubCat2) = split('-', $Feed['itunes_cat_2']);
	if( $Feed['itunes_cat_3'] != '' )
			list($Cat3, $SubCat3) = split('-', $Feed['itunes_cat_3']);
 
	if( $Cat1 )
	{
		$CatDesc = $Categories[$Cat1.'-00'];
		$SubCatDesc = $Categories[$Cat1.'-'.$SubCat1];
		if( $Cat1 != $Cat2 && $SubCat1 == '00' )
		{
			echo "\t".'<itunes:category text="'. htmlspecialchars($CatDesc) .'" />'.PHP_EOL;
		}
		else
		{
			echo "\t".'<itunes:category text="'. htmlspecialchars($CatDesc) .'">'.PHP_EOL;
			if( $SubCat1 != '00' )
				echo "\t\t".'<itunes:category text="'. htmlspecialchars($SubCatDesc) .'" />'.PHP_EOL;
			
			// End this category set
			if( $Cat1 != $Cat2 )
				echo "\t".'</itunes:category>'.PHP_EOL;
		}
	}
 
	if( $Cat2 )
	{
		$CatDesc = $Categories[$Cat2.'-00'];
		$SubCatDesc = $Categories[$Cat2.'-'.$SubCat2];
	 
		// It's a continuation of the last category...
		if( $Cat1 == $Cat2 )
		{
			if( $SubCat2 != '00' )
				echo "\t\t".'<itunes:category text="'. htmlspecialchars($SubCatDesc) .'" />'.PHP_EOL;
			
			// End this category set
			if( $Cat2 != $Cat3 )
				echo "\t".'</itunes:category>'.PHP_EOL;
		}
		else // This is not a continuation, lets start a new category set
		{
			if( $Cat2 != $Cat3 && $SubCat2 == '00' )
			{
				echo "\t".'<itunes:category text="'. htmlspecialchars($CatDesc) .'" />'.PHP_EOL;
			}
			else // We have nested values
			{
				if( $Cat1 != $Cat2 ) // Start a new category set
					echo "\t".'<itunes:category text="'. htmlspecialchars($CatDesc) .'">'.PHP_EOL;
				if( $SubCat2 != '00' )
				echo "\t\t".'<itunes:category text="'. htmlspecialchars($SubCatDesc) .'" />'.PHP_EOL;
				if( $Cat2 != $Cat3 ) // End this category set
					echo "\t".'</itunes:category>'.PHP_EOL;
			}
		}
	}
 
	if( $Cat3 )
	{
		$CatDesc = $Categories[$Cat3.'-00'];
		$SubCatDesc = $Categories[$Cat3.'-'.$SubCat3];
	 
		// It's a continuation of the last category...
		if( $Cat2 == $Cat3 )
		{
			if( $SubCat3 != '00' )
				echo "\t\t".'<itunes:category text="'. htmlspecialchars($SubCatDesc) .'" />'.PHP_EOL;
			
			// End this category set
			echo "\t".'</itunes:category>'.PHP_EOL;
		}
		else // This is not a continuation, lets start a new category set
		{
			if( $Cat2 != $Cat3 && $SubCat3 == '00' )
			{
				echo "\t".'<itunes:category text="'. htmlspecialchars($CatDesc) .'" />'.PHP_EOL;
			}
			else // We have nested values
			{
				if( $Cat2 != $Cat3 ) // Start a new category set
					echo "\t".'<itunes:category text="'. htmlspecialchars($CatDesc) .'">'.PHP_EOL;
				if( $SubCat3 != '00' )
					echo "\t\t".'<itunes:category text="'. htmlspecialchars($SubCatDesc) .'" />'.PHP_EOL;
				// End this category set
				echo "\t".'</itunes:category>'.PHP_EOL;
			}
		}
	}
}

add_action('rss2_head', 'powerpress_rss2_head');

function powerpress_rss2_item()
{
	global $powerpress_feed, $powerpress_itunes_explicit, $powerpress_itunes_talent_name, $powerpress_default_url, $powerpress_process_podpress, $post;
	$duration = false;
	// are we processing a feed that powerpress should handle
	if( $powerpress_feed == false )
		return;
		
	// Check and see if we're working with a podcast episode
	$enclosureData = get_post_meta($post->ID, 'enclosure', true);
	if( !$enclosureData )
	{
		$EnclosureURL = '';
		if( $powerpress_process_podpress )
		{
			//$Settings = get_option('powerpress_general');
			$podPressMedia = get_post_meta($post->ID, 'podPressMedia', true);
			if( $podPressMedia )
			{
				$EnclosureURL = $podPressMedia[0]['URI'];
				if( strpos($EnclosureURL, 'http://' ) !== 0 )
					$EnclosureURL = $powerpress_default_url . $EnclosureURL;
				$EnclosureSize = $podPressMedia[0]['size'];
				$duration = $podPressMedia[0]['duration'];
				$EnclosureType = false;
				$UrlParts = parse_url($EnclosureURL);
				if( $UrlParts['path'] )
				{
					// using functions that already exist in Wordpress when possible:
					$FileType = wp_check_filetype($UrlParts['path']);
					if( $FileType )
						$EnclosureType = $FileType['type'];
				}
				
				if( $EnclosureType && $EnclosureSize && $EnclosureURL )
					echo "\t\t".'<enclosure url="' . $EnclosureURL . '" length="'. $EnclosureSize .'" type="'. $EnclosureType .'" />'.PHP_EOL;
				else
					return;
			}
			else
				return;
		}
		else
			return;
	}
	
	if( !$duration )
		$duration = get_post_meta($post->ID, 'itunes:duration', true);
		
	// Get the post tags:
	$tagobject = wp_get_post_tags( $post->ID );
	if( count($tagobject) )
	{
		$tags = array();
		for($c = 0; $c < count($tagobject) && $c < 12; $c++) // iTunes only accepts up to 12 keywords
			$tags[] = htmlentities($tagobject[$c]->name, ENT_NOQUOTES, 'UTF-8');
		
		echo "\t\t<itunes:keywords>" . powerpress_format_itunes_value(implode(",", $tags)) . '</itunes:keywords>'.PHP_EOL;
	}
	
	// Strip and format the wordpress way, but don't apply any other filters for these itunes tags
	$content_no_html = $post->post_content;
	if( function_exists('strip_shortcodes') )
		$content_no_html = strip_shortcodes( $content_no_html ); 
	$content_no_html = str_replace(']]>', ']]&gt;', $content_no_html);
	$content_no_html = strip_tags($content_no_html);
	
	$excerpt_no_html = strip_tags($post->post_excerpt);
	
	if( $excerpt_no_html )
		echo "\t\t<itunes:subtitle>". powerpress_format_itunes_value(powerpress_smart_trim($excerpt_no_html, 250, true)) .'</itunes:subtitle>'.PHP_EOL;
	else	
		echo "\t\t<itunes:subtitle>". powerpress_format_itunes_value(powerpress_smart_trim($content_no_html, 250, true)) .'</itunes:subtitle>'.PHP_EOL;
		
	echo "\t\t<itunes:summary>". powerpress_format_itunes_value(powerpress_smart_trim($content_no_html, 4000)) .'</itunes:summary>'.PHP_EOL;
	
	if( $powerpress_itunes_talent_name )
		echo "\t\t<itunes:author>" . $powerpress_itunes_talent_name . '</itunes:author>'.PHP_EOL;
	
	if( $powerpress_itunes_explicit )
		echo "\t\t<itunes:explicit>" . $powerpress_itunes_explicit . '</itunes:explicit>'.PHP_EOL;
	
	if( $duration && preg_match('/^(\d{1,2}:){0,2}\d{1,2}$/i', $duration) ) // Include duration if it is valid
		echo "\t\t<itunes:duration>" . ltrim($duration, '0:') . '</itunes:duration>'.PHP_EOL;
}

add_action('rss2_item', 'powerpress_rss2_item');

function powerpress_filter_rss_enclosure($content)
{
	$match_count = preg_match('/\surl="([^"]*)"/', $content, $matches);
	if( count($matches) != 2)
		return $content;
		
	// Original Media URL
	$OrigURL = $matches[1];
	
	// Modified Media URL
	$ModifiedURL = powerpress_add_redirect_url($OrigURL);
	
	// Replace the original url with the modified one...
	if( $OrigURL != $ModifiedURL )
		return str_replace($OrigURL, $ModifiedURL, $content);
	return $content;
}

add_filter('rss_enclosure', 'powerpress_filter_rss_enclosure');

function powerpress_do_podcast_feed()
{
	global $wp_query;
	$wp_query->get_posts();
	load_template(ABSPATH . 'wp-rss2.php');
}

function powerpress_init()
{
	add_feed('podcast', 'powerpress_do_podcast_feed');
}

add_action('init', 'powerpress_init');


function powerpress_posts_join($join)
{
	if( is_feed() && get_query_var('feed') == 'podcast' )
	{
		global $wpdb;
		$join .= " INNER JOIN {$wpdb->postmeta} ";
		$join .= " ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
	}
  return $join;
}

add_filter('posts_join', 'powerpress_posts_join' );

function powerpress_posts_where($where)
{
	if( is_feed() && get_query_var('feed') == 'podcast' )
	{
		global $wpdb;
		$where .= " AND (";
		$where .= " {$wpdb->postmeta}.meta_key = 'enclosure' ";
		
		// Powerpress settings:
		$Powerpress = get_option('powerpress_general');
	
		// Include Podpress data if exists...
		if( $Powerpress['process_podpress'] )
			$where .= " OR {$wpdb->postmeta}.meta_key = 'podPressMedia' ";
			
		$where .=") ";
	}
	return $where;
}

add_filter('posts_where', 'powerpress_posts_where' );

/*
Helper functions:
*/

function powerpress_itunes_categories($PrefixSubCategories = false)
{
	$temp = array();
	$temp['01-00'] = 'Arts';
		$temp['01-01'] = 'Design';
		$temp['01-02'] = 'Fashion & Beauty';
		$temp['01-03'] = 'Food';
		$temp['01-04'] = 'Literature';
		$temp['01-05'] = 'Performing Arts';
		$temp['01-06'] = 'Visual Arts';

	$temp['02-00'] = 'Business';
		$temp['02-01'] = 'Business News';
		$temp['02-02'] = 'Careers';
		$temp['02-03'] = 'Investing';
		$temp['02-04'] = 'Management & Marketing';
		$temp['02-05'] = 'Shopping';

	$temp['03-00'] = 'Comedy';

	$temp['04-00'] = 'Education';
		$temp['04-01'] = 'Education Technology';
		$temp['04-02'] = 'Higher Education';
		$temp['04-03'] = 'K-12';
		$temp['04-04'] = 'Language Courses';
		$temp['04-05'] = 'Training';
		 
	$temp['05-00'] = 'Games & Hobbies';
		$temp['05-01'] = 'Automotive';
		$temp['05-02'] = 'Aviation';
		$temp['05-03'] = 'Hobbies';
		$temp['05-04'] = 'Other Games';
		$temp['05-05'] = 'Video Games';

	$temp['06-00'] = 'Government & Organizations';
		$temp['06-01'] = 'Local';
		$temp['06-02'] = 'National';
		$temp['06-03'] = 'Non-Profit';
		$temp['06-04'] = 'Regional';

	$temp['07-00'] = 'Health';
		$temp['07-01'] = 'Alternative Health';
		$temp['07-02'] = 'Fitness & Nutrition';
		$temp['07-03'] = 'Self-Help';
		$temp['07-04'] = 'Sexuality';

	$temp['08-00'] = 'Kids & Family';
 
	$temp['09-00'] = 'Music';
 
	$temp['10-00'] = 'News & Politics';
 
	$temp['11-00'] = 'Religion & Spirituality';
		$temp['11-01'] = 'Buddhism';
		$temp['11-02'] = 'Christianity';
		$temp['11-03'] = 'Hinduism';
		$temp['11-04'] = 'Islam';
		$temp['11-05'] = 'Judaism';
		$temp['11-06'] = 'Other';
		$temp['11-07'] = 'Spirituality';
	 
	$temp['12-00'] = 'Science & Medicine';
		$temp['12-01'] = 'Medicine';
		$temp['12-02'] = 'Natural Sciences';
		$temp['12-03'] = 'Social Sciences';
	 
	$temp['13-00'] = 'Society & Culture';
		$temp['13-01'] = 'History';
		$temp['13-02'] = 'Personal Journals';
		$temp['13-03'] = 'Philosophy';
		$temp['13-04'] = 'Places & Travel';

	$temp['14-00'] = 'Sports & Recreation';
		$temp['14-01'] = 'Amateur';
		$temp['14-02'] = 'College & High School';
		$temp['14-03'] = 'Outdoor';
		$temp['14-04'] = 'Professional';
		 
	$temp['15-00'] = 'Technology';
		$temp['15-01'] = 'Gadgets';
		$temp['15-02'] = 'Tech News';
		$temp['15-03'] = 'Podcasting';
		$temp['15-04'] = 'Software How-To';

	$temp['16-00'] = 'TV & Film';

	if( $PrefixSubCategories )
	{
		while( list($key,$val) = each($temp) )
		{
			$parts = split('-', $key);
			$cat = $parts[0];
			$subcat = $parts[1];
		 
			if( $subcat != '00' )
				$temp[$key] = $temp[$cat.'-00'].' > '.$val;
		}
		reset($temp);
	}
 
	return $temp;
}

function powerpress_get_root_url()
{
	$powerpress_dirname = basename( dirname(__FILE__) );
	return WP_PLUGIN_URL . '/'. $powerpress_dirname .'/';
}

function powerpress_format_itunes_value($value)
{
	$value = htmlentities($value, ENT_NOQUOTES, 'UTF-8');
	//$value = str_replace('&quot;', '', $value);
	$value = str_replace('&amp;', '', $value);
	$value = str_replace('&lt;', '', $value);
	$value = str_replace('&gt;', '', $value);
	//$value = str_replace('&nbsp;', '', $value);
	$value = str_replace('&', '', $value); // Last attempt to fix, just remove the &
	return $value;
}

function powerpress_smart_trim($value, $char_limit = 250, $remove_new_lines = false)
{
	if( strlen($value) > $char_limit )
	{
		$new_value = substr($value, 0, $char_limit);
		// Look back at most 50 characters...
		$eos = strrpos($new_value, '.');
		$eol = strrpos($new_value, "\n");

		// If the end of line is longer than the end of sentence and we're not loosing too much of our string...
		if( $eol > $eos && $eol > (strlen($new_value)-50) )
			$return = substr($new_value, 0, $eol);
		// If the end of sentence is longer than the end of line and we're not loosing too much of our string...
		else if( $eos > $eol && $eos > (strlen($new_value)-50) )
			$return = substr($new_value, 0, $eos);
		else // Otherwise, just add some dots to the end
			$return = substr($new_value, 0, $char_limit).'...';
		//	$return = $new_value;
	}
	else
	{
		$return = $value;
	}

	if( $remove_new_lines )
		$return = str_replace( array("\n", "\r", "\t"), array(' ', '', '  '), $return );
	return $return;
}

function powerpress_add_redirect_url($MediaURL, $GeneralSettings = false)
{
	$NewURL = $MediaURL;
	if( !$GeneralSettings ) // Get the general settings if not passed to this function, maintain the settings globally for further use
	{
		global $powerpress_general_settings;
		if( !$powerpress_general_settings )
			$powerpress_general_settings = get_option('powerpress_general');
		$GeneralSettings = $powerpress_general_settings;
	}
	
	for( $x = 3; $x > 0; $x-- )
	{
		$key = sprintf('redirect%d', $x);
		if( $GeneralSettings[ $key ] )
		{
			$RedirectClean = str_replace('http://', '', $GeneralSettings[ $key ]);
			if( !strstr($NewURL, $RedirectClean) )
				$NewURL = 'http://'. $RedirectClean . str_replace('http://', '', $NewURL);
		}
	}

	return $NewURL;
}

/*
Code contributed from upekshapriya on the Blubrry Forums
*/
function powerpress_byte_size($ppbytes) 
{
	$ppsize = $ppbytes / 1024;
	if($ppsize < 1024)
	{
		$ppsize = number_format($ppsize, 1);
		$ppsize .= 'KB';
	} 
	else 
	{
		if($ppsize / 1024 < 1024) 
		{
			$ppsize = number_format($ppsize / 1024, 1);
			$ppsize .= 'MB';
		}
		else if ($ppsize / 1024 / 1024 < 1024)   
		{
		$ppsize = number_format($ppsize / 1024 / 1024, 1);
		$ppsize .= 'GB';
		} 
	}
	return $ppsize;
}
/*
End Helper Functions
*/

// Are we in the admin?
if( is_admin() )
	require_once('powerpressadmin.php');
		
?>