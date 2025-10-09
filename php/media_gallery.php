<?php
namespace SIM\FRONTENDPOSTING;
use SIM;

add_filter('sim-media-edit-link', __NAMESPACE__.'\editLink', 10, 2);
function editLink($link, $id){
    $url			= SIM\ADMIN\getDefaultPageLink(MODULE_SLUG, 'front-end-post-pages');
	if($url){
		return"<a href='$url?post-id=$id' class='button'>Edit</a>";
    }
    return $link;
}