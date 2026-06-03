<?php
/**
 * Plugin Name: Dynamic Metal Price Calculator
 * Plugin URI: https://tresifylab.com/case-studies/dynamic-metal-price-calculator
 * Description: Live metal pricing for WooCommerce jewellery with karat, weight, GST, markup, and shipping support. Supports variable products.
 * Version: 3.0.2
 * Author: aolo
 * Author URI: https://tresifylab.com/
 * License: GPLv2 or later
 * Text Domain: dynamic-metal-price-calculator
 * Requires at least: 5.8
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * Stable tag: 3.0.2
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

/**
 * Render admin page (secure: capability, nonces, wp_unslash, sanitization)
 */
function dmmp_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

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
			$calc = dmmp_compute_price_for_product_object( $variation );
			if ( $calc !== null && $calc > 0 ) {
				// Use WooCommerce product methods to set prices
				$variation->set_price( $calc );
				$variation->set_regular_price( $calc );
				$variation->set_sale_price( $calc );
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
function dmmp_compute_price_for_product_object( $product ) {
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
	$base_price    = floatval( get_post_meta( $product_id, '_dmmp_base_price', true ) );
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
		update_post_meta( $post_id, '_price', $calc );
		update_post_meta( $post_id, '_regular_price', $calc );

		$product->set_price( $calc );
		$product->set_regular_price( $calc );
	}
}, 20, 3 );

/* ---------------------------------------------------------------------
 * Frontend/cart integration (dynamic price fallback)
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_product_get_price', function( $price, $product ) {
	$calc = dmmp_compute_price_for_product_object( $product );
	return ( $calc !== null && $calc > 0 ) ? $calc : $price;
}, 10, 2 );

add_filter( 'woocommerce_product_get_regular_price', function( $price, $product ) {
	$calc = dmmp_compute_price_for_product_object( $product );
	return ( $calc !== null && $calc > 0 ) ? $calc : $price;
}, 10, 2 );

/* ---------------------------------------------------------------------
 * Variable product: Variation price filters
 * --------------------------------------------------------------------- */
add_filter( 'woocommerce_variation_prices_price', function( $price, $variation ) {
	$calc = dmmp_compute_price_for_product_object( $variation );
	// Only override if we have a valid positive computed price
	if ( $calc !== null && $calc > 0 ) {
		return $calc;
	}
	return $price;
}, 10, 2 );

add_filter( 'woocommerce_variation_prices_regular_price', function( $price, $variation ) {
	$calc = dmmp_compute_price_for_product_object( $variation );
	// Only override if we have a valid positive computed price
	if ( $calc !== null && $calc > 0 ) {
		return $calc;
	}
	return $price;
}, 10, 2 );

add_filter( 'woocommerce_variation_prices_sale_price', function( $price, $variation ) {
	$stored_sale = get_post_meta( $variation->get_id(), '_sale_price', true );
	if ( $stored_sale !== '' && floatval( $stored_sale ) > 0 ) {
		// Temporarily use sale price as base for calculation
		$base_backup = get_post_meta( $variation->get_id(), '_dmmp_base_price', true );
		update_post_meta( $variation->get_id(), '_dmmp_base_price', floatval( $stored_sale ) );
		$calc = dmmp_compute_price_for_product_object( $variation );
		if ( $base_backup === '' ) {
			delete_post_meta( $variation->get_id(), '_dmmp_base_price' );
		} else {
			update_post_meta( $variation->get_id(), '_dmmp_base_price', $base_backup );
		}
		if ( $calc !== null && $calc > 0 ) {
			return $calc;
		}
		return $price;
	} else {
		$calc = dmmp_compute_price_for_product_object( $variation );
		if ( $calc !== null && $calc > 0 ) {
			return $calc;
		}
		return $price;
	}
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
		$calc = dmmp_compute_price_for_product_object( $product );
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
