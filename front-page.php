<?php get_header() ?>

<section id="hero">
    <div class="wrap">
        <h1>Lorem ipsum dor amet Lorem ipsum dor amet Lorem ipsum dor amet</h1>
        <a href="#" class="btn">
            Click To Action
            <span>Free Quote</span>
        </a>
    </div>
</section>



<section id="slider-1">

    <!-- Slider main container -->
    <div class="swiper swiper_1">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <!-- Slides -->
            <div class="swiper-slide">Slide 1</div>
            <div class="swiper-slide">Slide 2</div>
            <div class="swiper-slide">Slide 3</div>
        </div>

        <div class="swiper-pagination pagination_1"></div>


        <div class="swiper-button-prev prev_1"></div>
        <div class="swiper-button-next next_1"></div>

        <div class="swiper-scrollbar scrollbar_1"></div>
    </div>

</section>


<section id="slider-2">
    <div class="swiper swiper_2">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <!-- Slides -->
            <div class="swiper-slide">Slide 1</div>
            <div class="swiper-slide">Slide 2</div>
            <div class="swiper-slide">Slide 3</div>
        </div>

        <div class="swiper-pagination pagination_2"></div>


        <div class="swiper-button-prev prev_2"></div>
        <div class="swiper-button-next next_2"></div>

        <div class="swiper-scrollbar scrollbar_2"></div>
    </div>
</section>


<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php the_content() ?>
    <?php endwhile; ?>
<?php endif; ?>


<?php get_footer() ?>