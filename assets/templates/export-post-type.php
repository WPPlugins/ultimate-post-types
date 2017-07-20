<div class="wrap uf-wrap uf-options">
<div class="head">
	<div id="icon-edit" class="icon32 icon32-posts-ultimatefields"></div>
	<h2><?php printf( __( 'Export "%s" to PHP', 'upt' ), $title ) ?></h2>

	<div style="padding: 10px 0 10px 280px; overflow:hidden;">
		<div class="metabox-holder" style="float: left; width: 250px; position: relative; margin-left: -280px; padding: 0;">
			<div class="postbox">
				<h3 class="hndle" style="cursor:default"><span><?php _e( 'Instructions', 'upt' ) ?></span></h3>
				<div class="inside">
					<p><?php
_e( 'You can export post types and taxonomies to PHP code and add them to your theme&apos;s or plugin&apos;s files.', 'upt' )
?></p>

					<p><?php
_e( 'When a post type or taxonomy is exported this way, it will not appear in the list, here in the admin.', 'upt' ) ?>
						<?php
_e( 'Even if you see the old one, the exported one will overwrite it.', 'upt' )
?></p>

					<p><?php
_e( 'The code can be added anywhere in your files, just make sure it is done before the <strong>init</strong> hook.', 'upt' ) ?>
						<?php
_e( 'Adding the code directly to your functions.php works too.', 'upt' ) ?>
						<?php
_e( 'Please keep in mind, that you do not need Ultimate Post Types enabled in order for this to work.', 'upt' )
?></p>

					<p><?php
_e( 'You only need Ultimate Fields enabled and that&apos;s only in case you have custom fields assigned to the post type.', 'upt' )
?></p>
				</div>
			</div>
		</div>

		<textarea class="uf-export" style="height:474px" readonly>&lt;?php
/**
* Ultimate Post Types Export
*
* This code will setup a post type called <?php echo $title ?>.
* The register_<?php echo $slug ?>_post_type function does not require either Ultimate Fields or Ultimate Post Types
* to be installed or enabled.
* 
* Add this code directly to you functions.php file or a file that's included in it.
*
* For more information, please visit http://ultimate-fields.com/
*/
add_action( 'init', 'register_<?php echo $slug ?>_post_type' );
function register_<?php echo $slug ?>_post_type() {
	register_post_type( '<?php echo $slug ?>', <?php echo $out ?> );
}

<?php if( $fields ): ?>
/**
 * The following code sets up the post meta container that is associated with the <?php echo $title ?> post type.
 *
 * This code only requires Ultimate Fields to be installed: Ultimate Post Types is not required.
 *
 * If for some reason none of the plugins is installed, this code will not be executed and will not cause errors.
 * That's because it is hooked to the 'uf_setup_containers' action, which is only executed if Ultimate Fields is present.
 */
add_action( 'uf_setup_containers', 'setup_<?php echo $slug ?>_meta_fields' );
function setup_<?php echo $slug ?>_meta_fields() {
	uf_setup_container( array (
		'uf_title' => '<?php printf( __( '%s Settings', 'upt' ), $title ) ?>',
		'uf_type' => 'post-meta',
		'uf_postmeta_posttype' => '<?php echo $slug ?>',
		'fields' => <?php echo $fields ?>

	) );
}

<?php endif; ?>
<?php if( $template != 'single' ): ?>
/**
 * The code below changes the template for the <?php echo $title ?> post type.
 *
 * You can omit this function if you've already created a single-<?php echo $slug ?>.php template in your theme.
 */
add_action( 'template_redirect', 'change_<?php echo $slug ?>_template' );
function change_<?php echo $slug ?>_template() {
	# The template does not need to be changed unless a singular <?php echo $title ?> post is being viewed.
	if( ! is_singular( '<?php echo $slug ?>' ) ) {
		return;
	}

	<?php if( $template == 'single' ):
		?>get_template_part( 'single', '<?php echo $slug ?>' );<?php
	elseif($template == 'page' ):
		?>get_template_part( 'page' );<?php
	else:
		?>locate_template( '<?php echo $template ?>', true, false );<?php
	endif ?>

	exit;
}

<?php endif ?>
<?php if( $before_content || $after_content ): ?>
/**
 * Adds content before/after the standard content of the post type.
 *
 * The filter should be hooked with a high priority (small number) because it can include shortcodes
 * and they will be processed at a later point.
 */
add_filter( 'the_content', 'add_<?php echo $slug ?>_fields_to_content', 2 );
function add_<?php echo $slug ?>_fields_to_content( $content ) {
	if( is_singular( '<?php echo $slug ?>' ) ) {
		<?php if( $before_content ) {
			?>$content = '<?php echo esc_html( str_replace( "'", "\'", $before_content ) ) ?>' . "\n" . $content;<?php
		} 

		if( $after_content ) {
			if( $before_content ) echo "\n\t\t";
			?>$content .= "\n" . '<?php echo esc_html( str_replace( "'", "\'", $after_content ) ) ?>';<?php
		} ?>

	}

	return $content;
}
<?php endif ?>
?&gt;</textarea>
	</div>
</div>