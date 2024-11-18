<?php
namespace SIM\FRONTENDPOSTING;
use SIM;

add_filter('sim-media-edit-link', __NAMESPACE__.'\editLink', 10, 2);
function editLink($link, $id){
    $url			= SIM\ADMIN\getDefaultPageLink(MODULE_SLUG, 'front_end_post_pages');
	if($url){
		return"<a href='$url?post_id=$id' class='button'>Edit</a>";
    }
    return $link;
}