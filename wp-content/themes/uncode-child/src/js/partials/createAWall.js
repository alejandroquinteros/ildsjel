if ( $("#primary").attr("page") === "create-a-wall" ) {

	$(".editor-switch").click( function(){

		$("html").toggleClass('editor-active');
	});


	// Set Tile heights
	function createAWallTileHeights(){

		$(".create-a-wall-col a").css("padding", "0");
		$(".create-a-wall-col a").css("height", $(".create-a-wall-col a").first().width() * 1.333333);
	}
	createAWallTileHeights();

	function setCreateAWallEditorTileHeights(){

		// $(".wall-tiles .tile").css("height", $(".wall-tiles .tile:first-of-type").first().width() * 1.333333);
		$(".tile-height").html("<style>.wall-tiles .tile{" +
			"height:" + $(".wall-tiles .tile:first-of-type").width() * 1.333333 + "px!important;" +
			"}</style>");	
	}
	setCreateAWallEditorTileHeights();

	// Set Tile heights
	function rotateTiles(){

		$(".create-a-wall-row").each( function(){

			var position = 0;
			var row = $(this);
			if (row.find(".tile").length > 0) {
				    	
				var tileWidth = row.find(".tile:first-of-type").width() + Number(row.find(".tile:first-of-type").first().css("margin-right").replace(/[^-\d\.]/g, ''));
				var maxWidth = row.find(".tile").length * tileWidth;
				row.find(".next").click( function(){

					if (-(position - tileWidth) < maxWidth) {

						position = position - tileWidth;
					}
					row.find(".tile").css("left", position);
				});
				row.find(".prev").click( function(){

					if (position < 0) {

						position = position + tileWidth;
					}
					row.find(".tile").css("left", position);
				});
			} else {
				row.remove();
			}
		});
	}
	rotateTiles();

	// Add Tiles To Wall
	var emptyTile = '<div class="tile empty-tile"></div>';
	function addTilesToWall(){
		var tileNo = 0;
		$(".create-a-wall-items .tile").each(function(){

			tileNo++
			var thisTileNo = tileNo;
			var tile = this;
			$(tile).attr('tile-no', thisTileNo)
			$(tile).find(".down-count").click( function(event){
								    	
				var curCount = Number($(tile).attr("count"));
				if (curCount > 0) {
					
					$(tile).attr("count", curCount - 1);
					$(tile).find(".count").text(curCount - 1);
					$(".create-a-wall-editor .tile[tile-no='" + thisTileNo + "']").last().remove();
					checkIfEmpty();
					updateCreateAWall();
				}
			});
			$(tile).find(".up-count, .image, .tile-label").click( function(event){
    			
    			event.preventDefault();
				var guid = newGUID();
				var item = $(tile).clone().html();
				var item = "<div id='" + guid + "' tile-no='" + thisTileNo + "'' class='tile'>" + item + '</div>';

				var curCount = Number($(tile).attr("count"));
				$(tile).attr("count", curCount + 1);
				$(tile).find(".count").text(curCount + 1);

				var firstAvailableEmpty = $(".wall-tiles-wrapper .empty-tile").first();
				$(firstAvailableEmpty).after(item);
				firstAvailableEmpty.remove();

				// if ($(".wall-tiles-wrapper .tile:first-of-type").hasClass("empty-tile")) {

				// 	$(".wall-tiles-wrapper .tile:first-of-type").remove();
				// 	$(".wall-tiles-wrapper").prepend(item);
				// } else {

				// 	$(".wall-tiles-wrapper").prepend(item);
				// 	$(".wall-tiles-wrapper .tile:last-of-type").remove();
				// }
				checkIfEmpty();
				updateCreateAWall();
				    	
				$(".tile#" + guid).find(".remove").click( function(){
					    	
					var curCount = Number($(tile).attr("count"));
					$(tile).attr("count", curCount - 1);
					$(tile).find(".count").text(curCount - 1);
					$(emptyTile).insertAfter($(".tile#" + guid));
					$(".tile#" + guid).remove();
					checkIfEmpty();
					updateCreateAWall();
				});
			});

			// $(tile).find(".down-count").click( function(){

			// 	$(tile).attr("count", curCount - 1);
			// 	$(tile).find(".count").text(curCount - 1);
			// });
		});
	}
	addTilesToWall();

	// $('.tile.vertical-center-wrap').each( function(){
	// 	$(this).draggable();
	// });

	function checkIfEmpty(){

		if ($(".wall-tiles-wrapper .tile.empty-tile").length === $(".wall-tiles-wrapper .tile").length ) {

			$(".create-a-wall-editor").removeClass('has-tiles');
		} else {

			$(".create-a-wall-editor").addClass('has-tiles');
		}
	}
	checkIfEmpty();

	$(".rows .slider").slider({

		orientation: "horizontal",
		range: "min",
		min: 1,
		max: 10,
		value: 5,
		animate: true,
		slide: function( event, ui ) {

			$(this).find( ".ui-slider-handle" ).attr("value", ui.value);
			$(this).parent().find( ".amount" ).text(ui.value);
			$(".wall-tiles").attr("tiles-per-row", ui.value);
			updateCreateAWall();
		},
		create: function( event, ui ) {

			$(this).find( ".ui-slider-handle" ).attr("value", "5");
			$(".wall-tiles").attr("tiles-per-row", 5);
		}
	});

	$(".gutter .slider").slider({

		orientation: "horizontal",
		range: "min",
		min: 1,
		max: 20,
		value: 5,
		animate: true,
		slide: function( event, ui ) {

			$(this).find( ".ui-slider-handle" ).attr("value", ui.value + "px");
			$(this).parent().find( ".amount" ).text(ui.value + "px");
			$(".wall-tiles").attr("padding-per-row", ui.value);
			updateCreateAWall();
		},
		create: function( event, ui ) {

			$(this).find( ".ui-slider-handle" ).attr("value", "5px");
			$(".wall-tiles").attr("padding-per-row", "5");
		}
	});

	$(".color-item").click( function(){

		$(".color-item.active").removeClass("active");
		$(this).addClass("active");
		$(".wall-tiles").attr("background-color", $(this).attr("color"));
		$(".create-a-wall-editor").css("background", "#" + $(this).attr("color"));
		$("body").attr("background-color", $(this).attr("color"));
	});

	function insertAtIndex(i, wrap, html) {

	    if(i === 0) {

	     	$(wrap).prepend(html);        
	     	return;
	    }
	    $(wrap + " > .tile:nth-child(" + (i) + ")").after(html);
	}

	function updateCreateAWall(){

		var margin = $(".wall-tiles").attr("padding-per-row");
		var width = $(".wall-tiles").attr("tiles-per-row");
		$(".wall-tiles-wrapper")
			.css("margin", "-" + (margin/2) + "px")
			.css("width", "calc(100% + " + (margin) + "px)")
			.css("height", "calc(100% - " + (margin) + "px)");	
		$(".tile-style").html("<style>.wall-tiles .tile{" +
			"width:" + "calc(" + (100 / width) + "% - " + margin + "px)!important;" +
			"margin:" + margin/2 + "px!important;" +
			"margin-bottom:" + margin/2 + "px!important;" +
			"}</style>");	
		setCreateAWallEditorTileHeights();


		// CREATING IDEAL TILE HEIGHT
		if ($("html").hasClass('page-loaded')) {
			var wallWidth = $(".wall-tiles-wrapper").width();
			var wallHeight = $(".wall-tiles-wrapper").height();
			var tileHeight = $(".wall-tiles-wrapper .tile:last-of-type").outerHeight();
			var tilesPerWall =  Math.floor((wallHeight / tileHeight)) * width;
			var curTileCount = $(".wall-tiles-wrapper .tile").length;
			if (tilesPerWall > curTileCount) {
				var dif = tilesPerWall - curTileCount;
				for (var i = 0; i < Math.floor(dif); i++) {
					$(".wall-tiles-wrapper").append(emptyTile);
				}
			}
			if (tilesPerWall < curTileCount) {
				if (curTileCount > 26) {
					var dif = curTileCount - tilesPerWall;
					if (dif !== 0) {
						$(".wall-tiles .tile").slice(-dif).remove();
					}
				}
			}

			curTileCount = $(".wall-tiles-wrapper .tile").length;
			var toRemove = curTileCount % width;
			    	
			if (toRemove !== 0) {
				$(".wall-tiles .tile").slice(-toRemove).remove()
			}  

			for (var i = 0; i < Math.floor(width); i++) {
				$(".wall-tiles-wrapper").append(emptyTile);
			} 

		}

		$(".wall-tiles .tile")
			// .css("width", "calc(" + (100 / width) + "% - " + margin + "px)")
			// .css("margin", margin/2 + "px")
			.css('margin-bottom', 0 + "px")
		 	.slice(-width).attr('style', 'margin-bottom: 0!important');
		// Sorting Mechanizm
		var tmpTile = '<div class="tile empty-tile temp-tile" ></div>';
		var emptyTileIndex = 0;
		var emptyTileToggle = false;
		var insertedTempTile = false;
		function setSortable(){
		    $( ".wall-tiles-wrapper" ).sortable({
		    	placeholder: "tile",
		    	items: "> .tile",
	  			scroll: false,
	  			tolerance: "pointer",
	  			handle: '.image',


		        start: function(event, ui) {

			        $(".temp-tile").removeClass("temp-tile");
		            var start_pos = ui.item.index();
		            ui.item.data('start_pos', start_pos);
		        },
		        change: function(event, ui) {

		            var start_pos = ui.item.data('start_pos');	                	
		            var index = ui.placeholder.index();
		        	// $(".temp-tile").remove();
		            if (emptyTileToggle === true) {

		            	$(".wall-tiles-wrapper > .tile:nth-child(" + (emptyTileIndex + 1) + ")").after(emptyTile);
		            }
		            if (start_pos !== index) {

		            	if (insertedTempTile !== true ) {

							insertAtIndex(start_pos, ".wall-tiles-wrapper", tmpTile);
							insertedTempTile = true;
		            	}
		            } else {

		        		$(".temp-tile").remove();
						insertedTempTile = false;
		            }
		                	
		            if ($(".wall-tiles-wrapper > .tile:nth-child(" + (index + 2) + ")").hasClass('empty-tile')) {
		            	    	
		            	$(".wall-tiles-wrapper > .tile:nth-child(" + (index + 2) + ")").remove();
		            	emptyTileToggle = true;
		            	emptyTileIndex = index;
		            } else {

		            	emptyTileToggle = false;
		            }
		            $(".tile:not(.ui-sortable-handle)").addClass('ui-sortable-handle');
		        	$(".wall-tiles-wrapper").trigger("sortupdate");

		        	$( ".wall-tiles-wrapper" ).sortable( "refresh" );
		        },
		        update: function(event, ui) {

		            $(".temp-tile").removeClass("temp-tile");
		            emptyTileToggle = false;
		            insertedTempTile = false;
		        }

		    });
			// var tmpTile = $( ".wall-tiles-wrapper .empty-tile:last-of-type" ).clone().addClass('temp-tile');
		}
		setSortable();
	    $( ".wall-tiles-wrapper" ).disableSelection();
	}
	updateCreateAWall();

	$(window).load(function() {
	});

	////////////////////////////////////////////////
	// ON RESIZE
	var updateCreateAWallPage = _.debounce(function(e) {
		createAWallTileHeights();
			updateCreateAWall();

	}, 500);
	window.addEventListener("resize", updateCreateAWallPage, false);

}