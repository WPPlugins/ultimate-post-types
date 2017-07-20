<?php
/**
 * Loads all necessary files and triggers them.
 * 
 * @package Ultimate Post Types.
 * @since 0.1
 */

# Define some basic constants for throughout the plugin
define( 'UPT_URL', plugins_url( '/', __FILE__ ) );
define( 'UPT_DIR', dirname( __FILE__ ) . '/' );
define( 'UPT_VER', '0.1' );

# Add basic actions
add_action( 'admin_enqueue_scripts', 'upt_register_admin_scripts' );

# Register plugin textdomain
$mofile = UPT_DIR . "/languages/upt-" . get_locale() . ".mo";
if ( file_exists( $mofile ) )
	load_textdomain( 'upt', $mofile );

# Include the necessary classes
require_once( 'classes/UPT_Editable_Base.php' );
require_once( 'classes/UPT_Post_Type.php' );
require_once( 'classes/UPT_Custom_Post_Type.php' );
require_once( 'classes/UPT_Taxonomy.php' );
require_once( 'classes/UPT_Custom_Taxonomy.php' );

# Some functions
function upt_register_admin_scripts() {
	wp_register_script( 'upt-admin', UPT_URL . 'assets/js/upt-admin.js', array(), UPT_VER );
}

/**
 * Exclude the new post types from Ultimate Fields.
 * 
 * @param string[] $post_types The post types that are already excluded.
 * @return string[]
 */
add_filter( 'uf_excluded_post_types', 'upt_exclude_own_post_types' );
function upt_exclude_own_post_types( $post_types ) {
	$post_types[] = 'ultimate-post-type';
	$post_types[] = 'upt-taxonomy';

	return $post_types;
}

# Create the main post type
new UPT_Post_Type();
new UPT_Taxonomy();