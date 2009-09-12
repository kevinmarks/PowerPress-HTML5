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
// powerpressadmin_editfeed.php
function powerpress_admin_editfeed($feed_slug=false, $cat_ID =false)
{
	$UploadArray = wp_upload_dir();
	$upload_path =  rtrim( substr($UploadArray['path'], 0, 0 - strlen($UploadArray['subdir']) ), '\\/').'/powerpress/';
	
	if( !file_exists($upload_path) )
		$SupportUploads = @mkdir($upload_path, 0777);
	else
		$SupportUploads = true;
		
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
		$FeedTitle = sprintf( '%s: %s', $FeedTitle, $General['custom_feeds'][$feed_slug]) ;
		echo sprintf('<input type="hidden" name="feed_slug" value="%s" />', $feed_slug);
	}
	else if( $cat_ID )
	{
		$category = get_category_to_edit($cat_ID);
		$FeedTitle = sprintf( 'Category %s: %s', $FeedTitle, $category->name) ;
		echo sprintf('<input type="hidden" name="cat" value="%s" />', $cat_ID);
	}
	
	$AdvancedMode = $General['advanced_mode'];
?>
<h2><?php echo $FeedTitle; ?></h2>
<?php if( $feed_slug ) { ?>
<input type="hidden" name="action" value="powerpress-save-customfeed" />
<p style="margin-bottom: 0;">
	<?php _e('Configure your custom podcast feed.'); ?>
</p>
<?php } else if( $cat_ID ) { ?>
<input type="hidden" name="action" value="powerpress-save-categoryfeedsettings" />
<p style="margin-bottom: 0;">
	<?php _e('Configure your category feed to support podcasting.'); ?>
</p>
<?php } else { ?>
<input type="hidden" name="action" value="powerpress-save-feedsettings" />
<p style="margin-bottom: 0;">
	<?php _e('Configure your feeds to support podcasting.'); ?>
</p>
<?php } ?>
<table class="form-table">
<?php if( !$feed_slug && !$cat_ID ) { ?>
<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">

<?php echo __('Enhance Feeds'); ?></th> 
<td>
	<ul>
		<li><label><input type="radio" name="Feed[apply_to]" value="1" <?php if( $FeedSettings['apply_to'] == 1 ) echo 'checked'; ?> /> Enhance All Feeds</label> (Recommended)</li>
		<li>
			<ul>
				<li>Adds podcasting support to all feeds</li>
				<li>Allows for Category Casting (Visitors may subscribe to your categories as a podcast)</li>
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
				<li>Feed Settings below will only apply to your podcast only feeds</li>
			</ul>
		</li>
	</ul>
		
<?php /* ?>
<select name="Feed[apply_to]" class="bpp_input_large"  style="width: 60%;">
<?php
$applyoptions = array(1=>'All RSS2 Feeds (category / tag specific podcast feeds)', 2=>'Main RSS2 Feed only', 0=>'Disable (settings below ignored)');

while( list($value,$desc) = each($applyoptions) )
	echo "\t<option value=\"$value\"". ($FeedSettings['apply_to']==$value?' selected':''). ">$desc</option>\n";
	
?>
</select>
<p>Select 'All RSS Feeds' to include podcast episodes in all feeds such as category and tag feeds.</p>
<p>Select 'Main RSS2 Feed only' to include podcast episodes only in your primary RSS2 feed.</p>
<p>Select 'Disable' to prevent Blubrry PowerPress from adding podcast episodes to any feeds.</p>
<?php */ ?>
</td>
</tr>
<?php } // End AdvancedMode ?>

<tr valign="top">
<th scope="row">

<?php _e("Main Site Feed"); ?></th> 
<td>
	<p style="margin-top: 5px; margin-bottom: 0;">Main RSS2 Feed: <a href="<?php echo get_bloginfo('rss2_url'); ?>" title="Main RSS 2 Feed" target="_blank"><?php echo get_bloginfo('rss2_url'); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_bloginfo('rss2_url')); ?>" title="Validate Feed" target="_blank">validate</a></p>
