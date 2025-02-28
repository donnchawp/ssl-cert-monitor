=== SSL Certificate Monitor ===
Contributors: donncha
Tags: ssl, security, certificate, expiration
Requires at least: 5.2
Tested up to: 6.7.1
Requires PHP: 7.2
Stable tag: 0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Monitor SSL certificate expiration dates for your domains and receive timely notifications before they expire.

== Description ==

SSL Certificate Monitor helps you avoid the embarrassment and security risks of expired SSL certificates by monitoring expiration dates and sending timely notifications.

**Key Features:**

* **Domain Monitoring** - Add any number of domains to monitor their SSL certificates
* **Admin Notices** - Receive dismissable admin notices when certificates are within 3 weeks of expiring
* **Email Notifications** - Get daily email alerts when certificates are within 1 week of expiring
* **Final Reminder** - Receive a final reminder email 1 day before expiration (even if unsubscribed)
* **Unsubscribe Option** - Ability to unsubscribe from daily notifications (except final reminder)

Never be caught off guard by an expired SSL certificate again! This plugin performs daily checks on your specified domains and provides a clear, user-friendly interface to manage your SSL certificate monitoring.

== Installation ==

1. Upload the `ssl-cert-monitor` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > SSL Cert Monitor to configure the domains you want to monitor

== Frequently Asked Questions ==

= How does the plugin check SSL certificates? =

The plugin uses PHP's OpenSSL functions to connect to each domain and retrieve the SSL certificate information.

= Can I customize the notification schedule? =

The notification schedule is currently fixed (3 weeks for admin notices, 1 week for emails). Future versions may include customization options.

= Will I receive notifications for subdomains? =

You need to add each subdomain separately that you want to monitor.

= What email address will notifications be sent to? =

Notifications are sent to the WordPress admin email address configured in your WordPress settings.

= Can I disable email notifications? =

Yes, you can unsubscribe from daily email notifications. However, you will still receive a final reminder email one day before a certificate expires.

= Does this plugin work with Let's Encrypt certificates? =

Yes, this plugin works with any SSL certificate regardless of the issuer.

== Screenshots ==

1. Settings page where you can add domains to monitor
2. Admin notice showing when a certificate is about to expire
3. Email notification example

== Changelog ==

= 0.0.1 =
* Initial release

== Upgrade Notice ==

= 0.0.1 =
Initial release of SSL Certificate Monitor.

== Usage ==

1. Navigate to Settings > SSL Cert Monitor
2. Enter the domains you want to monitor (one per line)
3. Save your settings

The plugin will automatically check the SSL certificates for the specified domains once per day. If any certificates are nearing expiration, you'll receive notifications according to the schedule described above. 