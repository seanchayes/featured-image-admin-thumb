<?php
/**
 * Featured Image Admin Thumb.
 *
 * @package   Featured_Image_Admin_Thumb_Admin
 * @author    Sean Hayes <sean@seanhayes.biz>
 * @license   GPL-2.0+
 * @link      http://www.seanhayes.biz
 * @copyright 2014 Sean Hayes
 */

/**
 * Plugin class. This class works with the
 * administrative side of the WordPress site.
 *
 * @package Featured_Image_Admin_Thumb_Admin
 * Sean Hayes <sean@seanhayes.biz>
 */
class Featured_Image_Admin_Thumb_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */

	protected $fiat_nonce = null;
	protected $text_domain;
	protected $fiat_image_size = 'fiat_thumb';
	protected $is_woocommerce_active;
	protected $is_ninja_forms_active;
	protected $is_edd_active;
	protected $plugin_slug;
	protected $template_html;
	protected $template_html_nothickbox;
	protected $fiat_kses;
	protected $fiat_thumbnail_size = 'thumbnail';
	protected $fiat_link_action = 'thumbnail';
	protected $fiat_sizes = array();

	// Expect that we won't need thumbnails for these post types.
	protected $default_excluded_post_types = array(
		'nav_menu_item',
		'revision',
		'attachment',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
	);

	private function __construct() {
		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin              			= Featured_Image_Admin_Thumb::get_instance();
		$this->plugin_slug   			= $plugin->get_plugin_slug();
		$this->text_domain   			= $plugin->load_plugin_textdomain();

		// Get the current plugin settings.
		$this->fiat_thumbnail_size 		= get_option( 'fiat-thumb-size', 'thumbnail' );
		$this->fiat_link_action 		= get_option( 'fiat-link-action', 'thumbnail' );

		// Register our image size.
		add_image_size( $this->fiat_image_size, 60, 60, array( 'center', 'center' ) );

		// Get the list of registered image sizes
		$this->fiat_sizes = wp_get_registered_image_subsizes();

		// Make sure we have a registered size set, if not default to 'thumbnail'.
		if( ! array_key_exists( $this->fiat_thumbnail_size, $this->fiat_sizes ) ) {
			$this->fieat_thumbnail_size = 'thumbnail';
		}

		// Setup the template html strings.
		$this->template_html 			= '<a title="' . __( 'Change featured image', 'featured-image-admin-thumb-fiat' ) . '" href="%1$s" class="fiat_thickbox" data-thumbnail-id="%3$d">%2$s</a>';
		$this->template_html_nothickbox = '<a title="' . __( 'Edit post', 'featured-image-admin-thumb-fiat' ) . '" href="%1$s" data-thumbnail-id="%3$d">%2$s</a>';

		// Define our allowed kses parameters
		$this->fiat_kses     			= array(
			'a'   => array(
				'href'              => array(),
				'class'             => array(),
				'title'             => array(),
				'id'                => array(),
				'data-thumbnail-id' => array(),
			),
			'img' => array(
				'src'    => array(),
				'alt'    => array(),
				'width'  => array(),
				'height' => array(),
				'class'  => array(),
			),
			'br' => array(),
		);

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// See if some other common plugins are active.
		$this->is_woocommerce_active 	= in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
		$this->is_ninja_forms_active 	= in_array( 'ninja-forms/ninja-forms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
		$this->is_edd_active         	= in_array( 'easy-digital-downloads/easy-digital-downloads.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );

		// Don't try and add thumbnails to ninja forms pages.
		if ( $this->is_ninja_forms_active ) {
			add_filter( 'fiat/restrict_post_types', array( $this, 'restrict_post_type_filter' ) );
		}

		// Setup the main plugin actions.
		add_action( 'admin_init', array( $this, 'fiat_init_columns' ) );
		add_action( 'wp_ajax_fiat_get_thumbnail', array( $this, 'fiat_get_thumbnail' ) );
		add_action( 'pre_get_posts', array( $this, 'fiat_posts_orderby' ) );
		add_action( 'admin_menu', array($this, 'add_plugin_admin_page' ) );

	}
	/**
	 * Register admin column handlers for posts and pages, taxonomies and other custom post types
	 *
	 * Find all post_types that support thumbnails and remove those that are excluded with our filter
	 * Then add thumbnail support
	 *
	 * Fired in the 'admin_init' action
	 */
	public function fiat_init_columns() {

		$available_post_types = array_keys( array_diff( array_flip ( get_post_types_by_support('thumbnail') ), apply_filters( 'fiat/restrict_post_types', $this->default_excluded_post_types ) ) );

		if ( $this->is_edd_active && isset( $available_post_types['download'] ) ) {
			add_filter( 'edd_download_columns', array( $this, 'include_thumb_column_edd' ) );
			add_filter( 'fes_download_table_columns', array( $this, 'include_thumb_column_edd' ) );
		}

		array_map( array( $this, 'add_post_type_thumb_support' ), $available_post_types );

		// For taxonomies.
		$taxonomies = get_taxonomies( array(), 'names' );

		foreach ( $taxonomies as $taxonomy ) {
			add_action( "manage_{$taxonomy}_posts_custom_column", array( $this, 'fiat_custom_columns' ), 2, 2 );
			add_filter( "manage_{$taxonomy}_posts_columns", array( $this, 'fiat_add_thumb_column' ) );
		}
	}

	/**
	 * @param $post_type
	 *
	 * Add thumbnail display support for the selected post_type
	 */
	public function add_post_type_thumb_support( $post_type )
	{
		add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'fiat_custom_columns' ), 2, 2 );
		add_filter( "manage_{$post_type}_posts_columns", array( $this, 'fiat_add_thumb_column' ) );
		add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'fiat_thumb_sortable_columns' ) );
	}
	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue inline admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'fiat-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Featured_Image_Admin_Thumb::VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 */
	public function enqueue_admin_scripts() {

		// Enable the next block if the settings page returns
		/*if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}*/
        $screen = get_current_screen();
        // Check if we are on an "all posts" screen type.
        // If not then just return and do not load the JavaScript code
        if ( ! $screen || ( 'edit-' . $screen->post_type !== $screen->id ) ) {
            return;
        }
		$available_post_types = array_diff( get_post_types(), apply_filters( 'fiat/restrict_post_types', $this->default_excluded_post_types ) );
		// Add custom uploader css and js support for specific post types.
		if ( isset( $available_post_types[ $screen->post_type ] ) ) {

			// Add support for custom media uploader to be shown inside a thickbox.
			add_thickbox();
			wp_enqueue_media();
			wp_enqueue_script(
				$this->plugin_slug . '-admin-script-thumbnail',
				plugins_url( 'assets/js/admin-thumbnail.js', __FILE__ ),
				array( 'post' ),
				Featured_Image_Admin_Thumb::VERSION,
				true
			);
			wp_localize_script(
				$this->plugin_slug . '-admin-script-thumbnail',
				'fiat_thumb',
				array(
					'button_text'           => __( 'Use as thumbnail', 'featured-image-admin-thumb-fiat' ),
					'change_featured_image' => __( 'Change featured image', 'featured-image-admin-thumb-fiat' ),
				)
			);

			$this->enqueue_admin_styles();
		}
	}

	public function add_plugin_admin_page() {
		add_options_page( __( 'Featured Image Admin Thumb', 'featured-image-admin-thumb-fiat' ), __( 'Featured Image Admin Thumb', 'featured-image-admin-thumb-fiat' ), 'manage_options', 'fiat', array( $this, 'display_plugin_admin_page' ) );
	}


	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once 'views/admin.php';
	}

	/**
	 * Remove known post_types
	 *
	 * @param array $post_types post_types to filter.
	 *
	 * Remove post types that conflict with known plugins.
	 * @return array
	 */
	public function restrict_post_type_filter( $post_types ) {
		$post_types[] = 'nf_sub'; // Ninja Forms Submissions post type.
		return $post_types;
	}

	/**
	 * Append thumbnail to EDD columns unless filtered out.
	 *
	 * @param array $download_columns Columns in EDD Downloads table.
	 *
	 * @since 1.5.1
	 * @return array
	 */
	public function include_thumb_column_edd( $download_columns ) {
		return array_merge(
			$download_columns,
			array(
				'thumb' => __( 'Featured Image', 'featured-image-admin-thumb-fiat' ),
			)
		);
	}
	/**
	* @return array|bool
	* @since 1.0.0
	*
	* @uses fiat_thumb
	*
	* Function to process an image attachment id via AJAX and return to caller
	* This is used to populate the "Featured Image" column with an image html snippet of the selected thumbnail
	*
	*/
	public function fiat_get_thumbnail() {

		// Get the post id we are to attach the image to
		if ( isset( $_POST['post_id'] ) && ! empty( $_POST['post_id'] ) ) {
			$post_ID = intval( $_POST['post_id'] );
			if ( ! current_user_can( 'edit_post', $post_ID ) ) {
				wp_die( -1 );
			} else {
				// Check we know who's calling us before proceeding
				check_ajax_referer( 'set_post_thumbnail-' . $post_ID, $this->fiat_nonce );

				// Get thumbnail ID so we can then get html src to use for thumbnail
				if ( isset( $_POST['thumbnail_id'] ) && ! empty( $_POST['thumbnail_id'] ) ) {
					$thumbnail_id = intval( $_POST['thumbnail_id'] );
					$thumb_url    = get_image_tag( $thumbnail_id, '', '', '', $this->fiat_thumbnail_size );

					// Create the html based on what the thumbnail click action is.
					if ( $this->fiat_link_action === 'thumbnail' ) {
						$html = sprintf(
							$this->template_html,
							admin_url( 'media-upload.php?post_id=' . $post_ID . '&amp;type=image&amp;TB_iframe=1&_wpnonce=' . $this->fiat_nonce ),
							$thumb_url,
							esc_attr( $thumbnail_id )
						);
					} else {
						$html = sprintf(
							$this->template_html_nothickbox,
							admin_url( 'post.php?post=' . $post_ID . '&amp;action=edit&_wpnonce=' . $this->fiat_nonce ),
							$thumb_url,
							esc_attr( $thumbnail_id )
						);

						$set_featured_image = __( 'Set featured image', 'featured-image-admin-thumb-fiat' );
						$set_edit_markup    = $this->fiat_on_woocommerce_products_list() ? '' : $set_featured_image;

						// Add in an extra break so the link appears under it instead of beside it.
						$html .= '<br>';

						$html .= sprintf(
							$this->template_html,
							admin_url( 'media-upload.php?post_id=' . $post_id . '&amp;type=image&amp;TB_iframe=1&_wpnonce=' . $this->fiat_nonce ),
							$set_edit_markup,
							$post_id
						);
					}

					echo wp_kses( $html, $this->fiat_kses );
				}
			}
		}
		die();
	}

	/**
	* @param $column
	* @param $post_id
	* @return void
	* @since 1.0.0
	* @uses fiat_thumb, thumb
	*
	* Insert representative thumbnail image into Admin Dashboard view
	* for All Posts/Pages if we are on the "thumb" column
	*
	*/
	public function fiat_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'thumb':
				$post_has_thumbnail = has_post_thumbnail( $post_id );

				if ( $post_has_thumbnail ) {
					// Determine if our image size has been created and use
					// that size/attribute combination
					// else get the post-thumbnail image and apply custom sizing to
					// size it to fit in the admin dashboard
					$sizes        = array();
					$thumbnail_id = get_post_thumbnail_id( $post_id );
					$tpm          = wp_get_attachment_metadata( $thumbnail_id );

					if ( false !== $tpm && ! empty( $tpm ) ) {
						$sizes = $tpm['sizes'];
					}

					// Attempt to generate the image sub-sizes if we don't have any or the thumbnail size doesn't exist.
					if ( empty( $sizes ) || !array_key_exists( $this->fiat_thumbnail_size, $sizes ) ) {
						wp_update_image_subsizes( $thumbnail_id, );
					}

					/**
					 * Check if WooCommerce is active
					 * If so, only return anchor link markup to inline edit thumbnail
					 * WooCommerce will supply the image markup
					 **/
					if ( $this->fiat_on_woocommerce_products_list() ) {
						$thumb_url = '';
					} else {
						$thumb_url = wp_get_attachment_image( $thumbnail_id, $this->fiat_thumbnail_size, false );
					}
					// Here it is!
					$this->fiat_nonce = wp_create_nonce( 'set_post_thumbnail-' . $post_id );

					// Create the html based on what the thumbnail click action is.
					if ( $this->fiat_link_action === 'thumbnail' ) {
						$html = sprintf(
							$this->template_html,
							admin_url( 'media-upload.php?post_id=' . $post_id . '&amp;type=image&amp;TB_iframe=1&_wpnonce=' . $this->fiat_nonce ),
							$thumb_url,
							$thumbnail_id
						);
					} else {
						$html = sprintf(
							$this->template_html_nothickbox,
							admin_url( 'post.php?post=' . $post_id . '&amp;action=edit&_wpnonce=' . $this->fiat_nonce ),
							$thumb_url,
							$thumbnail_id
						);
					}

					// Click me to change!
					echo wp_kses( $html, $this->fiat_kses );
				}

				if ( ! $post_has_thumbnail || $this->fiat_link_action === 'postedit' ) {
					$this->fiat_nonce   = wp_create_nonce( 'set_post_thumbnail-' . $post_id );
					$set_featured_image = __( 'Set featured image', 'featured-image-admin-thumb-fiat' );
					$set_edit_markup    = $this->fiat_on_woocommerce_products_list() ? '' : $set_featured_image;

					$html = sprintf(
						$this->template_html,
						admin_url( 'media-upload.php?post_id=' . $post_id . '&amp;type=image&amp;TB_iframe=1&_wpnonce=' . $this->fiat_nonce ),
						$set_edit_markup,
						$post_id
					);

					// Add in an extra break to if there is a thumbnail so the link appears under it instead of beside it.
					if( $post_has_thumbnail ) { echo '<br>'; }

					// Click me!
					echo wp_kses( $html, $this->fiat_kses );
				}
				break;
		}
	}

	/**
	 * @return bool
	 *
	 * Is WooCommerce installed and activated and we are showing the product post type?
	 *
	 * Logic from here: https://docs.woothemes.com/document/create-a-plugin/
	 */
	public function fiat_on_woocommerce_products_list() {
		global $post;
		return null !== $post && 'product' === $post->post_type && $this->is_woocommerce_active;
	}
	/**
	* @param $columns
	* @return array
	* @since 1.0.0
	*
	* Add our custom column to all posts/pages/custom post types view
	*
	*/
	public function fiat_add_thumb_column( $columns ) {
		/**
		 * Check if WooCommerce is active
		 * If so WooCommerce supplies the title for the column and then we bail
		 **/
		if ( $this->fiat_on_woocommerce_products_list() ) {
			return $columns;
		} else {
			return array_merge(
				$columns,
				array(
					'thumb' => __( 'Featured Image', 'featured-image-admin-thumb-fiat' ),
				)
			);
		}
	}

	/**
	 * Check query is main query within admin and if so check
	 * to see if it's on the thumb column and determine the query
	 * modification to make.
	 *
	 * @param \WP_Query $query
	 */
	public function fiat_posts_orderby( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() && ! $this->fiat_on_woocommerce_products_list() ) {
			return;
		}

		if ( 'thumb' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_thumbnail_id' );
			$query->set( 'meta_type', 'NUMERIC' );
			$query->set( 'post_status', 'any' );
			'desc' === $query->get( 'order' ) ? $query->set( 'meta_compare', 'NOT EXISTS' ) : $query->set( 'meta_compare', 'EXISTS' );
		}
	}

	/**
	 * Add the Featured Image column to the available sortable columns
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function fiat_thumb_sortable_columns( $columns ) {
		$columns['thumb'] = 'thumb';
		return $columns;
	}
}
