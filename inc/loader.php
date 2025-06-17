<?php

require_once "enque_scripts_styles.php";
require_once "prepare_context.php";
require_once "menu_custom_field_header.php";
include_once "disable_comments.php";
include_once "woocommerce_theme_support.php";
include_once "woo-related.php";
include_once "woomenuitems.php";
include_once "blocks/autoLoadCustomBlocks.php";
include_once "gutenberg.php";
include_once "menuprepare.php";
require_once "search_ajax.php";
require_once "stories/stories.php";

// read all file in folder cpt and include them
foreach (glob(__DIR__ . "/cpt/*.php") as $filename) {
    require_once $filename;
}

// read all file in folder extras and include them
foreach (glob(__DIR__ . "/extras/*.php") as $filename) {
    require_once $filename;
}

//woocustomizations folder has php files that customize woocommerce
// iterate through all files in the folder and include them
foreach (glob(__DIR__ . "/woocustomizations/*.php") as $filename) {
    require_once $filename;
}

// read all files in folder helpers and include them
foreach (glob(__DIR__ . "/helpers/*.php") as $filename) {
    require_once $filename;
}

// read all files in folder options and include them
foreach (glob(__DIR__ . "/options/*.php") as $filename) {
    require_once $filename;
}
