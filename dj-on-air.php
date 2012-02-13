<?php
/**
* @package DJ_On_Air_Widget
* @version 0.2.5
*/
/*
Plugin Name: DJ On Air Widget
Plugin URI: http://nlb-creations.com/2011/09/02/wordpress-plugin-dj-on-air-widget/
Description: This plugin adds additional fields to user profiles to designate users as DJs and provide shift scheduling.
Author: Nikki Blight <nblight@nlb-creations.com>
Version: 0.2.5
Author URI: http://www.nlb-creations.com
*/

global $djDefaultOptionVals;

//set the default options, or, if they've already been customized, load the options.
function dj_set_globals() {
	global $djDefaultOptionVals;
	
	$options = get_option('dj_access_roles');
	
	if(!$options) {
		$djDefaultOptionVals = array(
			'roles' => array('administrator')
		);
		update_option('dj_access_roles', $djDefaultOptionVals);
	}
	else {
		$djDefaultOptionVals = $options;
	}
}
add_action( 'init', 'dj_set_globals' );

//add the stylesheet to the frontend theme
if ( !is_admin() ) {
	$dir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	wp_enqueue_style( 'dj-on-air', $dir.'/styles/djonair.css' );
}

//shortcode function for current DJ on-air
function dj_show_widget($atts) {
	extract( shortcode_atts( array(
		'title' => '',	
		'show_avatar' => 0,
		'show_link' => 0,
		'default_name' => ''
	), $atts ) );
	
	//find out which DJ(s) are currently scheduled to be on-air and display them
	$djs = dj_get_current();
	
	$dj_str = '';
	
	$dj_str .= '<div class="on-air-embedded">';
	if($title != '') {
		$dj_str .= '<h3>'.$title.'</h3>';
	}
	$dj_str .= '<ul class="on-air-list">';
	if(count($djs) > 0) {
		foreach($djs as $dj) {
			$dj_str .= '<li class="on-air-dj">';
			if($show_avatar) {
				$dj_str .= '<span class="on-air-dj-avatar">'.get_avatar($dj->ID).'</span>';
			}
			
			if($show_link) {
				$dj_str .= '<a href="';
				$dj_str .= get_author_posts_url($dj->ID);
				$dj_str .= '">';
				$dj_str .= $dj->display_name.'</a>';
			}
			else {
				$dj_str .= $dj->display_name;
			}
			$dj_str .= '<span class="clear"></span></li>';
		}
	}
	else {
		$dj_str .= '<li class="on-air-dj default-dj">'.$default_name.'</li>';
	}
	$dj_str .= '</ul>';
	$dj_str .= '</div>';
	
	return $dj_str;
	
}
add_shortcode( 'dj-widget', 'dj_show_widget');

