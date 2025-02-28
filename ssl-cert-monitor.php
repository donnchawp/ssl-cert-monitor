<?php
/**
 * SSL Certificate Monitor
 *
 * @package           SSL_Cert_Monitor
 * @author            Donncha O Caoimh
 * @copyright         Automattic
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       SSL Certificate Monitor
 * Plugin URI:        https://odd.blog/ssl-certificate-monitor/
 * Description:       Monitors SSL certificate expiration dates for specified domains and sends notifications when they're about to expire.
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Donncha O Caoimh
 * Author URI:        https://odd.blog
 * Text Domain:       ssl-cert-monitor
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'SSL_CERT_MONITOR_VERSION', '0.0.1' );
define( 'SSL_CERT_MONITOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SSL_CERT_MONITOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_ssl_cert_monitor() {
	require_once SSL_CERT_MONITOR_PLUGIN_DIR . 'includes/class-ssl-cert-monitor-activator.php';
	SSL_Cert_Monitor_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_ssl_cert_monitor() {
	require_once SSL_CERT_MONITOR_PLUGIN_DIR . 'includes/class-ssl-cert-monitor-deactivator.php';
	SSL_Cert_Monitor_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ssl_cert_monitor' );
register_deactivation_hook( __FILE__, 'deactivate_ssl_cert_monitor' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require SSL_CERT_MONITOR_PLUGIN_DIR . 'includes/class-ssl-cert-monitor.php';

/**
 * Begins execution of the plugin.
 *
 * @since    0.0.1
 */
( new SSL_Cert_Monitor() )->run();