<?php
/**
 * Handles the post type that handles post types.
 *
 * @package Ultimate Post Types
 * @since 0.1
 */
class UPT_Post_Type extends UPT_Editable_Base {
	/**
	 * Holds the name of the post type to allow easy swapping later.
	 * 
	 * @type string
	 * @since 0.1
	 */
	protected $slug = 'ultimate-post-type';

	function __construct() {
		parent::__construct();
		add_action( 'admin_menu', array( $this, 'add_export_page' ) );
	}

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
			'show_in_menu'        => true,
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'menu_position'       => 91,
			'supports'            => array( 'title' ),

			'labels' => array(
				'name'                => __( 'Post Types', 'upt' ),
				'singular_name'       => __( 'Post Type', 'upt' ),
				'add_new'             => __( 'Add Post Type', 'upt' ),
				'add_new_item'        => __( 'Add Post Type', 'upt' ),
				'edit_item'           => __( 'Edit Post Type', 'upt' ),
				'new_item'            => __( 'New Post Type', 'upt' ),
				'view_item'           => __( 'View Post Type', 'upt' ),
				'search_items'        => __( 'Search Post Types', 'upt' ),
				'not_found'           => __( 'No Post Types found', 'upt' ),
				'not_found_in_trash'  => __( 'No Post Types found in Trash', 'upt' ),
				'parent_item_colon'   => __( 'Parent Post Type:', 'upt' ),
				'menu_name'           => __( 'Post Types', 'upt' ),
			)
		);

		register_post_type( $this->slug, $args );

		# Immediately after this post type is registered, register the custom ones
		$this->register_post_types();
	}

	/**
	 * Registers all user-created post types
	 */
	public function register_post_types() {
		$post_types = get_posts(array(
			'post_type'      => $this->slug,
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		));

		foreach( $post_types as $post_type ) {
			new UPT_Custom_Post_Type( $post_type );
		}
	}

	/**
	 * Display save/delete buttons in the space for tabs when editing a container.
	 * 
	 * @param UF_Container The container whose tabs are being displayed.
	 */
	public function add_save_button( $container ) {
		if( $container->get_id() != 'post-type-settings' ) {
			return;
		}

		echo '<div class="submitbox">';
			submit_button( __( 'Save' ), 'primary', 'publish', false, array( 'accesskey' => 'p' ) );
		echo '</div>';
	}

	/**
	 * Adds a page for exporting.
	 */	
	function add_export_page() {
		$parent = "edit.php?post_type=$this->slug";
		$title = __( 'Export', 'upt' );
		add_submenu_page( $parent, $title , $title , 'manage_options', 'upt-export', array( $this, 'export_page' ) );
	}

	/**
	 * Displays the export page with variable content based on the needed functionality.
	 */
	public function export_page() {
		if( isset( $_GET[ 'export_id' ] ) && $post = get_post( $_GET[ 'export_id' ] ) ) {
			if( $post->post_type == 'ultimate-post-type' ) {
				extract( $this->get_export_code( $post ) );
				include( UPT_DIR . '/assets/templates/export-post-type.php' ); exit;
			} elseif( $post->post_type == 'upt-taxonomy' ) {
				extract( $this->get_taxonomy_export_code( $post ) );
				include( UPT_DIR . '/assets/templates/export-taxonomy.php' ); exit;
			}
		}

		include( UPT_DIR . '/assets/templates/export-default.php' );
	}

	/**
	 * Prepares a post type's export code.
	 *
	 * @param WP_Post $post The post that is being exported.
	 */
	protected function get_export_code( $post )  {
		$pt   = new UPT_Custom_Post_Type( $post, false );
		list( $slug, $args, $vars ) = $pt->get_args();

		# Convert to an exportable string
		$out = var_export( $args, true );

		# Convert spaces to tabs
		$out = preg_replace( '~  ~', "\t", $out );
		$out = preg_replace( '~\n~', "\n\t", $out );
		$out = esc_html( $out );

		/**
		 * Prepare custom fields if any
		 */
		if( $raw = get_post_meta( $post->ID, 'fields', true ) ) {
			$raw = apply_filters( 'uf_exportable_post_type', $raw );

			# Convert to an exportable string
			$fields = var_export( $raw, true );

			# Convert spaces to tabs
			$fields = preg_replace( '~  ~', "\t", $fields );
			$fields = preg_replace( '~\n~', "\n\t", $fields );
			$fields = esc_html( $fields );
			$fields = preg_replace( "~\n~", "\n\t", $fields );
		} else {
			$fields = '';
		}

		return apply_filters( 'upt_post_type_export_page_vars', array(
			'slug'           => $slug,
			'out'            => $out,
			'title'          => apply_filters( 'the_title', $post->post_title ),
			'out'            => $out,
			'fields'         => $fields,
			'template'       => $vars[ 'template_type' ],
			'before_content' => $vars[ 'before_content' ],
			'after_content'  => $vars[ 'after_content' ]
		) );
	}

	/**
	 * Prepares a taxonomy's export code.
	 *
	 * @param WP_Post $post The post that is being exported.
	 */
	protected function get_taxonomy_export_code( $post )  {
		$pt   = new UPT_Custom_Taxonomy( $post, false );
		list( $slug, $args, $vars ) = $pt->get_args();

		# Convert to an exportable string
		$out = var_export( $args, true );

		# Convert spaces to tabs
		$out = preg_replace( '~  ~', "\t", $out );
		$out = preg_replace( '~\n~', "\n\t", $out );
		$out = esc_html( $out );

		/**
		 * Prepare custom fields if any
		 */
		if( $raw = get_post_meta( $post->ID, 'fields', true ) ) {
			$raw = apply_filters( 'uf_exportable_post_type', $raw );

			# Convert to an exportable string
			$fields = var_export( $raw, true );

			# Convert spaces to tabs
			$fields = preg_replace( '~  ~', "\t", $fields );
			$fields = preg_replace( '~\n~', "\n\t", $fields );
			$fields = esc_html( $fields );
			$fields = preg_replace( "~\n~", "\n\t", $fields );
		} else {
			$fields = '';
		}

		return apply_filters( 'upt_post_type_export_page_vars', array(
			'slug'       => $slug,
			'out'        => $out,
			'title'      => apply_filters( 'the_title', $post->post_title ),
			'out'        => $out,
			'fields'     => $fields,
			'post_types' => $vars[ 'post_types' ]
		) );
	}
}