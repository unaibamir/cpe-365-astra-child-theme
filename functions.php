<?php

/**
 * CPE 365 Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package CPE 365
 * @since 1.0.0
 */


if (!function_exists("dd")) {
    function dd($data, $exit_data = true)
    {
        echo '<pre>' . print_r($data, true) . '</pre>';
        if ($exit_data == false) {
            echo '';
        } else {
            exit;
        }
    }
}


/**
 * Define Constants
 */
define('CHILD_THEME_CPE_365_VERSION', '1.0.0');
define('ASSETS_VERSION', time());
define('CPE_ASSETS', get_stylesheet_directory_uri() . "/assets");
define('CPE_DIR', trailingslashit(get_stylesheet_directory()));
define('CPE_LANG', "cpe-lang");


require_once CPE_DIR . "admin/admin-settings.php";

require_once CPE_DIR . "shortcodes/cpe_user_available_courses.php";
require_once CPE_DIR . "shortcodes/cpe_user_certificates.php";
require_once CPE_DIR . "shortcodes/cpe_user_completed_courses.php";
require_once CPE_DIR . "shortcodes/cpe_user_in_progress_courses.php";
require_once CPE_DIR . "shortcodes/cpe_user_profile.php";
require_once CPE_DIR . "paid-memberships-pro/user-credits.php";


add_action('after_setup_theme', 'cpe_theme_after_setup');
function cpe_theme_after_setup()
{
    register_nav_menus(array(
        'user_menu' => __("User Dashboard Menu", CPE_LANG),
    ));
}

require CPE_DIR . 'paid-memberships-pro' . DIRECTORY_SEPARATOR . 'cpe-custom-shortcodes.php';

/**
 * Enqueue styles
 */
function child_enqueue_styles()
{


    wp_enqueue_style('bootstrap-css', CPE_ASSETS . '/css/bootstrap.min.css', array(), ASSETS_VERSION, 'all');
    wp_enqueue_style('fontawesome-css', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), CHILD_THEME_CPE_365_VERSION, 'all');
    wp_enqueue_style('tooltipster-css', CPE_ASSETS . '/tooltipster/css/tooltipster.bundle.min.css', array(), ASSETS_VERSION, 'all');
    wp_enqueue_style('datatables-bt-css', CPE_ASSETS . '/css/dataTables.bootstrap.min.css', array(), ASSETS_VERSION, 'all');
    wp_enqueue_style('cpe-365-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), ASSETS_VERSION, 'all');


    wp_enqueue_script('tooltipster-js', CPE_ASSETS . '/tooltipster/js/tooltipster.bundle.min.js', array('jquery'), ASSETS_VERSION, true);
    wp_enqueue_script('datatables-js', CPE_ASSETS . '/js/jquery.dataTables.min.js', array('jquery'), ASSETS_VERSION, true);
    wp_enqueue_script('datatables-bt-js', CPE_ASSETS . '/js/dataTables.bootstrap.min.js', array('jquery'), ASSETS_VERSION, true);
    wp_enqueue_script('cpe-365-js', CPE_ASSETS . '/js/script.js', array('jquery'), ASSETS_VERSION, true);
}
add_action('wp_enqueue_scripts', 'child_enqueue_styles', 15);

add_filter("body_class", "woo_add_body_classes", 999, 2);
function woo_add_body_classes($classes, $class)
{
    if (is_tax("ld_course_category")) {
        $classes[] = "ast-left-sidebar";
    }

    return $classes;
}


function filter_ld_tax_posts($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_archive()) {
        if (is_tax("ld_course_category")) {
            $lists_style = isset($_GET["list_style"]) && !empty($_GET["list_style"]) ? $_GET["list_style"] : "list";

            $query->set('order', 'ASC');
            $query->set('orderby', 'title');

            if ($lists_style == "list") {
                $query->set('posts_per_page', 50);
            }
        }
    }
}
add_action('pre_get_posts', 'filter_ld_tax_posts');


/*
 * custom pagination with bootstrap .pagination class
 * source: http://www.ordinarycoder.com/paginate_links-class-ul-li-bootstrap/
 */
function bootstrap_pagination($echo = true)
{
    global $wp_query;

    $big = 999999999; // need an unlikely integer

    $pages = paginate_links(
        array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
            'type'  => 'array',
            'prev_next'   => true,
            'prev_text'    => __('« Prev'),
            'next_text'    => __('Next »'),
        )
    );

    if (is_array($pages)) {
        $paged = (get_query_var('paged') == 0) ? 1 : get_query_var('paged');

        $pagination = '<ul class="pagination pagination-sm">';

        foreach ($pages as $page) {
            $pagination .= "<li>$page</li>";
        }

        $pagination .= '</ul>';

        if ($echo) {
            echo $pagination;
        } else {
            return $pagination;
        }
    }
}

