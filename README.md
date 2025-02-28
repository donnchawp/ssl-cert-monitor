# SSL Certificate Monitor

A WordPress plugin that monitors SSL certificate expiration dates for specified domains and sends notifications when they're about to expire.

## Description

This plugin allows you to monitor SSL certificates for any number of domains. It will:

- Show admin notices when certificates are within 3 weeks of expiring
- Send daily email notifications when certificates are within 1 week of expiring
- Send a final reminder email 1 day before expiration (even if unsubscribed)

## Features

- Easy-to-use settings page to add domains for monitoring
- Dismissable admin notices (will reappear after 1 week)
- Email notifications with unsubscribe functionality
- Daily automatic checks of SSL certificate expiration dates

## Installation

1. Upload the `ssl-cert-monitor` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > SSL Cert Monitor to configure the domains you want to monitor

## Usage

1. Navigate to Settings > SSL Cert Monitor
2. Enter the domains you want to monitor (one per line)
3. Save your settings

The plugin will automatically check the SSL certificates for the specified domains once per day. If any certificates are nearing expiration, you'll receive notifications according to the schedule described above.

## Requirements

- WordPress 5.2 or higher
- PHP 7.2 or higher
- OpenSSL PHP extension

## Frequently Asked Questions

### How does the plugin check SSL certificates?

The plugin uses PHP's OpenSSL functions to connect to each domain and retrieve the SSL certificate information.

### Can I customize the notification schedule?

The notification schedule is currently fixed (3 weeks for admin notices, 1 week for emails). Future versions may include customization options.

### Will I receive notifications for subdomains?

You need to add each subdomain separately that you want to monitor.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by WordPress Developer 