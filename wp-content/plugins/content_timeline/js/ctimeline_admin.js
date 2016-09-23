(function($){
$(document).ready(function(){
	my_debug=false;
	debug=function(t,o){
		if(my_debug){
			if(typeof window.console !='undefined'){
				console.log(t,o);
			}
		}
	}
	$(document).on('change',"#my-vertical-limit",function(){
		var is=$("#my-vertical-limit").is(":checked");
		if(is){
			$("#my_option_my-vertical-count").show();
			$("#my_option_my-vertical-count-small").show();
		}else {
			$("#my_option_my-vertical-count").hide();
			$("#my_option_my-vertical-count-small").hide();
			
		}
	});
	$(document).on('change','#vertical',function(){
		var is=$("#vertical").is(":checked");
		if(is){
			$("#pbox2").show();
		}else {
			$("#pbox2").hide();
		}
	});
	var is12312344=$("#my-vertical-limit").is(":checked");
	if(is12312344){
		$("#my_option_my-vertical-count").show();
		$("#my_option_my-vertical-count-small").show();
	}else {
		$("#my_option_my-vertical-count").hide();
		$("#my_option_my-vertical-count-small").hide();
	}
	var is12345=$("#vertical").is(":checked");
	if(is12345){
		$("#pbox2").show();
	}else {
		$("#pbox2").hide();
	}
	pre_style=$("#style option:selected").val();
	debug("Pre style",pre_style);
	$("#style").change(function(){
		var s=$("#style option:selected").val();
		debug('Change style',{pre_style:pre_style,s:s});
		if(s!=pre_style){
			pre_style=s;
			if(typeof wp_my_timeline_styles[s]!='undefined'){
				debug("Style options",wp_my_timeline_styles[s]);
				var obj=wp_my_timeline_styles[s];
				$.each(obj,function(i,v){
					if(i=='shdow')i='shadow';
					var id="#"+i;
					var tag=$(id).prop('tagName');
					if(typeof tag!='undefined'){
					tag=tag.toLowerCase();
					var classes=$(id).attr('class');
					debug("Tag name",{tag:tag,i:i,v:v,classes:classes});
					if(tag=='a'){
						if($(id).hasClass("cw-image-upload")){
							if(v==''){
							$(id).attr('style','');
							var id_1=i+'-input';
							
							$("#"+id_1).val(v);
							debug("Background",{v:v,id_1:id_1});
							$(id).parents(".cw-image-select-holder").find(".remove-image").trigger('click');
						}else {
							var id_1=i+'-input';
							
							$("#"+id_1).val(v);
							debug("Background",{v:v,id_1:id_1});
							$(id).attr("style","background:url('"+v+"')");
							
						}
						}
					}
					else if(tag=='input'){
						if($(id).hasClass('wp-color-picker')){
							debug("Color picker",{i:i,v:v});
							$(id).val(v);
							$(id).trigger('change');
							$(id).wpColorPicker('color',v);
						}else {
							var type=$(id).attr('type');
							debug("Input type",type);
							if(type=='text' || type=='hidden'){
								
								
								
									$(id).val(v);
									
								//}
							}else if(type=='checkbox'){
								if(v){
									debug('Set checbox ',v);
									$(id).prop('checked',true);
								}else {
									debug('Unset checbox',v);
									$(id).prop('checked',false);
								}
							}
						}
					}else if(tag=='select'){
						debug('Set selecvted',v);
						$(id).val(v);
						//$(id).prop('selected',v);
					}
					}
				});
			}
			
		}
		
	})
	
	// COLORPICKER
	var colPickerOn = false,
		colPickerShow = false, 
		pluginUrl = $('#plugin-url').val(),
		imageurl = ajaxurl + '?action=ctimeline_image_get';
		
	$('.timeline_color_input').wpColorPicker();
	// colorpicker field
	$('.cw-color-picker').each(function(){
		var $this = $(this),
			id = $this.attr('rel');
 
		$this.farbtastic('#' + id);
		$this.click(function(){
			$this.show();
		});
		$('#' + id).click(function(){
			$('.cw-color-picker:visible').hide();
			$('#' + id + '-picker').show();
			colPickerOn = true;
			colPickerShow = true;
		});
		$this.click(function(){
			colPickerShow = true;	
		});
		
	});
	$('body').click(function(){
		if(colPickerShow) colPickerShow = false;
		else {
			colPickerOn = false;
			$('.cw-color-picker:visible').hide();
		}
	});

	// IMAGE UPLOAD
	var thickboxId =  '',
		thickItem = false; 
	
	// backgorund images
	$('.cw-image-upload').click(function(e) {
		e.preventDefault();
		thickboxId = '#' + $(this).attr('id');
		formfield = $(thickboxId + '-input').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	

	window.send_to_editor = function(html) {
		if(window.console){
			console.log('html',html);
		}	
		var img_pos=html.indexOf('<img');
		if (img_pos>0) html=html.substring(img_pos);
		img_pos=html.indexOf('>');
		if (img_pos>0) html=html.substring(0, img_pos+1);
		while (html.indexOf('\\"')>-1) html=html.replace('\\"','"');
		var $jhtml=$(html);
		var imgurl = $jhtml.attr('src');
		$(thickboxId + '-input').val(imgurl);
		if (thickItem) {
			thickItem = false;
			$(thickboxId).attr('src', imageurl + '&src=' + imgurl + '&w=258&h=50');
		}
		else {
			$(thickboxId).css('background', 'url('+imgurl+') repeat');
		}
		tb_remove();
	}
	
	$('.remove-image').click(function(e){
		e.preventDefault();
		$(this).parent().parent().find('input').val('');
		$(this).parent().parent().find('.cw-image-upload').css('background-image', 'url(' + pluginUrl + '/images/no_image.jpg)');
	});

	// CATEGORIES
	if ($('#cat-type').val() == 'categories') {
		$('.cat-display').show();
		$('.data_id').css('color', 'gray');
	}
	else {
		$('.category_id').css('color', 'gray');
	}
	$('#cat-type').change(function(){
		if ($(this).val() == 'months') {
			$('.cat-display').hide();
			$('.category_id').css('color', 'gray');
			$('.data_id').css('color', '');
			alert('Check the Date field of your items before you save!');
		}
		else if($(this).val()=='categories'){
			$('.cat-display').show();
			$('.data_id').css('color', 'gray');
			$('.category_id').css('color', '');
			alert('Check the Category field of your items, and pick categoryes you want to show before you save!');
		}
	});
	
	$('#cat-check-all').click(function(){
		$('.cat-name').attr('checked', true);
	});
	
	$('#cat-uncheck-all').click(function(){
		$('.cat-name').attr('checked', false);
	});
	
	
	// SORTABLE
	
	$('#timeline-sortable').sortable({
		placeholder: "tsort-placeholder"
	});
	
	//---------------------------------------------
	// Ctimeline Sortable Actions
	//---------------------------------------------
	
	// add
	$('#tsort-add-new').click(function(e){
		e.preventDefault();
		ctimelineAddNew(pluginUrl);
	});

	// open item
	$(document).on('click', '.tsort-plus', function(){
		if (!$(this).hasClass('open')) {
			$(this).addClass('open');
			$(this).html('-').css('padding', '5px 8px');
			$(this).next().next('.tsort-content').show();
		}
		else {
			$(this).removeClass('open');
			$(this).html('+').css('padding', '7px 5px');
			$(this).next().next('.tsort-content').hide();
		}
	});
	// delete
	$(document).on('click', '.tsort-delete', function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
	});
	
	$(document).on('click', '.tsort-remove', function(e){
		e.preventDefault();
		$(this).parent().find('input').val('');
		$(this).parent().find('img').attr('src', pluginUrl + '/images/no_image.jpg');
	});
	
	
	// item images
	$(document).on('click', '.tsort-change', function(e) {
		e.preventDefault();
		thickItem = true;
		thickboxId = '#' + $(this).parent().find('img').attr('id');
		formfield = $(thickboxId + '-input').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
	
	// item images
	$(document).on('click', '.tsort-start-item', function(e) {
		$('.tsort-start-item').attr('checked', false);
		$(this).attr('checked', 'checked');
	});
	
	$(document).on('keyup', '.tsort-title', function(e) {
		$(this).closest('.sortableItem').find('.tsort-header small i').html($(this).val());
	});
	
	// ----------------------------------------
	
	// AJAX subbmit
	$('#save-timeline').click(function(e){
	e.preventDefault();
	var serr = $('#post_form').serialize();
	serr = serr.replace(/=/g, '::');
	serr = serr.replace(/&/g, '||');
		$('#save-loader').show();
		$.ajax({
			type:'POST', 
			url: 'admin-ajax.php', 
			data:'action=ctimeline_save&data=' + serr, 
			success: function(response) {
				$('#timeline_id').val(response);
				$('#save-loader').hide();
			}
		});
	});
	
	$('#preview-timeline').click(function(e){
		e.preventDefault();
		
		var html = '<div id="TBct_overlay" class="TBct_overlayBG"></div>';
		html += '<div id="TBct_window" style="width:250px; margin-left:-75px; height:80px; margin-top:-40px; visibility: visible;">';
		html += '<div id="TBct_title"><div id="TBct_ajaxWindowTitle">Preview</div>';
		html += '<div id="TBct_closeAjaxWindow"><a id="TBct_closeWindowButton" title="Close" href="#"><img src="'+pluginUrl+'/images/tb-close.png" alt="Close"></a></div>';
		html += '</div>';
		html += '<div id="timelineHolder" style="margin:0 auto;">';
		html += '<img style="margin:20px 20px;" id="TBct_loader" src="'+pluginUrl+'/images/loadingAnimation.gif" />';
		html += '</div>';
		html += '<div style="clear:both;"></div></div>';
		html += '</div>';
		$('body').append(html);
		var postForm = $('#post_form').serialize();
		postForm = postForm.replace(/=/g, '::');
		postForm = postForm.replace(/&/g, '||');
		
		$.ajax({
			type:'POST', 
			url: 'admin-ajax.php', 
			data:'action=ctimeline_preview&data=' + postForm, 
			success: function(response) {
				$('#TBct_loader').hide();
				$('#TBct_window').animate({width: '100%', marginLeft:'-50%', marginTop: '-250px', height: '500px'}, 500, function(){
					$('#timelineHolder').html(response);
					$('#timelineHolder').css({'overflow-y':'scroll', 'position': 'relative', 'width':'100%', 'height':'470px'});
					if($('#read-more').val() == 'whole-item') {
						var $read_more = '.item';
						var $swipeOn = false;
					}
					else if ($('#read-more').val() == 'button') {
						var $read_more = '.read_more';
						var $swipeOn = true;
					}
					else {
						var $read_more = '.none';
						var $swipeOn = true;
					}
					
					var startItem = $('#ctimeline-preview-start-item').val();
					var vertical=$("#vertical").is(":checked");
					
					var $cats = [];
					var $numOfItems = []; 
					var numGet = parseInt($('#number-of-posts').val());
					$('input[name|="cat-name"]:checked').each(function(){
						$cats.push($(this).val());
						$numOfItems.push(numGet);
					
					});
					
					
					var my_id_str=$("#timelineHolder").find(".timeline").attr('id');
					var my_id=my_id_str.replace('tl','');
					var my_is_years_val=$("#cat-type option:selected").val();
					var my_is_years=0;
					if(my_is_years_val=='years')my_is_years=1;
					var jsonOptions = {
						my_id:my_id,	
						my_debug:0,
						my_del:130,	
						my_trigger_width:800,
						my_is_years:my_is_years,
						my_sizes:{
							card:{
								item_width:parseInt($("#item-width").val()),
								item_height:parseInt($("#item-height").val()),
								margin:parseInt($('#item-margin').val())
							},
							active:{
								item_width:parseInt($("#item-open-width").val()),
								item_height:parseInt($("#item-open-height").val()),
								image_height:parseInt($('#item-open-image-height').val())
							}
						},
						itemMargin : parseInt($('#item-margin').val()),
						swipeOn : $swipeOn, 
						scrollSpeed : parseInt($('#scroll-speed').val()),
						easing : $('#easing').val(),
						openTriggerClass : $read_more,
						startItem : startItem,
						yearsOn : ($('#years-on:checked').length > 0  ),
						hideTimeline : ($('#hide-line:checked').length > 0 ),
						hideControles : ($('#hide-nav:checked').length > 0 )
					}
					
					if (typeof $cats[0] != 'undefined' && $('#cat-type').val() == 'categories') {
						jsonOptions.yearsOn = false;
						jsonOptions.categories = $cats;
						jsonOptions.numberOfSegments = $numOfItems;
					}
					$("#timelineHolder .scrollable-content").mCustomScrollbar();
					if(vertical){
						var shadow=$("#shadow option:selected").val();
						var my_p_123=10;
						var my_p_123_1=5;
						var my_p_123_2=5;
						if( shadow== 'on-hover') {
							my_p_123=15;
							my_p_123_1=52;
							my_p_123_2=30;
						}else {
							my_p_123=5;
							if(shadow!='hide')
							my_p_123=10;
							my_p_123_1=my_p_123;
							my_p_123_2=my_p_123;
						}
						var jsonOptions1={};
						var jsonOptions2={};
						jsonOptions2={
								vertical:1,
								myMarginLeftRight:20,
								myShow:$("#my-vertical-count").val(),
								myShowSmall:$("#my-vertical-count-small").val(),
								myVerticalPadding:my_p_123,
								myVerticalPadding1:my_p_123_1,
								myVerticalPadding2:my_p_123_2,					
						};
						jsonOptions1=$.extend(jsonOptions,jsonOptions2);
						if($('#cat-type').val()=='mothhs'){
							if(my_ctimeline_has_wpml==1){
								if(my_ctimeline_lang!='en'){
									jsonOptions2['cats']=my_ctimeline_months;
								}
							}
							
						}
						$('#timelineHolder .timeline').VerticalTimeline(jsonOptions1);
					}else $('#timelineHolder .timeline').timeline(jsonOptions);
					$('#preview-loader').hide();
					/*$("#timelineHolder").find(".item_open").each(function(i,v){
						if(window.console){
							console.log('Update height');
						}
						var scrCnt = $(v).find(".scrollable-content");
						scrCnt.height(scrCnt.parent().height() - scrCnt.parent().children("h2").height() - parseInt(scrCnt.parent().children("h2").css("margin-bottom")));
						//scrCnt.mCustomScrollbar({theme:"light-thin"});
						srcCnt.mCustomScrollbar('update');
					});*/
					$('#TBct_closeWindowButton').click(function(ev){
						ev.preventDefault();
						$('.timeline').timeline('destroy');
						$('#TBct_overlay').remove();
						$('#TBct_window').remove();
					});
				});
				
			}
		});
	});
	
	
});


function ctimelineSortableActions(pluginUrl) {

	
	
		
}

function ctimelineAddNew(pluginUrl) {
	var searches = new Array();
	searches[''] = '';
	var html = '<div id="TBct_overlay" class="TBct_overlayBG"></div>';
	html += '<div id="TBct_window" style="width:450px; margin-left:-225px; margin-top:-35px; height:70px; visibility: visible;">';
	html += '<div id="TBct_title"><div id="TBct_ajaxWindowTitle">Add new timeline item</div>'
	html += '<div id="TBct_closeAjaxWindow"><a id="TBct_closeWindowButton" title="Close" href="#"><img src="'+pluginUrl+'/images/tb-close.png" alt="Close"></a></div>';
	html += '</div>';
	html += '<a href="#" id="TBct_timelineSubmit" style="margin:10px;" class="button button-highlighted alignright">Add</a><img id="TBct_timelineSubmitLoader" class="alignright" src="'+pluginUrl+'/images/ajax-loader.gif" /><select id="TBct_timelineSelect" style="margin:10px; width:150px;"><option value="new">Add New</option><option value="post">From Post</option><option value="category">Whole Category</option></select>';
	html += '<div id="TBct_timelineFromPost" style="padding:10px; border-top:1px solid gray; display:none;"><label for="timelineFromPost">Search posts:</label> <span id="timelineFromPostHolder"><input id="timelineFromPost" name="timelineFromPost" style="width:325px;"/><img id="timelineFromPostLoader" src="'+pluginUrl+'/images/ajax-loader.gif" /> <ul style="display:none;" id="timelineFromPostComplete"></ul></span>';
	
	html += '</div>';
	html += '<div id="TBct_timelineWholeCategory" style="padding:10px; border-top:1px solid gray; display:none;">';
	html += '<label for="TBct_timelineCategorySelect">Pick category</label> <select style="width:200px" id="TBct_timelineCategorySelect" name="TBct_timelineCategorySelect">'
	var allCats = $('#categories-hidden').val();
	if(allCats) {
		allCats = allCats.split('||');
	}
	else {
		allCats = new Array();
	}
	for (cate in allCats) {
		html += '<option value="'+allCats[cate]+'">'+allCats[cate]+'</option>';
	}
	
	html += '</select>';
	html += '</div>';
	html += '</div>';
	$('body').prepend(html);
	

	$('#TBct_closeWindowButton').click(function(e){
		e.preventDefault();
		$('#TBct_overlay').remove();
		$('#TBct_window').remove();
	});
	
	$('#TBct_timelineSelect').change(function(){
		if ($(this).val() == 'new') {
			$('#TBct_window').css({marginTop:'-35px', height:'70px'});
			$('#TBct_timelineFromPost').hide();
			$('#TBct_timelineWholeCategory').hide();
		}
		if ($(this).val() == 'category') {
			$('#TBct_window').css({marginTop:'-60px', height:'120px'});
			$('#TBct_timelineWholeCategory').show();
			$('#TBct_timelineFromPost').hide();
		}
		else {
			$('#TBct_window').css({marginTop:'-150px', height:'300px'});
			$('#TBct_timelineFromPost').show();
			$('#TBct_timelineWholeCategory').hide();
		}	
	});
	
	$('#TBct_timelineSubmit').click(function(e){
		e.preventDefault();
		var timelineItem = '';
		if ($('#TBct_timelineSelect').val() == 'new') {
			timelineItem = timelineGenerateItem();
			$('#timeline-sortable').append(timelineItem);
			$('.tsort-start-item').eq($('.tsort-start-item').length-1).trigger('click').attr('checked', 'checked');
			$('#TBct_overlay').remove();
			$('#TBct_window').remove();
		}
		else if ($('#TBct_timelineSelect').val() == 'category') {
			$('#TBct_timelineSubmitLoader').show();
			cat_name=$('#TBct_timelineCategorySelect').val();
			if($("#my_timeline_cats[value='"+cat_name+"']").length==0){
				debug("Add new cat",cat_name);
				var html='<input type="hidden" name="my_timeline_cats[]" id="my_timeline_cats" value="'+cat_name+'"/>';
				debug("Html",html);
				$("#post_form").prepend(html);
			}else {
				debug('Cat exists',cat_name);
			}
			$.ajax({
				url:"admin-ajax.php",
				type:"POST",
				data:'action=ctimeline_post_category_get&cat_name='+$('#TBct_timelineCategorySelect').val(),
				
				success:function(results){
					var resultsArray = results.split('||');
					var ii = 0;
					while (typeof resultsArray[0+ii] != 'undefined') {
							
						var properties = {
							'title' : resultsArray[0+ii],
							'dataId' : resultsArray[1+ii],
							'categoryId' : resultsArray[2+ii],
							'itemContent' : resultsArray[3+ii],
							'itemImage' : resultsArray[4+ii],
							'itemPrettyPhoto' : resultsArray[4+ii],
							'itemOpenPrettyPhoto' : resultsArray[4+ii],
							'itemOpenContent' : resultsArray[5+ii],
							'myItemPostId':resultsArray[6+ii]
							}
						timelineItem = timelineGenerateItem(properties);
						$('#timeline-sortable').append(timelineItem);
						ii +=7;
					}
					$('.tsort-start-item').eq($('.tsort-start-item').length-1).trigger('click').attr('checked', 'checked');
					$('#TBct_overlay').remove();
					$('#TBct_window').remove();
				}
			});
		}
		
		else if($('#timelineFromPostComplete li a.active').length < 1) {
			alert('You have to select post you want to add, or choose add new!');
		}
		else {
			var postId = $('#timelineFromPostComplete li a.active').attr('href');
			$('#TBct_timelineSubmitLoader').show();
			$.ajax({
				url:"admin-ajax.php",
				type:"POST",
				data:'action=ctimeline_post_get&post_id='+postId,
				
				success:function(results){
					var resultsArray = results.split('||');
					var properties = {
						'title' : resultsArray[0],
						'dataId' : resultsArray[1],
						'categoryId' : resultsArray[2],
						'itemContent' : resultsArray[3],
						'itemImage' : resultsArray[4],
						'itemPrettyPhoto' : resultsArray[4],
						'itemOpenPrettyPhoto' : resultsArray[4],
						'itemOpenContent' : resultsArray[5],
						'myItemPostId':resultsArray[6]
						}
					timelineItem = timelineGenerateItem(properties);
					$('#timeline-sortable').append(timelineItem);
					$('.tsort-start-item').eq($('.tsort-start-item').length-1).trigger('click').attr('checked', 'checked');
					$('#TBct_overlay').remove();
					$('#TBct_window').remove();
				}
			});
		}
		
	})
	
	$('#timelineFromPost').keyup(function(e){
		var icall = null,
			qinput = $('#timelineFromPost').val();
		
		if(qinput in searches) {
			if(icall != null) icall.abort();
			$('#timelineFromPostComplete').html(searches[qinput]).show();
			$('#timelineFromPostComplete li a').click(function(e){
				e.preventDefault();
				$('#timelineFromPostComplete li a.active').removeClass('active');
				$(this).addClass('active');
			});
			$('#timelineFromPostLoader').hide();
		}
		else {
			$('#timelineFromPostLoader').show();
			if(icall != null) icall.abort();
			icall = $.ajax({
				url:"admin-ajax.php",
				type:"POST",
				data:'action=ctimeline_post_search&query='+qinput,
				
				success:function(results){
					$('#timelineFromPostComplete').html(results).show();
					searches[qinput] = results;
					$('#timelineFromPostComplete li a').click(function(e){
						e.preventDefault();
						$('#timelineFromPostComplete li a.active').removeClass('active');
						$(this).addClass('active');
					});
					$('#timelineFromPostLoader').hide();
				}
			});
		}
	});
}

function timelineGenerateItem(properties) {
	// set globals
	var pluginUrl = $('#plugin-url').val(),
		imageurl = ajaxurl + '?action=ctimeline_image_get';
	
	// calculate item number
	var itemNumber = 1;
	while($('#sort'+itemNumber).length > 0) {
		itemNumber++;
	}
	
	// get current date
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1;
	var yyyy = today.getFullYear();
	if(dd<10){dd='0'+dd} 
	if(mm<10){mm='0'+mm} 
	today = dd+'/'+mm+'/'+yyyy;

	// get input properties
	var pr = $.extend({
		'title' : 'Title',
		'dataId' : today,
		'categoryId' : '',
		'categoryId' : '',
		'itemContent' : 'Content',
		'itemPrettyPhoto' : '',
		'itemLink' : '',
		'itemImage' : '',
		'itemOpenContent' : 'Content',
		'itemOpenPrettyPhoto' : ''
	}, properties);
	// bring all the pieces together
	var itemHtml = '\n'+	
'					<li id="sort'+itemNumber+'" class="sortableItem">\n'+
'						<div class="tsort-plus">+</div>\n'+
'						<div class="tsort-header">Item '+itemNumber+' <small><i>- '+pr.title+'</i></small> &nbsp;<a href="#" class="tsort-delete"><i>delete</i></a></div>\n'+
'						<div class="tsort-content">\n'+
'							<div class="tsort-dataid">\n'+
'								<input name="sort'+itemNumber+'-my-post-id" id="'+itemNumber+'-my-post-id" class="" type="hidden" value="'+pr.myItemPostId+'">'+								
'								<input name="sort'+itemNumber+'-start-item" id="'+itemNumber+'-start-item" class="tsort-start-item alignright" type="checkbox"><label for="'+itemNumber+'-start-item" class="alignright">Start item &nbsp;</label>'+
'								<span class="timeline-help">? <span class="timeline-tooltip">Argument by which are elements organised (date - dd/mm/yyyy, Category - full category name) Different field is used for different categorizing type.</span></span>'+
'								<label for="sort'+itemNumber+'-dataid">Date</label>'+
'								<input style="margin-left:5px;" id="sort'+itemNumber+'-dataid" name="sort'+itemNumber+'-dataid" value="'+pr.dataId+'" type="text"/>'+
'								<label style="margin-left:5px;" for="sort'+itemNumber+'-categoryid">Category</label>'+
'								<input style="margin-left:5px;" id="sort'+itemNumber+'-categoryid" name="sort'+itemNumber+'-categoryid" value="'+pr.categoryId+'" type="text"/>'+
'								<label style="margin-left:5px;" for="sort'+itemNumber+'-node-name">Title of the timeline node (optional)</label>'+
'								<input style="margin-left:5px;" id="sort'+itemNumber+'-node-name" name="sort'+itemNumber+'-node-name" value="" type="text" />'+
'							</div>\n'+
'							<div class="tsort-item">\n'+
'								<h3 style="padding-left:0;"><span class="timeline-help">? <span class="timeline-tooltip">Base item content (image, title and content).</span></span>Item Options</h3>\n'+
'								<div class="tsort-image"><img id="sort'+itemNumber+'-item-image" src="'+((pr.itemImage != '') ? imageurl + '&src=' + pr.itemImage + '&w=258&h=50' : pluginUrl + '/images/no_image.jpg')+ '" /><a href="#" id="sort'+itemNumber+'-item-image-change" class="tsort-change">Change</a>\n' +
'									<input id="sort'+itemNumber+'-item-image-input" name="sort'+itemNumber+'-item-image" type="hidden" value="'+pr.itemImage+'" />\n'+
'									<a href="#" id="sort'+itemNumber+'-item-image-remove" class="tsort-remove">Remove</a>\n'+
'								</div>\n'+
'								<input class="tsort-title" name="sort'+itemNumber+'-item-title" value="'+pr.title+'" type="text" />\n'+
'								<div class="clear"></div>\n'+
'								<textarea class="tsort-contarea" name="sort'+itemNumber+'-item-content">'+pr.itemContent+'</textarea>\n'+
'								<table style="width:100%;">\n'+
'								<tr>\n'+
'									<td style="width:120px;"><span class="timeline-help">? <span class="timeline-tooltip">"PrittyPhoto"(Lightbox) URL, it can be image, video or site url. Leave it empty to link to full-size image.</span></span><label for="sort'+itemNumber+'-item-prettyPhoto">PrettyPhoto URL</label></td>\n'+
'									<td><input class="tsort-prettyPhoto" name="sort'+itemNumber+'-item-prettyPhoto" value="'+pr.itemPrettyPhoto+'" type="text" style="width:100%;" /></td>\n'+
'								</tr>\n'+
'								<tr>\n'+
'									<td style="width:120px;"><span class="timeline-help">? <span class="timeline-tooltip">Link to post or page. Leave it empty for default behaivor.</span></span><label for="sort'+itemNumber+'-item-link">Button URL</label></td>\n'+
'									<td><input class="tsort-link" name="sort'+itemNumber+'-item-link" value="'+pr.itemLink+'" type="text" style="width:100%;" /></td>\n'+
'								</tr>\n'+										
'								</table>\n'+
'							</div>\n'+
'							<div class="tsort-itemopen">\n'+
'								<h3 style="padding-left:0;"><span class="timeline-help">? <span class="timeline-tooltip">Opened item content (image, title and content).</span></span>Item Open Options</h3>\n'+
'								<div class="tsort-image"><img id="sort'+itemNumber+'-item-open-image" src="'+((pr.itemImage != '') ? imageurl + '&src=' + pr.itemImage + '&w=258&h=50' : pluginUrl + '/images/no_image.jpg')+ '" /><a href="#" id="sort'+itemNumber+'-item-open-image-change" class="tsort-change">Change</a>'+
'									<input id="sort'+itemNumber+'-item-open-image-input" name="sort'+itemNumber+'-item-open-image" type="hidden" value="'+pr.itemImage+'" />\n'+
'									<a href="#" id="sort'+itemNumber+'-item-open-image-remove" class="tsort-remove">Remove</a>\n'+
'								</div>\n'+
'								<input class="tsort-title" name="sort'+itemNumber+'-item-open-title" value="'+pr.title+'" type="text" />\n'+
'								<div class="clear"></div>\n'+
'								<textarea class="tsort-contarea" name="sort'+itemNumber+'-item-open-content">'+pr.itemOpenContent+'</textarea>\n'+
'								<table style="width:100%;">\n'+
'								<tr>\n'+
'									<td style="width:120px;"><span class="timeline-help">? <span class="timeline-tooltip">"PrittyPhoto"(Lightbox) URL, it can be image, video or site url. LEAVE IT EMPTY TO DESPLAY FULL SIZED IMGAE.</span></span><label for="sort'+itemNumber+'-item-open-prettyPhoto">PrettyPhoto URL</label></td>\n'+
'									<td><input class="tsort-prettyPhoto" name="sort'+itemNumber+'-item-open-prettyPhoto" value="'+pr.itemOpenPrettyPhoto+'" type="text" style="width:100%;" /></td>\n'+
'								</tr>\n'+								
'								</table>\n'+
'								<label for="sort'+itemNumber+'-desable-scroll">Disable Scroll &nbsp;</label>\n'+
'								<input type="checkbox" id="sort'+itemNumber+'-desable-scroll" name="sort'+itemNumber+'-desable-scroll" />\n'+
'							</div>\n'+
'						</div>\n'+
'					</li>\n';
	return itemHtml;
}


})(jQuery)

