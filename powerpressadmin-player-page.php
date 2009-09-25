<?php
// PowerPress Player settings page
	
function powerpress_admin_players()
{
	$General = powerpress_get_settings('powerpress_general');
	$select_player = false;
	if( isset($_GET['sp']) )
		$select_player = true;
	else if( !isset($General['player']) )
		$select_player = true;
		
	$Audio = array();
	$Audio['default'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/FlowPlayerClassic.mp3';
	$Audio['audio-player'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/1_Pixel_Out_Flash_Player.mp3';
	$Audio['flashmp3-maxi'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/Flash_Maxi_Player.mp3';
	$Audio['simple_flash'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/Simple_Flash_MP3_Player.mp3';
	$Audio['audioplay'] = 'http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/AudioPlay.mp3';
		
		
		/*
		<div><
		object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="30" height="30">
		<PARAM NAME=movie VALUE="http://www.strangecube.com/audioplay/online/audioplay.swf?file=http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/AudioPlay.mp3&auto=no&sendstop=yes&repeat=1&buttondir=http://www.strangecube.com/audioplay/online/alpha_buttons/negative&bgcolor=0xffffff&mode=playpause"><PARAM NAME=quality VALUE=high><PARAM NAME=wmode VALUE=transparent><embed src="http://www.strangecube.com/audioplay/online/audioplay.swf?file=http://media.blubrry.com/blubrry/content.blubrry.com/blubrry/AudioPlay.mp3&auto=no&sendstop=yes&repeat=1&buttondir=http://www.strangecube.com/audioplay/online/alpha_buttons/negative&bgcolor=0xffffff&mode=playpause" quality=high wmode=transparent width="30" height="30" align="" TYPE="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object></div><!-- End of generated code -->
		*/
		
		
?>
<link rel="stylesheet" href="<?php echo powerpress_get_root_url(); ?>3rdparty/colorpicker/css/colorpicker.css" type="text/css" />
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>3rdparty/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>player.js"></script>
<script type="text/javascript">

function rgb2hex(rgb) {
 
 rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
 function hex(x) {
  hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
  return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
 }
 
 if( rgb )
	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
 return '';
}

function UpdatePlayerPreview(name, value)
{
	if( typeof(generator) != "undefined" ) // Update the Maxi player...
	{
		generator.updateParam(name, value);
		generator.updatePlayer();
	}
	
	if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
		update_audio_player();
}
				
jQuery(document).ready(function($) {
	
	jQuery('.color_preview').ColorPicker({
		onSubmit: function(hsb, hex, rgb, el) {
			jQuery(el).css({ 'background-color' : '#' + hex });
			jQuery(el).ColorPickerHide();
			var Id = jQuery(el).attr('id');
			Id = Id.replace(/_prev/, '');
			jQuery('#'+ Id  ).val( '#' + hex );
			UpdatePlayerPreview(Id, '#'+hex );
		},
		onBeforeShow: function () {
			jQuery(this).ColorPickerSetColor( rgb2hex( jQuery(this).css("background-color") ) );
		}
	})
	.bind('keyup', function(){
		jQuery(this).ColorPickerSetColor( rgb2hex( jQuery(this).css("background-color") ) );
	});
	
	jQuery('.color_field').bind('change', function () {
		var Id = jQuery(this).attr('id');
		jQuery('#'+ Id + '_prev'  ).css( { 'background-color' : jQuery(this).val() } );
		if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
			update_audio_player();
	});
	
	jQuery('.other_field').bind('change', function () {
		if( typeof(update_audio_player) != "undefined" ) // Update the 1 px out player...
			update_audio_player();
	});

});
	
</script>


<!-- special page styling goes here -->
<style type="text/css">
div.color_control { display: block; float:left; width: 100%; padding:  0; }
div.color_control input { display: inline; float: left; }
div.color_control div.color_picker { display: inline; float: left; margin-top: 3px; }
#player_preview { margin-bottom: 0px; height: 30px; margin-top: 8px; }
input#colorpicker-value-input {
	width: 60px;
	height: 16px;
	padding: 0;
	margin: 0;
	font-size: 12px;
}
</style>
<?php
	
	// mainly 2 pages, first page selects a player, second configures the player, if there are optiosn to configure for that player. If the user is on the second page,
	// a link should be provided to select a different player.
	if( $select_player )
	{
?>
<input type="hidden" name="action" value="powerpress-select-player" />
<h2><?php echo __('Blubrry PowerPress Player Options'); ?></h2>
<p style="margin-bottom: 0;"><?php echo __('Select the media player you would like to use.'); ?></p>
<table class="form-table">
<tr valign="top">
<th scope="row"><?php echo __('Select Player'); ?></th>  
<td>

	<ul>
		<li><label><input type="radio" name="Player[player]" value="default" <?php if( $General['player'] == 'default' || !isset($General['default']) ) echo 'checked'; ?> /> Flow Player Classic (default)</label></li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
<?php
			$media_url = '';
			$content = '';
			$content .= '<div id="flow_player_classic"></div>'.PHP_EOL;
			$content .= '<script type="text/javascript">'.PHP_EOL;
			$content .= "pp_flashembed(\n";
			$content .= "	'flow_player_classic',\n";
			$content .= "	{src: '". powerpress_get_root_url() ."FlowPlayerClassic.swf', width: 320, height: 24 },\n";
			$content .= "	{config: { autoPlay: false, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$Audio['default']}', loop: false, autoRewind: true } }\n";
			$content .= ");\n";
			$content .= "</script>\n";
			echo $content;
?>
			</p>
			<p>
				Flow Player Classic is an open source flash player that supports both audio (mp3 only) and video (flv only) media files.
				It includes all the necessary features for playback including a play/pause button, scrollable position bar, ellapsed time, 
				total time, mute button and volume control.
			</p>
			<p>
				Flow Player Classic was chosen as the default player in Blubrry PowerPress because if its backwards compatibility with older versions of Flash and support for both audio and flash video.
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" value="audio-player" <?php if( $General['player'] == 'audio-player' ) echo 'checked'; ?> /> 1 Pixel Out Audio Player</label></li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
<script language="JavaScript" src="<?php echo powerpressplayer_get_root_url();?>audio-player.js"></script>
<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url();?>audio-player.swf" id="audioplayer1" height="24" width="290">
<param name="movie" value="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf">
<param name="FlashVars" value="playerID=1&amp;soundFile=<?php echo $Audio['audio-player']; ?>">
<param name="quality" value="high">
<param name="menu" value="false">
<param name="wmode" value="transparent">
</object>			</p>
			<p>
				1 Pixel Out Audio Player is a popular customizable audio (mp3 only) flash player.
				Features include an animated play/pause button, scrollable position bar, ellapsed/remaining time, volume control and color styling options.
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" value="flashmp3-maxi" <?php if( $General['player'] == 'flashmp3-maxi' ) echo 'checked'; ?> /> Flash Player Maxi</label></li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
				<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url(); ?>player_mp3_maxi.swf" width="200" height="20">
    <param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>player_mp3_maxi.swf" />
    <param name="bgcolor" value="#ffffff" />
    <param name="FlashVars" value="mp3=<?php echo $Audio['flashmp3-maxi']; ?>&amp;showstop=1&amp;showinfo=1&amp;showvolume=1" />
</object>
			</p>
			<p>
				Flash Maxi Player is a customizable open source audio (mp3 only) flash player. Features include pause/play/stop/file info buttons, scrollable position bar, volume control and color styling options.
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" value="simple_flash" <?php if( $General['player'] == 'simple_flash' ) echo 'checked'; ?> /> Simple Flash MP3 Player</label></li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>

    <object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" width="150" height="50">
    <param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" />
    <param name="wmode" value="transparent" />
    <param name="FlashVars" value="url=http://&amp;autostart=false" />
    <param name="quality" value="high" />
    <embed wmode="transparent" src="url=<?php echo $Audio['simple_flash']; ?>&amp;autostart=false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="150" height="50"></embed>
</object>
			</p>
			<p>
				Simple Flash MP3 Player is a free and simple audio (mp3 only) flash player. Features include play/pause and stop buttons.
			</p>
		</li>
		
		<li><label><input type="radio" name="Player[player]" value="audioplay" <?php if( $General['player'] == 'audioplay' ) echo 'checked'; ?> /> AudioPlay</label></li>
		<li style="margin-left: 30px; margin-bottom:16px;">
			<p>
                           <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="30" height="30">
			<param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>audioplay.swf?buttondir=<?php echo powerpressplayer_get_root_url(); ?>buttons/negative" />
			<param name="quality" value="high" />
			<param name="bgcolor" value="#FFFFFF" />
			
                        <param name="FlashVars" value="buttondir=<?php echo powerpressplayer_get_root_url(); ?>buttons/negative&amp;mode=playstop" />
			    <embed src="<?php echo powerpressplayer_get_root_url(); ?>audioplay.swf?file=<?php echo $Audio['audioplay']; ?>&amp;auto=no&amp;&sendstop=yes&amp;repeat=1&amp;mode=playpause&amp;buttondir=<?php echo powerpressplayer_get_root_url(); ?>buttons/negative" quality=high bgcolor=#FFFFFF width="30" height="30"
				align="" TYPE="application/x-shockwave-flash"
				pluginspage="http://www.macromedia.com/go/getflashplayer">
			    </embed>

		</object>

			</p>
			<p>
				AudioPlay is one button freeware audio (mp3 only) flash player. Features include a play/stop or play/pause button available in two sizes in either black or white.
			</p>
		</li>
		
		
	</ul>

</td>
</tr>
</table>
<h4 style="margin-bottom: 0;">Click 'Save Changes' to configure selected player.</h4>
<?php
	}
	else
	{
?>
<h2><?php _e("Configure Player"); ?></h2>
<p style="margin-bottom: 20px;"><strong><a href="<?php echo admin_url(( @$General['advanced_mode']?'admin':'options-general').".php?page=powerpress/powerpressadmin_player.php&amp;sp=1"); ?>" title="Select a different flash player">Select a different flash player</a></strong></p>
<?php 
		// Start adding logic here to display options based on the player selected...
		switch( $General['player'] )
		{
			case 'audio-player': {
			
            $PlayerSettings = powerpress_get_settings('powerpress_audio-player');
            if($PlayerSettings == ""):
                $PlayerSettings = array(
                    'width'=>'290',
                    'transparentpagebg' => 'yes',
                    'lefticon' => '#333333',
                    'leftbg' => '#CCCCCC',
                    'bg' => '#E5E5E5',
                    'voltrack' => '#F2F2F2',
                    'volslider' => '#666666',
                    'rightbg' => '#B4B4B4',
                    'rightbghover' => '#999999',
                    'righticon' => '#333333',
                    'righticonhover' => '#FFFFFF',
                    'loader' => '#009900',
                    'track' => '#FFFFFF',
                    'tracker' => '#DDDDDD',
                    'border' => '#CCCCCC',
                    'skip' => '#666666',
                    'text' => '#333333',
                    'pagebg' => '',
                    'rtl' => 'no',
										'initialvolume'=>'60'
                    
                    );
            endif;
                            $keys = array_keys($PlayerSettings);
                    $flashvars ='';
                foreach ($keys as $key) {
                    if($PlayerSettings[$key] != "") {                        
                        $flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$PlayerSettings[$key]);
                    }
								}

                if($PlayerSettings['pagebg'] != ""){
                    $transparency = '<param name="bgcolor" value="'.$PlayerSettings['pagebg'].'" />';
                    $PlayerSettings['transparentpagebg'] = "no";
                    $flashvars .= '&amp;transparentpagebg=no';
                    $flashvars .= '&amp;pagebg='.$PlayerSettings['pagebg'];
                }
                else {
                    $PlayerSettings['transparentpagebg'] = "yes";
                    $transparency = '<param name="wmode" value="transparent" />';
                    $flashvars .= '&amp;transparentpagebg=yes';
                }
?>

<script type="text/javascript">

function update_audio_player()
{
	var myParams = new Array("lefticon","leftbg", "bg", "voltrack", "rightbg", "rightbghover", "righticon", "righticonhover", "loader", "track", "tracker", "border", "skip", "text", "pagebg", "rtl", "animation", "titles", "initialvolume");
	var myWidth = document.getElementById('player_width').value;
	var myBackground = '';
	if( myWidth < 10 || myWidth > 900 )
		myWidth = 290;
	
	var out = '<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf" width="'+myWidth+'" height="24">'+"\n";
	out += '    <param name="movie" value="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf" />'+"\n";
	out += '    <param name="FlashVars" value="playerID=1&amp;soundFile=<?php echo $Audio['audio-player']; ?>';
	
	var x = 0;
	for( x = 0; x < myParams.length; x++ )
	{
		if( myParams[ x ] == 'border' )
			var Element = document.getElementById( 'player_border' );
		else
			var Element = document.getElementById( myParams[ x ] );
		
		if( Element )
		{
			if( Element.value != '' )
			{
				out += '&amp;';
				out += myParams[ x ];
				out += '=';
				out += Element.value.replace(/^#/, '');
				if( myParams[ x ] == 'pagebg' )
				{
					myBackground = '<param name="bgcolor" value="'+ Element.value +'" />';
					out += '&amp;transparentpagebg=no';
				}
			}
			else
			{
				if( myParams[ x ] == 'pagebg' )
				{
					out += '&amp;transparentpagebg=yes';
					myBackground = '<param name="wmode" value="transparent" />';
				}
			}
		}
	}
	
	out += '" />'+"\n";
	out += '<param name="quality" value="high" />';
	out += '<param name="menu" value="false" />';
	out += myBackground;
	out += '</object>';
	
	var player = document.getElementById("player_preview");
	player.innerHTML = out;
}

</script>
	<input type="hidden" name="action" value="powerpress-audio-player" />
	Configure the 1 pixel out Audio Player
	
	
<table class="form-table">
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Preview of Player"); ?> 
		</th>
		<td>
			<div id="player_preview">
<object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url();?>audio-player.swf" id="audioplayer1" height="24" width="<?php echo $PlayerSettings['width']; ?>">
<param name="movie" value="<?php echo powerpressplayer_get_root_url();?>/audio-player.swf">
<param name="FlashVars" value="playerID=1&amp;soundFile=<?php echo $Audio['audio-player']; ?><?php echo $flashvars;?>">
<param name="quality" value="high">
<param name="menu" value="false">
<?php echo $transparency; ?>
</object>
			</div>
		</td>
	</tr>
	<tr><td><h2>General Settings</h2></td></tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Page Background Color"); ?> <br />
                        <small><?php _e('leave blank for transparent'); ?></small>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="pagebg" name="Player[pagebg]" class="color_field" value="<?php echo $PlayerSettings['pagebg']; ?>" maxlength="20" />
				<img id="pagebg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['pagebg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>	<tr valign="top">
		<th scope="row">
			<?php _e("Player Background Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bg" name="Player[bg]" class="color_field" value="<?php echo $PlayerSettings['bg']; ?>" maxlength="20" />
				<img id="bg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Width (in pixels)"); ?> 
		</th>
		<td>
          <input type="text" style="width: 50px;" id="player_width" name="Player[width]" class="other_field" value="<?php echo $PlayerSettings['width']; ?>" maxlength="20" />
				width of the player. e.g. 290 (290 pixels) or 100%
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Right-to-Left"); ?> 
		</th>
		<td>
			<select style="width: 50px;" id="rtl" name="Player[rtl]" class="other_field"> 
			<?php
			$option = array('no','yes');
			 foreach($option as $option){
							if($PlayerSettings['rtl'] == $option):
									$selected = " SELECTED";
							else:
									$selected = "";
							endif;
							echo '<option value="'. $option .'"'. $selected .' >'. ucwords($option) .'</option>';
			}?>
          </select>			switches the layout to animate from the right to the left
		</td>
	</tr>
	
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Play Animation"); ?> 
		</th>
		<td>
			<div class="color_control">
<select style="width: 50px;" id="animation" name="Player[animation]" class="other_field"> 
                                <?php
                                $option = array('no','yes');
                                 foreach($option as $option){
                                        if($PlayerSettings['animation'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. ucwords($option) .'</option>';
                                }?>
                                </select>			if no, player is always open</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Text In Player"); ?> 
		</th>
		<td>
          <input type="text" style="width: 60%;" id="titles" name="Player[titles]" class="other_field" value="<?php echo $PlayerSettings['titles']; ?>" maxlength="100" />
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Text Color"); ?> 
		</th>
		<td>
			<div class="color_control">
                <input type="text" style="width: 100px;" id="text" name="Player[text]" class="color_field" value="<?php echo $PlayerSettings['text']; ?>" maxlength="20" />
						<img id="text_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['text']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Loading Bar Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="loader" name="Player[loader]" class="color_field" value="<?php echo $PlayerSettings['loader']; ?>" maxlength="20" />
				<img id="loader_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['loader']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Progress Track Background"); ?> 
		</th>
		<td>
			<div class="color_control">
										<input type="text" style="width: 100px;" id="track" name="Player[track]" class="color_field" value="<?php echo $PlayerSettings['track']; ?>" maxlength="20" />
										<img id="track_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['track']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Progress Track Color"); ?> 
		</th>
		<td>
			<div class="color_control">
                            <input type="text" style="width: 100px;" id="tracker" name="Player[tracker]" class="color_field" value="<?php echo $PlayerSettings['tracker']; ?>" maxlength="20" />
											<img id="tracker_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['tracker']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Progress Bar Border"); ?> 
		</th>
		<td>
			<div class="color_control">
                            <input type="text" style="width: 100px;" id="player_border" name="Player[border]" class="color_field" value="<?php echo $PlayerSettings['border']; ?>" maxlength="20" />
											<img id="player_border_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['border']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>       
        <tr><td colspan="2"><h2>Volumn Settings</h2></td></tr>
				
				
				
	<tr valign="top">
		<th scope="row">
			<?php _e("Initial Volume"); ?> 
		</th>
		<td>
			<select style="width: 100px;" id="initialvolume" name="Player[initialvolume]" class="other_field"> 
			<?php
			
			for($x = 0; $x <= 100; $x +=5 )
			{
				echo '<option value="'. $x .'"'. ($PlayerSettings['initialvolume'] == $x?' selected':'') .'>'. $x .'%</option>';
			}?>
			</select> initial volume level (default: 60)
		</td>
	</tr>
				
	<tr valign="top">
		<th scope="row">
			<?php _e("Volumn Background Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="leftbg" name="Player[leftbg]" class="color_field" value="<?php echo $PlayerSettings['leftbg']; ?>" maxlength="20" />
				<img id="leftbg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['leftbg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Speaker Icon Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="lefticon" name="Player[lefticon]" class="color_field" value="<?php echo $PlayerSettings['lefticon']; ?>" maxlength="20" />
				<img id="lefticon_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['lefticon']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Volume Icon Background"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="voltrack" name="Player[voltrack]" class="color_field" value="<?php echo $PlayerSettings['voltrack']; ?>" maxlength="20" />
				<img id="voltrack_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['voltrack']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Volume Slider Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="volslider" name="Player[volslider]" class="color_field" value="<?php echo $PlayerSettings['volslider']; ?>" maxlength="20" />
				<img id="volslider_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['volslider']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr><td colspan="2"><h2>Play/Pause Settings</h2></td></tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Play/Pause Background Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="rightbg" name="Player[rightbg]" class="color_field" value="<?php echo $PlayerSettings['rightbg']; ?>" maxlength="20" />
				<img id="rightbg_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['rightbg']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Play/Pause Hover Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="rightbghover" name="Player[rightbghover]" class="color_field" value="<?php echo $PlayerSettings['rightbghover']; ?>" maxlength="20" />
				<img id="rightbghover_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['rightbghover']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Play/Pause Icon Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="righticon" name="Player[righticon]" class="color_field" value="<?php echo $PlayerSettings['righticon']; ?>" maxlength="20" />
				<img id="righticon_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['righticon']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Play/Pause Icon Hover Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="righticonhover" name="Player[righticonhover]" class="color_field" value="<?php echo $PlayerSettings['righticonhover']; ?>" maxlength="20" />
				<img id="righticonhover_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['righticonhover']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>

</table>
	
	
<?php
			}; break;
                        case 'simple_flash':{ ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e("Preview of Player"); ?> 
		</th>
		<td>
			<p>
    <object type="application/x-shockwave-flash" data="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" width="150" height="50">
    <param name="movie" value="<?php echo powerpressplayer_get_root_url(); ?>simple_mp3.swf" />
    <param name="wmode" value="transparent" />
    <param name="FlashVars" value="url=http://&amp;autostart=false" />
    <param name="quality" value="high" />
    <embed wmode="transparent" src="url=<?php echo $Audio['simple_flash']; ?>&amp;autostart=false" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="150" height="50"></embed>
</object>

			</p>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			&nbsp;
		</th>
		<td>
			<p>Simple Flash Player has no additional settings.</p>
		</td>
	</tr>
</table>                            
              <?php          }; break;

			case 'flashmp3-maxi': {
                            //get settings for Flash MP3 Maxi player
                            $PlayerSettings = powerpress_get_settings('powerpress_flashmp3-maxi');
                            
                            
                            //set array values for dropdown lists
                            $options = array('0','1');
                            $autoload = array('always','never','autohide');
                            $volume = array('0','25','50','75','100','125','150','175','200');
                            
                            //set array values for flash variables with no dependencies
                            $keys = array('bgcolor1','bgcolor2','bgcolor','textcolor','buttoncolor','buttonovercolor','showstop','showinfo','showvolume','height','width','showloading','buttonwidth','volume','showslider');
                            
                            //set PlayerSettings as blank array for initial setup
                                //This keeps the foreach loop from returning an error
                            if($PlayerSettings == ""){
                                $PlayerSettings = array(
                                    'bgcolor1'=>'#7c7c7c',
                                    'bgcolor2'=>'#333333',
                                    'textcolor' => '#FFFFFF',
                                    'buttoncolor' => '#FFFFFF',
                                    'buttonovercolor' => '#FFFF00',
                                    'showstop' => '0',
                                    'showinfo' => '0',
                                    'showvolume' => '1',
                                    'height' => '20',
                                    'width' => '200',
                                    'showloading' => 'autohide',
                                    'buttonwidth' => '26',
                                    'volume' => '100',
                                    'showslider' => '1',
																		'slidercolor1'=>'#cccccc',
																		'slidercolor2'=>'#888888',
                                    'sliderheight' => '10',
                                    'sliderwidth' => '20',
                                    'loadingcolor' => '#FFFF00', 
                                    'volumeheight' => '6',
                                    'volumewidth' => '30',
                                    'sliderovercolor' => '#eeee00'
                                    );
                            }

                            $flashvars = '';
                            $flashvars .= "mp3=".$Audio['flashmp3-maxi'];

                            //set non-blank options without dependencies as flash variables for preview
                            foreach($keys as $key) {
                                if($PlayerSettings[$key] != "") {
                                    $flashvars .= '&amp;'. $key .'='. preg_replace('/\#/','',$PlayerSettings[''.$key.'']);
                                }
                            }
                            //set slider dependencies
                            if($PlayerSettings['showslider'] != "0") {
                                if($PlayerSettings['sliderheight'] != "") {
                                    $flashvars .= '&amp;sliderheight='. $PlayerSettings['sliderheight'];
                                }
                                if($PlayerSettings['sliderwidth'] != "") {
                                    $flashvars .= '&amp;sliderwidth='. $PlayerSettings['sliderwidth'];
                                }
                                if($PlayerSettings['sliderovercolor'] != ""){
                                    $flashvars .= '&amp;sliderovercolor='. preg_replace('/\#/','',$PlayerSettings['sliderovercolor']);
                                }
                            }
                            //set volume dependencies
                            if($PlayerSettings['showvolume'] != "0") {
                                if($PlayerSettings['volumeheight'] != "") {
                                    $flashvars .= '&amp;volumeheight='. $PlayerSettings['volumeheight'];
                                }
                                if($PlayerSettings['volumewidth'] != "") {
                                    $flashvars .= '&amp;volumewidth='. $PlayerSettings['volumewidth'];
                                }
                            }
                            //set autoload dependencies
                            if($PlayerSettings['showautoload'] != "never") {
                                if($PlayerSettings['loadingcolor'] != "") {
                                    $flashvars .= '&amp;laodingcolor='. preg_replace('/\#/','',$PlayerSettings['loadingcolor']);
                                }
                            }


                            //set default width for object
                            if($PlayerSettings['width'] == ""){
                                $width = "200";
                            }else{
                                $width = $PlayerSettings['width'];
                            }
                            if($PlayerSettings['height'] == ""){
                                $height = "20";
                            }else{
                                $height = $PlayerSettings['height'];
                            }

                            //set background transparency
                            if($PlayerSettings['bgcolor'] != ""){
                                $transparency = '<param name="bgcolor" value="'. $color7 .'" />';
                            }else{
                                $transparency = '<param name="wmode" value="transparent" />';
                            }
                            
                            //set flashvars
                            if($flashvars != ""){
                                $flashvars= '<param name="FlashVars" value="'. $flashvars .'" />'.PHP_EOL;
                            }

?>
	<input type="hidden" name="action" value="powerpress-flashmp3-maxi" />
	Configure the Flash Mp3 Maxi Player
<table class="form-table">
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Preview of Player"); ?> 
		</th>
		<td>
			<div id="player_preview">
<?php 

$content = '<object type="application/x-shockwave-flash" data="'. powerpressplayer_get_root_url().'player_mp3_maxi.swf" width="'. $width.'" height="'. $height .'">'.PHP_EOL;
$content .=  '<param name="movie" value="'. powerpressplayer_get_root_url().'player_mp3_maxi.swf" />'.PHP_EOL;
$content .= $transparency.PHP_EOL;
$content .= $flashvars;
$content .= '</object>'.PHP_EOL;

// print $content;
?>
                        </div>

<script type="text/javascript" src="<?php echo powerpress_get_root_url(); ?>3rdparty/maxi_player/generator.js"></script>
<input type="hidden" id="gen_mp3" name="gen_mp3" value="<?php echo $Audio['flashmp3-maxi']; ?>" />


		</td>
	</tr>
        <tr valign="top">
            <td colspan="2">
            <h2><?php _e('General Player Settings'); ?></h2>
            <?php _e('leave blank for default values'); ?>
            </td>
        </tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Player Gradient Color Top"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor1"  name="Player[bgcolor1]" class="color_field" value="<?php echo $PlayerSettings['bgcolor1']; ?>" maxlength="20" />
				<img id="bgcolor1_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor1']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Player Gradient Color Bottom"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor2" name="Player[bgcolor2]" class="color_field" value="<?php echo $PlayerSettings['bgcolor2']; ?>" maxlength="20" />
				<img id="bgcolor2_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor2']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Background Color"); ?><br />
                        <small><?php _e("leave blank for transparent");?></small>
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor" name="Player[bgcolor]" class="color_field" value="<?php echo $PlayerSettings['bgcolor']; ?>" maxlength="20" />
				<img id="bgcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Text Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="textcolor" name="Player[textcolor]" class="color_field" value="<?php echo $PlayerSettings['textcolor']; ?>" maxlength="20" />
				<img id="textcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['textcolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Player Height (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="player_height" name="Player[height]" value="<?php echo $PlayerSettings['height']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Player Width (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="player_width" name="Player[width]" value="<?php echo $PlayerSettings['width']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
        <tr valign="top">
            <td colspan="2">
            <h2><?php _e('Button Settings'); ?></h2>
            </td>
        </tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Button Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="buttoncolor" name="Player[buttoncolor]" class="color_field" value="<?php echo $PlayerSettings['buttoncolor']; ?>" maxlength="20" />
				<img id="buttoncolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['buttoncolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Button Hover Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="buttonovercolor" name="Player[buttonovercolor]" class="color_field" value="<?php echo $PlayerSettings['buttonovercolor']; ?>" maxlength="20" />
				<img id="buttonovercolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['buttonovercolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Button Width (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="buttonwidth" name="Player[buttonwidth]" value="<?php echo $PlayerSettings['buttonwidth']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Show Stop Button"); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showstop" name="Player[showstop]"> 
                               <?php foreach($options as $option){
                                        if($PlayerSettings['showstop'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if($option == "1"):
                                            $name = "Yes";
                                        else:
                                            $name = "No";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. $name .'</option>';
                                }?>
                                </select>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Show Info"); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showinfo" name="Player[showinfo]"> 
                                <?php foreach($options as $option){
                                        if($PlayerSettings['showinfo'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if($option == "1"):
                                            $name = "Yes";
                                        else:
                                            $name = "No";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. $name .'</option>';
                                }?>
                                </select>
			</div>
		</td>
	</tr>
        <tr valign="top">
            <td colspan="2">
            <h2><?php _e('Volume Settings'); ?></h2>
            </td>
        </tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Show Volume"); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showvolume" name="Player[showvolume]"> 
                                <?php foreach($options as $option){
                                        if($PlayerSettings['showvolume'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if($option == "1"):
                                            $name = "Yes";
                                        else:
                                            $name = "No";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. $name .'</option>';
                                }?>
                                </select>
			</div>
		</td>
	</tr>	
        <tr valign="top">
		<th scope="row">
			<?php _e("Volume"); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="volume" name="Player[volume]"> 
                                <?php foreach($volume as $volume){
                                        if($PlayerSettings['volume'] == $volume):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        echo '<option value="'. $volume .'"'. $selected .' >'. $volume .'%</option>';
                                }?>
                                </select>
			</div>
		</td>
	</tr>	
	<tr valign="top">
		<th scope="row">
			<?php _e("Volume Height (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="volumeheight" name="Player[volumeheight]" value="<?php echo $PlayerSettings['volumeheight']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Volume Width (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="volumewidth" name="Player[volumewidth]" value="<?php echo $PlayerSettings['volumewidth']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
        <tr valign="top">
            <td colspan="2">
            <h2><?php _e('Slider Settings'); ?></h2>
            </td>
        </tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Show Slider"); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showslider" name="Player[showslider]"> 
                                <?php foreach($options as $option){
                                        if($PlayerSettings['showslider'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if($option == "1"):
                                            $name = "Yes";
                                        else:
                                            $name = "No";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. $name .'</option>';
                                }?>
                                </select>
			</div>
		</td>
	</tr>	
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Slider Color Top"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="slidercolor1" name="Player[slidercolor1]" class="color_field" value="<?php echo $PlayerSettings['slidercolor1']; ?>" maxlength="20" />
				<img id="slidercolor1_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['slidercolor1']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Slider Color Bottom"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="slidercolor2" name="Player[slidercolor2]" class="color_field" value="<?php echo $PlayerSettings['slidercolor2']; ?>" maxlength="20" />
				<img id="slidercolor2_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['slidercolor2']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Slider Hover Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="sliderovercolor" name="Player[sliderovercolor]" class="color_field" value="<?php echo $PlayerSettings['sliderovercolor']; ?>" maxlength="20" />
				<img id="sliderovercolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['sliderovercolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Slider Height (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="sliderheight" name="Player[sliderheight]" value="<?php echo $PlayerSettings['sliderheight']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Slider Width (in pixels)"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 50px;" id="sliderwidth" name="Player[sliderwidth]" value="<?php echo $PlayerSettings['sliderwidth']; ?>" maxlength="20" />
			</div>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Show Loading Buffer"); ?> 
		</th>
		<td>
			<div class="color_control">
				<select style="width: 100px;" id="showloading" name="Player[showloading]"> 
                                <?php foreach($autoload as $option){
                                        if($PlayerSettings['showloading'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. $option .'</option>';
                                }?>
                                </select>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Loading Buffer Color"); ?> 
		</th>
		<td>
			<div class="color_control">
				<input type="text" style="width: 100px;" id="loadingcolor" name="Player[loadingcolor]" class="color_field" value="<?php echo $PlayerSettings['loadingcolor']; ?>" maxlength="20" />
				<img id="loadingcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['loadingcolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>

</table>

<script type="text/javascript">

	generator.player = '<?php echo powerpressplayer_get_root_url(); ?>player_mp3_maxi.swf';
	generator.addParam("gen_mp3", "mp3", "url", '');
	generator.addParam("player_height", "height", "int", "20");
	generator.addParam("player_width", "width", "int", "200");
	generator.addParam("bgcolor1", "bgcolor1", "color", "#7c7c7c");
	generator.addParam("bgcolor2", "bgcolor2", "color", "#333333");
	generator.addParam("bgcolor", "bgcolor", "color", "");
	generator.addParam("textcolor", "textcolor", "color", "#FFFFFF");
	generator.addParam("loadingcolor", "loadingcolor", "color", "#FFFF00");
	generator.addParam("buttoncolor", "buttoncolor", "color", "#FFFFFF");
	generator.addParam("buttonovercolor", "buttonovercolor", "color", "#FFFF00");
	generator.addParam("showloading", "showloading", "text", "autohide");
	generator.addParam("showinfo", "showinfo", "bool", "0");
	generator.addParam("showstop", "showstop", "int", "0");
	generator.addParam("showvolume", "showvolume", "int", "0");
	generator.addParam("buttonwidth", "buttonwidth", "int", "26");
	generator.addParam("volume", "volume", "int", "100");
	generator.addParam("volumeheight", "volumeheight", "int", "6");
	generator.addParam("volumewidth", "volumewidth", "int", "30");
	generator.addParam("sliderovercolor", "sliderovercolor", "color", "#eeee00");
	generator.addParam("showslider", "showslider", "bool", "1");
	generator.addParam("slidercolor1", "slidercolor1", "color", "#cccccc");
	generator.addParam("slidercolor2", "slidercolor2", "color", "#888888");
	generator.addParam("sliderheight", "sliderheight", "int", "10");
	generator.addParam("sliderwidth", "sliderwidth", "int", "20");
	
	generator.updatePlayer();
</script>

<?php
			}; break;
			
			case 'audioplay': {
				$PlayerSettings = powerpress_get_settings('powerpress_audioplay');
                                if($PlayerSettings == "") {
                                    $PlayerSettings = array(
                                    'bgcolor' => '',
                                    'buttondir' => 'negative',
                                    'mode' => 'playpause'
                                    );
                                }
                                
                                // Set standard variables for player
                                $flashvars = 'file='. $Audio['audioplay'];
                                $flashvars .= '&amp;repeat=1';
                                
                                if($PlayerSettings['bgcolor'] == ""){
                                    $flashvars .= "&amp;usebgcolor=no";
                                    $transparency = '<param name="wmode" value="transparent" />';
                                    $htmlbg = "";
                                }
                                else{
                                    $flashvars .= "&amp;bgcolor=". preg_replace('/\#/','0x',$PlayerSettings['bgcolor']);
                                    $transparency = '<param name="bgcolor" value="'. $PlayerSettings['bgcolor']. '" />';
                                    $htmlbg = 'bgcolor="'. $PlayerSettings['bgcolor'].'"';

                                }
                                
                                if($PlayerSettings['buttondir'] == "") {
                                    $flashvars .= "&amp;buttondir=".powerpressplayer_get_root_url()."buttons/negative";
                                }else{
                                    $flashvars .= "&amp;buttondir=".powerpressplayer_get_root_url().'buttons/'.$PlayerSettings['buttondir'];
                                    
                                }
																
																$width = $height = (strstr($PlayerSettings['buttondir'], 'small')===false?30:15);
                                
                                $flashvars .= '&amp;mode='. $PlayerSettings['mode'];
                                
?>
        	<input type="hidden" name="action" value="powerpress-audioplay" />
	Configure the AudioPlay Player<br clear="all" />

<table class="form-table">
	
	<tr valign="top">
		<th scope="row">
			<?php _e("Preview of Player"); ?> 
		</th>
		<td colspan="2">
			<div id="player_preview">
                        
<?php                                                                         
$content = '<object type="application/x-shockwave-flash" width="'. $width .'" height="'. $height .'" data="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'">'.PHP_EOL;
$content .= '<param name="movie" value="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'" />'.PHP_EOL;
$content .= '<param name="quality" value="high" />'.PHP_EOL;
$content .= $transparency.PHP_EOL;
$content .= '<param name="FlashVars" value="'.$flashvars.'" />'.PHP_EOL;
$content .= '<embed src="'. powerpressplayer_get_root_url().'audioplay.swf?'.$flashvars.'" quality="high"  width="30" height="30" type="application/x-shockwave-flash">'.PHP_EOL;
$content .= "</embed>\n		</object>\n";

print $content;
?>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2"><h2>General Settings</h2></td>
        </tr>
        <tr valign="top">
		<th scope="row">
			<?php _e("Background Color"); ?> <br />
                        <small><?php _e("leave blank for transparent");?></small>
		</th>
		<td valign="top">
			<div class="color_control">
				<input type="text" style="width: 100px;" id="bgcolor" name="Player[bgcolor]" class="color_field" value="<?php echo $PlayerSettings['bgcolor']; ?>" maxlength="20" />
				<img id="bgcolor_prev" src="<?php echo powerpress_get_root_url(); ?>images/color_preview.gif" width="14" height="14" style="background-color: <?php echo $PlayerSettings['bgcolor']; ?>;" class="color_preview" />
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Player Mode"); ?>
		</th>
		<td valign="top">
			<div class="color_control">
                            <select name="Player[mode]" id="mode">
                                <?php $options = array('playpause','playstop');
                                 foreach($options as $option){
                                        if($PlayerSettings['mode'] == $option):
                                            $selected = " SELECTED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if($option == "playpause"):
                                            $name = "Play/Pause";
                                        else:
                                            $name = "Play/Stop";
                                        endif;
                                        echo '<option value="'. $option .'"'. $selected .' >'. $name .'</option>';
                                }?>
                                
                            </select>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<?php _e("Player Button"); ?>
		</th>
		<td valign="top">
			<div class="color_control">
                        <table cellpadding="0" cellspacing="0">
                                <?php $options = array('classic','classic_small','negative','negative_small');
                                 foreach($options as $option){
                                        if($PlayerSettings['buttondir'] == $option):
                                            $selected = " CHECKED";
                                        else:
                                            $selected = "";
                                        endif;
                                        if(($option == "classic") || ($option == "classic_small")){
                                            $td = '<td style="background: #999;" align="center">';
                                            $warning = "(ideal for dark backgrounds)";
                                            if($option == "classic_small") {
                                                $name = "Small White";
                                            }else{
                                                $name = "Large White";
                                            }
                                        }
                                        else {
                                            $td = '<td align="center">';
                                            $warning = "";
                                            if($option == "negative_small") {
                                                $name = "Small Black";
                                            }else{
                                                $name = "Large Black";
                                            }

                                        }
                                        echo '<tr><td><input type="radio" name="Player[buttondir]" value="'. $option .'"'. $selected .' /></td>'.$td.'<img src="'. powerpressplayer_get_root_url().'buttons/'.$option.'/playup.png" /></td><td>'.$name.' Button '.$warning.'</td></tr>';
                                }?>
                                
                            </table>
			</div>
		</td>
	</tr>

</table>
<?php
			}; break;
		
			default: {
			
?>

<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e("Preview of Player"); ?> 
		</th>
		<td>
			<p>
<?php
			$media_url = '';
			$content = '';
			$content .= '<div id="flow_player_classic"></div>'.PHP_EOL;
			$content .= '<script type="text/javascript">'.PHP_EOL;
			$content .= "pp_flashembed(\n";
			$content .= "	'flow_player_classic',\n";
			$content .= "	{src: '". powerpress_get_root_url() ."FlowPlayerClassic.swf', width: 320, height: 24 },\n";
			$content .= "	{config: { autoPlay: false, autoBuffering: false, initialScale: 'scale', showFullScreenButton: false, showMenu: false, videoFile: '{$Audio['default']}', loop: false, autoRewind: true } }\n";
			$content .= ");\n";
			$content .= "</script>\n";
			echo $content;
?>
			</p>
		</td>
	</tr>
	
	<tr valign="top">
		<th scope="row">
			&nbsp;
		</th>
		<td>
			<p>Flow Player Classic has no additional settings.</p>
		</td>
	</tr>
</table>


<?php
			} break;
		}
?>

<?php
	}
}

?>