//shortcode to display a full schedule od DJs
function dj_schedule() {
	global $wpdb;
	
	//set up the structure of the master schedule
	$default_dj = get_option('dj_default_name');
	$time_settings = get_option('dj_time_settings');
	$master_list = array('Sunday' => array(), 'Monday' => array(), 'Tuesday' => array(), 'Wednesday' => array(), 'Thursday' => array(), 'Friday' => array(), 'Saturday' => array());
	
	foreach($master_list as $day => $times) {
		for($i=0; $i<24; $i++) {
			$zero = '';
			if($i < 10) {
				$zero = '0';
			}
			
			if($time_settings == 'quarterhour') {
				$master_list[$day][$zero.$i.':00'] = array();
				$master_list[$day][$zero.$i.':15'] = array();
				$master_list[$day][$zero.$i.':30'] = array();
				$master_list[$day][$zero.$i.':45'] = array();
			}
			elseif($time_settings == 'halfhour') {
				$master_list[$day][$zero.$i.':00'] = array();
				$master_list[$day][$zero.$i.':30'] = array();
			}
			else {
				$master_list[$day][$zero.$i.':00'] = array();
			}
		}
	}	
	
	//pull all users who have dj schedules set
	$djs = $wpdb->get_results("SELECT `meta`.`user_id` FROM ".$wpdb->prefix."usermeta AS `meta`
											WHERE `meta_key` = 'shifts';"
											);
	
	//insert the djs into the master schedule
	foreach($djs as $dj) {
		$shifts = get_user_meta($dj->user_id,'shifts',false);
	   
	    if(isset($shifts[0])) {
	    	$shifts = unserialize($shifts[0]);	
	    }
		
		foreach($shifts as $shift) {
			if(isset($master_list[$shift['day']][$shift['time']])) {
				$master_list[$shift['day']][$shift['time']][] = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."users AS `user` WHERE `user`.`ID` = ".$dj->user_id.";");
			}
		}
	}
	
	//format for output
	$sched = '';
	
	foreach($master_list as $day => $times) {
		$sched .= '<div class="on-air-dj-schedule-day-block"><h3 class="on-air-dj-schedule-day-title">'.$day.'</h3>';
		
		$sched .= '<ul class="on-air-dj-schedule-time-list">';
		foreach($times as $time => $djs) {
			$sched .= '<li class="on-air-dj-schedule-time-item">';
			$sched .= date('g:i a', strtotime($time.':00')).': ';
			
			$sched .= '<ul class="on-air-dj-schedule-dj-list">';
			if(empty($djs)) {
				$sched .= '<li class="on-air-dj-schedule-dj-item on-air-no-dj">'.$default_dj.'</li>';
			}
			else {
				foreach($djs as $dj) {
					$sched .= '<li class="on-air-dj-schedule-dj-item scheduled-dj on-air-dj-id-'.$dj->user_id.'">'.$dj->display_name.'</li>';
				}
			}
			$sched .= '</ul>';
			$sched .= '</li>';
		}
		$sched .= '</ul></div>';
	}
	
	return $sched;
}
add_shortcode( 'dj-schedule', 'dj_schedule');

//fetch the current DJ(s) on-air
function dj_get_current() {	
	//load the info for the DJ
	global $wpdb;
	
	//get the current time
	$now = date('H', strtotime(current_time("mysql", $gmt)));
	$min = date('i', strtotime(current_time("mysql", $gmt)));
	$curDay = date('l', strtotime(current_time("mysql", $gmt)));
	
	//take the time settings into account... are we looking for 15 minute shifts, half hour shifts, or hours shifts
	$time_settings = get_option('dj_time_settings');
	
	
	if($time_settings == 'quarterhour') {
		if($min < 15) {
			$now = $now.':00';
		}
		elseif($min >= 15 && $min < 30) {
			$now = $now.':15';
		}
		elseif($min >= 30 && $min < 45) {
			$now = $now.':30';
		}
		else {
			$now = $now.':45';
		}
	}
	elseif($time_settings == 'halfhour') {
		if($min < 30) {
			$now = $now.':00';
		}
		else {
			$now = $now.':30';
		}
	}
	else {
		$now = $now.':00';
	}
	
	$day_map = array(
					'Monday' => '{s:3:"day";s:6:"Monday";',
					'Tuesday' => '{s:3:"day";s:7:"Tuesday";',
					'Wednesday' => '{s:3:"day";s:9:"Wednesday";',
					'Thursday' => '{s:3:"day";s:8:"Thursday";',
					'Friday' => '{s:3:"day";s:6:"Friday";',
					'Saturday' => '{s:3:"day";s:8:"Saturday";',
					'Sunday' => '{s:3:"day";s:6:"Sunday";'
				);
	
	$serializedDayTime = $day_map[$curDay].'s:4:"time";s:5:"'.$now.'";}';
	
	
	$dj_ids = $wpdb->get_results("SELECT `meta`.`user_id` FROM ".$wpdb->prefix."usermeta AS `meta`
											WHERE `meta_key` = 'shifts' 
											AND `meta_value` LIKE '%".$serializedDayTime."%';"   
											);
	$djs = array();
	foreach($dj_ids as $id) {
		$fetch = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."users AS `user` WHERE `user`.`ID` = ".$id->user_id.";");
		
		$djs[] = $fetch;
	}
	
	return $djs;
}

