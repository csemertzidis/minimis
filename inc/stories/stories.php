<?php

//registers an option page for the stories
//option page is called stories
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Stories',
        'menu_title' => 'Stories',
        'menu_slug' => 'stories',
        'capability' => 'edit_posts',
        'position' => 2,
        'icon_url' => 'dashicons-format-gallery',
        'redirect' => false
    ));
}

function prepare_stories()
{

    //get stories from the option page
    $stories = get_field('stories', 'option');

    //if stories are not set, return an empty array
    if (!$stories) {
        return [];
    }

    $stories_out = array();
    //iterate through the stories and prepare the data
    foreach ($stories as $story) {

        $story_out = array();

        $story_out['type'] = $story['media_type'];
        if ($story['media_type'] == 'Image') {
            //url
            $story_out['image'] = $story['image']['url'];
            //alt
            $story_out['alt'] = $story['image']['alt'];
            //srcset
            $story_out['srcset'] = $story['image']['sizes'];
            //sizes

        } else {
            $story_out['video'] = $story['video'];
        }

        $story_out['link'] = $story['link'];

        //append the story to the stories_out array
        $stories_out['slides'][] = $story_out;
    }

    //find the first slide with type image
    $first_image = array_filter($stories_out['slides'], function ($slide) {
        return $slide['type'] == 'Image';
    });

    //add it in the stories_out array in the key preview image
    $stories_out['preview_image'] = array_shift($first_image);

    return $stories_out;

}

//there are pages that we do not want to show the stories
function show_stories()
{
    $show_stories = true;

    //list of slug of pages that we do not want to show the stories
    $pages = array('my-account', 'checkout');

    //get the current page
    $current_page = get_post_field('post_name', get_post());

    //if the current page is in the list of pages, do not show the stories
    if (in_array($current_page, $pages)) {
        $show_stories = false;
    }

    return $show_stories;
}