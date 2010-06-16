<?php

if( !function_exists('add_action') )
	die("access denied.");
	
function powerpress_languages()
{
	// List copied from PodPress:
	$langs = array();
	$langs['af'] = 'Afrikaans';
	$langs['sq'] = 'Albanian';
	$langs['eu'] = 'Basque';
	$langs['be'] = 'Belarusian';
	$langs['bg'] = 'Bulgarian';
	$langs['ca'] = 'Catalan';
	$langs['zh-cn'] = 'Chinese (Simplified)';
	$langs['zh-tw'] = 'Chinese (Traditional)';
	$langs['hr'] = 'Croatian';
	$langs['cs'] = 'Czech';
	$langs['da'] = 'Danish';
	$langs['nl'] = 'Dutch';
	$langs['nl-be'] = 'Dutch (Belgium)';
	$langs['nl-nl'] = 'Dutch (Netherlands)';
	$langs['en'] = 'English';
	$langs['en-au'] = 'English (Australia)';
	$langs['en-bz'] = 'English (Belize)';
	$langs['en-ca'] = 'English (Canada)';
	$langs['en-ie'] = 'English (Ireland)';
	$langs['en-jm'] = 'English (Jamaica)';
	$langs['en-nz'] = 'English (New Zealand)';
	$langs['en-ph'] = 'English (Phillipines)';
	$langs['en-za'] = 'English (South Africa)';
	$langs['en-tt'] = 'English (Trinidad)';
	$langs['en-gb'] = 'English (United Kingdom)';
	$langs['en-us'] = 'English (United States)';
	$langs['en-zw'] = 'English (Zimbabwe)';
	$langs['et'] = 'Estonian';
	$langs['fo'] = 'Faeroese';
	$langs['fi'] = 'Finnish';
	$langs['fr'] = 'French';
	$langs['fr-be'] = 'French (Belgium)';
	$langs['fr-ca'] = 'French (Canada)';
	$langs['fr-fr'] = 'French (France)';
	$langs['fr-lu'] = 'French (Luxembourg)';
	$langs['fr-mc'] = 'French (Monaco)';
	$langs['fr-ch'] = 'French (Switzerland)';
	$langs['gl'] = 'Galician';
	$langs['gd'] = 'Gaelic';
	$langs['de'] = 'German';
	$langs['de-at'] = 'German (Austria)';
	$langs['de-de'] = 'German (Germany)';
	$langs['de-li'] = 'German (Liechtenstein)';
	$langs['de-lu'] = 'German (Luxembourg)';
	$langs['de-ch'] = 'German (Switzerland)';
	$langs['el'] = 'Greek';
	$langs['haw'] = 'Hawaiian';
	$langs['hu'] = 'Hungarian';
	$langs['is'] = 'Icelandic';
	$langs['in'] = 'Indonesian';
	$langs['ga'] = 'Irish';
	$langs['it'] = 'Italian';
	$langs['it-it'] = 'Italian (Italy)';
	$langs['it-ch'] = 'Italian (Switzerland)';
	$langs['ja'] = 'Japanese';
	$langs['ko'] = 'Korean';
	$langs['mk'] = 'Macedonian';
	$langs['no'] = 'Norwegian';
	$langs['pl'] = 'Polish';
	$langs['pt'] = 'Portuguese';
	$langs['pt-br'] = 'Portuguese (Brazil)';
	$langs['pt-pt'] = 'Portuguese (Portugal)';
	$langs['ro'] = 'Romanian';
	$langs['ro-mo'] = 'Romanian (Moldova)';
	$langs['ro-ro'] = 'Romanian (Romania)';
	$langs['ru'] = 'Russian';
	$langs['ru-mo'] = 'Russian (Moldova)';
	$langs['ru-ru'] = 'Russian (Russia)';
	$langs['sr'] = 'Serbian';
	$langs['sk'] = 'Slovak';
	$langs['sl'] = 'Slovenian';
	$langs['es'] = 'Spanish';
	$langs['es-ar'] = 'Spanish (Argentina)';
	$langs['es-bo'] = 'Spanish (Bolivia)';
	$langs['es-cl'] = 'Spanish (Chile)';
	$langs['es-co'] = 'Spanish (Colombia)';
	$langs['es-cr'] = 'Spanish (Costa Rica)';
	$langs['es-do'] = 'Spanish (Dominican Republic)';
	$langs['es-ec'] = 'Spanish (Ecuador)';
	$langs['es-sv'] = 'Spanish (El Salvador)';
	$langs['es-gt'] = 'Spanish (Guatemala)';
	$langs['es-hn'] = 'Spanish (Honduras)';
	$langs['es-mx'] = 'Spanish (Mexico)';
	$langs['es-ni'] = 'Spanish (Nicaragua)';
	$langs['es-pa'] = 'Spanish (Panama)';
	$langs['es-py'] = 'Spanish (Paraguay)';
	$langs['es-pe'] = 'Spanish (Peru)';
	$langs['es-pr'] = 'Spanish (Puerto Rico)';
	$langs['es-es'] = 'Spanish (Spain)';
	$langs['es-uy'] = 'Spanish (Uruguay)';
	$langs['es-ve'] = 'Spanish (Venezuela)';
	$langs['sv'] = 'Swedish';
	$langs['sv-fi'] = 'Swedish (Finland)';
	$langs['sv-se'] = 'Swedish (Sweden)';
	$langs['tr'] = 'Turkish';
	$langs['uk'] = 'Ukranian';
	return $langs;
}

