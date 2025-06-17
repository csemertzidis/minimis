document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.count-up-number');

    counters.forEach(counter => {
        const target = +counter.dataset.target; // Get the target number from a data attribute
        let current = 0;
        const increment = target / 200; // Adjust duration here (200 frames)

        const updateCounter = () => {
            if (current < target) {
                current += increment;
                counter.textContent = Math.ceil(current).toLocaleString('en-US'); // Format as needed
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString('en-US');
            }
        };

        updateCounter();
    });
});