<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// create custom plugin settings menu
add_action('admin_menu', 'rcfwc_create_menu');
function rcfwc_create_menu() {

	//create new top-level menu
	add_submenu_page( 'options-general.php', 'reCAPTCHA for WooCommerce', 'reCAPTCHA WooCommerce', 'manage_options', __FILE__, 'rcfwc_settings_page' );

	//call register settings function
	add_action( 'admin_init', 'register_rcfwc_settings' );
}

// Register Settings
function register_rcfwc_settings() {
  register_setting( 'rcfwc-settings-group', 'rcfwc_key' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_secret' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_theme' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_login' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_register' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_reset' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_woo_checkout' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_guest_only' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_woo_login' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_woo_register' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_woo_reset' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_selected_payment_methods' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_woo_checkout_pos' );
  register_setting( 'rcfwc-settings-group', 'rcfwc_scripts_all' );
}

// Keys Updated
add_action('update_option_rcfwc_key', 'rcfwc_keys_updated', 10);
add_action('update_option_rcfwc_secret', 'rcfwc_keys_updated', 10);
function rcfwc_keys_updated() {
	update_option('rcfwc_tested', 'no');
}

/**
 * Enqueue admin scripts
 */
function rcfwc_admin_script_enqueue() {
	wp_register_script("recaptcha", "https://www.google.com/recaptcha/api.js?explicit&hl=" . get_locale());
	wp_enqueue_script("recaptcha");
  }
  add_action( 'admin_enqueue_scripts', 'rcfwc_admin_script_enqueue' );
  
// Admin test form to check reCAPTCHA response
function rcfwc_admin_test() {
	?>
	<form action="" method="POST">
	<?php
	if(!empty(get_option('rcfwc_key')) && !empty(get_option('rcfwc_secret'))) {
		$check = rcfwc_recaptcha_check();
		$success = '';
		$error = '';
		if(isset($check['success'])) $success = $check['success'];
		if(isset($check['error_code'])) $error = $check['error_code'];
		echo '<br/><div class="rcfwc-test-response-box">';
		if($success != true) {
			echo '<p style="font-weight: 600; font-size: 19px; margin-top: 0; margin-bottom: 0;">' . __( 'Almost done...', 'recaptcha-woo' ) . '</p>';
		}
		if(!isset($_POST['g-recaptcha-response'])) {
			echo '<p>'
			. '<span style="color: red; font-weight: bold;">' . __( 'API keys have been updated. Please test the reCAPTCHA API response below.', 'recaptcha-woo' ) . '</span>'
			. '<br/>'
			. __( 'reCAPTCHA will not be added to WP login until the test is successfully complete.', 'recaptcha-woo' )
			. '</p>';
		} else {
			if($success == true) {
            	echo '<div class="rcfwc-status-success" style="margin: 0;"><span class="dashicons dashicons-yes-alt"></span> ' . __( 'Success! reCAPTCHA seems to be working correctly with your API keys.', 'recaptcha-woo' ) . '</div>';
				update_option('rcfwc_tested', 'yes');
			} else {
				if($error == "missing-input-response") {
					echo '<p style="font-weight: bold; color: red;">' . esc_html__( 'Please verify that you are human.', 'recaptcha-woo' ) . '</p>';
				} else {
					echo '<p style="font-weight: bold; color: red;">' . esc_html__( 'Failed! There is an error with your API settings. Please check & update them.', 'recaptcha-woo' ) . '<br/>' . esc_html__( 'Error Code:', 'recaptcha-woo' ) . ' ' . $error . '</p>';
				}
			}
			if($error) {
				echo '<p style="font-weight: bold;">' . esc_html__( 'Error Message:', 'recaptcha-woo' ) . " " . esc_html__( 'Please verify that you are human.', 'recaptcha-woo' ) . '</p>';
			}
		}
		if($success != true) {
			echo '<div style="margin-left: 0;">';
			echo rcfwc_field('', '');
			echo '</div><div style="margin-bottom: -20px;"></div>';
			echo '<button type="submit" style="margin-top: 10px; padding: 7px 10px; background: #1c781c; color: #fff; font-size: 15px; font-weight: bold; border: 1px solid #176017; border-radius: 4px; cursor: pointer;">
			'.__( 'TEST RESPONSE', 'recaptcha-woo' ).' <span class="dashicons dashicons-arrow-right-alt"></span>
			</button>';
		}
		echo '</div>';
	}
	?>
	</form>
	<?php
}  

