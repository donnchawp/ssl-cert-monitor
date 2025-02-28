<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://odd.blog/ssl-certificate-monitor/
 * @since      0.0.1
 *
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/includes
 * @author     Donncha O Caoimh
 */
class SSL_Cert_Monitor_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Sets up the options and schedules the daily check.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {
		// Initialize options if they don't exist.
		if ( ! get_option( 'ssl_cert_monitor_domains' ) ) {
			add_option( 'ssl_cert_monitor_domains', array() );
		}

		if ( ! get_option( 'ssl_cert_monitor_dismissed_notices' ) ) {
			add_option( 'ssl_cert_monitor_dismissed_notices', array() );
		}

		if ( ! get_option( 'ssl_cert_monitor_unsubscribed' ) ) {
			add_option( 'ssl_cert_monitor_unsubscribed', false );
		}

		// Schedule the daily check event if not already scheduled.
		if ( ! wp_next_scheduled( 'ssl_cert_monitor_daily_check' ) ) {
			wp_schedule_event( time(), 'daily', 'ssl_cert_monitor_daily_check' );
		}
	}
} 
