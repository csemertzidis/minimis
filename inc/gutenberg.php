<?php

add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_specific_template', 10, 2);

function disable_gutenberg_for_specific_template($use_block_editor, $post_type)
{
    if ($post_type === 'page') { // Only apply to pages
        $template = get_page_template_slug(get_the_ID());
        if ($template === 'template-homepage.php') { // Replace with your template name
            $use_block_editor = false;
        }
    }
    return $use_block_editor;
}