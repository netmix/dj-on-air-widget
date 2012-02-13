<?php
/**
* @package DJ_On_Air_Widget
* @version 0.2
*/
/*
Plugin Name: DJ On Air Widget
Plugin URI: http://nlb-creations.com/2011/09/02/wordpress-plugin-dj-on-air-widget/
Description: This plugin adds additional fields to user profiles to designate users as DJs and provide shift scheduling.
Author: Nikki Blight <nblight@nlb-creations.com>
Version: 0.2.1
Author URI: http://www.nlb-creations.com
*/

global $defaultOptionVals;

//set the default options, or, if they've already been customized, load the options.
function set_globals() {
	global $defaultOptionVals;
	
	$options = get_option('dj_access_roles');
	
	if(!$options) {
		$defaultOptionVals = array(
			'roles' => array('administrator')
		);
		update_option('dj_access_roles', $defaultOptionVals);
	}
	else {
		$defaultOptionVals = $options;
	}
}
add_action( 'init', 'set_globals' );

//add the stylesheet to the frontend theme
if ( !is_admin() ) {
	$dir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	wp_enqueue_style( 'dj-on-air', $dir.'/styles/djonair.css' );
}

//show the extra fields on the user profile form and load the widget
add_action( 'show_user_profile', 'dj_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'dj_show_extra_profile_fields' );

/* Generates the customized meta field for the shift list
* Code adapted from http://wordpress.stackexchange.com/questions/19838/create-more-meta-boxes-as-needed/19852#19852
* and http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
*/
function dj_show_extra_profile_fields( $user ) { 
	if(!hasPluginAccess()) {
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
	            			<option value="00:00"<?php if($track['time'] == "00:00") { echo ' selected="selected"'; } ?>>12am</option>
	            			<option value="01:00"<?php if($track['time'] == "01:00") { echo ' selected="selected"'; } ?>>1am</option>
	            			<option value="02:00"<?php if($track['time'] == "02:00") { echo ' selected="selected"'; } ?>>2am</option>
	            			<option value="03:00"<?php if($track['time'] == "03:00") { echo ' selected="selected"'; } ?>>3am</option>
	            			<option value="04:00"<?php if($track['time'] == "04:00") { echo ' selected="selected"'; } ?>>4am</option>
	            			<option value="05:00"<?php if($track['time'] == "05:00") { echo ' selected="selected"'; } ?>>5am</option>
	            			<option value="06:00"<?php if($track['time'] == "06:00") { echo ' selected="selected"'; } ?>>6am</option>
	            			<option value="07:00"<?php if($track['time'] == "07:00") { echo ' selected="selected"'; } ?>>7am</option>
	            			<option value="08:00"<?php if($track['time'] == "08:00") { echo ' selected="selected"'; } ?>>8am</option>
	            			<option value="09:00"<?php if($track['time'] == "09:00") { echo ' selected="selected"'; } ?>>9am</option>
	            			<option value="10:00"<?php if($track['time'] == "10:00") { echo ' selected="selected"'; } ?>>10am</option>
	            			<option value="11:00"<?php if($track['time'] == "11:00") { echo ' selected="selected"'; } ?>>11am</option>
	            			<option value="12:00"<?php if($track['time'] == "12:00") { echo ' selected="selected"'; } ?>>12pm</option>
	            			<option value="13:00"<?php if($track['time'] == "13:00") { echo ' selected="selected"'; } ?>>1pm</option>
	            			<option value="14:00"<?php if($track['time'] == "14:00") { echo ' selected="selected"'; } ?>>2pm</option>
	            			<option value="15:00"<?php if($track['time'] == "15:00") { echo ' selected="selected"'; } ?>>3pm</option>
	            			<option value="16:00"<?php if($track['time'] == "16:00") { echo ' selected="selected"'; } ?>>4pm</option>
	            			<option value="17:00"<?php if($track['time'] == "17:00") { echo ' selected="selected"'; } ?>>5pm</option>
	            			<option value="18:00"<?php if($track['time'] == "18:00") { echo ' selected="selected"'; } ?>>6pm</option>
	            			<option value="19:00"<?php if($track['time'] == "19:00") { echo ' selected="selected"'; } ?>>7pm</option>
	            			<option value="20:00"<?php if($track['time'] == "20:00") { echo ' selected="selected"'; } ?>>8pm</option>
	            			<option value="21:00"<?php if($track['time'] == "21:00") { echo ' selected="selected"'; } ?>>9pm</option>
	            			<option value="22:00"<?php if($track['time'] == "22:00") { echo ' selected="selected"'; } ?>>10pm</option>
	            			<option value="23:00"<?php if($track['time'] == "23:00") { echo ' selected="selected"'; } ?>>11pm</option>
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
				output += '<option value="00:00">12am</option>';
				output += '<option value="01:00">1am</option>';
				output += '<option value="02:00">2am</option>';
				output += '<option value="03:00">3am</option>';
				output += '<option value="04:00">4am</option>';
				output += '<option value="05:00">5am</option>';
				output += '<option value="06:00">6am</option>';
				output += '<option value="07:00">7am</option>';
				output += '<option value="08:00">8am</option>';
				output += '<option value="09:00">9am</option>';
				output += '<option value="10:00">10am</option>';
				output += '<option value="11:00">11am</option>';
				output += '<option value="12:00">12pm</option>';
				output += '<option value="13:00">1pm</option>';
				output += '<option value="14:00">2pm</option>';
				output += '<option value="15:00">3pm</option>';
				output += '<option value="16:00">4pm</option>';
				output += '<option value="17:00">5pm</option>';
				output += '<option value="18:00">6pm</option>';
				output += '<option value="19:00">7pm</option>';
				output += '<option value="20:00">8pm</option>';
				output += '<option value="21:00">9pm</option>';
				output += '<option value="22:00">10pm</option>';
				output += '<option value="23:00">11pm</option>';
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
		
 
		//load the info for the DJ
		global $wpdb;
		
		//get the current time
		$now = date('H', strtotime(current_time("mysql", $gmt)));
		$curDay = date('l', strtotime(current_time("mysql", $gmt)));
		
		$day_map = array(
						'Monday' => '{s:3:"day";s:6:"Monday";',
						'Tuesday' => '{s:3:"day";s:7:"Tuesday";',
						'Wednesday' => '{s:3:"day";s:9:"Wednesday";',
						'Thursday' => '{s:3:"day";s:8:"Thursday";',
						'Friday' => '{s:3:"day";s:6:"Friday";',
						'Saturday' => '{s:3:"day";s:8:"Saturday";',
						'Sunday' => '{s:3:"day";s:6:"Sunday";'
					);
		
		$serializedDayTime = $day_map[$curDay].'s:4:"time";s:5:"'.$now.':00";}';
		
		
		$dj_ids = $wpdb->get_results("SELECT `meta`.`user_id` FROM ".$wpdb->prefix."usermeta AS `meta`
												WHERE `meta_key` = 'shifts' 
												AND `meta_value` LIKE '%".$serializedDayTime."%';"   
												);
		$djs = array();
		foreach($dj_ids as $id) {
			$fetch = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."users AS `user` WHERE `user`.`ID` = ".$id->user_id.";");
			
			$djs[] = $fetch;
		}
		
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
function hasPluginAccess() {
	global $user_ID;
	global $defaultOptionVals;
	
	//ensure we have a logged in user
	if (!empty($user_ID)) {
		$user = new WP_User($user_ID);
		
		if (!is_array($user->roles)) $user->roles = array($user->roles);
		foreach ($user->roles as $role) {
			if (in_array($role, $defaultOptionVals['roles'])) {
				return true;
			}
		}
	}
	 
	return false;
}

//create a menu item for the options page
function admin_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('DJ On-Air Options', 'DJ On-Air', 'manage_options', 'dj-on-air-widget', 'admin_options');
	}
}
add_action( 'init', 'admin_menu' );

//output the options page
function admin_options() {
	global $defaultOptionVals;
	
	
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
		$defaultOptionVals['roles'] = $_POST['dj_access_roles'];
 
		//update options settings
		update_option('dj_access_roles', $defaultOptionVals);
 
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
								echo '<option value="' . $role . '"' . (in_array($role, $defaultOptionVals['roles']) ? ' selected="selected"' : '') . '>' . $role . '</option>';
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