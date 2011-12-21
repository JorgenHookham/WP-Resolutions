<?php
/*
Plugin Name: WP Resolutions 
Plugin URI: http://wpresolutions.co
Description: Resolutions will make sure that image files are never bigger than what a device can use.
Version: Beta
Author: J&oslash;rgen Hookham
Author URI: http://jorgen.co
License: GPL
*/

$pluginname = "WP Resolutions";
$shortname = "wprxr"; 
$options = array (

array(  "type" => "open"),

array(	"name" => "Watch paths",
		"desc" => "Enter the paths that you want Resolutions to watch. Put each path on a new line (separate paths with a return.)",
		"id" => $shortname . "_include_paths",
		"type" => "textarea",
		"std" => "/wp-content/uploads/"),

array(  "type" => "close")

);

$server_path = explode('wp-content', $theme_directory);
$theme_directory = dirname(__FILE__);
$theme_directory = str_replace($server_path[0], '', $theme_directory);



// Javascript cookie needs to be created before any image requests (including css)
function wprxr_js()
{
	echo "<script>document.cookie='resolution='+Math.max(screen.width,screen.height)+'; path=/';</script>";
}



function wprxr_add_page()
{

    global $pluginname, $shortname, $options;

    if ( $_GET['page'] == 'wprxr' )
    {
        if ( 'save' == $_REQUEST['action'] )
        {
			
			// Redundant?
            foreach ($options as $value)
            {
                update_option( $value['id'], $_REQUEST[ $value['id'] ] );
			}

		    foreach ($options as $value)
		    {
		        if( isset( $_REQUEST[ $value['id'] ] ) )
		        {
		        	update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
		        }
		        
		        else
		        {
		        	delete_option( $value['id'] );
		        }
			}

            header("Location: options-general.php?page=wprxr&saved=true");
            die;
        }
    }

    add_options_page("WP Resolutions", "WP Resolutions", 'edit_themes', 'wprxr', 'wprxr_page');
}



// Set up the plugin when it is activated
function wprxr_activate()
{
	add_option('wprxr_include_paths', '/wp-content/uploads/');
	
	$new_htaccess = wprxr_htaccess();

	if ( !file_exists(get_home_path() . '.htaccess') ) 
	{
		@fopen(get_home_path() . '.htaccess', 'w') or die("<div id=\"message\" class=\"error\"><strong>No .htaccess file exists, and one could not be created.</strong> To fix this you can: <br> 1. Update permissions for the WordPress root directory to allow write access, or <br> 2. Manually create your .htaccess file with this rewrite block:<br><br> <pre>$new_htaccess</pre></div>");
	}
	
	if ( !is_writable(get_home_path() . '.htaccess') )
	{
		die("<div id=\"message\" class=\"error\"><strong>The permissions on your .htaccess file restrict automatic setup.</strong> To fix this you can: <br> 1. Change permissions on the .htaccess file in your WordPress root directory to allow write access, or <br> 2. Manually add this rewrite block to your .htaccess file: <br><br> <pre>$new_htaccess</pre></div>");
	}
	
	if ( !is_dir(get_home_path() . 'ai-cache'))
	{
		@mkdir(get_home_path() . 'ai-cache') or die("<div id=\"message\" class=\"error\"><strong>Unable to create cache folder.</strong> To fix this you can: <br> 1. Update permissions for the WordPress root directory to allow write access, or <br> 2. Manually create the directory 'ai-cache' within your WordPress root directory and give it write permissions.</div>");
	}
	
	$old_htaccess = file_get_contents(get_home_path() . '.htaccess');
		
	if ( preg_match('/# WP Resolutions.*# END WP Resolutions\n/s', $old_htaccess) )
	{
		$new_htaccess = preg_replace('/# WP Resolutions.*# END WP Resolutions\n/s', $new_htaccess, $old_htaccess);
	}
	
	else
	{
		$new_htaccess .= $old_htaccess;
	}
	
	file_put_contents(get_home_path() . '.htaccess', $new_htaccess) or die("<div id=\"message\" class=\"error\"><strong>Could not write to .htaccess.</div>");
}



// Remove the plugin when it is deactivated
function wprxr_deactivate()
{
	$old_htaccess = file_get_contents(get_home_path() . '.htaccess');
	$new_htaccess = preg_replace('/# WP Resolutions.*# END WP Resolutions\n/s', '', $old_htaccess);
	file_put_contents(get_home_path() . '.htaccess', $new_htaccess);
}



// This function returns the .htaccess rewrite block
function wprxr_htaccess ()
{
	$wprxr_include_paths = get_settings('wprxr_include_paths');
	
	$includes = explode("\n", $wprxr_include_paths);
    
	$new_htaccess = "# WP Resolutions\n<IfModule mod_rewrite.c>\nRewriteEngine On\n\n# Watch directories:";

	foreach ( $includes as $include )
		$new_htaccess .= "\nRewriteCond %{REQUEST_URI} $include";

	$new_htaccess .= "\n\nRewriteRule \.(?:jpe?g|gif|png)$ $theme_directory /adaptive-images.php\n</IfModule>\n# END WP Resolutions\n";
	
	return $new_htaccess;
}



