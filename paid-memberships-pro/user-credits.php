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
    $unlimited_credits    = get_pmpro_membership_level_meta($level_id, "cpe_unlimited_credits", true);
    $cpe_term       = get_option( "cpe_term", "CPE" );
    ?>

    <h3 class="topborder">CPE Credits Settings</h3>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row" valign="top"><label><?php _e( $cpe_term.' unlimited Credits', CPE_LANG);?>:</label></th>
                <td>
                    <input type="checkbox" id="unlimited_credits" name="unlimited_credits" value="yes" <?php checked( $unlimited_credits, "yes" ); ?>>
                    <p class="description"><?php _e("Please check if credits of this membership is unlimited.", CPE_LANG); ?></p>
                </td>
            </tr>
            <tr id="cpe_credits" class="<?php echo $unlimited_credits == "yes" ? "hidden" : ""; ?>">
                <th scope="row" valign="top"><label><?php _e( $cpe_term.' Credits', CPE_LANG);?>:</label></th>
                <td>
                    <input type="number" name="cpe_credits" value="<?php echo $cpe_credits; ?>" min="0" class="small-text">
                    <p class="description"><?php _e("Please enter the credits for this membership level. ", CPE_LANG); ?></p>
                </td>
            </tr>
        </tbody>
    </table>

    <script>
        jQuery("#unlimited_credits").click(function(){
            if( jQuery(this).prop("checked") == true ) {
                jQuery("#cpe_credits").hide();
            } else if( jQuery(this).prop("checked") == false ) {
                jQuery("#cpe_credits").show();
            }
        });
    </script>
    <?php
}

add_action('pmpro_save_membership_level', 'woo_pmp_level_save_cpe_fields');
function woo_pmp_level_save_cpe_fields($level_id)
{    
    $cpe_credits = sanitize_text_field( $_POST["cpe_credits"], "");
    $unlimited_credits = sanitize_text_field( $_POST["unlimited_credits"], "");
    update_pmpro_membership_level_meta( $level_id, 'cpe_credits', $cpe_credits);
    update_pmpro_membership_level_meta( $level_id, 'cpe_unlimited_credits', $unlimited_credits);
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
        update_user_meta( $user_id, "cpe_credits", '', '' );
    } else {
        $cpe_credits = get_pmpro_membership_level_meta( $level_id, 'cpe_credits', true );
        $unlimited_credits = get_pmpro_membership_level_meta( $level_id, 'cpe_unlimited_credits', true );

        if( $unlimited_credits == "yes" ) {
            update_user_meta( $user_id, "cpe_credits", "unlimited", '' );
        } else {
            $old_credits = get_user_meta( $user_id, 'cpe_credits', true );
            $old_credits = !empty($old_credits) ? $old_credits : 0;
            $total_credits = $old_credits + $cpe_credits;
            update_user_meta( $user_id, "cpe_credits", $total_credits, '' );
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

function cpe_get_user_credits( $user_id ) {
    global $wpdb;

    $user_credit = $wpdb->get_results( "SELECT * FROM `{$wpdb->base_prefix}user_credits` WHERE user_id = {$user_id}" );

    if( !empty($user_credit) ) {
        return $user_credit;
    } else {
        return false;
    }
}


function cpe_get_user_total_credits( $user_id ) {
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
            "user_id"   =>  $user_id,
            "post_id"   =>  $post_id,
            "credit"    =>  $credits
        )
    );
}

function cpe_user_started_courses( $user_id ) {
    global $wpdb;

    $user_started_courses = $wpdb->get_col( "SELECT `post_id` FROM `{$wpdb->base_prefix}user_credits` WHERE user_id = {$user_id} ORDER BY created_at DESC" );
    return $user_started_courses;
}

/**
 * Get user post credit access
 *
 * @since 2.1.0
 *
 * @param int $user_id user id.
 * @param int $post_id post id.
 * @return bool true or false
 */
