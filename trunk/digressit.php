<?php
/*	
Plugin Name: Digress.it
Plugin URI: http://digress.it
Description:  digress.it allows readers to comment paragraph by paragraph in the margins of a text. You can use it to comment, gloss, workshop, debate and more!
Author: Eddie A Tejeda
Version: 3.0
Author URI: http://www.visudo.com
License: GPLv2 (http://creativecommons.org/licenses/GPL/2.0/)

Special thanks to:	
Matteo Bicocchi @ www.open-lab.com
The developers of JQuery @ www.jquery.com
Mark James, for the famfamfam iconset @ http://www.famfamfam.com/lab/icons/silk/
Joss Winn and Tony Hirst @ writetoreply.com
Jesse Wilbur, Ben Vershbow, Dan Visel and Bob Stein @ futureofthebook.org
*/

define("DIGRESSIT_VERSION", '3.0');
define("DIGRESSIT_COMMUNITY", 'digress.it');
define("DIGRESSIT_COMMUNITY_HOSTNAME", 'digress.it');



register_activation_hook(__FILE__,  'activate_digressit');
register_deactivation_hook(__FILE__, 'deactivate_digressit' );

add_action('wp_print_styles', 'digressit_wp_print_styles');
add_action('wp_print_scripts', 'digressit_wp_print_scripts' );


add_action('admin_menu', 'digressit_add_admin_menu');


function activate_digressit(){
	global $wpdb;
	$options = get_option('digressit');

	//PRE-3.0
	$commentpress_upgraded_to_digress_it = get_option('commentpress_upgraded_to_digress_it');
	$digressit_community_hostname = get_option('digressit_community_hostname');
	$digressit_client_password = get_option('digressit_client_password');
	$digressit_installation_key = get_option('digressit_installation_key');


	$plugin_name = str_replace("/", "", str_replace(basename( __FILE__),"",plugin_basename(__FILE__))); 
	$plugin_url = WP_PLUGIN_URL .'/' . $plugin_name . '/';		
	$plugin_file = $plugin_url. plugin_basename(__FILE__); 


	$digressit_server = 'http://'. DIGRESSIT_COMMUNITY_HOSTNAME . '/';


	$is_multiuser = is_mu_or_network_mode();			


	$theme_url = $plugin_url. 'theme/'; 

	$js_path = $plugin_url. 'js/'; 
	$jquery_path = $js_path . 'jquery/'; 
	$jquery_extensions_path =  $jquery_path. 'external/'; 
	$jquery_theme_path = $jquery_path . 'themes/'; 
	$jquery_elements_path = $jquery_path . 'elements/'; 
	$jquery_css_path = $jquery_path . 'css/'; 

	$style_path = $plugin_url . 'style/'; 
	$image_path = $plugin_url . 'theme/images/'; 
	$punctuations = null;


	$url = $_SERVER["SERVER_NAME"] ;
	preg_match("/^(http:\/\/)?([^\/]+)/i" , $url, $found);
	preg_match("/[^\.\/]+\.[^\.\/]+$/" , $found[2], $found);



	$hostname = $found[0];
	$default_skin = 'skin1';
	$default_stylesheet  = 'default';

	
	$installation_key  = null;
	$installation_key = strlen($current_digressit['installation_key']) == 32 ? $current_digressit['installation_key'] : null;

	$options['wp_path'] = $wp_path;
	$options['debug_mode'] = 0;
	$options['allow_text_selection'] = 0;
	$options['default_skin'] = $default_skin;
	$options['stylesheet'] = $default_stylesheet;
	$options['default_left_position'] = '400px';
	$options['default_top_position'] = '175px';
	$options['allow_users_to_minimize'] = 0;
	$options['allow_users_to_resize'] = 0;
	$options['server_sync_interval'] = $monthly;
	$options['allow_users_to_drag'] = 1;
	$options['highlight_color'] = '#FFFC00';
	$options['parse_list_items'] = 0;
	$options['enable_chrome_frame']	= 1;
	$options['front_page_post_type'] = 'post';
	$options['front_page_numberposts'] = 10;
	$options['frontpage_sidebar'] = 0;
	$options['front_page_content'] = '';
	$options['front_page_order'] = 'ASC';
	$options['front_page_order_by'] = 'date';
	$options['allow_general_comments'] = 1;
	$options['allow_comments_search'] = 0;
	
	
	
	$options['commentpress_upgraded_to_digress_it'] = $digressit_installation_key;
	$options['digressit_community_hostname'] = $digressit_community_hostname;
	$options['digressit_client_password'] = $digressit_client_password;
	$options['digressit_installation_key'] = $digressit_installation_key;

	$options['content_parser'] = 'standard_digressit_content_parser';
	$options['comments_parser'] = 'standard_digressit_comment_parser';
	$options['commentbox_parser'] = 'standard_digressit_commentbox_parser';
	
	
	
	
	
	delete_option('digressit');
	add_option('digressit', $options);	
	
	
	$sql = "SHOW COLUMNS FROM $wpdb->comments";	
	$columns = $wpdb->get_results($sql);

	$comment_text_signature_exists = false;
	foreach($columns as $col){
		if($col->Field == 'comment_text_signature'){
			$comment_text_signature_exists = true;
		}
	}

	

	if($comment_text_signature_exists == false){
		$sql = "ALTER TABLE `$wpdb->comments` ADD `comment_text_signature` VARCHAR( 255 ) NULL;";	
		$wpdb->query($sql);
	}



	$themes_dir = WP_CONTENT_DIR . '/themes/';
	$plugin_theme_link = WP_CONTENT_DIR . '/plugins/'. $plugin_name.'/theme/';



	$options = get_option('digressit');
	
	if(is_writable( $themes_dir)){

		$theme_link = $themes_dir . $plugin_name;
		
		//CREATE THE THEME DIRECTORY
		if(is_link($theme_link)){
			//i think we're good
		}
		elseif(!file_exists($theme_link)){
			if(symlink($plugin_theme_link,$theme_link)){
				//we're good
				update_option($options['theme_mode'], 'stylesheet');
			}
			else{
				die( "There was an error creating the symlink of <b>$plugin_theme_link</b> in <b>$theme_link</b>. If the server doesn't have write permission try creating it manually");
			}
		}
		else{
			//probably a windows person
			//die( "There was a error creating the symlink of <b>$plugin_theme_link</b> in <b>$theme_link</b>. Maybe a theme named DigressIt already exists?");					
		}
		
		
	}
	else{
		die("no write permission on $themes_dir please give the server write permission on this directory");
	}
	
	switch_theme($plugin_name, $plugin_name);	

}


