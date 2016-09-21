/**
 * CodePeople Post Map 
 * Version: 1.0.1
 * Author: CodePeople
 * Plugin URI: http://wordpress.dwbooster.com
*/

(function ($) {
	var _latlng_btn,
		map_loaded = false,
        shape,
		shape_map,
		shape_markers = [],
		shape_markers_path;
	
	// thumbnail selection
    window["cpm_thumbnail_selection"] = function(e){
        var thumbnail_field = $(e).parent().find('input[type="text"]');
        var media = wp.media({
                title: 'Select Point Thumbnail',
                button: {
                text: 'Select Image'
                },
                multiple: false
        }).on('select', 
			(function( field ){
				return function() {
					var attachment = media.state().get('selection').first().toJSON();
					if( !/image/g.test( attachment.mime ) ) return;
					var fullSize = attachment.url;
					var imgUrl = (typeof attachment.sizes.thumbnail === "undefined") ? fullSize : attachment.sizes.thumbnail.url;
					field.val( imgUrl );
				};
			})( thumbnail_field )	
		).open();
        return false;
    };
    
    //---------------------------------------------------------
    
    window['cpm_get_latlng'] = function (){
		function transform( v )
		{
			if( $.isNumeric( v ) ) return v;
			v = v.replace(/[\W_]/g, " " ).replace(/\s+/g, ' ' ).replace( /^\s+/, '' ).replace( /\s+$/, '' ).toLowerCase();
			var ref = ( /[ne]/.test( v ) ) ? 1 : -1,
				parts = v.split( ' ' ),
				l = parts.length;
			
			if( l >= 3 ) return ref * ( parts[ l - 3 ]*1 + parts[ l - 2 ]*1 / 60 + parts[ l - 1 ]*1  / 3600 );	
			return v;	
		};
		
		var g  			= new google.maps.Geocoder(),
			f 			= _latlng_btn.parents('.point_form'),
			a 			= f.find('#cpm_point_address').val(),
			longitude 	= f.find('#cpm_point_longitude').val(),
			latitude 	= f.find('#cpm_point_latitude').val(),
			language	= f.find('#cpm_map_language').val(),
			request		= {};
		
		// Remove unnecessary spaces characters
		longitude = longitude.replace(/^\s+/, '').replace(/\s+$/, '');
		latitude  = latitude.replace(/^\s+/, '').replace(/\s+$/, '');
		a = a.replace(/^\s+/, '').replace(/\s+$/, '');
		
		if(longitude.length && latitude.length){
			request['location'] = new google.maps.LatLng( transform( latitude ), transform( longitude ) )	;
		}else if(a.length){
			request['address'] = a.replace(/[\n\r]/g, '');
		}else{
			return false;
		}	

		g.geocode(
			request, 
			( function( a, r )
				{
					return  function(result, status){
								if(status && status == "OK"){
									// Update fields
									var address   = ( $.trim( a ).length && typeof  r[ 'location' ] != 'undefined' ) ? a : result[0]['formatted_address'],
										latitude  = result[0]['geometry']['location'].lat(),
										longitude = result[0]['geometry']['location'].lng();

									if(address && latitude && longitude){
										f.find('#cpm_point_address').val(address);
										f.find('#cpm_point_longitude').val(longitude);
										f.find('#cpm_point_latitude').val(latitude);
										
										// Load Map
										cpm_load_map(f.find('.cpm_map_container'),latitude, longitude);
									}
								}else{
									alert('The point is not located');
								}
							};
				} 
			)( a, request ) 
		);
	};
	
	window['cpm_edit_point'] = function(id){
		var f = $('#point_form'+id);
		if(f.length && f.is(":hidden")){
			$('.point_form').hide();
			f.show();
			var latitude = f.find('#cpm_point_latitude').val(),
				longitude = f.find('#cpm_point_longitude').val();
			if(!is_empty(latitude) && !is_empty(longitude)){
					// Load Map
					cpm_load_map(f.find('.cpm_map_container'),latitude, longitude);
				}
		}
		
	};
	
	window['cpm_delete_point'] = function(id, e){
		var v = $( '#cpm_points_order' ).val();
		
		$( '#cpm_points_order' ).val( v.replace( '|' + id + '|', '|' ) );
		
		$('#point_form'+id).remove();
		$('#point_form').show();
		$(e).parents('li').remove();
	};
	
	function is_empty(v){
		return /^\s*$/.test(v);
	};
	
	function clear_form(){
		var f = $('#point_form'),
			d = $('#cpm_map_default_icon').val();

		f.find('input[type="text"]').val("");
		f.find('#cpm_point_thumbnail').val("");
		f.find('#selected_icon').val(d);
		f.find('.cpm_selected').removeClass('cpm_selected');
		f.find('img[src="'+d+'"]').parent().addClass('cpm_selected');
		
		// Clear map
		f.find('.cpm_map_container').replaceWith('<div id="cpm_map_container0" class="cpm_map_container" style="width:400px; height:250px; border:1px dotted #CCC;"><div style="margin:20px;">To correct the latitude and longitud directly on MAP, type the address and press the Verify button.</div></div>');
			
	}
	
	// Check the point or address existence
	window['cpm_checking_point'] = function (e){
		var language = 'en';
		_latlng_btn = $(e);

		if(typeof google != 'undefined' && google.maps){
			cpm_get_latlng();
		}else{
			$('<script type="text/javascript" src="'+(( typeof window.location.protocol != 'undefined' ) ? window.location.protocol : 'http:' )+'//maps.google.com/maps/api/js?sensor=false'+((language) ? '&language='+language: '')+'&callback=cpm_get_latlng"></script>').appendTo('body');
		}
	};
	
	window['cpm_load_map'] = function(container, latitude, longitude){
		var c = container,
			f = c.parents('.point_form'),
			p = new google.maps.LatLng(latitude, longitude),
			m = new google.maps.Map(c[0], {
								zoom: 5,
								center: p,
								mapTypeId: google.maps.MapTypeId['ROADMAP'],
								
								// Show / Hide controls
								panControl: true,
								scaleControl: true,
								zoomControl: true,
								mapTypeControl: true,
								scrollWheel: true
						}),
			mk = new google.maps.Marker({
							  position: p,
							  map: m,
							  icon: new google.maps.MarkerImage(cpm_default_marker),
							  draggable: true
						 });
			
			google.maps.event.addListener(mk, 'position_changed', function(){
				f.find('#cpm_point_latitude').val(mk.getPosition().lat());
				f.find('#cpm_point_longitude').val(mk.getPosition().lng());
			});				
	};
	
	window['cpm_set_map_flag'] = function(){
		map_loaded = true;
	};
	
    window[ 'cpm_display_more_info' ] = function( e ){
        e = $( e );
        e.parent().hide().siblings( '.cpm_more_info' ).show();
    };
    
    window[ 'cpm_hide_more_info' ] = function( e ){
        e = $( e );
        e.parent().hide().siblings( '.cpm_more_info_hndl' ).show();
    };
    
    function enable_disable_fields(f, v){
        var p = f.parents('#map_data');
        p.find('input[type="text"]').attr({'DISABLED':v,'READONLY':v});
        p.find('select').attr({'DISABLED':v,'READONLY':v});
        p.find('input[type="checkbox"]').filter('[id!="cpm_map_single"]').attr({'DISABLED':v,'READONLY':v});
    };
    
	window[ 'cpm_hide_show_shape_fields' ] = function(){
		if( typeof shape_markers_path == 'undefined' || shape_markers_path == null)
		{
			shape_markers_path = new google.maps.MVCArray
		}
		
		if( $( '#cpm_shape_selector' ).is( ':checked' ) )
		{
			$( '#cpm_shape_data' ).show();
			if( typeof shape_map == 'undefined' || shape_map == null )
			{
				var center_latlng = new google.maps.LatLng( 40.714623, -74.006605 ),
					shape_attr = {
									strokeWeight: 0,
									strokeColor : '#333333',
									fillColor	: '#333333',
									fillOpacity	: 0.5
								};
				
				if( !/^\s*$/.test( $( '#cpm_shape_stroke_weight' ).val() ) )
				{
					shape_attr[ "strokeWeight" ] = $.trim( $( '#cpm_shape_stroke_weight' ).val() );
				}
				
				if( !/^\s*$/.test( $( '#cpm_shape_fill_color' ).val() ) )
				{
					shape_attr[ "fillColor" ] = $.trim( $( '#cpm_shape_fill_color' ).val() );
					shape_attr[ "strokeColor" ] = shape_attr[ "fillColor" ];
				}
				
				if( !/^\s*$/.test( $( '#cpm_shape_fill_opacity' ).val() ) )
				{
					shape_attr[ "fillOpacity" ] = $.trim( $( '#cpm_shape_fill_opacity' ).val() );
				}
				
				shape_map = new google.maps.Map( document.getElementById( 'cpm_shape_map_container' ), {
				  zoom: 3,
				  center: center_latlng,
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				});

				shape = new google.maps.Polygon( shape_attr );
				shape.setMap( shape_map );
				shape.setPaths( new google.maps.MVCArray( [ shape_markers_path ] ) );
				google.maps.event.addListener( shape_map, 'click', shape_add_point );
			}
		}
		else
		{
			$( '#cpm_shape_data' ).hide();
		}
	};	
	
	window[ 'shape_change_settings' ] = function( e, attr )
	{
		if( typeof shape != 'undefined' && shape != null )
		{
			
			var attr_obj = {};
			attr_obj[ attr ] = e.value;
			shape.setOptions( attr_obj );
		}	
	};
	
	window[ 'shape_add_point' ] = function( event ) {
		shape_markers_path.insertAt( shape_markers_path.length, event.latLng );

		var marker = new google.maps.Marker({
		  position: event.latLng,
		  map: shape_map,
		  draggable: true
		});
		
		shape_markers.push( marker );
		marker.setTitle( "#" + shape_markers_path.length );
		
		google.maps.event.addListener( marker, 'click', function() {
			  marker.setMap(null);
			  for (var i = 0, I = shape_markers.length; i < I && shape_markers[i] != marker; ++i);
			  shape_markers.splice(i, 1);
			  shape_markers_path.removeAt(i);
		  }
		);

		google.maps.event.addListener(marker, 'dragend', function() {
			  for (var i = 0, I = shape_markers.length; i < I && shape_markers[i] != marker; ++i);
			  shape_markers_path.setAt(i, marker.getPosition());
		  }
		);
	};
	
	$(function(){
		// Actions for icons
		$(".add_point").bind('click', function(){
			var f = $('.point_form:visible'),
				v = f.find('#cpm_point_address').val(),
                lat = f.find('#cpm_point_latitude').val(),
                lng = f.find('#cpm_point_longitude').val();
			v = v.replace(/'/g, "\'").replace(/"/g, '\"');

			if(!(is_empty(v) || is_empty(lat) || is_empty(lng))){
				var	c = f.clone(true),
					id = f.find('#cpm_point_id');

				if(id.length){
					var id_val = id.val();
					$('#cpm_point'+id_val).find('span').text(v);
					$('#point_form'+id_val).hide();
					$('#point_form').show();
				}else{
					c.attr('id', 'point_form'+cpm_point_counter);
					c.append('<input type="hidden" id="cpm_point_id" value="'+cpm_point_counter+'" />');
					c.find('input').each(function(){
						var n = $(this).attr('name');
							
						if(n){
							n = n.replace('[0]', '['+cpm_point_counter+']');
							$(this).attr('name', n);
						}	
					});	
				
					f.after(c.hide());
					$("#points_container").append('<li><div class="button" id="cpm_point'+cpm_point_counter+'" style="display:inline-block;height:auto;"><img class="handle" /><span onclick="cpm_edit_point('+cpm_point_counter+')">'+v+'</span><input type="button" value="X" onclick="cpm_delete_point('+cpm_point_counter+', this);"  /></div></li>');
					
					var v = $( '#cpm_points_order' ).val();
					$( '#cpm_points_order' ).val( v + cpm_point_counter + '|' );
					
					cpm_point_counter++;
					clear_form();
				}
			}else{
				alert('Address, latitude and longitude are required. Please, enter the address and press the verify button.');
			}	
		});
		
        $(".cpm_icon").bind('click', function(){
			var  i = $(this),
				 f = i.parents('.point_form');
			
			f.find('.cpm_icon.cpm_selected').removeClass('cpm_selected');
			i.addClass('cpm_selected');
			f.find('#selected_icon').val($('img', i).attr('src'));
		}).mouseover(function(){
			$(this).css({"border":"solid #BBBBBB 1px"})
		}).mouseout(function(){
			$(this).css({"border":"solid #F9F9F9 1px"})
		});
		
		// Action for insert shortcode 
		$('#cpm_map_shortcode').click(function(){
            if(window.cpm_send_to_editor_default)
                window.send_to_editor = window.cpm_send_to_editor_default;
        	if(send_to_editor){
				var c = '',
					l = $( '#pointsCategories' ).val();	
					
				if( l )
				{
					var s = [];
					for( var i in l )
					{
						if( l[ i ] == -1 )
						{
							s = [-1];
							break;
						}else
						{
							s.push( l[ i ] );
						}
					}
					c = ' cat="'+s.join( ',' )+'"';
				}
				
				var shortcode = '[codepeople-post-map'+c+']'; 
				
				// Shape 
				if( $( '#cpm_shape_selector' ).is( ':checked' ) && shape_markers.length )
				{
					var shape_str = '"paths" :[',
						separator = '';
					for( var i = 0, h = shape_markers.length; i < h; i++ )
					{
						shape_str += separator + '{"lat":' + shape_markers[ i ].position.lat() + ',"lng":' + shape_markers[ i ].position.lng() + '}';
						separator = ',';
					}
					shape_str += ']';
					
					if( !/^\s*$/.test( $( '#cpm_shape_stroke_weight' ).val() ) )
					{
						shape_str += separator + '"strokeWeight":' + $.trim( $( '#cpm_shape_stroke_weight' ).val() );
					}
					
					if( !/^\s*$/.test( $( '#cpm_shape_fill_color' ).val() ) )
					{
						shape_str += separator + '"fillColor":"' + $.trim( $( '#cpm_shape_fill_color' ).val() ) + '"';
					}
					
					if( !/^\s*$/.test( $( '#cpm_shape_fill_opacity' ).val() ) )
					{
						shape_str += separator + '"fillOpacity":' + $.trim( $( '#cpm_shape_fill_opacity' ).val() );
					}
					
					shortcode += '{' + shape_str + '}' + '[/codepeople-post-map]';
				}
				
				send_to_editor( shortcode );
				
				$( '#pointsCategories option' ).prop( 'selected', false );
			}
        });
		
		// Create the script tag and load the maps api
		if($('.cpm_map_container').length){
			$('<script type="text/javascript" src="'+(( typeof window.location.protocol != 'undefined' ) ? window.location.protocol : 'http:' )+'//maps.google.com/maps/api/js?sensor=false&callback=cpm_set_map_flag"></script>').appendTo('body');
		};
        
        $('#cpm_map_single').each(function(){
            var f = $(this);
            enable_disable_fields(f, !f[0].checked);
            f.click(function(){
                enable_disable_fields(f,!f[0].checked);
            });
        });
        
        $('#cpm_map_stylized').click(function(){
			if( this.checked ) $('#cpm_map_styles').attr( { 'disabled' : false, 'readonly' : false });
			else  $('#cpm_map_styles').attr( { 'disabled' : true, 'readonly' : true });
		});
		
		// Enable the drag and drop and sortable
		$( "#points_container" ).sortable( 
			{ 
				handle: '.handle',
				stop: function( evt, ui )
				{
					var v = '|';
					
					function getID( id )
					{
						return id.replace( 'cpm_point', '' );
					};
					
					$( '#points_container .button' ).each(
						function()
						{
							v += getID( this.id ) + '|';
						}
					)
					$( '#cpm_points_order' ).val( v );
				}
			} 
		);
		$( "#points_container" ).disableSelection();
		
		// Shape fields
		$( "#cpm_shape_selector" ).change(
			function( evt )
			{
				if(typeof google != 'undefined' && google.maps){
					cpm_hide_show_shape_fields();
				}else{
					$('<script type="text/javascript" src="'+(( typeof window.location.protocol != 'undefined' ) ? window.location.protocol : 'http:' )+'//maps.google.com/maps/api/js?sensor=false'+((language) ? '&language='+language: '')+'&callback=cpm_hide_show_shape_fields"></script>').appendTo('body');
				}
			}
		);
		
		try
		{
			$('#cpm_shape_color_container').hide();
			$('#cpm_shape_color_container')
			 .farbtastic("#cpm_shape_fill_color")
			 .mouseup( 
				function()
				{
					$( '#cpm_shape_fill_color' ).change();
				} 
			 );
			$("#cpm_shape_fill_color").click( function(){ $('#cpm_shape_color_container').slideToggle(); } );
		}catch( err )
		{
		}
	});
})(jQuery);