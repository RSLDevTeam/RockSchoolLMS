<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package rslfranchise
 */

get_header();
?>

	<main id="primary" class="site-main">
	<article id="post-<?php the_ID(); ?>">
		<section class="error-404 not-found">
			<div id="notfound">
				<div class="notfound">
					<div class="notfound-404">
						<h1>:(</h1>
					</div>
					<h2>404 - Page not found</h2>
					<p>The page you are looking for might have been removed had its name changed or is temporarily unavailable.</p>
					<a href="<?php echo home_url();?>">home page</a>
				</div>
			</div>
		</section><!-- .error-404 -->
	</article>
	</main><!-- #main -->

<?php
get_footer();
