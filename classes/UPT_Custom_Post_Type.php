<?php
/**
 * This class registers and handles all custom post types
 * that are generated through the plugin.
 * 
 * @package Ultimate Post Types
 * @since 0.1.
 */
class UPT_Custom_Post_Type {
	/**
	 * Holds all variables that are assigned through custom fields.
	 */
	protected $vars = array();

	/**
	 * Holds register_post_type's arguments before it is called.
	 */
	protected $args = array(
		'capability_type' => 'post',
		'query_var'       => true,		
		'supports'        => array()
	);

	/**
	 * Constructs the post type.
	 * 
	 * @param object $post_type_post The post as it's saved.
	 */
	function __construct( $post_type_post, $register = true ) {
		$this->post = $post_type_post;

		# Retrieve all data for the post type
		$this->retrieve_data( $post_type_post );

		# If for some reason the slug is missing, don't continue
		if( $register ) {
			if( ! isset( $this->vars[ 'slug' ] ) || ! $this->vars[ 'slug' ] ) {
				return;
			}

			# Verity that the post type can be registered
			if( ! $this->verify_availability( $this->vars[ 'slug' ] ) ) {
				return;
			}
		}

		# Retrieve all vars and register the post type
		$this->collect_general_vars();
		$this->add_rewrites();
		$this->generate_labels();

		# Delay registering to init
		if( $register ) {
			$this->setup_containers();
			$this->register();

			# Add a filter for the template
			add_filter( 'template_include', array( $this, 'template_include' ) );
			add_filter( 'the_content', array( $this, 'add_fields_to_content' ), 2 );			
		}
	}

