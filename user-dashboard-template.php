<?php
/**
 * Template Name: User Dashboard Pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( !is_user_logged_in() ) {
	$login_url = wp_login_url( get_permalink() );
	wp_safe_redirect( $login_url );
	exit;
}

get_header(); ?>

<div class="">
	<div class="ast-row">
		<div class="ast-col-md-3 ast-col-lg-3 ast-col-xs-12 courses-left">
			<div itemtype="https://schema.org/WPSideBar" itemscope="itemscope" id="secondary" class="widget-area secondary " role="complementary">
				<div class="sidebar-main">
					<div class="left_section">
						<div class="search-term course">
							<h3><?php _e( "Quick Links", CPE_LANG ); ?></h3>
							<?php
							wp_nav_menu( $args = array(
							    'theme_location'    => "user_menu",
							    'menu_class'        => "user-dashboard-menu ",
							    'menu_id'        	=> "user-dashboard-menu",
							    'container'         => "",
							    'before' 			=> '<span>',
							    'after'  			=> '</span>'
							) );
							?>
						</div>

						<?php do_action( "cpe_user_dashboard_sidebar", get_the_ID() ); ?>

					</div>
				</div>
			</div>
		</div>
		<div class="ast-col-md-9 ast-col-lg-9 ast-col-xs-12 courses-right">
			<div id="primary" <?php astra_primary_class(); ?>>
				<main id="main" class="site-main">

					<?php astra_primary_content_top(); ?>
			
					<?php if ( have_posts() ) : ?>

						<?php do_action( 'astra_template_parts_content_top' ); ?>

						<?php
						while ( have_posts() ) :
							the_post();

								do_action( 'astra_page_template_parts_content' );

							?>

						<?php endwhile; ?>

						<?php do_action( 'astra_template_parts_content_bottom' ); ?>

					<?php else : ?>

						<?php do_action( 'astra_template_parts_content_none' ); ?>

					<?php endif; ?>

					<?php astra_primary_content_bottom(); ?>
				</main><!-- #main -->
			</div><!-- #primary -->
		</div>
	</div>
</div>

<?php get_footer(); ?>
