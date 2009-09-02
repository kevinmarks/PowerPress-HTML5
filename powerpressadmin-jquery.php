<?php
	// jQuery specific functions and code go here..
	
	// Credits:
	/*
	FOLDER ICON provided by Silk icon set 1.3 by Mark James link: http://www.famfamfam.com/lab/icons/silk/
	*/
	
function powerpress_add_blubrry_redirect($program_keyword)
{
	$Settings = powerpress_get_settings('powerpress_general');
	$RedirectURL = 'http://media.blubrry.com/'.$program_keyword;
	$NewSettings = array();
	
	// redirect1
	// redirect2
	// redirect3
	for( $x = 1; $x <= 3; $x++ )
	{
		$field = sprintf('redirect%d', $x);
		if( $Settings[$field] == '' )
		{
			$NewSettings[$field] = $RedirectURL.'/';
			break;
		}
		else if( stristr($Settings[$field], $RedirectURL ) )
		{
			return; // Redirect already implemented
		}
	}
	if( count($NewSettings) > 0 )
		powerpress_save_settings($NewSettings);
}

function powerpress_admin_jquery_init()
{
	$Settings = false; // Important, never remove this
	$Settings = get_option('powerpress_general');
	
	$Error = false;

	$Programs = false;
	$Step = 1;
	
	$action = (isset($_GET['action'])?$_GET['action']: (isset($_POST['action'])?$_POST['action']:false) );
	if( !$action )
		return;
	
	$DeleteFile = false;
	switch($action)
	{
		case 'powerpress-jquery-stats': {
		
			// Make sure users have permission to access this
			if( @$Settings['use_caps'] && !current_user_can('view_podcast_stats') )
			{
				powerpress_admin_jquery_header( __('Blubrry Media Statistics') );
?>
<h2><?php echo __('Blubrry Media Statistics'); ?></h2>
<p><?php echo __('You do not have sufficient permission to manage options.'); ?></p>
<p style="text-align: center;"><a href="#" onclick="self.parent.tb_remove();" title="<?php echo __('Close'); ?>"><?php echo __('Close'); ?></a></p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			}
			else if( !current_user_can('edit_posts') )
			{
				powerpress_admin_jquery_header('Blubrry Media Statistics');
				powerpress_page_message_add_notice( __('You do not have sufficient permission to view media statistics.') );
				powerpress_page_message_print();
				powerpress_admin_jquery_footer();
				exit;
			}
				
			$StatsCached = get_option('powerpress_stats');
			
			powerpress_admin_jquery_header( __('Blubrry Media Statistics') );
?>
<h2><?php echo __('Blubrry Media Statistics'); ?></h2>
<?php
			echo $StatsCached['content'];
			powerpress_admin_jquery_footer();
			exit;
		}; break;
		case 'powerpress-jquery-media-delete': {
			
			if( !current_user_can('edit_posts') )
			{
				powerpress_admin_jquery_header('Uploader');
				powerpress_page_message_add_notice( __('You do not have sufficient permission to upload media.') );
				powerpress_page_message_print();
				powerpress_admin_jquery_footer();
				exit;
			}
			
			check_admin_referer('powerpress-jquery-media-delete');
			$DeleteFile = $_GET['delete'];
			
		}; // No break here, let this fall thru..
		case 'powerpress-jquery-media': {
			
			if( !current_user_can('edit_posts') )
			{
				powerpress_admin_jquery_header( __('Select Media') );
?>
<h2><?php echo __('Select Media'); ?></h2>
<p><?php echo __('You do not have sufficient permission to manage options.'); ?></p>
<p style="text-align: center;"><a href="#" onclick="self.parent.tb_remove();" title="<?php echo __('Close'); ?>"><?php echo __('Close'); ?></a></p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			}
			
			if( !isset($Settings['blubrry_auth']) || $Settings['blubrry_auth'] == '' || !isset($Settings['blubrry_hosting']) || $Settings['blubrry_hosting'] == 0 )
			{
				powerpress_admin_jquery_header( __('Select Media') );
?>
<h2><?php echo __('Select Media'); ?></h2>
<p><?php echo __('Wait a sec! This feature is only available to Blubrry Podcast paid hosting members.');
if( !isset($Settings['blubrry_auth']) || $Settings['blubrry_auth'] == '' )
	echo ' '. __('Join our community to get free podcast statistics and access to other valuable').' <a href="http://www.blubrry.com/powerpress_services/" target="_blank">'. __('services') . '</a>.';
?>
</p>
<p>Our <a href="http://www.blubrry.com/powerpress_services/" target="_blank">podcast-hosting integrated</a> PowerPress makes podcast publishing simple. Check out the <a href="http://www.blubrry.com/powerpress_services/" target="_blank">video</a> on our exciting three-step publishing system!</p>
<p style="text-align: center;"><a href="#" onclick="self.parent.tb_remove();" title="<?php echo __('Close'); ?>"><?php echo __('Close'); ?></a></p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			}
			
			$Msg = false;
			if( $DeleteFile )
			{
				$api_url = sprintf('%s/media/%s/%s?format=json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Settings['blubrry_program_keyword'], $DeleteFile );
				$json_data = powerpress_remote_fopen($api_url, $Settings['blubrry_auth'], array(), 10, 'DELETE');
				$results =  powerpress_json_decode($json_data);
				
				if( isset($results['text']) )
					$Msg = $results['text'];
				else if( isset($results['error']) )
					$Msg = $results['error'];
				else
					$Msg = __('An unknown error occurred deleting media file.');
			}

			$api_url = sprintf('%s/media/%s/index.json?quota=true', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Settings['blubrry_program_keyword'] );
			$json_data = powerpress_remote_fopen($api_url, $Settings['blubrry_auth']);
			$results =  powerpress_json_decode($json_data);
				
			$FeedSlug = $_GET['podcast-feed'];
			powerpress_admin_jquery_header( __('Select Media'), true );
?>
<script language="JavaScript" type="text/javascript">

function SelectMedia(File)
{
	self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').value=File;
	self.parent.document.getElementById('powerpress_hosting_<?php echo $FeedSlug; ?>').value='1';
	self.parent.document.getElementById('powerpress_url_<?php echo $FeedSlug; ?>').readOnly='true';
	self.parent.tb_remove();
}
function DeleteMedia(File)
{
	return confirm('Delete '+File+', are you sure?');
}
</script>
		<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding:0;"><a href="#" onclick="self.parent.tb_remove();" title="Cancel"><img src="<?php echo admin_url(); ?>/images/no.png" /></a></p>
		<div id="media-header">
			<h2><?php echo __('Select Media'); ?></h2>
			<?php
				if( $Msg )
				echo '<p>'. $Msg . '</p>';
			?>
			<div class="media-upload-link"><a href="<?php echo admin_url() . wp_nonce_url("admin.php?action=powerpress-jquery-upload", 'powerpress-jquery-upload'); ?>&podcast-feed=<?php echo $FeedSlug; ?>&keepThis=true&TB_iframe=true&height=350&width=530&modal=true" class="thickbox" title="Upload Media File">Upload Media File</a></div>
			<p>Select from media files uploaded to blubrry.com:</p>
		</div>
	<div id="media-items-container">
		<div id="media-items">
<?php
		$QuotaData = false;
		if( isset($results['error']) )
		{
			echo $results['error'];
		}
		else
		{
			while( list($index,$data) = each($results) )
			{
				if( $index === 'quota' )
				{
					$QuotaData = $data;
					continue;
				}

?>
<div class="media-item">
	<strong class="media-name"><?php echo $data['name']; ?></strong>
	<cite><?php echo powerpress_byte_size($data['length']); ?></cite>
	<div class="media-item-links">
		<?php if (function_exists('curl_init')) { ?>
		<a href="<?php echo admin_url() . wp_nonce_url("admin.php?action=powerpress-jquery-media-delete", 'powerpress-jquery-media-delete'); ?>&amp;podcast-feed=<?php echo $FeedSlug; ?>&amp;delete=<?php echo urlencode($data['name']); ?>" onclick="return DeleteMedia('<?php echo $data['name']; ?>');">Delete</a> | <?php } ?>
		<a href="#" onclick="SelectMedia('<?php echo $data['name']; ?>'); return false;">Select</a>
	</div> 
</div>
<?php				
			}
		}
?>
		</div>
	</div>
	<div id="media-footer">
		<div class="media-upload-link"><a href="<?php echo admin_url() . wp_nonce_url("admin.php?action=powerpress-jquery-upload", 'powerpress-jquery-upload'); ?>&podcast-feed=<?php echo $FeedSlug; ?>&keepThis=true&TB_iframe=true&height=350&width=530&modal=true" class="thickbox" title="Upload Media File">Upload Media File</a></div>
		<?php
		if( $QuotaData ) { 
			$NextDate = strtotime( $QuotaData['published']['next_date']);
		?>
			<p>You have uploaded <em><?php echo powerpress_byte_size($QuotaData['unpublished']['available']); ?></em> of your <em><?php echo powerpress_byte_size($QuotaData['unpublished']['total']); ?></em> limit</p>
			<p>You are hosting <em><?php echo powerpress_byte_size($QuotaData['published']['available']); ?></em> of your <em><?php echo powerpress_byte_size($QuotaData['published']['total']); ?></em>/month limit.</p>
			<p>Your limit will adjust on <?php echo date('m/d/Y', $NextDate); ?> to <em><?php echo powerpress_byte_size($QuotaData['published']['next_available']); ?></em>.</p>
		<?php } ?>
		<p style="text-align: center;"><a href="#" onclick="self.parent.tb_remove();" title="<?php echo __('Close'); ?>"><?php echo __('Close'); ?></a></p>
	</div>
	
<?php	
			powerpress_admin_jquery_footer(true);
			exit;
		}; break;
		case 'powerpress-jquery-account-save': {
		
			if( !current_user_can('manage_options') )
			{
				powerpress_admin_jquery_header('Blubrry Services Integration');
				powerpress_page_message_add_notice( __('You do not have sufficient permission to manage options.') );
				powerpress_page_message_print();
				powerpress_admin_jquery_footer();
				exit;
			}
			
			check_admin_referer('powerpress-jquery-account');
			
			$Password = $_POST['Password'];
			$SaveSettings = $_POST['Settings'];
			$Password = powerpress_stripslashes($Password);
			$General = powerpress_stripslashes($SaveSettings);
			
			$Save = false;
			$Close = false;
		
			
			if( $_POST['Remove'] )
			{
				$SaveSettings['blubrry_username'] = '';
				$SaveSettings['blubrry_auth'] = '';
				$SaveSettings['blubrry_program_keyword'] = '';
				$SaveSettings['blubrry_hosting'] = 0;
				$Close = true;
				$Save = true;
			}
			else
			{
				$Programs = array();
				//if( isset($_POST['ChangePassword']) )
				//{
				//	$Settings['blubrry_program_keyword'] = ''; // Reset the program keyword stored
					
					// Anytime we change the password we need to test it...
				$auth = base64_encode( $SaveSettings['blubrry_username'] . ':' . $Password );
				if( $SaveSettings['blubrry_hosting'] == 0 )
					$api_url = sprintf('%s/stats/index.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/') );
				else
					$api_url = sprintf('%s/media/index.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/') );
				$json_data = powerpress_remote_fopen($api_url, $auth);
				$results =  powerpress_json_decode($json_data);
				
				if( isset($results['error']) )
				{
					$Error = $results['error'];
					if( strstr($Error, 'currently not available') )
						$Error = 'Unable to find podcasts for this account.';
				}
				else if( !is_array($results) )
				{
					$Error = $json_data;
				}
				else
				{
					// Get all the programs for this user...
					while( list($null,$row) = each($results) )
						$Programs[ $row['program_keyword'] ] = $row['program_title'];
					
					if( count($Programs) > 0 )
					{
						$SaveSettings['blubrry_auth'] = $auth;
						
						if( $SaveSettings['blubrry_program_keyword'] != '' )
						{
							powerpress_add_blubrry_redirect($SaveSettings['blubrry_program_keyword']);
							$Save = true;
							$Close = true;
						}
						else if( isset($SaveSettings['blubrry_program_keyword']) )
						{
							$Error = 'You must select a program to continue.';
						}
						else if( count($Programs) == 1 )
						{
							list($keyword, $title) = each($Programs);
							$SaveSettings['blubrry_program_keyword'] = $keyword;
							powerpress_add_blubrry_redirect($keyword);
							$Close = true;
							$Save = true;
						}
						else
						{
							$Error = 'Please select your podcast program to continue.';
							$Step = 2;
							$Settings['blubrry_username'] = $SaveSettings['blubrry_username'];
							$Settings['blubrry_hosting'] = $SaveSettings['blubrry_hosting'];
						}
					}
					else
					{
						$Error = 'No podcasts for this account are listed on blubrry.com.';
					}
				}
			}
			
			if( $Save )
				powerpress_save_settings($SaveSettings);
			
			// Clear cached statistics
			delete_option('powerpress_stats');
			
			if( $Error )
				powerpress_page_message_add_notice( $Error );
				
			if( $Close )
			{
				powerpress_admin_jquery_header('Blubrry Services Integration');
				powerpress_page_message_print();
?>
<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding:0;"><a href="#" onclick="self.parent.tb_remove(); return false;" title="Close"><img src="<?php echo admin_url(); ?>/images/no.png" alt="Close" /></a></p>
<h2>Blubrry Services Integration</h2>
<p style="text-align: center;"><strong>Settings Saved Successfully!</strong></p>
<p style="text-align: center;">
	<a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_basic.php"); ?>" target="_top" title="Close">Close</a>
</p>
<?php
				powerpress_admin_jquery_footer();
				exit;
			}
			
			
		} // no break here, let the next case catch it...
		case 'powerpress-jquery-account':
		{
			if( !current_user_can('manage_options') )
			{
				powerpress_admin_jquery_header('Blubrry Services Integration');
				powerpress_page_message_add_notice( __('You do not have sufficient permission to manage options.') );
				powerpress_page_message_print();
				powerpress_admin_jquery_footer();
				exit;
			}
			
			check_admin_referer('powerpress-jquery-account');
			
			if( !$Settings )
				$Settings = get_option('powerpress_general');
			
			if( $Programs == false )
				$Programs = array();
			
			// If we have programs to select from, then we're at step 2
			//if( count($Programs) )
			//	$Step = 2;
			
			powerpress_admin_jquery_header('Blubrry Services Integration');
			powerpress_page_message_print();	
?>
<p style="text-align: right; position: absolute; top: 5px; right: 5px; margin: 0; padding: 0;"><a href="#" onclick="self.parent.tb_remove();" title="Cancel"><img src="<?php echo admin_url(); ?>/images/no.png" /></a></p>
<form action="<?php echo admin_url(); ?>" enctype="multipart/form-data" method="post">
<?php wp_nonce_field('powerpress-jquery-account'); ?>
<input type="hidden" name="action" value="powerpress-jquery-account-save" />
<div id="accountinfo">
	<h2>Blubrry Services Integration</h2>
<?php if( $Step == 1 ) { ?>
	<p>
		<label for="blubrry_username">Blubrry User Name</label>
		<input type="text" id="blubrry_username" name="Settings[blubrry_username]" value="<?php echo $Settings['blubrry_username']; ?>" />
	</p>
	<p id="password_row">
		<label for="password_password">Blubrry Password</label>
		<input type="password" id="password_password" name="Password" value="" />
	</p>
	<p><strong>Select Blubrry Services</strong></p>
	<p style="margin-left: 20px; margin-bottom: 0px;margin-top: 0px;">
		<input type="radio" name="Settings[blubrry_hosting]" value="0" <?php echo ($Settings['blubrry_hosting']==0?'checked':''); ?> />Statistics Integration only
	</p>
	<p style="margin-left: 20px; margin-top: 0px;">
		<input type="radio" name="Settings[blubrry_hosting]" value="1" <?php echo ($Settings['blubrry_hosting']==1?'checked':''); ?> />Statistics and Hosting Integration (Requires Blubrry Hosting Account)
	</p>
<?php } else { ?>
	<input type="hidden" name="Settings[blubrry_username]" value="<?php echo htmlspecialchars($Settings['blubrry_username']); ?>" />
	<input type="hidden" name="Password" value="<?php echo htmlspecialchars($Password); ?>" />
	<input type="hidden" name="Settings[blubrry_hosting]" value="<?php echo $Settings['blubrry_hosting']; ?>" />
	<p>
		<label>Blubrry Program Keyword</label>
<select name="Settings[blubrry_program_keyword]">
<option value="">Select Program</option>
<?php
while( list($value,$desc) = each($Programs) )
	echo "\t<option value=\"$value\"". ($Settings['blubrry_program_keyword']==$value?' selected':''). ">$desc</option>\n";
?>
</select>
	</p>
<?php } ?>
	<p>
		<input type="submit" name="Remove" value="Remove" style="float: right;" onclick="return confirm('Remove Blubrry Services Integration, are you sure?');" />
		<input type="submit" name="Save" value="Save" />
		<input type="button" name="Cancel" value="Cancel" onclick="self.parent.tb_remove();" />
	</p>
</div>
</form>
<?php
			powerpress_admin_jquery_footer();
			exit;
		}; break;
		case 'powerpress-jquery-upload': {
			
			if( !current_user_can('edit_posts') )
			{
				powerpress_admin_jquery_header('Uploader');
				powerpress_page_message_add_notice( __('You do not have sufficient permission to upload media.') );
				powerpress_page_message_print();
				powerpress_admin_jquery_footer();
				exit;
			}
			
			check_admin_referer('powerpress-jquery-upload');
			
			$RedirectURL = false;
			$Error = false;
			if( $Settings['blubrry_hosting'] == 0 )
			{
				$Error = __('This feature is available to Blubrry Hosting users only.');
			}
			
			if( $Error == false )
			{
				$api_url = sprintf('%s/media/%s/upload_session.json', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Settings['blubrry_program_keyword'] );
				$json_data = powerpress_remote_fopen($api_url, $Settings['blubrry_auth']);
				
				$results =  powerpress_json_decode($json_data);
				
				// We need to obtain an upload session for this user...
				if( isset($results['error']) && strlen($results['error']) > 1 )
				{
					$Error = $results['error'];
					if( strstr($Error, 'currently not available') )
						$Error = 'Unable to find podcasts for this account.';
				}
				else if( $results === $json_data )
				{
					$Error = $json_data;
				}
				else if( !is_array($results) || $results == false )
				{
					$Error = $json_data;
				}
				else
				{
					if( isset($results['url']) && !empty($results['url']) )
						$RedirectURL = $results['url'];
				}
			}
			
			if( $Error == false && $RedirectURL )
			{
				header("Location: $RedirectURL");
				exit;
			}
			else if( $Error == false )
			{
				$Error = __('Unable to obtain upload session.');
			}
			
			powerpress_admin_jquery_header('Uploader');
			echo '<h2>'. __('Uploader') .'</h2>';
			echo '<p>';
			echo $Error;
			echo '</p>';
			?>
			<p style="text-align: center;"><a href="#" onclick="self.parent.tb_remove();" title="<?php echo __('Close'); ?>"><?php echo __('Close'); ?></a></p>
			<?php
			powerpress_admin_jquery_footer();
			exit;
		}; break;
		case 'powerpress-jquery-upload-complete': {
		
			if( !current_user_can('edit_posts') )
			{
				powerpress_admin_jquery_header('Uploader');
				powerpress_page_message_add_notice( __('You do not have sufficient permission to upload media.') );
				powerpress_page_message_print();
				powerpress_admin_jquery_footer();
				exit;
			}
			
			$File = $_GET['File'];
			$Message = $_GET['Message'];
			
			powerpress_admin_jquery_header('Upload Complete');
			echo '<h2>'. __('Uploader') .'</h2>';
			echo '<p>';
			if( $File )
			{
				echo 'File: ';
				echo $File;
				echo ' - ';
			}
			echo $Message;
			echo '</p>';
			?>
			<p style="text-align: center;"><a href="#" onclick="self.parent.tb_remove();" title="<?php echo __('Close'); ?>"><?php echo __('Close'); ?></a></p>
			<?php
			
			if( $Message == '' )
			{
?>
<script language="JavaScript" type="text/javascript">
<?php if( $File != '' ) { ?>
self.parent.SelectMedia('<?php echo $File ; ?>'); <?php } ?>
self.parent.tb_remove();
</script>
<?php
			}
			powerpress_admin_jquery_footer();
			exit;
		}; break;
	}
	
}

function powerpress_admin_jquery_header($title, $jquery = false)
{
	if( $jquery )
		add_thickbox(); // we use the thckbox for some settings
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php echo $title; ?> &#8212; WordPress</title>
<?php


wp_admin_css( 'css/global' );
wp_admin_css();
if( $jquery )
	wp_enqueue_script('utils');

do_action('admin_print_styles');
do_action('admin_print_scripts');
do_action('admin_head');

echo '<!-- done adding extra stuff -->';

?>
<link rel="stylesheet" href="<?php echo powerpress_get_root_url(); ?>css/jquery.css" type="text/css" media="screen" />
<?php if( $other ) echo $other; ?>
</head>
<body>
<div id="container">
<?php
}


function powerpress_admin_jquery_footer($jquery = false)
{
	if( $jquery )
		do_action('admin_print_footer_scripts');
	
?>
</div><!-- end container -->
</body>
</html>
<?php
}


?>