//show the extra fields on the user profile form and load the widget
add_action( 'show_user_profile', 'dj_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'dj_show_extra_profile_fields' );

/* Generates the customized meta field for the shift list
* Code adapted from http://wordpress.stackexchange.com/questions/19838/create-more-meta-boxes-as-needed/19852#19852
* and http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
*/
function dj_show_extra_profile_fields( $user ) { 
	if(!dj_hasPluginAccess()) {
		return false;
	}
	?>
	

	<h3>Additional profile information</h3>
	<?php 
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'dynamicMeta_noncename' );
	?>
		<h3>DJ shifts</h3>
	    <div id="meta_inner">
	    <?php
	
	    //get the saved meta as an array
	    $shifts = get_user_meta($user->ID,'shifts',false);
	    //print_r($shifts);
	    if(isset($shifts[0])) {
	    	$shifts = unserialize($shifts[0]);	
	    }
		//print_r($shifts);
	    $c = 0;
	    if (count($shifts) > 0){
	        foreach($shifts as $track ){
	            if (isset($track['day']) || isset($track['time'])){
	            	?>
	            	<p>
	            		Day: 
	            		<select name="shifts[<?php echo $c; ?>][day]">
	            			<option value=""></option>
	            			<option value="Monday"<?php if($track['day'] == "Monday") { echo ' selected="selected"'; } ?>>Monday</option>
	            			<option value="Tuesday"<?php if($track['day'] == "Tuesday") { echo ' selected="selected"'; } ?>>Tuesday</option>
	            			<option value="Wednesday"<?php if($track['day'] == "Wednesday") { echo ' selected="selected"'; } ?>>Wednesday</option>
	            			<option value="Thursday"<?php if($track['day'] == "Thursday") { echo ' selected="selected"'; } ?>>Thursday</option>
	            			<option value="Friday"<?php if($track['day'] == "Friday") { echo ' selected="selected"'; } ?>>Friday</option>
	            			<option value="Saturday"<?php if($track['day'] == "Saturday") { echo ' selected="selected"'; } ?>>Saturday</option>
	            			<option value="Sunday"<?php if($track['day'] == "Sunday") { echo ' selected="selected"'; } ?>>Sunday</option>
	            		</select>
	            		 - 
	            		Time: 
	            		<select name="shifts[<?php echo $c; ?>][time]">
	            			<option value=""></option>
	            		<?php for($i=0; $i<24; $i++): ?>
	            			<?php 
	            				//we need to store time in 24 hour format, but display in 12 hour format, so we need to do a little manipulation here
	            				$hour = $i;
	            				if($i >= 12) {
	            					if($i == 12) {
	            						$hour = 12;
	            					}
	            					else {
	            						$hour = $hour - 12;
	            					}
	            					$meridian = 'pm';
	            				}
	            				else {
	            					$meridian = 'am';
			    					if($i == 0) {
			    						$hour = 12;
			    					}
	            				}
	            				
	            				if($i < 10) {
									$i = "0".$i;
	            				}
	            				
	            				//now we figure out what increments we need
	            				$time_settings = get_option('dj_time_settings');
	            				
	            				//if the time settings are not set, set them now to the default of 1 hour
	            				if(!$time_settings) {
	            					$time_settings = "hour";
	            					update_option('dj_time_settings', $time_settings);
	            				}
	            			?>
	            			<?php if($time_settings == 'quarterhour'): ?>
	            			<option value="<?php echo $i; ?>:00"<?php if($track['time'] == $i.":00") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:00 <?php echo $meridian; ?></option>
	            			<option value="<?php echo $i; ?>:15"<?php if($track['time'] == $i.":15") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:15 <?php echo $meridian; ?></option>
	            			<option value="<?php echo $i; ?>:30"<?php if($track['time'] == $i.":30") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:30 <?php echo $meridian; ?></option>
	            			<option value="<?php echo $i; ?>:45"<?php if($track['time'] == $i.":45") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:45 <?php echo $meridian; ?></option>
	            			<?php elseif($time_settings == 'halfhour'): ?>
	            			<option value="<?php echo $i; ?>:00"<?php if($track['time'] == $i.":00") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:00 <?php echo $meridian; ?></option>
	            			<option value="<?php echo $i; ?>:30"<?php if($track['time'] == $i.":30") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:30 <?php echo $meridian; ?></option>
	            			<?php else: ?>
	            			<option value="<?php echo $i; ?>:00"<?php if($track['time'] == $i.":00") { echo ' selected="selected"'; } ?>><?php echo $hour; ?>:00 <?php echo $meridian; ?></option>
	            			<?php endif; ?>
	            			
	            		<?php endfor; ?>
	            		</select> 
	            		<span class="remove" style="cursor: pointer; color: #ff0000;">Remove</span>
	            	</p>
	            	<?php 
	                //echo '<p> Day: <input type="text" name="shifts['.$c.'][day]" value="'.$track['day'].'" /> - Time: <input type="text" name="shifts['.$c.'][time]" value="'.$track['time'].'" /> <span class="remove" style="cursor: pointer;">Remove</span></p>';
	                $c = $c +1;
	            }
	        }
	    }
	
	    ?>
	<span id="here"></span>
	<span class="add button-secondary" style="cursor: pointer; display:block; width: 75px; padding: 8px; text-align: center;"><?php echo __('Add Shift'); ?></span>
	<script>
	    var $ =jQuery.noConflict();
	    $(document).ready(function() {
	        var count = <?php echo $c; ?>;
	        $(".add").click(function() {
	            count = count + 1;
				output = '<p>Day: '; 
				output += '<select name="shifts[' + count + '][day]">';
				output += '<option value="Monday">Monday</option>';
				output += '<option value="Tuesday">Tuesday</option>';
				output += '<option value="Wednesday">Wednesday</option>';
				output += '<option value="Thursday">Thursday</option>';
				output += '<option value="Friday">Friday</option>';
				output += '<option value="Saturday">Saturday</option>';
				output += '<option value="Sunday">Sunday</option>';
				output += '</select>';
				output += ' - Time: ';
				output += '<select name="shifts[' + count + '][time]">';


				<?php for($i=0; $i<24; $i++): ?>
    			<?php 
    				//we need to store time in 24 hour format, but display in 12 hour format, so we need to do a little manipulation here
    				$hour = $i;
    				if($i >= 12) {
    					if($i == 12) {
            				$hour = 12;
						}
           				else {
            				$hour = $hour - 12;
            			}
    					$meridian = 'pm';
    				}
    				else {
    					$meridian = 'am';
    					if($i == 0) {
    						$hour = 12;
    						$meridian = 'am';
    					}
    				}
    				
    				if($i < 10) {
						$i = "0".$i;
    				}
    				
    				//now we figure out what increments we need
    				$time_settings = get_option('dj_time_settings');
    				
    				//if the time settings are not set, set them now to the default of 1 hour
    				if(!$time_settings) {
    					$time_settings = "hour";
    					update_option('dj_time_settings', $time_settings);
    				}
    			?>
				<?php if($time_settings == 'quarterhour'): ?>
    			output += '<option value="<?php echo $i; ?>:00"><?php echo $hour; ?>:00 <?php echo $meridian; ?></option>';
    			output += '<option value="<?php echo $i; ?>:15"><?php echo $hour; ?>:15 <?php echo $meridian; ?></option>';
    			output += '<option value="<?php echo $i; ?>:30"><?php echo $hour; ?>:30 <?php echo $meridian; ?></option>';
    			output += '<option value="<?php echo $i; ?>:45"><?php echo $hour; ?>:45 <?php echo $meridian; ?></option>';
    			<?php elseif($time_settings == 'halfhour'): ?>
    			output += '<option value="<?php echo $i; ?>:00"><?php echo $hour; ?>:00 <?php echo $meridian; ?></option>';
    			output += '<option value="<?php echo $i; ?>:30"><?php echo $hour; ?>:30 <?php echo $meridian; ?></option>';
    			<?php else: ?>
    			output += '<option value="<?php echo $i; ?>:00"><?php echo $hour; ?>:00 <?php echo $meridian; ?></option>';
    			<?php endif; ?>

				<?php endfor; ?>

				output += '</select> ';
				output += '<span class="remove" style="cursor: pointer; color: #ff0000;">Remove</span></p>';
	            $('#here').append( output );

	            //$('#here').append('<p> Day: <input type="text" name="shifts['+count+'][day]" value="" /> - Time: <input type="text" name="shifts['+count+'][time]" value="" /> <span class="remove" style="cursor: pointer;">Remove</span></p>' );
	            return false;
	        });
	        $(".remove").live('click', function() {
	            $(this).parent().remove();
	        });
	    });
	    </script>
	</div>
<?php
}

