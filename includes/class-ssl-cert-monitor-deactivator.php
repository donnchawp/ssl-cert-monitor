<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://odd.blog/ssl-certificate-monitor/
 * @since      0.0.1
 *
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/includes
 * @author     WordPress Developer
 */
class SSL_Cert_Monitor_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Clear scheduled events when the plugin is deactivated.
	 *
	 * @since    0.0.1
	 */
	public static function deactivate() {
		// Make sure we're in WordPress environment
		if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_unschedule_event' ) ) {
			// Clear the scheduled event.
			$timestamp = wp_next_scheduled( 'ssl_cert_monitor_daily_check' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'ssl_cert_monitor_daily_check' );
			}
		}
	}
} 