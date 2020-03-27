<?php

function cpe_credits_info_callback( $atts = array() ) {
	
	// Add check to ensure user it logged in
    if (!is_user_logged_in()) {
        return '';
    }

    $user_id 				      = get_current_user_id();
    $cpe_term             = get_option( "cpe_term", "CPE" );
    $user_total_credits 	= get_user_meta( $user_id, 'cpe_credits', true );

    if( empty($user_total_credits) ) {
        $user_total_credits = 0;
    }

    $user_credits             = cpe_get_user_credits( $user_id );
    $user_used_credits        = cpe_get_user_total_credits( $user_id );
    
    if( $user_total_credits && $user_used_credits )  {
        $remaining_credits        = max( $user_total_credits - $user_used_credits, 0);
    } else {
        $remaining_credits        = 0;
    }
    
    ob_start();

    ?>

    <div class="ast-container-fluids">
       <div class="ast-row">
           <div class="ast-col-lg-4 ast-col-md-4 ast-col-sm-12 ast-col-xs-12">
                <div class="aligncenter">
                   <h3><?php echo __( sprintf("Subscription %s", "Credits") ); ?></h3>
                   <p><span><?php echo $user_total_credits; ?></span></p>
                </div>
           </div>
           <div class="ast-col-lg-4 ast-col-md-4 ast-col-sm-12 ast-col-xs-12">
                <div class="aligncenter">
                   <h3><?php echo __( sprintf("Used %s", "Credits") ); ?></h3>
                   <p><span><?php echo $user_used_credits; ?></span></p>
                </div>
           </div>
           <div class="ast-col-lg-4 ast-col-md-4 ast-col-sm-12 ast-col-xs-12">
                <div class="aligncenter">
                   <h3><?php echo __( sprintf("Remaining %s", "Credits") ); ?></h3>
                   <?php echo $remaining_credits; ?>
                </div>
           </div>
       </div>
   </div>

    <?php

    $shortcode_html = ob_get_clean();

    return $shortcode_html;
}

add_shortcode( 'cpe_credits_info', 'cpe_credits_info_callback' );