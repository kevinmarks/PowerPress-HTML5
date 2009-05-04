<?php

function powerpress_dashboard_head()
{
?>
<style type="text/css">
#blubrry_stats_summary {
	
}
#blubrry_stats_summary label {
	width: 40%;
	max-width: 150px;
	float: left;
}
#blubrry_stats_summary h2 {
	font-size: 14px;
	margin: 0;
	padding: 0;
}
.blubrry_stats_ul {
	padding-left: 20px;
	margin-top: 5px;
	margin-bottom: 10px;
}
.blubrry_stats_ul li {
	list-style-type: none;
	margin: 0px;
	padding: 0px;
}
#blubrry_stats_media {
	display: none;
}
#blubrry_stats_media_show {
	text-align: right;
	font-size: 85%;
}
#blubrry_stats_media h4 {
	margin-bottom: 10px;
}
.blubrry_stats_title {
	margin-left: 10px;
}
.blubrry_stats_updated {
	font-size: 80%;
}
</style>
<?php
}

function powerpress_dashboard_stats_content()
{
	$content = false;
	$StatsCached = get_option('powerpress_stats');
	if( false && !$StatsCached && $StatsCached['updated'] > (time()-(60*60*3)) )
		$content = $StatsCached['content'];
	
	if( !$content )
	{
		$Settings = get_option('powerpress_general');
		$UserPass = $Powerpress['blubrry_userpass'];
		$Keyword = $Powerpress['blubrry_keyword'];
		
		$UserPass = base64_encode('amandato@gmail.com:testit');
		$Keyword = 'compiled_weekly2';
		if( !$UserPass )
		{
			$content = 'Error: No User name or password specified.';
		}
		else
		{
			$api_url = sprintf('%s/stats/%s/summary.html?year=2008&month=7&nobody=1', rtrim(POWERPRESS_BLUBRRY_API_URL, '/'), $Keyword);
			$content = powerpress_remote_fopen($api_url, $UserPass);
			if( $content )
				update_option('powerpress_stats', array('updated'=>time(), 'content'=>$content) );
			else
				$content = 'Error: An error occurred authenticating user.';
		}
	}
//$content = http_get('http://api.blubrry.local/stats/compiled_weekly2/summary.html?year=2008&month=7', 'amandato@gmail.com', 'testit');

//$decoded = my_json_decode($content['data'], true);
//print_r( $content ); 
	echo $content;
	//echo 'Podcast Statistics go here.';
?>
<div id="blubrry_stats_media_show">
	<a href="javascript:void()" onclick="javascript:document.getElementById('blubrry_stats_media').style.display='block';document.getElementById('blubrry_stats_media_show').style.display='none';return false;">more</a>
</div>
<?php
}
	 

function powerpress_dashboard_setup()
{
	$Settings = get_option('powerpress_general');
	$Settings['blubrry_stats'] = true;
	if( $Settings && $Settings['blubrry_stats'] == true )
	{
		wp_add_dashboard_widget( 'powerpress_dashboard_stats', __( 'Blubrry Podcast Statistics' ), 'powerpress_dashboard_stats_content' );
	}
}
	 
add_action('admin_head-index.php', 'powerpress_dashboard_head');
add_action('wp_dashboard_setup', 'powerpress_dashboard_setup');

?>