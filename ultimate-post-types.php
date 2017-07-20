<?php
/**
 * Plugin Name: Ultimate Post Types
 * Plugin URI: http://post-types.ultimate-fields.com/
 * Description: An add-on for Ultimate Fields which allows creating post types and assign fields to them.
 * Version: 0.3
 * Author: Radoslav Georgiev
 * Author URI: http://rageorgiev.com/
 * Copyright: Radoslav Georgiev
 */

/**
 * Loads the whole plugin, sub-files and etc.
 */
add_action( 'plugins_loaded', 'upt_load' );
function upt_load() {
	if( upt_check_ultimate_fields() ) {
		include( 'loader.php' );
	} else {
		include( 'classes/parent/UF_Notices.php' );
		$message = __( 'The Ultimate Post Types plugin is active, but it will not work until it&apos;s dependency &quot;Ultimate Fields&quot; is active too. Please <a href="plugin-install.php?tab=plugin-information&plugin=ultimate-fields&TB_iframe=true&width=830&height=880" class="thickbox">install Ultimate Fields</a>.' );
		UF_Notices::add( $message, true );
	}
}

/**
 * Checks if the mother-plugin is active.
 *
 * @package Ultimate Post Types
 * @since 0.1
 *
 * @return boolean
 */
function upt_check_ultimate_fields() {
	$plugin = 'ultimate-fields/ultimate-fields.php';

	if( in_array( $plugin, get_option( 'active_plugins', array() ) ) ) {
		return true;
	} elseif( is_multisite() ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if( isset( $network_plugins[ $plugin ] ) ) {
			return true;
		}
	}

	return false;
}