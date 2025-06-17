	const mslider = new Swiper('.mainslider', {
	    // Optional parameters
	    direction: 'horizontal',
	    loop: true,
	
		crossfade: false,
		effect: 'fade',
		//pauseOnMouseEnter
		autoplay: {
		delay: 5000,
		disableOnInteraction: false,
		pauseOnMouseEnter: true,
		},

	
	    // If we need pagination
	    pagination: {
	    el: '.swiper-pagination',
	    },
	
	    // Navigation arrows
	    navigation: {
	    nextEl: '.swiper-button-next',
	    prevEl: '.swiper-button-prev',
	    },
	
	    // And if we need scrollbar
	    scrollbar: {
	    el: '.swiper-scrollbar',
	    },
	});