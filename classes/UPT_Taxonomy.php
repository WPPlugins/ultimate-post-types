<?php
/**
 * Handles the post type that handles taxonomies.
 *
 * @package Ultimate Post Types
 * @since 0.1
 */
class UPT_Taxonomy extends UPT_Editable_Base {	
	/**
	 * Holds the name of the post type to allow easy swapping later.
	 * 
	 * @type string
	 * @since 0.1
	 */
	protected $slug = 'upt-taxonomy';

	/**
	 * Registers the post type with all appropriate vars
	 * 
	 * @since 0.1
	 */
	public function register() {
		$args = array(
			'hierarchical'        => true,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=ultimate-post-type',
			'menu_position'       => 91,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title' ),

			'labels' => array(
				'name'                => __( 'Taxonomies', 'upt' ),
				'singular_name'       => __( 'Taxonomy', 'upt' ),
				'add_new'             => __( 'Add Taxonomy', 'upt' ),
				'add_new_item'        => __( 'Add Taxonomy', 'upt' ),
				'edit_item'           => __( 'Edit Taxonomy', 'upt' ),
				'new_item'            => __( 'New Taxonomy', 'upt' ),
				'view_item'           => __( 'View Taxonomy', 'upt' ),
				'search_items'        => __( 'Search Taxonomies', 'upt' ),
				'not_found'           => __( 'No Taxonomies found', 'upt' ),
				'not_found_in_trash'  => __( 'No Taxonomies found in Trash', 'upt' ),
				'parent_item_colon'   => __( 'Parent Taxonomy:', 'upt' ),
				'menu_name'           => __( 'Taxonomies', 'upt' ),
			)
		);

		register_post_type( $this->slug, $args );

		# Immediately after this post type is registered, register the custom ones
		$this->register_taxonomies();
	}

	/**
	 * Registers all user-created taxonomies.
	 */
	public function register_taxonomies() {
		$taxonomies = get_posts(array(
			'post_type'      => $this->slug,
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		));

		foreach( $taxonomies as $taxonomy ) {
			new UPT_Custom_Taxonomy( $taxonomy );
		}
	}

	/**
	 * Display save/delete buttons in the space for tabs when editing a container.
	 * 
	 * @param UF_Container The container whose tabs are being displayed.
	 */
	public function add_save_button( $container ) {
		if( $container->get_id() != 'taxonomy-settings' ) {
			return;
		}

		echo '<div class="submitbox">';
			submit_button( __( 'Save' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) );
		echo '</div>';
	}