add_action('add_meta_boxes', 'learndash_course_cpe_meta_box');
/**
 * Add course grid settings meta box
 */
function learndash_course_cpe_meta_box()
{
    add_meta_box(
        'learndash-course-extras-meta-box',
        __('Course Extra Settings', 'learndash-course-grid'),
        'learndash_course_extras_output_meta_box',
        array('sfwd-courses'),
        'advanced',
        'low',
        array()
    );
}


/**
 * Output course grid settings meta box
 *
 * @param  array $args List or args passed on callback function
 */
function learndash_course_extras_output_meta_box($args)
{
    $post_id        = get_the_ID();
    $post           = get_post($post_id);
    $instructor     = get_post_meta($post_id, '_learndash_course_instructor', true);
    $cpe_credits    = get_post_meta($post_id, '_learndash_course_cpe_credits', true);
    $cpe_term       = get_option( "cpe_term", "CPE" );

    wp_nonce_field('learndash_course_extra_save', 'learndash_course_extra_nonce');
    ?>
<div class="sfwd sfwd_options">
    <div class="sfwd_input">
        <span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
            <a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_cpe_text');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
                <label class="sfwd_label textinput"><?php _e($cpe_term . ' Credits', 'learndash-course-grid'); ?></label></a>
        </span>
        <span class="sfwd_option_input">
            <div class="sfwd_option_div">
                <input name="learndash_course_cpe_credits" type="text" value="<?php echo esc_attr($cpe_credits); ?>"></textarea>
            </div>
            <div class="sfwd_help_text_div" style="display:none" id="learndash_course_cpe_text">
                <label class="sfwd_help_text"><?php _e('Use this field to change the '.$cpe_term.' credits value.', 'learndash-course-grid'); ?>
                </label>
            </div>
        </span>
        <p style="clear:left"></p>
    </div>

    <div class="sfwd_input">
        <span class="sfwd_option_label" style="text-align:right;vertical-align:top;">
            <a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!" onclick="toggleVisibility('learndash_course_instructor_text');"><img src="<?php echo LEARNDASH_LMS_PLUGIN_URL . 'assets/images/question.png' ?>">
                <label class="sfwd_label textinput"><?php _e('Instructor', 'learndash-course-grid'); ?></label></a>
        </span>
        <span class="sfwd_option_input">
            <div class="sfwd_option_div">
                <input name="learndash_course_instructor" type="text" value="<?php echo esc_attr($instructor); ?>"></textarea>
            </div>
            <div class="sfwd_help_text_div" style="display:none" id="learndash_course_instructor_text">
                <label class="sfwd_help_text"><?php _e('Use this field to change the Instructor of the course.', 'learndash-course-grid'); ?>
                </label>
            </div>
        </span>
        <p style="clear:left"></p>
    </div>

</div>

<?php
}

add_action('save_post', 'learndash_course_extras_save_meta_box', 10, 3);
/**
 * Save course grid meta box fields
 *
 * @param  int    $post_id Post ID
 * @param  object $post    WP post object
 * @param  bool   $update  True if post is an update
 */
