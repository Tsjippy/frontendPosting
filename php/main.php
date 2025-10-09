<?php
namespace SIM\FRONTENDPOSTING;
use SIM;

/**
 * Gets all the pages who have not been edited recently and are not static
 *
 * @return	array	Array of post objects
 */
function getOldPages(){
	$maxAge	= SIM\getModuleOption(MODULE_SLUG, 'max-page-age');
	//$maxAge	= date('Y-m-d', strtotime("-$maxAge months"));

	//Get all pages without the static content meta key who have been edited last more than X months ago
	return get_posts(array(
		'numberposts'      	=> -1,
		'post_type'        	=> ['page', 'location'],
		'orderby'			=> 'modified',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' 		=> 'static_content',
				'compare'	=> 'NOT EXISTS'
			),
			array(
				'key'		=> 'static_content',
				'compare'	=> '!=',
				'value'		=> true
			),
		),
		'date_query' => [
			'column' => 'post_modified',
			'before'  => "$maxAge months ago",
		],
	));
}

function sendPendingPostWarning( object $post, $update){
	//Do not continue if already send
	if(!empty(get_post_meta($post->ID, 'pending_notification_send', true))){
		return;
	}

	$channels	= SIM\getModuleOption(MODULE_SLUG, 'pending-channels', false);

	if(empty($channels)){
		return;
	}
	
	$roles	= SIM\getModuleOption(MODULE_SLUG, 'content-manager-roles');
	
	//get all the content managers
	$users = get_users( array(
		'role__in'    => $roles,
	));
	
	if($update){
		$actionText = 'updated';
	}else{
		$actionText = 'created';
	}
	
	$type = $post->post_type;
	
	//send notification to all content managers
	$url			= SIM\ADMIN\getDefaultPageLink(MODULE_SLUG, 'front-end-post-pages');
	if(!$url){
		return;
	}

	$url			= add_query_arg( ['post-id' => $post->ID], $url );
	$authorName		= get_userdata($post->post_author)->display_name;
	
	foreach($users as $user){
		if(in_array('email', $channels)){
			$pendinfPostEmail    = new PendingPostEmail($user, $authorName, $actionText, $type, $url);
			$pendinfPostEmail->filterMail();
				
			//Send e-mail
			wp_mail( $user->user_email, $pendinfPostEmail->subject, $pendinfPostEmail->message);
		}
	}
	
	//Mark warning as send
	update_metadata( 'post', $post->ID, 'pending_notification_send', true);
}

//Delete the indicator that the warning has been send
add_action(  'transition_post_status', __NAMESPACE__.'\onStatusChange', 10, 3 );
function onStatusChange( $newStatus, $oldStatus, $post ) {
	if ($newStatus == 'publish' && $oldStatus == 'pending'){
		delete_post_meta($post->ID, 'pending_notification_send');
	}
}

//Allow display attributes in post content
add_filter( 'safe_style_css',  __NAMESPACE__.'\safeStyles');
function safeStyles( $styles ) {
    $styles[] = 'display';
    return $styles;
}

/**
 * Checks if the current user is allowed to edit a post
 *
 * @return	boolean			True if allowed
 */
function allowedToEdit($post){
	if(empty($post)){
		return true;
	}
	
	if(is_numeric($post)){
		$post	= get_post($post);
	}

	$postId			= $post->ID;
	$user 			= wp_get_current_user();
	$postAuthor 	= $post->post_author;
	$postCategory 	= $post->post_category;
	$userPageId 	= SIM\maybeGetUserPageId($user->ID);
	$ministries 	= (array)get_user_meta($user->ID, "jobs", true);

	if (
		$postAuthor == $user->ID 															|| 	// Own page
		in_array($post->ID, array_keys($ministries))										||	// ministry pafe
		$userPageId == $postId																||	// pseronal user page
		apply_filters('sim_frontend_content_edit_rights', false, $postCategory)				||	// external filter
		$user->has_cap( 'edit_others_posts' )													// user has permission to edit any post
	){
		return true;
	}

	return false;
}

//Add post edit button
add_filter( 'the_content', __NAMESPACE__.'\filterContent', 15);
function filterContent( $content ) {
	//Do not show if:
	if (
		!is_user_logged_in() 							||	// not logged in or
		str_contains($content, '[front_end_post]')  	||	// already on the post edit page
		!is_singular() 									||  // it is not a single page
		is_tax()										||	// not an archive page
		is_front_page()										// is the front page
	){
		return $content;
	}

	global $post;
	
	//This is a draft
	if(isset($_GET['p']) || isset($_GET['page_id'])){
		if(isset($_GET['p'])){
			$postId 	= $_GET['p'];
		}else{
			$postId 	= $_GET['page_id'];
		}
	//published
	}else{
		$postId 		= get_the_ID();
	}

	$postViewRoles	= get_post_meta($postId, 'post_view_roles');
	if(is_array($postViewRoles)){
		$type		= get_post_meta($postId, 'permission_filter_type', true);
		
		if(!empty($type)){
			$roles 		= get_userdata(get_current_user_id())->roles;
			$match		= array_intersect($postViewRoles, $roles);

			if(
				(
					$type 	== 'block'	&&
					$match	== true		
				) ||
				(
					$type 	== 'allow'	&&
					$match	== false		
				)
			){
				return '<div class="error">You have no permission to see this</div>';
			}
		}
	}
	
	$buttonHtml	= '';
	//Add an edit page button if:
	if ( allowedToEdit($postId) ){
		$type 		= $post->post_type;
		$buttonText = "Edit this $type";

		if($type == 'attachment'){
			$url		= admin_url("post.php?post=$post->ID&action=edit");
			$buttonHtml	= "<a href=$url class='button small hidden' id='page-edit'>$buttonText</a>";
		}else{
			$buttonHtml	= "<button class='button small hidden' id='page-edit' data-id='$postId'>$buttonText</button>";
		}
	}
	$buttonHtml	= apply_filters('post-edit-button', $buttonHtml, $post, $content);

	return $buttonHtml."<div class='content-wrapper'>$content</div>";
}

add_filter('sim-template-filter',  __NAMESPACE__.'\templateFilter');
function templateFilter($templateFile){
	if(str_contains($templateFile, 'single-attachment')){
		return MODULE_PATH.'templates/single-attachment.php';
	}

	return $templateFile;
}