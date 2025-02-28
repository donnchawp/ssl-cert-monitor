<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://odd.blog/ssl-certificate-monitor/
 * @since      0.0.1
 *
 * @package    SSL_Cert_Monitor
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all options created by the plugin
delete_option( 'ssl_cert_monitor_domains' );
delete_option( 'ssl_cert_monitor_dismissed_notices' );
delete_option( 'ssl_cert_monitor_notices' );
delete_option( 'ssl_cert_monitor_unsubscribed' );
delete_option( 'ssl_cert_monitor_expiration_dates' );
