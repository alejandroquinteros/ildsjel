if ( $("#primary").attr("page") === "archive" ) {



	// Set Tile heights
	function setTileHeights(){
		$(".archive-col a").css("padding", "0");
		$(".archive-col a").css("height", $(".archive-col a").first().width() * 1.333333);

	}
	setTileHeights();


	$(window).load(function() {
	});

	////////////////////////////////////////////////
	// ON RESIZE
	var updateArtistPage = _.debounce(function(e) {
		setTileHeights();
	}, 500);
	window.addEventListener("resize", updateArtistPage, false);

}