</td>
</tr>


<tr valign="top">
<th scope="row">

<?php _e("Custom Podcast Feeds"); ?></th> 
<td>
<?php
	
	$General = get_option('powerpress_general');
	$Feeds = array('podcast'=>'Special Podcast only Feed');
	if( isset($General['custom_feeds']['podcast']) )
		$Feeds = $General['custom_feeds'];
	else if( is_array($General['custom_feeds']) )
		$Feeds += $General['custom_feeds'];
		
	while( list($feed_slug, $feed_title) = each($Feeds) )
	{
		$edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_customfeeds.php&amp;action=powerpress-editfeed&amp;feed_slug=') . $feed_slug;
?>
<p><?php echo $feed_title; ?>: <a href="<?php echo get_feed_link($feed_slug); ?>" title="<?php echo $feed_title; ?>" target="_blank"><?php echo get_feed_link($feed_slug); ?></a>
| <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_feed_link($feed_slug)); ?>" title="Validate Feed" target="_blank">validate</a>
	<?php if( $AdvancedMode ) { ?>
	| <a href="<?php echo $edit_link; ?>" title="Edit Feed">edit</a>
	<?php } ?>
</p>
<?php } ?>
</td>
</tr>
<?php } else { // Else if( $feed_slug)  ?>

<tr valign="top">
<th scope="row">
<?php _e("Feed URL"); ?> <br />
</th>
<td>
<?php if( $cat_ID ) { ?>
<p style="margin-top: 0;"><a href="<?php echo get_category_feed_link($cat_ID); ?>" target="_blank"><?php echo get_category_feed_link($cat_ID); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode( str_replace('&amp;', '&', get_category_feed_link($cat_ID))); ?>" target="_blank"><?php _e('validate'); ?></a></p>
<?php } else { ?>
<p style="margin-top: 0;"><a href="<?php echo get_feed_link($feed_slug); ?>" target="_blank"><?php echo get_feed_link($feed_slug); ?></a> | <a href="http://www.feedvalidator.org/check.cgi?url=<?php echo urlencode(get_feed_link($feed_slug)); ?>" target="_blank"><?php _e('validate'); ?></a></p>
<?php } ?>
</td>
</tr>


<tr valign="top">
<th scope="row">
<?php _e("Feed Title"); ?>
</th>
<td>
<input type="text" name="Feed[title]"style="width: 60%;"  value="<?php echo $FeedSettings['title']; ?>" maxlength="250" /> 
<?php if( $cat_ID ) { ?>
(leave blank to use category title)
<?php } else { ?>
(leave blank to use blog title)
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
<?php _e("Feed Landing Page"); ?> <br />
</th>
<td>
<input type="text" name="Feed[url]"style="width: 60%;"  value="<?php echo $FeedSettings['url']; ?>" maxlength="250" />  (optional)
<?php if( $cat_ID ) { ?>
<p>Leave blank to use category page: <?php echo get_category_link($cat_ID); ?></p>
<?php } else { ?>
<p>e.g. <?php echo get_bloginfo('home'); ?>/custom-page/</p>
<?php } ?>
</td>
</tr>

<?php

	if( $General['ping_itunes'] && $feed_slug != 'podcast' )
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

<p><input name="TestiTunesPing" type="checkbox" value="1" /> Test iTunes Ping (recommended)</p>
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
<p>You may also ping iTunes by using the following link: <a href="#" onclick="javascript: window.open('<?php echo $ping_url; ?>'); return false;" title="Ping iTunes in New Window">Ping iTunes in New Window</a></p>

<?php
		if( preg_match('/id=(\d+)/', $FeedSettings['itunes_url'], $matches) )
		{
			$FEEDID = $matches[1];
			$Logging = get_option('powerpress_log');
			
			if( isset($Logging['itunes_ping_'. $FEEDID ]) )
			{
				$PingLog = $Logging['itunes_ping_'. $FEEDID ];
?>
		<h3>Latest iTunes Ping Status: <?php if( $PingLog['success'] ) echo '<span style="color: #006505;">Successful</span>'; else echo '<span style="color: #f00;">Error</span>';  ?></h3>
		<div style="font-size: 85%; margin-left: 20px;">
			<p>
				<?php echo sprintf( __('iTunes pinged on %s at %s'), date(get_option('date_format'), $PingLog['timestamp']), date(get_option('time_format'), $PingLog['timestamp'])); ?>
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
<p style="margin-top: 5px; margin-bottomd: 0;">Note: Setting above applies only to custom podcast feeds</p>
<?php } ?>
</td>
</tr>

<?php if( $AdvancedMode ) { ?>
	<tr valign="top">
	<th scope="row" >

<?php _e("iTunes New Feed URL"); ?></th> 
	<td>
		<div id="new_feed_url_step_1" style="display: <?php echo ($FeedSettings['itunes_new_feed_url'] || $FeedSettings['itunes_new_feed_url_podcast']  ?'none':'block'); ?>;">
			 <p style="margin-top: 5px;"><a href="#" onclick="return powerpress_new_feed_url_prompt();">Click here</a> if you need to change the Feed URL for iTunes subscribers.</p>
		</div>
		<div id="new_feed_url_step_2" style="display: <?php echo ($FeedSettings['itunes_new_feed_url'] || $FeedSettings['itunes_new_feed_url_podcast']  ?'block':'none'); ?>;">
			<p style="margin-top: 5px;"><strong>WARNING: Changes made here are permanent. If the New Feed URL entered is incorrect, you will lose subscribers and will no longer be able to update your listing in the iTunes Store.</strong></p>
			<p><strong>DO NOT MODIFY THIS SETTING UNLESS YOU ABSOLUTELY KNOW WHAT YOU ARE DOING.</strong></p>
			<p>
				Apple recommends you maintain the &lt;itunes:new-feed-url&gt; tag in your feed for at least two weeks to ensure that most subscribers will receive the new New Feed URL.
			</p>
			<p>
				Example URL: <?php echo get_feed_link( ($feed_slug?$feed_slug:'podcast') ); ?>
			</p>
			<p style="margin-bottom: 0;">
				<label style="width: 25%; float:left; display:block; font-weight: bold;">New Feed URL</label>
				<input type="text" name="Feed[itunes_new_feed_url]"style="width: 55%;"  value="<?php echo $FeedSettings['itunes_new_feed_url']; ?>" maxlength="250" />
			</p>
			<p style="margin-left: 25%;margin-top: 0;font-size: 90%;">(Leave blank for no New Feed URL)</p>
			<p>More information regarding the iTunes New Feed URL is available <a href="http://www.apple.com/itunes/whatson/podcasts/specs.html#changing" target="_blank" title="Apple iTunes Podcasting Specificiations">here</a>.</p>
		</div>
	</td>
	</tr>


<tr valign="top">
<th scope="row">

<?php _e("iTunes Summary"); ?></th>
<td>
<p style="margin-top: 5px;">Your summary may not contain HTML and cannot exceed 4,000 characters in length.</p>

<textarea name="Feed[itunes_summary]" rows="5" style="width:80%;" ><?php echo $FeedSettings['itunes_summary']; ?></textarea>
<?php if ( version_compare( '5', phpversion(), '>=' ) ) { ?>
<div><input type="checkbox" name="Feed[enhance_itunes_summary]" value="1" <?php echo ($FeedSettings['enhance_itunes_summary']?'checked ':''); ?>/> Enhance iTunes Summary from Blog Posts (<a href="http://help.blubrry.com/blubrry-powerpress/settings/enhanced-itunes-summary/" target="_blank">What's this</a>)
<?php } ?>
</div>
</td>
</tr>

<tr valign="top">
<th scope="row">
<?php _e("iTunes Program Subtitle"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_subtitle]"style="width: 60%;"  value="<?php echo $FeedSettings['itunes_subtitle']; ?>" maxlength="250" />
</td>
</tr>
<?php } else { // End AdvancedMode ?>
<input type="hidden" name="Feed[enhance_itunes_summary]" value="<?php echo ($FeedSettings['enhance_itunes_summary']?'1':'0'); ?>" />
<?php } ?>

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

<?php if( $AdvancedMode ) { ?>
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
<?php } // End AdvancedMode ?>

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

<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">

<?php _e("Feed Language"); ?></th>
<td>
<?php
	


?>
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
<?php _e("Talent Name"); ?> <br />
</th>
<td>
<input type="text" name="Feed[itunes_talent_name]"style="width: 60%;"  value="<?php echo $FeedSettings['itunes_talent_name']; ?>" maxlength="250" /><br />
<div><input type="checkbox" name="Feed[itunes_author_post]" value="1" <?php echo ($FeedSettings['itunes_author_post']?'checked ':''); ?>/> Use blog post author's name for individual episodes.

</td>
</tr>



<?php } // End AdvancedMode ?>

<tr valign="top">
<th scope="row">
<?php _e("Email"); ?>
</th>
<td>
<input type="text" name="Feed[email]"  style="width: 60%;" value="<?php echo $FeedSettings['email']; ?>" maxlength="250" />
</td>
</tr>

<?php if( $AdvancedMode ) { ?>
<tr valign="top">
<th scope="row">
<?php _e("Copyright"); ?>
</th>
<td>
<input type="text" name="Feed[copyright]" style="width: 60%;" value="<?php echo $FeedSettings['copyright']; ?>" maxlength="250" />
</td>
</tr>
<?php } // End AdvancedMode ?>
<?php if( $feed_slug ) { ?>
<tr valign="top">
<th scope="row">
<?php _e("Episode Box Background Color"); ?>
</th>
<td>
<input type="text" id="episode_background_color" name="EpisodeBoxBGColor[<?php echo $feed_slug; ?>]" style="width: 100px; float:left; border: 1px solid #333333; <?php if( !empty($General['episode_box_background_color'][ $feed_slug ]) ) echo 'background-color: '.$General['episode_box_background_color'][ $feed_slug ]; ?>;" value="<?php if( !empty($General['episode_box_background_color'][ $feed_slug ]) )  echo $General['episode_box_background_color'][ $feed_slug ]; ?>" maxlength="10" onblur="jQuery('#episode_background_color').css({'background-color' : this.value });" />
<style type="text/css">
.powerpress_color_box {
	float: left;
	width: 16px;
	height: 16px;
	cursor: pointer;
	margin: 4px 1px;
	border: 1px solid #666666;
	
}
</style>
<div style="background-color: #FF99CC;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FFCCCC'; jQuery('#episode_background_color').css({'background-color' :'#FF99CC' });"></div>
<div style="background-color: #FADCB3;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FFCC99'; jQuery('#episode_background_color').css({'background-color' :'#FADCB3' });"></div>
<div style="background-color: #FFFF99;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FFFFCC'; jQuery('#episode_background_color').css({'background-color' :'#FFFF99' });"></div>
<div style="background-color: #CCFFCC;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#99FF99'; jQuery('#episode_background_color').css({'background-color' :'#CCFFCC' });"></div>
<div style="background-color: #CCFFFF;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#CCFFFF'; jQuery('#episode_background_color').css({'background-color' :'#CCFFFF' });"></div>
<div style="background-color: #C2D1F0;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#CCCCFF'; jQuery('#episode_background_color').css({'background-color' :'#C2D1F0' });"></div>
<div style="background-color: #E1C7E1;" class="powerpress_color_box" onclick="document.getElementById('episode_background_color').value='#FFCCFF'; jQuery('#episode_background_color').css({'background-color' :'#E1C7E1' });"></div>
 &nbsp; (leave blank for default)

<div class="clear"></div>
</td>
</tr>
<?php } // end customm feeds ?>
</table>
<?php
	}
	
?>