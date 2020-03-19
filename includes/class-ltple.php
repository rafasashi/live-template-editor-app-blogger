<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_App_Blogger {

	/**
	 * The single instance of LTPLE_App_Blogger.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file='', $parent, $version = '1.0.0' ) {

		$this->parent = $parent;
	
		$this->_version = $version;
		$this->_token	= md5($file);
		
		$this->message = '';
		
		// Load plugin environment variables
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->script_suffix = '';

		register_activation_hook( $this->file, array( $this, 'install' ) );
		
		// Load frontend JS & CSS
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		$this->settings = new LTPLE_App_Blogger_Settings( $this->parent );
		
		$this->admin = new LTPLE_App_Blogger_Admin_API( $this );

		if ( !is_admin() ) {

			// Load API for generic admin functions
			
			add_action( 'wp_head', array( $this, 'get_header') );
			add_action( 'wp_footer', array( $this, 'get_footer') );
		}
		
		// Handle localisation
		
		$this->load_plugin_textdomain();
		
		// load_localisation
		
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
		//init profiler 
		
		add_action( 'init', array( $this, 'init_app' ));

		add_action( 'admin_init', array( $this, 'admin_init_app' ));		

		// Custom editor template
		
		add_filter( 'template_include', array( $this, 'app_template'), 1 );

		// list apps
		
		add_action( 'ltple_list_apps', array( $this, 'register_app_type' ), 10 );
		
	} // End __construct ()
	
	public function register_app_type(){
		
		$apps = $this->parent->apps->get_terms( 'app-type', array(
			
			'blogger' => array(
			
				'name' 		=> 'Blogger',
				'options' 	=> array(
				
					'thumbnail' => $this->assets_url . 'blogger.png',
					'types' 	=> array('networks','blogs','images'),
					'api_client'=> 'blogger',
					'parameters'=> array (
					
						'input' => array ( 'password', 'password', 'password' ),
						'key' 	=> array ( 'goo_api_project', 'goo_consumer_key', 'goo_consumer_secret' ),
						'value' => array ( '', '', ''),
					),
				),
			),
		),'DESC');
		
		// merge app
		
		foreach( $apps as $app){
			
			if( !in_array_field( $app->term_id, 'term_id', $this->parent->apps->list ) ){
				
				$this->parent->apps->list[] = $app;
			}			
		}
	}
	
	public function app_template( $template_path ){
		
		
		return $template_path;
	}
	
	public function init_app(){	
		

	}
	
	public function admin_init_app(){	
	
	
	}
	
	public function get_header(){
		
	}
	
	public function get_footer(){
		
		
	}
	
	public function get_user_profile_url($app){
		
		return 'https://' . $app->user_name . '.blogspot.com';
	}						
	
	public function get_social_icon_url($app){
		
		return $this->assets_url . 'images/social-icon.png';
	}
	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new LTPLE_Client_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new LTPLE_Client_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		//wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		//wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		//wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		//wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		
		load_plugin_textdomain( $this->settings->plugin->slug, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		
	    $domain = $this->settings->plugin->slug;

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main LTPLE_App_Blogger Instance
	 *
	 * Ensures only one instance of LTPLE_App_Blogger is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_App_Blogger()
	 * @return Main LTPLE_App_Blogger instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
