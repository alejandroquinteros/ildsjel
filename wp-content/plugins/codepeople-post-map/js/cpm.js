/**
 * CodePeople Post Map 
 * Version: 1.0.1
 * Author: CodePeople
 * Plugin URI: http://wordpress.dwbooster.com
*/

var CodePeoplePostMapPublicCounter = 0;
function CodePeoplePostMapPublic()
{
	CodePeoplePostMapPublicCounter++;
	if( typeof jQuery == 'undefined' )
	{	
		if( CodePeoplePostMapPublicCounter <= 6 ) setTimeout( function(){ CodePeoplePostMapPublic() }, 1000 );
		return;
	}
	jQuery(function( $ ){
		// Create a class with CodePeople Post Map functionalities and attributes
		$.CPM = function(container, config){
			this.markers = [];
			this.data = $.extend(true, {}, this.defaults, config);
			if( this.data.legend )
			{
				this.legend_container = $( '<fieldset class="cpm-map-legend '+this.data.legend_class+'">'+( ( !/^\s*$/.test( this.data.legend_title) ) ? '<legend>'+this.data.legend_title+'</legend>' : '' )+'</fieldset>' );
				this.legend_container.appendTo( container );
				this._set_legend();
			}
			this.map_container = $( '<div class="cpm-map-container"></div>' );
			this.map_container.attr( 'style', container.attr( 'style' ) ).css( 'margin', '0' ).show().appendTo( container.css( 'height', 'auto' ) );
		}; 
		
		$.CPM.prototype = {
			defaults : {
				your_location   	: false,
				your_location_title : '',
				panoramioLayer	: false,
				MarkerClusterer : false,
				shapes			: {
									paths 		: [],
									strokeWeight: 0,
									fillColor	: '#333333',
									fillOpacity	: 0.5
								  },
				markers 		: [],
				zoom			: 10,
				dynamic_zoom    : false,
				drag_map        : true,
				type			: 'ROADMAP',
				mousewheel 		: true,
				scalecontrol 	: true,
				zoompancontrol 	: true,
				streetviewcontrol: true,
				typecontrol 	: true,
				show_window     : true,
				show_default    : true,
				display			: 'map',
				highlight		: true,
				route           : false,
				polyline        : false,
				polylineColor   : '#FF0000', 
				mode            : 'DRIVING',
				highlight_class : 'cpm_highlight',
				legend          : false,
				legend_title    : '',
				legend_class    : ''
			},
			
			// private methods to complete every steps in map generation
			_correct : function( v ){
				return v*1;
			},
			
			_unique : function( l, process_list ){
				var uniqueArray = {},
					rtn = [];
				for( var i = 0, h = l.length; i < h; i++ )
				{
					if( typeof uniqueArray[ l[ i ].position.toString() ] == 'undefined' )
					{	
						uniqueArray[ l[ i ].position.toString() ] = l[ i ];
						rtn.push( l[ i ] );
					}
					else if( process_list )
					{
						l[ i ].visible = false;
					}	
							
				}
				
				return rtn;
			},
			
			_empty : function (v){
				return (!v || /^\s*$/.test(v));
			},
			
			_get_latlng : function(i){
				var me = this,
					g  = new google.maps.Geocoder(),
					m  = me.data.markers,
					a  = m[i]['address'];
				
				g.geocode({address:a}, function(result, status){
					me.counter--;
					if(status && status == "OK"){
						m[i]['latlng'] = new google.maps.LatLng( me._correct( result[0]['geometry']['location'].lat() ), me._correct( result[0]['geometry']['location'].lng() ) );
					}else{
						m[i]['invalid'] = true;
					}
					
					// All points have been checked now is possible to load the map
					if(me.counter == 0){
						me._load_map();
						
						// Set Route
						me._set_route();
					}
				});
			},
			
			_set_route : function(){
				var me = this,
					m  = me.data.markers;
				
				if(!me.data.route) return;
				// Create the matrix of waypoints
				var wps_list = [],
					c = -1,
					d = 1,
					post = '';
				
				for(var i = 0, h = m.length; i < h; i++){
					if(!m[i]['invalid']){
						if(m[i].post != post || d%9 == 0){
							c++;
							wps_list[c] = [];
							
							if(d%9 == 0 && m[i].post == post){
								wps_list[c].push(wps_list[c-1][wps_list[c-1].length-1]);
							}
							
							post = m[i].post;
							d = 1;
						} 
					   
						if( me.data.polyline ) wps_list[c].push( m[i].latlng );
						else wps_list[c].push({location:m[i].latlng});
						d++;
					}
				}
				
				for(var i = 0, h = wps_list.length; i < h; i++)
				{
					if( me.data.polyline )
					{
						var flightPath = new google.maps.Polyline({
							path: wps_list[ i ],
							geodesic: true,
							strokeColor: me.data.polylineColor,
							strokeOpacity: 1.0,
							strokeWeight: 2
						});

						flightPath.setMap( me.map );
						continue;	
					}	
					var rendererOptions   = { map: me.map, suppressMarkers: true },
						directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions),
						directionsService = new google.maps.DirectionsService(),
						wps = wps_list[i],
						origin, destination, request;
					
					
					var l = wps.length;
					if( l > 1){
						
						request = {
							origin : wps.shift().location,
							destination : wps.pop().location,
							waypoints : wps,
							travelMode: google.maps.DirectionsTravelMode[me.data.mode]
						};
						
						directionsService.route(
							request, 
							(function (dd) {
								return function(response, status) {
									if (status == google.maps.DirectionsStatus.OK) {
										dd.setOptions({preserveViewport:true});
										dd.setDirections(response);
									}
									else
										if(console)
											console.log('failed to get directions');
								}
							})(directionsDisplay)        
						);
					}
				}    
			},
			
			_str_transform : function( t ){
				return t.replace( /&lt;/g, '<')
						.replace( /&gt;/g, '>')
						.replace( /&amp;/g, '&')
						.replace( /&quot;/g, '"')
						.replace(/\\'/g, "'")
						.replace(/\\"/g, '"' );
			},
			
			_createMarker : function( latlng, map, icon, title, m ){
				var marker = new google.maps.Marker({
							  position: latlng,
							  map: map,
							  icon: new google.maps.MarkerImage(icon),
							  title:title
							});

				if( typeof m[ 'taxonomies' ] != 'undefined' ) marker[ 'taxonomies' ] = m[ 'taxonomies' ];
				return marker;
			},
			
			_set_shape : function(){
				// private function
				function replacePaths( arr )
				{
					for( var i = 0, h = arr.length; i < h; i++ )
					{
						if( $.isArray( arr[ i ] ) )
						{
							arr[ i ] = replacePaths( arr[ i ] );
						}
						else
						{
							arr[ i ] = new google.maps.LatLng( arr[ i ][ 'lat' ], arr[ i ][ 'lng' ] );
						}
					}
					return arr;
				};
				
				var me    = this,
					s     = me.data.shapes;
					
				if( s[ 'paths' ].length )
				{
					s[ 'paths' ] = replacePaths( s[ 'paths' ] );
					var poly = new google.maps.Polygon(s);
					poly.setMap( me.map );
				}
			},
			
			// Create clusters
			_set_cluster : function( markers, process_list )
			{
				if( this.data[ 'MarkerClusterer' ] && typeof MarkerClusterer != 'undefined' )
				{
					var uniqueMarkers = this._unique( markers, process_list );
					if( typeof this[ 'markerCluster' ] != 'undefined' ) this[ 'markerCluster' ].clearMarkers();
					this[ 'markerCluster' ] = new MarkerClusterer( this.map, uniqueMarkers );
				}
			},
			
			// Create panoramio layer
			_set_panoramio : function()
			{
				var me = this;
				if( me.data[ 'panoramioLayer' ] && typeof google.maps.panoramio !== 'undefined' )
				{
					me.panoramioLayer = new google.maps.panoramio.PanoramioLayer();
					me.panoramioLayer.setMap( me.map );
					google.maps.event.addListener( me.panoramioLayer, 'click', (function( obj ){
						return function( photo ){ 
							try { 
								obj.infowindow.close(); 
							}catch( e ){}; 
						};
					})( me ) );
				}
			},
			
			_load_map : function(){
				var me = this,
					m  = me.data.markers,
					h  = m.length,
					c  = 0,
					v  = 0; // Number of valid points

				while(c < h && m[c]['invalid']) c++;
				
				if(c < h){
					me.map = new google.maps.Map( me.map_container[ 0 ], {
							zoom: me.data.zoom,
							center: m[c].latlng,
							mapTypeId: google.maps.MapTypeId[me.data.type],
							draggable: me.data.drag_map,
							
							// Show / Hide controls
							panControl: me.data.zoompancontrol,
							scaleControl: me.data.scalecontrol,
							zoomControl: me.data.zoompancontrol,
							mapTypeControl: me.data.typecontrol,
							streetViewControl: me.data.streetviewcontrol,
							scrollwheel: me.data.mousewheel
					});

					if( typeof me.data[ 'map_styles' ] != 'undefined' ) me.map.setOptions({styles: me.data.map_styles });
					
					var map = me.map,
						bounds = new google.maps.LatLngBounds(),
						default_point = -1;
					
					if(me.data.show_default){
						google.maps.event.addListenerOnce(map, 'idle', function(){
							setTimeout(function(){
								if( me.markers.length ) google.maps.event.trigger( ( ( default_point < 0 ) ? me.markers[ 0 ] : me.markers[ default_point ] ), 'click' );
							}, 1000);				
						});
					}
					
					me.infowindow = new google.maps.InfoWindow();

					for (var i = c; i < h; i++){		
						if(!m[i]['invalid']){
						
							if( typeof m[ i ][ 'default' ] != 'undefined' && m[ i ][ 'default' ] )
							{
								default_point = me.markers.length;
							}
							
							bounds.extend(m[i].latlng);
							var marker = me._createMarker( m[i].latlng, map, m[i].icon, ( ( m[i].address ) ? me._str_transform( m[i].address ) : '' ), m[ i ] );
							marker.id = i;
							me.markers.push(marker);
							google.maps.event.addListener(marker, 'click', function(){ me.open_infowindow(this); });
							google.maps.event.addListener(marker, 'mouseover', function(){ me.set_highlight(this); });
							google.maps.event.addListener(marker, 'mouseout', function(){ me.unset_highlight(this); });
						}
					}	
					
					h = me.markers.length;
					if (h > 1 && me.data.dynamic_zoom) {
						setTimeout( ( function( m, b ){ return function(){ m.fitBounds( b ); }; } )( map, bounds ), 500 );
					}
					else if ( h == 1 || !me.data.dynamic_zoom ) {
						if( default_point != -1 )
						{
							map.setCenter( me.markers[ default_point ].getPosition() );
						}
						else
						{
							map.setCenter(bounds.getCenter());
						}
						map.setZoom(me.data.zoom);
					}
					
					if( me.data[ 'your_location' ] )
					{
						if ( typeof navigator.geolocation !== 'undefined' ) 
						{
							navigator.geolocation.getCurrentPosition( ( function( map, obj )
																		{
																			return function( position )
																					{
																						var marker = me._createMarker( new google.maps.LatLng(position.coords.latitude, position.coords.longitude), map, obj.data[ 'your_location_icon' ], obj.data[ 'your_location_title' ], {} );
																						marker.id = obj.data.markers.length;
																						if( !/^\s*$/.test( obj.data[ 'your_location_title' ] ) )
																						{
																							obj.data.markers.push( { info : '<div class="cpm-infowindow" style="min-height:0;"><div class="cpm-content" style="width:auto;"><div class="title">' + obj.data[ 'your_location_title' ] + '</div></div></div>' } );
																							google.maps.event.addListener(marker, 'click', function(){ obj.open_infowindow(this); });
																						}
																					};
																		} )( map, me ) );
						}   
					}
					
					me._set_cluster( me.markers, true );
					me._set_panoramio();
				}
			},
			
			// Create the legend
			_set_legend: function(){
				var me = this,
					m = me.data.markers,
					str = '',
					tmp = {};
					
				for( var i = 0, h = m.length; i < h; i++ )
				{
					if( typeof m[ i ].taxonomies != 'undefined' && m[ i ].taxonomies.length )
					{
						for( j = 0, l = m[ i ].taxonomies.length; j < l; j++ )
						{
							if( typeof tmp[ m[ i ].taxonomies[ j ] ]  == 'undefined' )
							{
								str += '<li><input type="checkbox" checked value="'+m[ i ].taxonomies[ j ]+'" /><label>'+m[ i ].taxonomies[ j ]+'</label></li>';
								tmp[ m[ i ].taxonomies[ j ] ] = true;
							}
						}
					}
				}
				
				str = '<ul>'+str+'</ul>';
				me.legend_container.append( $( str ) );
				me.legend_container
				  .find( 'input[type="checkbox"]' )
				  .click(
					function()
					{
						
						me._show_hide_markers( $( this ).parents( '.cpm-map-legend' ).find( 'input[type="checkbox"]:checked' ).map( function(){ return this.value } ).get() );
					}
				  );
			},
			
			// public methods
			set_map: function(){
				var me = this;
				if(me.data.markers.length){
					var m = me.data.markers,
						h = m.length;
					
					me.counter = h; // Counter is used to know the moment where all latitudes or longitudes were calculated
					
					for(var i=0; i < h; i++){
						if( (me._empty(m[i].lat) || me._empty(m[i].lng)) && !me._empty(m[i].address)){
							me._get_latlng(i);
						}else if(me._empty(m[i].lat) && me._empty(m[i].lng)){
							// The address is not present so the point may be removed from the list
							m[i]['invalid'] = true;
							me.counter--;
						}else{
							m[i]['latlng'] = new google.maps.LatLng( me._correct( m[i].lat ), me._correct( m[i].lng ) );
							me.counter--;
						}
						
					}
					
					// All points have been checked now is possible to load the map
					if(me.counter == 0){
						me._load_map();
						
						// Set Route
						me._set_route();
						
						// Set Shapes
						me._set_shape();
					}
				}
			},
			
			// Show and Hide the points, in function of the option selected on legend
			_show_hide_markers : function( taxonomies ){
				
				var me = this,
					m  = me.markers,
					tmp = [];
				for( var i = 0, h = m.length; i < h; i++ )
				{
					if( typeof m[ i ][ 'taxonomies' ] != 'undefined' )
					{
						if( $( taxonomies ).filter( m[ i ][ 'taxonomies' ] ).length )
						{
							m[ i ].setMap( me.map );
							tmp.push( m[ i ] );
						}
						else
						{
							m[ i ].setMap( null );
						}
					}
					else
					{
						tmp.push( m[ i ] );
					}
				}
				me._set_cluster( tmp, false );
			},
			
			// Open the marker bubble
			open_infowindow : function(m){
				var me = this;
				if( !me.data.show_window ) return;
				if(typeof me.panoramioLayer !== 'undefined' ){
					me.panoramioLayer.setOptions( { suppressInfoWindows: true } );
					me.panoramioLayer.setOptions( { suppressInfoWindows: false } );
				}
				
				var c  = me._str_transform( me.data.markers[ m.id ].info ),
					img = $( c ).find( 'img' );
					
				if( img.length )
				{
					$( '<img src="'+img.attr( 'src' ) +'">' ).load( (function( c, m ){
						return function(){
							me.infowindow.setContent( c );
							me.infowindow.open( me.map, m );
						};
					} )( c, m ) );
				}
				else
				{
					me.infowindow.setContent( c );
					me.infowindow.open( me.map, m );
				}	
			},	
			
			// Set the highlight class to the post with ID m['post']
			set_highlight : function(m){
				if(this.data.highlight){
					var id = this.data.markers[m.id]['post'];
					$('.post-'+id).addClass(this.data.highlight_class);
				}	
			},
			
			// Remove the highlight class from the post with ID m['post_id']
			unset_highlight : function(m){
				if(this.data.highlight){
					var id = this.data.markers[m.id]['post'];
					$('.post-'+id).removeClass(this.data.highlight_class);
				}
			}
		};	
		// End CPM class definition
		
		// Load the street view on infowindow
		$( document ).on( 'click', '#cpm_display_streetview_btn', function(){
			var me = $(this),
				c = me.parents( '.cpm-map-container' ),
				latlng = new google.maps.LatLng( me.attr( 'lat' ), me.attr( 'lng' ) ),
				st = new google.maps.StreetViewPanorama( 
															c[ 0 ], 
															{
																'position': latlng,
																'enableCloseButton': true
															} 
														);
			st.setVisible(true);
		} );
		
		
		// Callback function to be called after loading the maps api
		function initialize( e )
		{
			var map_container = $( e ),
				map_id = map_container.attr('id');

			if( map_container.parent().is( ':hidden' ) )
			{
				setTimeout( function(){ initialize( e ); }, 500 );
				return;
			}

			if(cpm_global && cpm_global[map_id] && cpm_global[map_id]['markers'].length){
				// The maps data are defined
				var cpm = new $.CPM( map_container, cpm_global[map_id]);

				// Display map
				if(cpm_global[map_id]['display'] == 'map'){
					map_container.show();
					cpm.set_map();
				}else{
					// Insert a icon to display map
					var map_icon = $('<div class="cpm-mapicon"></div>');
					map_icon.click(function(){
						if(map_container.is( ':visible' ))
						{
							map_container.hide();
						}
						else
						{
							map_container.show();
							cpm.set_map();
						}	
					});
					map_icon.insertBefore(map_container);
				}	
			}
		};
		
		window['cpm_init'] = function(){
			$('.cpm-map').each(function(){
			
				if( $( this ).parent().is( ':hidden' ) )
				{
					setTimeout(
						( function ( e )
							{
								return function(){ initialize( e ); };
							} )( this ),
						500
					);
				}
				else
				{
					initialize( this );
				}	
				
			});
		};
		
		var map = $('.cpm-map');
		if(map.length){
			if(typeof google == 'undefined' || google['maps'] == null){
				// Create the script tag and load the maps api
				var script=document.createElement('script');
				script.type  = "text/javascript";
				script.src=(( typeof window.location.protocol != 'undefined' ) ? window.location.protocol : 'http:' )+'//maps.google.com/maps/api/js?sensor=false'+((typeof cpm_language != 'undefined' && cpm_language.lng) ? '&language='+cpm_language.lng: '')+'&libraries=panoramio&callback=cpm_init';
				document.body.appendChild(script);
			}else{
				cpm_init();
			}
		}	
	});
}

if( typeof jQuery == 'undefined' ) setTimeout( function(){ CodePeoplePostMapPublic() }, 1000 );
else CodePeoplePostMapPublic();	