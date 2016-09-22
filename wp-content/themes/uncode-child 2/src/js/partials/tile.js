
if ( $("body").hasClass('single-tile') ) {

   	var url					= "/wp-content/themes/XCollection",
   		startPos			= 222,
   		tileSizeList		= [ 250, 500, 750, 1000, 1250, 1500, 1750 ],
   		tileSize			= 6,
   		MoztileSize			= 500,
   		position			= 0,
   		tilePosition		= startPos,
   		clickTilePos		= 1,
   		tempSavePosition	= startPos,
   		mouseDown 			= false,
   		mouseDownPos		= 0,
   		tileCount			= 240,
   		docWidth			= $(document).width(),
   		pageTileFrac		= tileCount/docWidth,
   		running				= 0;

   	var url 				= "http://localhost:8888/XCollection-SidDickens/wp-content/themes/XCollection";
   	var url 				= "https://s3.amazonaws.com/x-collection";
   	var url 				= "http://d1e619unrocd59.cloudfront.net";

   	var tileName = $(".spinner-wrap").attr("tile");

   	var tileUrl = "";
   	var fileType = "";

   	if (tileName === "tree-of-life") {
   		tileUrl = "tree-tile/tree_";
   		fileType = "jpeg";
   	} else if (tileName === "fabled-bird") {
   		tileUrl = "peacock-tile/Peacock_";
   		fileType = "jpg";
   	} else if (tileName === "sacred-heart") {
   		tileUrl = "heart-tile/Heart_";
   		fileType = "jpg";
   	}


	////////////////////////////////////////////////
	// SPINNER

   	// var cssSave = "";
   	// for (var i = 0; i < 240; i++) {

   	// 	cssSave += ' url("http://localhost:8888/XCollection-SidDickens/wp-content/themes/XCollection/dist/img/" + tileUrl + "v1_'+lpad(i, 3)+'.1250.jpeg")'
   	// }
   	// console.log(cssSave);
   	 
	function pause(milliseconds) {

		var dt = new Date();
		while ((new Date()) - dt <= milliseconds) {  /* Do nothing */ }
	}



	////////////////////////////////////////////////
	// SPINNER
	function destroySpinner(){

		if (tileSizeList[tileSize] < $(document).width() ) {

			$(".spinner-wrap .spinner").html("");
			createSpinner();
		}
	}
	function createSpinner(){

		// if ($('html').hasClass('moz')) {

		// 	for (var i = 0; i < tileCount; i++) {

		// 		console.log(i);
		// 		var tempI 		= i,
		// 			loadingIMG 	= false;
		// 		$(".slide" + i).on('load', function() {

 
		// 			loadingIMG 	= true;
		// 		})
		// 		if (loadingIMG) {

		// 			$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + MoztileSize + '.' + fileType + '\');" ></div>');
		// 		} else{

		// 			i = tempI - 1;
		// 		}
		// 	}	

		// if ($('html').hasClass('moz')) {


		// 	for (var i = 0; i < tileCount; i++) {

		// 		var image_holder = new Image();
		// 		image_holder.src = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + MoztileSize + '.' + fileType;				    	
		// 		$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder.src + '\');" ></div>');
		// 	}		

		if ($(document).width() < xsmall) {

   			tileSize = 2;
		} else if ($(document).width() < small) {

   			tileSize = 3;
		} else if ($(document).width() < medium) {

   			tileSize = 4;
		} else if ($(document).width() < large) {

   			tileSize = 5;
		} else if ($(document).width() < xlarge) {

   			tileSize = 6;
		} else if ($(document).width() < xxlarge) {

   			tileSize = 6;
		} else {

   			tileSize = 6;
		} 

		$(".spinner-wrap").css("background-image", "url('" + url + "/" + tileUrl + "v1_" + lpad((startPos), 3) + '.' + tileSizeList[tileSize] + "." + fileType + "')");
		var t2 = tileCount/2;
		for (var i = 0; i < t2; i++) {

			var image_holder = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSizeList[tileSize] + '.' + fileType;				    	
			$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder + '\');" ></div>');
		}		
		$(window).load(function() {

			for (var i = 0+t2; i < t2+t2; i++) {

				var image_holder = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSizeList[tileSize] + '.' + fileType;				    	
				$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder + '\');" ></div>');
			}						    	
			$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
			setTimeout(function(){

				if ($(".spinner-wrap .spinner > div.active").length = 0) {

					$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
				}
			}, 100);
		});
		// if ($('html').hasClass('moz')) {

		// 	$(".spinner-wrap").css("background-image", "url('" + url + "/" + tileUrl + "v1_" + lpad((startPos), 3) + '.' + tileSize + "." + fileType + "')");
		// 	var t2 = tileCount/2
		// 	for (var i = 0; i < t2; i++) {

		// 		var image_holder = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSize + '.' + fileType;				    	
		// 		$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder + '\');" ></div>');
		// 	}		
		// 	$(window).load(function() {

		// 		for (var i = 0+t2; i < t2+t2; i++) {

		// 			var image_holder = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSize + '.' + fileType;				    	
		// 			$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder + '\');" ></div>');
		// 		}						    	
		// 		$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
		// 		setTimeout(function(){

		// 			if ($(".spinner-wrap .spinner > div.active").length = 0) {

		// 				$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
		// 			}
		// 		}, 100);
		// 	});
		// 	// var t2 = tileCount/2
		// 	// for (var i = 0; i < t2; i++) {

		// 	// 	var image_holder = new Image();
		// 	// 	image_holder.src = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + MoztileSize + '.' + fileType;				    	
		// 	// 	$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder.src + '\');" ></div>');
		// 	// }		
		// 	// $(window).load(function() {

		// 	// 	for (var i = 0+t2; i < t2+t2; i++) {

		// 	// 		var image_holder = new Image();
		// 	// 		image_holder.src = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + MoztileSize + '.' + fileType;				    	
		// 	// 		$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder.src + '\');" ></div>');
		// 	// 	}		
		// 	// 	$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
		// 	// });
		
		// } else {

		// 	// for (var i = 0; i < tileCount; i++) {

		// 	// 	$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSize + '.' + fileType + '\');" ></div>')
		// 	// }	
		// 	// $(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
		// 	$(".spinner-wrap").css("background-image", "url('" + url + "/" + tileUrl + "v1_" + lpad((startPos), 3) + '.' + tileSize + "." + fileType + "')");
		// 	var t2 = tileCount/2
		// 	for (var i = 0; i < t2; i++) {

		// 		var image_holder = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSize + '.' + fileType;				    	
		// 		$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder + '\');" ></div>');
		// 	}		
		// 	$(window).load(function() {

		// 		for (var i = 0+t2; i < t2+t2; i++) {

		// 			var image_holder = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSize + '.' + fileType;				    	
		// 			$(".spinner-wrap .spinner").append('<div class="spinner-slide slide' + (i+1) + '" style="background-image: url(\'' + image_holder + '\');" ></div>');
		// 		}						    	
		// 		$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
		// 		setTimeout(function(){

		// 			if ($(".spinner-wrap .spinner > div.active").length = 0) {

		// 				$(".spinner-wrap .spinner > div:nth(" + startPos + ")").addClass('active');
		// 			}
		// 		}, 100);
		// 	});

		// }
	}
	createSpinner();


	document.body.onmousedown = function(event) {

	  	mouseDown = true;	
	  	clickTilePos = tilePosition;		  	    	
	  	mouseDownPos = event.clientX;
	  	$(".spinner-wrap").addClass("grabbed");  
	  	$(".mozWarning").removeClass("mozWarning"); 	
	};

	document.body.onmouseup = function() {

	  	tilePosition = tempSavePosition;	      	
	  	mouseDown = false;	
	  	$(".spinner-wrap").removeClass("grabbed");   	
	};
	document.body.onmouseleave = function() {

	  	tilePosition = tempSavePosition;	      	
	  	mouseDown = false;	
	  	$(".spinner-wrap").removeClass("grabbed");   	
	};

	function lpad(value, padding) {

	    var zeroes = new Array(padding+1).join("0");
	    return (zeroes + value).slice(-padding);
	}
	
	//$(".spinner-wrap").css("background-image", "url('" + url + "/dist/img/" + tileUrl + "v1_" + lpad(startPos, 3) + ".1250.jpeg')");
	function printMousePos(event) {
		   	    			    	    	   	    			    	
  		if (!running){

  			++running;
			if (mouseDown) {

				position = parseInt( ( event.clientX - mouseDownPos ) * pageTileFrac );
				var tempPosition = tilePosition + position;
				if (tempPosition >= tileCount+1) {

					tempPosition = 1 + (tempPosition % tileCount);
				} else if (tempPosition <= 0) {

					tempPosition = tileCount + tempPosition;
				}
				tempSavePosition = tempPosition;	

				// var bgurlSuccess = false;
				var bgurl = $(".spinner-wrap .spinner > div:nth(" + (tempPosition-1)  + ")")
					.bgLoaded({

					    afterLoaded : function() {

							// bgurlSuccess = true;
							$(".spinner-wrap").css("background-image", $(".spinner-wrap .spinner > div:nth(" + (tempPosition-1)  + ")").css("background-image"));
					    }
					});
				//  setTimeout( function() {

				// 	if (bgurlSuccess == false) {

				// 		var tempImage_holder = url + "/" + tileUrl + "v1_" + lpad((tempPosition), 3) + '.' + tileSizeList[tileSize - 1] + '.' + fileType;				    	
				// 		$(".spinner-slide.slide" + (tempPosition)).css('background-image', 'url(\'' + tempImage_holder + '\')');
				// 	}
				// }, 5000);

				// bgurl = bgurl.replace('url(','').replace(')','');
				    	
				$(".spinner-wrap .spinner > div:nth(" + (tempPosition-1)  + ")").addClass('tmp-active');
				$(".spinner-wrap .spinner > div").removeClass('active');
				$(".spinner-wrap .spinner > div:nth(" + (tempPosition-1)  + ")").addClass('active');
				$(".spinner-wrap .spinner > div").removeClass('tmp-active');
				
				// if ($('html').hasClass('moz')) {

				// 	image_holder.src = url + "/" + tileUrl + "v1_" + lpad((i+1), 3) + '.' + tileSize + '.' + fileType;				    	
				// 	$(".spinner-wrap").css("background-image", "url('" + url + "/dist/img/" + tileUrl + "v1_" + lpad(tempPosition, 3) + ".1250.jpeg')");

				// } else {

				// 	$(".spinner-wrap .spinner > div:nth(" + (tempPosition-1)  + ")").addClass('tmp-active');
				// 	$(".spinner-wrap .spinner > div").removeClass('active');
				// 	$(".spinner-wrap .spinner > div:nth(" + (tempPosition-1)  + ")").addClass('active');
				// 	$(".spinner-wrap .spinner > div").removeClass('tmp-active');
				// }
			}	
  			--running;
		}
	}

	document.body.onmousemove = function(event) {

	  	printMousePos(event);	
	};
	
	//////////////////////////////////////////////////////
	// Hider for page

	function hideTileInfo(){

		var adjHeight = $(".tile-description").height() + $(".add-to-cart").height() -1;
		$(".tile-title, .status").css("top", "calc(" + adjHeight + "px - 3.5rem)");
	}
	$(".hider a").click(function(){

		hideTileInfo();
		$(this).closest(".tile-description-wrapper").toggleClass("disc-hidden");
	});
	

	////////////////////////////////////////////////
	// SET MARGIN FOR DESCRIPTION

	function setDesription(){

		$(".tile-description-wrapper").each(function(index, el) {

			$(this).css('margin-top', 0); 
			if ($(document).width() > medium) {

				var marginHeight = $(document).height() - $(this).height();				    	
				$(this).css('margin-top', marginHeight); 
			}
		});
	}

	$(window).load(function() {

		setDesription();
	});


	////////////////////////////////////////////////
	// ON RESIZE
	var updateDesription = _.debounce(function(e) {

		setDesription();
		setTimeout(setDesription(), 1000);
		hideTileInfo();
		setTimeout(hideTileInfo(), 1000);
		// destroySpinner();

	}, 500);
	window.addEventListener("resize", updateDesription, false);
}