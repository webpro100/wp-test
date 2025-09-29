<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">
    <title>
        <?php wp_title(); ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri()); ?>/favicon.svg">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <header>
        <div class="wrap">
            <?php
            $main_nav = array(
                'theme_location' => 'main_menu',
                'fallback_cb' => '',
                'container' => ''
            );
            wp_nav_menu($main_nav);
            ?>
        </div>
    </header>





    <?php

/*


    <!-- Slider main container -->
    <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">

            <div class="swiper-slide">Slide 1</div>
            <div class="swiper-slide">Slide 2</div>
            <div class="swiper-slide">Slide 3</div>

        </div>
        <!-- If we need pagination -->
        <div class="swiper-pagination"></div>

        <!-- If we need navigation buttons -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>

        <!-- If we need scrollbar -->
        <div class="swiper-scrollbar"></div>
    </div>


    */


// $main_nav = array(
//     'theme_location' => 'main_menu',
//     'fallback_cb' => '',
//     'container' => ''
// );
// wp_nav_menu($main_nav);
