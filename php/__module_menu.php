<?php
namespace SIM\FRONTENDPOSTING;
use SIM;

const MODULE_VERSION		= '8.3.3';

DEFINE(__NAMESPACE__.'\MODULE_PATH', plugin_dir_path(__DIR__));

//module slug is the same as grandparent folder name
DEFINE(__NAMESPACE__.'\MODULE_SLUG', strtolower(basename(dirname(__DIR__))));

DEFINE(__NAMESPACE__.'\MODULE_PATH', plugin_dir_path(__DIR__));

add_filter('sim_submenu_frontendposting_description', __NAMESPACE__.'\subMenuDescription', 10, 2);
function subMenuDescription($description, $moduleSlug){
	ob_start();

	$url		= SIM\ADMIN\getDefaultPageLink($moduleSlug, 'front-end-post-pages');
	$url2		= SIM\ADMIN\getDefaultPageLink($moduleSlug, 'pending-pages');
	
	if(!empty($url)){
		?>
		<p>
			<strong>Auto created pages:</strong><br>
			<a href='<?php echo $url;?>'>Add content</a><br>
			<a href='<?php echo $url2;?>'>Pending pages</a>
		</p>
		<?php
	}

	return $description.ob_get_clean();
}

add_filter('sim_submenu_frontendposting_options', __NAMESPACE__.'\subMenuOptions', 10, 2);
function subMenuOptions($optionsHtml, $settings){
	if(!isset($settings['pending-channels']) || !is_array($settings['pending-channels'])){
		$settings['pending-channels']	= [];
	}

	ob_start();
	?>
	<label>Content manager role</label>
	<select name='content-manager-roles[]' multiple='multiple'>
		<?php
		global $wp_roles;
	
		//Get all available roles
		$userRoles	= $wp_roles->role_names;
		
		//Sort the roles
		asort($userRoles);

		if(!isset($settings['content-manager-roles'])){
			$settings['content-manager-roles']	= [];
		}

		foreach($userRoles as $key=>$role){
			$selected	= '';
			if(is_array($settings['content-manager-roles']) && in_array($key, $settings['content-manager-roles'])){
				$selected	= 'selected=selected';
			}
			echo "<option value='$key' $selected>$role</option>";
		}
		?>
	</select>
	<br>
	<label>How should content managers be notified about pending content?</label>
	<br>
	<label><input type='checkbox' name='pending-channels[]' value='email' <?php if(is_array($settings['pending-channels']) && in_array('email', $settings['pending-channels'])){echo 'checked';}?>>E-mail</label>
	<label><input type='checkbox' name='pending-channels[]' value='signal' <?php if(is_array($settings['pending-channels']) && in_array('signal', $settings['pending-channels'])){echo 'checked';}?>>Signal</label>
	<br>
	<br>

	<label>How often should people be reminded of content which should be updated?</label>
	<select name="page-age-reminder">
		<?php
		SIM\ADMIN\recurrenceSelector($settings['page-age-reminder']);
		?>
	</select>
	<br>
	<label>What should be the max time in months for a page without any changes?</label>
	<select name="max-page-age">
		<?php
		for ($x = 0; $x <= 12; $x++) {
			if($settings['max-page-age'] === strval($x)){
				$selected = 'selected="selected"';
			}else{
				$selected = '';
			}
			echo "<option value='$x' $selected>$x</option>";
		}
		?>
	</select>
	<br>
	<label>
		Post status after expiry<br>
		<label>
			<input type='radio' name='expired-post-type' id='expired-post-type' value='trash' <?php if($settings['expired-post-type'] == 'trash'){echo 'checked';}?>>
			Trashed
		</label>

		<label>
			<input type='radio' name='expired-post-type' id='expired-post-type' value='archived' <?php if($settings['expired-post-type'] == 'archived'){echo 'checked';}?>>
			Archived
		</label>
	</label>
	<?php
	return $optionsHtml.ob_get_clean();
}

add_filter('sim_email_frontendposting_settings', __NAMESPACE__.'\emailSettings', 10, 2);
function emailSettings($html, $settings){
	ob_start();
	?>
	<h4>E-mail send to people when a page is out of date</h4>
	<label>
		Define the e-mail people get when they are responsible for a page which is out of date.<br>
		You can use placeholders in your inputs.<br>
		These ones are available (click on any of them to copy):
	</label>
	<?php
	$email    = new PostOutOfDateEmail(wp_get_current_user());
	$email->printPlaceholders();
	$email->printInputs($settings);
	?>
	<br>
	<br>
	<label>
		Define the e-mail people get when they are responsible for multiple pages which is out of date.<br>
		You can use placeholders in your inputs.<br>
		These ones are available (click on any of them to copy):
	</label>
	<?php
	$email    = new PostOutOfDateEmails(wp_get_current_user());
	$email->printPlaceholders();
	$email->printInputs($settings);
	?>
	<br>
	<br>
	<h4>E-mail send to content managers when a post is pending</h4>
	<label>
		Define the e-mail content managers get when someone has submitted a post or post update for review<br>
	</label>
	<?php
	$email    = new PendingPostEmail(wp_get_current_user());
	$email->printPlaceholders();
	$email->printInputs($settings);
	?>
	<br>
	<br>
	<h4>E-mail send to authors when their content is approved</h4>
	<label>
		Define the e-mail authors when their post is approved<br>
	</label>
	<?php
	$email    = new ApprovedPostMail(wp_get_current_user());
	$email->printPlaceholders();
	$email->printInputs($settings);

	return $html.ob_get_clean();
}

add_filter('sim_module_frontendposting_after_save', __NAMESPACE__.'\moduleUpdated', 10, 2);
function moduleUpdated($options, $oldOptions){
	// Create frontend posting page
	$options	= SIM\ADMIN\createDefaultPage($options, 'front-end-post-pages', 'Add content', '[front_end_post]', $oldOptions);

	$options	= SIM\ADMIN\createDefaultPage($options, 'pending-pages', 'Pending Posts', '[pending-pages]', $oldOptions);

	scheduleTasks();

	return $options;
}

add_filter('display_post_states', __NAMESPACE__.'\postStatus', 10, 2);
function postStatus( $states, $post ) {

    if ( in_array($post->ID, SIM\getModuleOption(MODULE_SLUG, 'front-end-post-pages', false))) {
        $states[] = __('Frontend posting page');
    }elseif ( in_array($post->ID, SIM\getModuleOption(MODULE_SLUG, 'pending-pages', false))) {
        $states[] = __('Pending posts page');
    }

    return $states;
}

add_action('sim_module_frontendposting_deactivated', __NAMESPACE__.'\moduleDeactivate');
function moduleDeactivate($options){
	foreach($options['front-end-post-pages'] as $page){
		// Remove the auto created page
		wp_delete_post($page, true);
	}
}