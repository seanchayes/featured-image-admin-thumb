<?php
/**
 * Featured Image Admin Thumb.
 *
 * @package   Featured_Image_Admin_Thumb_Admin
 * @author    Sean Hayes <sean@seanhayes.biz>
 * @license   GPL-2.0+
 * @link      http://www.seanhayes.biz
 * @copyright 2013 Sean Hayes
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

    protected $fiat_image_size = 'fiat_thumb';

	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = Featured_Image_Admin_Thumb::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts',        array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts',        array( $this, 'enqueue_admin_scripts' ) );

        /* These setting for an options page are not used at present in this plugin
		// Add the options page and menu item.
		//add_action( 'admin_menu',                   array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		//$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		//add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
        */

        add_image_size( $this->fiat_image_size , 60  );

        add_filter( 'manage_posts_columns' ,        array( $this, 'fiat_add_thumb_column' ) );
        add_action( 'manage_posts_custom_column' ,  array( $this, 'fiat_custom_columns') , 10, 2 );
        add_filter( 'manage_pages_columns' ,        array( $this, 'fiat_add_thumb_column') );
        add_action( 'manage_pages_custom_column' ,  array( $this, 'fiat_custom_columns') , 10, 2 );

        // For taxonomies:

        $taxonomies=get_taxonomies('','names');

        foreach ($taxonomies as $taxonomy ) {
                add_action( "manage_{$taxonomy}_posts_custom_column" ,  array( $this, 'fiat_custom_columns'), 10, 2 );
                add_filter( "manage_{$taxonomy}_posts_columns" ,        array( $this, 'fiat_add_thumb_column') );
            }
        add_action( 'wp_ajax_fiat_get_thumbnail',   array( $this, 'fiat_get_thumbnail') );


    }

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Featured_Image_Admin_Thumb::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

        // Enable the next block if the settings page retures
		/*if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}*/

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_script(
                $this->plugin_slug . '-admin-script',
                plugins_url( 'assets/js/admin.js', __FILE__ ),
                array( 'jquery' ),
                Featured_Image_Admin_Thumb::VERSION );
		}

        $current_post_type = get_post_type();
        // Add custom uploader css and js support for all post types.
        if ( "edit-{$current_post_type}" == $screen->id  ) {

            // Add support for custom media uploader to be shown inside a thickbox
            add_thickbox();
            wp_enqueue_media();
            wp_enqueue_script(
                $this->plugin_slug . '-admin-script-thumbnail',
                plugins_url( 'assets/js/admin-thumbnail.js', __FILE__ ),
                array( 'post' ), Featured_Image_Admin_Thumb::VERSION, true );

        }
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Featured Image Admin Thumb', $this->plugin_slug ),
			__( 'FIAT', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

    /**
     * @return array|bool
     * @since 1.0.0
     *
     * @uses fiat_thumb
     *
     * Function to process an image attachment id via AJAX and return to caller
     * This is used to populate the "Thumb" column with an image html snippet of the selected thumbnail
     *
     */
    public function fiat_get_thumbnail() {

        // Get the post id we are to attach the image to
        $post_ID = intval( $_POST['post_id'] );
        if ( ! current_user_can( 'edit_post', $post_ID ) )
            wp_die( -1 );

        // Check who's calling us before proceeding
        check_ajax_referer( 'set_post_thumbnail-' . $post_ID, $this->fiat_nonce );

        // Get thumbnail ID so we can then get html src to use for thumbnail
        $thumbnail_id = intval( $_POST['thumbnail_id'] );
        $thumb_url = wp_get_attachment_image( $thumbnail_id, array( 60,60 ) );
        echo $thumb_url;

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
                if ( has_post_thumbnail( $post_id) ) {
                    // Determine if our image size has been created and use
                    // that size/attribute combination
                    // else get the post-thumbnail image and apply custom sizing to
                    // size it to fit in the admin dashboard
                    $thumbnail_id = get_post_thumbnail_id( $post_id );
                    $tpm = wp_get_attachment_metadata( $thumbnail_id );
                    $sizes = $tpm['sizes'];

                    // Default to thumbnail size (as this will be sized down reducing the bandwidth until the image thumbnail is regenerated)
                    $fiat_image_size = 'thumbnail';

                    // Review the sizes this particular image has been set to

                    foreach ($sizes as $s => $k) {
                        if ( $this->fiat_image_size == $s ) {

                            // our size is present, set it and break out
                            $fiat_image_size = $this->fiat_image_size;
                            break;
                        }
                    }

                    if ( 'thumbnail' == $fiat_image_size ) {
                        // size down this time
                        $thumb_url = wp_get_attachment_image( $thumbnail_id, array( 60,60 ) );
                    } else {
                        // use native sized image
                        $thumb_url = get_image_tag( $thumbnail_id, '', '', '', $fiat_image_size );
                    }
                    // Here it is!
                    print_r($thumb_url);
                } else {

                    // This nonce "action" parameter must match the Ajax Referrer action used in the js and PHP
                    // wp-admin/includes/ajax-actions.php wp-includes/pluggable.php
                    // It's like dealing with the IRS. :-)

                    $this->fiat_nonce = wp_create_nonce( 'set_post_thumbnail-' . $post_id );
                    $template_html = '<a title="Set featured image" href="%s" id="set-post-thumbnail" class="fiat_thickbox" >Set <br/>featured image</a>';
                    $html = sprintf( $template_html,
                        home_url() . '/wp-admin/media-upload.php?post_id=' . $post_id .'&amp;type=image&amp;TB_iframe=1&_wpnonce=' . $this->fiat_nonce
                    );
                    // Click me!
                    echo $html;
                }
                break;
        }
    }

    /**
     * @param $columns
     * @return array
     * @since 1.0.0
     *
     * Add our custom column to all posts/pages view
     *
     */
    public function fiat_add_thumb_column($columns) {
        return array_merge(
            $columns,
            array(
                'thumb' => __('Thumb'),
            )
        );
    }

}
