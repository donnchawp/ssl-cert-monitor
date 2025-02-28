<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://odd.blog/ssl-certificate-monitor/
 * @since      0.0.1
 *
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/admin
 * @author     Donncha O Caoimh
 */
class SSL_Cert_Monitor_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Array to store expiration dates for domains
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      array    $expiration_dates    Array of expiration dates for domains.
	 */
	private $expiration_dates;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param    string $plugin_name       The name of this plugin.
	 * @param    string $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ssl-cert-monitor-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ssl-cert-monitor-admin.js', array( 'jquery' ), $this->version, false );

		// Add the ajax url and nonce for our script
		wp_localize_script(
			$this->plugin_name,
			'ssl_cert_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ssl_cert_nonce' ),
			)
		);
	}

	/**
	 * Add options page
	 */
	public function add_plugin_admin_menu() {
		add_options_page(
			__( 'SSL Certificate Monitor Settings', 'ssl-cert-monitor' ),
			__( 'SSL Cert Monitor', 'ssl-cert-monitor' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_setup_page' )
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    0.0.1
	 * @param    array $links    Plugin action links.
	 * @return   array    Plugin action links.
	 */
	public function add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', 'ssl-cert-monitor' ) . '</a>',
		);
		return array_merge( $settings_link, $links );
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.0.1
	 */
	public function display_plugin_setup_page() {
		// Check if we need to refresh the expiration dates
		$this->expiration_dates = get_option( 'ssl_cert_monitor_expiration_dates', array() );

		// If we don't have any expiration dates, try to get them
		if ( empty( $this->expiration_dates ) ) {
			$domains = get_option( 'ssl_cert_monitor_domains', array() );
			if ( ! empty( $domains ) ) {
				$this->check_ssl_certificates();
				// Reload the expiration dates after checking
				$this->expiration_dates = get_option( 'ssl_cert_monitor_expiration_dates', array() );
			}
		}

		// Include the settings page view
		include_once 'partials/ssl-cert-monitor-admin-display.php';
	}

	/**
	 * Validate and update options
	 *
	 * @since    0.0.1
	 */
	public function options_update() {
		register_setting(
			$this->plugin_name,
			'ssl_cert_monitor_domains',
			array( $this, 'validate_domains' )
		);
	}

	/**
	 * Validate domains
	 *
	 * @since    0.0.1
	 * @param    array $input    Array of domain names.
	 * @return   array    Validated array of domain names.
	 */
	public function validate_domains( $input ) {
		$domains = array();

		if ( isset( $input['domains'] ) && ! empty( $input['domains'] ) ) {
			$domain_list = explode( "\n", $input['domains'] );

			foreach ( $domain_list as $domain ) {
				$domain = trim( $domain );
				if ( ! empty( $domain ) ) {
					// Basic domain validation.
					if ( preg_match( '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain ) ) {
						$domains[] = sanitize_text_field( $domain );
					}
				}
			}
		}

		return $domains;
	}

	/**
	 * Check SSL certificates for all saved domains
	 *
	 * @since    0.0.1
	 */
	public function check_ssl_certificates() {
		$domains           = get_option( 'ssl_cert_monitor_domains', array() );
		$dismissed_notices = get_option( 'ssl_cert_monitor_dismissed_notices', array() );
		$unsubscribed      = get_option( 'ssl_cert_monitor_unsubscribed', false );
		$admin_email       = get_option( 'admin_email' );
		$expiration_dates  = array();

		foreach ( $domains as $domain ) {
			$expiration_date = $this->get_ssl_expiration_date( $domain );

			if ( $expiration_date ) {
				$days_until_expiration = $this->calculate_days_until_expiration( $expiration_date );

				// Store the expiration date information for admin display
				$expiration_dates[$domain] = array(
					'date'           => $expiration_date,
					'days_remaining' => $days_until_expiration,
				);

				// If certificate expires within 3 weeks, show admin notice.
				if ( $days_until_expiration <= 21 ) {
					// Check if notice was dismissed less than a week ago.
					if (
							! isset( $dismissed_notices[ $domain ] ) ||
							( time() - $dismissed_notices[ $domain ] ) > ( 7 * DAY_IN_SECONDS )
						) {
						// Add to notices to be displayed.
						update_option(
							'ssl_cert_monitor_notices',
							array(
								$domain => array(
									'expiration_date' => $expiration_date,
									'days_remaining'  => $days_until_expiration,
								),
							),
							false
						);
					}

					// If certificate expires within 1 week, send email notification.
					if ( $days_until_expiration <= 7 ) {
						// Only send if not unsubscribed or it's the final day.
						if ( ! $unsubscribed || $days_until_expiration <= 1 ) {
							$this->send_expiration_email( $domain, $expiration_date, $days_until_expiration, $admin_email );
						}
					}
				}
			} else {
				// If we couldn't get the expiration date, store that information
				$expiration_dates[$domain] = array(
					'date'           => __( 'Unable to retrieve', 'ssl-cert-monitor' ),
					'days_remaining' => false,
				);
			}
		}

		// Update the expiration dates option for admin display
		update_option( 'ssl_cert_monitor_expiration_dates', $expiration_dates, false );

		// Also update the class property for immediate use
		$this->expiration_dates = $expiration_dates;
	}

	/**
	 * Get SSL certificate expiration date for a domain
	 *
	 * @since    0.0.1
	 * @param    string $domain    Domain name.
	 * @return   string|bool       Expiration date in Y-m-d format or false on failure.
	 */
	private function get_ssl_expiration_date( $domain ) {
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$result = @stream_socket_client( "ssl://{$domain}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context );

		if ( ! $result ) {
			return false;
		}

		$params = stream_context_get_params( $result );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return false;
		}

		$cert = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );

		if ( ! $cert || ! isset( $cert['validTo_time_t'] ) ) {
			return false;
		}

		return gmdate( 'Y-m-d', $cert['validTo_time_t'] );
	}

	/**
	 * Calculate days until expiration
	 *
	 * @since    0.0.1
	 * @param    string $expiration_date    Expiration date in Y-m-d format.
	 * @return   int    Number of days until expiration.
	 */
	private function calculate_days_until_expiration( $expiration_date ) {
		$current_date = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$expiry_date  = new DateTime( $expiration_date, new DateTimeZone( 'UTC' ) );
		$interval     = $current_date->diff( $expiry_date );

		return $interval->days;
	}

	/**
	 * Send email notification about expiring certificate
	 *
	 * @since    0.0.1
	 * @param    string $domain                  The domain with expiring certificate.
	 * @param    string $expiration_date         The expiration date.
	 * @param    int    $days_until_expiration   Days until expiration.
	 * @param    string $admin_email             The admin email to send to.
	 */
	private function send_expiration_email( $domain, $expiration_date, $days_until_expiration, $admin_email ) {
		// Create a unique token for unsubscribe link
		$unsubscribe_token = wp_create_nonce( 'ssl_cert_unsubscribe' );

		// Subject
		$subject = sprintf(
			// translators: %1$s is the blog name, %2$s is the domain name
			__( '[%1$s] SSL Certificate Expiration Warning: %2$s', 'ssl-cert-monitor' ),
			get_bloginfo( 'name' ),
			$domain
		);

		// Message
		$message = sprintf(
			// translators: %1$s is the domain name, %2$d is the number of days until expiration, %3$s is the expiration date
			__( 'The SSL certificate for %1$s will expire in %2$d days (on %3$s).', 'ssl-cert-monitor' ),
			$domain,
			$days_until_expiration,
			$expiration_date
		);

		$message .= "\n\n";
		$message .= __( 'Please renew your SSL certificate before it expires to avoid security warnings for your website visitors.', 'ssl-cert-monitor' );

		// Only add unsubscribe link if it's not the final reminder
		if ( $days_until_expiration > 1 ) {
			// Create unsubscribe link
			$unsubscribe_url = add_query_arg(
				array(
					'action' => 'ssl_cert_unsubscribe',
					'token'  => $unsubscribe_token,
				),
				admin_url( 'admin-ajax.php' )
			);

			$message .= "\n\n";
			$message .= sprintf(
				// translators: %s is the unsubscribe URL
				__( 'To unsubscribe from these notifications until the final reminder, click here: %s', 'ssl-cert-monitor' ),
				$unsubscribe_url
			);
		}

		// Send the email
		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Display admin notices for expiring certificates
	 */
	public function display_ssl_expiration_notices() {
		// Get the expiration dates
		$expiration_dates = get_option( 'ssl_cert_monitor_expiration_dates', array() );

		// Get dismissed notices
		$dismissed_notices = get_option( 'ssl_cert_monitor_dismissed_notices', array() );

		// Check each domain
		foreach ( $expiration_dates as $domain => $expiration_info ) {
			// Skip if no days_remaining or if more than 21 days remaining
			if ( ! isset( $expiration_info['days_remaining'] ) || $expiration_info['days_remaining'] > 21 ) {
				continue;
			}

			// Skip if this notice has been dismissed
			if ( isset( $dismissed_notices[ $domain ] ) && $dismissed_notices[ $domain ] >= $expiration_info['days_remaining'] ) {
				continue;
			}

			// Display the notice
			?>
			<div class="notice notice-error is-dismissible ssl-cert-expiration-notice" data-domain="<?php echo esc_attr( $domain ); ?>" data-days="<?php echo esc_attr( $expiration_info['days_remaining'] ); ?>">
				<p>
					<strong><?php esc_html_e( 'SSL Certificate Expiration Warning:', 'ssl-cert-monitor' ); ?></strong>
					<?php
					echo ' ' . sprintf(
						// translators: %1$s is the domain name, %2$d is the number of days until expiration, %3$s is the expiration date
						esc_html__( 'The SSL certificate for %1$s will expire in %2$d days (on %3$s).', 'ssl-cert-monitor' ),
						'<strong>' . esc_html( $domain ) . '</strong>',
						esc_html( $expiration_info['days_remaining'] ),
						esc_html( $expiration_info['date'] )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * AJAX callback for unsubscribing from email notifications
	 */
	public function unsubscribe_callback() {
		// Verify the nonce
		if ( ! isset( $_GET['token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['token'] ) ), 'ssl_cert_unsubscribe' ) ) {
			wp_die( esc_html__( 'Invalid unsubscribe link.', 'ssl-cert-monitor' ) );
		}

		// Set the unsubscribed option
		update_option( 'ssl_cert_monitor_unsubscribed', true );

		// Show a confirmation message
		wp_die(
			'<h1>' . esc_html__( 'Unsubscribed Successfully', 'ssl-cert-monitor' ) . '</h1>' .
			'<p>' . esc_html__( 'You have been unsubscribed from SSL certificate expiration notifications. You will still receive a final reminder 1 day before any certificate expires.', 'ssl-cert-monitor' ) . '</p>' .
			'<p><a href="' . esc_url( admin_url() ) . '">' . esc_html__( 'Back to Dashboard', 'ssl-cert-monitor' ) . '</a></p>',
			esc_html__( 'Unsubscribed', 'ssl-cert-monitor' ),
			array( 'response' => 200 )
		);
	}
}