function powerpress_admin_capabilities()
{
	global $wp_roles;
	
	$capnames = array();
	// Get Role List
	foreach($wp_roles->role_objects as $key => $role) {
		foreach($role->capabilities as $cap => $grant) {
			$capnames[$cap] = ucwords( str_replace('_', ' ',  $cap) );
		}
	}

	$capnames = array_unique($capnames);
	$remove_keys = array('level_0', 'level_1', 'level_2', 'level_3', 'level_4', 'level_5', 'level_6', 'level_7', 'level_8', 'level_9', 'level_10');
	while( list($null,$key) = each($remove_keys) )
		unset($capnames[ $key ]);
	asort($capnames);
	return $capnames;
}
$g_SupportUploads = null;
function powerpressadmin_support_uploads()
{
	global $g_SupportUploads;
	if( $g_SupportUploads != null )
		return $g_SupportUploads;
	
	$g_SupportUploads = false;
	$UploadArray = wp_upload_dir();
	if( false === $UploadArray['error'] )
	{
		$upload_path =  $UploadArray['basedir'].'/powerpress/';
		
		if( !file_exists($upload_path) )
			$g_SupportUploads = @wp_mkdir_p( rtrim($upload_path, '/') );
		else
			$g_SupportUploads = true;
	}
	return $g_SupportUploads;
}

// powerpressadmin_editfeed.php
function powerpress_admin_editfeed($feed_slug=false, $cat_ID =false)
{
	$SupportUploads = powerpressadmin_support_uploads();
	$General = powerpress_get_settings('powerpress_general');
	
	
	if( $feed_slug )
	{
		$FeedSettings = powerpress_get_settings('powerpress_feed_'.$feed_slug);
		if( !$FeedSettings )
		{
			$FeedSettings = array();
			$FeedSettings['title'] = $General['custom_feeds'][$feed_slug];
		}
		$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed_custom');
		
		if( !isset($General['custom_feeds'][$feed_slug]) )
			$General['custom_feeds'][$feed_slug] = 'Podcast (default)';
	}
	else if( $cat_ID )
	{
		$FeedSettings = powerpress_get_settings('powerpress_cat_feed_'.$cat_ID);
		$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed_custom');
	}
	else
	{
		$FeedSettings = powerpress_get_settings('powerpress_feed');
		$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed');
	}
		
	$FeedTitle = __('Feed Settings');
	if( $feed_slug )
	{
		$FeedTitle = sprintf( 'Edit Podcast Channel: %s', $General['custom_feeds'][$feed_slug]);
		echo sprintf('<input type="hidden" name="feed_slug" value="%s" />', $feed_slug);
	}
	else if( $cat_ID )
	{
		$category = get_category_to_edit($cat_ID);
		$FeedTitle = sprintf( 'Edit Category Feed: %s', $category->name);
		echo sprintf('<input type="hidden" name="cat" value="%s" />', $cat_ID);
	}
	
		echo '<h2>'. $FeedTitle .'</h2>';
	
	if( $cat_ID && (isset($_GET['from_categories']) || isset($_POST['from_categories'])) )
	{
		echo '<input type="hidden" name="from_categories" value="1" />';
	}
	
?>
<div id="powerpress_settings_page" class="powerpress_tabbed_content"> 
  <ul class="powerpress_settings_tabs">
		<li><a href="#feed_tab_feed"><span>Feed Settings</span></a></li>
		<li><a href="#feed_tab_itunes"><span>iTunes Settings</span></a></li>
	<?php if( $feed_slug ) { ?>
		<li><a href="#feed_tab_appearance"><span>Appearance</span></a></li>
		<li><a href="#feed_tab_other"><span>Other Settings</span></a></li> 
	<?php } ?>
	<?php if( $cat_ID ) { ?>
		<li><a href="#feed_tab_other"><span>Other Settings</span></a></li> 
	<?php } ?>
  </ul>
	
	
	<div id="feed_tab_feed" class="powerpress_tab">
		<?php
		//powerpressadmin_edit_feed_general($FeedSettings, $General);
		//powerpressadmin_edit_feed_settings($FeedSettings, $General);
		powerpressadmin_edit_feed_settings($FeedSettings, $General, $cat_ID, $feed_slug );
		?>
	</div>
	
	<div id="feed_tab_itunes" class="powerpress_tab">
		<?php
		//powerpressadmin_edit_itunes_general($General);
		if( $feed_slug != 'podcast' )
			powerpressadmin_edit_itunes_general($General, $FeedSettings, $feed_slug, $cat_ID);
		powerpressadmin_edit_itunes_feed($FeedSettings, $General, $feed_slug, $cat_ID);
		?>
	</div>
	
	<?php if( $feed_slug ) { ?>
	<div id="feed_tab_appearance" class="powerpress_tab">
		<?php
		//powerpressadmin_appearance($General);
		powerpressadmin_edit_appearance_feed($General, $FeedSettings, $feed_slug);
		?>
	</div>
	
	<div id="feed_tab_other" class="powerpress_tab">
		<?php
		powerpressadmin_edit_basics_feed($General, $FeedSettings, $feed_slug)
		?>
	</div>
	<?php } ?>
	
	<?php if( $cat_ID ) { ?>
	<div id="feed_tab_other" class="powerpress_tab">
		<?php
		powerpressadmin_edit_basics_feed($General, $FeedSettings, $feed_slug, $cat_ID)
		?>
	</div>
	<?php } ?>
	
</div>
<div class="clear"></div>
<?php




		//if( !$cat_ID && !$feed_slug )
		//	powerpressadmin_edit_feed_general($FeedSettings, $General);
		
		
		
		
}

