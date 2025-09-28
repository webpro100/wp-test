<?php

//URL: CPT/TAXCAT/SINGLE


//custom taxonomy attached to CPT
    $taxname = 'Category';
    $taxlabels = array(
        'name' => $taxname,
        'singular_name' => $taxname,
        'search_items' => 'Search Categories',
        'popular_items' => 'Popular Categories',
        'all_items' => 'All Categories',
        'parent_item' => 'Parent Category',
        'edit_item' => 'Edit Category',
        'update_item' => 'Update Category',
        'add_new_item' => 'Add New Category',
        'new_item_name' => 'New Category',
        'separate_items_with_commas' => 'Separate Categories with commas',
        'add_or_remove_items' => 'Add or remove Categories',
        'choose_from_most_used' => 'Choose from most used Categories'
    );

    $taxarr = array(
        'label' => $taxname,
        'labels' => $taxlabels,
        'public' => true,
        'hierarchical' => true,
        'show_in_nav_menus' => true,
        'args' => array('orderby' => 'term_order'),
        'query_var' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'services'),
        'show_admin_column' => true
    );
    register_taxonomy('what_we_do_category', 'services', $taxarr);
    register_post_type( 'services',
        array(
            'labels' => array(
                'name' => 'Services',
                'singular_name' => 'Services',
                'menu_name' => 'Services'
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
            'rewrite' => array( 'slug' => '/services/%what_we_do_category%' ),
            'has_archive' => "services",
            'hierarchical' => true,
            'show_in_nav_menus' => true,
            'capability_type' => 'page',
            'query_var' => true,
            'menu_icon' => 'dashicons-welcome-widgets-menus',
        ));




  $taxname = 'Category';
    $taxlabels = array(
        'name' => $taxname,
        'singular_name' => $taxname,
        'search_items' => 'Search Categories',
        'popular_items' => 'Popular Categories',
        'all_items' => 'All Categories',
        'parent_item' => 'Parent Category',
        'edit_item' => 'Edit Category',
        'update_item' => 'Update Category',
        'add_new_item' => 'Add New Category',
        'new_item_name' => 'New Category',
        'separate_items_with_commas' => 'Separate Categories with commas',
        'add_or_remove_items' => 'Add or remove Categories',
        'choose_from_most_used' => 'Choose from most used Categories'
    );

    $taxarr = array(
        'label' => $taxname,
        'labels' => $taxlabels,
        'public' => true,
        'hierarchical' => true,
        'show_in_nav_menus' => true,
        'args' => array('orderby' => 'term_order'),
        'query_var' => true,
        'show_ui' => true,
        'rewrite' => array('slug' => 'technologies'),
        'show_admin_column' => true
    );
    register_taxonomy('tech_category', 'technologies', $taxarr);
    register_post_type( 'technologies',
        array(
            'labels' => array(
                'name' => 'Technologies',
                'singular_name' => 'Technology',
                'menu_name' => 'Technologies'
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
            'rewrite' => array( 'slug' => '/technologies/%tech_category%' ),
            'has_archive' => "technologies",
            'hierarchical' => true,
            'show_in_nav_menus' => true,
            'capability_type' => 'page',
            'query_var' => true,
            'menu_icon' => 'dashicons-editor-code',
        ));






function create_custom_permalinks( $post_link, $post ){
    if ( is_object( $post ) && $post->post_type == 'services' ){
        $terms = wp_get_object_terms( $post->ID, 'what_we_do_category' );
        
//        var_dump($terms);die;
        
        if( $terms ){
            return str_replace( '%what_we_do_category%' , $terms[0]->slug , $post_link );
        }
    } elseif ( is_object( $post ) && $post->post_type == 'technologies' ){
        $terms = wp_get_object_terms( $post->ID, 'tech_category' );
        
//        var_dump($terms);die;
        
        if( $terms ){
            return str_replace( '%tech_category%' , $terms[0]->slug , $post_link );
        }
    }
    return $post_link;
}
add_filter( 'post_type_link', 'create_custom_permalinks', 1, 2 );
