<?php
/*
 * Plugin Name: Live Template Editor App Blogger
 * Version: 1.0.2.4
 * Plugin URI: https://github.com/rafasashi
 * Description: Blogger API integrator for Live Template Editor.
 * Author: Rafasashi
 * Author URI: https://github.com/rafasashi
 * Requires at least: 4.6
 * Tested up to: 4.7
 *
 * Text Domain: ltple
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Rafasashi
 * @since 1.0.0
 */
	
	/**
	* Add documentation link
	*
	*/
	
	if ( ! defined( 'ABSPATH' ) ) exit;

	/**
	 * Returns the main instance of LTPLE_App_Blogger to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object LTPLE_App_Blogger
	 */
	function LTPLE_App_Blogger ( $version = '1.0.0' ) {
		
		if ( ! class_exists( 'LTPLE_Client' ) ) return;
	
		$instance = LTPLE_Client::instance( __FILE__, $version );
		
		if ( empty( $instance->App_Blogger ) ) {
			
			$instance->App_Blogger = new stdClass();
			
			$instance->App_Blogger = LTPLE_App_Blogger::instance( __FILE__, $instance, $version );
		}

		return $instance;
	}	
	
	add_filter( 'plugins_loaded', function(){

		// Load plugin functions
		require_once( 'includes/functions.php' );	
		
		// Load plugin class files

		require_once( 'includes/class-ltple.php' );
		require_once( 'includes/class-ltple-settings.php' );

		// Autoload plugin libraries
		
		$lib = glob( __DIR__ . '/includes/lib/class-ltple-*.php');
		
		foreach($lib as $file){
			
			require_once( $file );
		}

		LTPLE_App_Blogger('1.1.0');	
	});