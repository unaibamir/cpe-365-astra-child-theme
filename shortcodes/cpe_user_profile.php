<?php


add_shortcode("cpe_user_profile", "cpe_user_profile_callback");
function cpe_user_profile_callback(){
    global $post;

    $user_id = get_current_user_id();
    if ($user_id == 0) {
        return;
    }

    $user = wp_get_current_user();

    $reset_link = wp_lostpassword_url(get_permalink($post));

    $cpe_values = get_user_meta($user_id, '_cpe_values', true);
    
    ob_start();

    ?>
    <div class="container-area cpe-user-profile-page">
        <div class="ast-row">
            <div class="ast-col-md-8 ast-col-sm-10 ast-col-xs-12">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tr>
                            <th><?php _e("First Name", CPE_LANG); ?></th>
                            <td><?php echo $user->first_name; ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Last Name", CPE_LANG); ?></th>
                            <td><?php echo $user->last_name; ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Email", CPE_LANG); ?></th>
                            <td><?php echo $user->user_email; ?></td>
                        </tr>
                        <tr>
                            <th><?php _e("Login Username", CPE_LANG); ?></th>
                            <td><?php echo $user->user_login; ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="<?php echo $reset_link; ?>">
                                    <?php _e("Reset Login Password", CPE_LANG); ?>
                                </a>
                            </td>
                        </tr>
                    </table>

                    <!-- <form  class="form-horizontal cpa_user_fields" action="<?php echo admin_url("admin-post.php") ?>" method="POST">
                        
                        <table class="table table-borderless">
                            <tr>
                                <td colspan="2">
                                    <p><?php _e("Please select your credentials", CPE_LANG); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="yes" name="user_credentials[type][cpa]" <?php checked($cpe_values["cpa"]["checked"], "yes"); ?>>
                                            <?php _e("CPA", CPE_LANG); ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <label for="cpa_state" class="control-label">State</label>
                                    <input type="text" class="form-control" id="cpa_state" name="user_credentials[value][cpa_value]" value="<?php echo $cpe_values["cpa"]["value"]; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="yes" name="user_credentials[type][ea]" <?php checked($cpe_values["ea"]["checked"], "yes"); ?>>
                                            <?php _e("EA", CPE_LANG); ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <label for="ea_ptin_number" class="control-label">PTIN No.</label>
                                    <input type="text" class="form-control" id="ea_ptin_number" name="user_credentials[value][ea_value]" value="<?php echo $cpe_values["ea"]["value"]; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="yes" name="user_credentials[type][asfr]" <?php checked($cpe_values["asfr"]["checked"], "yes"); ?>>
                                            <?php _e("ASFR", CPE_LANG); ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <label for="asfr_ptin_number" class="control-label">PTIN No.</label>
                                    <input type="text" class="form-control" id="asfr_ptin_number" name="user_credentials[value][asfr_value]" value="<?php echo $cpe_values["asfr"]["value"]; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="yes" name="user_credentials[type][cfp]" <?php checked($cpe_values["cfp"]["checked"], "yes"); ?>>
                                            <?php _e("CFP", CPE_LANG); ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <label for="cfp_number" class="control-label">CFP No.</label>
                                    <input type="text" class="form-control" id="cfp_number" name="user_credentials[value][cfp_value]" value="<?php echo $cpe_values["cfp"]["value"]; ?>">
                                </td>
                            </tr>
                        </table>
                    
                        <input type="submit" name="submit" value="<?php _e("Update", CPE_LANG); ?>" class="btn btn-primary">
                        <input type="hidden" name="action" value="cpe_update_user_profile">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <?php wp_nonce_field("cpe_user_profile_action", 'cpe_user_profile_wpnonce'); ?>
                    </form> -->
                </div>
            </div>
        </div>
    </div>
    <?php

    $shortcode_html = ob_get_clean();

    return $shortcode_html;
}