	/**
	 * Verifies if a post type's name is not reserved
	 * or already in use.
	 * 
	 * @param string $post_type The name of the post type.
	 * @return boolean.
	 */
	protected function verify_availability( $post_type ) {
		# Check reserved pot types
		$reserved = array(
			'post',
			'page',
			'attachment',
			'revision',
			'nav_menu_item',
			'action',
			'order',
			'theme'
		);

		if( in_array( $post_type, $reserved ) ) {
			return false;
		}

		# Check post types that are already registered.
		$existing = get_post_types();
		if( in_array( $post_type, $existing ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves all data that is associated with the post type
	 * through Ultimate Fields.
	 * 
	 * @since 0.1
	 */
	protected function retrieve_data( $post_obj ) {
		$this->vars[ 'menu_name' ] = apply_filters( 'the_title', $post_obj->post_title );

		foreach( get_post_meta( $post_obj->ID, null ) as $key => $row ) {
			if( strpos( $key, 'upt_pt_' ) === 0 ) {
				$this->vars[ str_replace( 'upt_pt_', '', $key ) ] = maybe_unserialize( $row[ 0 ] );
			}
		}
	}

	/**
	 * Generates all labels based on settings.
	 */
	protected function generate_labels() {
		$plural   = $this->vars[ 'name' ];
		$singular = $this->vars[ 'singular_name' ];

		# Generate the default labels
		$labels = array(
			'name'                => sprintf( __( '%s', 'upt' ),                   $plural ),
			'singular_name'       => sprintf( __( '%s', 'upt' ),                   $singular ),
			'add_new'             => sprintf( __( 'Add %s', 'upt' ),               $singular ),
			'add_new_item'        => sprintf( __( 'Add %s', 'upt' ),               $singular ),
			'edit_item'           => sprintf( __( 'Edit %s', 'upt' ),              $singular ),
			'new_item'            => sprintf( __( 'New %s', 'upt' ),               $singular ),
			'view_item'           => sprintf( __( 'View %s', 'upt' ),              $singular ),
			'search_items'        => sprintf( __( 'Search %s', 'upt' ),            $plural ),
			'not_found'           => sprintf( __( 'No %s found', 'upt' ),          $plural ),
			'not_found_in_trash'  => sprintf( __( 'No %s found in Trash', 'upt' ), $plural ),
			'parent_item_colon'   => sprintf( __( 'Parent %s:', 'upt' ),           $singular ),
		);
		
		# Add the main label
		$labels[ 'menu_name' ] = $this->vars[ 'menu_name' ];

		# Add fine tuned labels eventually
		if( $this->vars[ 'fine_tune' ] ) {
			foreach( $labels as $key => $label ) {
				if( $this->vars[ $key ] ) {
					$labels[ $key ] = $this->vars[ $key ];
				}
			}
		}

		$this->args[ 'labels' ] = array_merge( $labels );
	}

	/**
	 * Collects basic vars.
	 */
	protected function collect_general_vars() {
		$this->args[ 'hierarchical' ]        = (bool) $this->vars[ 'hierarchical' ];
		$this->args[ 'public' ]              = (bool) $this->vars[ 'public' ];
		$this->args[ 'show_in_menu' ]        = (bool) $this->vars[ 'show_in_menu' ];
		$this->args[ 'show_ui' ]             = (bool) $this->vars[ 'show_ui' ];
		$this->args[ 'show_in_admin_bar' ]   = (bool) $this->vars[ 'show_in_admin_bar' ];
		$this->args[ 'show_in_nav_menus' ]   = (bool) $this->vars[ 'show_in_nav_menus' ];
		$this->args[ 'publicly_queryable' ]  = (bool) $this->vars[ 'publicly_queryable' ];
		$this->args[ 'exclude_from_search' ] = (bool) $this->vars[ 'exclude_from_search' ];
		$this->args[ 'has_archive' ]         = (bool) $this->vars[ 'has_archive' ];
		$this->args[ 'can_export' ]          = (bool) $this->vars[ 'can_export' ];

		$possible_supports = array(
			'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks',
			'custom_fields', 'comments', 'revisions', 'page_attributes', 'post_formats'
		);
		foreach( $possible_supports as $s ) {
			if( $this->vars[ 'supports_' . $s ] ) {
				$this->args[ 'supports' ][] = str_replace( '_', '-', $s );
			}
		}
	}

	/**
	 * Prepares rewrite data.
	 */
	protected function add_rewrites() {
		if( $this->vars[ 'rewrite_enable' ] ) {
			$this->args[ 'rewrite' ] = array(
				'with_front' => $this->vars[ 'rewrite_with_front' ],
				'feeds'      => $this->vars[ 'rewrite_feeds' ],
				'pages'      => $this->vars[ 'rewrite_pages' ]
			);

			if( $this->vars[ 'rewrite_slug' ] ) {
				$this->args[ 'rewrite' ][ 'slug' ] = $this->vars[ 'rewrite_slug' ];
			}
		}
	}

	/**
	 * When all args are prepared registers the post type
	 */
	protected function register() {
		register_post_type( $this->vars[ 'slug' ], $this->args );

		if( $this->vars[ 'supports_post_formats' ] ) {
			add_post_type_support( 'post-formats', $this->vars[ 'formats' ] );
		}
	}

	/**
	 * Adds containers for Ultimate Fields to set up if necessary.
	 */
	public function setup_containers() {
		global $wpdb;

		$container = $this->post;

		$meta = array();
		$raw_meta = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$container->ID" );
		foreach( $raw_meta as $m ) {
			$meta[ $m->meta_key ] = maybe_unserialize( $m->meta_value );
		}

		$meta[ 'uf_title' ]             = sprintf( __( '%s Settings', 'upt' ), $this->vars[ 'singular_name' ] );
		$meta[ 'uf_type' ]              = 'post-meta';
		$meta[ 'uf_postmeta_posttype' ] = array( $this->vars[ 'slug' ] );

		uf_setup_containers( array(
			'post' => $container,
			'meta' => $meta
		));
	}	

	/**
	 * For the current post type, sets the appropriate post template.
	 */
	public function template_include( $template ) {
		if( ! is_singular( $this->vars[ 'slug' ] ) ) {
			return $template;
		}

		$type = $this->vars[ 'template_type' ];
		if( 'single' == $type ) {
			$template = array( 'single-' . $this->vars[ 'slug' ] . '.php', 'single.php' );
		} elseif( 'page' == $type ) {
			$template = 'page.php';
		} else {
			$template = $type;
		}

		return locate_template( $template, false, false );
	}

	/**
	 * On singulars of the current post type, adds before/after content
	 * to create the appropriate template.
	 */
	public function add_fields_to_content( $content ) {
		if( is_singular( $this->vars[ 'slug' ] ) ) {
			if( $this->vars[ 'before_content' ] ) {
				$content = $this->vars[ 'before_content' ] . "\n" . $content;
			}

			if( $this->vars[ 'after_content' ] ) {
				$content .= "\n" . $this->vars[ 'after_content' ];
			}
		}

		return $content;
	}

	/**
	 * Returns all arguments for the PT
	 */
	public function get_args() {
		return array( $this->vars[ 'slug' ], $this->args, $this->vars );
	}
}