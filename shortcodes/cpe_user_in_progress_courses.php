<?php

add_shortcode("cpe_user_in_progress_courses", "cpe_user_in_progress_courses");
function cpe_user_in_progress_courses($atts) {
    global $learndash_shortcode_used;
    
    // Add check to ensure user it logged in
    if (!is_user_logged_in()) {
        return '';
    }
    
    $defaults = array(
        'user_id'               =>  get_current_user_id(),
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

    $current_user = get_user_by('id', $atts['user_id']);
    //$user_courses = ld_get_mycourses( $atts['user_id'], $atts );

    $args = array(
        'orderby'   =>  'date'
    );

    $posts_in_courses           =   array();
    //$user_enrolled_courses      =   learndash_user_get_enrolled_courses($atts['user_id'], $args);
    $user_started_courses       =   cpe_user_started_courses( $atts['user_id'] );
    $user_enrolled_courses      =   $user_started_courses;
    
    ob_start();

    ?>
    <div class="learndash-wrapper cpe_user_in-progress_courses">
    <?php

    if (empty($user_enrolled_courses)) {
        $alert = array(
            'icon'    => 'alert',
            'message' => __( 'No Courses Available.', 'learndash'),
            'type'    => 'warning',
        );
        
        return learndash_get_template_part('modules/alert.php', $alert, false );
    }

    foreach ($user_enrolled_courses as $key => $enrolled_course_id) {
        $progress = learndash_course_progress(array(
            'user_id'   => $atts['user_id'],
            'course_id' => $enrolled_course_id,
            'array'     => true
        ));

        if ($progress['percentage'] >= 0 && $progress['percentage'] != 100) {
            if (sfwd_lms_has_access($enrolled_course_id, $atts['user_id'])) {
                array_push($posts_in_courses, $enrolled_course_id);
            }
        }
    }

    $user_courses   =   $posts_in_courses;

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

    if( isset($_GET["cpe_status"]) && $_GET["cpe_status"] == "course_removed" ) {

        $alert = array(
            'icon'    => 'alert',
            'message' => __( 'Course credits has been removed.', 'learndash'),
            'type'    => 'warning',
        );
        learndash_get_template_part('modules/alert.php', $alert, true);
    }
    
    $courses =  SFWD_LMS::get_template(
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

    echo $courses;

    ?>
    </div>
    <?php

    $shortcode_html = ob_get_clean();
    return $shortcode_html;
}