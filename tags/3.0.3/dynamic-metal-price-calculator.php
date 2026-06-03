<?php
/**
 * Plugin Name: Dynamic Metal Price Calculator
 * Plugin URI: https://tresifylab.com/case-studies/dynamic-metal-price-calculator
 * Description: Live metal pricing for WooCommerce jewellery with karat, weight, GST, markup, and shipping support. Supports variable products.
 * Version: 3.0.3
 * Author: aolo
 * Author URI: https://tresifylab.com/
 * License: GPLv2 or later
 * Text Domain: dynamic-metal-price-calculator
 * Requires at least: 5.8
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * Stable tag: 3.0.3
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );

/* ---------------------------------------------------------------------
 * Activation welcome lead popup
 * --------------------------------------------------------------------- */
register_activation_hook( __FILE__, function() {
	if ( ! get_option( 'dmmp_activation_lead_submitted' ) && ! get_option( 'dmmp_activation_lead_dismissed' ) ) {
		update_option( 'dmmp_show_activation_lead_popup', 1, false );
	}
} );

add_action( 'admin_footer', function() {
	if ( ! current_user_can( 'manage_options' ) || empty( get_option( 'dmmp_show_activation_lead_popup' ) ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && in_array( $screen->id, [ 'plugins', 'dashboard', 'toplevel_page_dmmp-settings' ], true ) === false ) {
		return;
	}

	$current_user = wp_get_current_user();
	$suggested_email = $current_user && ! empty( $current_user->user_email ) ? $current_user->user_email : get_option( 'admin_email' );
	?>
	<style>
		.dmmp-lead-overlay {
			position: fixed;
			inset: 0;
			z-index: 100000;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
			background: rgba(15, 23, 42, 0.55);
		}
		.dmmp-lead-modal {
			width: min(520px, 100%);
			overflow: hidden;
			border-radius: 16px;
			background: #fff;
			box-shadow: 0 28px 70px rgba(15, 23, 42, 0.35);
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		}
		.dmmp-lead-hero {
			padding: 28px 30px;
			color: #fff;
			background: linear-gradient(135deg, #0f766e, #2563eb);
		}
		.dmmp-lead-hero h2 {
			margin: 0 0 8px;
			color: #fff;
			font-size: 24px;
			line-height: 1.2;
		}
		.dmmp-lead-hero p {
			margin: 0;
			color: rgba(255, 255, 255, 0.88);
			font-size: 14px;
		}
		.dmmp-lead-body {
			padding: 24px 30px 28px;
		}
		.dmmp-lead-body label {
			display: block;
			margin-bottom: 8px;
			font-weight: 600;
			color: #111827;
		}
		.dmmp-lead-body input[type="email"] {
			width: 100%;
			margin: 0 0 10px;
			padding: 12px 14px;
			border: 1px solid #cbd5e1;
			border-radius: 10px;
			font-size: 15px;
		}
		.dmmp-lead-note {
			margin: 0 0 18px;
			color: #64748b;
			font-size: 13px;
			line-height: 1.45;
		}
		.dmmp-lead-actions {
			display: flex;
			gap: 10px;
			align-items: center;
			justify-content: flex-end;
		}
		.dmmp-lead-primary {
			border: 0;
			border-radius: 10px;
			padding: 11px 16px;
			color: #fff;
			background: #0f766e;
			font-weight: 700;
			cursor: pointer;
		}
		.dmmp-lead-secondary {
			border: 0;
			padding: 10px 12px;
			color: #64748b;
			background: transparent;
			cursor: pointer;
		}
		.dmmp-lead-message {
			min-height: 20px;
			margin-top: 12px;
			font-size: 13px;
		}
		.dmmp-lead-message.is-error {
			color: #b91c1c;
		}
		.dmmp-lead-message.is-success {
			color: #047857;
		}
	</style>
	<div class="dmmp-lead-overlay" id="dmmp-lead-overlay" role="dialog" aria-modal="true" aria-labelledby="dmmp-lead-title">
		<div class="dmmp-lead-modal">
			<div class="dmmp-lead-hero">
				<h2 id="dmmp-lead-title"><?php echo esc_html__( 'Welcome to Dynamic Metal Price Calculator', 'dynamic-metal-price-calculator' ); ?></h2>
				<p><?php echo esc_html__( 'Get setup help, product updates, and practical jewellery pricing tips from Tresify Lab.', 'dynamic-metal-price-calculator' ); ?></p>
			</div>
			<div class="dmmp-lead-body">
				<label for="dmmp-lead-email"><?php echo esc_html__( 'Your email address', 'dynamic-metal-price-calculator' ); ?></label>
				<input id="dmmp-lead-email" type="email" value="<?php echo esc_attr( $suggested_email ); ?>" autocomplete="email" />
				<p class="dmmp-lead-note"><?php echo esc_html__( 'By submitting, your email, site URL, and admin email will be sent to Tresify Lab so we can follow up with onboarding and support. You can skip this.', 'dynamic-metal-price-calculator' ); ?></p>
				<div class="dmmp-lead-actions">
					<button type="button" class="dmmp-lead-secondary" id="dmmp-lead-skip"><?php echo esc_html__( 'Skip', 'dynamic-metal-price-calculator' ); ?></button>
					<button type="button" class="dmmp-lead-primary" id="dmmp-lead-submit"><?php echo esc_html__( 'Send me setup help', 'dynamic-metal-price-calculator' ); ?></button>
				</div>
				<div class="dmmp-lead-message" id="dmmp-lead-message" aria-live="polite"></div>
			</div>
		</div>
	</div>
	<script>
		(function() {
			var overlay = document.getElementById('dmmp-lead-overlay');
			var email = document.getElementById('dmmp-lead-email');
			var submit = document.getElementById('dmmp-lead-submit');
			var skip = document.getElementById('dmmp-lead-skip');
			var message = document.getElementById('dmmp-lead-message');
			var nonce = '<?php echo esc_js( wp_create_nonce( 'dmmp_activation_lead' ) ); ?>';

			function setMessage(text, type) {
				message.textContent = text || '';
				message.className = 'dmmp-lead-message' + (type ? ' is-' + type : '');
			}

			function send(action) {
				var data = new window.FormData();
				data.append('action', action);
				data.append('nonce', nonce);
				data.append('email', email.value);

				submit.disabled = true;
				skip.disabled = true;
				setMessage(action === 'dmmp_submit_activation_lead' ? '<?php echo esc_js( __( 'Sending...', 'dynamic-metal-price-calculator' ) ); ?>' : '', '');

				window.fetch(ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					body: data
				}).then(function(response) {
					return response.json();
				}).then(function(response) {
					if (!response || !response.success) {
						throw new Error(response && response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Please try again.', 'dynamic-metal-price-calculator' ) ); ?>');
					}

					if (action === 'dmmp_submit_activation_lead') {
						setMessage('<?php echo esc_js( __( 'Thanks. We will send helpful setup notes soon.', 'dynamic-metal-price-calculator' ) ); ?>', 'success');
						window.setTimeout(function() {
							overlay.remove();
						}, 800);
					} else {
						overlay.remove();
					}
				}).catch(function(error) {
					setMessage(error.message, 'error');
					submit.disabled = false;
					skip.disabled = false;
				});
			}

			submit.addEventListener('click', function() {
				send('dmmp_submit_activation_lead');
			});
			skip.addEventListener('click', function() {
				send('dmmp_dismiss_activation_lead');
			});
		}());
	</script>
	<?php
} );

add_action( 'wp_ajax_dmmp_submit_activation_lead', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'You do not have permission to perform this action.', 'dynamic-metal-price-calculator' ) ], 403 );
	}

	check_ajax_referer( 'dmmp_activation_lead', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'dynamic-metal-price-calculator' ) ], 400 );
	}

	$site_url = home_url();
	$admin_email = get_option( 'admin_email' );
	$subject = sprintf( 'new downloads - email lead - %s', $email );
	$message = sprintf(
		"New Dynamic Metal Price Calculator lead\n\nEmail: %s\nSite: %s\nAdmin email: %s\nDate: %s\n",
		$email,
		$site_url,
		$admin_email,
		current_time( 'mysql' )
	);
	$headers = [ 'Reply-To: ' . $email ];

	$sent = wp_mail( 'info@tresifylab.com', $subject, $message, $headers );
	if ( ! $sent ) {
		wp_send_json_error( [ 'message' => __( 'The email could not be sent from this site. Please check the site mail configuration.', 'dynamic-metal-price-calculator' ) ], 500 );
	}

	update_option( 'dmmp_activation_lead_submitted', 1, false );
	delete_option( 'dmmp_activation_lead_dismissed' );
	delete_option( 'dmmp_show_activation_lead_popup' );

	wp_send_json_success();
} );

add_action( 'wp_ajax_dmmp_dismiss_activation_lead', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'You do not have permission to perform this action.', 'dynamic-metal-price-calculator' ) ], 403 );
	}

	check_ajax_referer( 'dmmp_activation_lead', 'nonce' );
	update_option( 'dmmp_activation_lead_dismissed', 1, false );
	delete_option( 'dmmp_show_activation_lead_popup' );

	wp_send_json_success();
} );

