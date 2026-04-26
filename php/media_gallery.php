<?php
namespace TSJIPPY\FRONTENDPOSTING;
use TSJIPPY;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('tsjippy-media-edit-link', __NAMESPACE__.'\editLink', 10, 2);
function editLink($link, $id){
    $url			= TSJIPPY\ADMIN\getDefaultPageLink(PLUGINSLUG, 'front-end-post-pages');
	if($url){
		return"<a href='$url?post-id=$id' class='button'>Edit</a>";
    }
    return $link;
}