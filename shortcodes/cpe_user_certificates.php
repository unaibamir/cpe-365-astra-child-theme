<?php

add_shortcode("cpe_user_certificates", "cpe_user_certificates_callback");
function cpe_user_certificates_callback() {
    if (! is_user_logged_in()) {
            return '';
    }

    if (isset($atts['class'])) {
        $class = $atts['class'];
    } else {
        $class = 'certificate-list-container';
    }

    if (isset($atts['title'])) {
        $title = $atts['title'];
    } else {
        $title = __("");
    }

    if (isset($atts['no-cert-message'])) {
        $no_cert_message = $atts['no-cert-message'];
    } else {
        $no_cert_message = esc_html__('Complete courses to earn certificates', 'uncanny-learndash-toolkit');
    }

    $certificate_list = array();

    /* GET Certificates For Courses*/
    $args = array(
        'post_type'      => 'sfwd-courses',
        'posts_per_page' => - 1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $courses = get_posts($args);
    $key_row = 0;
    foreach ($courses as $course) {
        $certificate_id     = learndash_get_setting($course->ID, 'certificate');
        $certificate_object = get_post($certificate_id);

        if (! empty($certificate_object)) {
            $certificate_title = $course->post_title;
            $certificate_link  = learndash_get_course_certificate_link($course->ID);

            if ($certificate_link && '' !== $certificate_link) {
                //$certificate_list .= '<a target="_blank" href="' . $certificate_link . '">' . $certificate_title . '</a><br>';
                $certificate_list[$key_row]["link"] = $certificate_link;
                $certificate_list[$key_row]["title"] = $certificate_title;
                $key_row++;
            }
        }
    }

    /* GET Certificates for Quizzes*/
    $quiz_attempts = get_user_quiz_attempts();

    if (! empty($quiz_attempts)) {
        $quiz_attempts = array_reverse($quiz_attempts);

        foreach ($quiz_attempts as $k => $quiz_attempt) {
            if (isset($quiz_attempt['certificate'])) {
                $certificateLink     = $quiz_attempt['certificate']['certificateLink'];
                $quiz_title_fallback = ( isset($quiz_attempt['quiz_title']) ) ? $quiz_attempt['quiz_title'] : '';
                $quiz_title          = ! empty($quiz_attempt['post']->post_title) ? $quiz_attempt['post']->post_title : $quiz_title_fallback;

                if (! empty($certificateLink)) {
                    $meta               = get_post_meta($quiz_attempt['post']->ID, '_sfwd-quiz', true);
                    $certificate_id     = $meta['sfwd-quiz_certificate'];
                    $certificate_object = get_post($certificate_id);
                    $certificate_title  = $quiz_title;

                    //$certificate_list .= '<a target="_blank" href="' . esc_url( $certificateLink ) . '">' . $certificate_title . '</a><br>';
                    $certificate_list[$key_row]["link"] = $certificate_link;
                    $certificate_list[$key_row]["title"] = $certificate_title;
                    $key_row++;
                }
            }
        }
    }

    $certificate_list = apply_filters('certificate_list_shortcode', $certificate_list);

    ob_start();
    ?>
    <div class="learndash-wrapper cpe_user_certificates">
        <div id="ld-profile">
            <div class="ld-item-list ld-course-list"  style="margin-top:0;">
                <div class="ld-section-heading">
                    <h3><?php echo $title; ?></h3>
                </div>

                <div class="ld-item-list-items" id="ld-main-course-list">
                    <?php
                    if (!empty($certificate_list)) {
                        foreach ($certificate_list as $key => $certificate) {
                            ?>
                            <div class="ld-item-list-item ld-item-list-item-course ld-expandable learndash-complete" id="ld-course-list-item-29270">
                                <div class="ld-item-list-item-preview">


                                    <a href="<?php echo $certificate["link"]; ?>" class="ld-item-name" target="_blank">
                                        <div class="ld-status-icon ld-status-complete ld-secondary-background"><span class="ld-icon-checkmark ld-icon"></span></div>
                                        <span class="ld-course-title"><?php echo $certificate["title"]; ?></span>
                                    </a> <!--/.ld-course-name-->

                                    <div class="ld-item-details">

                                        <a class="ld-certificate-link" target="_blank" href="<?php echo $certificate["link"]; ?>" aria-label="Certificate">
                                            <span class="ld-icon ld-icon-certificate"></span>
                                        </a>
                                        
                                        <div class="ld-status ld-status-complete ld-secondary-background"><?php _e('Completed', 'learndash'); ?></div>

                                    </div> <!--/.ld-course-details-->

                                </div> <!--/.ld-course-preview-->
                            </div>
                            <?php
                        }
                    } else {
                        $alert = array(
                            'icon'    => 'alert',
                            'message' => __('No Certificates found', 'learndash'),
                            'type'    => 'warning',
                        );
                        learndash_get_template_part('modules/alert.php', $alert, true);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php

    $shortcode_html = ob_get_clean();

    return $shortcode_html;
}



function get_user_quiz_attempts(){
    $quiz_attempts = array();
    $current_user  = wp_get_current_user();

    if (empty($current_user->ID)) {
        return $quiz_attempts;
    }

    $user_id            = $current_user->ID;
    $quiz_attempts_meta = get_user_meta($user_id, '_sfwd-quizzes', true);
    $count              = 0;
    if (! ( empty($quiz_attempts_meta) || false === $quiz_attempts_meta )) {
        foreach ($quiz_attempts_meta as $quiz_attempt) {
            $quiz_attempt['post'] = get_post($quiz_attempt['quiz']);
            $c                    = learndash_certificate_details($quiz_attempt['quiz'], $user_id);
            if (get_current_user_id() == $user_id &&
                ! empty($c['certificateLink']) &&
                (
                ( isset($quiz_attempt['percentage']) &&
                  $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100
                )
                )
            ) {
                $quiz_attempt['certificate']          = $c;
                $quiz_attempt['certificate']['count'] = $count;
            }

            $quiz_attempts[] = $quiz_attempt;
            $count ++;
        }
    }

    return $quiz_attempts;
}