function deactivate_digressit(){
	switch_theme('default', 'default');	
}





function digressit_wp_print_styles(){
}

function digressit_wp_print_scripts(){		

}

function digressit_add_admin_menu() {
	add_submenu_page( 'themes.php', 'Digress.it', 'Digress.it', 'administrator', 'digressit.php', 'digressit_theme_options_page');
}




function digressit_theme_options_page() {
	global $wpdb, $digressit_content_function, $digressit_comments_function, $digressit_commentbox_function, $blog_id;


	//var_dump($digressit_content_function);
	if($_POST['reset'] == 'Reset Options'){
		delete_option('digressit');
		activate_digressit();
		//echo "resetting";
		
	}
	elseif(isset($_POST['update-digressit-options'])){
		$options = get_option('digressit');
		
		foreach($_POST as $key => $value){
			$options[$key] = $value;
		}
		
		delete_option('digressit');
		add_option('digressit', $options);
		//echo "updating";
	}

	$options = get_option('digressit');



		
	?>
  	<div class="wrap" style="position: relative; font-size: 110%;">
	
		<div id="digressit-donate" style="position: absolute; right: 50px; top: 50px; width: 200px; border: 1px solid; background-color: white; padding: 0 10px">
		<h3>Please consider donating to help keep this project alive:</h3>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="XYBB4WEBLRHMN">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		</div>
	
		<form method="post" action="<?php $PHP_SELF; ?>">

	<h2>Digress.it Options</h2>

	<table class="form-table" style="vertical-align: top; width: 800px; padding: 0; margin: 0" >
	


	<?php if(is_super_admin()): ?>
	<tr>
		<td style="width: 200px"><b>Debug Mode</b></td>
		<td><?php print_dropdown('debug_mode', array('no' => 0, 'yes' => '1'), $options['debug_mode']); ?></td>
	</tr>
	<?php endif; ?>
		
	
	<?php   
	
		$pages = null;
		foreach(get_pages() as $page){
			$pages[$page->post_title] = $page->ID;			
		}
		
	?>
		
	<tr>
		<td style="width: 200px"><b>Front Page Content</b></td>
		<td><?php print_dropdown('front_page_content', $pages, $options['front_page_content']); ?></td>
	</tr>
		
	<tr>
		<td style="width: 200px"><b>Content Parsing Function</b></td>
		<td><?php print_dropdown('content_parser', $digressit_content_function, $options['content_parser']); ?></td>
	</tr>

	<tr>
		<td style="width: 200px"><b>Comments Parsing Function</b></td>
		<td><?php print_dropdown('comments_parser', $digressit_comments_function, $options['comments_parser']); ?></td>
	</tr>
	
	<tr>
		<td style="width: 200px"><b>CommentBox Parsing Function</b></td>
		<td><?php print_dropdown('commentbox_parser', $digressit_commentbox_function, $options['commentbox_parser']); ?></td>
	</tr>


	<tr>
		<td style="width: 200px"><b>Allow General Comments</b></td>
		<td><?php print_dropdown('allow_general_comments', array('no' => 0, 'yes' => '1'), $options['allow_general_comments']); ?></td>
	</tr>

	<tr>
		<td style="width: 200px"><b>Allow Comments Search</b></td>
		<td><?php print_dropdown('allow_comments_search', array('no' => 0, 'yes' => '1'), $options['allow_comments_search']); ?></td>
	</tr>
	
	

	</table>

	<input type="hidden" name="update-digressit-options" value="1" />

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	<input type="submit" name="reset" class="button-primary" value="Reset Options" />
	</p>

	</form>
	</div>
	<?php 
	//restore_current_blog();
}







function print_dropdown($form, $options = array(), $selected){
	echo "<select name='$form'>";
	foreach($options as $name => $value) {
		$selected_html = ($value == $selected) ? " selected='selected' " : '';
		echo "<option $selected_html value='$value'>$name</option>";
	}
	echo "</select>";
}

function is_mu_or_network_mode(){

	$is_multiuser = false;

	if(function_exists('wpmu_create_blog') || WP_ALLOW_MULTISITE){
		$is_multiuser = true;
	}
	
	return 	$is_multiuser;
}


?>