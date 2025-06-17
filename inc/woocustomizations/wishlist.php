<?php

add_action('wp_ajax_add_to_wishlist', 'add_to_wishlist_callback');
add_action('wp_ajax_nopriv_add_to_wishlist', 'add_to_wishlist_callback');

function add_to_wishlist_callback()
{
  //check if cookie exists
  if (isset($_COOKIE['minimiswishlist'])) {
    $wishlist = json_decode(stripslashes($_COOKIE['minimiswishlist']), true);
  } else {
    $wishlist = array();
  }

  //check if product id exists in database
  if (!wc_get_product($_POST['product_id'])) {
    // echo json_encode($wishlist);
    wp_die();
  }

  //check if product is already in wishlist
  if (in_array($_POST['product_id'], $wishlist)) {
    //echo json_encode($wishlist);
    wp_die();
  }

  //add product to wishlist
  $wishlist[] = $_POST['product_id'];

  //set cookie
  setcookie('minimiswishlist', json_encode($wishlist), time() + (86400 * 30), '/');
  echo json_encode($wishlist);

  //DIE mf!
  wp_die();


}


//remove-from-wishlist
add_action('wp_ajax_remove_from_wishlist', 'remove_from_wishlist_callback');
add_action('wp_ajax_nopriv_remove_from_wishlist', 'remove_from_wishlist_callback');

function remove_from_wishlist_callback()
{

  //check if cookie exists
  if (isset($_COOKIE['minimiswishlist'])) {
    $wishlist = json_decode(stripslashes($_COOKIE['minimiswishlist']), true);
  } else {
    $wishlist = array();
  }

  //check if product id exists in database
  if (!wc_get_product($_POST['product_id'])) {
    echo json_encode($wishlist);
    wp_die();
  }

  //check if product is already in wishlist
  if (!in_array($_POST['product_id'], $wishlist)) {
    echo json_encode($wishlist);
    wp_die();
  }

  //remove product from wishlist
  foreach ($wishlist as $key => $value) {
    if ($value == $_POST['product_id']) {
      unset($wishlist[$key]);
    }
    if ($value == null) {
      unset($wishlist[$key]);
    }
  }

  //set cookie
  setcookie('minimiswishlist', json_encode($wishlist), time() + (86400 * 30), '/');
  echo json_encode($wishlist);

  //DIE mf!
  wp_die();
}


add_action('wp_enqueue_scripts', 'enqueue_wishlist_script');
function enqueue_wishlist_script()
{
  wp_enqueue_script('wishlist-script', get_template_directory_uri() . '/wishlist.js', array('jquery'), '1.0', true);
  wp_localize_script('wishlist-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}



//retrieves the wishlist from the cookie
function get_wishlist()
{
  if (isset($_COOKIE['minimiswishlist'])) {
    $wishlist = json_decode(stripslashes($_COOKIE['minimiswishlist']), true);
  } else {
    $wishlist = array();
  }
  $whishlist_products = prepare_wishlist($wishlist);
  return $whishlist_products;
}

function prepare_wishlist($ids)
{
  foreach ($ids as $id) {
    $product = wc_get_product($id);
    if (!$product) {
      continue;
    }
    $wishlist[] = array(
      'id' => $id,
      'name' => $product->get_name(),
      'price' => $product->get_price(),
      'permalink' => get_permalink($id),
      'image' => get_the_post_thumbnail_url($id, 'full') ?: wc_placeholder_img_src(),
      'srcset' => get_post_thumbnail_id($id) ? wp_get_attachment_image_srcset(get_post_thumbnail_id($id)) : '',
    );

  }

  if (isset($wishlist)) {
    return $wishlist;
  }
  return false;


}

//this is an ajax response function for the wish list
//it returns formated html for the wishlist
// function is called wishlist_formated_html
add_action('wp_ajax_wishlist_formated_html', 'wishlist_formated_html_callback');
add_action('wp_ajax_nopriv_wishlist_formated_html', 'wishlist_formated_html_callback');

function wishlist_formated_html_callback()
{

  $d = get_wishlist();

  //print($d);
  foreach ($d as $product) {

    ?>
    <div class="row mb-2">
      <div class="col-3">
        <a href="<?php echo $product['permalink'] ?>">
          <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['alt'] ?>" class="img-fluid">
        </a>
      </div>
      <div class="col-7">
        <h6><?php echo $product['name']; ?></h6>
        <p><?php echo $product['price']; ?></p>
      </div>
      <div class="col-2 text-end">
        <i class="bi bi-trash remove-from-wishlist" data-product-id="<?php echo $product['id']; ?>"></i>
      </div>
    </div>

    <?php
  }
  wp_die();
}



function is_in_wishlist($product_id)
{
  
  if (isset($_COOKIE['minimiswishlist'])) {
    $wishlist = json_decode(stripslashes($_COOKIE['minimiswishlist']), true);
  } else {
    $wishlist = array();
  }

  if (in_array($product_id, $wishlist)) {
    return true;
  }
  return false;
}