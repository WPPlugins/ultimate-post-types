<?php
/**
 * Handles the post type that handles both post types and taxonomies.
 *
 * @package Ultimate Post Types
 * @since 0.1
 */
class UPT_Editable_Base {
	/**
	 * Holds the name of the post type to allow easy swapping later.
	 * 
	 * @type string
	 * @since 0.1
	 */
	protected $slug = 'ultimate-post-type';

	function __construct() {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'admin_head', array( $this, 'admin_icon_style' ) );
		add_action( 'uf_setup_settings', array( $this, 'setup_fields' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'init', array( $this, 'flush_rewrites' ) );
		add_action( 'save_post', array( $this, 'post_saved' ) );
		add_filter( 'post_updated_messages', array( $this, 'change_updated_message' ) );
		add_action( 'uf_after_tabs', array( $this, 'add_save_button' ) );
		add_action( 'admin_menu', array( $this, 'hide_submitdiv' ) );
		add_filter( 'get_user_option_screen_layout_' . $this->slug, array( $this, 'edit_screen_cols' ) );
		add_filter( 'page_row_actions', array( $this, 'change_quick_actions' ), 10, 2 );
	}

	/**
	 * When Ultimate Fields is setting up it's fields,
	 * this method will be called.
	 * 
	 * @since 0.1
	 */
	public function setup_fields() {
		if( class_exists( 'UF_Postmeta_B' ) ) {
			$box = new UF_Postmeta_B( __( 'Post Type Settings', 'upt' ), $this->slug );
		} else {
			$box = new UF_Postmeta( __( 'Post Type Settings', 'upt' ), $this->slug );
		}

		$templates = array(
			'single' => __( 'Use the post template.', 'upt' ),
			'page' => __( 'Use the default page template.', 'upt' )
		);

		foreach( wp_get_theme()->get_page_templates() as $template => $name ) {
			$templates[ $template ] = sprintf( __( 'Use the &quot;%s&quot; page template.', 'upt' ), $name );
		}

		$box->tab( 'main', array(
			UF_Field::factory( 'text', 'upt_pt_slug', __( 'Slug', 'upt' ) )
				->set_description( __( 'This slug will be used when quierying posts from the post type or in URLs by default. It must be unique and not among the reserved <a href="http://codex.wordpress.org/Function_Reference/register_post_type#Reserved_Post_Types">post types</a>. Please use only lowercase letters, dashes and numbers!', 'upt' ) )
				->make_required( '/^[a-z0-9\-]+$/' ),
			UF_Field::factory( 'text', 'upt_pt_name', __( 'Plural Name', 'upt' ) )
				->set_description( __( 'This is plural name of the post type (e.g. Pages).', 'upt' ) ),
			UF_Field::factory( 'text', 'upt_pt_singular_name', __( 'Singular Name', 'upt' ) )
				->set_description( __( 'This is the singular name of the post type (e.g. Page).', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_fine_tune', __( 'Fine tune', 'upt' ) )
				->set_default_value( false )
				->set_description( __( 'All other labels for the post type are generated automatically by using the &quot;Name&quot; &amp; &quot;Singular Name&quot; fields&apos; values. If you want to change a detail in those labels, check this.', 'upt' ) ),

			UF_Field::factory( 'text', 'upt_pt_add_new', __( 'Add New', 'upt' ) )
				->set_description( __( 'The label for adding in the post type&apos;s section (e.g. Add Page).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_add_new_item', __( 'Add New Item', 'upt' ) )
				->set_description( __( 'The adding label that will appear in other places of the admin/front end. (e.g. Add New Page).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_edit_item', __( 'Edit Item', 'upt' ) )
				->set_description( __( 'The Edit Item label (e.g. Edit Page).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_new_item', __( 'New Item', 'upt' ) )
				->set_description( __( 'The New Item label (e.g. New Page).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_view_item', __( 'View Item', 'upt' ) )
				->set_description( __( 'The View Item label (e.g. View Page).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_search_items', __( 'Search Items', 'upt' ) )
				->set_description( __( 'The Search Items label (e.g. Search Pages).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_not_found', __( 'Not Found', 'upt' ) )
				->set_description( __( 'The Not Found label (e.g. No Pages found).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_not_found_in_trash', __( 'Not Found In Trash', 'upt' ) )
				->set_description( __( 'The Not Found In Trash label (e.g. No Pages found in Trash).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' ),
			UF_Field::factory( 'text', 'upt_pt_parent_item_colon', __( 'Parent Item Colon', 'upt' ) )
				->set_description( __( 'The Parent Item Colon label (e.g. Parent Page).', 'upt' ) )
				->set_dependency( 'upt_pt_fine_tune' )
		), 'dashicons dashicons-list-view', __( 'Slug &amp; Labels', 'upt' ) );

		$box->tab( 'general', array(
			UF_Field::factory( 'checkbox', 'upt_pt_hierarchical', __( 'Hierarchical', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Allows Parent to be specified. In the &apos;supports&apos; tab, please check &apos;page-attributes&apos; to show the parent select box on the editor page.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_public', __( 'Public', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Controls how the type is visible to authors and readers.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_show_in_menu', __( 'Show In Menu', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Whether to show the post type in the admin menu.', 'upt' ) ),
			UF_Field::factory( 'separator', 'upt_pt_separator_advanced', __( 'Advanced Settings', 'upt' ) )
				->set_description( __( 'Please don&apos; edit those settings unless you really know what you are doing!', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_show_ui', __( 'Show UI', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Whether to generate a default UI for managing this post type in the admin.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_show_in_admin_bar', __( 'Show In Admin Bar', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Whether to make this post type available in the WordPress admin bar.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_show_in_nav_menus', __( 'Show In Nav Menus', 'upt' ) )
				->set_default_value( true )
				->set_dependency( 'upt_pt_public' )
				->set_description( __( 'Whether post_type is available for selection in navigation menus.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_publicly_queryable', __( 'Publicly Queryable', 'upt' ) )
				->set_default_value( true )
				->set_dependency( 'upt_pt_public' )
				->set_description( __( 'Whether queries can be performed on the front end.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_exclude_from_search', __( 'Exclude from Search', 'upt' ) )
				->set_default_value( true )
				->set_dependency( 'upt_pt_public' )
				->set_description( __( 'Whether to exclude posts with this post type from front end search results.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_has_archive', __( 'Has Archive', 'upt' ) )
				->set_default_value( false )
				->set_dependency( 'upt_pt_public' )
				->set_description( __( 'Enables post type archives.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_can_export', __( 'Can Export', 'upt' ) )
				->set_default_value( true )
				->set_description( __( 'Can this post type be exported.', 'upt' ) )			
		), 'dashicons dashicons-admin-generic', __( 'General Settings', 'upt' ) );

		$box->tab( 'suppots', array(
			UF_Field::factory( 'checkbox', 'upt_pt_supports_title', __( 'Title', 'upt' ) )
				->set_description( __( 'Allow the post type posts to have a title.', 'upt' ) )
				->set_default_value( true ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_editor', __( 'Editor', 'upt' ) )
				->set_description( __( 'Allow the post type posts to have content, entered through the TinyMCE (WYSIWYG) editor.' ) )
				->set_default_value( true ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_author', __( 'Author', 'upt' ) )
				->set_description( __( 'Allow administrators to choose who is the author of the current post.', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_thumbnail', __( 'Thumbnail', 'upt' ) )
				->set_description( __( 'Allow setting a featured image to the post. <strong>Please</strong> be sure that your theme supprts <strong>post-thumbnails</strong>', 'upt' ) )
				->set_default_value( false ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_excerpt', __( 'Excerpt', 'upt' ) )
				->set_description( __( 'The WordPress Excerpt is an optional summary or description of a post; in short, a post summary', 'upt' ) )
				->set_default_value( false ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_trackbacks', __( 'Trackbacks', 'upt' ) )
				->set_description( __( 'A trackback is a way of cross referencing two blog posts. ', 'upt' ) )
				->set_default_value( false ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_custom_fields', __( 'Custom Fields', 'upt' ) )
				->set_description( __( 'Allow managing custom fields by the default WordPress way. If you are planning to add fields generated by Ultimate Fields, you will prefer to leave this unchecked!', 'upt' ) )
				->set_default_value( false ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_comments', __( 'Comments', 'upt' ) )
				->set_description( ' (also will see comment count balloon on edit screen)' )
				->set_default_value( true ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_revisions', __( 'Revisions', 'upt' ) )
				->set_description( __( 'Allow the storing of revisions', 'upt' ) )
				->set_default_value( true ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_page_attributes', __( 'Page Attributes', 'upt' ) )
				->set_description( __( 'Display a box that contains the menu order field, or parent option for hierarchical post types.', 'upt' ) )
				->set_default_value( true ),
			UF_Field::factory( 'checkbox', 'upt_pt_supports_post_formats', __( 'Post Formats', 'upt' ) )
				->set_description( __( 'Allow the post type to have formats like video, photo, quote, etc. Select the formats in the next field.', 'upt' ) )
				->set_default_value( false ),
			UF_Field::factory( 'set', 'upt_pt_formats', __( 'Supported Formats', 'upt' ) )
				->add_options(array(
					'aside'   => __( 'Aside', 'upt' ),
					'audio'   => __( 'Audio', 'upt' ),
					'chat'    => __( 'Chat', 'upt' ),
					'gallery' => __( 'Gallery', 'upt' ),
					'image'   => __( 'Image', 'upt' ),
					'link'    => __( 'Link', 'upt' ),
					'quote'   => __( 'Quote', 'upt' ),
					'status'  => __( 'Status', 'upt' ),
					'video'   => __( 'Video', 'upt' )
				))
				->set_dependency( 'upt_pt_supports_post_formats' )
		), 'dashicons dashicons-plus-alt', __( 'Supports', 'upt' ) );

		$box->tab( 'urls', array(
			UF_Field::factory( 'checkbox', 'upt_pt_rewrite_enable', __( 'Enable URL rewrite', 'upt' ) ),

			UF_Field::factory( 'text', 'upt_pt_rewrite_slug', __( 'Slug', 'upt' ) )
				->set_description( __( 'Customize the permalink structure slug, ex. <strong>books</strong>/', 'upt' ) ),
			UF_Field::factory( 'checkbox', 'upt_pt_rewrite_with_front', __( 'With Front', 'upt' ) )
				->set_description( __( 'Include the blog base for the URLs of this post type.', 'upt' ) )
				->set_default_value( false ),
			UF_Field::factory( 'checkbox', 'upt_pt_rewrite_feeds', __( 'Feeds', 'upt' ) )
				->set_description( __( 'Should a feed permalink structure be built for this post type.', 'upt' ) )
				->set_default_value( true ),
			UF_Field::factory( 'checkbox', 'upt_pt_rewrite_pages', __( 'Pages', 'upt' ) )
				->set_description( __( 'Should the permalink structure provide for pagination.', 'upt' ) )
				->set_default_value( true ),
		), 'dashicons dashicons-admin-site', __( 'URLs', 'upt' ) );

		$box->tab( 'fields', array(
			uf_get_available_fields()
				->set_custom_template( 'field-no-label' )	
			), 'dashicons dashicons-list-view', __( 'Fields', 'uf' ) );

		$box->tab( 'layout', array(
			UF_Field::factory( 'radio', 'upt_pt_template_type', __( 'Template Type', 'upt' ) )
				->add_options( $templates ),
			UF_Field::factory( 'richtext', 'upt_pt_before_content', __( 'Before Content', 'upt' ) ),
			UF_Field::factory( 'richtext', 'upt_pt_after_content', __( 'After Content', 'upt' ) )
				->set_description( __( 'This content will be displayed before/after the content of the post type, in the template that is selected above. You can use shorcodes like [uf key="meta_key"] to display values that are associated with the current post type in order to create a template for it. <strong>meta_key</strong> is the key of the field as created int the <strong>Fields</strong> tab.' ) )
		), 'dashicons dashicons-align-center', __( 'Appearance', 'upt' ) );
	}

	/**
	 * Enqueues the necessary scripts for the admin.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( 'upt-admin' );
		wp_enqueue_script( 'uf-settings', UF_URL . 'settings/interface/settings.js' );
	}

	/**
	 * When such a post is saved, know that rules need to be rewrittern.
	 * 
	 * @param int $post_id
	 */
	public function post_saved( $post_id ) {
		if( get_post_type( $post_id ) == $this->slug ) {
			update_option( 'upt_flush_rewrites', true );
		}
	}

	/**
	 * Flushes rewrites rules when a post type post is being saved.
	 * 
	 * @param int $post_id
	 */
	public function flush_rewrites( $post_id ) {
		if( get_option( 'upt_flush_rewrites' ) ) {
			flush_rewrite_rules();
			delete_option( 'upt_flush_rewrites' );
		}
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

		$message = __( 'Post type saved.', 'upt' );
		$messages[ 'post' ][ 1 ] = $messages[ 'post' ][ 6 ] = $message;

		return $messages;
	}

	/**
	 * Hide the default publish box
	 */
	function hide_submitdiv() {
		# Remove the default submit div
		remove_meta_box( 'submitdiv', $this->slug, 'side' );
	}

	/**
	 * Force a single column layout on the container edit screen for post types.
	 * 
	 * @param int $cols The current number of cols, as per the current user.
	 * @return int The int 1.
	 */
	function edit_screen_cols( $cols ) {
		return 1;
	}

	/**
	 * Adds the icon for the Ultimate Post Types post type
	 * 
	 * @since 0.1
	 */
	function admin_icon_style() {
		?>
		<style type="text/css">
		#menu-posts-<?php echo $this->slug ?> a.menu-icon-post .wp-menu-image:before {
			content: "\e600";
			font-family: 'ultimate-fields' !important;
		}
		</style>
		<?php
	}	

	/**
	 * Modifies the actions for the current post type.
	 * 
	 * The quick edit should not be there, but instead an export button is needed.
	 * 
	 * @param mixed[] $actions The current actions.
	 * @return mixed[]
	 *
	 * @since 0.2
	 */
	public function change_quick_actions( $actions ) {
		global $post;

		# If the post is not an ultimatefields post or there is no edit action, don't do anything.
		if( $post->post_type != $this->slug || ! isset( $actions[ 'edit' ] ) ) {
			return $actions;
		}

		# This is the export link for that container.
		$export_link = admin_url( sprintf(
			'edit.php?post_type=ultimate-post-type&page=upt-export&export_id=%d',
			$post->ID
		 ) );
		
		# Replace the actions and add the export link.
		$actions = array(
			'edit'        => $actions[ 'edit' ],
			'export-link' => '<a href="' . esc_attr( $export_link ) . '">' . __( 'Export PHP code', 'uf' ) . '</a>',
			'trash'       => $actions[ 'trash' ]
		);

	    return $actions;
	}
}