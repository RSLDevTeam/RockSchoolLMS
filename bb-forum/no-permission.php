<?php
/**
 * Template Name: Restricted Forum Page
 */
get_header();
?>
		<main id="primary" class="site-main">
	<article id="post-<?php the_ID(); ?>">
		<section class="error-404 not-found">
			<div id="notfound">
				<div class="notfound">
					<h2>Unauthorized!</h2>
					<p>You do not have permission to access this forum.</p>
					<a href="<?php echo home_url();?>">home page</a>
				</div>
			</div>
		</section><!-- .error-404 -->
	</article>
	</main><!-- #main -->
<?php get_footer(); ?>
