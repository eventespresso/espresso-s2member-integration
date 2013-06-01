<?php
/*
  Plugin Name: Event Espresso - s2Member Integration
  Plugin URI: http://eventespresso.com/
  Description: s2Member integration for Event Espresso <a href="admin.php?page=support" >Support</a>

  Version: 0.1-ALPHA

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
	return '0.1-ALPHA';
}

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

function espresso_s2member_level_check($member) {
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
add_filter( 'filter_hook_espresso_above_member_threshold', 'espresso_s2member_level_check', 10, 1 );

function espresso_s2member_save_member_settings($member_options) {
	$member_options['S2_option'] = isset($_POST['S2_option']) && !empty($_POST['S2_option']) ? $_POST['S2_option'] : '';
	$member_options['S2_threshold'] = isset($_POST['S2_threshold']) && !empty($_POST['S2_threshold']) ? $_POST['S2_threshold'] : '';
	return $member_options;
}
add_filter( 'filter_hook_espresso_save_member_settings', 'espresso_s2member_save_member_settings', 10, 1 );

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