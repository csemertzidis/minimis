jQuery(document).ready(function($) {
    $("#searchInput").on("keyup", function() {
        var search_term = $(this).val(); 

        if (search_term.length > 2) { // Start searching after 2+ characters
            $.ajax({
                type: "POST",
                url: ajax_object.ajax_url, 
                data: {
                    action: 'my_search_function', // Name of your WP AJAX function
                    search_term: search_term
                },
                success: function(response) {
                    $("#searchResults").html(response); 
                },
                error: function(error) {
                    console.error("AJAX Error:", error); 
                }
            });
        } else {
            $("#searchResults").html(''); // Clear results if search term is too short
        }
    });
});

//on click on .search clean the input
jQuery(document).ready(function($) {
    $(".search").on("click", function() {
        $("#searchInput").val('');
        $("#searchResults").html('');
    });

    
});
