<?php
/**
 * The template for displaying 404 pages (Not Found)
**/

get_header(); ?>
		<div id="content" class="site-content-404" role="main">
			<h1 class="title"><?php _e('404: puslapis nerastas','ntis'); ?></h1>
            <div class="content">
                <p>
                    <?php _e('Gali būti, kad puslapis neegzistuoja, buvo ištrintas arba perkeltas į kitą vietą.','ntis'); ?>
                </p>
			</div><!-- .page-content -->
		</div><!-- #content -->
<?php get_footer(); ?>