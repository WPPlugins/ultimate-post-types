<?php
/**
 * This class registers and handles all custom taxonomies
 * that are generated through the plugin.
 * 
 * @package Ultimate Post Types
 * @since 0.1.
 */
class UPT_Custom_Taxonomy {
	/**
	 * Holds all variables that are assigned through custom fields.
	 */
	protected $vars = array();

	/**
	 * Holds register_taxonmy's arguments before it is called.
	 */
	protected $args = array(
		'labels' => array()
	);

	/**
	 * Constructs the taxonomy.
	 * 
	 * @param object $taxonomy_post The post as it's saved.
	 */
	function __construct( $taxonomy_post, $register = true ) {
		$this->post = $taxonomy_post;

		# Retrieve all data for the taxonomy
		$this->retrieve_data( $taxonomy_post );

		# Verity that the taxonomy can be registered
		if( $register ) {
			if( ! $this->verify_availability( $this->vars[ 'slug' ] ) ) {
				return;
			}
		}

		# Retrieve all vars and register the post type
		$this->generate_labels();
		$this->collect_general_vars();
		$this->add_rewrites();

		if( $register ) {
			$this->setup_containers();

			# Delay registering to init
			$this->register();			
		}
	}

	/**
	 * Retrieves all data that is associated with the taxonomy through Ultimate Fields.
	 * 
	 * @since 0.1
	 */
	protected function retrieve_data( $post_obj ) {
		$this->vars[ 'menu_name' ] = apply_filters( 'the_title', $post_obj->post_title );

		foreach( get_post_meta( $post_obj->ID, null ) as $key => $row ) {
			if( strpos( $key, 'upt_tax_' ) === 0 ) {
				$this->vars[ str_replace( 'upt_tax_', '', $key ) ] = maybe_unserialize( $row[ 0 ] );
			}
		}
	}

	/**
	 * Verifies if a post type's name is not reserved
	 * or already in use.
	 * 
	 * @param string $taxonomy The slug of the taxonomy.
	 * @return boolean.
	 */
	protected function verify_availability( $taxonomy ) {
		# Check post types that are already registered.
		$existing = get_taxonomies();
		if( in_array( $taxonomy, $existing ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generates all labels based on settings.
	 */
	protected function generate_labels() {
		$plural   = $this->vars[ 'name' ];
		$singular = $this->vars[ 'singular_name' ];

		# Generate the default labels
		$labels = array(
			'name'                       => $plural,
			'singular_name'              => $singular,
			'popular_items'              => sprintf( __( 'Popular %s',                   'upt' ), $plural   ),
			'all_items'                  => sprintf( __( 'All %s',                       'upt' ), $plural   ),
			'update_item'                => sprintf( __( 'Update %s',                    'upt' ), $singular ),
			'search_items'               => sprintf( __( 'Search %s',                    'upt' ), $plural   ),
			'edit_item'                  => sprintf( __( 'Edit %s',                      'upt' ), $singular ),
			'add_new_item'               => sprintf( __( 'Add New %s',                   'upt' ), $singular ),
			'new_item_name'              => sprintf( __( 'New %s Name',                  'upt' ), $singular ),
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas',      'upt' ), $plural   ),
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s',             'upt' ), $plural   ),
			'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'upt' ), $plural   ),
			'not_found'                  => sprintf( __( 'No %s found.',                 'upt' ), $plural   )
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

		$this->args[ 'labels' ] = array_merge( $this->args[ 'labels' ], $labels );
	}

	/**
	 * Collects basic vars.
	 */
	protected function collect_general_vars() {
		$this->args[ 'hierarchical' ]      = (bool) $this->vars[ 'hierarchical' ];
		$this->args[ 'public' ]            = (bool) $this->vars[ 'public' ];
		$this->args[ 'show_ui' ]           = (bool) $this->vars[ 'show_ui' ];
		$this->args[ 'show_in_nav_menus' ] = (bool) $this->vars[ 'show_in_nav_menus' ];
		$this->args[ 'show_admin_column' ] = (bool) $this->vars[ 'show_admin_column' ];
	}


	/**
	 * Prepares rewrite data.
	 */
	protected function add_rewrites() {
		if( $this->vars[ 'rewrite_enable' ] ) {
			$this->args[ 'rewrite' ] = array(
				'with_front' => $this->vars[ 'rewrite_with_front' ],
			);

			if( $this->vars[ 'rewrite_slug' ] ) {
				$this->args[ 'rewrite' ][ 'slug' ] = $this->vars[ 'rewrite_slug' ];
			}
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
		$meta[ 'uf_type' ]              = 'term-meta';
		$meta[ 'uf_termsmeta_taxonomies' ] = array( $this->vars[ 'slug' ] );
		$meta[ 'uf_options_page_slug' ] = $this->vars[ 'slug' ] . '-settings';

		uf_setup_containers( array(
			'post' => $container,
			'meta' => $meta
		));
	}

	/**
	 * When all args are prepared registers the post type
	 */
	protected function register() {
		register_taxonomy( $this->vars[ 'slug' ], $this->vars[ 'post_types' ], $this->args );
	}

	/**
	 * Returns all arguments for the PT
	 */
	public function get_args() {
		return array( $this->vars[ 'slug' ], $this->args, $this->vars );
	}
}