//add an action to tell wordpress to save the new fields on submit
add_action( 'personal_options_update', 'dj_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'dj_save_extra_profile_fields' );

//logic to perform the save
function dj_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
	return false;
	
	update_user_meta( $user_id, 'shifts', serialize($_POST['shifts']) );
}

/* Sidebar widget functions */
class DJ_Widget extends WP_Widget {
	
	function DJ_Widget() {
		$widget_ops = array('classname' => 'DJ_Widget', 'description' => 'The current on-air DJ.');
		$this->WP_Widget('DJ_Widget', 'DJ On-Air', $widget_ops);
	}
 
	function form($instance) {
		$instance = wp_parse_args((array) $instance, array( 'title' => '' ));
		$title = $instance['title'];
		$djavatar = $instance['djavatar'];
		$default = $instance['default'];
		$link = $instance['link'];
		
		?>
			<p>
		  		<label for="<?php echo $this->get_field_id('title'); ?>">Title: 
		  		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		  		</label>
		  	</p>
		  	
		  	<p>
		  		<label for="<?php echo $this->get_field_id('djavatar'); ?>"> 
		  		<input id="<?php echo $this->get_field_id('djavatar'); ?>" name="<?php echo $this->get_field_name('djavatar'); ?>" type="checkbox" <?php if($djavatar) { echo 'checked="checked"'; } ?> /> 
		  		Show Avatars
		  		</label>
		  	</p>
		  	
		  	<p>
		  		<label for="<?php echo $this->get_field_id('link'); ?>"> 
		  		<input id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="checkbox" <?php if($link) { echo 'checked="checked"'; } ?> /> 
		  		Link to DJ's user profile
		  		</label>
		  	</p>
		  	
		  	<p>
		  		<label for="<?php echo $this->get_field_id('default'); ?>">Default DJ Name: 
		  		<input class="widefat" id="<?php echo $this->get_field_id('default'); ?>" name="<?php echo $this->get_field_name('default'); ?>" type="text" value="<?php echo attribute_escape($default); ?>" />
		  		</label>
		  		<small>If no DJ is scheduled for the current hour, display this name/text.</small>
		  	</p>
		<?php
	}
 
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['djavatar'] = ( isset( $new_instance['djavatar'] ) ? 1 : 0 );
		$instance['link'] = ( isset( $new_instance['link'] ) ? 1 : 0 );
		$instance['default'] = $new_instance['default']; 
		return $instance;
	}
 
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
 
		echo $before_widget;
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$djavatar = $instance['djavatar'];
		$link = $instance['link'];
 		$default = empty($instance['default']) ? '' : $instance['default'];
		
 		//fetch the current DJs
		$djs = dj_get_current();
		
		?>
		<div class="widget">
			<?php 
				if (!empty($title)) {
					echo $before_title . $title . $after_title;
				}
				else {
					echo $before_title.$after_title;
				}
			?>
			
			<ul class="on-air-list">
				<?php 
				//find out which DJ(s) are currently scheduled to be on-air and display them
				if(count($djs) > 0) {
					foreach($djs as $dj) {
						echo '<li class="on-air-dj">';
						if($djavatar) {
							echo '<span class="on-air-dj-avatar">'.get_avatar($dj->ID).'</span>';
						}
						
						if($link) {
							echo '<a href="';
							echo get_author_posts_url($dj->ID);
							echo '">';
							echo $dj->display_name.'</a>';
						}
						else {
							echo $dj->display_name;
						}
						echo '<span class="clear"></span></li>';
					}
				}
				else {
					echo '<li class="on-air-dj default-dj">'.$default.'</li>';
				}
				?>
			</ul>
		</div>
		<?php
 
		echo $after_widget;
	}
}