function powerpressadmin_edit_podcast_channel($FeedSettings, $General)
{
	// TODO
?>
<input type="hidden" name="action" value="powerpress-save-customfeed" />
<p style="margin-bottom: 0;">
	<?php _e('Configure your custom podcast feed.'); ?>
</p>
<?php
}

function powerpressadmin_edit_category_feed($FeedSettings, $General)
{
?>
<input type="hidden" name="action" value="powerpress-save-categoryfeedsettings" />
<p style="margin-bottom: 0;">
	<?php _e('Configure your category feed to support podcasting.'); ?>
</p>
<?php
}

function powerpressadmin_edit_feed_general($FeedSettings, $General)
{
	$AdvancedMode = $General['advanced_mode'];
?>
<h3>Podcast Feeds</h3>
<table class="form-table">

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row">

<?php echo __('Enhance Feeds'); ?></th> 
<td>
	<ul>
		<li><label><input type="radio" name="Feed[apply_to]" value="1" <?php if( $FeedSettings['apply_to'] == 1 ) echo 'checked'; ?> /> Enhance All Feeds</label> (Recommended)</li>
		<li>
			<ul>
				<li>Adds podcasting support to all feeds</li>
				<li>Allows for Category Podcasting (Visitors may subscribe to your categories as a podcast)</li>
				<li>Allows for Tag/Keyword Casting (Visitors may subscribe to your tags as a podcast)</li>
			</ul>
		</li>
		<li><label><input type="radio" name="Feed[apply_to]" value="2" <?php if( $FeedSettings['apply_to'] == 2 ) echo 'checked'; ?> /> Enhance Main Feed Only</label></li>
		<li>
			<ul>
				<li>Adds podcasting support to your main feed only</li>
			</ul>
		</li>
		<li><label><input type="radio" name="Feed[apply_to]" value="0" <?php if( $FeedSettings['apply_to'] == 0 ) echo 'checked'; ?> /> Do Not Enhance Feeds</label></li>
		<li>
			<ul>
				<li>Feed Settings below will only apply to your podcast channel feeds</li>
			</ul>
		</li>
	</ul>
</td>
</tr>

<tr valign="top">
<th scope="row">

<?php _e("Main Site Feed"); ?></th> 
<td>
	<p style="margin-top: 5px; margin-bottom: 0;">Main RSS2 Feed: <a href="<?php echo get_bloginfo('rss2_url'); ?>" title="Main RSS 2 Feed" target="_blank"><?php echo get_bloginfo('rss2_url'); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_bloginfo('rss2_url')); ?>" title="Validate Feed" target="_blank">validate</a></p>
</td>
</tr>
<?php
	}
?>
<tr valign="top">
<th scope="row">

<?php _e("Podcast Channel Feeds"); ?></th> 
<td>
<?php
	
	//$General = get_option('powerpress_general');
	$Feeds = array('podcast'=>'Special Podcast only Feed');
	if( isset($General['custom_feeds']['podcast']) )
		$Feeds = $General['custom_feeds'];
	else if( isset($General['custom_feeds'])&& is_array($General['custom_feeds']) )
		$Feeds += $General['custom_feeds'];
		
	while( list($feed_slug, $feed_title) = each($Feeds) )
	{
		$edit_link = admin_url( 'admin.php?page=powerpress/powerpressadmin_customfeeds.php&amp;action=powerpress-editfeed&amp;feed_slug=') . $feed_slug;
?>
<p><?php echo $feed_title; ?>: <a href="<?php echo get_feed_link($feed_slug); ?>" title="<?php echo $feed_title; ?>" target="_blank"><?php echo get_feed_link($feed_slug); ?></a>
| <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_feed_link($feed_slug)); ?>" title="Validate Feed" target="_blank">validate</a>
	<?php if( false && $feed_slug != 'podcast' ) { ?>
	| <a href="<?php echo $edit_link; ?>" title="Edit Podcast Channel">edit</a>
	<?php } ?>
</p>
<?php } ?>
</td>
</tr>
</table>
<?php
}

function powerpressadmin_edit_feed_settings($FeedSettings, $General, $cat_ID = false, $feed_slug = false)
{
	$SupportUploads = powerpressadmin_support_uploads();
	if( !isset($FeedSettings['posts_per_rss']) )
		$FeedSettings['posts_per_rss'] = '';
	if( !isset($FeedSettings['rss2_image']) )
		$FeedSettings['rss2_image'] = '';
	if( !isset($FeedSettings['copyright']) )
		$FeedSettings['copyright'] = '';
	
	if( $cat_ID || $feed_slug )
	{
?>
<h3>Feed Information</h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php _e("Feed URL"); ?>
</th>
<td>
<?php if( $cat_ID ) { ?>
<p style="margin-top: 0;"><a href="<?php echo get_category_feed_link($cat_ID); ?>" target="_blank"><?php echo get_category_feed_link($cat_ID); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode( str_replace('&amp;', '&', get_category_feed_link($cat_ID))); ?>" target="_blank"><?php _e('validate'); ?></a></p>
<?php } else { ?>
<p style="margin-top: 0;"><a href="<?php echo get_feed_link($feed_slug); ?>" target="_blank"><?php echo get_feed_link($feed_slug); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_feed_link($feed_slug)); ?>" target="_blank"><?php _e('validate'); ?></a></p>
<?php } ?>
</td>
</tr>
</table>
<?php
	}