	/**
	 * When Ultimate Fields is setting up it's fields,
	 * this method will be called.
	 * 
	 * @since 0.1
	 */
	public function setup_fields() {
		if( class_exists( 'UF_Postmeta_B' ) ) {
			$box = new UF_Postmeta_B( __( 'Taxonomy Settings', 'upt' ), $this->slug );
		} else {
			$box = new UF_Postmeta( __( 'Taxonomy Settings', 'upt' ), $this->slug );
		}

		$post_types = array();
		$excluded = apply_filters( 'uf_excluded_post_types', array( 'attachment', 'ultimatefields' ) );
		$raw = get_post_types( array(
			'show_ui' => true
		), 'objects' );
		foreach( $raw as $id => $post_type ) {
			if( in_array( $id, $excluded ) ) {
				continue;
			}

			$post_types[ $id ] = $post_type->labels->name;
		}

		$box->tab( 'main', array(
			UF_Field::factory( 'text', 'upt_tax_slug', __( 'Slug', 'upt' ) )
				->set_description( __( 'This slug will be used when quierying posts from the post type or in URLs by default. Please use only lowercase letters, dashes and numbers!', 'upt' ) )
				->make_required( '/^[a-z0-9\-]+$/' ),
			UF_Field::factory( 'text', 'upt_tax_name', __( 'Plural Name', 'upt' ) )
				->set_description( __( 'This is plural name of the taxonomy (e.g. Categories).', 'upt' ) ),
			UF_Field::factory( 'text', 'upt_tax_singular_name', __( 'Singular Name', 'upt' ) )
				->set_description( __( 'This is the singular name of the taxonomy (e.g. Category).', 'upt' ) ),
			UF_Field::factory( 'set', 'upt_tax_post_types', __( 'Post Types', 'upt' ) )
				->add_options( $post_types )
				->set_description( __( 'The taxonomy will be associated with those post types.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_tax_fine_tune', __( 'Fine tune labels', 'upt' ) )
				->set_default_value( false )
				->set_description( __( 'All other labels for the taxonomy are generated automatically by using the &quot;Name&quot; &amp; &quot;Singular Name&quot; fields&apos; values. If you want to change a detail in those labels, check this.', 'upt' ) ),
			UF_Field::factory( 'text', 'upt_tax_add_new_item', __( 'Add New Item', 'upt' ) )
				->set_description( __( 'The adding label that will appear in other places of the admin/front end. (e.g. Add New Page).', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),
			UF_Field::factory( 'text', 'upt_tax_edit_item', __( 'Edit Item', 'upt' ) )
				->set_description( __( 'The Edit Item label (e.g. Edit Page).', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),
			UF_Field::factory( 'text', 'upt_tax_search_items', __( 'Search Items', 'upt' ) )
				->set_description( __( 'The Search Items label (e.g. Search Pages).', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),
			UF_Field::factory( 'text', 'upt_tax_not_found', __( 'Not Found', 'upt' ) )
				->set_description( __( 'The Not Found label (e.g. No Pages found).', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),
			UF_Field::factory( 'text', 'upt_tax_parent_item_colon', __( 'Parent Item Colon', 'upt' ) )
				->set_description( __( 'The Parent Item Colon label (e.g. Parent Page).', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),
			UF_Field::factory( 'text', 'upt_tax_popular_items', __( 'Popular Items', 'upt' ) )
				->set_description( __( 'Popular Writers', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),			
			UF_Field::factory( 'text', 'upt_tax_all_items', __( 'All Items', 'upt' ) )
				->set_description( __( 'All Writers', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),			
			UF_Field::factory( 'text', 'upt_tax_new_item_name', __( 'New Item Name', 'upt' ) )
				->set_description( __( 'New Writer Name', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),			
			UF_Field::factory( 'text', 'upt_tax_separate_items_with_commas', __( 'Separate Items With Commas', 'upt' ) )
				->set_description( __( 'Separate writers with commas', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),			
			UF_Field::factory( 'text', 'upt_tax_add_or_remove_items', __( 'Add Or Remove Items', 'upt' ) )
				->set_description( __( 'Add or remove writers', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),			
			UF_Field::factory( 'text', 'upt_tax_choose_from_most_used', __( 'Choose From Most Used', 'upt' ) )
				->set_description( __( 'Choose from the most used writers', 'upt' ) )
				->set_dependency( 'upt_tax_fine_tune' ),			
		), 'dashicons dashicons-list-view', __( 'Slug &amp; Labels', 'upt' ) );

		$box->tab( 'general', array(
			UF_Field::factory( 'checkbox', 'upt_tax_hierarchical', __( 'Hierarchical', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Allows Parent to be specified. Also, non-hierarchical taxonomies work like tags, meaning that on post type screens the user has to manually enter terms manually.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_tax_public', __( 'Public', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Controls how the type is visible to authors and readers.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_tax_show_ui', __( 'Show UI', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Whether to generate a default UI for managing this taxonomy in the admin.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_tax_show_in_nav_menus', __( 'Show In Nav Menus', 'upt' ) )
				->set_default_value( true )
				->set_dependency( 'upt_tax_public' )
				->set_description( __( 'Whether the taxonomy is available for selection in navigation menus.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_tax_show_admin_column', __( 'Show admin column', 'upt' ) )
				->set_default_value( true )
				->set_dependency( 'upt_tax_public' )
				->set_description( __( 'Show the taxonomy terms in the post type listing.', 'upt' ) ),
		), 'dashicons dashicons-admin-generic', __( 'General Settings', 'upt' ) );

		
		$box->tab( 'urls', array(
			UF_Field::factory( 'checkbox', 'upt_tax_rewrite_enable', __( 'Enable URL rewrite', 'upt' ) ),

			UF_Field::factory( 'text', 'upt_tax_rewrite_slug', __( 'Slug', 'upt' ) )
				->set_description( __( 'Customize the permalink structure slug, ex. <strong>genre</strong>/', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_tax_rewrite_with_front', __( 'With Front', 'upt' ) )
				->set_description( __( 'Include the blog base for the URLs of this taxonomy.', 'upt' ) )
				->set_default_value( false )
		), 'dashicons dashicons-admin-site', __( 'URLs', 'upt' ) );

		# Add the fields tab
		$fields = array( uf_get_available_fields()->set_custom_template( 'field-no-label' ) );

		if( ! class_exists( 'UF_Terms_Meta' ) ) {
			$html = <<<HTML
<h2>This functionality is only available for Ultimate Fields Premium users.</h2>
<p>To add fields to a taxonomy, you need to use the <strong>Terms Meta</strong> container, which is a premium feature. You can add fields below, but they wil not be active until you get the plugin.</p>
<p>Ultimate Fields Premium is available at <a href="http://ultimate-fields.com/premium/" target="_blank">http://ultimate-fields.com/premium/</a>.</p>
HTML;
			$description = sprintf(
				$html
			);

			array_unshift( $fields, UF_Field::factory( 'html', 'uf_tax_pro_separator' )->set_description( $description ) );
		}

		$box->tab( 'fields', $fields, 'dashicons dashicons-list-view', __( 'Fields', 'uf' ) );			
	}

	/**
	 * When a container is updated, it's message should not be "Post Published".
	 *
	 * @param mixed[] $messages The current group of messages.
	 * @return mixed[]
	 */
	public function change_updated_message( $messages ) {
		if( ! isset( $_GET[ 'post' ] ) )
			return $messages;

		$p = get_post( $_GET[ 'post' ] );
		if( $p->post_type != $this->slug ) {
			return $messages;
		}

		$message = __( 'Taxonomy saved.', 'upt' );
		$messages[ 'post' ][ 1 ] = $messages[ 'post' ][ 6 ] = $message;

		return $messages;
	}

}