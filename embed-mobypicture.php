<?php
/*
Plugin Name: Embed Mobypicture
Plugin URI: http://wordpress.rebelic.nl/embed-mobypicture/?utm_source=wordpress&utm_medium=plugin&utm_campaign=embed-mobypicture
Description: Embed Mobypicture videos easily into your pages and posts with the [mobypicture] shortcode.
Version: 1.0
Author: Timan Rebel
Author URI: http://wordpress.rebelic.nl/
*/

function mobypicture_embed_shortcode($atts, $content = null) {
	$api_key = '0Gj9PE0mA4c8xncu';
	
	extract(shortcode_atts(array(  
		"width" 		=> $width,  
		"height" 		=> $height
	), $atts));
	
	$tiny_url = str_replace('http://moby.to/', '', $content);
	
	//get meta data from tiny URL
	$request = new WP_Http();
	$http_result = $request->request('http://api.mobypicture.com?k='.$api_key.'&t='.
									 		$tiny_url.'&format=xml&action=getMediaInfo');
	
	//get video_url from XML, without the use of a XML parser
	$type_match = null;
	preg_match('|<type>(.*?)</type>|is', $http_result['body'], $type_match);
	$type = trim($type_match[1]);
	
	if($type == 'video') {
		if(empty($width))
			$width = 620;
			
		if(empty($height))
			$height = 361;
		
		$video_url_match = null;
		preg_match('|<url_video>(.*?)</url_video>|is', $http_result['body'], $video_url_match);
		$video_url = trim($video_url_match[1]);
		
		$thumb_url_match = null;
		preg_match('|<url_full>(.*?)</url_full>|is', $http_result['body'], $thumb_url_match);
		$thumb_url = trim($thumb_url_match[1]);
	
	return '<object width="'.attribute_escape($width).'" height="'.attribute_escape($height).'">
				<param name="allowScriptAccess" value="always" />
				<param name="allownetworking" value="all" />
				<param name="allowfullscreen" value="true" />
				<param name="movie" value="http://www.mobypicture.com/static/flash/player.swf" />
				<param name="quality" value="high" />
				<param name="bgcolor" value="#ffffff" />
				<param name="flashvars" value="file='.$video_url.'&image='.$thumb_url.'" />
					<embed  type="application/x-shockwave-flash" 
							src="http://www.mobypicture.com/static/flash/player.swf" 
							quality="high" 
							bgcolor="#ffffff" 
							width="'.attribute_escape($width).'" height="'.attribute_escape($height).'" 
							allowScriptAccess="always" allownetworking="all" allowfullscreen="true" 
							flashvars="file='.$video_url.'&image='.$thumb_url.'" 
						/>
			</object>';
	} elseif($type == 'photo') {
		if(!empty($width))
			$width_tag = 'width="'.attribute_escape($width).'"';
		if(!empty($height))
			$height_tag = 'height="'.attribute_escape($height).'"';
		
		$url_match = null;
		preg_match('|<url_full>(.*?)</url_full>|is', $http_result['body'], $url_match);
		$url = trim($url_match[1]);
		
		return '<img src="'.$url.'" '.$width_tag.' '.$height_tag.' />';
	}
}
add_shortcode('mobypicture', 'mobypicture_embed_shortcode');

