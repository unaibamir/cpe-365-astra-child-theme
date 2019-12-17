<?php

add_action( "init", "cpe_user_credit_table" );
function cpe_user_credit_table() {
	global $wpdb;

	if( isset($_GET["cpe_create_table"]) ) {
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$wpdb->base_prefix}user_credits` (
		`id` INT(11) NOT NULL AUTO_INCREMENT ,
		`user_id` INT(11) NOT NULL ,
		`post_id` INT(11) NOT NULL ,
		`credit` INT(11) NOT NULL ,
		`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
		PRIMARY KEY  (`id`)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

}

add_action("admin_post_cpe_update_user_profile", "cpe_update_user_profile_callbak");
function cpe_update_user_profile_callbak()
{

    $user_cpe_values = array();

    if (isset($_POST["action"]) && $_POST["action"] == "cpe_update_user_profile") {
        $counter = 0;

        $field_types = array("cpa", "ea", "asfr", "cfp");
        $user_input = $_POST["user_credentials"];

        foreach ($field_types as $type) {
            $user_cpe_values[$type]["checked"] = isset($user_input["type"][$type]) ? $user_input["type"][$type] : "no";
            $user_cpe_values[$type]["value"] = isset($user_input["value"]["{$type}_value"]) ? $user_input["value"]["{$type}_value"] : "" ;
        }
        
        update_user_meta($_POST['user_id'], "_cpe_values", $user_cpe_values, '');

        wp_safe_redirect($_POST["_wp_http_referer"]);
        exit;
    }
}


add_action("pmpro_membership_level_after_other_settings", "woo_pmp_level_add_cpe_fields");
function woo_pmp_level_add_cpe_fields()
{
    $level_id       = $_REQUEST['edit'];
    $cpe_credits    = get_pmpro_membership_level_meta($level_id, "cpe_credits", true);
    $cpe_term       =   get_option( "cpe_term", "CPE" );
    ?>

    <h3 class="topborder">CPE Credits Settings</h3>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row" valign="top"><label><?php _e( $cpe_term.' Credits', CPE_LANG);?>:</label></th>
                <td>
                    <input type="number" name="cpe_credits" value="<?php echo $cpe_credits; ?>" min="0" class="small-text">
                </td>
            </tr>
        </tbody>
    </table>
    <?php
}

add_action('pmpro_save_membership_level', 'woo_pmp_level_save_cpe_fields');
function woo_pmp_level_save_cpe_fields($level_id)
{
    $cpe_credits = sanitize_text_field($_POST["cpe_credits"], "");
    update_pmpro_membership_level_meta($level_id, 'cpe_credits', $cpe_credits);
}


add_action( "pmpro_after_change_membership_level", "add_user_credits_memberships", 999, 3 );

/**
 * Update user course access on user memberhip level change
 * 
 * @param  int $level        ID of new membership level
 * @param  int $user_id      ID of a WP_User
 * @param  int $cancel_level ID of old membership level
 */
function add_user_credits_memberships( $level_id, $user_id, $cancel_level ) {
	// Add approval check if PMPro approval addon is active
	if ( class_exists( 'PMPro_Approvals' ) ) {
		if ( PMPro_Approvals::requiresApproval( $level_id ) && ! PMPro_Approvals::isApproved( $user_id, $level_id ) ) {
			return;
		}
	}

	if( !empty($cancel_level) ) {
		update_user_meta( $user_id, "cpe_credits", 0, '' );
	} else {
		$cpe_credits = get_pmpro_membership_level_meta( $level_id, 'cpe_credits', true );
		update_user_meta( $user_id, "cpe_credits", $cpe_credits, '' );
	}

}