function learndash_course_extras_save_meta_box($post_id, $post, $update)
{
    if (!in_array($post->post_type, array('sfwd-courses'))) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (!isset($_POST['learndash_course_extra_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['learndash_course_extra_nonce'], 'learndash_course_extra_save')) {
        wp_die(__('Cheatin\' huh?'));
    }

    update_post_meta($post_id, '_learndash_course_cpe_credits', wp_filter_kses($_POST['learndash_course_cpe_credits']));
    update_post_meta($post_id, '_learndash_course_instructor', wp_filter_kses($_POST['learndash_course_instructor']));
}


add_filter('pre_get_posts', "filter_pre_posts_course_tax_page", 5);
function filter_pre_posts_course_tax_page()
{
    if (is_tax("ld_course_category")) {
        remove_filter('pre_get_posts', 'pmpro_search_filter', 10);
    }
}


add_action("learndash-course-infobar-action-cell-after", "course_add_sub_option", 99, 3);
function course_add_sub_option($post_type, $course_id, $user_id)
{
    if ($post_type != "sfwd-courses") {
        return;
    }

    $level_course_option    = get_option('_level_course_option');
    $pmpro_levels_page_id   = get_option("pmpro_levels_page_id", 0);
    $course_pricing         = learndash_get_course_price($course_id);
    $meta                   = get_post_meta($course_id, '_sfwd-courses', true);
    $custom_button_url      = @$meta['sfwd-courses_custom_button_url'];



    if (array_key_exists($course_id, $level_course_option) && !empty($level_course_option[$course_id]) && $pmpro_levels_page_id != 0) {
        ?>
<?php if ($custom_button_url != "") : ?>
<p style="margin: 5px 0px;">
    <?php _e('OR', 'learndash'); ?>
</p>
<?php endif; ?>
<a class="btn-join" href="<?php echo get_permalink($pmpro_levels_page_id); ?>" style="margin: 5px 0px;">
    <?php _e('Buy a Subscription', 'learndash'); ?>
</a>
<?php
    }
}

add_filter("learndash_get_label", "woo_learndash_set_label", 999, 2);
function woo_learndash_set_label($label, $key)
{
    switch ($key) {
        case 'button_take_this_course':
            $label = esc_html__('Purchase Course', 'learndash');
            break;
    }

    return $label;
}

add_filter("pmpro_not_logged_in_text_filter", function ($content) {
    global $post;
    if ($post->post_type == "sfwd-courses") {
        return "";
    }

    return $content;
});


function get_course_plans($course_id, $count = false)
{
    global $wpdb;
    $query = "SELECT * FROM {$wpdb->pmpro_memberships_pages} WHERE `page_id` = $course_id";
    $result = $wpdb->get_results($query);
    
    if ($count && !empty($result)) {
        return count($result);
    } elseif ($count && empty($result)) {
        return false;
    } elseif (!$count && empty($result)) {
        return false;
    } elseif (!$count && !empty($result)) {
        return $result;
    }
}



add_action("learndash-course-before", "cpe_course_breadcrumbs", 9, 3);
function cpe_course_breadcrumbs($post_id, $course_id, $user_id)
{
    if ("sfwd-courses" != get_post_type($post_id)) {
        return;
    }

    global $post;

    $user_id        =   get_current_user_id();
    $post_id        =   $post->ID;

    $term_list = wp_get_post_terms($post_id, 'ld_course_category', array("fields" => "all"));
    
    ?>
<div class="learndash-breadcrumbs">
    <ol class="breadcrumb course-breadcrumb">
        <li><a href="<?php echo home_url("/") ?>"><?php _e("Home"); ?></a></li>
        <?php
        if (!empty($term_list)) {
        ?>
        <li><a href="<?php echo get_term_link($term_list[0]->term_id, $term_list[0]->taxonomy) ?>"><?php echo $term_list[0]->name; ?></a></li>
        <?php
        }
        ?>
        <li class="active"><?php echo get_the_title($course_id); ?></li>
    </ol>

    <?php

    if ( is_user_logged_in() && sfwd_lms_has_access( $post_id, $user_id ) ) {

        $progress = learndash_course_progress(array(
            'user_id'   => $user_id,
            'course_id' => $post_id,
            'array'     => true
        ));

        $btn_link = woo_user_course_resume_link($post_id, $user_id);
        
        if ($progress["percentage"] > 0 && $btn_link) {
            $btn_text = __("Resume");
        } else {
            $btn_text = __("Start");
            $course_lessons = learndash_course_get_steps_by_type($post_id, 'sfwd-lessons');
            if (function_exists('learndash_get_step_permalink')) {
                $permalink = learndash_get_step_permalink($course_lessons[0], $post_id);
            } else {
                $permalink = get_permalink($course_lessons[0]);
            }
            $permalink = add_query_arg(array("cpe_access" => "grant_access"), $permalink);
            $btn_link = $permalink;
        }
        ?>
        <a href="<?php echo $btn_link; ?>" class="user-progress-btn learndash_mark_complete_button"><?php echo $btn_text; ?></a>
        <?php
    }
    ?>
</div>
    <?php
}

function woo_user_course_resume_link($step_course_id, $user_id)
{
    $step_id = get_user_meta($user_id, 'learndash_last_known_course_' . $step_course_id, true);
    if (empty($step_id)) {
        return false;
    }

    $last_know_post_object = get_post($step_id);

    if (null !== $last_know_post_object) {
        if (function_exists('learndash_get_step_permalink')) {
            $permalink = learndash_get_step_permalink($step_id, $step_course_id);
        } else {
            $permalink = get_permalink($step_id);
        }
        return $permalink;
    }

    return false;
}


add_filter("ld_course_list_shortcode_attr_defaults", "filter_course_list_args", 99, 1);
function filter_course_list_args($attr)
{
    $attr["list_type"]  = "";
    return $attr;
}

add_filter("learndash_ld_course_list_query_args", "cpe_learndash_filter_inprogress_user_courses", 999, 2);
function cpe_learndash_filter_inprogress_user_courses($data, $atts) {
    
    if ($atts["list_type"] == "in_progress" && $atts["mycourses"] == "enrolled") {
        $posts_in_courses           =   array();
        $user_enrolled_courses      =   learndash_user_get_enrolled_courses($atts['user_id']);

        if (empty($user_enrolled_courses)) {
            return $data;
        }

        foreach ($user_enrolled_courses as $key => $enrolled_course_id) {
            $progress = learndash_course_progress(array(
                'user_id'   => $atts['user_id'],
                'course_id' => $enrolled_course_id,
                'array'     => true
            ));
            
            if ($progress['percentage'] > 0 && $progress['percentage'] !== 100) {
                if (sfwd_lms_has_access($enrolled_course_id, $atts['user_id'])) {
                    array_push($posts_in_courses, $enrolled_course_id);
                }
            }
        }

        $data['post__in']       =   !empty($posts_in_courses) ? $posts_in_courses : array(0);
    }

    if ($atts["list_type"] == "completed" && $atts["mycourses"] == "enrolled") {
        $posts_in_courses           =   array();
        $user_enrolled_courses      =   learndash_user_get_enrolled_courses($atts['user_id']);

        if (empty($user_enrolled_courses)) {
            return $data;
        }

        foreach ($user_enrolled_courses as $key => $enrolled_course_id) {
            $progress = learndash_course_progress(array(
                'user_id'   => $atts['user_id'],
                'course_id' => $enrolled_course_id,
                'array'     => true
            ));
            
            if ($progress['percentage'] == 100) {
                array_push($posts_in_courses, $enrolled_course_id);
            }
        }
        $data['post__in']       =   !empty($posts_in_courses) ? $posts_in_courses : array(0);
    }

    if ($atts["list_type"] == "available" && $atts["mycourses"] == "enrolled") {
        $posts_in_courses           =   array();
        $user_enrolled_courses      =   learndash_user_get_enrolled_courses($atts['user_id']);

        if (empty($user_enrolled_courses)) {
            return $data;
        }

        foreach ($user_enrolled_courses as $key => $enrolled_course_id) {
            $progress = learndash_course_progress(array(
                'user_id'   => $atts['user_id'],
                'course_id' => $enrolled_course_id,
                'array'     => true
            ));
            
            if ($progress['percentage'] == 100) {
                array_push($posts_in_courses, $enrolled_course_id);
            }
        }
        $data['post__in']       =   !empty($posts_in_courses) ? $posts_in_courses : array(0);
    }

    return $data;
}


//add_filter( "template_include", "unaib_filter_ld_focus_mode_template", 9999, 1 );
function unaib_filter_ld_focus_mode_template($template){
    $focus_mode = LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Theme_LD30', 'focus_mode_enabled');

    if ($focus_mode !== 'yes') {
        global $ld_in_focus_mode;
        $ld_in_focus_mode = true;

        return $template;
    }

    global $post;

    $user_id        =   get_current_user_id();
    $post_id        =   $post->ID;
    $has_access     =   sfwd_lms_has_access($course_id, $user_id);

    if (is_user_logged_in() && $has_access) {
        $post_types = array(
            'sfwd-courses',
            'sfwd-lessons',
            'sfwd-topic',
            'sfwd-assignment',
            'sfwd-quiz',
        );
    } else {
        $post_types = array(
            'sfwd-lessons',
            'sfwd-topic',
            'sfwd-assignment',
            'sfwd-quiz',
        );
    }

    if (in_array(get_post_type(), $post_types, true) && is_singular($post_types)) {
        return LEARNDASH_LMS_PLUGIN_DIR . 'themes/ld30/templates/focus/index.php';
    }

    return $template;
}


/**
 * Load the Orders print view.
 *
 * @since 1.8.6
 */
function cpe_orders_print_view()
{

    /*dd(var_export(wp_verify_nonce($_GET['_wpnonce'], "cpe_print_invoice-{$_GET['order']}"), true));

    if (!wp_verify_nonce($nonce, "cpe_print_invoice-{$_GET['order']}")) {
        wp_die(
            __("Sorry! Something went wrong. Please contact site administrator or go back and retry."),
            __("Not Allowed!"),
            array(
                "response" => 403,
                "back_link" => true
            )
        );
    }*/

    // Do we have an order ID?
    if (empty($_GET['order'])) {
        wp_redirect(admin_url('admin.php?page=pmpro-orders'));
        exit;
    }

    // Get order and membership level.
    $order = new MemberOrder($_GET['order']);

    if (!isset($order->id)) {
        wp_die(
            __("Sorry! Something went wrong. Please contact site administrator or go back and retry."),
            __("Not Allowed!"),
            array(
                "response" => 403,
                "back_link" => true
            )
        );
    }

    $level = pmpro_getLevel($order->membership_id);

    // Load template
    $template = CPE_DIR . '/paid-memberships-pro/pages/orders-print.php';
    
    require_once($template);

    ?>
    <script>
        window.print();
    </script>

    <?php
    exit;
}
add_action('admin_post_cpe_print_invoice', 'cpe_orders_print_view');