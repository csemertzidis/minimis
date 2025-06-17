<?php

add_action('init', 'register_acf_blocks');
function register_acf_blocks()
{
	$blockFolders = getFolders(__DIR__);

	foreach ($blockFolders as $blockFolder) {
		register_block_type(__DIR__ . '/' . $blockFolder, [
			'render_callback' => $blockFolder,
		]);
	}
}

add_action('init', 'require_php_controllers');
function require_php_controllers()
{
	$blockFolders = getFolders(__DIR__);
	foreach ($blockFolders as $blockFolder) {
		require_once $blockFolder . '/' . $blockFolder . '.php';
	}
}

function getFolders($dir)
{
	$folders = [];
	foreach (scandir($dir) as $file) {
		if ('.' === $file) {
			continue;
		}
		if ('..' === $file) {
			continue;
		}
		if (is_dir($dir . '/' . $file)) {
			$folders[] = $file;
		}
	}
	return $folders;
}

// Register a new category for Gutenberg blocks
// and move it first in the list
// use it in all blocks with: 'category' => 'custom_blocks'

add_filter('block_categories_all', 'register_layout_category', 20, 2);
function register_layout_category($categories)
{
	$custom_block = [
		'slug' => 'custom_blocks',
		'title' => 'MiNiMiS Blocks',
	];

	$categories_sorted = [];
	$categories_sorted[0] = $custom_block;

	foreach ($categories as $category) {
		$categories_sorted[] = $category;
	}


	return $categories_sorted;
}