?>
<h3>Feed Settings</h3>
<table class="form-table">

<?php
if( $feed_slug || $cat_ID )
{
?>
<tr valign="top">
<th scope="row">
<?php _e("Feed Title"); ?>
</th>
<td>
<input type="text" name="Feed[title]"style="width: 60%;"  value="<?php echo $FeedSettings['title']; ?>" maxlength="250" />
<?php if( $cat_ID ) { ?>
(leave blank to use default category title)
<?php } else { ?>
(leave blank to use blog title)
<?php } ?>
<?php if( $cat_ID ) { 
	$category = get_category_to_edit($cat_ID);
	$CategoryName = htmlspecialchars($category->name);
?>
<p><?php echo __('Default Category title:') .' '. get_bloginfo_rss('name') . ' &#187; '. $CategoryName; ?></p>
<?php } else { ?>
<p><?php echo __('Blog title:') .' '. get_bloginfo_rss('name'); ?></p>
<?php } ?>
</td>
</tr>
<tr valign="top">
<th scope="row">
<?php _e("Feed Description"); ?>
</th>
<td>
<input type="text" name="Feed[description]"style="width: 60%;"  value="<?php echo $FeedSettings['description']; ?>" maxlength="1000" /> 
<?php if( $cat_ID ) { ?>
(leave blank to use category description)
<?php } else { ?>
(leave blank to use blog description)
<?php } ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Feed Landing Page URL"); ?> <br />
</th>
<td>
<input type="text" name="Feed[url]"style="width: 60%;"  value="<?php echo $FeedSettings['url']; ?>" maxlength="250" />
<?php if( $cat_ID ) { ?>
(leave blank to use category page)
<?php } else { ?>
(leave blank to use home page)
<?php } ?>
<?php if( $cat_ID ) { ?>
<p>Category page URL: <?php echo get_category_link($cat_ID); ?></p>
<?php } else { ?>
<p>e.g. <?php echo get_bloginfo('home'); ?>/custom-page/</p>
<?php } ?>
</td>
</tr>

<?php

	// TODO: This is a bug, user should be able to ping a specific feed without pinging the main feed.
	if( false && $General['ping_itunes'] && $feed_slug != 'podcast' )
	{
?>
<tr valign="top">
<th scope="row">
<?php _e("iTunes URL"); ?>
</th>
<td>
<input type="text" style="width: 80%;" name="Feed[itunes_url]" value="<?php echo $FeedSettings['itunes_url']; ?>" maxlength="250" />
<p>Click the following link to <a href="https://phobos.apple.com/WebObjects/MZFinance.woa/wa/publishPodcast" target="_blank" title="Publish a Podcast on iTunes">Publish a Podcast on iTunes</a>.
Once your podcast is listed on iTunes, enter your one-click subscription URL above.
</p>
<p>e.g. http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=000000000</p>

<p><input name="TestiTunesPing" type="checkbox" value="1" /> Test Update iTunes Listing (recommended)</p>
<?php if( $FeedSettings['itunes_url'] ) {

		$ping_url = str_replace(
			array(	'https://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id=',
								'https://www.itunes.com/podcast?id=',
								'http://www.itunes.com/podcast?id='),
			'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=', $FeedSettings['itunes_url']);
?>
<p>You may also update your iTunes listing by using the following link: <a href="#" onclick="javascript: window.open('<?php echo $ping_url; ?>'); return false;" title="Update iTunes Listing in New Window">Update iTunes Listing in New Window</a></p>

<?php
		if( preg_match('/id=(\d+)/', $FeedSettings['itunes_url'], $matches) )
		{
			$FEEDID = $matches[1];
			$Logging = get_option('powerpress_log');
			
			if( isset($Logging['itunes_ping_'. $FEEDID ]) )
			{
				$PingLog = $Logging['itunes_ping_'. $FEEDID ];
?>
		<h3>Latest Update iTunes Listing Status: <?php if( $PingLog['success'] ) echo '<span style="color: #006505;">Successful</span>'; else echo '<span style="color: #f00;">Error</span>';  ?></h3>
		<div style="font-size: 85%; margin-left: 20px;">
			<p>
				<?php echo sprintf( __('iTunes notified on %s at %s'), date(get_option('date_format'), $PingLog['timestamp']), date(get_option('time_format'), $PingLog['timestamp'])); ?>
<?php
					if( $PingLog['post_id'] )
					{
						$post = get_post($PingLog['post_id']);
						if( $post )
							echo __(' for post: ') . htmlspecialchars($post->post_title); 
					}
?>
			</p>
<?php if( $PingLog['success'] ) { ?>
			<p>Feed pulled by iTunes: <?php echo $PingLog['feed_url']; ?>
			</p>
			<?php
				
			?>
<?php } else { ?>
			<p>Error: <?php echo htmlspecialchars($PingLog['content']); ?></p>
<?php } ?>
		</div>
<?php
			}
		}
?>

<?php } ?>

</td>
</tr>
<?php
	}
?>



<tr valign="top">
<th scope="row">
<?php _e("FeedBurner Feed URL"); ?>
</th>
<td>
<input type="text" name="Feed[feed_redirect_url]"style="width: 60%;"  value="<?php echo $FeedSettings['feed_redirect_url']; ?>" maxlength="100" />  (leave blank to use current feed)
<p>Use this option to redirect this feed to a hosted feed service such as <a href="http://www.feedburner.com/" target="_blank">FeedBurner</a>.</p>
<?php
if( $cat_ID )
	$link = get_category_feed_link($cat_ID);
else
	$link = get_feed_link($feed_slug);
	
if( strstr($link, '?') )
	$link .= "&redirect=no";
else
	$link .= "?redirect=no";
?>
<p>Bypass Redirect URL: <a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a></p>
</td>
</tr>

<?php } // End $feed_slug ?>

