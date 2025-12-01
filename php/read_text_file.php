<?php
namespace SIM\FRONTENDPOSTING;
use SIM;

function readTextFile($path){
	// Ensure the WordPress Filesystem API is loaded
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	// Initialize the filesystem object
	WP_Filesystem();

	global $wp_filesystem;

	$ext 	= pathinfo($path, PATHINFO_EXTENSION);
		
	if($ext == 'docx'){
		$reader = 'Word2007';
	}elseif($ext == 'doc'){
		$reader = 'MsDoc';
	}elseif($ext == 'rtf'){
		$reader = 'rtf';
	}elseif($ext == 'txt'){
		$reader = 'plain';
	}else{
		$reader = 'Word2007';
	}
	
	if($reader == 'plain'){
		$file = $wp_filesystem->fopen($path, "r");
		$contents =  $wp_filesystem->fread($file,filesize($path));
		$wp_filesystem->fclose($file);
		
		return str_replace("\n", '<br>', $contents);
	}else{
		//Load the filecontents
		$phpWord = \PhpOffice\PhpWord\IOFactory::createReader($reader)->load($path);

		//Convert it to html
		$htmlWriter = new \PhpOffice\PhpWord\Writer\HTML($phpWord);
		
		$html 		= $htmlWriter->getWriterPart('Body')->write();

		// Replace paragraps with linebreaks
		$re 		= '/<p.*?>(.*?)<\/p>/s';
		$html 		= preg_replace($re, "$1<br>", $html);

		// Replace spans with bold
		$re 		= '~<span[^>]*?font-weight: bold;[^>]*>([^<]*)<\/span>~sm';
		$html 		= preg_replace($re, '<b>$1</b>', $html);

		// Unwanting html
		$allowedTags 		= '<br>,<br />,<strong>,<b>,<i>';
		$html 		= strip_tags($html, $allowedTags);

		// Remove remaining spans
		$re 		= '~<span[^>]*>([^<]*)<\/span>~sm';
		$html 		= preg_replace($re, '$1', $html);

		// Remove duplicate tags like </b><b>
		$re			= '/<\/([^>]*)>\s*<\1>/s';
		$html 		= preg_replace($re, '$2', $html);

		// remove &nbsp;
		$html 		= str_replace('&nbsp;', '', $html);
		
		//Return the contents
		return $html;
	}
}