// Show Settings Page
function rcfwc_settings_page() {
?>
<style>
.rcfwc-modern-wrap {
    background: #f9fafb;
    min-height: 100vh;
    margin: 0 -20px -20px -20px;
    padding: 20px;
}

.rcfwc-container {
    max-width: 1200px;
    margin: 0 auto;
}

.rcfwc-header {
    margin: 40px 0;
}

.rcfwc-header h1 {
    margin: 0 0 20px 0;
    font-size: 2.5rem;
    font-weight: 700;
    color: #000;
}

.rcfwc-header p {
    margin: 0;
    font-size: 1.1rem;
    opacity: 0.9;
}

.rcfwc-quick-links {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.rcfwc-quick-links .links-grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: center;
}

.rcfwc-quick-links a {
    text-decoration: none;
}

.rcfwc-quick-links a:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

.rcfwc-quick-links .dashicons {
    margin-left: 4px;
    font-size: 14px;
}

.rcfwc-settings-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
}

@media (min-width: 1024px) {
    .rcfwc-settings-grid {
        grid-template-columns: 2fr 1fr;
    }
}

.rcfwc-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    overflow: hidden;
	margin-bottom: 20px;
}

.rcfwc-card-header {
    background: #f8fafc;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.rcfwc-card-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.rcfwc-card-content {
    padding: 20px;
}

.rcfwc-form-group {
    margin-bottom: 20px;
}

.rcfwc-form-group:last-child {
    margin-bottom: 0;
}

.rcfwc-form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.rcfwc-form-input {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.rcfwc-form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.rcfwc-form-select {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background-color: white;
    transition: border-color 0.2s;
}

.rcfwc-form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.rcfwc-checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.rcfwc-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #e5e7eb;
    border-radius: 4px;
    transition: all 0.2s;
}

.rcfwc-checkbox:checked {
    background-color:rgb(173, 239, 255);
    border-color: #38bdf8;
}

.rcfwc-main-settings input[type="text"],
.rcfwc-main-settings select {
	width: 100%;
	padding: 10px 14px;
	border: 2px solid #e5e7eb;
	border-radius: 8px;
	font-size: 14px;
	transition: border-color 0.2s;
}

.rcfwc-section-divider {
    border: none;
    height: 1px;
    background: linear-gradient(to right, transparent, #e5e7eb, transparent);
    margin: 30px 0;
}

.rcfwc-status-success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rcfwc-status-success .dashicons {
    color: #10b981;
}

.rcfwc-submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.rcfwc-submit-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.rcfwc-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.rcfwc-info-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.rcfwc-info-card h3 {
    margin: 0 0 15px 0;
    color: #1f2937;
    font-size: 1.1rem;
    font-weight: 600;
}

.rcfwc-info-card ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.rcfwc-info-card li {
    margin-bottom: 8px;
    padding-left: 20px;
    position: relative;
}

.rcfwc-info-card li:before {
    content: "→";
    position: absolute;
    left: 0;
    color: #667eea;
    font-weight: bold;
}

.rcfwc-info-card a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.rcfwc-info-card a:hover {
    text-decoration: underline;
}

.rcfwc-toggle-section {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: 20px 0;
}

.rcfwc-toggle-header {
    background: #f8fafc;
    padding: 15px 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    transition: background-color 0.2s;
}

.rcfwc-toggle-header:hover {
    background: #f1f5f9;
}

.rcfwc-toggle-content {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
}

.rcfwc-payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.rcfwc-payment-method {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #f9fafb;
}

.rcfwc-help-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 5px;
}

.rcfwc-test-response-box {
	background:rgb(255, 255, 255);
	padding: 20px;
	border-radius: 8px;
	margin-bottom: 20px;
	border: 1px solid #e5e7eb;
}
</style>

