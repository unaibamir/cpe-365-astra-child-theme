<?php

global $wp_query;

$lists_style = isset($_GET["list_style"]) && !empty($_GET["list_style"]) ? $_GET["list_style"] : "list";
$lists_url = add_query_arg("list_style", "list");
$grids_url = add_query_arg("list_style", "grid");
add_filter('excerpt_more', '__return_empty_string');
$user_id = get_current_user_id();
$cpe_term       =   get_option( "cpe_term", "CPE" );

get_header();

?>

<div class="ast-row">

</div>

<div class="ast-row">
	<div class="ast-col-md-3 ast-col-lg-3 ast-col-xs-12 courses-left">
		<?php get_sidebar("ld-category"); ?>
	</div>
	<div class="ast-col-md-9 ast-col-lg-9 ast-col-xs-12 courses-right">
		<div id="primary" <?php astra_primary_class(); ?>>

			<?php astra_primary_content_top(); ?>

			<?php //astra_archive_header(); 
			?>

			<div class="ast-row category-details">
				<div class="ast-col-md-12 ast-col-xs-12">
					<h1>
						<?php echo single_tag_title('', false); ?>
					</h1>
					<?php echo get_the_archive_description(); ?>
				</div>
			</div>

			<main id="main" class="site-main category-courses" role="main">

				<div class="courses-list <?php echo $lists_style; ?>">

					<div class="ast-row">
						<div class="ast-col-md-6 ast-col-xs-6">
							<div class="ast-pagination">
								<?php bootstrap_pagination(); ?>
							</div>
						</div>
						<div class="ast-col-md-3 ast-col-xs-6 pull-right">
							<div class="right_box view">
								<p>View as:</p>
								<ul>
									<li>
										<a href="<?php echo esc_url($lists_url); ?>" class="<?php echo $lists_style == "list" ? "active" : ""; ?>">
											<i class="fa fa-th-list" aria-hidden="true"></i>
										</a>
									</li>
									<li>
										<a href="<?php echo esc_url($grids_url); ?>" class="<?php echo $lists_style == "grid" ? "active" : ""; ?>">
											<i class="fa fa-th-large" aria-hidden="true"></i>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>

					<?php
					if ($lists_style == "grid") {
						?>

						<?php if (have_posts()) : ?>
							<div class="ast-row">
								<?php
								while (have_posts()) : the_post();
									$course_id 			= get_the_ID();
									$course_settings 	= learndash_get_setting($course_id);
									$course_price 		= !empty(@$course_settings["course_price"]) ? $course_settings["course_price"] : "0.00";
									$course_cpe 		= get_post_meta($course_id, "_learndash_course_cpe_credits", true);
									$course_cpe 		= !empty($course_cpe) ? $course_cpe : "0.00";
									$instructor 		= get_post_meta($course_id, "_learndash_course_instructor", true);
									?>
									<div class="ast-col-md-6 ast-col-sm-6 ast-col-xs-12 course-post-wrapper">
										<article id="post-<?php the_ID(); ?>" <?php post_class("course-post course-tooltips"); ?> data-tooltip-content="#tooltip_content_<?php echo $course_id; ?>">

											<div class="courseBlock">
												<div class="overly">
													<!-- <span>Online</span> -->
													<span class="price"><?php echo $course_price; ?></span>
												</div>


												<div class="txt">
													<header class="entry-header">
														<?php the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>
													</header><!-- .entry-header -->
													<?php if (!empty($instructor)) : ?>
														<h3>
															<span>Instructor:</span> <?php echo $instructor; ?>
														</h3>
													<?php endif; ?>
													<h3>
														<span><?php echo $cpe_term ?>:</span> <?php echo $course_cpe; ?>
													</h3>



													<div class="txtBtn">
														<?php
														if (sfwd_lms_has_access($course_id, $user_id)) :
															?>
															<a href="<?php echo esc_url(get_permalink()); ?>" class="course-buy-btn">
																Start Course
															</a>
														<?php
														else :
															if (@$course_settings["course_price_type"] == "closed") {
																$course_link = esc_url(get_permalink());
																$link_text = __("Buy");
															} elseif (@$course_settings["course_price_type"] == "closed") {
																$course_link = esc_url(get_permalink());
																$link_text = __("Buy");
															} elseif (@$course_settings["course_price_type"] == "free") {
																$course_link = esc_url(get_permalink());
																$link_text = __("Start Course");
															} elseif (@$course_settings["course_price_type"] == "open") {
																$course_link = esc_url(get_permalink());
																$link_text = __("Start Course");
															} else {
																$course_link = esc_url(get_permalink());
																$link_text = __("More details");
															}
															?>
															<a href="<?php echo $course_link; ?>" class="course-buy-btn">
																<?php echo $link_text; ?>
															</a>
														<?php
														endif;
														?>
														
													</div>
												</div>
											</div>

											<div class="tooltip_templates">
												<span id="tooltip_content_<?php echo $course_id; ?>">
													<?php echo wpautop(get_the_excerpt()); ?>
													<p><b>Field of Study: </b><?php single_cat_title(); ?></p>
													<p><b><?php echo $cpe_term; ?> Hours: </b>3.0</p>
												</span>
											</div>
										</article><!-- #post-## -->
									</div>
								<?php
								endwhile;
								?>
							</div>
						<?php else : ?>

							<?php do_action('astra_template_parts_content_none'); ?>

						<?php endif; ?>

					<?php
					} else {
						if (have_posts()) :
							?>
							<div class="table-responsive">
								<table class="table table-bordered table-striped course-list-table">
									<thead>
										<tr>
											<th class="ast-col-lg-7">Course Name</th>
											<th><?php echo $cpe_term; ?></th>
											<th>Price</th>
											<th>Select</th>
										</tr>
									</thead>
									<tbody>
										<?php while (have_posts()) : the_post(); ?>

											<?php
											$course_id 			= get_the_ID();
											$course_settings 	= learndash_get_setting($course_id);
											$course_price 		= !empty(@$course_settings["course_price"]) ? $course_settings["course_price"] : "0.00";
											$course_cpe 		= get_post_meta($course_id, "_learndash_course_cpe_credits", true);
											$course_details 	= get_post_meta($course_id, "_learndash_course_grid_short_description", true);
											$course_cpe 		= !empty($course_cpe) ? $course_cpe : "0.00";
											?>

											<tr id="course-<?php echo $course_id; ?>">
												<td class="course-title">
													<a href="<?php echo esc_url(get_permalink()); ?>" title="<?php the_title(); ?>" class="course-tooltip" data-tooltip-content="#tooltip_content_<?php echo $course_id; ?>" rel="bookmark">
														<?php the_title(); ?>
													</a>

													<div class="tooltip_templates">
														<span id="tooltip_content_<?php echo $course_id; ?>">
															<?php echo wpautop($course_details); ?>
															<p><b>Field of Study: </b><?php single_cat_title(); ?></p>
															<p><b><?php echo $cpe_term; ?> Hours: </b><?php echo $course_cpe; ?></p>
														</span>
													</div>
												</td>
												<td>
													<?php echo $course_cpe; ?>
												</td>
												<td><?php echo $course_price; ?></td>
												<td>
													<?php
													if (sfwd_lms_has_access($course_id, $user_id)) :
														?>
														<a href="<?php echo esc_url(get_permalink()); ?>" class="course-buy-btn">
															Start Course
														</a>
													<?php
													else :
														if (@$course_settings["course_price_type"] == "closed") {
															$course_link = esc_url(get_permalink());
															$link_text = __("Buy");
														} elseif (@$course_settings["course_price_type"] == "closed") {
															$course_link = esc_url(get_permalink());
															$link_text = __("Buy");
														} elseif (@$course_settings["course_price_type"] == "free") {
															$course_link = esc_url(get_permalink());
															$link_text = __("Start Course");
														} elseif (@$course_settings["course_price_type"] == "open") {
															$course_link = esc_url(get_permalink());
															$link_text = __("Start Course");
														} else {
															$course_link = esc_url(get_permalink());
															$link_text = __("More details");
														}
														?>
														<a href="<?php echo $course_link; ?>" class="course-buy-btn">
															<?php echo $link_text; ?>
														</a>
													<?php
													endif;
													?>
												</td>
											</tr>

										<?php endwhile; ?>

									</tbody>
								</table>
							</div>
						<?php
						else :
							do_action('astra_template_parts_content_none');
						endif;
					}
					?>
				</div>

			</main><!-- #main -->

			<div class="ast-pagination">
				<?php bootstrap_pagination(); ?>
			</div>

			<?php astra_primary_content_bottom(); ?>

		</div><!-- #primary -->
	</div>
</div>



<?php get_footer(); ?>