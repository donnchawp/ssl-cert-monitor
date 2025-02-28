<?php
/**
 * The core plugin class.
 *
 * @since      0.0.1
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      0.0.1
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/includes
 * @author     Donncha O Caoimh
 */
class SSL_Cert_Monitor {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      SSL_Cert_Monitor_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'SSL_CERT_MONITOR_VERSION' ) ) {
			$this->version = SSL_CERT_MONITOR_VERSION;
		} else {
			$this->version = '0.0.1';
		}
		$this->plugin_name = 'ssl-cert-monitor';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_cron_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - SSL_Cert_Monitor_Loader. Orchestrates the hooks of the plugin.
	 * - SSL_Cert_Monitor_Admin. Defines all hooks for the admin area.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ssl-cert-monitor-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ssl-cert-monitor-admin.php';

		$this->loader = new SSL_Cert_Monitor_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new SSL_Cert_Monitor_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add menu item.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add Settings link to the plugin.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

		// Save/Update our plugin options.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'options_update' );

		// Admin notices.
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_ssl_expiration_notices' );

		// AJAX for dismissing notices.
		$this->loader->add_action( 'wp_ajax_ssl_cert_dismiss_notice', $plugin_admin, 'dismiss_notice_callback' );

		// AJAX for unsubscribing from email notifications.
		$this->loader->add_action( 'wp_ajax_ssl_cert_unsubscribe', $plugin_admin, 'unsubscribe_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_ssl_cert_unsubscribe', $plugin_admin, 'unsubscribe_callback' );
	}

	/**
	 * Register all of the hooks related to the cron functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_cron_hooks() {
		$plugin_admin = new SSL_Cert_Monitor_Admin( $this->get_plugin_name(), $this->get_version() );

		// Schedule the daily check.
		$this->loader->add_action( 'ssl_cert_monitor_daily_check', $plugin_admin, 'check_ssl_certificates' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    SSL_Cert_Monitor_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