<div class="rcfwc-modern-wrap">
    <div class="rcfwc-container">
        <div class="rcfwc-header">
            <h1><?php echo __( 'reCAPTCHA for WooCommerce', 'recaptcha-woo' ); ?></h1>
            <p><?php echo __( 'Protect your WooCommerce forms and checkout with Google reCAPTCHA to help prevent spam and abuse.', 'recaptcha-woo' ); ?></p>
        </div>

        <div class="rcfwc-quick-links">
            <div class="links-grid">
                <a href="https://relywp.com/blog/how-to-add-google-recaptcha-to-woocommerce/?utm_source=plugin" target="_blank">
                    <?php echo __('View Setup Guide', 'recaptcha-woo'); ?>
					<span class="dashicons dashicons-external" style="margin: 2.5px 0 -2.5px 0; font-size: 14px;"></span>
                </a>
                <span style="color: #9ca3af;">•</span>
                <a href="https://wordpress.org/support/plugin/recaptcha-woo/reviews/#new-post" target="_blank">
                    <?php echo __('Like the plugin? Please leave a review', 'recaptcha-woo'); ?> ⭐️⭐️⭐️⭐️⭐️
                </a>
            </div>
        </div>

        <?php
        if(empty(get_option('rcfwc_tested')) || get_option('rcfwc_tested') != 'yes') {
            echo rcfwc_admin_test();
        } else {
            echo '<div class="rcfwc-status-success"><span class="dashicons dashicons-yes-alt"></span> ' . __( 'Success! reCAPTCHA seems to be working correctly with your API keys.', 'recaptcha-woo' ) . '</div>';
        } ?>

        <div class="rcfwc-settings-grid">
            <div class="rcfwc-main-settings">
                <form method="post" action="options.php">
                    <?php settings_fields( 'rcfwc-settings-group' ); ?>
                    <?php do_settings_sections( 'rcfwc-settings-group' ); ?>

                    <div class="rcfwc-card">
                        <div class="rcfwc-card-header">
                            <h2><?php echo __( 'API Key Settings', 'recaptcha-woo' ); ?></h2>
                        </div>
                        <div class="rcfwc-card-content">
                            <p class="rcfwc-help-text" style="margin-bottom: 20px;">
                                <?php echo __( 'Get your reCAPTCHA keys from:', 'recaptcha-woo' ); ?> 
                                <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a>
                            </p>
                            <p class="rcfwc-help-text" style="margin-bottom: 20px;">
                                <?php echo __( 'Currently reCAPTCHA v2 ("challenge") is the only version supported.' ); ?>
							</p>
							<p class="rcfwc-help-text">
								<?php echo __( 'When creating your API key, enable the "Challenge v2" option.', 'recaptcha-woo' ); ?>
                            </p>

                            <div class="rcfwc-form-group">
                                <label class="rcfwc-form-label"><?php echo __( 'Site Key / ID', 'recaptcha-woo' ); ?></label>
                                <input type="text" name="rcfwc_key" class="rcfwc-form-input" value="<?php echo esc_attr( get_option('rcfwc_key') ); ?>" />
                            </div>

                            <div class="rcfwc-form-group">
                                <label class="rcfwc-form-label"><?php echo __( 'Secret Key', 'recaptcha-woo' ); ?></label>
                                <input type="text" name="rcfwc_secret" class="rcfwc-form-input" value="<?php echo esc_attr( get_option('rcfwc_secret') ); ?>" />
                            </div>

                            <div class="rcfwc-form-group">
                                <label class="rcfwc-form-label"><?php echo __( 'reCAPTCHA Theme', 'recaptcha-woo' ); ?></label>
                                <select name="rcfwc_theme" class="rcfwc-form-select">
                                    <option value="light"<?php if(!get_option('rcfwc_theme') || get_option('rcfwc_theme') == "light") { ?>selected<?php } ?>>
                                        <?php esc_html_e( 'Light', 'recaptcha-woo' ); ?>
                                    </option>
                                    <option value="dark"<?php if(get_option('rcfwc_theme') == "dark") { ?>selected<?php } ?>>
                                        <?php esc_html_e( 'Dark', 'recaptcha-woo' ); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rcfwc-card">
                        <div class="rcfwc-card-header">
                            <h2><?php echo __( 'WordPress Forms', 'recaptcha-woo' ); ?></h2>
                        </div>
                        <div class="rcfwc-card-content">
                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_login" class="rcfwc-checkbox" <?php if(get_option('rcfwc_login')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'WordPress Login', 'recaptcha-woo' ); ?></label>
                                </div>
                            </div>

                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_register" class="rcfwc-checkbox" <?php if(get_option('rcfwc_register')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'WordPress Register', 'recaptcha-woo' ); ?></label>
                                </div>
                            </div>

                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_woo_reset" class="rcfwc-checkbox" <?php if(get_option('rcfwc_woo_reset')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'Reset Password', 'recaptcha-woo' ); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rcfwc-card" <?php if ( !class_exists( 'WooCommerce' ) ) { ?>style="opacity: 0.5; pointer-events: none;"<?php } ?>>
                        <div class="rcfwc-card-header">
                            <h2><?php echo __( 'WooCommerce Forms', 'recaptcha-woo' ); ?></h2>
                        </div>
                        <div class="rcfwc-card-content">
                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_woo_login" class="rcfwc-checkbox" <?php if(get_option('rcfwc_woo_login')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'WooCommerce Login', 'recaptcha-woo' ); ?></label>
                                </div>
                            </div>

                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_woo_register" class="rcfwc-checkbox" <?php if(get_option('rcfwc_woo_register')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'WooCommerce Register', 'recaptcha-woo' ); ?></label>
                                </div>
                            </div>

                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_woo_checkout" class="rcfwc-checkbox" <?php if(get_option('rcfwc_woo_checkout')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'WooCommerce Checkout', 'recaptcha-woo' ); ?></label>
                                </div>
                                <div class="rcfwc-checkbox-group" style="margin-left: 26px; margin-top: 8px;">
                                    <input type="checkbox" name="rcfwc_guest_only" class="rcfwc-checkbox" <?php if(get_option('rcfwc_guest_only')) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'Guest Checkout Only', 'recaptcha-woo' ); ?></label>
                                </div>
                            </div>

                            <div class="rcfwc-form-group">
                                <label class="rcfwc-form-label"><?php echo __( 'Widget Location on Checkout', 'recaptcha-woo' ); ?></label>
                                <select name="rcfwc_woo_checkout_pos" class="rcfwc-form-select">
                                    <option value="beforepay" <?php if (!get_option('rcfwc_woo_checkout_pos') || get_option('rcfwc_woo_checkout_pos') == "beforepay") { ?>selected<?php } ?>>
                                        <?php esc_html_e('Before Payment', 'recaptcha-woo'); ?>
                                    </option>
                                    <option value="afterpay" <?php if (get_option('rcfwc_woo_checkout_pos') == "afterpay") { ?>selected<?php } ?>>
                                        <?php esc_html_e('After Payment', 'recaptcha-woo'); ?>
                                    </option>
                                    <option value="beforebilling" <?php if (get_option('rcfwc_woo_checkout_pos') == "beforebilling") { ?>selected<?php } ?>>
                                        <?php esc_html_e('Before Billing', 'recaptcha-woo'); ?>
                                    </option>
                                    <option value="afterbilling" <?php if (get_option('rcfwc_woo_checkout_pos') == "afterbilling") { ?>selected<?php } ?>>
                                        <?php esc_html_e('After Billing', 'recaptcha-woo'); ?>
                                    </option>
                                </select>
                            </div>

							<?php if ( class_exists( 'WooCommerce' ) ) { ?>
								<?php $available_gateways = WC()->payment_gateways->get_available_payment_gateways(); ?>
								<?php if(!empty($available_gateways)) { ?>
									<div class="rcfwc-toggle-section">
										<div class="rcfwc-toggle-header" id="toggleButtonSkipMethods">
											<?php echo __('Payment Methods to Skip', 'recaptcha-woo'); ?>
											<span class="dashicons dashicons-arrow-down"></span>
										</div>
										<div class="rcfwc-toggle-content" id="toggleContentSkipMethods" style="display: none;">
											<p class="rcfwc-help-text">
												<?php echo __("If selected below, reCAPTCHA check will not be run for that specific payment method.", 'recaptcha-woo'); ?>
												<br/>
												<?php echo __("Useful for 'Express Checkout' payment methods compatibility.", 'recaptcha-woo'); ?>
											</p>

											<?php
											$selected_payment_methods = get_option('rcfwc_selected_payment_methods', array());
											if(!$selected_payment_methods) $selected_payment_methods = array();
											if(!empty($available_gateways)) { ?>
												<div class="rcfwc-payment-methods">
												<?php foreach ( $available_gateways as $gateway ) : ?>
													<div class="rcfwc-payment-method">
														<input type="checkbox" name="rcfwc_selected_payment_methods[]" class="rcfwc-checkbox"
														value="<?php echo esc_attr( $gateway->id ); ?>" <?php echo in_array( $gateway->id, $selected_payment_methods, true ) ? 'checked' : ''; ?> >
														<label><?php echo __("Skip:", 'recaptcha-woo'); ?> <?php echo esc_html( $gateway->get_title() ); ?></label>
													</div>
												<?php endforeach; ?>
												</div>
											<?php } ?>
										</div>
									</div>

									<script type="text/javascript">
										document.getElementById("toggleButtonSkipMethods").addEventListener("click", function() {
											var content = document.getElementById("toggleContentSkipMethods");
											var arrow = this.querySelector('.dashicons');
											if (content.style.display === "none") {
												content.style.display = "block";
												arrow.className = "dashicons dashicons-arrow-up";
											} else {
												content.style.display = "none";
												arrow.className = "dashicons dashicons-arrow-down";
											}
										});
									</script>
								<?php } ?>
							<?php } ?>

                        </div>
                    </div>

                    <div class="rcfwc-card">
                        <div class="rcfwc-card-header">
                            <h2><?php echo __( 'Other Settings', 'recaptcha-woo' ); ?></h2>
                        </div>
                        <div class="rcfwc-card-content">
                            <div class="rcfwc-form-group">
                                <div class="rcfwc-checkbox-group">
                                    <input type="checkbox" name="rcfwc_scripts_all" class="rcfwc-checkbox" <?php if(get_option('rcfwc_scripts_all', true)) { ?>checked<?php } ?>>
                                    <label class="rcfwc-form-label"><?php echo __( 'Load scripts on all pages?', 'recaptcha-woo' ); ?></label>
                                </div>
                          <p class="rcfwc-help-text">
                                    <?php echo __( 'If unchecked, scripts will only load on the WP Login, My Account, and Checkout pages.', 'recaptcha-woo' ); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php submit_button(__('Save Changes', 'recaptcha-woo'), 'primary rcfwc-submit-btn'); ?>
                </form>
            </div>

            <div class="rcfwc-sidebar">
                <div class="rcfwc-info-card">
                    <h3><?php echo __( 'About the Developer', 'recaptcha-woo' ); ?></h3>
                    <p style="margin: 0;"><?php echo __( '100% free plugin developed by', 'recaptcha-woo' ); ?> <a href="https://twitter.com/ElliotSowersby" target="_blank">Elliot Sowersby</a> (<a href="https://www.relywp.com/?utm_campaign=recaptcha-woo-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank">RelyWP</a>) 🙌</p>
                </div>

                <div class="rcfwc-info-card">
                    <h3><?php echo __( 'Support & Resources', 'recaptcha-woo' ); ?></h3>
                    <ul>
                        <li><a href="https://wordpress.org/support/plugin/recaptcha-woo/reviews/#new-post" target="_blank"><?php echo __( 'Leave a review', 'recaptcha-woo' ); ?> ⭐️⭐️⭐️⭐️⭐️</a></li>
                        <li><a href="https://wordpress.org/support/plugin/recaptcha-woo" target="_blank"><?php echo __( 'Get support on the community forums', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://www.paypal.com/donate/?hosted_button_id=RX28BBH7L5XDS" target="_blank"><?php echo __( 'Donate to support future development', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://translate.wordpress.org/projects/wp-plugins/recaptcha-woo/" target="_blank"><?php echo __( 'Translate into your language', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://github.com/elliotsowersby/recaptcha-woo" target="_blank"><?php echo __( 'View on GitHub', 'recaptcha-woo' ); ?></a></li>
                    </ul>
                </div>

                <div class="rcfwc-info-card">
                    <h3><?php echo __( 'Other Plugins', 'recaptcha-woo' ); ?></h3>
                    <ul>
                        <li><a href="https://wordpress.org/plugins/simple-cloudflare-turnstile/" target="_blank"><?php echo __( 'Simple Cloudflare Turnstile', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://couponaffiliates.com/?utm_campaign=recaptcha-woo-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php echo __( 'Coupon Affiliates for WooCommerce', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://relywp.com/plugins/tax-exemption-woocommerce/?utm_campaign=recaptcha-woo-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php echo __( 'Tax Exemption for WooCommerce', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://relywp.com/plugins/better-coupon-restrictions-woocommerce/?utm_campaign=recaptcha-woo-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php echo __( 'Better Coupon Restrictions', 'recaptcha-woo' ); ?></a></li>
                              <li><a href="https://relywp.com/plugins/advanced-customer-reports-woocommerce/?utm_campaign=recaptcha-woo-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php echo __( 'Advanced Customer Reports', 'recaptcha-woo' ); ?></a></li>
                        <li><a href="https://relywp.com/plugins/ai-text-to-speech/?utm_campaign=recaptcha-woo-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php echo __( 'AI Text to Speech', 'recaptcha-woo' ); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php } ?>