/* Admin Functions */

//check to see if the user has access to make changes
function dj_hasPluginAccess() {
	global $user_ID;
	global $djDefaultOptionVals;
	
	//ensure we have a logged in user
	if (!empty($user_ID)) {
		$user = new WP_User($user_ID);
		
		if (!is_array($user->roles)) $user->roles = array($user->roles);
		foreach ($user->roles as $role) {
			if (in_array($role, $djDefaultOptionVals['roles'])) {
				return true;
			}
		}
	}
	 
	return false;
}

//create a menu item for the options page
function dj_admin_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('DJ On-Air Options', 'DJ On-Air', 'manage_options', 'dj-on-air-widget', 'dj_admin_options');
	}
}
add_action( 'admin_menu', 'dj_admin_menu' );

//output the options page
function dj_admin_options() {
	global $djDefaultOptionVals;
	
	
	//grab the array of all user roles
	$roles = new WP_Roles();
	$roles = array_keys($roles->role_names);
 
  	//watch for form submission
	if (!empty($_POST['dj_access_roles'])) {
    	//validate the referer
		check_admin_referer('dj_access_roles_options_valid');
 
		if (empty($_POST['dj_access_roles'])) {
			echo '<div id="message" class="updated fade"><p><strong>' . __('You must select at least one role for this application to be properly enabled.') . '</strong></p></div>';
			return false;
		}
 
    	//update the new value
		$djDefaultOptionVals['roles'] = $_POST['dj_access_roles'];
 
		//update options settings
		update_option('dj_access_roles', $djDefaultOptionVals);
		update_option('dj_time_settings', $_POST['dj_time_settings']);
		update_option('dj_default_name', $_POST['dj_default_name']);
 
		//show success
		echo '<div id="message" class="updated fade"><p><strong>' . __('Your configuration settings have been saved.') . '</strong></p></div>';
	}
 
	//display the admin options page
?>
 
<div style="width: 620px; padding: 10px">
	<h2><?php _e('DJ On-air Options'); ?></h2>
	<form action="" method="post" id="me_likey_form" accept-charset="utf-8" style="position:relative">
		<?php wp_nonce_field('dj_access_roles_options_valid'); ?>
		<input type="hidden" name="action" value="update" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row">User Role Restriction*</th>
				<td>
					<select name="dj_access_roles[]" id="dj_access_roles" multiple="multiple" style="height: 150px;">
					<?php
						if (!empty($roles)) {
							foreach ($roles as $role) {
								echo '<option value="' . $role . '"' . (in_array($role, $djDefaultOptionVals['roles']) ? ' selected="selected"' : '') . '>' . $role . '</option>';
							}
						}
					?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
					<td>Please select all user roles that should be allowed to add DJ schedules to user accounts.</td>
			</tr>
			<tr valign="top">
				<th scope="row">Shift Length</th>
				<td>
					<?php $time_settings = get_option('dj_time_settings'); ?>
					<select name="dj_time_settings" id="dj_time_settings">
						<option value="hour" <?php if($time_settings == 'hour') { echo 'selected="selected"'; } ?>>Hour</option>
						<option value="halfhour" <?php if($time_settings == 'halfhour') { echo 'selected="selected"'; } ?>>Half-Hour</option>
						<option value="quarterhour" <?php if($time_settings == 'quarterhour') { echo 'selected="selected"'; } ?>>15 Minutes</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
					<td>Please select the length of a DJ's shift.</td>
			</tr>
			<tr valign="top">
				<th scope="row">Default DJ on Schedule</th>
				<td>
					<?php $default_dj = get_option('dj_default_name'); ?>
					<input type="text" name="dj_default_name" id="dj_default_name" value="<?php echo $default_dj; ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
					<td>Enter the name or text to be displayed when no DJ is on-air (For use with the [dj-schedule] shortcode. The sidebar widget will still allow you to set this value separately.)</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
				</td>
			</tr>
		</table>
	</form>
</div>
 
<?php
}

add_action( 'widgets_init', create_function('', 'return register_widget("DJ_Widget");') );

?>