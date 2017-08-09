<?php 
// display shortcode
function rfadm_menu_shortcode_handler($atts, $content) {
	global $wp_query, $post;
	
	$args = array(
			'posts_per_page' => '1',
			'post_type' => 'menu_page_cpt',
			'p' => $atts['p'],
	);
	
	$the_query= new WP_Query($args);
	
	$output = '';
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			
			$menu_items = get_post_meta($post->ID, 'menu_items', true);
			$section_names = get_post_meta($post->ID, 'menu_section_name', true);
			
			/*
			 print "<pre>";
			 print_r($section_names);
			 echo '------------------------------------------<br>';
			 print_r($menu_items);
			 print "</pre>";
			 */
			
			$output .= '<style>
			.clear {
				clear: both;
			}
			.container-fluid {
			  padding-right: 15px;
			  padding-left: 15px;
			  margin-right: auto;
			  margin-left: auto;
			}
			.row {
			  margin-right: -15px;
			  margin-left: -15px;
			}
			.col-lg-6, .col-md-6, .col-sm-6 {
				float: left;
				width: 50%;
			}
			.menu_section {
				margin-bottom: 10px;
			}
			#menu {
				text-align: center;
			}
			</style>';
			
			$output .=
			'<div class="wrap">
				<div id="menu_text">' . the_content() . '</div>';
			
			$output .= '
				<div id="menu">';
			foreach ( $section_names as $section ) {
				$output .= '<div class="menu_section">
						<div class="col-lg-12 title">' .  $section['section_name'] . '</div>
							<div class="col-lg-12 items">';
				foreach ( $menu_items as $item ) {
					if ( $item['section'] == $section['section_id'] ) {
						$output .= '<div class="col-lg-6 item">' . $item['item_name'] . '</div>';
						$output .= '<div class="col-lg-6 price">' . $item['price'] . '</div>';
					}
				}
				$output .= '</div>
					<div class="clear"></div>
					</div>';
			}
			$output .= '</div><!-- #menu -->
			</div><!-- .wrap -->';
		}
	} else {
		// no posts found
	}
	
	return $output;
	wp_reset_query();
}
add_shortcode( 'display_menu', 'rfadm_menu_shortcode_handler');
?>