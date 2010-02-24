<?php
// powerpressadmin-podpress.php

if( !function_exists('add_action') )
	die("access denied.");
	
	function powerpress_only_include_ext($url)
	{
		if( isset($_GET['include_only_ext']) && trim($_GET['include_only_ext']) != '' )
		{
			global $powerpress_only_include_ext_array;
			if( !isset($powerpress_only_include_ext_array) )
			{
				$extensions = strtolower(preg_replace("/\s/", '', $_GET['include_only_ext']));
				$powerpress_only_include_ext_array = explode(',', trim($extensions, ',') );
			}
			
			$partsURL = @parse_url( trim($url) );
			if( !$partsURL )
				return false;
			$filename = substr($partsURL['path'], strrpos($partsURL['path'], '/')+1 );
			$partsFile = pathinfo($filename);
				
			if( in_array( strtolower($partsFile['extension']), $powerpress_only_include_ext_array ) )
			{
				return true;
			}
			return false;
		}
		
		return true; // we include all extensions otherwise
	}
	
	function powerpress_get_podpress_episodes($hide_errors=true)
	{
		global $wpdb;
		
		$PodpressSettings = get_option('podPress_config');
		if( !$PodpressSettings )
		{
			$PodpressSettings = array();
			$PodpressSettings['mediaWebPath'] = '';
			$powerpress_settings = get_option('powerpress_general');
			if( !empty($powerpress_settings['default_url']) )
			{
				$PodpressSettings['mediaWebPath'] = $powerpress_settings['default_url'];
				powerpress_page_message_add_notice( sprintf(__('Unable to detect PodPress media URL setting. Using the PowerPress setting "Default Media URL" (%s) instead.'), $PodpressSettings['mediaWebPath']) );
			}
			else
			{
				// We need to print a warning that they need to configure their podPress settings as the settings are no longer found in the database.
				powerpress_page_message_add_error( __('Unable to detect PodPress media URL setting. Please set the "Default Media URL" setting in PowerPress to properly import podcast episodes.') );
			}
		}
		
		
		$media_url = $PodpressSettings['mediaWebPath'];
		if( substr($media_url, 0, -1) != '/' )
			$media_url .= '/'; // Make sure the URL has a trailing slash
			
		
		$return = array();
		$return['feeds_required'] = 0;
		$query = "SELECT p.ID, p.post_title, p.post_date, pm.meta_value ";
		$query .= "FROM {$wpdb->posts} AS p ";
		$query .= "INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id ";
		$query .= "WHERE pm.meta_key = 'podPressMedia' ";
		$query .= "AND p.post_type != 'revision' ";
		$query .= "GROUP BY p.ID ";
		$query .= "ORDER BY p.post_date DESC ";
		
		$results_data = $wpdb->get_results($query, ARRAY_A);
		if( $results_data )
		{
			
			while( list($null,$row) = each($results_data) )
			{
				//$return = $row;
				$podpress_data = @unserialize($row['meta_value']);
				if( !$podpress_data )
				{
					$podpress_data_serialized = powerpress_repair_serialize( $row['meta_value'] );
					$podpress_data = @unserialize($podpress_data_serialized);
					if( !is_array($podpress_data) && is_string($podpress_data) )
					{
						$podpress_data_two = @unserialize($podpress_data);
						if( !is_array($podpress_data_two)  )
						{
							$podpress_data_serialized = powerpress_repair_serialize($podpress_data);
							$podpress_data_two = @unserialize($podPressMedia);
						}
						
						if( is_array($podpress_data_two)  )
							$podpress_data = $podpress_data_two;
					}
				}
				else if( is_string($podpress_data) )
				{
					// May have been double serialized...
					$podpress_unserialized = @unserialize($podpress_data);
					if( !$podpress_unserialized )
					{
						$podpress_data_serialized = powerpress_repair_serialize( $podpress_data );
						$podpress_unserialized = @unserialize($podpress_data_serialized);
					}
					
					$podpress_data = $podpress_unserialized;
				}
				
				if( $podpress_data )
				{
					if( !is_array($podpress_data) )
					{
						// display a warning here...
						if( $hide_errors == false )
							powerpress_page_message_add_error( sprintf('Error decoding PodPress data for post "%s"', $row['post_title']) );
						continue;
					}
					
					$clean_data = array();
					while( list($episode_index,$episode_data) = each($podpress_data) )
					{
						if( trim($episode_data['URI']) != '' )
						{
							$MediaURL = $episode_data['URI'];
							if( strtolower(substr($MediaURL, 0, 4)) != 'http' )
								$MediaURL = $media_url . rtrim($episode_data['URI'], '/');
							
							if( !powerpress_only_include_ext($MediaURL) ) // Skip this media type
								continue;
							
							$clean_data[ $episode_index ] = array();
							$clean_data[ $episode_index ]['url'] = $MediaURL;
							$clean_data[ $episode_index ]['size'] = $episode_data['size'];
							if( trim($episode_data['duration']) && $episode_data['duration'] != 'UNKNOWN' )
								$clean_data[ $episode_index ]['duration'] = powerpress_readable_duration($episode_data['duration'], true);
							$ContentType = powerpress_get_contenttype( $episode_data['URI'] );
							if( $ContentType )
								$clean_data[ $episode_index ]['type'] = trim($ContentType);
							if( !empty($episode_data['previewImage']) )
								$clean_data[ $episode_index ]['image'] = $episode_data['previewImage'];
						}
					}
					
					if( count($clean_data) == 0 )
						continue; // Go to the next record...
					
					
					if( $return['feeds_required'] < count( $clean_data ) )
					{
						$return['feeds_required'] = count( $clean_data );
					}
					$return[ $row['ID'] ] = array();
					$return[ $row['ID'] ]['podpress_data'] = $clean_data;
					$return[ $row['ID'] ]['post_title'] = $row['post_title'];
					$return[ $row['ID'] ]['post_date'] = $row['post_date'];
					
					// Check that there is no other enclosure...
					$enclosure_data = get_post_meta($row['ID'], 'enclosure', true);
					if( $enclosure_data )
					{
						$Included = false;
						list($EnclosureURL,$null) = explode("\n", $enclosure_data);
						$return[ $row['ID'] ]['enclosure'] = $enclosure_data;
						
						while( list($episode_index_temp,$episode_data_temp) = each($clean_data) )
						{
							if( trim($EnclosureURL) == trim($episode_data_temp['url']) )
							{
								$Included = true;
								break; // We found the media already.
							}
							else if( trim($episode_data_temp['url']) == '' )
							{
								unset($clean_data[$episode_index_temp]); // Empty URL, lets remove it so we don't accidently use it
							}
						}
						reset($clean_data);
						
						if( $Included == false && $return['feeds_required'] < (count( $clean_data )+1) )
						{
							$return['feeds_required'] = count( $clean_data )+1; // We can't remove this enclosure
						}
					}
					
					// Check for additional itunes data in the database..
					if( false ) // Possibly for future verions, but seems unnecessary at this point
					{
						$itunes_data = get_post_meta($row['ID'], 'podPressPostSpecific', true);
						if( $itunes_data && is_array($itunes_data) )
						{
							$return[ $row['ID'] ]['itunes'] = array();
							
							// Add iTunes stuff...
							if( $itunes_data['itunes:subtitle'] != '##PostExcerpt##' && $itunes_data['itunes:subtitle'] != '' )
								$return[ $row['ID'] ]['itunes']['subtitle'] = $itunes_data['itunes:subtitle'];
							
							if( $itunes_data['itunes:summary'] != '##PostExcerpt##' && $itunes_data['itunes:summary'] != '##Global##' && $itunes_data['itunes:summary'] != '' )
								$return[ $row['ID'] ]['itunes']['summary'] = $itunes_data['itunes:summary'];
							
							if( $itunes_data['itunes:keywords'] != '##WordPressCats##' && $itunes_data['itunes:keywords'] != '##Global##' && $itunes_data['itunes:keywords'] != '' )
								$return[ $row['ID'] ]['itunes']['keywords'] = $itunes_data['itunes:keywords'];
								
							if( $itunes_data['itunes:author'] != '##Global##' && $itunes_data['itunes:author'] != '' )
								$return[ $row['ID'] ]['itunes']['author'] = $itunes_data['itunes:author'];
								
							if( strtolower($itunes_data['itunes:explicit']) == 'yes' )
								$return[ $row['ID'] ]['itunes']['explicit'] = 'yes';
							
							if( strtolower($itunes_data['itunes:block']) == 'yes' )
								$return[ $row['ID'] ]['itunes']['block'] = 'yes';						
							
							if( count($return[ $row['ID'] ]['itunes']) == 0 )
								unset($return[ $row['ID'] ]['itunes']);
						}
					}
				}
			}
		}
		return $return;
	}
	
	function powerpressadmin_podpress_delete_data()
	{
		global $wpdb;
		// Delete podpress data from database...
		$query = "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'podPress%'";
		$deleted_count = $wpdb->query($query);
		powerpress_page_message_add_notice( sprintf(__('PodPress data deleted from database successfully. (%d database records removed)'), $deleted_count) );
	}
	
	function powerpressadmin_podpress_do_import()
	{
		$Import = $_POST['Import'];
		$PodPressData = powerpress_get_podpress_episodes(true);
		
		while( list($post_id, $podpress_episode_feeds) = each($Import) )
		{
			while( list($podpress_index, $feed_slug) = each($podpress_episode_feeds) )
			{
				if( $feed_slug )
				{
					$EpisodeData = $PodPressData[ $post_id ]['podpress_data'][ $podpress_index ];
					
					if( $EpisodeData['size'] == '' || !is_numeric($EpisodeData['size']) ) // Get the content length
					{
						$headers = wp_get_http_headers($EpisodeData['url']);
						if( $headers && $headers['content-length'] )
							$EpisodeData['size'] = (int) $headers['content-length'];
					}
					$EnclosureData = trim($EpisodeData['url']) . "\n" . trim($EpisodeData['size']) . "\n". trim($EpisodeData['type']);
					$Serialized = array();
					if( !empty($EpisodeData['duration']) )
						$Serialized['duration'] = $EpisodeData['duration'];
					if( !empty($EpisodeData['image']) )
						$Serialized['image'] = $EpisodeData['image'];
					
					if( count($Serialized) > 0 )
						$EnclosureData .= "\n".serialize($Serialized);
					
					if( $feed_slug == 'podcast' )
						add_post_meta($post_id, 'enclosure', $EnclosureData, true);
					else
						add_post_meta($post_id, '_'. $feed_slug .':enclosure', $EnclosureData, true);
					
					powerpressadmin_podpress_import_log($PodPressData[ $post_id ]['post_title'], $EpisodeData['url'], $feed_slug);
				}
			}
		}
	}
	
	function powerpressadmin_podpress_import_log($post_title, $episode_url, $feed_slug)
	{
		global $g_podpress_import_log, $g_podpress_import_count;
		$filename = substr($episode_url, strrpos($episode_url, '/')+1);
		$g_podpress_import_log .= '<p style="font-weight: normal; margin-top: 2px; margin-bottom: 2px; margin-left: 20px;">';
		$g_podpress_import_log .= 'Podpress Episode "'. htmlspecialchars($filename) .'" for blog post "'. htmlspecialchars($post_title) .'" imported to feed "'. $feed_slug ."\".\n";
		
		$g_podpress_import_log .= '</p>';
		
		if( $g_podpress_import_count )
			$g_podpress_import_count++;
		else
			$g_podpress_import_count = 1;
	}
	
	function powerpressadmin_podpress_import_print_log()
	{
		global $g_podpress_import_log, $g_podpress_import_count;
		if( !$g_podpress_import_log )
		{
			echo '<div style="" class="updated powerpress-notice">';
			echo '<p>If you are unsure about importing your PodPress data, try the option under Basic Settings titled \'PodPress Episodes\' and set to \'Include in posts and feeds\'.</p>';
			echo '<p>Once you feel comfortable with PowerPress, you can use this screen to import your PodPress data.</p>';
			echo '</div>';
			return;
		}
		echo '<div style="" class="updated powerpress-notice">';
		echo '<h3 style="margin-top: 2px; margin-bottom: 2px;">PodPress Import Log</h3>';
		echo $g_podpress_import_log;
		$g_podpress_import_log='';
		echo "<p style=\"font-weight: normal;\">Imported $g_podpress_import_count PodPress episode(s).</p>";
		echo '</div>';
	}
	
	function powerpressadmin_importpodpress_columns($data=array())
	{
		$Settings = powerpress_get_settings('powerpress_general', false);
		$data['post-title'] = 'Episode Title';
		$data['post-date'] = 'Date';
		
		$data['feed-podcast'] = 'Feed: (podcast)';
		
		if( is_array($Settings['custom_feeds']) )
		{
			while( list($feed_slug,$value) = each($Settings['custom_feeds']) )
			{
				if( $feed_slug != 'podcast' )
					$data['feed-'.$feed_slug] = 'Feed: ('.$feed_slug.')';
			}
		}
		$data['exclude'] = '<a href="#" onclick="no_import_all();return false;">No Import</a>';
		
		return $data;
	}
	
	add_filter('manage_powerpressadmin_importpodpress_columns', 'powerpressadmin_importpodpress_columns');
	
	function powerpressadmin_importpodpress_columns_print($include_ids=true)
	{
		$Columns = powerpressadmin_importpodpress_columns();
		while( list($key,$title) = each($Columns) )
		{
			if( $include_ids )
				echo  '<th scope="col" id="'. $key .'" class="manage-column column-'. $key .'" style="">'. $title .'</th>';
			else
				echo  '<th scope="col" class="manage-column column-'. $key .'" style="">'. $title .'</th>';
		}
	}
	
	function powerpress_admin_podpress()
	{
		$results = powerpress_get_podpress_episodes(false);
		$Settings = powerpress_get_settings('powerpress_general', false);
		if( !isset($Settings['custom_feeds']['podcast']) )
			$Settings['custom_feeds']['podcast'] = 'Podcast Feed (default)';
			
		$AllowImport = false;
		$AllowCleanup = true;
		
		if( $results )
		{
			if( $results['feeds_required'] > count($Settings['custom_feeds']) )
			{
				powerpress_page_message_add_error( sprintf(__('We found blog posts that have %d media files. You will need to create %d more Custom Feed%s in order to continue.'), $results['feeds_required'], $results['feeds_required'] - count($Settings['custom_feeds']), (( ( $results['feeds_required'] - count($Settings['custom_feeds']) ) > 1 )?'s':'') ) );
			}
			else
			{
				$AllowImport = true;
			}
		}
		
		powerpress_page_message_print();
		
		powerpressadmin_podpress_import_print_log();
		
?>
<style type="text/css">
.column-exclude {
	width: 80px;
}
.column-post-date {
	width: 80px;
}
label {
	float: left;
	width: 160px;
}
</style>
<script language="javascript">

function check_radio_selection(obj, PostID, FileIndex)
{
	if( obj.value == '' ) // Users can select the no feed option as much as they want
		return true;
	
	var Field = obj.id;
	while( Field.charAt( Field.length-1 ) >= "0" &&  Field.charAt( Field.length-1 ) <= "9" ) // ( chr < "0" ) || ( chr > "9" )
	{
		Field = Field.substring(0, Field.length-1);
	}
	
	var Pos = 0;
	var CheckObj = document.getElementsByName( "Import["+PostID+"]["+Pos+"]" );
	while( CheckObj )
	{
		if( CheckObj.length == 0 )
			break;
			
		if( Pos != FileIndex )
		{
			for (var i = 0; i < CheckObj.length; i++)
			{
				if (CheckObj[i].type == 'radio' && CheckObj[i].checked && CheckObj[i].value == obj.value )
				{
					alert("Sorry, you may only select one media file per post per feed. ");
					return false;
				}
			}
		}
		Pos++;
		var CheckObj = document.getElementsByName( "Import["+PostID+"]["+Pos+"]" );
	}
	
	return true;
}

function no_import_all()
{
	if( !confirm('Select "No Import" option for all media files?') )
		return;
		
	var Inputs = document.getElementsByTagName('input');
	for (var i = 0; i < Inputs.length; i++)
	{
		var Elem = Inputs[i];
		if( Elem.type == 'radio' && Elem.value == '' )
			Elem.checked = true;
	}
}

function select_all(index,value)
{
	var NoImport = [];
	var Inputs = document.getElementsByTagName('input');
	for (var i = 0; i < Inputs.length; i++)
	{
		var Elem = Inputs[i];
		if( Elem.type == 'radio' && Elem.value == value )
		{
			ElemIndex = Elem.id.substring( Elem.id.lastIndexOf('_')+1);
			if( ElemIndex == index )
				Elem.checked = true;
			else if( Elem.checked && Elem.value != '' )
				NoImport.push( Elem.id );
		}
	}
	for (var i = 0; i < Inputs.length; i++)
	{
		var Elem = Inputs[i];
		if( Elem.type == 'radio' && Elem.value == '' )
		{
			for (var j = 0; j < NoImport.length; j++)
			{
				if( NoImport[j] == Elem.id )
					Elem.checked = true;
			}
		}
	}
}

</script>
<h2><?php echo __("Import PodPress Episodes"); ?></h2>
<?php

	//echo "<pre id=\"podpress_debug_info\" style=\"display: none;\">";
	//print_r($results);
	//echo "</pre>";
	//echo '<p><a href="javascript:void();" onclick="javascript:document.getElementById(\'podpress_debug_info\').style.display=\'block\';this.style.display=\'none\';return false;">Show Debug Info</a></p>';
	
	if( count($results) == 0 || count($results) == 1 )
	{
?>	
	<p>No PodPress episodes found to import.</p>
<?php
	}
	else
	{
?>
<input type="hidden" name="action" value="powerpress-importpodpress" />
<p>Select the media file under each feed for each episode you wish to import.</p>
<table class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
<?php 
	if( function_exists('print_column_headers') )
	{
		print_column_headers('powerpressadmin_importpodpress');
	}
	else // WordPress 2.6 or older
	{
		powerpressadmin_importpodpress_columns_print();
	}
?>
	</tr>
	</thead>

	<tfoot>
	<tr>
<?php 
	if( function_exists('print_column_headers') )
	{
		print_column_headers('powerpressadmin_importpodpress', false);
	}
	else // WordPress 2.6 or older
	{
		powerpressadmin_importpodpress_columns_print(false);
	}
?>
	</tr>
	</tfoot>
	<tbody>
<?php
	
	$StrandedEpisodes = 0;
	$ImportableEpisodes = 0;
	
	$count = 0;
	while( list($post_id, $import_data) = each($results	) )
	{
		$edit_link = get_edit_post_link( $post_id );
		if( $post_id == 'feeds_required' )
			continue;
		
		$columns = powerpressadmin_importpodpress_columns();
		
		$CurrentEnclosures = array();
		
		if( is_array($Settings['custom_feeds']) )
		{
			while( list($feed_slug,$value) = each($Settings['custom_feeds']) )
			{
				if( $feed_slug == 'podcast' )
					$enclosure_data = get_post_meta($post_id, 'enclosure', true);
				else
					$enclosure_data = get_post_meta($post_id, '_'. $feed_slug .':enclosure', true);
				if( !$enclosure_data )
					continue;
					
				@list($EnclosureURL, $EnclosureSize, $EnclosureType, $Serialized) = explode("\n", $enclosure_data);
				if( $EnclosureURL )
				{
					$CurrentEnclosures[ $feed_slug ] = array();
					$CurrentEnclosures[ $feed_slug ]['url'] = trim($EnclosureURL);
					$CurrentEnclosures[ $feed_slug ]['imported'] = false;
				}
				
				$found = false;
				while( list($episode_index,$episode_data) = each($import_data['podpress_data']) )
				{
					if( $episode_data['url'] == $CurrentEnclosures[ $feed_slug ]['url'] )
					{
						$import_data['podpress_data'][$episode_index]['imported'] = true;
						$CurrentEnclosures[ $feed_slug ]['imported'] = true;
						$found  = true;
						break;
					}
				}
				reset($import_data['podpress_data']);
				if( $found == false )
				{
					// Add it to the media file list, prepend it...
					$not_podpress_data = array();
					$not_podpress_data['url'] = $CurrentEnclosures[ $feed_slug ]['url'];
					$not_podpress_data['imported'] = true;
					$not_podpress_data['not_podpress'] = true;
					
					array_push($import_data['podpress_data'], $not_podpress_data);
					$CurrentEnclosures[ $feed_slug ]['imported'] = true;
					$CurrentEnclosures[ $feed_slug ]['present'] = true;
				}
			}
			reset($Settings['custom_feeds']);
		}
		
		if( $count % 2 == 0 )
			echo '<tr valign="middle" class="alternate">';
		else
			echo '<tr valign="middle">';
			
		$CheckedEpisodes = array(); // key = file_index, value = feed-slug
		
		$feed_index = 0;
		foreach($columns as $column_name=>$column_display_name)
		{
			$class = "class=\"column-$column_name\"";
			
			switch($column_name)
			{
				case 'post-title': {
					
					echo '<td '.$class.'><strong>';
					if ( current_user_can( 'edit_post', $post_id ) )
					{
					?><a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo attribute_escape(sprintf(__('Edit "%s"'), $import_data['post_title'])); ?>"><?php echo $import_data['post_title'] ?></a><?php
					}
					else
					{
						echo $import_data['post_title'];
					}
					
					echo '</strong><br />';
					echo '<div style="margin-left: 10px;">';
					$index = 1;
					while( list($episode_index,$episode_data) = each($import_data['podpress_data']) )
					{
						$Parts = parse_url($episode_data['url']);
						$filename = substr($Parts['path'], strrpos($Parts['path'], '/')+1 );
						echo "File&nbsp;$index:&nbsp;";
						
						if( empty($episode_data['not_podpress']) && empty($episode_data['imported']) )
						{
							echo '<span style="color: #CC0000; font-weight: bold; cursor:pointer;" onclick="alert(\'File: '. $filename .'\nURL: '. $episode_data['url'] .'\')">';
							$AllowCleanup = false;
							$StrandedEpisodes++;
						}
						else if( empty($episode_data['not_podpress']) && $episode_data['imported'] )
							echo '<span style="color: green; font-weight: bold; cursor:pointer;" onclick="alert(\'File: '. $filename .'\nURL: '. $episode_data['url'] .'\')">';
						
						if( empty($episode_data['not_podpress']) && empty($episode_data['imported']) )
							echo '*';
						echo $filename;
						if( empty($episode_data['not_podpress']) )
							echo '</span>';
							
						echo '<br/>';
						$index++;
					}
					reset($import_data['podpress_data']);
					
					echo '</div>';
					echo '</td>';
				
				}; break;
				case 'post-date': {
					echo "<td $class>";
					$timestamp = strtotime($import_data['post_date']);
					echo date('Y/m/d', $timestamp);
					echo "</td>";
				}; break;
				case 'feed-slug': {
					
					echo "<td $class>$feed_slug";
					echo "</td>";
					
				}; break;
				
				default: {
				
					echo "<td $class>";
					$feed_slug = substr($column_name, 5);
					if( $column_name == 'exclude' )
						$feed_slug = '';
					$enclosure_data = false;
					$EnclosureURL = '';
					
					echo '<div class="">&nbsp;<br />';
					if( isset($CurrentEnclosures[$feed_slug]) && $CurrentEnclosures[$feed_slug]['imported'] )
					{
						$index = 1;
						while( list($episode_index,$episode_data) = each($import_data['podpress_data']) )
						{
							echo "File $index: ";
							if( $CurrentEnclosures[$feed_slug]['url'] == $episode_data['url'] )
							{
								if( !empty($CurrentEnclosures[$feed_slug]['present']) )
									echo '<strong style="color: green;">present</strong>';
								else
									echo '<strong style="color: green;">imported</strong>';
							}
							else
								echo 'X';
							echo "<br/>\n";
							$index++;
						}
						reset($import_data['podpress_data']);
					}
					else
					{
						$index = 1;
						while( list($episode_index,$episode_data) = each($import_data['podpress_data']) )
						{
							echo "File&nbsp;$index:&nbsp;";
							if( @$episode_data['imported'] )
							{
									echo '&nbsp;X';
							}
							else
							{
								$checked = '';
								if( !isset($CheckedEpisodes[ $episode_index ]) && !in_array($feed_slug, $CheckedEpisodes) )
								{
									$checked = 'checked';
									$CheckedEpisodes[ $episode_index ] = $feed_slug;
								}
								if( !isset($CheckedEpisodes[ $episode_index ]) && $feed_slug == '' )
								{
									$checked = 'checked';
								}
								
								
								echo '<input type="radio" id="import_'. $post_id .'_'. $episode_index .'" name="Import['.$post_id.']['.$episode_index.']" value="'.$feed_slug.'" '. $checked .' onclick="return check_radio_selection(this, '.$post_id.', '.$episode_index.')" />';
							}
							echo '<br/>';
							$index++;
						}
						reset($import_data['podpress_data']);
					}
					
					echo '</div>';
					
					
					echo "</td>";
					$feed_index++;
				};	break;
			}
		}
		echo "\n    </tr>\n";
		$count++;
	}
?>
	</tbody>
</table>
<p>Importable PodPress episodes highlighted in <span style="color: #CC0000; font-weight: bold;">red</span> with asterisks *.</p>
<p style="margin-bottom: 0; padding-bottom: 0;">Select Only:</p>
<?php
			if( $results['feeds_required'] < 1 )
				$results['feeds_required'] = 1;
			
			for( $number = 0; $number < $results['feeds_required']; $number++ )
			{
?>
<p style="margin: 0 0 0 40px; padding: 0;">
 File <?php echo ($number+1); ?>:
<?php
				while( list($feed_slug,$feed_title) = each($Settings['custom_feeds']) )
				{
					echo '<a href="javascript:void()" onclick="select_all('. $number .',\''. $feed_slug .'\');return false;">'. htmlspecialchars($feed_title) .'</a> | ';
				}
?>
<a href="javascript:void()" onclick="select_all(<?php echo $number; ?>,'');return false;">No Import</a>
</p>
<?php
				break;
			}

		}
		
		if( $StrandedEpisodes )
		{
?>
<p>
	There are <?php echo $StrandedEpisodes; ?> PodPress media files that can be imported.
</p>
<?php
		}
		
		if( $AllowImport )
		{
			if( count($results) > 1 && $StrandedEpisodes > 0 )
			{
?>
<p class="submit">
<input type="submit" name="Submit" id="powerpress_import_button" class="button-primary" value="Import Episodes" />
</p>
<?php
			}
			else
			{
?>
<p class="submit">
<input type="button" name="Submit" id="powerpress_import_button" class="button-primary" value="Import Episodes" onclick="alert('There are no PodPress episodes found to import.');" />
</p>
<?php
			}
			
			/*
			if( $AllowCleanup )
			{
?>
</form>
<form enctype="multipart/form-data" method="post" action="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_tools.php') ?>">
<?php wp_nonce_field('powerpress-delete-podpress-data'); ?>
<input type="hidden" name="action" value="deletepodpressdata" />
<p class="submit">
<input type="submit" name="Submit" id="powerpress_delete_button" class="button-primary" value="Delete PodPress Data from Database" onclick="return confirm('Delete old PodPress data from database, are you sure?\n\nAll PodPress episode data will be permanently deleted.');" />
<br />  There is no need to delete PodPress data, but if you prefer to clean up your database then please feel free to use this option.
</p>
<?php
			}
			else
			{
?>

<p class="submit">
<input type="button" name="Submit" id="powerpress_delete_button" class="button-primary" value="Delete PodPress Data from Database" onclick="alert('This option will be enabled once all PodPress episodes have been imported.');" />
</p>
<?php
			}
			*/
		}
		else
		{
?>
<div class="error powerpress-error">
	We found blog posts that have <?php echo $results['feeds_required']; ?> media files.
	
	You will need to create <?php echo ( $results['feeds_required'] - count($Settings['custom_feeds']) ); ?> more Custom Feed<?php if( ( $results['feeds_required'] - count($Settings['custom_feeds']) ) > 1 ) echo 's'; ?> in order to continue.
</div>
<p>
	Blubrry PowerPress does not allow you to include multiple media files for one feed item (blog post).
	This is because each podcatcher handles multiple enclosures in feeds differently. iTunes will download
	the first enclosure that it sees in the feed ignoring the rest. Other podcatchers and podcasting directories
	either pick up the first enclosure or the last in each post item. This inconsistency combined with the fact that
	<a href="http://www.reallysimplesyndication.com/2004/12/21" target="_blank">Dave Winer does not recommend multiple enclosures</a>
	and the
	<a href="http://www.feedvalidator.org/docs/warning/DuplicateEnclosure.html" target="_blank">FeedValidator.org recommendation against it</a>
	is why Blubrry PowerPress does not support them.
</p>
<p>
	As a alternative, PowerPress allows you to create additional <a href="<?php echo admin_url('admin.php?page=powerpress/powerpressadmin_customfeeds.php'); ?>">Custom Podcast Channels</a> to associate additional media files in a blog post to specific feed channels.
</p>
<p class="submit">
<input type="button" name="Submit" id="powerpress_import_button" class="button-primary" value="Import Episodes" onclick="alert('We found blog posts that have <?php echo $results['feeds_required']; ?> media files.\n\nYou will need to create <?php echo ( $results['feeds_required'] - count($Settings['custom_feeds']) ); ?> more Custom Feed<?php if( ( $results['feeds_required'] - count($Settings['custom_feeds']) ) > 1 ) echo 's'; ?> in order to continue. ');" />
</p>



<?php
		}
?>
</form>
<hr />
<form enctype="enctype" method="get" action="<?php echo admin_url('admin.php') ?>">
<input type="hidden" name="page" value="powerpress/powerpressadmin_tools.php" />
<input type="hidden" name="action" value="powerpress-podpress-epiosdes" />
<h2>Filter Results</h2>
<p><label>Include Only</label><input type="text" name="include_only_ext" value="<?php if( !empty($_GET['include_only_ext']) ) echo htmlspecialchars($_GET['include_only_ext']); ?>" style="width: 240px;" /> (leave blank for all media) <br />
<label>&nbsp;</label>specify the file extensions to include separated by commas (e.g. mp3, m4v).
</p>
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="Filter Episodes" />
</p>
<!-- start footer -->
<?php
	}

?>