/* ---------------------------------------------------------------------
 * Admin menu and settings page
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', function() {
	add_menu_page(
		'Metal Prices',
		'Metal Prices',
		'manage_options',
		'dmmp-settings',
		'dmmp_render_admin_page',
		'dashicons-chart-line',
		56
	);
} );

function dmmp_admin_status_badge( $label, $type = 'optional' ) {
	return '<span class="dmmp-admin-badge dmmp-admin-badge-' . esc_attr( $type ) . '">' . esc_html( $label ) . '</span>';
}

function dmmp_admin_setting_row( $id, $label, $input_html, $help, $example = '' ) {
	?>
	<div class="dmmp-admin-field">
		<label for="<?php echo esc_attr( $id ); ?>" class="dmmp-admin-label"><?php echo esc_html( $label ); ?></label>
		<div class="dmmp-admin-control"><?php echo $input_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<p class="dmmp-admin-help"><?php echo esc_html( $help ); ?></p>
		<?php if ( $example !== '' ) : ?>
			<p class="dmmp-admin-example"><?php echo esc_html( $example ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

function dmmp_admin_section_header( $title, $description, $badge_label, $badge_type = 'optional' ) {
	?>
	<span class="dmmp-admin-summary-text">
		<span class="dmmp-admin-summary-title"><?php echo esc_html( $title ); ?></span>
		<span class="dmmp-admin-summary-description"><?php echo esc_html( $description ); ?></span>
	</span>
	<?php echo dmmp_admin_status_badge( $badge_label, $badge_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php
}

function dmmp_render_admin_page_v303() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$opts = get_option( 'dmmp_options', [] );

	if ( isset( $_POST['dmmp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dmmp_nonce'] ) ), 'dmmp_save_settings' ) ) {
		$numeric_fields = [
			'dmmp_gold_rate',
			'dmmp_silver_rate',
			'dmmp_gst',
			'dmmp_shipping',
			'dmmp_gold_22k_percent',
			'dmmp_gold_20k_percent',
			'dmmp_gold_18k_percent',
		];

		foreach ( $numeric_fields as $f ) {
			if ( isset( $_POST[ $f ] ) ) {
				$opts[ $f ] = floatval( wp_unslash( $_POST[ $f ] ) );
			}
		}

		if ( isset( $_POST['save_rates'] ) ) {
			$opts['dmmp_show_extra_charges'] = isset( $_POST['dmmp_show_extra_charges'] ) ? 1 : 0;
		}

		if ( isset( $_POST['dmmp_custom_slug'], $_POST['dmmp_custom_name'], $_POST['dmmp_custom_formula'] ) ) {
			$slug    = sanitize_title( wp_unslash( $_POST['dmmp_custom_slug'] ) );
			$name    = sanitize_text_field( wp_unslash( $_POST['dmmp_custom_name'] ) );
			$formula = sanitize_text_field( wp_unslash( $_POST['dmmp_custom_formula'] ) );

			if ( $slug && $name && $formula ) {
				if ( empty( $opts['custom_metals'] ) || ! is_array( $opts['custom_metals'] ) ) {
					$opts['custom_metals'] = [];
				}
				$opts['custom_metals'][ $slug ] = [
					'name'    => $name,
					'formula' => $formula,
				];
			}
		}

		if ( isset( $_POST['dmmp_delete_custom_metal'] ) ) {
			$del = sanitize_text_field( wp_unslash( $_POST['dmmp_delete_custom_metal'] ) );
			if ( ! empty( $opts['custom_metals'][ $del ] ) ) {
				unset( $opts['custom_metals'][ $del ] );
			}
		}

		update_option( 'dmmp_options', $opts );

		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Dynamic Metal Price Calculator: settings saved.', 'dynamic-metal-price-calculator' ) . '</p></div>';
		} );
	}

	$opts = get_option( 'dmmp_options', [] );

	$gold_rate          = isset( $opts['dmmp_gold_rate'] ) ? $opts['dmmp_gold_rate'] : '';
	$silver_rate        = isset( $opts['dmmp_silver_rate'] ) ? $opts['dmmp_silver_rate'] : '';
	$r22                = isset( $opts['dmmp_gold_22k_percent'] ) ? $opts['dmmp_gold_22k_percent'] : 93;
	$r20                = isset( $opts['dmmp_gold_20k_percent'] ) ? $opts['dmmp_gold_20k_percent'] : 85.5;
	$r18                = isset( $opts['dmmp_gold_18k_percent'] ) ? $opts['dmmp_gold_18k_percent'] : 78;
	$global_gst         = isset( $opts['dmmp_gst'] ) ? $opts['dmmp_gst'] : '';
	$global_shipping    = isset( $opts['dmmp_shipping'] ) ? $opts['dmmp_shipping'] : '';
	$show_extra_charges = ! empty( $opts['dmmp_show_extra_charges'] );
	$custom_metals      = isset( $opts['custom_metals'] ) && is_array( $opts['custom_metals'] ) ? $opts['custom_metals'] : [];

	$rates_ready  = ( floatval( $gold_rate ) > 0 || floatval( $silver_rate ) > 0 );
	$charges_used = ( floatval( $global_gst ) > 0 || floatval( $global_shipping ) > 0 );
	$wc_active    = class_exists( 'WooCommerce' );
	$wp_ready     = version_compare( get_bloginfo( 'version' ), '5.8', '>=' );
	$php_ready    = version_compare( PHP_VERSION, '7.4', '>=' );
	$hpos_ready   = class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' );

	?>
	<div class="wrap dmmp-admin">
		<style>
			.dmmp-admin {
				max-width: 1180px;
			}
			.dmmp-admin-subtitle {
				max-width: 760px;
				margin: 8px 0 22px;
				color: #50575e;
				font-size: 15px;
			}
			.dmmp-admin-card,
			.dmmp-admin-section {
				margin: 0 0 20px;
				border: 1px solid #dcdcde;
				border-radius: 8px;
				background: #fff;
				box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
			}
			.dmmp-admin-card {
				padding: 22px;
			}
			.dmmp-admin-toolbar {
				display: flex;
				flex-wrap: wrap;
				gap: 10px;
				align-items: center;
				justify-content: space-between;
				margin-bottom: 16px;
			}
			.dmmp-admin-toolbar h2 {
				margin: 0;
			}
			.dmmp-admin-progress {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
				gap: 16px;
				margin-top: 14px;
			}
			.dmmp-admin-step {
				padding: 16px;
				border: 1px solid #e2e4e7;
				border-radius: 8px;
				background: #f6f7f7;
			}
			.dmmp-admin-step h3 {
				margin: 0 0 6px;
				font-size: 14px;
			}
			.dmmp-admin-step p {
				margin: 0 0 10px;
				color: #646970;
			}
			.dmmp-admin-section summary {
				display: flex;
				gap: 12px;
				align-items: center;
				justify-content: space-between;
				padding: 18px 22px;
				cursor: pointer;
			}
			.dmmp-admin-section summary::-webkit-details-marker {
				display: none;
			}
			.dmmp-admin-summary-title {
				display: block;
				color: #1d2327;
				font-weight: 700;
			}
			.dmmp-admin-summary-description {
				display: block;
				margin-top: 3px;
				color: #646970;
				font-size: 13px;
				font-weight: 400;
			}
			.dmmp-admin-section-body {
				padding: 0 22px 22px;
				border-top: 1px solid #f0f0f1;
			}
			.dmmp-admin-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
				gap: 16px;
			}
			.dmmp-admin-field {
				padding: 16px;
				border: 1px solid #edf0f2;
				border-radius: 8px;
				background: #fbfbfc;
			}
			.dmmp-admin-label {
				display: block;
				margin-bottom: 6px;
				color: #1d2327;
				font-weight: 600;
			}
			.dmmp-admin-control input[type="number"],
			.dmmp-admin-control input[type="text"] {
				width: 100%;
				max-width: 320px;
			}
			.dmmp-admin-help,
			.dmmp-admin-example {
				max-width: 620px;
				margin: 6px 0 0;
				color: #646970;
				font-size: 13px;
				line-height: 1.4;
			}
			.dmmp-admin-example {
				display: inline-block;
				padding: 8px 10px;
				border-radius: 6px;
				color: #3c434a;
				background: #f6f7f7;
			}
			.dmmp-admin-badge {
				display: inline-flex;
				align-items: center;
				border-radius: 999px;
				padding: 4px 9px;
				font-size: 12px;
				font-weight: 700;
				white-space: nowrap;
			}
			.dmmp-admin-badge-configured {
				color: #065f46;
				background: #d1fae5;
			}
			.dmmp-admin-badge-warning {
				color: #92400e;
				background: #fef3c7;
			}
			.dmmp-admin-badge-optional {
				color: #1e40af;
				background: #dbeafe;
			}
			.dmmp-admin-badge-neutral {
				color: #374151;
				background: #e5e7eb;
			}
			.dmmp-admin-guide-list {
				margin: 0;
			}
			.dmmp-admin-guide-list li {
				margin: 0 0 10px 18px;
			}
			.dmmp-admin-table {
				margin-top: 12px;
			}
			@media (max-width: 782px) {
				.dmmp-admin-section summary {
					align-items: flex-start;
					flex-direction: column;
				}
				.dmmp-admin-toolbar {
					align-items: flex-start;
					flex-direction: column;
				}
			}
		</style>

		<h1><?php echo esc_html__( 'Dynamic Metal Price Calculator', 'dynamic-metal-price-calculator' ); ?></h1>
		<p class="dmmp-admin-subtitle"><?php echo esc_html__( 'Set metal rates and pricing options for WooCommerce jewellery products.', 'dynamic-metal-price-calculator' ); ?></p>

		<form method="post" novalidate>
			<?php wp_nonce_field( 'dmmp_save_settings', 'dmmp_nonce' ); ?>

			<div class="dmmp-admin-toolbar">
				<h2><?php echo esc_html__( 'Setup Checklist', 'dynamic-metal-price-calculator' ); ?></h2>
				<button type="submit" name="save_rates" value="1" class="button button-primary"><?php echo esc_html__( 'Save Settings', 'dynamic-metal-price-calculator' ); ?></button>
			</div>

			<div class="dmmp-admin-card">
				<p><?php echo esc_html__( 'Complete these steps to start calculating product prices.', 'dynamic-metal-price-calculator' ); ?></p>
				<div class="dmmp-admin-progress">
					<div class="dmmp-admin-step">
						<h3><?php echo esc_html__( '1. Metal Rates', 'dynamic-metal-price-calculator' ); ?></h3>
						<p><?php echo esc_html__( 'Set gold and silver prices per gram.', 'dynamic-metal-price-calculator' ); ?></p>
						<?php echo dmmp_admin_status_badge( $rates_ready ? __( 'Ready', 'dynamic-metal-price-calculator' ) : __( 'Needed', 'dynamic-metal-price-calculator' ), $rates_ready ? 'configured' : 'warning' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#dmmp-section-rates" class="button button-small dmmp-open-section"><?php echo esc_html__( 'Open', 'dynamic-metal-price-calculator' ); ?></a>
					</div>
					<div class="dmmp-admin-step">
						<h3><?php echo esc_html__( '2. Gold Karats', 'dynamic-metal-price-calculator' ); ?></h3>
						<p><?php echo esc_html__( 'Review the purity percentages used for 22K, 20K, and 18K products.', 'dynamic-metal-price-calculator' ); ?></p>
						<?php echo dmmp_admin_status_badge( __( 'Ready', 'dynamic-metal-price-calculator' ), 'configured' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#dmmp-section-karats" class="button button-small dmmp-open-section"><?php echo esc_html__( 'Open', 'dynamic-metal-price-calculator' ); ?></a>
					</div>
					<div class="dmmp-admin-step">
						<h3><?php echo esc_html__( '3. Product Fields', 'dynamic-metal-price-calculator' ); ?></h3>
						<p><?php echo esc_html__( 'Add metal type, weight, and charges to products.', 'dynamic-metal-price-calculator' ); ?></p>
						<?php echo dmmp_admin_status_badge( __( 'Active', 'dynamic-metal-price-calculator' ), 'configured' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#dmmp-section-product-fields" class="button button-small dmmp-open-section"><?php echo esc_html__( 'Open', 'dynamic-metal-price-calculator' ); ?></a>
					</div>
					<div class="dmmp-admin-step">
						<h3><?php echo esc_html__( '4. Extra Charges', 'dynamic-metal-price-calculator' ); ?></h3>
						<p><?php echo esc_html__( 'Set GST and shipping defaults.', 'dynamic-metal-price-calculator' ); ?></p>
						<?php echo dmmp_admin_status_badge( $charges_used ? __( 'Ready', 'dynamic-metal-price-calculator' ) : __( 'Optional', 'dynamic-metal-price-calculator' ), $charges_used ? 'configured' : 'optional' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#dmmp-section-extra-charges" class="button button-small dmmp-open-section"><?php echo esc_html__( 'Open', 'dynamic-metal-price-calculator' ); ?></a>
					</div>
					<div class="dmmp-admin-step">
						<h3><?php echo esc_html__( '5. Frontend Display', 'dynamic-metal-price-calculator' ); ?></h3>
						<p><?php echo esc_html__( 'Choose what customers can see.', 'dynamic-metal-price-calculator' ); ?></p>
						<?php echo dmmp_admin_status_badge( $show_extra_charges ? __( 'Active', 'dynamic-metal-price-calculator' ) : __( 'Hidden', 'dynamic-metal-price-calculator' ), $show_extra_charges ? 'configured' : 'neutral' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#dmmp-section-frontend" class="button button-small dmmp-open-section"><?php echo esc_html__( 'Open', 'dynamic-metal-price-calculator' ); ?></a>
					</div>
					<div class="dmmp-admin-step">
						<h3><?php echo esc_html__( '6. Test Pricing', 'dynamic-metal-price-calculator' ); ?></h3>
						<p><?php echo esc_html__( 'Check a simple pricing example.', 'dynamic-metal-price-calculator' ); ?></p>
						<?php echo dmmp_admin_status_badge( __( 'Guide', 'dynamic-metal-price-calculator' ), 'optional' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<a href="#dmmp-section-help" class="button button-small dmmp-open-section"><?php echo esc_html__( 'Open', 'dynamic-metal-price-calculator' ); ?></a>
					</div>
				</div>
			</div>

			<details id="dmmp-section-rates" class="dmmp-admin-section" open>
				<summary><?php dmmp_admin_section_header( __( 'Metal Rates', 'dynamic-metal-price-calculator' ), __( 'Enter today\'s metal prices per gram.', 'dynamic-metal-price-calculator' ), $rates_ready ? __( 'Ready', 'dynamic-metal-price-calculator' ) : __( 'Needed', 'dynamic-metal-price-calculator' ), $rates_ready ? 'configured' : 'warning' ); ?></summary>
				<div class="dmmp-admin-section-body">
					<div class="dmmp-admin-grid">
						<?php
						dmmp_admin_setting_row(
							'dmmp_gold_rate',
							__( 'Gold Rate / gram', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_gold_rate" name="dmmp_gold_rate" type="number" step="0.01" min="0" value="' . esc_attr( $gold_rate ) . '" />',
							__( 'Enter today\'s 24K gold price per gram.', 'dynamic-metal-price-calculator' ),
							__( 'Quick example: 7,000 per gram × 2g = 14,000 metal value.', 'dynamic-metal-price-calculator' )
						);
						dmmp_admin_setting_row(
							'dmmp_silver_rate',
							__( 'Silver Rate / gram', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_silver_rate" name="dmmp_silver_rate" type="number" step="0.01" min="0" value="' . esc_attr( $silver_rate ) . '" />',
							__( 'Enter today\'s silver price per gram.', 'dynamic-metal-price-calculator' ),
							__( 'Quick example: 100 per gram × 10g = 1,000 metal value.', 'dynamic-metal-price-calculator' )
						);
						?>
					</div>
				</div>
			</details>

			<details id="dmmp-section-karats" class="dmmp-admin-section">
				<summary><?php dmmp_admin_section_header( __( 'Gold Karats', 'dynamic-metal-price-calculator' ), __( 'Set purity percentages based on the 24K gold rate.', 'dynamic-metal-price-calculator' ), __( 'Ready', 'dynamic-metal-price-calculator' ), 'configured' ); ?></summary>
				<div class="dmmp-admin-section-body">
					<div class="dmmp-admin-grid">
						<?php
						dmmp_admin_setting_row(
							'dmmp_gold_22k_percent',
							__( '22K Percentage', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_gold_22k_percent" name="dmmp_gold_22k_percent" type="number" step="0.01" min="0" value="' . esc_attr( $r22 ) . '" />',
							__( 'Used to calculate 22K gold prices from the 24K rate.', 'dynamic-metal-price-calculator' ),
							__( '93 means 93% of the 24K rate.', 'dynamic-metal-price-calculator' )
						);
						dmmp_admin_setting_row(
							'dmmp_gold_20k_percent',
							__( '20K Percentage', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_gold_20k_percent" name="dmmp_gold_20k_percent" type="number" step="0.01" min="0" value="' . esc_attr( $r20 ) . '" />',
							__( 'Used to calculate 20K gold prices from the 24K rate.', 'dynamic-metal-price-calculator' )
						);
						dmmp_admin_setting_row(
							'dmmp_gold_18k_percent',
							__( '18K Percentage', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_gold_18k_percent" name="dmmp_gold_18k_percent" type="number" step="0.01" min="0" value="' . esc_attr( $r18 ) . '" />',
							__( 'Used to calculate 18K gold prices from the 24K rate.', 'dynamic-metal-price-calculator' )
						);
						?>
					</div>
				</div>
			</details>

			<details id="dmmp-section-extra-charges" class="dmmp-admin-section">
				<summary><?php dmmp_admin_section_header( __( 'Extra Charges', 'dynamic-metal-price-calculator' ), __( 'Set default GST and shipping values.', 'dynamic-metal-price-calculator' ), $charges_used ? __( 'Ready', 'dynamic-metal-price-calculator' ) : __( 'Optional', 'dynamic-metal-price-calculator' ), $charges_used ? 'configured' : 'optional' ); ?></summary>
				<div class="dmmp-admin-section-body">
					<div class="dmmp-admin-grid">
						<?php
						dmmp_admin_setting_row(
							'dmmp_gst',
							__( 'Global GST / Tax', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_gst" name="dmmp_gst" type="number" step="0.01" min="0" value="' . esc_attr( $global_gst ) . '" />',
							__( 'Default tax percentage added to calculated prices.', 'dynamic-metal-price-calculator' )
						);
						dmmp_admin_setting_row(
							'dmmp_shipping',
							__( 'Global Shipping', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_shipping" name="dmmp_shipping" type="number" step="0.01" min="0" value="' . esc_attr( $global_shipping ) . '" />',
							__( 'Default flat shipping amount added to calculated prices.', 'dynamic-metal-price-calculator' ),
							__( 'Products can override these values.', 'dynamic-metal-price-calculator' )
						);
						?>
					</div>
				</div>
			</details>

			<details id="dmmp-section-frontend" class="dmmp-admin-section">
				<summary><?php dmmp_admin_section_header( __( 'Frontend Display', 'dynamic-metal-price-calculator' ), __( 'Choose what pricing details customers can see.', 'dynamic-metal-price-calculator' ), $show_extra_charges ? __( 'Active', 'dynamic-metal-price-calculator' ) : __( 'Hidden', 'dynamic-metal-price-calculator' ), $show_extra_charges ? 'configured' : 'neutral' ); ?></summary>
				<div class="dmmp-admin-section-body">
					<div class="dmmp-admin-field">
						<label for="dmmp_show_extra_charges" class="dmmp-admin-label">
							<input id="dmmp_show_extra_charges" name="dmmp_show_extra_charges" type="checkbox" value="1" <?php checked( $show_extra_charges ); ?> />
							<?php echo esc_html__( 'Show Product Page Breakdown', 'dynamic-metal-price-calculator' ); ?>
						</label>
						<p class="dmmp-admin-help"><?php echo esc_html__( 'Show metal value and extra charges on the product page.', 'dynamic-metal-price-calculator' ); ?></p>
						<p class="dmmp-admin-example"><?php echo esc_html__( 'CSS class: .dmmp-extra-charges', 'dynamic-metal-price-calculator' ); ?></p>
					</div>
				</div>
			</details>

			<div class="dmmp-admin-toolbar">
				<span></span>
				<button type="submit" name="save_rates" value="1" class="button button-primary"><?php echo esc_html__( 'Save Settings', 'dynamic-metal-price-calculator' ); ?></button>
			</div>
		</form>

		<details id="dmmp-section-custom-metals" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Custom Metals & Formulas', 'dynamic-metal-price-calculator' ), __( 'Create simple rates from gold or silver.', 'dynamic-metal-price-calculator' ), ! empty( $custom_metals ) ? __( 'Ready', 'dynamic-metal-price-calculator' ) : __( 'Optional', 'dynamic-metal-price-calculator' ), ! empty( $custom_metals ) ? 'configured' : 'optional' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<form method="post" novalidate>
					<?php wp_nonce_field( 'dmmp_save_settings', 'dmmp_nonce' ); ?>
					<div class="dmmp-admin-grid">
						<?php
						dmmp_admin_setting_row(
							'dmmp_custom_slug',
							__( 'Custom Metal Slug', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_custom_slug" name="dmmp_custom_slug" type="text" />',
							__( 'Short internal name, such as platinum or rose-gold.', 'dynamic-metal-price-calculator' )
						);
						dmmp_admin_setting_row(
							'dmmp_custom_name',
							__( 'Display Name', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_custom_name" name="dmmp_custom_name" type="text" />',
							__( 'Name shown in the metal type dropdown.', 'dynamic-metal-price-calculator' )
						);
						dmmp_admin_setting_row(
							'dmmp_custom_formula',
							__( 'Simple Formula', 'dynamic-metal-price-calculator' ),
							'<input id="dmmp_custom_formula" name="dmmp_custom_formula" type="text" placeholder="' . esc_attr__( 'gold_rate * 1.25', 'dynamic-metal-price-calculator' ) . '" />',
							__( 'Use gold_rate or silver_rate.', 'dynamic-metal-price-calculator' ),
							__( 'gold_rate * 1.25 = 25% higher than gold.', 'dynamic-metal-price-calculator' )
						);
						?>
					</div>
					<p><button type="submit" name="add_custom" value="1" class="button button-secondary"><?php echo esc_html__( 'Add Custom Metal', 'dynamic-metal-price-calculator' ); ?></button></p>
				</form>

				<?php if ( ! empty( $custom_metals ) ) : ?>
					<table class="widefat striped dmmp-admin-table">
						<thead><tr><th><?php echo esc_html__( 'Name', 'dynamic-metal-price-calculator' ); ?></th><th><?php echo esc_html__( 'Slug', 'dynamic-metal-price-calculator' ); ?></th><th><?php echo esc_html__( 'Formula', 'dynamic-metal-price-calculator' ); ?></th><th><?php echo esc_html__( 'Action', 'dynamic-metal-price-calculator' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( $custom_metals as $slug => $data ) : ?>
								<tr>
									<td><?php echo esc_html( $data['name'] ); ?></td>
									<td><code><?php echo esc_html( $slug ); ?></code></td>
									<td><code><?php echo esc_html( $data['formula'] ); ?></code></td>
									<td>
										<form method="post">
											<?php wp_nonce_field( 'dmmp_save_settings', 'dmmp_nonce' ); ?>
											<input type="hidden" name="dmmp_delete_custom_metal" value="<?php echo esc_attr( $slug ); ?>" />
											<button type="submit" class="button button-link-delete"><?php echo esc_html__( 'Delete', 'dynamic-metal-price-calculator' ); ?></button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php echo esc_html__( 'No custom metals are defined yet.', 'dynamic-metal-price-calculator' ); ?></p>
				<?php endif; ?>
			</div>
		</details>

		<details id="dmmp-section-product-fields" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Product Pricing Fields', 'dynamic-metal-price-calculator' ), __( 'These fields appear inside WooCommerce products.', 'dynamic-metal-price-calculator' ), __( 'Guide', 'dynamic-metal-price-calculator' ), 'optional' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<ul class="dmmp-admin-guide-list">
					<li><strong><?php echo esc_html__( 'Metal Type', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Choose gold, silver, or custom metal.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'Weight', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Enter product metal weight in grams.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'Base Price', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Add fixed cost like stone, design, or packaging.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'Making Charge', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Add labour or production cost.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'Wastage', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Add percentage for metal loss.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'Markup', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Add your profit margin.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'GST / Tax', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Override the global tax value.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><strong><?php echo esc_html__( 'Shipping', 'dynamic-metal-price-calculator' ); ?></strong> <?php echo esc_html__( 'Override the global shipping value.', 'dynamic-metal-price-calculator' ); ?></li>
				</ul>
			</div>
		</details>

		<details id="dmmp-section-variable" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Variable Products', 'dynamic-metal-price-calculator' ), __( 'Use separate metal values for product variations.', 'dynamic-metal-price-calculator' ), __( 'Active', 'dynamic-metal-price-calculator' ), 'configured' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<p><?php echo esc_html__( 'Enable dynamic pricing on variations when size, design, or weight changes.', 'dynamic-metal-price-calculator' ); ?></p>
				<ul class="dmmp-admin-guide-list">
					<li><?php echo esc_html__( 'Use parent settings when all variations share the same values.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Use variation values when each variation has its own weight or charges.', 'dynamic-metal-price-calculator' ); ?></li>
				</ul>
			</div>
		</details>

		<details id="dmmp-section-cart" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Cart & Checkout', 'dynamic-metal-price-calculator' ), __( 'Keep calculated prices active in cart and checkout.', 'dynamic-metal-price-calculator' ), __( 'Active', 'dynamic-metal-price-calculator' ), 'configured' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<p><?php echo esc_html__( 'The plugin uses saved product metal fields to calculate cart and checkout prices.', 'dynamic-metal-price-calculator' ); ?></p>
				<p><?php echo esc_html__( 'Keep product metal values updated for accurate pricing.', 'dynamic-metal-price-calculator' ); ?></p>
			</div>
		</details>

		<details id="dmmp-section-order" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Order Snapshot', 'dynamic-metal-price-calculator' ), __( 'Save pricing details with WooCommerce orders.', 'dynamic-metal-price-calculator' ), __( 'Guide', 'dynamic-metal-price-calculator' ), 'optional' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<p><?php echo esc_html__( 'Stores the final calculated price in the order.', 'dynamic-metal-price-calculator' ); ?></p>
				<p><?php echo esc_html__( 'Future versions may add a full calculation breakdown.', 'dynamic-metal-price-calculator' ); ?></p>
			</div>
		</details>

		<details id="dmmp-section-compatibility" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Compatibility', 'dynamic-metal-price-calculator' ), __( 'Check plugin readiness.', 'dynamic-metal-price-calculator' ), $wc_active ? __( 'Ready', 'dynamic-metal-price-calculator' ) : __( 'Needed', 'dynamic-metal-price-calculator' ), $wc_active ? 'configured' : 'warning' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<ul class="dmmp-admin-guide-list">
					<li><?php echo esc_html__( 'WooCommerce:', 'dynamic-metal-price-calculator' ); ?> <?php echo $wc_active ? esc_html__( 'Active', 'dynamic-metal-price-calculator' ) : esc_html__( 'Needed', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'HPOS:', 'dynamic-metal-price-calculator' ); ?> <?php echo $hpos_ready ? esc_html__( 'Ready', 'dynamic-metal-price-calculator' ) : esc_html__( 'Needed', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Blocks / Store API:', 'dynamic-metal-price-calculator' ); ?> <?php echo esc_html__( 'Basic support', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'WordPress:', 'dynamic-metal-price-calculator' ); ?> <?php echo $wp_ready ? esc_html__( 'Supported', 'dynamic-metal-price-calculator' ) : esc_html__( 'Update recommended', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'PHP:', 'dynamic-metal-price-calculator' ); ?> <?php echo $php_ready ? esc_html__( 'Supported', 'dynamic-metal-price-calculator' ) : esc_html__( 'Update required', 'dynamic-metal-price-calculator' ); ?></li>
				</ul>
			</div>
		</details>

		<details id="dmmp-section-help" class="dmmp-admin-section">
			<summary><?php dmmp_admin_section_header( __( 'Help & Examples', 'dynamic-metal-price-calculator' ), __( 'Use these examples to check your setup.', 'dynamic-metal-price-calculator' ), __( 'Guide', 'dynamic-metal-price-calculator' ), 'optional' ); ?></summary>
			<div class="dmmp-admin-section-body">
				<h3><?php echo esc_html__( 'Simple Gold Product', 'dynamic-metal-price-calculator' ); ?></h3>
				<p class="dmmp-admin-example"><?php echo esc_html__( 'Gold Rate / gram: 7,000 | Weight: 2g | Metal value: 14,000', 'dynamic-metal-price-calculator' ); ?></p>
				<p><?php echo esc_html__( 'Then the plugin adds making charge, wastage, markup, GST, and shipping if set.', 'dynamic-metal-price-calculator' ); ?></p>
				<h3><?php echo esc_html__( 'Variable Ring Sizes', 'dynamic-metal-price-calculator' ); ?></h3>
				<p class="dmmp-admin-example"><?php echo esc_html__( 'Size 6: 2.1g | Size 7: 2.3g | Size 8: 2.5g', 'dynamic-metal-price-calculator' ); ?></p>
				<p><?php echo esc_html__( 'Use variation pricing when each size has a different weight.', 'dynamic-metal-price-calculator' ); ?></p>
				<h3><?php echo esc_html__( 'How to set up', 'dynamic-metal-price-calculator' ); ?></h3>
				<ol>
					<li><?php echo esc_html__( 'Enter gold and silver rates per gram.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Open a WooCommerce product.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Select metal type.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Enter weight in grams.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Add charges if needed.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Save the product.', 'dynamic-metal-price-calculator' ); ?></li>
					<li><?php echo esc_html__( 'Check product, cart, and checkout pages.', 'dynamic-metal-price-calculator' ); ?></li>
				</ol>
			</div>
		</details>

		<script>
			(function() {
				var links = document.querySelectorAll('.dmmp-open-section');
				links.forEach(function(link) {
					link.addEventListener('click', function(event) {
						var target = document.querySelector(link.getAttribute('href'));
						if (!target) {
							return;
						}
						event.preventDefault();
						target.open = true;
						target.scrollIntoView({ behavior: 'smooth', block: 'start' });
					});
				});
			}());
		</script>
	</div>
	<?php
}

/**
 * Render admin page (secure: capability, nonces, wp_unslash, sanitization)
 */