<tr valign="top">
<th scope="row">
<?php _e("Show the most recent"); ?>
</th>
<td>
<input type="text" name="Feed[posts_per_rss]"style="width: 50px;"  value="<?php echo $FeedSettings['posts_per_rss']; ?>" maxlength="5" />  episodes / posts per feed (leave blank to use blog default: <?php form_option('posts_per_rss'); ?>)
<?php if( !$feed_slug && !$cat_ID ) { ?>
<p style="margin-top: 5px; margin-bottomd: 0;">Note: Setting above applies only to podcast channel feeds</p>
<?php } ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("RSS2 Image"); ?> <br />
</th>
<td>
<input type="text" id="rss2_image" name="Feed[rss2_image]" style="width: 60%;" value="<?php echo $FeedSettings['rss2_image']; ?>" maxlength="250" />
<a href="#" onclick="javascript: window.open( document.getElementById('rss2_image').value ); return false;">preview</a>

<p>Place the URL to the RSS image above. e.g. http://mysite.com/images/rss.jpg</p>
<p>RSS image should be at least 88 and at most 144 pixels wide and at least 31 and at most 400 pixels high in either .gif, .jpg and .png format. A square 144 x 144 pixel image is recommended.</p>

<?php if( $SupportUploads ) { ?>
<p><input name="rss2_image_checkbox" type="checkbox" onchange="powerpress_show_field('rss_image_upload', this.checked)" value="1" /> Upload new image</p>
<div style="display:none" id="rss_image_upload">
	<label for="rss2_image">Choose file:</label><input type="file" name="rss2_image_file"  />
</div>
<?php } ?>
</td>
</tr>

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row">

<?php _e("Feed Language"); ?></th>
<td>
<select name="Feed[rss_language]" class="bpp_input_med">
<?php
$Languages = powerpress_languages();

echo '<option value="">Blog Default Language</option>';
while( list($value,$desc) = each($Languages) )
	echo "\t<option value=\"$value\"". ($FeedSettings['rss_language']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";
?>
</select>
<?php
	$rss_language = get_option('rss_language');
if( isset($Languages[ $rss_language ]) )
{
?>
 Blog Default: <?php echo $Languages[ $rss_language ]; ?>
 <?php } ?>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("Copyright"); ?>
</th>
<td>
<input type="text" name="Feed[copyright]" style="width: 60%;" value="<?php echo $FeedSettings['copyright']; ?>" maxlength="250" />
</td>
</tr>
<?php
	} // end advanced_mode
?>
</table>
<?php
}


