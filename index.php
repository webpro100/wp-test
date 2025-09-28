<?php get_header() ?>

<?php if (have_posts()) : ?>
	<?php while (have_posts()) : the_post(); ?>
    
		<?php /*
        <div class="post">
            <?php the_post_thumbnail( "medium" );  ?>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title() ?></a></h2>
            <time><?php the_time( "j F Y" ); ?></time>
            <div class="cat"><?php the_category() ?></div>
            <p><?php echo wp_trim_words( get_the_content(), 51, '...' ); ?></p>
        </div>
        */ ?>
        
        <?php // the_title() ?>
        <?php //the_content() ?>
        <?php //the_permalink() ?>
        <?php //the_excerpt() ?>
        <?php //the_time() ?>
        <?php //the_category() ?>
    
    <?php endwhile; ?>
    
    <?php // the_posts_pagination(); ?>  
      
<?php endif; ?>


<?php get_footer() ?>