function dmmp_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	dmmp_render_admin_page_v303();
	return;

	$opts = get_option( 'dmmp_options', [] );

	// Save handler (single entry point) — verify nonce
	if ( isset( $_POST['dmmp_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dmmp_nonce'] ) ), 'dmmp_save_settings' ) ) {

		$numeric_fields = [
			'dmmp_gold_rate',
			'dmmp_silver_rate',
			'dmmp_gst',
			'dmmp_shipping',             // global shipping per product (flat)
			'dmmp_gold_22k_percent',
			'dmmp_gold_20k_percent',
			'dmmp_gold_18k_percent',
		];

		foreach ( $numeric_fields as $f ) {
			if ( isset( $_POST[ $f ] ) ) {
				$opts[ $f ] = floatval( wp_unslash( $_POST[ $f ] ) );
			}
		}

		if ( isset( $_POST['save_rates'] ) ) {
			$opts['dmmp_show_extra_charges'] = isset( $_POST['dmmp_show_extra_charges'] ) ? 1 : 0;
		}

		// Add custom metal (slug, name, formula)
		if ( isset( $_POST['dmmp_custom_slug'], $_POST['dmmp_custom_name'], $_POST['dmmp_custom_formula'] ) ) {
			$slug    = sanitize_title( wp_unslash( $_POST['dmmp_custom_slug'] ) );
			$name    = sanitize_text_field( wp_unslash( $_POST['dmmp_custom_name'] ) );
			$formula = sanitize_text_field( wp_unslash( $_POST['dmmp_custom_formula'] ) );

			if ( $slug && $name && $formula ) {
				if ( empty( $opts['custom_metals'] ) || ! is_array( $opts['custom_metals'] ) ) {
					$opts['custom_metals'] = [];
				}
				$opts['custom_metals'][ $slug ] = [
					'name'    => $name,
					'formula' => $formula,
				];
			}
		}

		// Delete custom metal
		if ( isset( $_POST['dmmp_delete_custom_metal'] ) ) {
			$del = sanitize_text_field( wp_unslash( $_POST['dmmp_delete_custom_metal'] ) );
			if ( ! empty( $opts['custom_metals'][ $del ] ) ) {
				unset( $opts['custom_metals'][ $del ] );
			}
		}

		update_option( 'dmmp_options', $opts );

		// admin success notice (display once)
		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Dynamic Metal Price Calculator: settings saved.', 'dynamic-metal-price-calculator' ) . '</p></div>';
		} );
	}

	// refresh options
	$opts = get_option( 'dmmp_options', [] );

	$gold_rate   = isset( $opts['dmmp_gold_rate'] ) ? $opts['dmmp_gold_rate'] : '';
	$silver_rate = isset( $opts['dmmp_silver_rate'] ) ? $opts['dmmp_silver_rate'] : '';

	$r22 = isset( $opts['dmmp_gold_22k_percent'] ) ? $opts['dmmp_gold_22k_percent'] : 93;
	$r20 = isset( $opts['dmmp_gold_20k_percent'] ) ? $opts['dmmp_gold_20k_percent'] : 85.5;
	$r18 = isset( $opts['dmmp_gold_18k_percent'] ) ? $opts['dmmp_gold_18k_percent'] : 78;

	$global_gst      = isset( $opts['dmmp_gst'] ) ? $opts['dmmp_gst'] : '';
	$global_shipping = isset( $opts['dmmp_shipping'] ) ? $opts['dmmp_shipping'] : '';
	$show_extra_charges = ! empty( $opts['dmmp_show_extra_charges'] );

	$custom_metals = isset( $opts['custom_metals'] ) && is_array( $opts['custom_metals'] ) ? $opts['custom_metals'] : [];
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Dynamic Metal Price Calculator', 'dynamic-metal-price-calculator' ); ?></h1>

		<form method="post" novalidate>
			<?php wp_nonce_field( 'dmmp_save_settings', 'dmmp_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th><label for="dmmp_gold_rate"><?php echo esc_html__( 'Gold rate (per gram)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_gold_rate" name="dmmp_gold_rate" type="number" step="0.01" min="0" value="<?php echo esc_attr( $gold_rate ); ?>" class="regular-text" /></td>
				</tr>

				<tr>
					<th><label for="dmmp_silver_rate"><?php echo esc_html__( 'Silver rate (per gram)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_silver_rate" name="dmmp_silver_rate" type="number" step="0.01" min="0" value="<?php echo esc_attr( $silver_rate ); ?>" class="regular-text" /></td>
				</tr>

				<tr>
					<th><label for="dmmp_gst"><?php echo esc_html__( 'Global GST (%)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_gst" name="dmmp_gst" type="number" step="0.01" min="0" value="<?php echo esc_attr( $global_gst ); ?>" class="small-text" /> <span class="description"><?php echo esc_html__( 'Global GST applied by default; product-level override available.', 'dynamic-metal-price-calculator' ); ?></span></td>
				</tr>

				<tr>
					<th><label for="dmmp_shipping"><?php echo esc_html__( 'Global Shipping (flat per product)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_shipping" name="dmmp_shipping" type="number" step="0.01" min="0" value="<?php echo esc_attr( $global_shipping ); ?>" class="small-text" /> <span class="description"><?php echo esc_html__( 'Flat shipping charge applied per product; product-level override available.', 'dynamic-metal-price-calculator' ); ?></span></td>
				</tr>

				<tr>
					<th><label for="dmmp_show_extra_charges"><?php echo esc_html__( 'Frontend Extra Charges Display', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td>
						<label>
							<input id="dmmp_show_extra_charges" name="dmmp_show_extra_charges" type="checkbox" value="1" <?php checked( $show_extra_charges ); ?> />
							<?php echo esc_html__( 'Show GST, shipping, making charge, and wastage details on single product pages.', 'dynamic-metal-price-calculator' ); ?>
						</label>
						<p class="description"><?php echo esc_html__( 'Disabled by default so automated rate details are hidden from customers unless you choose to show them.', 'dynamic-metal-price-calculator' ); ?></p>
					</td>
				</tr>

				<tr>
					<th><label for="dmmp_gold_22k_percent"><?php echo esc_html__( '22K Percentage (%)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_gold_22k_percent" name="dmmp_gold_22k_percent" type="number" step="0.01" min="0" value="<?php echo esc_attr( $r22 ); ?>" class="small-text" /> <span class="description"><?php echo esc_html__( 'Percent of 24K gold (e.g. 93)', 'dynamic-metal-price-calculator' ); ?></span></td>
				</tr>

				<tr>
					<th><label for="dmmp_gold_20k_percent"><?php echo esc_html__( '20K Percentage (%)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_gold_20k_percent" name="dmmp_gold_20k_percent" type="number" step="0.01" min="0" value="<?php echo esc_attr( $r20 ); ?>" class="small-text" /> <span class="description"><?php echo esc_html__( 'Percent of 24K gold (e.g. 85.5)', 'dynamic-metal-price-calculator' ); ?></span></td>
				</tr>

				<tr>
					<th><label for="dmmp_gold_18k_percent"><?php echo esc_html__( '18K Percentage (%)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_gold_18k_percent" name="dmmp_gold_18k_percent" type="number" step="0.01" min="0" value="<?php echo esc_attr( $r18 ); ?>" class="small-text" /> <span class="description"><?php echo esc_html__( 'Percent of 24K gold (e.g. 78)', 'dynamic-metal-price-calculator' ); ?></span></td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Save Rates & Settings', 'dynamic-metal-price-calculator' ), 'primary', 'save_rates' ); ?>
		</form>

		<hr/>

		<h2><?php echo esc_html__( 'Custom Metals (create/delete)', 'dynamic-metal-price-calculator' ); ?></h2>

		<form method="post" novalidate>
			<?php wp_nonce_field( 'dmmp_save_settings', 'dmmp_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="dmmp_custom_slug"><?php echo esc_html__( 'Slug (unique)', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_custom_slug" name="dmmp_custom_slug" type="text" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="dmmp_custom_name"><?php echo esc_html__( 'Display Name', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_custom_name" name="dmmp_custom_name" type="text" class="regular-text" /></td>
				</tr>
				<tr>
					<th><label for="dmmp_custom_formula"><?php echo esc_html__( 'Formula', 'dynamic-metal-price-calculator' ); ?></label></th>
					<td><input id="dmmp_custom_formula" name="dmmp_custom_formula" type="text" class="regular-text" placeholder="<?php echo esc_attr__( 'e.g. gold_rate * 1.25', 'dynamic-metal-price-calculator' ); ?>" /></td>
				</tr>
			</table>

			<?php submit_button( esc_html__( 'Add Custom Metal', 'dynamic-metal-price-calculator' ), 'secondary', 'add_custom' ); ?>
		</form>

		<hr/>

		<h2><?php echo esc_html__( 'Existing Custom Metals', 'dynamic-metal-price-calculator' ); ?></h2>
		<?php if ( ! empty( $custom_metals ) ) : ?>
			<table class="widefat fixed striped">
				<thead><tr><th><?php echo esc_html__( 'Name', 'dynamic-metal-price-calculator' ); ?></th><th><?php echo esc_html__( 'Slug', 'dynamic-metal-price-calculator' ); ?></th><th><?php echo esc_html__( 'Formula', 'dynamic-metal-price-calculator' ); ?></th><th><?php echo esc_html__( 'Action', 'dynamic-metal-price-calculator' ); ?></th></tr></thead>
				<tbody>
					<?php foreach ( $custom_metals as $slug => $data ) : ?>
						<tr>
							<td><?php echo esc_html( $data['name'] ); ?></td>
							<td><?php echo esc_html( $slug ); ?></td>
							<td><code><?php echo esc_html( $data['formula'] ); ?></code></td>
							<td>
								<form method="post" style="display:inline;">
									<?php wp_nonce_field( 'dmmp_save_settings', 'dmmp_nonce' ); ?>
									<input type="hidden" name="dmmp_delete_custom_metal" value="<?php echo esc_attr( $slug ); ?>" />
									<?php submit_button( esc_html__( 'Delete', 'dynamic-metal-price-calculator' ), 'delete', 'delete_metal', false ); ?>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php echo esc_html__( 'No custom metals defined.', 'dynamic-metal-price-calculator' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/* ---------------------------------------------------------------------
 * Product meta fields
 * --------------------------------------------------------------------- */
add_action( 'woocommerce_product_options_general_product_data', function() {
	$opts   = get_option( 'dmmp_options', [] );
	$custom = isset( $opts['custom_metals'] ) && is_array( $opts['custom_metals'] ) ? $opts['custom_metals'] : [];

	$options = [
		''      => __( '— Select —', 'dynamic-metal-price-calculator' ),
		'gold'  => __( 'Gold (24K base)', 'dynamic-metal-price-calculator' ),
		'22k'   => __( 'Gold 22k', 'dynamic-metal-price-calculator' ),
		'20k'   => __( 'Gold 20k', 'dynamic-metal-price-calculator' ),
		'18k'   => __( 'Gold 18k', 'dynamic-metal-price-calculator' ),
		'silver'   => __( 'Silver', 'dynamic-metal-price-calculator' ),
		// note: 12k and silver removed per request
	];

	if ( ! empty( $custom ) ) {
		foreach ( $custom as $slug => $data ) {
			$options[ $slug ] = sanitize_text_field( $data['name'] );
		}
	}

	woocommerce_wp_select( [
		'id'      => '_dmmp_metal_type',
		'label'   => __( 'Metal Type', 'dynamic-metal-price-calculator' ),
		'options' => $options,
	] );

	woocommerce_wp_text_input( [
		'id'    => '_dmmp_weight',
		'label' => __( 'Weight (g)', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Metal rate × weight, example 500 × 1 g = 500', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );

	woocommerce_wp_text_input( [
		'id'    => '_dmmp_base_price',
		'label' => __( 'Base Price', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Optional Add base price, example 500 + 500 = 1000', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );
	
	woocommerce_wp_text_input( [
		'id'    => '_dmmp_making_charge',
		'label' => __( 'Making Charge (%)', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Optional Add percent of subtotal, example 10% → 1000 + 100 = 1100', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );

	woocommerce_wp_text_input( [
		'id'    => '_dmmp_wastage',
		'label' => __( 'Wastage (%)', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Optional Add percent of subtotal, example 5% → 1100 + 55 = 1155', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );



	woocommerce_wp_text_input( [
		'id'    => '_dmmp_markup',
		'label' => __( 'Markup (%)', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Optional Add seller margin, example 20% → 1155 + 231 = 1386', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );

	woocommerce_wp_text_input( [
		'id'    => '_dmmp_gst',
		'label' => __( 'GST (%)', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Optional per-product GST to override global (leave blank to use global)', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );

	woocommerce_wp_text_input( [
		'id'    => '_dmmp_shipping',
		'label' => __( 'Shipping (flat)', 'dynamic-metal-price-calculator' ),
		'type'  => 'number',
		'desc_tip' => true,
		'description' => __( 'Optional per-product flat shipping charge to override global (leave blank to use global)', 'dynamic-metal-price-calculator' ),
		'custom_attributes' => [ 'step' => '0.01' ],
	] );
} );

/* ---------------------------------------------------------------------
 * Save product meta securely (nonce checked, sanitized)
 * --------------------------------------------------------------------- */
add_action( 'woocommerce_admin_process_product_object', function( $product ) {
	// Use WP core nonce for post update
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $product->get_id() ) ) {
		return;
	}

	$save_map = [
		'_dmmp_metal_type'    => 'text',
		'_dmmp_weight'        => 'float',
		'_dmmp_base_price'    => 'float',
		'_dmmp_wastage'       => 'float',
		'_dmmp_making_charge' => 'float',
		'_dmmp_markup'        => 'float',
		'_dmmp_gst'           => 'float',
		'_dmmp_shipping'      => 'float',
	];

	foreach ( $save_map as $meta_key => $type ) {
		if ( isset( $_POST[ $meta_key ] ) ) {
			$raw = isset( $_POST[ $meta_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) ) : '';
			if ( $type === 'float' ) {
				$product->update_meta_data( $meta_key, floatval( sanitize_text_field( $raw ) ) );
			} else {
				$product->update_meta_data( $meta_key, sanitize_text_field( $raw ) );
			}
		}
	}
} );

/* ---------------------------------------------------------------------
 * Variable product: Variation metal pricing fields in admin
 * --------------------------------------------------------------------- */
add_action( 'woocommerce_product_after_variable_attributes', function( $loop, $variation_data, $variation ) {
	$opts   = get_option( 'dmmp_options', [] );
	$custom = isset( $opts['custom_metals'] ) && is_array( $opts['custom_metals'] ) ? $opts['custom_metals'] : [];

	$options = [
		''      => __( '— Select —', 'dynamic-metal-price-calculator' ),
		'gold'  => __( 'Gold (24K base)', 'dynamic-metal-price-calculator' ),
		'22k'   => __( 'Gold 22k', 'dynamic-metal-price-calculator' ),
		'20k'   => __( 'Gold 20k', 'dynamic-metal-price-calculator' ),
		'18k'   => __( 'Gold 18k', 'dynamic-metal-price-calculator' ),
		'silver'   => __( 'Silver', 'dynamic-metal-price-calculator' ),
	];

	if ( ! empty( $custom ) ) {
		foreach ( $custom as $slug => $data ) {
			$options[ $slug ] = sanitize_text_field( $data['name'] );
		}
	}

	$variation_id = $variation->ID;

	$metal_type    = get_post_meta( $variation_id, '_dmmp_metal_type', true );
	$weight        = get_post_meta( $variation_id, '_dmmp_weight', true );
	$base_price    = get_post_meta( $variation_id, '_dmmp_base_price', true );
	$making_charge = get_post_meta( $variation_id, '_dmmp_making_charge', true );
	$wastage       = get_post_meta( $variation_id, '_dmmp_wastage', true );
	$markup        = get_post_meta( $variation_id, '_dmmp_markup', true );
	$gst           = get_post_meta( $variation_id, '_dmmp_gst', true );
	$shipping      = get_post_meta( $variation_id, '_dmmp_shipping', true );
	$enable_dmp    = get_post_meta( $variation_id, '_dmmp_enable', true );

	?>
	<div class="dmmp-variation-fields">
		<p class="form-row form-row-first">
			<label for="_dmmp_enable_var[<?php echo esc_attr( $variation_id ); ?>]">
				<input 
					type="checkbox" 
					id="_dmmp_enable_var[<?php echo esc_attr( $variation_id ); ?>]" 
					name="_dmmp_enable_var[<?php echo esc_attr( $variation_id ); ?>]" 
					value="1" 
					<?php checked( $enable_dmp, '1' ); ?> 
					class="dmmp-enable-toggle"
				/>
				<?php echo esc_html__( 'Use Dynamic Metal Price Calculator for this variation', 'dynamic-metal-price-calculator' ); ?>
			</label>
		</p>
		<div class="dmmp-metal-fields" <?php echo $enable_dmp !== '1' ? 'style="display:none;"' : ''; ?>>
		<h4><?php echo esc_html__( 'Metal Pricing', 'dynamic-metal-price-calculator' ); ?></h4>
		<?php
		woocommerce_wp_select( [
			'id'      => "_dmmp_metal_type_var[{$variation_id}]",
			'label'   => __( 'Metal Type', 'dynamic-metal-price-calculator' ),
			'value'   => $metal_type,
			'options' => $options,
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_weight_var[{$variation_id}]",
			'label' => __( 'Weight (g)', 'dynamic-metal-price-calculator' ),
			'value' => $weight,
			'type'  => 'number',
			'custom_attributes' => [ 'step' => '0.01' ],
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_base_price_var[{$variation_id}]",
			'label' => __( 'Base Price', 'dynamic-metal-price-calculator' ),
			'value' => $base_price,
			'type'  => 'number',
			'custom_attributes' => [ 'step' => '0.01' ],
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_making_charge_var[{$variation_id}]",
			'label' => __( 'Making Charge (%)', 'dynamic-metal-price-calculator' ),
			'value' => $making_charge,
			'type'  => 'number',
			'custom_attributes' => [ 'step' => '0.01' ],
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_wastage_var[{$variation_id}]",
			'label' => __( 'Wastage (%)', 'dynamic-metal-price-calculator' ),
			'value' => $wastage,
			'type'  => 'number',
			'custom_attributes' => [ 'step' => '0.01' ],
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_markup_var[{$variation_id}]",
			'label' => __( 'Markup (%)', 'dynamic-metal-price-calculator' ),
			'value' => $markup,
			'type'  => 'number',
			'custom_attributes' => [ 'step' => '0.01' ],
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_gst_var[{$variation_id}]",
			'label' => __( 'GST (%)', 'dynamic-metal-price-calculator' ),
			'value' => $gst,
			'type'  => 'number',
			'desc_tip' => true,
			'description' => __( 'Leave blank to use global GST', 'dynamic-metal-price-calculator' ),
			'custom_attributes' => [ 'step' => '0.01' ],
		] );

		woocommerce_wp_text_input( [
			'id'    => "_dmmp_shipping_var[{$variation_id}]",
			'label' => __( 'Shipping (flat)', 'dynamic-metal-price-calculator' ),
			'value' => $shipping,
			'type'  => 'number',
			'desc_tip' => true,
			'description' => __( 'Leave blank to use global shipping', 'dynamic-metal-price-calculator' ),
			'custom_attributes' => [ 'step' => '0.01' ],
		] );
		?>
		</div>
	</div>
	<?php
}, 10, 3 );

/* ---------------------------------------------------------------------
 * JavaScript for variation toggle functionality
 * --------------------------------------------------------------------- */
add_action( 'admin_footer', function() {
	global $pagenow;
	if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) ) {
		return;
	}
	
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'product' ) {
		return;
	}
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Handle toggle click
		$(document).on('change', '.dmmp-enable-toggle', function() {
			var $this = $(this);
			var $container = $this.closest('.dmmp-variation-fields');
			var $metalFields = $container.find('.dmmp-metal-fields');
			
			if ($this.is(':checked')) {
				$metalFields.slideDown();
			} else {
				$metalFields.slideUp();
			}
		});
		
		// Also handle any existing variations on page load
		$('.dmmp-enable-toggle').each(function() {
			var $this = $(this);
			var $container = $this.closest('.dmmp-variation-fields');
			var $metalFields = $container.find('.dmmp-metal-fields');
			
			if (!$this.is(':checked')) {
				$metalFields.hide();
			}
		});
		
		// Watch for new variations being added
		$(document).on('woocommerce_variation_added', function(event, index) {
			var $variation = $('.woocommerce_variation').eq(index);
			var $toggle = $variation.find('.dmmp-enable-toggle');
			var $metalFields = $variation.find('.dmmp-metal-fields');
			
			if ($toggle.length && !$toggle.is(':checked')) {
				$metalFields.hide();
			}
		});
	});
	</script>
	<?php
});

/* ---------------------------------------------------------------------
 * Save variation meta fields
 * --------------------------------------------------------------------- */
add_action( 'woocommerce_save_product_variation', function( $variation_id, $i ) {
	if ( ! current_user_can( 'edit_product', $variation_id ) ) {
		return;
	}

	// Verify WooCommerce nonce for variation saving
	// WooCommerce adds a nonce field automatically to the variation forms
	$nonce_check = isset( $_POST['woocommerce_save_nonce'] ) ? 
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_save_nonce'] ) ), 'woocommerce_save_variation' ) : 
		( defined( 'DOING_AJAX' ) && DOING_AJAX ? true : false );
	
	// Also check the WordPress nonce as fallback
	$wp_nonce = isset( $_POST['_wpnonce'] ) ? 
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $variation_id ) : 
		false;
	
	// Allow if either nonce is valid (WooCommerce nonce or WP post nonce)
	if ( ! $nonce_check && ! $wp_nonce && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	$meta_fields = [
		'_dmmp_enable_var',
		'_dmmp_metal_type_var',
		'_dmmp_weight_var',
		'_dmmp_base_price_var',
		'_dmmp_making_charge_var',
		'_dmmp_wastage_var',
		'_dmmp_markup_var',
		'_dmmp_gst_var',
		'_dmmp_shipping_var',
	];

	$meta_keys = [
		'_dmmp_enable_var'      => '_dmmp_enable',
		'_dmmp_metal_type_var'    => '_dmmp_metal_type',
		'_dmmp_weight_var'        => '_dmmp_weight',
		'_dmmp_base_price_var'    => '_dmmp_base_price',
		'_dmmp_making_charge_var' => '_dmmp_making_charge',
		'_dmmp_wastage_var'       => '_dmmp_wastage',
		'_dmmp_markup_var'        => '_dmmp_markup',
		'_dmmp_gst_var'           => '_dmmp_gst',
		'_dmmp_shipping_var'      => '_dmmp_shipping',
	];

	// Check if enable checkbox is present in POST data
	$enable_checkbox_sent = isset( $_POST['_dmmp_enable_var'] ) && isset( $_POST['_dmmp_enable_var'][ $variation_id ] );

	foreach ( $meta_fields as $field ) {
		if ( $field === '_dmmp_enable_var' ) {
			// Handle checkbox separately - it's sent when checked, not sent when unchecked
			if ( $enable_checkbox_sent ) {
				update_post_meta( $variation_id, '_dmmp_enable', '1' );
			} else {
				delete_post_meta( $variation_id, '_dmmp_enable' );
			}
			continue;
		}

		if ( isset( $_POST[ $field ] ) && isset( $_POST[ $field ][ $variation_id ] ) ) {
			// Sanitize input properly based on field type
			$raw_value = isset($_POST[$field][$variation_id]) ? sanitize_text_field(wp_unslash($_POST[$field][$variation_id])) : '';
			$real_key = $meta_keys[ $field ];

			if ( $field === '_dmmp_metal_type_var' ) {
				update_post_meta( $variation_id, $real_key, $raw_value );
			} else {
				// For numeric fields, use floatval
				$value = floatval( $raw_value );
				update_post_meta( $variation_id, $real_key, $value );
			}
		}
	}

	// If dynamic metal pricing is enabled, compute and save the price
	$enable_dmp = get_post_meta( $variation_id, '_dmmp_enable', true );
	if ( $enable_dmp === '1' ) {
		$variation = wc_get_product( $variation_id );
		if ( $variation ) {
			$calc = dmmp_compute_active_price_for_product_object( $variation );
			if ( $calc !== null && $calc > 0 ) {
				// Use WooCommerce product methods to set prices
				$variation->set_price( $calc );
				if ( ! dmmp_product_has_raw_sale_price( $variation ) ) {
					$variation->set_regular_price( $calc );
					$variation->set_sale_price( '' );
				}
				$variation->set_stock_status( 'instock' );
				$variation->save();
			}
		}
	}
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Safe expression evaluator (RPN) - no eval()
 * --------------------------------------------------------------------- */

function dmmp_tokenize_expression( $expr ) {
	$expr = trim( (string) $expr );
	$length = strlen( $expr );
	$tokens = [];
	$number = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$ch = $expr[ $i ];
		if ( $ch === ' ' || $ch === "\t" || $ch === "\n" ) {
			continue;
		}
		if ( ( $ch >= '0' && $ch <= '9' ) || $ch === '.' ) {
			$number .= $ch;
			continue;
		}
		if ( $number !== '' ) {
			$tokens[] = $number;
			$number = '';
		}
		if ( in_array( $ch, [ '+', '-', '*', '/', '(', ')' ], true ) ) {
			$tokens[] = $ch;
			continue;
		}
		// invalid character
		return [];
	}
	if ( $number !== '' ) {
		$tokens[] = $number;
	}
	return $tokens;
}

function dmmp_infix_to_rpn( $tokens ) {
	$precedence = [ '+' => 1, '-' => 1, '*' => 2, '/' => 2 ];
	$out = [];
	$stack = [];
	foreach ( $tokens as $token ) {
		if ( is_numeric( $token ) ) {
			$out[] = $token;
			continue;
		}
		if ( isset( $precedence[ $token ] ) ) {
			while ( ! empty( $stack ) ) {
				$top = end( $stack );
				if ( isset( $precedence[ $top ] ) && $precedence[ $top ] >= $precedence[ $token ] ) {
					$out[] = array_pop( $stack );
					continue;
				}
				break;
			}
			$stack[] = $token;
			continue;
		}
		if ( $token === '(' ) {
			$stack[] = $token;
			continue;
		}
		if ( $token === ')' ) {
			$found = false;
			while ( ! empty( $stack ) ) {
				$pop = array_pop( $stack );
				if ( $pop === '(' ) {
					$found = true;
					break;
				}
				$out[] = $pop;
			}
			if ( ! $found ) {
				return [];
			}
			continue;
		}
		return [];
	}
	while ( ! empty( $stack ) ) {
		$pop = array_pop( $stack );
		if ( $pop === '(' || $pop === ')' ) return [];
		$out[] = $pop;
	}
	return $out;
}

function dmmp_eval_rpn( $rpn ) {
	$stack = [];
	foreach ( $rpn as $token ) {
		if ( is_numeric( $token ) ) {
			$stack[] = (float) $token;
			continue;
		}
		if ( in_array( $token, [ '+', '-', '*', '/' ], true ) ) {
			if ( count( $stack ) < 2 ) return null;
			$b = array_pop( $stack );
			$a = array_pop( $stack );
			switch ( $token ) {
				case '+': $res = $a + $b; break;
				case '-': $res = $a - $b; break;
				case '*': $res = $a * $b; break;
				case '/': $res = ( $b == 0 ) ? null : ( $a / $b ); break;
				default: $res = null;
			}
			if ( $res === null ) return null;
			$stack[] = $res;
			continue;
		}
		return null;
	}
	if ( count( $stack ) !== 1 ) return null;
	return array_pop( $stack );
}

/**
 * Evaluate expression that contains only numbers/operators.
 * Returns float on success, false on failure.
 */
function dmmp_evaluate_expression_safe( $expr ) {
	$expr = trim( (string) $expr );
	// allow only digits, dot, whitespace, operators and parentheses
	if ( preg_match( '/[^0-9+\-.*\/()\s]/', $expr ) ) {
		return false;
	}
	$tokens = dmmp_tokenize_expression( $expr );
	if ( empty( $tokens ) ) return false;
	$rpn = dmmp_infix_to_rpn( $tokens );
	if ( empty( $rpn ) ) return false;
	$result = dmmp_eval_rpn( $rpn );
	if ( $result === null ) return false;
	return (float) $result;
}

/* ---------------------------------------------------------------------
 * Determine metal rate for a given type (handles 24k,22k,20k,18k,custom)
 * Custom formulas: allowed variables: gold_rate, silver_rate (case-insensitive).
 * Replace allowed vars with numeric values and evaluate safely.
 * --------------------------------------------------------------------- */
function dmmp_get_metal_rate_for_type( $type ) {
	$opts = get_option( 'dmmp_options', [] );

	$gold_rate   = isset( $opts['dmmp_gold_rate'] ) ? floatval( $opts['dmmp_gold_rate'] ) : 0;
	$silver_rate = isset( $opts['dmmp_silver_rate'] ) ? floatval( $opts['dmmp_silver_rate'] ) : 0;

	$p22 = isset( $opts['dmmp_gold_22k_percent'] ) ? floatval( $opts['dmmp_gold_22k_percent'] ) : 93;
	$p20 = isset( $opts['dmmp_gold_20k_percent'] ) ? floatval( $opts['dmmp_gold_20k_percent'] ) : 85.5;
	$p18 = isset( $opts['dmmp_gold_18k_percent'] ) ? floatval( $opts['dmmp_gold_18k_percent'] ) : 78;

	switch ( $type ) {
		case 'gold':
		case '24k':
			return $gold_rate;
		case '22k':
			return ( $gold_rate * ( $p22 / 100 ) );
		case '20k':
			return ( $gold_rate * ( $p20 / 100 ) );
		case '18k':
			return ( $gold_rate * ( $p18 / 100 ) );
		case 'silver':
			// silver remains available for custom formulas or external use, but not in product dropdown per request
			return $silver_rate;
		default:
			// custom formulas stored in options
			if ( ! empty( $opts['custom_metals'] ) && isset( $opts['custom_metals'][ $type ] ) ) {
				$formula = (string) $opts['custom_metals'][ $type ]['formula'];
				if ( $formula === '' ) return 0;

				// prepare formula: replace allowed variable tokens (case-insensitive)
				$map = [
					'gold_rate'   => $gold_rate,
					'goldrate'    => $gold_rate,
					'silver_rate' => $silver_rate,
					'silverrate'  => $silver_rate,
				];

				$formula_lower = $formula;
				// replace tokens with numeric values using word boundaries
				foreach ( $map as $var => $val ) {
					$formula_lower = preg_replace( '/\b' . preg_quote( $var, '/' ) . '\b/i', (string) $val, $formula_lower );
				}

				// evaluate safely
				$val = dmmp_evaluate_expression_safe( $formula_lower );
				if ( $val === false ) {
					return 0;
				}
				return $val;
			}
			return 0;
	}
}

/* ---------------------------------------------------------------------
 * Price computation (secure). Logic:
 * 1) metal_component = metal_rate * weight
 * 2) base_total = metal_component + base_price
 * 3) apply making_charge (%) to base_total
 * 4) apply wastage (%) to base_total
 * 5) apply markup (%) to base_total
 * 6) apply GST (product-level override -> global)
 * 7) add shipping (product-level override -> global) [flat amount]
 * --------------------------------------------------------------------- */
function dmmp_compute_price_for_product_object( $product, $base_price_override = null ) {
	if ( ! $product || ! is_object( $product ) ) return null;

	$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : ( isset( $product->id ) ? $product->id : 0 );
	if ( ! $product_id ) return null;

	// Check if this is a variation and if dynamic metal pricing is enabled
	$is_variation = $product->is_type( 'variation' );
	if ( $is_variation ) {
		$enable_dmp = get_post_meta( $product_id, '_dmmp_enable', true );
		// Only compute price if dynamic metal pricing is enabled for this variation
		if ( $enable_dmp !== '1' ) {
			return null;
		}
	}

	$metal_type    = get_post_meta( $product_id, '_dmmp_metal_type', true );
	$base_price    = ( $base_price_override !== null ) ? floatval( $base_price_override ) : floatval( get_post_meta( $product_id, '_dmmp_base_price', true ) );
	$weight        = floatval( get_post_meta( $product_id, '_dmmp_weight', true ) );
	$wastage       = floatval( get_post_meta( $product_id, '_dmmp_wastage', true ) );
	$making_charge = floatval( get_post_meta( $product_id, '_dmmp_making_charge', true ) ); // percentage
	$markup        = floatval( get_post_meta( $product_id, '_dmmp_markup', true ) );

	// short-circuit: need either a metal_type with rate or base_price
	$metal_rate = dmmp_get_metal_rate_for_type( $metal_type );

	// If no metal rate and no base price, cannot compute
	if ( $metal_rate <= 0 && $base_price <= 0 ) {
		return null;
	}

	$metal_component = ( $metal_rate * $weight );
	$base_total = $metal_component + $base_price;

	// apply making charge (percentage) if present
	if ( $making_charge > 0 ) {
		$base_total += ( $base_total * ( $making_charge / 100 ) );
	}

	// apply wastage (percentage)
	if ( $wastage > 0 ) {
		$base_total += ( $base_total * ( $wastage / 100 ) );
	}

	// apply markup (percentage)
	if ( $markup > 0 ) {
		$base_total += ( $base_total * ( $markup / 100 ) );
	}

	// GST: product-level override falls back to global
	$opts = get_option( 'dmmp_options', [] );
	$global_gst = isset( $opts['dmmp_gst'] ) ? floatval( $opts['dmmp_gst'] ) : 0;
	$product_gst = floatval( get_post_meta( $product_id, '_dmmp_gst', true ) );
	$gst = ( $product_gst > 0 ) ? $product_gst : $global_gst;
	if ( $gst > 0 ) {
		$base_total += ( $base_total * ( $gst / 100 ) );
	}

	// Shipping: product-level override (flat) falls back to global (flat)
	$global_shipping = isset( $opts['dmmp_shipping'] ) ? floatval( $opts['dmmp_shipping'] ) : 0;
	$product_shipping = floatval( get_post_meta( $product_id, '_dmmp_shipping', true ) );
	$shipping = ( $product_shipping > 0 ) ? $product_shipping : $global_shipping;
	if ( $shipping > 0 ) {
		$base_total += $shipping;
	}

	return round( (float) $base_total, 2 );
}

function dmmp_get_raw_product_price_meta( $product, $meta_key ) {
	if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		return '';
	}

	return get_post_meta( $product->get_id(), $meta_key, true );
}

function dmmp_product_has_raw_sale_price( $product ) {
	$regular_price = dmmp_get_raw_product_price_meta( $product, '_regular_price' );
	$sale_price    = dmmp_get_raw_product_price_meta( $product, '_sale_price' );

	return $regular_price !== '' && $sale_price !== '' && floatval( $regular_price ) > 0 && floatval( $sale_price ) > 0;
}

function dmmp_compute_regular_price_for_product_object( $product ) {
	if ( dmmp_product_has_raw_sale_price( $product ) ) {
		$regular_price = dmmp_get_raw_product_price_meta( $product, '_regular_price' );
		return dmmp_compute_price_for_product_object( $product, $regular_price );
	}

	return dmmp_compute_price_for_product_object( $product );
}

function dmmp_compute_sale_price_for_product_object( $product ) {
	if ( ! dmmp_product_has_raw_sale_price( $product ) ) {
		return null;
	}

	$sale_price = dmmp_get_raw_product_price_meta( $product, '_sale_price' );
	return dmmp_compute_price_for_product_object( $product, $sale_price );
}

function dmmp_compute_active_price_for_product_object( $product ) {
	$sale_price = dmmp_compute_sale_price_for_product_object( $product );
	if ( $sale_price !== null && $sale_price > 0 ) {
		return $sale_price;
	}

	return dmmp_compute_price_for_product_object( $product );
}

/* ---------------------------------------------------------------------
 * Persist computed price on product save (so admin price shows updated)
 * and keep security checks
 * --------------------------------------------------------------------- */
add_action( 'save_post_product', function( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) ) return;
	if ( get_post_type( $post_id ) !== 'product' ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$product = wc_get_product( $post_id );
	if ( ! $product ) return;

	$calc = dmmp_compute_price_for_product_object( $product );
	if ( $calc !== null && $calc > 0 ) {
		if ( dmmp_product_has_raw_sale_price( $product ) ) {
			$regular_calc = dmmp_compute_regular_price_for_product_object( $product );
			$sale_calc    = dmmp_compute_sale_price_for_product_object( $product );

			if ( $regular_calc !== null && $regular_calc > 0 && $sale_calc !== null && $sale_calc > 0 ) {
				update_post_meta( $post_id, '_price', $sale_calc );

				$product->set_price( $sale_calc );
			}
		} else {
			update_post_meta( $post_id, '_price', $calc );
			update_post_meta( $post_id, '_regular_price', $calc );

			$product->set_price( $calc );
			$product->set_regular_price( $calc );
		}
	}
}, 20, 3 );

/* ---------------------------------------------------------------------
 * Frontend/cart integration (dynamic price fallback)
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_product_get_price', function( $price, $product ) {
	$calc = dmmp_compute_active_price_for_product_object( $product );
	return ( $calc !== null && $calc > 0 ) ? $calc : $price;
}, 10, 2 );

add_filter( 'woocommerce_product_get_regular_price', function( $price, $product ) {
	$calc = dmmp_compute_regular_price_for_product_object( $product );
	return ( $calc !== null && $calc > 0 ) ? $calc : $price;
}, 10, 2 );

add_filter( 'woocommerce_product_get_sale_price', function( $price, $product ) {
	$calc = dmmp_compute_sale_price_for_product_object( $product );
	return ( $calc !== null && $calc > 0 ) ? $calc : $price;
}, 10, 2 );

add_filter( 'woocommerce_get_price_html', function( $price_html, $product ) {
	if ( ! $product || ! is_object( $product ) || $product->is_type( 'variable' ) || ! dmmp_product_has_raw_sale_price( $product ) ) {
		return $price_html;
	}

	$regular_calc = dmmp_compute_regular_price_for_product_object( $product );
	$sale_calc    = dmmp_compute_sale_price_for_product_object( $product );

	if ( $regular_calc === null || $sale_calc === null || $regular_calc <= 0 || $sale_calc <= 0 || $sale_calc >= $regular_calc ) {
		return $price_html;
	}

	$regular_display = wc_get_price_to_display( $product, [ 'price' => $regular_calc ] );
	$sale_display    = wc_get_price_to_display( $product, [ 'price' => $sale_calc ] );

	return wp_kses_post( wc_format_sale_price( $regular_display, $sale_display ) . $product->get_price_suffix() );
}, 20, 2 );

/* ---------------------------------------------------------------------
 * Variable product: Variation price filters
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_variation_prices_price', function( $price, $variation ) {
	$calc = dmmp_compute_active_price_for_product_object( $variation );
	// Only override if we have a valid positive computed price
	if ( $calc !== null && $calc > 0 ) {
		return $calc;
	}
	return $price;
}, 10, 2 );

add_filter( 'woocommerce_variation_prices_regular_price', function( $price, $variation ) {
	$calc = dmmp_compute_regular_price_for_product_object( $variation );
	// Only override if we have a valid positive computed price
	if ( $calc !== null && $calc > 0 ) {
		return $calc;
	}
	return $price;
}, 10, 2 );

add_filter( 'woocommerce_variation_prices_sale_price', function( $price, $variation ) {
	$calc = dmmp_compute_sale_price_for_product_object( $variation );
	if ( $calc !== null && $calc > 0 ) {
		return $calc;
	}

	return $price;
}, 10, 2 );

// Cache variation prices after save
add_action( 'woocommerce_save_product_variation', function( $variation_id, $i ) {
	$variation = wc_get_product( $variation_id );
	if ( $variation ) {
		wc_delete_product_transients( $variation_id );
	}
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Ensure variation is purchasable when we have valid computed price
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_variation_is_purchasable', function( $is_purchasable, $variation ) {
	// If already purchasable, no change needed
	if ( $is_purchasable ) {
		return true;
	}
	
	// Check if we have valid metal pricing data that would compute to a positive price
	$calc = dmmp_compute_price_for_product_object( $variation );
	if ( $calc !== null && $calc > 0 ) {
		return true;
	}
	
	return $is_purchasable;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Ensure variation shows as in stock when we have valid computed price
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_product_variation_get_stock_status', function( $stock_status, $variation ) {
	// If already in stock, no change needed
	if ( $stock_status === 'instock' ) {
		return $stock_status;
	}
	
	// Check if we have valid metal pricing data
	$calc = dmmp_compute_price_for_product_object( $variation );
	if ( $calc !== null && $calc > 0 ) {
		return 'instock';
	}
	
	return $stock_status;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Ensure parent variable product is purchasable when variations have valid prices
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_is_purchasable', function( $is_purchasable, $product ) {
	// If already purchasable, no change needed
	if ( $is_purchasable ) {
		return true;
	}
	
	// Only apply to variable products
	if ( ! $product->is_type( 'variable' ) ) {
		return $is_purchasable;
	}
	
	// Check if any variation has valid computed price
	$variations = $product->get_children();
	foreach ( $variations as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( $variation ) {
			$calc = dmmp_compute_price_for_product_object( $variation );
			if ( $calc !== null && $calc > 0 ) {
				return true;
			}
		}
	}
	
	return $is_purchasable;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Filter availability for variable products
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_get_availability', function( $availability, $product ) {
	// If already in stock, no change needed
	if ( isset( $availability['class'] ) && $availability['class'] === 'in-stock' ) {
		return $availability;
	}
	
	// Only apply to variable products
	if ( ! $product->is_type( 'variable' ) ) {
		return $availability;
	}
	
	// Check if any variation has valid computed price
	$variations = $product->get_children();
	foreach ( $variations as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( $variation ) {
			$calc = dmmp_compute_price_for_product_object( $variation );
			if ( $calc !== null && $calc > 0 ) {
				$availability['class'] = 'in-stock';
				$availability['availability'] = '';
				return $availability;
			}
		}
	}
	
	return $availability;
}, 10, 2 );

add_action( 'woocommerce_before_calculate_totals', function( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		$product = $cart_item['data'];
		$calc = dmmp_compute_active_price_for_product_object( $product );
		if ( $calc !== null && $calc > 0 ) {
			$cart_item['data']->set_price( $calc );
		}
	}
}, 20 );

/* ---------------------------------------------------------------------
 * Display product-level extra charges on single product page
 * --------------------------------------------------------------------- */
add_action( 'woocommerce_single_product_summary', 'dmmp_show_extra_charges_on_product_page', 26 );
function dmmp_show_extra_charges_on_product_page() {
	global $product;
	if ( ! $product || ! is_object( $product ) ) return;

	$opts = get_option( 'dmmp_options', [] );
	if ( empty( $opts['dmmp_show_extra_charges'] ) ) {
		return;
	}

	$id = $product->get_id();
	$wastage = get_post_meta( $id, '_dmmp_wastage', true );
	$making  = get_post_meta( $id, '_dmmp_making_charge', true );
	$gst     = get_post_meta( $id, '_dmmp_gst', true );
	$shipping = get_post_meta( $id, '_dmmp_shipping', true );

	$global_gst = isset( $opts['dmmp_gst'] ) ? floatval( $opts['dmmp_gst'] ) : 0;
	$global_shipping = isset( $opts['dmmp_shipping'] ) ? floatval( $opts['dmmp_shipping'] ) : 0;

	echo '<div class="dmmp-extra-charges" style="margin-top:10px;">';
	if ( $wastage !== '' && $wastage !== null && floatval( $wastage ) !== 0.0 ) {
		echo '<p><strong>' . esc_html__( 'Wastage:', 'dynamic-metal-price-calculator' ) . '</strong> ' . esc_html( floatval( $wastage ) ) . '%' . '</p>';
	}
	if ( $making !== '' && $making !== null && floatval( $making ) !== 0.0 ) {
		echo '<p><strong>' . esc_html__( 'Making Charge:', 'dynamic-metal-price-calculator' ) . '</strong> ' . esc_html( floatval( $making ) ) . '%' . '</p>';
	}
	if ( $gst !== '' && $gst !== null && floatval( $gst ) !== 0.0 ) {
		echo '<p><strong>' . esc_html__( 'GST (product):', 'dynamic-metal-price-calculator' ) . '</strong> ' . esc_html( floatval( $gst ) ) . '%' . '</p>';
	} elseif ( $global_gst > 0 ) {
		echo '<p><strong>' . esc_html__( 'GST (global):', 'dynamic-metal-price-calculator' ) . '</strong> ' . esc_html( floatval( $global_gst ) ) . '%' . '</p>';
	}

	if ( $shipping !== '' && $shipping !== null && floatval( $shipping ) !== 0.0 ) {
		echo '<p><strong>' . esc_html__( 'Shipping (product):', 'dynamic-metal-price-calculator' ) . '</strong> ' . esc_html( number_format( floatval( $shipping ), 2 ) ) . '</p>';
	} elseif ( $global_shipping > 0 ) {
		echo '<p><strong>' . esc_html__( 'Shipping (global):', 'dynamic-metal-price-calculator' ) . '</strong> ' . esc_html( number_format( floatval( $global_shipping ), 2 ) ) . '</p>';
	}

	echo '</div>';
}