// The WP Resolutions settings page
function wprxr_page()
{

	global $options, $pluginname, $shortname;
	
	$new_htaccess = wprxr_htaccess();

?>
<div class="wrap">
<h2><?php echo $pluginname; ?> settings</h2>

<?php
	
    if ( $_REQUEST['saved'] )
    {
		
		$old_htaccess = file_get_contents(get_home_path() . '.htaccess');
		
		if ( preg_match('/# WP Resolutions.*# END WP Resolutions\n/s', $old_htaccess) )
		{
			$new_htaccess = preg_replace('/# WP Resolutions.*# END WP Resolutions\n/s', $new_htaccess, $old_htaccess);
		}
		
		else
		{
			$new_htaccess .= $old_htaccess;
		}
		
		file_put_contents(get_home_path() . '.htaccess', $new_htaccess);
		
		echo '<div id="message" class="updated fade"><p><strong>WP Resolutions updated successfully.</strong></p></div>';
    }
?>

<form method="post">

<?php

foreach ($options as $value)
{

	switch ( $value['type'] )
	{

		case "open":
			echo '<table width="100%" border="0" style="background-color:#eef5fb; padding:10px;">';
		break;

		case "close":
			echo '</table><br />';
		break;

		case "title":
			echo '<table width="100%" border="0" style="background-color:#dceefc; padding:5px 10px;"><tr>';
    		echo '<td colspan="2"><h3 style="font-family:Georgia,\'Times New Roman\',Times,serif;">' .  $value['name'] . '</h3></td>';
			echo '</tr>';
		break;

		case 'text':
			$value_or_std = ( get_settings( $value['id'] ) != "" ) ? get_settings( $value['id'] ) : $value['std'];
			echo '<tr>';
			echo '<td width="20%" rowspan="2" valign="middle"><strong>' . $value['name'] . '</strong></td>';
			echo '<td width="80%"><input style="width:400px;" name="' . $value['id'] . '" id="' . $value['id'] . '" type="' . $value['type'] . '" value="' . $value_or_std . '" /></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><small>' . $value['desc'] . '</small></td>';
			echo '</tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
		break;

		case 'textarea':
			$value_or_std = ( get_settings( $value['id'] ) != "" ) ? get_settings( $value['id'] ) : $value['std'];
			echo '<tr>';
			echo '<td width="20%" rowspan="2" valign="middle"><strong>' . $value['name'] . '</strong></td>';
			echo '<td width="80%"><textarea name="' . $value['id'] . '" style="width:400px; height:200px;" type="' . $value['type'] . '" cols="" rows="">' . $value_or_std . '</textarea></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><small>' . $value['desc'] . '</small></td>';
			echo '</tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
		break;

		case 'select':
			echo '<tr>';
			echo '<td width="20%" rowspan="2" valign="middle"><strong>' . $value['name'] . '</strong></td>';
			echo '<td width="80%"><select style="width:240px;" name="' . $value['id'] . '" id="' . $value['id'] . '">';
			foreach ($value['options'] as $option)
			{
				$selected = ( get_settings( $value['id'] ) == $option || $option == $value['std']) ? ' selected="selected"' : '';
				echo '<option' . $selected . '>' . $option . '</option>';
			}
			echo '</select></td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><small>' . $value['desc'] . '</small></td>';
			echo '</tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
		break;

		case "checkbox":
			$cheked = (get_settings($value['id'])) ? ' checked="checked"' : '';
			echo '<tr>';
			echo '<td width="20%" rowspan="2" valign="middle"><strong>' . $value['name'] . '</strong></td>';
			echo '<td width="80%">';
			echo '<input type="checkbox" name="' . $value['id'] . '" id="' .  $value['id'] . '" value="true"' . $checked . '/>';
			echo '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td><small>' . $value['desc'] . '</small></td>';
			echo '</tr><tr><td colspan="2" style="margin-bottom:5px;border-bottom:1px dotted #000000;">&nbsp;</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
		break;

	}
}

?>

<p class="submit">
<input name="save" type="submit" value="Save changes" />
<input type="hidden" name="action" value="save" />
</p>
</form>

<?php
}



// Add settings link on the WordPress plugins page.
function wprxr_add_settings_link($links, $file)
{
	static $this_plugin;
	
	if ( !$this_plugin )
		$this_plugin = plugin_basename(__FILE__);
	 
	if ( $file == $this_plugin )
	{
		$settings_link = '<a href="options-general.php?page=wprxr">'.__("Settings", "WP Resolutions").'</a>';
		array_unshift($links, $settings_link);
	}
	
	return $links;
}

add_action(wp_head, wprxr_js, 0);
add_action('admin_menu', 'wprxr_add_page');
register_activation_hook(__FILE__, 'wprxr_activate');
register_deactivation_hook(__FILE__, 'wprxr_deactivate');
add_filter('plugin_action_links', 'wprxr_add_settings_link', 10, 2 );

?>