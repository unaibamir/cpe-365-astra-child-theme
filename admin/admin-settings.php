<?php


add_action('admin_menu', 'wooninjas_cpe_settings_menu');

function wooninjas_cpe_settings_menu() {
    add_submenu_page(
        'options-general.php',
        __( "CPE365 Site Option", CPE_LANG ),
        __( "CPE365 Site Option", CPE_LANG ),
        'manage_options',
        'cpe-settings',
        'wooninjas_cpe_settings_menu_callback'
    );
}

function wooninjas_cpe_settings_menu_callback() {

	$billing_info 		= get_option( "cpe_billing_info", "" );
	$cpe_term 			= get_option( "cpe_term", "CPE" );
	$show_credentials 	= get_option( "show_credentials", "no" );

	?>
	<div class="wrap">
		<h1><?php _e( "CPE365 Site Option", CPE_LANG ); ?></h1>

		<form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="POST">
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php _e("Billing Information", CPE_LANG); ?></th>
						<td>
							<div style="max-width: 600px;">
								<?php
								wp_editor( $billing_info, "cpe_billing_info", array(
									"media_buttons" => false, 
									"teeny" => true, 
									"quicktags" => false, 
									"textarea_rows" => 7, 
									"textarea_name" => "cpe_billing_info"
								) );
								?>
							</div>
						</td>
					</tr>

					<tr>
						<th><?php _e("CPE Term", CPE_LANG); ?></th>
						<td>
							<input type="text" name="cpe_term" class="regular-text" value="<?php echo $cpe_term; ?>">
							<p class="description"><?php _e( "The term which will displayed all over website, i.e CPE or PDU. Default is CPE." ); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php _e("Show Credentials", CPE_LANG); ?></th>
						<td>
							<input type="checkbox" name="show_credentials" value="yes" <?php checked( $show_credentials, "yes" ); ?>>
							<p class="description"><?php _e( "Show credentials on my account or profile page." ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<?php wp_nonce_field( "cpe_settings_action", 'cpe_settings_wpnonce' ); ?>
			<input type="hidden" name="action" value="cpe_admin_settings">
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
		</form>
	</div>
	<?php
}



add_action( "admin_post_cpe_admin_settings", "save_cpe_admin_settings" );
function save_cpe_admin_settings() {
	if( isset($_POST["action"]) && $_POST["action"] == "cpe_admin_settings" ) {

		update_option( "cpe_billing_info", $_POST["cpe_billing_info"], false );
		update_option( "cpe_term", $_POST["cpe_term"], false );
		update_option( "show_credentials", $_POST["show_credentials"], false );
		
		$redirect_url = $_POST["_wp_http_referer"];
		$redirect_url = add_query_arg("setting-updated" , "true", $redirect_url);
		wp_safe_redirect( $redirect_url );
		exit;
	}

}