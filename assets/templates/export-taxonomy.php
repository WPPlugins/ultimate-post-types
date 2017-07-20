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
* This code will setup a taxonomy called <?php echo $title ?>.
* The register_<?php echo $slug ?>_taxonomy function does not require either Ultimate Fields or Ultimate Post Types
* to be installed or enabled.
* 
* Add this code directly to you functions.php file or a file that's included in it.
*
* For more information, please visit http://ultimate-fields.com/
*/
add_action( 'init', 'register_<?php echo str_replace( '-', '_', $slug ) ?>_taxonomy' );
function register_<?php echo str_replace( '-', '_', $slug ) ?>_taxonomy() {
	register_taxonomy( '<?php echo $slug ?>', array( '<?php echo implode( "', '", $post_types ) ?>' ), <?php echo $out ?> );
}

<?php if( $fields ): ?>
/**
 * The following code sets up the term meta container that is associated with the <?php echo $title ?> taxonomy.
 *
 * This code does require Ultimate Fields and Ultimate Fields Premium to be installed,
 * but not Ultimate Post Types.
 *
 * If for some reason none of the plugins is installed, this code will not be executed and will not cause errors.
 * That's because it is hooked to the 'uf_setup_containers' action, which is only executed if Ultimate Fields is present.
 */
add_action( 'uf_setup_containers', 'setup_<?php echo str_replace( '-', '_', $slug ) ?>_taxonomy_meta_fields' );
function setup_<?php echo str_replace( '-', '_', $slug ) ?>_taxonomy_meta_fields() {
	uf_setup_container( array (
		'uf_title' => '<?php printf( __( '%s Settings', 'upt' ), $title ) ?>',
		'uf_type' => 'term-meta',
		'uf_termsmeta_taxonomies' => array( '<?php echo $slug ?>' ),
		'uf_options_page_slug' => '<?php echo $slug ?>-tax-settings',
		'fields' => <?php echo $fields ?>

	) );
}

<?php endif; ?>
?&gt;</textarea>
	</div>
</div>