function cpe_get_post_user_credits( $user_id, $post_id ) {
    global $wpdb;

    $user_access = $wpdb->get_row(
        $wpdb->prepare("SELECT *  FROM `{$wpdb->base_prefix}user_credits` WHERE `post_id` = %d AND `user_id` = %d LIMIT 0, 1", $post_id, $user_id)
    );

    if( !empty($user_access) ) {
        return true;
    }
    return false;
}

add_action( 'show_user_profile', 'cpe_user_credits_fields' );
add_action( 'edit_user_profile', 'cpe_user_credits_fields' );

function cpe_user_credits_fields( $user ) {

    $user_id            =   $user->ID;
    $cpe_term           =   get_option( "cpe_term", "CPE" );
    $user_total_credits =   get_user_meta( $user_id, "cpe_credits", true );
    $user_used_credits  =   cpe_get_user_total_credits( $user_id );
    $user_used_credits  =   !empty($user_used_credits) ? $user_used_credits : 0;

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


add_action( "wp", "check_log_user_credit" );
function check_log_user_credit() {

    global $post, $wp_query;

    if( !is_user_logged_in() ) {
        return;
    }

    if( is_admin() ) {
        return;
    }

    $user = wp_get_current_user();

    if ( in_array( 'administrator', (array) $user->roles ) ) {
        return;
    }

    $user_id        = get_current_user_id();
    $active_levels  = pmpro_getMembershipLevelsForUser( $user_id );
    
    $post_types     = array(
        'sfwd-courses',
        'sfwd-lessons',
        'sfwd-topic',
        'sfwd-assignment',
        'sfwd-quiz',
    );

    $account_page_id    = pmpro_getOption( "account_page_id" );
    $account_page       = get_page( $account_page_id );
    $redirect_page      = get_page_by_path( "{$account_page->post_name}/available-courses" );

    //$redirect_page = get_page_by_path( "my-account-2/available-courses" );
    

    if( isset($wp_query->query["post_type"], $_GET["cpe_access"]) && in_array( $wp_query->query["post_type"], $post_types ) && $_GET["cpe_access"] == "grant_access" ) {
        
        $course_id          = learndash_get_course_id( $post->ID );
        $course_credits     = get_post_meta($course_id, '_learndash_course_cpe_credits', true);
        $user_total_credits = get_user_meta( $user_id, "cpe_credits", true );

        if( empty($user_total_credits) || $user_total_credits == 0 || $user_total_credits == "unlimited" ) {
            cpe_add_user_post_credits( $user_id, $course_id, $course_credits );
            wp_safe_redirect( remove_query_arg( "cpe_access" ) );
            exit;
            return;
        }

        $user_used_credits  = cpe_get_user_total_credits( $user_id );
        $remaining_credits  = $user_total_credits - $user_used_credits;
        $has_access         = cpe_check_user_post_credits( $user_id, $course_id );

        if( !$has_access ) {
            if( $remaining_credits >= $course_credits ) {
                cpe_add_user_post_credits( $user_id, $course_id, $course_credits );
                wp_safe_redirect( remove_query_arg( "cpe_access" ) );
                exit;
            } else {
                wp_safe_redirect( add_query_arg(array("cpe_status" => "restricted"), get_permalink( $redirect_page )) );
                exit;
            }
        }
    }
}


add_action( 'admin_post_remove_user_credit', 'remove_user_credit' );
function remove_user_credit() {

    global $wpdb;

    $return_url = wp_get_referer();

    if( wp_verify_nonce( $_GET['_wpnonce'], 'remove_user_credit' ) ) {

        $deleted = $wpdb->delete( $wpdb->base_prefix . "user_credits", 
            array(
                'user_id'   =>  $_GET["user_id"],
                'post_id'   =>  $_GET["course_id"],
            )
        );

        if( $deleted ) {
            $return_url = add_query_arg( 'cpe_status', 'course_removed', $return_url );
        }
    }

    wp_safe_redirect( $return_url );
    exit;
}