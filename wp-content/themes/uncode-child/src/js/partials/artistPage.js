if ( $("#primary").attr("page") === "artist-page" ) {

	function generateArtistSlideshow(){ // random name for the function just because

		// Generate Pagination
		var artistBlocks = $(".artist-bio-block"); //new variable artistBlocks
		var	bioPaginationHtml = ""; //new variable empty
		var count = 0; //count is 0

		$(artistBlocks).each( function(){  //gives a function to each artistBlock
			count++; //count is count + 1 ----  1+1 2+1
			bioPaginationHtml = bioPaginationHtml + "<a href='javascript:void(0)' count='" + count + "' class='slider-count slider-count-" + count + "'></a>";  // add this inbetween "" / why is there so many ' "?		});
		});

		$(".bio-pagination") //selects .bio pagination "why is there no {"
			.html(bioPaginationHtml) //prints bioPaginationHTML "why is there no ';'"
			.find(".slider-count:first-of-type").addClass("active"); //finds the first of its kind and adds class active

		$(".artist-bio-block:first-of-type").addClass("active");
		
		// On click events for next and prev
		var curCount = 1; //current count is 1

		$(".next").click( function(){ //on click of next adds function

			curCount++; // variable current count + 1
			if (curCount > count) {  //if current count is more than count (6), count should be 1 -- how does it know that 'count' will go til 6?

				curCount = 1;
			}
			$(".artist-bio-block.active").removeClass("active"); 
			$(".artist-bio-block.block-" + curCount).addClass("active");

			$(".bio-pagination .active").removeClass("active");
			$(".bio-pagination .slider-count-" + curCount).addClass("active");

		});

		$(".previous").click( function(){

			curCount--; //variable current count - 1
			if (curCount < 1) { //if cur count is lower than 1 it will always be 1?
				curCount = count;
			}
			$(".artist-bio-block.active").removeClass("active");
			$(".artist-bio-block.block-" + curCount).addClass("active");

			$(".bio-pagination .active").removeClass("active");
			$(".bio-pagination .slider-count-" + curCount).addClass("active");

		});

		// On click event for pagination
		$(".slider-count").click( function(){
			
			var sliderNumber = $(this).attr("count"); //what does .attr("count") do? 

			$(".artist-bio-block.active").removeClass("active");
			$(".artist-bio-block.block-" + sliderNumber).addClass("active");

			$(".bio-pagination .active").removeClass("active");
			$(".bio-pagination .slider-count-" + sliderNumber).addClass("active");

		});


		$(".slider-count").css("width", (100 / count) + "%");
		
		var f = 100 / $(window).height(),
            g = 100 / $(window).width();
		$("html").mousemove(function(a) {
            var b = a.pageX - $(window).width() / 16;
            a = a.pageY - $(window).height() / 2;
            b = g * b * -1;
            a = f * a * -1;
            var c = (Math.abs(a) + Math.abs(b)) / 100 + 0.9;
            $(".bio-image img").css("transform", "translate3d(" + -(b/2) + "px, " + -(a/1) + "px, " + 0 + "px)");
            $(".artist-bio-block .title, .artist-bio-block .description").css("transform", "translate3d(" + -(b/5) + "px, " + -(a/2) + "px, " + 0 + "px)");

        });
	

	}





	generateArtistSlideshow();

	$(window).load(function() {
	});

	////////////////////////////////////////////////
	// ON RESIZE
	var updateArtistPage = _.debounce(function(e) {

	}, 500);
	window.addEventListener("resize", updateArtistPage, false);


}