add_action( "wp", "check_log_user_credit" );
function check_log_user_credit() {

	global $post, $wp_query;

	if( !is_user_logged_in() ) {
		return;
	}

    if( is_admin() ) {
        return;
    }

	$user_id 		= get_current_user_id();
	$active_levels 	= pmpro_getMembershipLevelsForUser( $user_id );
	
	$post_types 	= array(
        'sfwd-courses',
        'sfwd-lessons',
        'sfwd-topic',
        'sfwd-assignment',
        'sfwd-quiz',
    );

    $redirect_page = get_page_by_path( "my-account-2/available-courses" );
    

    if( isset($wp_query->query["post_type"]) && in_array( $wp_query->query["post_type"], $post_types ) ) {

    	$course_id 			= learndash_get_course_id( $post->ID );
    	$course_credits 	= get_post_meta($course_id, '_learndash_course_cpe_credits', true);
    	$has_access 		= cpe_check_user_post_credits( $user_id, $course_id );
    	$user_used_credits 	= cpe_check_user_credits( $user_id );
    	$user_total_credits = get_user_meta( $user_id, "cpe_credits", true );
    	$remaining_credits 	= $user_total_credits - $user_used_credits;

    	if( !$has_access ) {
    		if( $remaining_credits >= $course_credits ) {
    			cpe_add_user_post_credits( $user_id, $course_id, $course_credits );
    		} else {
    			wp_safe_redirect( add_query_arg(array("cpe_status" => "restricted"), get_permalink( $redirect_page )) );
    		}
    	}
    }
}


function cpe_check_user_post_credits( $user_id, $post_id ) {
	global $wpdb;
	$has_credit = false;
	$user_credit = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}user_credits WHERE user_id = {$user_id} AND post_id = {$post_id}" );

	if( !empty($user_credit) ) {
		$has_credit = true;
	}

	return $has_credit;
}


function cpe_check_user_credits( $user_id ) {
	global $wpdb;

	$user_credit = $wpdb->get_col( "SELECT SUM(credit) FROM `{$wpdb->base_prefix}user_credits` WHERE user_id = {$user_id}" );

	if( !empty($user_credit) ) {
		return $user_credit[0];
	} else {
		return 0;
	}

}


function cpe_add_user_post_credits( $user_id, $post_id, $credits ) {
	global $wpdb;

	$wpdb->insert(
		"{$wpdb->base_prefix}user_credits",
		array(
			"user_id" 	=> $user_id,
			"post_id"	=>	$post_id,
			"credit"	=>	$credits
		)
	);
}

add_action( 'show_user_profile', 'cpe_user_credits_fields' );
add_action( 'edit_user_profile', 'cpe_user_credits_fields' );

function cpe_user_credits_fields( $user ) {

	$user_id 			=	$user->ID;
	$user_total_credits = 	get_user_meta( $user_id, "cpe_credits", true );
	$user_used_credits 	= 	cpe_check_user_credits( $user_id );
    $cpe_term           =   get_option( "cpe_term", "CPE" );

	?>
	<br>
    <h3><?php _e("User ".$cpe_term." Credits", CPE_LANG); ?></h3>

    <table class="form-table">
    <tr>
        <th><label for="user_credits"><?php _e("Total Credits"); ?></label></th>
        <td>
            <input type="text" name="user_credits" id="user_credits" value="<?php echo $user_total_credits; ?>" class="regular-text" /><br />
            <span class="description"><?php _e("User's total ".$cpe_term." credits."); ?></span>
        </td>
    </tr>

    <tr>
        <th><label for="user_credits"><?php _e("Used Credits"); ?></label></th>
        <td>
            <strong><?php echo $user_used_credits; ?></strong><br>
            <span class="description"><?php _e("User's used ".$cpe_term." credits."); ?></span>
        </td>
    </tr>
    </table>
<?php }


add_action( 'personal_options_update', 'save_user_credits_profile_fields' );
add_action( 'edit_user_profile_update', 'save_user_credits_profile_fields' );

function save_user_credits_profile_fields( $user_id ) {
    if ( !current_user_can( 'edit_user', $user_id ) ) { 
        return false; 
    }
    update_user_meta( $user_id, 'cpe_credits', $_POST['user_credits'] );
}