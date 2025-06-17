jQuery(document).ready(function($) {

  // Add to wishlist with event delegation
  $(document).on('click', '.add-to-wishlist', function() {
    var $button = $(this); // Store reference to the button
    var product_id = $button.data('product-id');

    //fade out animation
    setTimeout(function() {
      $('.heart' + product_id).fadeOut(  );
    });

    $.ajax({
      url: ajax_object.ajax_url,
      type: 'post',
      data: {
        action: 'add_to_wishlist',
        product_id: product_id
      },
      success: function(data) {
        // Change icon and classes
        $button.removeClass('add-to-wishlist').addClass('remove-from-wishlist');
        $('.heart' + product_id).removeClass('bi-heart add-to-wishlist').addClass('bi-heart-fill remove-from-wishlist'); 
        $('.box'+ product_id).addClass('fill'); // Add fill class to the wishlist icon
        //fade in animation
        setTimeout(function() {
          $('.heart' + product_id).fadeIn();
        });
        // Update wishlist content
        $.ajax({
          url: ajax_object.ajax_url,
          type: 'post',
          data: {
            action: 'wishlist_formated_html',
            product_id: product_id
          },
          success: function(data) {
            $('.wishlist-content').html(data);
          }
        });
      }
    });
  });

  // Remove from wishlist with event delegation
  $(document).on('click', '.remove-from-wishlist', function() {
    var $button = $(this); // Store reference to the button
    var product_id = $button.data('product-id');

      //fade out animation
    setTimeout(function() {
      $('.heart' + product_id).fadeOut();
    });

    $.ajax({
      url: ajax_object.ajax_url,
      type: 'post',
      data: {
        action: 'remove_from_wishlist',
        product_id: product_id
      },
      success: function(data) {
        // Change icon and classes
        $button.removeClass('remove-from-wishlist').addClass('add-to-wishlist');
        $('.heart' + product_id).removeClass('bi-heart-fill remove-from-wishlist').addClass('bi-heart add-to-wishlist'); 
        $('.box'+ product_id).removeClass('fill'); // Add fill class to the wishlist icon

        setTimeout(function() {
          $('.heart' + product_id).fadeIn();
        });

        // Update wishlist content
        $.ajax({
          url: ajax_object.ajax_url,
          type: 'post',
          data: {
            action: 'wishlist_formated_html',
            product_id: product_id
          },
          success: function(data) {
            $('.wishlist-content').html(data);
          }
        });
      }
    });
  });
});