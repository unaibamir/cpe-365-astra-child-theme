<?php
$terms = get_terms( array(
    'taxonomy' => 'ld_course_category',
    'hide_empty' => true,
    'orderby'	=>	'name',
    'order'		=>	'ASC'
) );

?>

<div itemtype="https://schema.org/WPSideBar" itemscope="itemscope" id="secondary" class="widget-area secondary " role="complementary">

	<div class="sidebar-main">
		<div class="left_section">
			
			<?php
			if( !empty( $terms ) ) {
				?>
				<div class="search-term course">
					<h3>Course Categories</h3>
					<div class="check-box">
						<?php
						$loop = 0;
						foreach ($terms as $key => $term) {
							$term_link = get_term_link( $term->term_id );
							$term_link = isset($_GET["list_style"]) && !empty( $_GET["list_style"] ) ? add_query_arg("list_style", $_GET["list_style"], $term_link) : $term_link;
							?>
							<div class="text">
			                    <span>
			                    	<a href="<?php echo $term_link; ?>"><?php echo $term->name; ?></a>
			                    </span>
			                </div>
							<?php
							$loop++;
							unset($term);
						}
						unset($terms);
						?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div><!-- .sidebar-main -->
</div>