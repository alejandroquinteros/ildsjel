if ( $("#primary").attr("page") === "home" ) {

	////////////////////////////////////////////////
	// CAROUSEL

	var carousel 			= $('.carousel'),
		prevButton 			= $('.navigation .previous'),
		nextButton 			= $('.navigation .next'),
		nextAndPrevButton 	= $('.navigation .next, .navigation .previous'),
		panelCount 			= carousel.children().length,
		transformProp 		= Modernizr.prefixed('transform'),
		theta 				= 0,
		activeCount 		= 1;

	function setFront( activeCount ){

		$('.carousel > *, .home-description-wrapper > *')
			.removeClass('active')
			.removeClass('front');
		$('.carousel > *:nth-child(' + activeCount + '), .home-description-wrapper > *:nth-child(' + activeCount + ')')
			.addClass('active');
		var front = $('.carousel > *:nth-child(' + activeCount + 
			'), .carousel > *:nth-child(' + ( activeCount - 1 ) + 
			'), .carousel > *:nth-child(' + ( activeCount + 1 ) + 
			'), .carousel > *:nth-child(' + ( activeCount + 2 ) + ')');
		front.addClass('front');
		if ( front.length <= 2 ) {

			$('.carousel > *:nth-child(1), .carousel > *:nth-child(2)').addClass('front');
		} else if ( front.length <= 3 ) {

			$('.carousel > *:nth-child(1), .carousel > *:nth-child(2), .carousel > *:nth-child(3)').addClass('front');
		}
	}
	setFront( 1 );

	function carouselNavClick( obj, area ){

		var increment = parseInt( $(obj).attr('data-increment') );
		theta += ( 360 / panelCount ) * increment * -1;
		carousel[0].style[ transformProp ] = 'translateZ( -' + area + 'px ) translateY(-150px) rotateY(' + theta + 'deg)';

		activeCount += increment;
		if (activeCount > panelCount) { activeCount = 1; }
		if (activeCount < 1) { activeCount = panelCount; }

		setFront( activeCount );
		setDescription();
	}

	$('.carousel > *:nth-child(1), .home-description-wrapper > *:nth-child(1)').addClass('active');

	nextAndPrevButton.click(function() {

		if ( $(document).width() < large ) {

			carouselNavClick($(this), 2000); 
		} else if ( $(document).width() < xlarge ) {

			carouselNavClick($(this), 1600); 
		} else if ( $(document).width() < xxlarge ) {

			carouselNavClick($(this), 1450); 
		} else {

			carouselNavClick($(this), 1300); 
		}
	});


	function setDescription(){

		$(".home-description-wrapper > .active .home-description").each(function(index, el) {

			$(this).css('margin-top', 0); 
			if ($(document).width() > medium) {

				var marginHeight = $(document).height() - $(this).height();
				$(this).css('margin-top', marginHeight); 
			}
		});
	}

	$(window).load(function() {

		setDescription();
	});



	////////////////////////////////////////////////
	// ON RESIZE
	var updateDesription = _.debounce(function(e) {

		setDescription();
		setTimeout(setDescription(), 1000);
	}, 500);
	window.addEventListener("resize", updateDesription, false);

}