<?php

$course      = get_post( $course_id);
$course_link = get_permalink( $course_id );

$progress = learndash_course_progress( array(
    'user_id'   => $user_id,
    'course_id' => $course_id,
    'array'     => true
) );

global $post;
$page_content = $post->post_content;

$status = ( $progress['percentage'] == 100 ) ? 'completed' : 'notcompleted';

if( $progress['percentage'] > 0 && $progress['percentage'] !== 100 ) {
    $status = 'progress';
}

$course_class = apply_filters( 'learndash-course-row-class',
                                'ld-item-list-item ld-item-list-item-course ld-expandable ' . ( $progress['percentage'] == 100 ? 'learndash-complete' : 'learndash-incomplete' ), $course, $user_id ); ?>

<div class="<?php echo esc_attr($course_class); ?>" id="<?php echo esc_attr( 'ld-course-list-item-' . $course_id ); ?>">
    <div class="ld-item-list-item-preview">


        <a href="<?php echo esc_url( get_the_permalink($course_id) ); ?>" class="ld-item-name">
            <?php learndash_status_icon( $status, get_post_type(), null, true ); ?>
            <span class="ld-course-title"><?php echo esc_html( get_the_title($course_id) ); ?></span>
        </a> <!--/.ld-course-name-->

        <div class="ld-item-details">

            <?php
            $certificateLink = learndash_get_course_certificate_link( $course->ID, $user_id );
            if ( !empty( $certificateLink ) ): ?>
                <a class="ld-certificate-link" target="_blank" href="<?php echo esc_attr($certificateLink); ?>" aria-label="<?php esc_attr_e( 'Certificate', 'learndash' ); ?>"><span class="ld-icon ld-icon-certificate"></span></span></a>
            <?php endif; ?>

            <?php
            if( has_shortcode( $page_content, "cpe_user_completed_courses" ) ) {
                $course_complete_date = learndash_user_get_course_completed_date( $user_id, $course_id );
                if( $course_complete_date ) {
                    $status = "cpe_completed_" . $course_complete_date;
                }
            }
            ?>
            
            <?php
            if( has_shortcode( $page_content, "cpe_user_in_progress_courses" ) ) {

                $cpe_credits    = get_post_meta( $course_id, "_learndash_course_cpe_credits", true );
                $cpe_term       = get_option( "cpe_term", "CPE" );
                if( !empty($cpe_credits) ) {
                    //$bubble = '<div class="ld-status ld-status-complete ld-secondary-background">' . __( "{$cpe_term} Credits: " . $cpe_credits ) . '</div>';
                    $bubble = '<div class="ld-status ld-status-complete ld-secondary-background">' . sprintf( __( "%s Credits: %s", 'learndash'), $cpe_term, $cpe_credits ) . '</div>';
                    echo wp_kses_post( $bubble );

                    $remove_link    = add_query_arg( array(
                        'user_id'   =>  $user_id,
                        'course_id' =>  $course_id,
                        'action'    =>  'remove_user_credit',
                        'credits'   =>  $cpe_credits,
                        '_wpnonce'  =>  wp_create_nonce( 'remove_user_credit' )
                    ), admin_url( 'admin-post.php' ) );

                    $remove_credit  = '<div class="ld-status ld-remove-credit"><a href="'.$remove_link.'"> ' . __( "Remove", 'learndash') . ' </a></div>';

                    //$remove_credit  = '<div class="ld-status ld-status-complete ld-secondary-background">' . sprintf( __( "Remove %s", 'learndash'), '') . '</div>';
                    echo wp_kses_post( $remove_credit );
                }
            }
            ?>

            <?php echo wp_kses_post(learndash_status_bubble($status)); ?>


            <div class="ld-expand-button ld-primary-background ld-compact ld-not-mobile" data-ld-expands="<?php echo esc_attr( 'ld-course-list-item-' . $course_id ); ?>">
                <span class="ld-icon-arrow-down ld-icon"></span>
            </div> <!--/.ld-expand-button-->

            <div class="ld-expand-button ld-button-alternate ld-mobile-only" data-ld-expands="<?php echo esc_attr( 'ld-course-list-item-' . $course_id ); ?>">
                <span class="ld-icon-arrow-down ld-icon"></span>
                <span class="ld-text ld-primary-color"><?php esc_html_e( 'Expand', 'learndash' ); ?></span>
            </div> <!--/.ld-expand-button-->

        </div> <!--/.ld-course-details-->

    </div> <!--/.ld-course-preview-->
    <div class="ld-item-list-item-expanded">

        <?php
        learndash_get_template_part( 'shortcodes/profile/course-progress.php', array(
            'user_id'   => $user_id,
            'course_id' => $course_id,
            'progress'  => $progress
        ), true );

        $assignments = learndash_get_course_assignments( $course_id, $user_id );

        if( $assignments || !empty($quiz_attempts[$course_id]) ): ?>

            <div class="ld-item-contents">

                <?php
                if ( !empty($quiz_attempts[$course_id]) && isset($shortcode_atts['show_quizzes']) && true === $shortcode_atts['show_quizzes'] && apply_filters( 'learndash_show_profile_quizzes', $shortcode_atts['show_quizzes'] ) ):

                    learndash_get_template_part( 'shortcodes/profile/quizzes.php', array(
                        'user_id'       => $user_id,
                        'course_id'     => $course_id,
                        'quiz_attempts' => $quiz_attempts
                    ), true );

                endif; ?>

                <?php
                if( $assignments && !empty($assignments) ):

                    learndash_get_template_part( 'shortcodes/profile/assignments.php', array(
                        'user_id'       => $user_id,
                        'course_id'     => $course_id,
                        'assignments'   => $assignments
                    ), true );

                endif; ?>

            </div> <!--/.ld-course-contents-->

        <?php endif; ?>

    </div> <!--/.ld-course-list-item-expanded-->

</div> <!--/.ld-course-list-item-->
