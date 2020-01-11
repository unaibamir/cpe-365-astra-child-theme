<?php


add_shortcode("cpe_user_available_courses", "cpe_user_available_courses");
function cpe_user_available_courses($atts) {
    global $learndash_shortcode_used;
    
    // Add check to ensure user it logged in
    if (!is_user_logged_in()) {
        return '';
    }

    $user_id = get_current_user_id();
    
    $defaults = array(
        'user_id'               =>  $user_id,
        'per_page'              =>  false,
        'order'                 => 'DESC',
        'orderby'               => 'ID',
        'course_points_user'    => 'no',
        'expand_all'            => false,
        'profile_link'          => 'no',
        'show_header'           => 'no',
        'show_quizzes'          => 'no',
        'show_search'           => 'no',
        'search'                => '',
        'title'                 => ''
    );

    $atts = wp_parse_args($atts, $defaults);

    if (( strtolower($atts['expand_all']) == 'yes' ) || ( $atts['expand_all'] == 'true' ) || ( $atts['expand_all'] == '1' )) {
        $atts['expand_all'] = true;
    } else {
        $atts['expand_all'] = false;
    }

    
    if (( strtolower($atts['show_header']) == 'yes' ) || ( $atts['show_header'] == 'true' ) || ( $atts['show_header'] == '1' )) {
        $atts['show_header'] = 'yes';
    } else {
        $atts['show_header'] = false;
    }

    if (( strtolower($atts['show_search']) == 'yes' ) || ( $atts['show_search'] == 'true' ) || ( $atts['show_search'] == '1' )) {
        $atts['show_search'] = 'yes';
    } else {
        $atts['show_search'] = false;
    }

    if (( strtolower($atts['course_points_user']) == 'yes' ) || ( $atts['course_points_user'] == 'true' ) || ( $atts['course_points_user'] == '1' )) {
        $atts['course_points_user'] = 'yes';
    } else {
        $atts['course_points_user'] = false;
    }

    if ($atts['per_page'] === false) {
        $atts['per_page'] = $atts['quiz_num'] = LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Section_General_Per_Page', 'per_page');
    } else {
        $atts['per_page'] = intval($atts['per_page']);
    }

    if ($atts['per_page'] > 0) {
        $atts['paged'] = 1;
    } else {
        unset($atts['paged']);
        $atts['nopaging'] = true;
    }

    if (( strtolower($atts['profile_link']) == 'yes' ) || ( $atts['profile_link'] == 'true' ) || ( $atts['profile_link'] == '1' )) {
        $atts['profile_link'] = true;
    } else {
        $atts['profile_link'] = false;
    }


    if (( strtolower($atts['show_quizzes']) == 'yes' ) || ( $atts['show_quizzes'] == 'true' ) || ( $atts['show_quizzes'] == '1' )) {
        $atts['show_quizzes'] = true;
    } else {
        $atts['show_quizzes'] = false;
    }
    

    if (( isset($_GET['ld-profile-search']) ) && ( ! empty($_GET['ld-profile-search']) )) {
        $atts['search'] = esc_attr($_GET['ld-profile-search']);
    }
    
    $atts = apply_filters('learndash_profile_shortcode_atts', $atts);

    if (isset($atts['search'])) {
        $atts['s'] = $atts['search'];
        unset($atts['search']);
    }

    if (empty($atts['user_id'])) {
        return;
    }

    $current_user   = get_user_by('id', $atts['user_id']);
    $user_courses   = ld_get_mycourses($atts['user_id'], $atts);
    $date_format    = get_option( 'date_format' );
    $course_tax     = 'ld_course_category';
    $course_terms   = wp_get_object_terms($user_courses, $course_tax);
    $cpe_term       = get_option( "cpe_term", "CPE" );
    $total_credits  = get_user_meta( $user_id, 'cpe_credits', true );
    $used_credits   = cpe_get_user_credits( $user_id );
    $limit_message  = get_option( "credit_limit_message", "Either upgrade your memberships or select courses with less credits" );
    ob_start();

    ?>
    <div class="learndash-wrapper cpe_user_available_courses">
        <div class="ld-item-list ld-course-list">
            <div class="ld-item-list-items" id="ld-main-course-list" data-ld-expand-list="true">
    <?php

    if( isset($_GET["cpe_status"]) ) {
        $limit_message = str_replace("%cpe_term%", $cpe_term, $limit_message);
        $limit_message = str_replace("%total_credits%", $total_credits, $limit_message);
        $limit_message = str_replace("%used_credits%", $used_credits, $limit_message);
        $alert = array(
            'icon'    => 'alert',
            'message' => __( $limit_message, 'learndash'),
            'type'    => 'warning',
        );
        learndash_get_template_part('modules/alert.php', $alert, true);

        ?>
        <div class="pmpro_actionlinks" style="margin-bottom: 10px;">
            <a id="pmpro_actionlink-levels" href="<?php echo home_url("/membership-packages/"); ?>">View all Membership Options</a>
        </div>
        <?php
    }

    if( !empty( $course_terms ) ) {
        foreach ($course_terms as $term) {


            $term_id    = $term->term_id;
            $term_title = $term->name;
            $term_totel = $term->count;


            ?>
            <div class="ld-item-list-item ld-item-list-item-course ld-expandable learndash-incomplete" id="ld-course-list-item-<?php echo $term_id; ?>">
                <div class="ld-item-list-item-preview">
                    <a href="javascript:void(0);" class="ld-item-name ld-expand-button" data-ld-expands="ld-course-list-item-<?php echo $term_id; ?>">
                        <div class="ld-status-icon ld-status-incomplete"></div>
                        <span class="ld-course-title"><?php echo $term_title; ?></span>
                    </a> <!--/.ld-course-name-->

                    <div class="ld-item-details">
                        <div class="ld-expand-button ld-primary-background ld-compact ld-not-mobile" data-ld-expands="ld-course-list-item-<?php echo $term_id; ?>">
                            <span class="ld-icon-arrow-down ld-icon"></span>
                        </div> <!--/.ld-expand-button-->

                        <div class="ld-expand-button ld-button-alternate ld-mobile-only" data-ld-expands="ld-course-list-item-<?php echo $term_id; ?>">
                            <span class="ld-icon-arrow-down ld-icon"></span>
                            <span class="ld-text ld-primary-color">Expand</span>
                        </div> <!--/.ld-expand-button-->

                    </div> <!--/.ld-course-details-->
                </div> <!--/.ld-course-preview-->

                <div class="ld-item-list-item-expanded" style="padding: 0px 20px 0px 20px;">

                
                    <?php

                    $term_courses = get_posts(
                        array(
                            'post_type'     =>  'sfwd-courses',
                            'post_status'   =>  'publish',
                            'posts_per_page'=>  -1,
                            'post__in'      =>  $user_courses,
                            'tax_query'     => array(
                                array(
                                    'taxonomy' => $course_tax,
                                    'field'    => 'term_id',
                                    'terms'    => $term_id,
                                ),
                            ),
                        )
                    );

                    //$term_courses = array();
                    if( !empty( $term_courses ) ) {
                        ?>
                        <div class="table-responsive courses-list list" style="margin: 10px 0 20px 0;">
                            <table class="table table-bordered table-striped course-list-table">
                                <thead>
                                    <tr>
                                        <th class="ast-col-lg-7">Course Name</th>
                                        <th><?php echo $cpe_term; ?></th>
                                        <th>Progress</th>
                                        <!-- <th class="ast-col-md-2">Available Date</th> -->
                                        <th class="ast-col-md-2">Access</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($term_courses as $term_course) {

                                    $course_id      =   $term_course->ID;

                                    $course_cpe     =   get_post_meta($course_id, "_learndash_course_cpe_credits", true);
                                    $course_cpe     =   !empty($course_cpe) ? $course_cpe : "0.00";
                                    $progress       =   learndash_course_progress( array(
                                        'user_id'   =>  $user_id,
                                        'course_id' =>  $course_id,
                                        'array'     =>  true
                                    ));

                                    $course_access  =   get_user_meta( $user_id, "course_{$course_id}_access_from", true );

                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo get_permalink( $term_course ); ?>">
                                                <?php echo get_the_title( $term_course ); ?>
                                            </a>
                                        </td>
                                        <td><?php echo $course_cpe;  ?></td>
                                        <td><?php echo @$progress['percentage']; ?>%</td>
                                        <!-- <td><?php echo date_i18n( "m/d/Y", $course_access ); ?></td> -->
                                        <td>
                                            <a href="<?php echo get_permalink( $term_course ); ?>" class="course-buy-btn">
                                                <?php _e("View Course"); ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                    } else {
                        $alert = array(
                            'icon'    => 'alert',
                            'message' => __('No Courses found', 'learndash'),
                            'type'    => 'error',
                        );
                        learndash_get_template_part('modules/alert.php', $alert, true);
                    }

                    ?>
                </div> <!--/.ld-course-list-item-expanded-->

            </div> <!--/.ld-item-list-item-->
            <?php

        }
    }

    ?>
            </div>
        </div>
    </div>
    <?php


    $shortcode_html = ob_get_clean();
    return $shortcode_html;

    $usermeta = get_user_meta($atts['user_id'], '_sfwd-quizzes', true);
    $quiz_attempts_meta = empty($usermeta) ? false : $usermeta;
    $quiz_attempts = array();
    
    $profile_pager = array();
    
    if (( isset($atts['per_page']) ) && ( intval($atts['per_page']) > 0 )) {
        $atts['per_page'] = intval($atts['per_page']);
            
        if (( isset($_GET['ld-profile-page']) ) && ( !empty($_GET['ld-profile-page']) )) {
            $profile_pager['paged'] = intval($_GET['ld-profile-page']);
        } else {
            $profile_pager['paged'] = 1;
        }
        
        $profile_pager['total_items'] = count($user_courses);
        $profile_pager['total_pages'] = ceil(count($user_courses) / $atts['per_page']);
        
        $user_courses = array_slice($user_courses, ( $profile_pager['paged'] * $atts['per_page'] ) - $atts['per_page'], $atts['per_page'], false);
    }
    
    $learndash_shortcode_used = true;

    ob_start();

    ?>
    <div class="learndash-wrapper">
        <div class="ld-item-list ld-course-list">
            <div class="ld-item-list-items" id="ld-main-course-list" data-ld-expand-list="true">

                <div class="ld-item-list-item ld-item-list-item-course ld-expandable learndash-incomplete" id="ld-course-list-item-29161">
                    <div class="ld-item-list-item-preview">
                        <a href="javascript:void(0);" class="ld-item-name">
                            <span class="ld-course-title">Tax Cuts and Jobs Act – Individual Tax Reform (Video Course)</span>
                        </a> <!--/.ld-course-name-->

                        <div class="ld-item-details">
                            <div class="ld-expand-button ld-primary-background ld-compact ld-not-mobile" data-ld-expands="ld-course-list-item-29161">
                                <span class="ld-icon-arrow-down ld-icon"></span>
                            </div> <!--/.ld-expand-button-->

                            <div class="ld-expand-button ld-button-alternate ld-mobile-only" data-ld-expands="ld-course-list-item-29161">
                                <span class="ld-icon-arrow-down ld-icon"></span>
                                <span class="ld-text ld-primary-color">Expand</span>
                            </div> <!--/.ld-expand-button-->

                        </div> <!--/.ld-course-details-->
                    </div> <!--/.ld-course-preview-->

                    <div class="ld-item-list-item-expanded" style="padding: 0px 20px 0px 20px;">

                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, quas dignissimos facilis tenetur laboriosam assumenda! Quo a inventore consectetur quas saepe molestiae, numquam animi possimus hic, optio dolor illum omnis.</p>

                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, quas dignissimos facilis tenetur laboriosam assumenda! Quo a inventore consectetur quas saepe molestiae, numquam animi possimus hic, optio dolor illum omnis.</p>

                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Totam, quas dignissimos facilis tenetur laboriosam assumenda! Quo a inventore consectetur quas saepe molestiae, numquam animi possimus hic, optio dolor illum omnis.</p>

                    </div> <!--/.ld-course-list-item-expanded-->

                </div> <!--/.ld-item-list-item-->



            </div>
        </div>
    </div>
    <?php
    $shortcode_html = ob_get_clean();

    //return $shortcode_html;
    
    return SFWD_LMS::get_template(
        'profile',
        array(
            'user_id'           =>  $atts['user_id'],
            'quiz_attempts'     =>  $quiz_attempts,
            'current_user'      =>  $current_user,
            'user_courses'      =>  $user_courses,
            'shortcode_atts'    =>  $atts,
            'profile_pager'     =>  $profile_pager
        )
    );
}