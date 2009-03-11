<?php
	// mp3info class for use in the Blubrry PowerPress
	// Main purpose of this file is to obtain the duration string for the itunes:duration field.
	// Library is packaged thin with only basic mp3 support.
	// Concept with this library is to get the information without downlaoding the entire file.
	// for efficienccy
	
	class Mp3Info {
		//var $m_DownloadBytesLimit = 1638400; // 200K (200*1024*8) bytes file
		var $m_DownloadBytesLimit = 204800; // 25K (25*1024*8) bytes file
		var $m_RedirectLimit = 5; // Number of times to do the 302 redirect
		var $m_UserAgent = 'Blubrry PowerPress/1.0';
		var $m_error = '';
		var $m_ContentLength = false;
		var $m_RedirectCount = 0;

		// Constructor
		function Mp3Info()
		{
			// Empty for now
		}
		
		/*
		Set how much of the media file to download. Default: 25K
		*/
		function SetDownloadBytesLimit($limit=204800)
		{
			$this->m_DownloadBytesLimit = $limit;
		}
		
		/*
		Set how many times we can follow a HTTP 30x header redirect before we fail.
		*/
		function SetRedirectLimit($limit=5)
		{
			$this->m_RedirectLimit = $limit;
		}
		
		/*
		Set the user agent to be sent by this plugin
		*/
		function SetUserAgent($user_agent)
		{
			$this->m_UserAgent = $user_agent;
		}
		
		/*
		Return the last set error message
		*/
		function GetError()
		{
			return $this->m_error;
		}
		
		/*
		Set the last error message
		*/
		function SetError($msg)
		{
			$this->m_error = $msg;
		}
		
		/*
		Get the length in bytes of the file to download.
		*/
		function GetContentLength()
		{
			return $this->m_ContentLength;
		}
		
		/*
		Get the number of times we followed 30x header redirects
		*/
		function GetRedirectCount()
		{
			return $this->m_RedirectCount;
		}

		/*
		Start the download and get the headers, handles the redirect if there are any
		*/
		function Download($url, $RedirectCount = 0)
		{
			if( !ini_get( 'allow_url_fopen' ) && !function_exists( 'curl_init' ) )
			{
				$this->SetError('Server must either have php.ini allow_url_fopen enabled or PHP CURL library loaded in order to continue.');
				return false;
			}
			
			if( !ini_get( 'allow_url_fopen' ) )
				return $this->DownloadAlt($url);
			
			// The following code relies on fopen_url capability.
			if( $RedirectCount > $this->m_RedirectLimit )
			{
				$this->SetError( 'Download exceeded redirect limit of '.$this->m_RedirectLimit .'.' );
				return false;
			}
			
			$this->m_ContentLength = false;
			$this->m_RedirectCount = $RedirectCount;
			
			$urlParts = parse_url($url);
			if( !isset( $urlParts['path']) )
				$urlParts['path'] = '/';
			if( !isset( $urlParts['port']) )
				$urlParts['port'] = 80;
			if( !isset( $urlParts['scheme']) )
				$urlParts['scheme'] = 'http';
			
			$fp = fsockopen($urlParts['host'], $urlParts['port'], $errno, $errstr, 30);
			if ($fp)
			{
				// Create and send the request headers
				$RequestHeaders = 'GET '.$urlParts['path'].(isset($urlParts['query']) ? '?'.@$urlParts['query'] : '')." HTTP/1.0\r\n";
				$RequestHeaders .= 'Host: '.$urlParts['host'].($urlParts['port'] != 80 ? ':'.$urlParts['port'] : '')."\r\n";
				$RequestHeaders .= "Connection: Close\r\n";
				$RequestHeaders .= "User-Agent: {this->m_UserAgent}\r\n";
				fwrite($fp, $RequestHeaders."\r\n");
				
				$Redirect = false;
				$RedirectURL = false;
				$ContentLength = false;
				$ContentType = false;
				// Loop through the headers
				while( !feof($fp) )
				{
					$line = fgets($fp, 1280); // Get the next header line...
					if( $line === false )
						break; // Something happened
					if ($line == "\r\n")
						break; // Okay we're ending the headers, now onto the content
					
					$line = rtrim($line); // Clean out the new line characters

					list($key, $value) = explode(':', $line, 2);
					$key = trim($key);
					$value = trim($value);
					
					if( stristr($line, '301 Moved Permanently') || stristr($line, '302 Found') || stristr($line, '307 Temporary Redirect') )
					{
						$Redirect = true; // We are dealing with a redirect, lets handle it
					}
					else
					{
						switch( strtolower($key) )
						{
							case 'location': {
								$RedirectURL = $value;
							}; break;
							case 'content-length': {
								$ContentLength = $value;
							}; break;
						}
					}
        }
				
				// Loop through the content till we reach our limit...
				$Content = '';
				if( $this->m_DownloadBytesLimit )
				{
					while( !feof($fp) )
					{
						$Content .= fread($fp, 8096);
						if( strlen($Content) > $this->m_DownloadBytesLimit )
							break; // We got enough of the file we should be able to determine the duration
					}
				}
				fclose($fp);
				
				// If we're dealing with a redirect, lets call our nested function call now
				if( $Redirect )
				{
					unset($Content); // clear what may be using a lot of memory
					return $this->Download($RedirectURL, $RedirectCount + 1 ); // Follow this redirect
				}
				else // Otherwise, lets set the data and return true for part two
				{
					global $TempFile;
					if( function_exists('get_temp_dir') ) // If wordpress function is available, lets use it
						$TempFile = tempnam(get_temp_dir(), 'wp_powerpress');
					else // otherwise use the default path
						$TempFile = tempnam('/tmp', 'wp_powerpress');
					
					if( $TempFile === false )
					{
						$this->SetError('Unable to save media information to temporary directory.');
						return false;
					}
					
					$fp = fopen( $TempFile, 'w' );
					fwrite($fp, $Content);
					fclose($fp);
					
					if( $ContentLength )
						$this->m_ContentLength = $ContentLength;
					return $TempFile;
				}
			}
			$this->SetError('Unable to connect to host '.$urlParts['host'].'.');
			return false;
		}
		
		/*
		Alternative method (curl) for downloading portion of a media file
		*/
		function DownloadAlt($url)
		{
			$curl = curl_init();
			// First, get the content-length...
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, true); // header will be at output
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request 
			curl_setopt($curl, CURLOPT_NOBODY, true );
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_MAXREDIRS, $this->m_RedirectLimit);
			$Headers = curl_exec($curl);
			
			$ContentLength = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
			$HttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			if( $HttpCode != 200 )
			{
				switch( $HttpCode )
				{
					case 301:
					case 302:
					case 307: {
						$this->SetError( 'Download exceeded redirect limit of '.$this->m_RedirectLimit .'.' );
					}; break;
					default: {
						$this->SetError( 'HTTP error '. $HttpCode .'.' );
					}; break;
				}
				return false;
			}
			
			global $TempFile;
			if( function_exists('get_temp_dir') ) // If wordpress function is available, lets use it
				$TempFile = tempnam(get_temp_dir(), 'wp_powerpress');
			else // otherwise use the default path
				$TempFile = tempnam('/tmp', 'wp_powerpress');
			if( $TempFile === false )
			{
				$this->SetError('Unable to create temporary file for checking media information.');
				return false;
			}
				
			$fp = fopen($TempFile, 'w+b');
				// Next get the first chunk of the file...
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
				curl_setopt($curl, CURLOPT_FILE, $fp);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER, false); // header will be at output
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // HTTP request 
				curl_setopt($curl, CURLOPT_NOBODY, false );
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_MAXREDIRS, $this->m_RedirectLimit);
				curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($curl, CURLOPT_HTTPHEADER,array('Range: bytes=0-'.($this->m_DownloadBytesLimit-1) )); 
				//curl_setopt($curl, CURLINFO_HEADER_OUT, true); // For debugging
				// Do the download
				$success = curl_exec($curl);
			fclose($fp);
			
			//$headers_sent = curl_getinfo($curl, CURLINFO_HEADER_OUT); // For debugging
			//var_dump($headers_sent); // For debugging
			
			// Make sure the file is only so big so we don't have memory allocation errors
			$filesize = filesize($TempFile);
			if( $filesize > $this->m_DownloadBytesLimit ) // The web server did not support the Range: request
			{
				$fp = fopen($TempFile, 'w+');
				ftruncate($fp, $this->m_DownloadBytesLimit);
				fclose($fp);
			}
			
			if( $success )
			{
				curl_close($curl);
				
				if( $ContentLength )
					$this->m_ContentLength = $ContentLength;
				return $TempFile;
			}
			else
			{
				if( curl_errno($curl) )
					$this->SetError(curl_error($curl) .' ('.curl_errno($curl).')');
				else
					$this->SetError('Unable to download media.');
			}
			
			curl_close($curl);
			return false;
		}
		
		/*
		Get the MP3 information
		*/
		function GetMp3Info($File)
		{
			$DeleteFile = false;
			if( strtolower( substr($File, 0, 7) ) == 'http://' )
			{
				$LocalFile = $this->Download($File);
				if( $LocalFile === false )
					return false;
				$DeleteFile = true;
			}
			else
			{
				$LocalFile = $File;
			}
			
			// Hack so this works in Windows, helper apps are not necessary for what we're doing anyway
			define('GETID3_HELPERAPPSDIR', true);
			require_once(dirname(__FILE__).'/getid3/getid3.php');
			$getID3 = new getID3;
			$FileInfo = $getID3->analyze( $LocalFile, $this->m_ContentLength );
			if( $DeleteFile )
				@unlink($LocalFile);
				
			if( $FileInfo )
			{
				// Remove extra data that is not necessary for us to return...
				unset($FileInfo['mpeg']);
				unset($FileInfo['audio']);
				if( isset($FileInfo['id3v2']) )
					unset($FileInfo['id3v2']);
				if( isset($FileInfo['id3v1']) )
					unset($FileInfo['id3v1']);
					
				$FileInfo['playtime_seconds'] = round($FileInfo['playtime_seconds']);
				return $FileInfo;
			}
			
			return false;
		}
	};
	
	/*
	// Example usage:
	
	$Mp3Info = new Mp3Info();
	if( $Data = $Mp3Info->GetMp3Info('http://www.podcampohio.com/podpress_trac/web/177/0/TS-107667.mp3') )
	{
		echo 'Success: ';
		echo print_r( $Data );
		echo PHP_EOL;
		exit;
	}
	else
	{
		echo 'Error: ';
		echo $Mp3Info->GetError();
		echo PHP_EOL;
		exit;
	}
	*/
	
	
?>