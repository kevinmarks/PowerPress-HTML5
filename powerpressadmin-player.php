<?php
	// PowerPress Player administration
	
	
// Handle post processing here for the players page.
function powerpress_admin_players_init()
{
	//wp_enqueue_script('jquery');
	
	$Settings = false; // Important, never remove this
	$Step = 1;
	
	$action = (isset($_GET['action'])?$_GET['action']: (isset($_POST['action'])?$_POST['action']:false) );

	if( !$action )
		return;
		
	switch($action)
	{
		case 'powerpress-select-player': {
			
			$SaveSettings = array();
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_general');
			powerpress_page_message_add_notice( __('Player activated successfully.', 'powerpress') );
			
		}; break;
		case 'powerpress-audio-player': {
		
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_audio-player');
			powerpress_page_message_add_notice( __('Audio Player settings saved successfully.', 'powerpress') );
		
		}; break;
		case 'powerpress-flashmp3-maxi': {
			
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_flashmp3-maxi');
			powerpress_page_message_add_notice( __('Flash Mp3 Maxi settings saved successfully.', 'powerpress') );
			
		} ; break; 
		case 'powerpress-audioplay':
		{
			$SaveSettings = $_POST['Player'];
			powerpress_save_settings($SaveSettings, 'powerpress_audioplay');
			powerpress_page_message_add_notice( __('AudioPlay settings saved successfully.', 'powerpress') );
		}; break;
	}
}

// add_action('init', 'powerpress_admin_players_init');

function powerpress_admin_page_player_error()
{

}

// Add what we need to the admin area of Blubrry PowerPress
function powerpress_admin_page_player()
{
	// Check that PowerPress is enabled..
	if( !defined('POWERPRESS_VERSION') )
	{
		// Print an error message here..
?>
<h2><?php echo __('Blubrry PowerPress Player Options', 'powerpress'); ?></h2>
<p><?php echo __('You must have the Blubrry PowerPress version 1.0 or newer installed for this plugin to work.', 'powerpress'); ?></p>
<?php
		return;
	}
	
	// Check that PowerPress is new enoguh..
	if( version_compare(POWERPRESS_VERSION, '0.9') < 1 && !defined('POWERPRESS_PLAYER_SKIP_VERSION_CHECK') )
	{
		// Print an error message here..
?>
<h2><?php echo __('Blubrry PowerPress Player Options', 'powerpress'); ?></h2>
<p><?php echo __('Your copy of Blubrry PowerPress is out of date. You must have Blubrry PowerPress version 1.0 or newer installed for this plugin to work.', 'powerpress'); ?></p>
<?php
		return;
	}
	
	//$Settings = get_option('powerpress_general');
	//powerpress_admin_page_header('powerpress/powerpressadmin_player.php',  'powerpress-edit', $simple_mode );
	require_once( POWERPRESS_ABSPATH.'/powerpressadmin-player-page.php');
	powerpress_admin_players();
	//powerpress_admin_page_footer(true);
}

/*
function powerpress_player_admin_menu()
{
	if( current_user_can('manage_options') )
	{
		$Settings = get_option('powerpress_general');
		if( $Settings && @$Settings['advanced_mode'] )
		{
			add_submenu_page('powerpress/powerpressadmin_basic.php', __('PowerPress Player Options'), __('Player Options'), 1, 'powerpress/powerpressadmin_player.php', 'powerpress_admin_page_player');
		}
		else
		{
			add_options_page('Blubrry PowerPress Player Options', 'PowerPress Player Options', 1, 'powerpress/powerpressadmin_player.php', 'powerpress_admin_page_player');
		}
	}
}

add_action('admin_menu', 'powerpress_player_admin_menu', 11); // later priority than PowerPress
*/
?>