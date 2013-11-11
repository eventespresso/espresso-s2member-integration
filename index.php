<?php
/*
  Plugin Name: Event Espresso - s2Member Integration
  Plugin URI: http://eventespresso.com/
  Description: s2Member integration for Event Espresso <a href="admin.php?page=support" >Support</a>

  Version: 1.0.b

  Author: Event Espresso
  Author URI: http://www.eventespresso.com

  Copyright (c) 2009-2013 Event Espresso  All Rights Reserved.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

//Define the version of the plugin
function espresso_s2member_version() {
	do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	return '1.0.b';
}

//Get the member level for a user
function espresso_s2member_level() {
	$current_user = wp_get_current_user();
	if($current_user->has_cap("s2member_level1"))
		return 1;
	if($current_user->has_cap("s2member_level2"))
		return 2;
	if($current_user->has_cap("s2member_level3"))
		return 3;
	if($current_user->has_cap("s2member_level4"))
		return 4;
	return 0;
}

//Gets the s2member threshold
function espresso_s2member_threshold($member) {
	$member_options = get_option('events_member_settings');
	$S2 = $member_options['S2_option'];
	$threshold = $member_options['S2_threshold'];
	if ($S2) {
		if (espresso_s2member_level() >= $threshold) {
			$member = TRUE;
		} else {
			$member = FALSE;
		}
	}
	return $member;
}
add_filter( 'filter_hook_espresso_above_member_threshold', 'espresso_s2member_threshold', 10, 1 );

//Filter to add keys and values to the $member_options array
function espresso_s2member_save_member_settings($member_options) {
	$member_options['S2_option'] = isset($_POST['S2_option']) && !empty($_POST['S2_option']) ? $_POST['S2_option'] : '';
	$member_options['S2_threshold'] = isset($_POST['S2_threshold']) && !empty($_POST['S2_threshold']) ? $_POST['S2_threshold'] : '';
	return $member_options;
}
add_filter( 'filter_hook_espresso_save_member_settings', 'espresso_s2member_save_member_settings', 10, 1 );

//Create a section in the settings page
function espresso_s2member_settings() {
	$member_options = get_option('events_member_settings');
	$S2_option = empty($member_options['S2_option']) ? FALSE : $member_options['S2_option'];
	$S2_threshold = empty($member_options['S2_threshold']) ? 1 : $member_options['S2_threshold'];
	?>
	<li>
		<label><?php _e('Use S2 member level threshold? ', 'event_espresso'); ?></label>
		<?php
		$values = array(
			array('id' => FALSE, 'text' => __('No', 'event_espresso')),
			array('id' => TRUE, 'text' => __('Yes', 'event_espresso')));
		echo select_input('S2_option', $values, $S2_option);
		?>
	</li>
	<li>
		<label><?php _e('S2 member threshold? ', 'event_espresso'); ?></label>
		<?php
		$values = array(
			array('id' => 1, 'text' => __('1', 'event_espresso')),
			array('id' => 2, 'text' => __('2', 'event_espresso')),
			array('id' => 3, 'text' => __('3', 'event_espresso')),
			array('id' => 4, 'text' => __('4', 'event_espresso')));
		echo select_input('S2_threshold', $values, $S2_threshold);
		?>
	</li>
	<?php
}
add_action('action_hook_espresso_member_settings_form_bottom','espresso_s2member_settings');

//Update notifications
add_action('action_hook_espresso_members_update_api', 'ee_s2member_load_pue_update');
function ee_s2member_load_pue_update() {
	global $org_options, $espresso_check_for_updates;
	if ( $espresso_check_for_updates == false )
		return;
		
	if (file_exists(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php')) { //include the file 
		require(EVENT_ESPRESSO_PLUGINFULLPATH . 'class/pue/pue-client.php' );
		$api_key = $org_options['site_license_key'];
		$host_server_url = 'http://eventespresso.com';
		$plugin_slug = array(
			// remove following line when releasing this version to stable
			'premium' => array('b' => 'espresso-s2member-pr'),
			// uncomment following line when releasing this version to stable
			// 'premium' => array('p' => 'espresso-s2member'),
			'prerelease' => array('b' => 'espresso-s2member-pr')
			);
		$options = array(
			'apikey' => $api_key,
			'lang_domain' => 'event_espresso',
			'checkPeriod' => '24',
			'option_key' => 'site_license_key',
			'options_page_slug' => 'event_espresso',
			'plugin_basename' => plugin_basename(__FILE__),
			'use_wp_update' => FALSE, //if TRUE then you want FREE versions of the plugin to be updated from WP
		);
		$check_for_updates = new PluginUpdateEngineChecker($host_server_url, $plugin_slug, $options); //initiate the class and start the plugin update engine!
	}
}