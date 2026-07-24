<?php
/*
Plugin Name: WP All Export - ACF Export Add-On
Plugin URI: http://www.wpallimport.com/
Description: Export ACF data from WordPress. Requires WP All Export.
Version: 1.0.1
Author: Soflyy
Requires at least: 5.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin root dir with forward slashes as directory separator regardless of actual DIRECTORY_SEPARATOR value
 * @var string
 */
define( 'PMAE_ROOT_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ) );
/**
 * Plugin root url for referencing static content
 * @var string
 */
define( 'PMAE_ROOT_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
/**
 * Plugin prefix for making names unique (be aware that this variable is used in conjuction with naming convention,
 * i.e. in order to change it one must not only modify this constant but also rename all constants, classes and functions which
 * names composed using this prefix)
 * @var string
 */
define( 'PMAE_PREFIX', 'pmae_' );

define( 'PMAE_VERSION', '1.0.1' );

define( 'PMAE_EDITION', 'free' );

/**
 * Main plugin file, Introduces MVC pattern
 *
 * @singletone
 * @author Maksym Tsypliakov <maksym.tsypliakov@gmail.com>
 */
final class PMAE_Plugin {
	/**
	 * Singletone instance
	 * @var PMAE_Plugin
	 */
	protected static $instance;

	/**
	 * Plugin root dir
	 * @var string
	 */
	const ROOT_DIR = PMAE_ROOT_DIR;
	/**
	 * Plugin root URL
	 * @var string
	 */
	const ROOT_URL = PMAE_ROOT_URL;
	/**
	 * Prefix used for names of shortcodes, action handlers, filter functions etc.
	 * @var string
	 */
	const PREFIX = PMAE_PREFIX;
	/**
	 * Plugin file path
	 * @var string
	 */
	const FILE = __FILE__;

	/**
	 * Return singletone instance
	 * @return PMAE_Plugin
	 */
	static public function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Common logic for requesting plugin info fields
	 */
	public function __call( $method, $args ) {
		if ( preg_match( '%^get(.+)%i', $method, $mtch ) ) {
			$info = get_plugin_data( self::FILE );
			if ( isset( $info[ $mtch[1] ] ) ) {
				return $info[ $mtch[1] ];
			}
		}
		throw new Exception(
			sprintf(
				'Requested method %s::%s does not exist.',
				esc_html( get_class( $this ) ),
				esc_html( $method )
			)
		);
	}

	/**
	 * Get path to plagin dir relative to wordpress root
	 *
	 * @param bool[optional] $noForwardSlash Whether path should be returned withot forwarding slash
	 *
	 * @return string
	 */
	public function getRelativePath( $noForwardSlash = false ) {
		$wp_root = str_replace( '\\', '/', ABSPATH );

		return ( $noForwardSlash ? '' : '/' ) . str_replace( $wp_root, '', self::ROOT_DIR );
	}

	/**
	 * Check whether plugin is activated as network one
	 * @return bool
	 */
	public function isNetwork() {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ plugin_basename( self::FILE ) ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Class constructor containing dispatching logic
	 *
	 * @param string $rootDir Plugin root dir
	 * @param string $pluginFilePath Plugin main file
	 */
	protected function __construct() {

		include_once 'src' . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR . 'Autoloader.php';
		$autoloader = new \Pmae\Common\Bootstrap\Autoloader( self::ROOT_DIR, self::PREFIX );
		// create/update required database tables

		// register autoloading method
		spl_autoload_register( array( $autoloader, 'autoload' ) );

		register_activation_hook( self::FILE, array( $this, 'activation' ) );

		$autoloader->init();

		// register admin page pre-dispatcher
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

	}

	/**
	 * Dispatch shorttag: create corresponding controller instance and call its index method
	 *
	 * @param array $args Shortcode tag attributes
	 * @param string $content Shortcode tag content
	 * @param string $tag Shortcode tag name which is being dispatched
	 *
	 * @return string
	 */
	public function shortcodeDispatcher( $args, $content, $tag ) {

		$controllerName = self::PREFIX . preg_replace_callback( '%(^|_).%', array(
				$this,
				"replace_callback"
			), $tag );// capitalize first letters of class name parts and add prefix
		$controller     = new $controllerName();
		if ( ! $controller instanceof PMAE_Controller ) {
			throw new Exception(
				sprintf(
					'Shortcode `%s` matches to a wrong controller type.',
					esc_html( $tag )
				)
			);
		}
		ob_start();
		$controller->index( $args, $content );

		return ob_get_clean();
	}

	public function replace_callback( $matches ) {
		return strtoupper( $matches[0] );
	}

	/**
	 * Plugin activation logic
	 */
	public function activation() {
		// Uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does.
		set_exception_handler( function ( $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( esc_html($e->getMessage()), E_USER_ERROR );
		} );
	}
}

PMAE_Plugin::getInstance();