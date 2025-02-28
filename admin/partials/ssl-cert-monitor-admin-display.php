<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://odd.blog/ssl-certificate-monitor/
 * @since      0.0.1
 *
 * @package    SSL_Cert_Monitor
 * @subpackage SSL_Cert_Monitor/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form method="post" action="options.php">
		<?php
		// Get option values.
		$domains     = get_option( 'ssl_cert_monitor_domains', array() );
		$domain_list = implode( "\n", $domains );

		settings_fields( $this->plugin_name );
		do_settings_sections( $this->plugin_name );
		?>

		<div class="ssl-cert-settings-container">
			<h3><?php esc_html_e( 'Domain Settings', 'ssl-cert-monitor' ); ?></h3>
			<p><?php esc_html_e( 'Enter the domain names you want to monitor for SSL certificate expiration. Enter one domain per line.', 'ssl-cert-monitor' ); ?></p>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="ssl_cert_domains"><?php esc_html_e( 'Domains to Monitor', 'ssl-cert-monitor' ); ?></label>
					</th>
					<td>
						<textarea name="ssl_cert_monitor_domains[domains]" id="ssl_cert_domains" class="large-text code" rows="10"><?php echo esc_textarea( $domain_list ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Example: example.com', 'ssl-cert-monitor' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php if ( ! empty( $domains ) ) : ?>
				<h3><?php esc_html_e( 'Current SSL Certificate Status', 'ssl-cert-monitor' ); ?></h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Domain', 'ssl-cert-monitor' ); ?></th>
							<th><?php esc_html_e( 'Expiration Date', 'ssl-cert-monitor' ); ?></th>
							<th><?php esc_html_e( 'Days Remaining', 'ssl-cert-monitor' ); ?></th>
							<th><?php esc_html_e( 'Status', 'ssl-cert-monitor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $domains as $domain ) : 
							$expiration_info = isset( $this->expiration_dates[ $domain ] ) ? $this->expiration_dates[ $domain ] : null;
							$status_class    = '';
							$status_text     = '';
							
							if ( $expiration_info ) {
								if ( $expiration_info['days_remaining'] === false ) {
									$status_class = 'ssl-status-unknown';
									$status_text  = __( 'Unknown', 'ssl-cert-monitor' );
								} elseif ( $expiration_info['days_remaining'] <= 7 ) {
									$status_class = 'ssl-status-critical';
									$status_text  = __( 'Critical', 'ssl-cert-monitor' );
								} elseif ( $expiration_info['days_remaining'] <= 21 ) {
									$status_class = 'ssl-status-warning';
									$status_text  = __( 'Warning', 'ssl-cert-monitor' );
								} else {
									$status_class = 'ssl-status-valid';
									$status_text  = __( 'Valid', 'ssl-cert-monitor' );
								}
							} else {
								$status_class = 'ssl-status-unknown';
								$status_text  = __( 'Checking...', 'ssl-cert-monitor' );
							}
						?>
							<tr>
								<td><?php echo esc_html( $domain ); ?></td>
								<td><?php echo esc_html( $expiration_info ? $expiration_info['date'] : __( 'Checking...', 'ssl-cert-monitor' ) ); ?></td>
								<td>
									<?php 
									if ( $expiration_info && $expiration_info['days_remaining'] !== false ) {
										echo esc_html( $expiration_info['days_remaining'] );
									} else {
										echo '—';
									}
									?>
								</td>
								<td><span class="ssl-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_text ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="description">
					<?php esc_html_e( 'Status refreshes when you save settings or when the daily check runs.', 'ssl-cert-monitor' ); ?>
				</p>
			<?php endif; ?>

			<h3><?php esc_html_e( 'Notification Settings', 'ssl-cert-monitor' ); ?></h3>
			<p>
				<?php esc_html_e( 'The plugin will automatically:', 'ssl-cert-monitor' ); ?>
				<ul>
					<li><?php esc_html_e( 'Show an admin notice when a certificate is within 3 weeks of expiring', 'ssl-cert-monitor' ); ?></li>
					<li><?php esc_html_e( 'Send a daily email when a certificate is within 1 week of expiring', 'ssl-cert-monitor' ); ?></li>
					<li><?php esc_html_e( 'Send a final reminder email 1 day before expiration (even if unsubscribed)', 'ssl-cert-monitor' ); ?></li>
				</ul>
			</p>

			<p>
				<?php esc_html_e( 'Emails will be sent to the WordPress admin email:', 'ssl-cert-monitor' ); ?>
				<strong><?php echo esc_html( get_option( 'admin_email' ) ); ?></strong>
			</p>
		</div>

		<?php submit_button( __( 'Save Settings', 'ssl-cert-monitor' ), 'primary', 'submit', true ); ?>
	</form>
</div>