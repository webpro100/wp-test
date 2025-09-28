<?php get_header() ?>

<section id="single" class="page-404">
    <div>
                
        <b>
            404
        </b>
        <p>
            Ooops the page you were looking for ran away we don't know where it went<br />
            but if you find it please notify the owner. We miss it very very much!
        </p>


        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php the_content() ?>         
            <?php endwhile; ?>          
        <?php endif; ?>
     
    </div>
</section>


<?php get_footer() ?>