function powerpressadmin_edit_basics_feed($General, $FeedSettings, $feed_slug, $cat_ID = false)
{

	if( $cat_ID )
	{
?>
	<h3><?php echo __('Media Statistics', 'powerpress'); ?></h3>
	<p>
	<?php echo __('Enter your Redirect URL issued by your media statistics service provider below.', 'powerpress'); ?>
	</p>

	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php echo __('Redirect URL', 'powerpress'); ?> 
	</th>
	<td>
	<input type="text" style="width: 60%;" name="Feed[redirect]" value="<?php echo $FeedSettings['redirect']; ?>" maxlength="250" />
	<p><?php echo __('Note: Category Media Redirect URL is applied to category feeds and pages only. The redirect will also apply to single pages if this is the only category associated with the blog post.', 'powerpress'); ?></p>
	</td>
	</tr>
	</table>
<?php
	}
	else // end if category, else channel...
	{
?>

<h3>Episode Entry Box</h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php _e("Background Color"); ?>
</th>
<td>
<input type="text" id="episode_background_color" name="EpisodeBoxBGColor[<?php echo $feed_slug; ?>]" style="width: 100px; float:left; border: 1px solid #333333; <?php if( !empty($General['episode_box_background_color'][ $feed_slug ]) ) echo 'background-color: '.$General['episode_box_background_color'][ $feed_slug ]; ?>;" value="<?php if( !empty($General['episode_box_background_color'][ $feed_slug ]) )  echo $General['episode_box_background_color'][ $feed_slug ]; ?>" maxlength="10" onblur="jQuery('#episode_background_color').css({'background-color' : this.value });" />
<div style="background-color: #FFDFEF;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FFDFEF'; jQuery('#episode_background_color').css({'background-color' :'#FFDFEF' });"></div>
<div style="background-color: #FBECD8;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FBECD8'; jQuery('#episode_background_color').css({'background-color' :'#FBECD8' });"></div>
<div style="background-color: #FFFFCC;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FFFFCC'; jQuery('#episode_background_color').css({'background-color' :'#FFFFCC' });"></div>
<div style="background-color: #DFFFDF;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#DFFFDF'; jQuery('#episode_background_color').css({'background-color' :'#DFFFDF' });"></div>

<div style="background-color: #EBFFFF;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#EBFFFF'; jQuery('#episode_background_color').css({'background-color' :'#EBFFFF' });"></div>
<div style="background-color: #D9E0EF;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#D9E0EF'; jQuery('#episode_background_color').css({'background-color' :'#D9E0EF' });"></div>
<div style="background-color: #EBE0EB;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#EBE0EB'; jQuery('#episode_background_color').css({'background-color' :'#EBE0EB' });"></div>
 &nbsp; (leave blank for default)

<p class="clear">Use a distinctive background color for this podcast channel's episode box.</p>
</td>
</tr>
</table>

<!-- password protected feed option -->

<?php
		if( @$General['premium_caps'] && $feed_slug && $feed_slug != 'podcast' )
		{
?>
<h3>Password Protect Podcast Channel</h3>
<p>
	Require visitors to have membership to your blog in order to gain access to this channel's Premium Content.
</p>
<table class="form-table">
<tr valign="top">
<th scope="row">

<?php _e("Protect Content"); ?></th>
<td>
	<p style="margin-top: 5px;"><input type="checkbox" name="ProtectContent" value="1" <?php echo ($FeedSettings['premium']?'checked ':''); ?> onchange="powerpress_toggle_premium_content(this.checked);" /> Require user to be signed-in to access feed.</p>
<?php ?>
	<div style="margin-left: 20px; display: <?php echo ($FeedSettings['premium']?'block':'none'); ?>;" id="premium_role">User must have 
<select name="Feed[premium]" class="bpp_input_med">
<?php
			$caps = powerpress_admin_capabilities();
			$actual_premium_value = $FeedSettings['premium'];
			if( !isset($FeedSettings['premium']) || $FeedSettings['premium'] == '' )
				$actual_premium_value = 'premium_content';
			
			echo '<option value="">None</option>';
			while( list($value,$desc) = each($caps) )
				echo "\t<option value=\"$value\"". ($actual_premium_value==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";
?>
</select> capability.</div>
</td>
</tr>
</table>
<div id="protected_content_message" style="display: <?php echo ($FeedSettings['premium']?'block':'none'); ?>;">
<script language="Javascript" type="text/javascript">
function powerpress_toggle_premium_content(enabled)
{
	jQuery('#premium_role').css('display', (enabled?'block':'none') );
	jQuery('#protected_content_message').css('display', (enabled?'block':'none') );
}	
function powerpress_premium_label_append_signin_link()
{
	jQuery('#premium_label').val( jQuery('#premium_label').val() + '<a href="<?php echo get_settings('siteurl'); ?>/wp-login.php" title="Sign In">Sign In<\/a>'); 
}
function powerpress_default_premium_label(event)
{
	if( confirm('Use default label, are you sure?') )
	{
		jQuery('#premium_label_custom').css('display', (this.checked==false?'block':'none') );
		jQuery('#premium_label').val('');
	}
	else
	{
		return false;
	}
	return true;
}
</script>
	<table class="form-table">
	<tr valign="top">
	<th scope="row">
	<?php _e("Unauthorized Label"); ?>
	</th>
	<td>
	<p style="margin-top: 5px;"><input type="radio" name="PremiumLabel" value="0" <?php echo ($FeedSettings['premium_label']==''?'checked ':''); ?> onclick="return powerpress_default_premium_label(this)" />
		Use default label:
	</p>
	<p style="margin-left: 20px;">
	<?php echo $FeedSettings['title']; ?>: <a href="<?php echo get_settings('siteurl'); ?>/wp-login.php" target="_blank" title="Protected Content">(Protected Content)</a>
	</p>
	<p style="margin-top: 5px;"><input type="radio" name="PremiumLabel" id="premium_label_1" value="1" <?php echo ($FeedSettings['premium_label']!=''?'checked ':''); ?> onchange="jQuery('#premium_label_custom').css('display', (this.checked?'block':'none') );" />
		Use a custom label:
	</p>
	
	<div id="premium_label_custom" style="margin-left: 20px; display: <?php echo ($FeedSettings['premium_label']!=''?'block':'none'); ?>;">
	<textarea name="Feed[premium_label]" id="premium_label" style="width: 80%; height: 65px; margin-bottom: 0; padding-bottom: 0;"><?php echo htmlspecialchars(@$FeedSettings['premium_label']); ?></textarea>
		<div style="width: 80%; font-size: 85%; text-align: right;">
			<a href="#" onclick="powerpress_premium_label_append_signin_link();return false;">Add sign in link to message</a>
		</div>
		<p style="width: 80%;">
			Label above appears in place of the in-page player and links when
			the current signed-in user does not have access to the protected content.
		</p>
	</div>
	</td>
	</tr>
	</table>
</div>
<?php
		}
	} // else if channel
}

function powerpressadmin_edit_appearance_feed($General,  $FeedSettings, $feed_slug)
{
	// Appearance Settings
?>
<h3>Appearance Settings</h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php _e("Disable Player"); ?>
</th>
<td>
	<input name="DisablePlayerFor" type="checkbox" <?php if( isset($General['disable_player'][$feed_slug]) ) echo 'checked '; ?> value="1" /> Do not display web player or links for this podcast channel.
	<input type="hidden" name="UpdateDisablePlayer" value="<?php echo $feed_slug; ?>" />
</td>
</tr>
</table>
<?php

}

function powerpressadmin_edit_itunes_feed($FeedSettings, $General, $feed_slug=false, $cat_ID=false)
{
	$SupportUploads = powerpressadmin_support_uploads();
	if( !isset($FeedSettings['itunes_subtitle']) )
		$FeedSettings['itunes_subtitle'] = '';
	if( !isset($FeedSettings['itunes_summary']) )
		$FeedSettings['itunes_summary'] = '';
	if( !isset($FeedSettings['itunes_keywords']) )
		$FeedSettings['itunes_keywords'] = '';	
	if( !isset($FeedSettings['itunes_cat_1']) )
		$FeedSettings['itunes_cat_1'] = '';
	if( !isset($FeedSettings['itunes_cat_2']) )
		$FeedSettings['itunes_cat_2'] = '';
	if( !isset($FeedSettings['itunes_cat_3']) )
		$FeedSettings['itunes_cat_3'] = '';
	if( !isset($FeedSettings['itunes_explicit']) )
		$FeedSettings['itunes_explicit'] = 0;
	if( !isset($FeedSettings['itunes_talent_name']) )
		$FeedSettings['itunes_talent_name'] = '';
	if( !isset($FeedSettings['email']) )
		$FeedSettings['email'] = '';
	if( !isset($FeedSettings['itunes_new_feed_url_podcast']) )
		$FeedSettings['itunes_new_feed_url_podcast'] = '';
	if( !isset($FeedSettings['itunes_new_feed_url']) )
		$FeedSettings['itunes_new_feed_url'] = '';
	
?>
<h3>iTunes Feed Settings</h3>
<table class="form-table">
	
<?php
	if( !empty($General['advanced_mode']) )
	{
?>
<tr valign="top">
<th scope="row">
<?php _e("iTunes Program Subtitle"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_subtitle]"style="width: 60%;"  value="<?php echo $FeedSettings['itunes_subtitle']; ?>" maxlength="250" />
</td>
</tr>

<tr valign="top">
<th scope="row">

<?php _e("iTunes Program Summary"); ?></th>
<td>
<p style="margin-top: 5px;">Your summary may not contain HTML and cannot exceed 4,000 characters in length.</p>

<textarea name="Feed[itunes_summary]" rows="5" style="width:80%;" ><?php echo $FeedSettings['itunes_summary']; ?></textarea>
</td>
</tr>

<tr valign="top">
<th scope="row">

<?php _e("iTunes Episode Summary"); ?></th>
<td>

<?php if ( version_compare( '5', phpversion(), '<=' ) ) { ?>
<div><input type="checkbox" name="Feed[enhance_itunes_summary]" value="1" <?php echo ( !empty($FeedSettings['enhance_itunes_summary'])?'checked ':''); ?>/> Optimize iTunes Summary from Blog Posts (<a href="http://help.blubrry.com/blubrry-powerpress/settings/enhanced-itunes-summary/" target="_blank">What's this</a>)
</div>
<p>
	Creates a friendlier view of your post/episode content by converting web links and images to clickable links in iTunes.
</p>
<?php } else { ?>

	<strong>Option Not Available</strong>

<p>
	This feature requires PHP version 5 or newer.
	Your server's version of PHP is <?php echo phpversion(); ?>. 
</p>
<?php } ?>
</td>
</tr>
<?php
	}
?>
<tr valign="top">
<th scope="row">
<?php _e("iTunes Program Keywords"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_keywords]" style="width: 60%;"  value="<?php echo $FeedSettings['itunes_keywords']; ?>" maxlength="250" />
<p>Enter up to 12 keywords separated by commas.</p>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Category"); ?> 
</th>
<td>
<select name="Feed[itunes_cat_1]" style="width: 60%;">
<?php
$linkoptions = array("On page", "Disable");

$Categories = powerpress_itunes_categories(true);

echo '<option value="">Select Category</option>';

while( list($value,$desc) = each($Categories) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_cat_1']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

reset($Categories);
?>
</select>
</td>
</tr>

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row">
<?php _e("iTunes Category 2"); ?> 
</th>
<td>
<select name="Feed[itunes_cat_2]" style="width: 60%;">
<?php
$linkoptions = array("On page", "Disable");

echo '<option value="">Select Category</option>';

while( list($value,$desc) = each($Categories) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_cat_2']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

reset($Categories);

?>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Category 3"); ?> 
</th>
<td>
<select name="Feed[itunes_cat_3]" style="width: 60%;">
<?php
$linkoptions = array("On page", "Disable");

echo '<option value="">Select Category</option>';

while( list($value,$desc) = each($Categories) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_cat_3']==$value?' selected':''). ">".htmlspecialchars($desc)."</option>\n";

reset($Categories);
?>
</select>
</td>
</tr>
<?php
	} // end advanced_mode
?>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Explicit"); ?> 
</th>
<td>
<select name="Feed[itunes_explicit]" class="bpp_input_med">
<?php
$explicit = array(0=>"no - display nothing", 1=>"yes - explicit content", 2=>"clean - no explicit content");

while( list($value,$desc) = each($explicit) )
	echo "\t<option value=\"$value\"". ($FeedSettings['itunes_explicit']==$value?' selected':''). ">$desc</option>\n";

?>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Image"); ?> 
</th>
<td>
<input type="text" id="itunes_image" name="Feed[itunes_image]" style="width: 60%;" value="<?php echo $FeedSettings['itunes_image']; ?>" maxlength="250" />
<a href="#" onclick="javascript: window.open( document.getElementById('itunes_image').value ); return false;">preview</a>

<p>Place the URL to the iTunes image above. e.g. http://mysite.com/images/itunes.jpg<br /><br />iTunes prefers square .jpg or .png images that are at 600 x 600 pixels (prevously 300 x 300), which is different than what is specified for the standard RSS image.</p>

<p>Note: It may take some time (days or even a month) for iTunes to cache modified or replaced iTunes images in the iTunes Podcast Directory. Please contact <a href="http://www.apple.com/support/itunes/">iTunes Support</a> if you are having issues with your image changes not appearing in iTunes.</p>
<?php if( $SupportUploads ) { ?>
<p><input name="itunes_image_checkbox" type="checkbox" onchange="powerpress_show_field('itunes_image_upload', this.checked)" value="1" /> Upload new image </p>
<div style="display:none" id="itunes_image_upload">
	<label for="itunes_image">Choose file:</label><input type="file" name="itunes_image_file"  />
</div>
<?php } ?>
</td>
</tr>

<?php
	if( @$General['advanced_mode'] )
	{
?>
<tr valign="top">
<th scope="row">
<?php _e("iTunes Talent Name"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_talent_name]"style="width: 60%;"  value="<?php echo $FeedSettings['itunes_talent_name']; ?>" maxlength="250" /><br />
<div><input type="checkbox" name="Feed[itunes_author_post]" value="1" <?php echo ( !empty($FeedSettings['itunes_author_post'])?'checked ':''); ?>/> Use blog post author's name for individual episodes.</div>

</td>
</tr>
<?php
	}
?>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Email"); ?>
</th>
<td>
<input type="text" name="Feed[email]"  style="width: 60%;" value="<?php echo $FeedSettings['email']; ?>" maxlength="250" />
<div>(<?php echo __('iTunes will email this address when your podcast is accepted into the iTunes Directory.'); ?>)</div>
</td>
</tr>

<?php
	if( @$General['advanced_mode'] )
	{
?>
	<tr valign="top">
	<th scope="row" >

<?php _e("iTunes New Feed URL"); ?></th> 
	<td>
		<div id="new_feed_url_step_1" style="display: <?php echo ( !empty($FeedSettings['itunes_new_feed_url']) || !empty($FeedSettings['itunes_new_feed_url_podcast'])  ?'none':'block'); ?>;">
			 <p style="margin-top: 5px;"><strong><a href="#" onclick="return powerpress_new_feed_url_prompt();"><?php echo __('Set iTunes New Feed URL'); ?></a></strong></p>
		</div>
		<div id="new_feed_url_step_2" style="display: <?php echo ( !empty($FeedSettings['itunes_new_feed_url']) || !empty($FeedSettings['itunes_new_feed_url_podcast'])  ?'block':'none'); ?>;">
			<p style="margin-top: 5px;"><strong><?php echo __('WARNING: Changes made here are permanent. If the New Feed URL entered is incorrect, you will lose subscribers and will no longer be able to update your listing in the iTunes Store.'); ?></strong></p>
			<p><strong><?php echo __('DO NOT MODIFY THIS SETTING UNLESS YOU ABSOLUTELY KNOW WHAT YOU ARE DOING.'); ?></strong></p>
			<p>
				<?php echo htmlspecialchars( __('Apple recommends you maintain the <itunes:new-feed-url> tag in your feed for at least two weeks to ensure that most subscribers will receive the new New Feed URL.') ); ?>
			</p>
			<p>
			<?php 
			$FeedName = 'Main RSS2 feed';
			$FeedURL = get_feed_link('rss2');
			if( $cat_ID )
			{
				$category = get_category_to_edit($cat_ID);
				$FeedName = sprintf( __('%s category feed'), htmlspecialchars($category->name) );
				$FeedURL = get_category_feed_link($cat_ID);
			}
			else if( $feed_slug )
			{
				if( !empty($General['custom_feeds'][ $feed_slug ]) )
					$FeedName = $General['custom_feeds'][ $feed_slug ];
				else
					$FeedName = __('Podcast');
				$FeedName = trim($FeedName).' '.__('feed');
				$FeedURL = get_feed_link($feed_slug);
			}
			
			echo sprintf(__('The New Feed URL value below will be applied to the %s (%s).'), $FeedName, $FeedURL);
?>
			</p>
			<p style="margin-bottom: 0;">
				<label style="width: 25%; float:left; display:block; font-weight: bold;">New Feed URL</label>
				<input type="text" name="Feed[itunes_new_feed_url]"style="width: 55%;"  value="<?php echo $FeedSettings['itunes_new_feed_url']; ?>" maxlength="250" />
			</p>
			<p style="margin-left: 25%;margin-top: 0;font-size: 90%;">(Leave blank for no New Feed URL)</p>
			
			<p>More information regarding the iTunes New Feed URL is available <a href="http://www.apple.com/itunes/whatson/podcasts/specs.html#changing" target="_blank" title="Apple iTunes Podcasting Specificiations">here</a>.</p>
			<p>
<?php
			if( !$cat_ID && !$feed_slug )
			{
				if( empty($General['channels']) )
					echo sprintf(__('Please activate the \'Custom Podcast Channels\' Advanced Option to set the new-feed-url for your podcast only feed (%s)'), get_feed_link('podcast') );
				else
					echo sprintf(__('Please navigate to the \'Custom Podcast Channels\' section to set the new-feed-url for your podcast only feed (%s)'), get_feed_link('podcast') );
			}
?>
			</p>
		</div>
	</td>
	</tr>
<?php
	} // end advanced_mode
?>

</table>
<?php
}
	
?>