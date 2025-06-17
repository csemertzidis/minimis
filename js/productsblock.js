/**
 * Products Block Load More functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get the elements
    const loadMoreButton = document.getElementById('loadmore-button');
    const loadMoreText = document.getElementById('loadmore-text');
    const loadMoreContainer = document.getElementById('loadmore-container');
    const secondBatchElements = document.querySelectorAll('.secondbatch');
    
    // Only proceed if the loadmore elements exist on the page
    if (loadMoreButton && secondBatchElements.length > 0) {
        // First add necessary styles for fade-in effect to secondBatchElements
        secondBatchElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transition = 'opacity 0.8s ease-in-out';
        });
        
        // Add click event to both the image and the text
        [loadMoreButton, loadMoreText].forEach(element => {
            element.addEventListener('click', function() {
                // Hide the load more container
                if (loadMoreContainer) {
                    loadMoreContainer.style.display = 'none';
                }
                
                // Show all second batch elements with fade-in effect
                secondBatchElements.forEach(element => {
                    // First set display to flex
                    element.style.display = 'flex';
                    
                    // Use setTimeout to ensure the display change is applied before starting the fade
                    setTimeout(() => {
                        element.style.opacity = '1';
                    }, 50);
                });
            });
        });
        
        // Make the cursor show as a pointer when hovering over the loadmore elements
        [loadMoreButton, loadMoreText].forEach(element => {
            element.style.cursor = 'pointer';
        });
    }
});
