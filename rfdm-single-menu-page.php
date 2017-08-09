<!-- single-menu_page_cpt.php -->
<?php

// first we get all the meta data
get_header(); 
$pid = get_the_ID();

$menu_items = get_post_meta ( $post->ID, 'menu_items', true );
$section_names = get_post_meta ( $post->ID, 'menu_section_name', true );

/*
print "<pre>";
print_r($section_names);
echo '------------------------------------------<br>';
print_r($menu_items);
print "</pre>";
*/

if ( ! isset ( $content_width ) ) {
	$content_width = 1170;
}
?>
<style>
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
.wrap {
	max-width: <?php echo $content_width; ?>px;
	margin: 0 auto;
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
</style>

<div class="wrap contact-page-header">
	<header class="page-header">
		<h1 class="page-title"><?php the_title(); ?></h1>
	</header>
</div>

<div id="primary" class="content-area menu-page">
	<main id="main" class="site-main" role="main">

		<?php 
		if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<div class="wrap">
			<div id="menu_text"><?php the_content(); ?></div>
			<div id="menu">
				<?php foreach ( $section_names as $section ) { ?>
					<div class="menu_section">
						<div class="col-lg-12 title"><?php echo $section['section_name']; ?></div>
							<div class="col-lg-12 items">
							<?php foreach ( $menu_items as $item ) { ?>
								<?php if ( $item['section'] == $section['section_id'] ) { ?>
									<div class="col-lg-6 item"><?php echo $item['item_name']; ?></div>
									<div class="col-lg-6 price"><?php echo $item['price']; ?></div>
								<?php } ?>
							<?php } ?>
							</div>
					<div class="clear"></div>
					</div>	
				<?php } ?>
			</div><!-- #menu -->
		</div><!-- .wrap -->
				
		<?php endwhile;
		else:
			echo 'Sorry';
		endif;
		?>
		
	</main><!-- #main -->
</div><!-- #primary -->
	<?php get_sidebar(); ?>

<?php get_footer();