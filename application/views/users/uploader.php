<?php if ($_GET['view'] == 'uploaded'):
	  unset($_SESSION['recentUploads']);
?>
<style>
#Profile{background:url(<?php echo SITE_IMAGES_PATH?>loginbuttonbg.jpg) repeat-x bottom!important;border:1px solid #48515A;}
#Profile > a{background-position:8px bottom!important;color:#FFF!important;text-shadow:1px 1px #333;} 
#Profile .menuarrow{background-position:left bottom!important;}
#MyItems-sub{font-weight:bold;}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_PATH?>public/css/fancybox/jquery.fancybox-1.3.4.css">
<script src="<?php echo WEB_PATH?>public/js/tagger/jquery.fancybox-1.3.4.js" type="text/javascript"></script>
<script src="<?php echo WEB_PATH?>public/js/uploader/tagme.js" type="text/javascript"></script>
<script>
$(document).ready(function(e) {
	$("#tagged_images").find("a").each(function(index, element) {
        boxIt(element);
    });
	$("#untagged_images").find("a").each(function(index, element) {
        boxIt(element);
    });  
});
function boxIt(element){
	var justLoadedImg = "";
	$(element).fancybox({
		'hideOnContentClick': false,
		'autoScale':false,
		onCleanup:function(ele){
			// This is to 
			ele.find(".picDeleteButton").remove();
			ele.find(".deleteConfirmDiv").remove();
			ele.find(".tm-tagbox").remove();
			ele.find(".tm-tagpanel").remove();
			ele.find(".tagItem-1").remove();
		},
		onComplete:function(ele,x1,x2,imageE){
			justLoadedImg = imageE.orig[0].attributes[1].nodeValue;
			
			var subscribeValuesSecond = new Array(),
				removeSubscriptedSecond = new Array(),
				alreadyCheckedItems = new Array(),
				subscriptionsAsStr = "", 
				removeSubscriptionsAsStr = "";
				
			ele.find(".tm-tagbox").remove();
			ele.find(".tm-tagpanel").remove();
			var deleteButton = $('<input type="button" value="Delete" class="picDeleteButton"/>');
			var deleteConfirm = $('<div class="deleteConfirmDiv"><span class="floatl">Do you want to delete the image (this will also delete the tags on this image.) </span><input type="button" value="No" class="noButton"/><input type="button" value="Yes" class="yesButton" /></div>');
			
			deleteButton.click(function(e){
				e.stopPropagation();
				$(this).hide();
				deleteConfirm.fadeIn("fast");
				deleteConfirm.find(".yesButton").click(function(e){
					e.stopPropagation();
					$.post("<?php echo WEB_PATH?>library/deleteCall.php",
						{imageHash:$(imageE.orig).attr("id")},
						function(res){
							if(!res.error){
								$(imageE.orig).remove();
								$(imageE).remove();
								$.fancybox.close();
								// This is to reload the masonary plugin
								$containerFor_untagged_images = $('#untagged_images');
								$containerFor_untagged_images.masonry('reload');
								$containerFor_tagged_images = $('#tagged_images');
								$containerFor_tagged_images.masonry('reload');
							}
						},'json');
				});
				deleteConfirm.find(".noButton").click(function(e){
					e.stopPropagation();
					deleteConfirm.hide();
					deleteButton.fadeIn("fast");
				});
			});
			deleteButton.appendTo(ele.find("#fancybox-bg-n"));
			deleteConfirm.hide();
			deleteConfirm.appendTo(ele.find("#fancybox-bg-n"));
			
			$(ele.find("#fancybox-bg-n")).click(function(e){
				e.stopPropagation();
			});			
			
			// This is to check this picture have subscribed to any other groups and then attach necessary details to the picture content
			$(".groupSubscriptedDivWrapper").html('');
			$.getJSON('<?php echo WEB_PATH?>library/ajaxFunctions.php?action=checkThesePicsGrouped&picId=' + $(imageE.orig).attr("id"), function(data){ 
				if ((data != false) && (data != null) && (!data.noGroupsJoined)){
					 $("#fancybox-bg-s").css({'display':'block'});	
					 $(ele.find("#fancybox-bg-s")).append("<div class='groupSubscriptedDivWrapper'><h2>Followed groups</h2>");
					 $(".groupSubscriptedDivWrapper").append(data.subscriptedGroupHtml);
					 // To stop the propagation
					 $(".groupSubscriptedDivWrapper").click(function(e){
						e.stopPropagation();												
					 });	 				
					 $("input[name='grpName[]']").click(function(){
						if (data.ownedSubscriptedGroups != null){										 
							if (data.ownedSubscriptedGroups.length == 1){
								removeSubscriptedSecond.push(data.ownedSubscriptedGroups[0]);
							}else{
								var currCheckBoxId = $(this).attr('id').replace('grpId_', '');
								$.each(data.ownedSubscriptedGroups, function(i){
									if (data.ownedSubscriptedGroups[i] == currCheckBoxId){
										removeSubscriptedSecond.push(data.ownedSubscriptedGroups[i]);
									}
								});
							}										 
						}
						subscribeValuesSecond.push($(this).attr('id').replace('grpId_', ''));
					 });
					 $(".groupSubscriptedDivWrapper #subscribeToTheseGroupsInUploaded").click(function(){
						// If new subscriptions array not empty
						if (subscribeValuesSecond.length != 0){
							$.ajax({  
								type: "POST",
								url: "<?php echo WEB_PATH?>library/ajaxFunctions.php?action=newSubscriptionsForUploadedPic", 
								data: "picId=" + $(imageE.orig).attr("id") + "&subscriptionsAsStr=" + subscribeValuesSecond, 
								success: function(data){	
									if (data != '0'){
										$(".groupSubscriptedDivWrapper").html('');
										$.fancybox.close();       
									}
								}
							});
						}
						// If removeCheckboxes array not empty
						if (removeSubscriptedSecond.length != 0){
							$.ajax({  
								  type: "POST",
								  url: "<?php echo WEB_PATH?>library/ajaxFunctions.php?action=removeCurSubscriptionsForUploadedPic", 
								  data: "picId=" + $(imageE.orig).attr("id") + "&removeSubscriptionsAsStr=" + removeSubscriptedSecond, 
								  success: function(data){	
									  if (data != '0'){
										  $(".groupSubscriptedDivWrapper").html('');
										  $.fancybox.close();
									  }
								  }
							  });
						  }
					 });
				}else{
					 $(ele.find("#fancybox-bg-s")).append("<div class='groupSubscriptedDivWrapper'><h2>No joined groups found !</h2>");
				}
			});
			$(".groupSubscriptedDivWrapper").append("</div>");
			$(ele.find("#fancybox-outer")).TagMe({
				id:$(imageE.orig).attr('id'),
				tagpanelElement: '<div class="tm-tagpanel">'+
										 '<span class="tagtitle">Tag an item...</span><div class="clearH15"></div><form id="tm-tagpanel-form">'+
											 '<input name="itemName" value="Item Name" class="TagInputBg">'+
											 '<div class="clearH10"></div><textarea name="itemDescription" class="TagTextareaBg">Item Description</textarea>'+
											 '<div class="clearH10"></div><input name="itemPrice" value="Price" class="TagInputBg" style="width:120px;">'+
											 
											 '<div class="floatr"><span class="floatl" style="margin:7px 7px 0 0;color:#545C69;">Free</span><div class="TagFree"><a id="freeNotifying" href="javascript:void(0)" class="floatr"></a></div></div>'+
											 '<button type="button" class="Tagpanelclose"></button><div class="clearTagshadow"></div><div class="clear"></div><button type="submit" class="GreenBut" style="float:right;">Add Item</button>'+
										 '</form>'+
									 '</div>',
				tagEditpanelElement: '<div class="tm-tagpanel">'+
									 '<span class="tagtitle">Edit Tag...</span><div class="clearH15"></div><form id="tm-tagpanel-form">'+
										 '<input name="itemName" value="Item Name" class="TagInputBg">'+
										 '<div class="clearH10"></div><textarea name="itemDescription" class="TagTextareaBg">Item Description</textarea>'+	
										 '<div class="clearH10"></div><input name="itemPrice" value="Price" class="TagInputBg" style="width:120px;">'+
										 '<div class="clearH10"></div><select name="itemStatus" class="DDBg">'+
										 '	<option value="available">Available</option>'+
										 '	<option value="free">Free</option>'+											 
										 '	<option value="sold">Sold</option>'+
										 '</select><div class="clearH5"></div>'+
										 '<button type="button" class="Tagpanelclose"></button><div class="clearTagshadow"></div><div class="clear"></div><button type="submit" class="GreenBut" style="float:right;">Save Item</button>'+
									 '</form>'+
								 '</div>',
				loadTags:true,
				loadTagsAction:{
						url:"<?php echo WEB_PATH?>library/tagMeCall.php",
						onProgress:function(ele){},
						onSuccess:function(ele){},
						onFail:function(ele){},
						json:true		
				},
				onTagAdded:function(ele,data){
					ele.removeClass();
					ele.addClass("tagItem-1");
					//var htmlTemp = "<span class='TagItemName'>" + data.itemName + "</span>";
					var htmlTemp = "";					
						switch(data.itemStatus){
							case "available":
								ele.addClass("itemToSell");
								htmlTemp += "<span class='TagItemPrice'>$" + data.itemPrice +"</span>";
							break;    
							case "free":
								ele.addClass("itemFree");
								htmlTemp += "<span class='TagItemPrice'>Free</span>";									
							break;
							case "sold":
								ele.addClass("itemSold");    
								htmlTemp += "<span class='TagItemPrice'>Sold</span>";									
							break;
						}
						ele.html(htmlTemp);
				},
				tagpanelAction:{
						url:"<?php echo WEB_PATH?>library/tagMeCall.php",
						submitFormId:'tm-tagpanel-form',
						onProgress:function(ele){},
						onSuccess:function(ele){
							var aLink = $("#tagedSpecific_" + justLoadedImg + " a").attr('href'),
								imgSrc = $("#tagedSpecific_" + justLoadedImg + " img").attr('src'),
								newDivInHtml = "";
							// This is to reload the masonary plugin
							newDivInHtml = "<div id='tagedSpecific_" + justLoadedImg + "' class='thumb box masonry-brick' style='position: absolute; top: 0px; left: 0px;'>";
							newDivInHtml += "<a href='" + aLink + "'><img id='" + justLoadedImg + "' src='" + imgSrc + "'></a></div>";								
							$("#tagedSpecific_" + justLoadedImg).remove();
							$("#tagged_images").prepend(newDivInHtml);
							
							$containerFor_untagged_imagesForLastTag = $('#untagged_images');
							$containerFor_untagged_imagesForLastTag.masonry('reload');
							
							$containerFor_tagged_imagesForLastTag = $('#tagged_images');
							$containerFor_tagged_imagesForLastTag.masonry('reload');
							
							$("#tagedSpecific_" + justLoadedImg + " a").fancybox({
								'transitionIn'		: 'fade',
								'transitionOut'		: 'fade',
								'titlePosition'		: 'outside',
								'overlayColor'		: '#777777',
								'overlayOpacity'	: 0.7,
								'hideOnContentClick': false,
								'autoScale':false,
								'showNavArrows':false,
								'cyclic':false,
								'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
									fancyContent = "";
								}
						   }).find("#fancybox-outer").TagMe();
						},
						onFail:function(ele){},
						json:true		
				},
				tagpanelEditAction:{
					url:"<?php echo WEB_PATH?>library/tagMeCall.php",
					submitFormId:'tm-tagpanel-form',
					onProgress:function(res){},
					onSuccess:function(res){
				
						var ele = $("#"+res.obj.tagId);
						ele.removeClass();
						ele.addClass("tagItem-1");
						ele.html("");

						//var htmlTemp = "<span class='TagItemName'>" + res.obj.itemName + "</span>";
						var htmlTemp = "";
														
						switch(res.obj.itemStatus){
							case "available":
								ele.addClass("itemToSell");
								htmlTemp += "<span class='TagItemPrice'>$" + res.obj.itemPrice +"</span>";
							break;    
							case "free":
								ele.addClass("itemFree");
								htmlTemp += "<span class='TagItemPrice'>Free</span>";									
							break;
							case "sold":
								ele.addClass("itemSold");    
								htmlTemp += "<span class='TagItemPrice'>Sold</span>";									
							break;
						}
						ele.html(htmlTemp);	
						$.fn.TagMe('closeTagBox');
						//$.fancybox.close();
						
					},
					onFail:function(res){},
					json:true		
				},
				tagpanelDeleteAction:{
					url:"<?php echo WEB_PATH?>library/tagMeCall.php",
					submitFormId:'tm-tagpanel-form',
					onProgress:function(res){},
					onSuccess:function(res,ele){
						var allTags = $(".tagItem-1");
						if (allTags.length == 0){
							var aLink = $("#tagedSpecific_" + res.obj.imageId + " a").attr('href'),
								imgSrc = $("#tagedSpecific_" + res.obj.imageId + " img").attr('src'),
								newDivInHtml = "";
							//$.fancybox.close();
							// This is to reload the masonary plugin
							newDivInHtml = "<div id='tagedSpecific_" + res.obj.imageId + "' class='thumb box masonry-brick' style='position: absolute; top: 0px; left: 0px;'>";
							newDivInHtml += "<a href='" + aLink + "'><img id='" + res.obj.imageId + "' src='" + imgSrc + "'></a></div>";								
							$("#untagged_images").prepend(newDivInHtml);
							$("#tagedSpecific_" + res.obj.imageId).remove();
							$containerFor_tagged_imagesForLastTag = $('#tagged_images');
							$containerFor_tagged_imagesForLastTag.masonry('reload');
							
							$containerFor_untagged_imagesForLastTag = $('#untagged_images');
							$containerFor_untagged_imagesForLastTag.masonry('reload');
						
							$("#tagedSpecific_" + res.obj.imageId + " a").fancybox({
								'transitionIn'		: 'fade',
								'transitionOut'		: 'fade',
								'titlePosition'		: 'outside',
								'overlayColor'		: '#777777',
								'overlayOpacity'	: 0.7,
								'hideOnContentClick': false,
								'autoScale':false,
								'showNavArrows':false,
								'cyclic':false,
								'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
									fancyContent = "";
								}
						   }).find("#fancybox-outer").TagMe();
								
						}
						//$("#"+res.obj.tagId).remove();
						
					},
					onFail:function(res){},
					json:true		
				},
				tagpanelOnAfterInitPanel:function(ele){
					
					var iname = ele.find("input[name=itemName]");
					var iprice = ele.find("input[name=itemPrice]");
					var ides = ele.find("textarea[name=itemDescription]");
					var freeCheckBox = ele.find("input[name=freeCheckBox]");

					freeCheckBox.click(function(){
						if ($(iprice).val() == 'Price'){
							$(iprice).val('$0.00');
						}else{
							$(iprice).val('Price');
						}	
					});

					iprice.keypress(function(event) {
						if ($.trim(iprice.val()) == '$0.0'){
							if ($.trim(iprice.val()) == '$0.0'){
								$.trim(iprice.val('$0.0'));
							}
							freeCheckBox.prop("checked", true);
						}else{
							freeCheckBox.prop("checked", false);
						}
					});						

					iname.focus(function(){
						$(this).removeClass('fail');
						if($.trim($(this).val()) == "Item Name")
							$(this).val("");
					});
					iname.blur(function(){
						if($.trim($(this).val()) == "")
							$(this).val("Item Name");
					});
					iprice.focus(function(){
						$(this).removeClass('fail');
						if($.trim($(this).val()) == "Price")
							$(this).val("");
					});
					iprice.blur(function(){
						if($.trim($(this).val()) == "")
							$(this).val("Price");
					});
					ides.focus(function(){
						$(this).removeClass('fail');
						if($.trim($(this).val()) == "Item Description")
							$(this).val("");
					});
					ides.blur(function(){
						if($.trim($(this).val()) == "")
							$(this).val("Item Description");
					});
					// This is for the free button activity
					$('#freeNotifying').click(function(){
						if ($(this).attr('class') == 'floatr'){
							$("input[name='itemPrice']").val('0.00');								
							$(this).removeClass('floatr');
							$(this).addClass('floatl');
						}else{
							$("input[name='itemPrice']").val('Price');																
							$(this).removeClass('floatl');
							$(this).addClass('floatr');
						}							   
					});
				},
				validateForm:function(ele){
					var ret =  true;
					var iname = ele.find("input[name=itemName]");
					var iprice = ele.find("input[name=itemPrice]");
					var ides = ele.find("textarea[name=itemDescription]");

					if ($.trim(iname.val()) == "" || $.trim(iname.val()) == "Item Name" ){
						ret = false;
						iname.addClass('fail');
					}
					if ($.trim(iprice.val()) == "" || $.trim(iprice.val()) == "Price" || !$.trim(iprice.val()).match(/^\$?((\d{1,3}(,\d{3})*)|\d+)(\.(\d{2})?)?$/) ){
						if ($("#status").val() != "free"){
							ret = false;
							iprice.addClass('fail');
						}else{
							ret = true;
							iprice.removeClass('fail');
						}
					}
					if ($.trim(ides.val()) == "" || $.trim(ides.val()) == "Item Description" ){
						ret = false;
						ides.addClass('fail');
					}
					return ret;							
				}
			});
		}
	});
}
</script>
<div id="UploadWrapper">
<div class="clearH"></div>
	<span class="UploaderTitle1">Tagged Items</span>
    <div class="clearH15"></div>
	<div id="tagged_images">
    	<?php foreach($ViewableData['taggedImages'] as $eachImage):
				$crypedId = md5($eachImage['title'].$eachImage['pictureId']);
        		$_SESSION['recentUploads'][$crypedId] =  $eachImage['pictureId'];				
		?>
    	<div class="thumb box" id="tagedSpecific_<?php echo $crypedId;?>">
        	<a href="<?php echo WEB_PATH.urldecode($eachImage['uploadLImgLocation']);?>"><img id="<?php echo $crypedId;?>" src="<?php echo WEB_PATH.urldecode($eachImage['uploadTImgLocation']);?>" /></a>
        </div>
    	<?php endforeach;?>
    </div>
    <div class="clearH50"></div>
    <div class="Border2 clearH50"></div>
    <span class="UploaderTitle1">Untagged Items</span>
    <div class="clearH15"></div>
    <div id="untagged_images">
    	<?php foreach($ViewableData['untaggedImages'] as $eachImage):
				$crypedId = md5($eachImage['title'].$eachImage['pictureId']);
        		$_SESSION['recentUploads'][$crypedId] =  $eachImage['pictureId'];				
		?>
    	<div class="thumb box" id="tagedSpecific_<?php echo $crypedId;?>">
        	<a href="<?php echo WEB_PATH.urldecode($eachImage['uploadLImgLocation']);?>"><img id="<?php echo $crypedId;?>" src="<?php echo WEB_PATH.urldecode($eachImage['uploadTImgLocation']);?>" /></a>
        </div>
    	<?php endforeach;?>
    </div>
<script type="text/javascript">
$(document).ready(function() {
	<?php if (strstr(REQ_PATH, "/www.rehand.com/users/uploader/?view=uploaded")):?>
    // This is to scroll tagged images and untagged images
    $("#taggedImgLink").click(function(){
    	$('html,body').animate({scrollTop: $("#tagged_images").offset().top - 100}, 2000);
    });
    $("#untaggedImgLink").click(function(){
    	$('html,body').animate({scrollTop: $("#untagged_images").offset().top}, 2000);
    });
	<?php endif;?>					   
	var $container = $('#tagged_images');
	$container.imagesLoaded(function(){
	  $container.masonry({
		itemSelector: '.box',
		isAnimated: true
	  });
	});
	var $container1 = $('#untagged_images');
	$container1.imagesLoaded(function(){
	  $container1.masonry({
		itemSelector: '.box',
		isAnimated: true
	  });
	});
});	
</script>    
</div>
<?php elseif($_GET['view'] == 'add'):?>
<style>
#Items{background:url(<?php echo SITE_IMAGES_PATH?>loginbuttonbg.jpg) repeat-x bottom!important;border:1px solid #48515A;}
#Items > a{background-position:8px bottom!important;color:#FFF!important;text-shadow:1px 1px #333;} 
#Items .menuarrow{background-position:left bottom!important;}
</style>
<script src="<?php echo WEB_PATH?>public/js/uploader/fileuploader.js" type="text/javascript"></script>
<script src="<?php echo WEB_PATH?>public/js/uploader/tagme.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
	createUploader();  
});
function createUploader(){
	var uploader = new qq.FileUploader({
		button: document.getElementById('uploadButton'),
		element: document.getElementById('file-uploader-demo1'),
		thumbHolder: document.getElementById('smallThumbs'),
		action: '<?php echo WEB_PATH?>library/uploadCall.php',
		//allowedExtensions : ['jpg','jpeg','gif','bmp', 'png'], // this is not to show a message
		debug:false,
		onSubmit: function(id, fileName, file){
			var errorInUploader, errorMsg;
			/*
			if (file.size < 100000){//  20480
				errorInUploader = true;
				errorMsg = "This file is too small to upload !";
			}
			*/
			if ((file.type != "image/jpg") && (file.type != "image/jpeg") && (file.type != "image/gif") && (file.type != "image/png") && (file.type != "image/bmp")){
				errorInUploader = true;
				errorMsg = "Invalid file type";
			}
			if (errorInUploader){
				$("#UploadError").removeClass("hide");
				$("#UploadError").html(errorMsg);
				setTimeout(function(){
					$("#UploadError").html(errorMsg);
					$("#UploadError").addClass("hide");
				}, 2000);
				return false;
			}
		},
		onCleanup:function(ele){
			ele.find(".picDeleteButton").remove();
			ele.find(".deleteConfirmDiv").remove();
			ele.find(".tm-tagbox").remove();
			ele.find(".tm-tagpanel").remove();
			ele.find(".tagItem-1").remove();
		},
		onProgress: function(id, fileName, file, loaded, total, percentage){
			
			if (file.size > 5120){
				$('#Loader').removeClass('hide');
				$('#Loading').removeClass('hide');
				$('.preview').css({"display" : "none"});
				$('#uploadButton').remove();
			}
		},
		onComplete: function(id, ele, responseJSON){

			if (responseJSON.success == true){
				
				$("#back-to-rehand").attr('href', '<?php echo WEB_PATH?>users/uploader/?view=add');
				$('#Loading').html('completed');
				setTimeout(function(){
					$('.preview').css({"display" : "block"});	
					$('#uploadStepsInFinal').removeClass('step1_upload');
					$('#uploadStepsInFinal').addClass('step2_tag');
					$('#uploadStepsInFinal #contentChangeDiv').append($('#userUploadArea'));				
					$('#uploadedButtonArea').html('');
					$('#finishTaggingButton').removeClass('hide');
					$('#steps3').removeClass('step1');
					$('#steps3').addClass('step2');		
					$('#tagsPanelForJustAdded').removeClass('hide');
					$('#clearTagMainshadow').removeClass('hide');
					$('#TagTips').removeClass('hide');
					$('#TagTips').html("Did you know: You can tag more than one item in this picture");
					$('#uploadStepsInFinal span.titlelogin').html('Step 2 - Tag your items');				
				}, 500); 			
				// This is for the location and title input form	
				$.ajax({  
						type: "POST",
						//data: "messageFromBuyer=" + $(".buyerInfoMessageWrite").val(),
						url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=getTheSuburbInputForm", 
						success: function(server_response){				
							if (server_response != ''){
								var SuccesMesg = "";
								$(".qq-uploader").removeClass("hide");													
								$("#file-uploader-demo1 .qq-uploader .qq-upload-list #" + $(ele).attr('id')).prepend(SuccesMesg);
								var locationForm = $("<div class='clearH'></div><div id='locationFomr_" + $(ele).attr('id') + "'>" + server_response + "</div>");
								$("#file-uploader-demo1 .qq-uploader .qq-upload-list").prepend(locationForm);
								$(".UploadInfo").removeClass('hide');
								$("#locationFomr_" + $(ele).attr('id')).click(function(e){
									e.stopPropagation();
								});
								$(".UploadInfo").click(function(e){
									e.stopPropagation();
								});
							}
						}
					});
				
					$('#finishTaggingButton').click(function(){
						// When this finish tagging button clicked what ever the values the suburb form is haivng should save to the database
						$.ajax({  
							type: "POST",
							data: "imgId=" + $(ele).attr('id') + "&location=" + $(".locationInputWrite").val() + "&contactNo=" + $(".ContactInputWrite").val(),
							url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=saveLocationData" 
						});
						$('#uploadStepsInFinal').removeClass('step2_tag');									 
						$('#uploadStepsInFinal').addClass('step3_group');
						$('#userUploadArea').addClass('hide'); 
						$('#uploadStepsInFinal #contentChangeDiv #groupNamesListWrapper_' + $(ele).attr('id')).html('');
						$('#uploadStepsInFinal span.titlelogin').html('Step 3 - Post to groups');
						$('#back-to-rehand').remove();
						$('#finishTaggingButton').addClass('hide');
						$('#tagsPanelForJustAdded').addClass('hide');
						$('#clearTagMainshadow').addClass('hide');
						$('#clearH20').addClass('hide');
						$('#TagTips').css({"width" : "253px","line-height" : "15px","font-size" : "12px","padding" : "5px 10px 6px 50px"});
						$('#TagTips').html("Did you know: Tag your item to groups to share among people with similar interests.");
						
						// This is for the groups subscription section
						$.getJSON("<?php echo WEB_PATH?>library/ajaxFunctions.php?action=loadAllOwnedGroupsForSubscrition&webpath=<?php echo WEB_PATH?>", function(data) {        			
							// This is for the group names subscription form
							var subscribeValuesSecond = new Array(),
								removeSubscriptedSecond = new Array(),
								alreadyCheckedItems = new Array(),
								letterPressedAsArray = new Array(),
								searchResultsAfterLetterPressing = new Array(),
								searchResultsAfterLetterPressingForSecondTime = new Array(),
								searchResultsAfterLetterPressingForThirdTime = new Array(),
								subscriptionsAsStr = "",
								noOfTimesLetterWasPressed = 0,
								groupNamesListInHtml = "",
								groupNamesListInHtml = "",
								filterdGroupNamesAfterLetterWasPressed = new Array(),
								serverResponse = "",
								secondResult = "",
								lastResult = "",
								subscriptionGroupIdArray = new Array(),
								origianlDataInTheSubscriptionWrapperAsHtml = "",
								clickedForSubscribedButton = false,
								removeSubscriptionsAsStr = "";
	
							groupNamesListInHtml += "<div class='clearH'></div><div id='groupNamesListWrapper_" + $(ele).attr('id') + "'>";
							if ((data != '') && (data != null) && (data.length != 0)){
								groupNamesListInHtml += "<h3>Select groups for your items to be featured in...</h3>";
								groupNamesListInHtml += "<a id='resetGroupUp' href='javascript:void(0);'>Reset</a><input type='text' name='searchGroupInpInPicUpload' value='Type a Letter' onBlur='if (this.value == \"\") {this.value = \"Type a Letter\";}' onFocus='if (this.value == \"Type a Letter\") {this.value = \"\";}' id='searchGroupInpInPicUpload' />";
								groupNamesListInHtml += "<div class='clearH15'></div>";
								groupNamesListInHtml += "<div id='groupNamesList_" + $(ele).attr('id') + "'>";
								$.each(data, function(i) {
									groupNamesListInHtml += data[i].html;
									origianlDataInTheSubscriptionWrapperAsHtml += data[i].html;
								});
								groupNamesListInHtml += "</div>";
								groupNamesListInHtml += "<div class='clear'></div>";							
								groupNamesListInHtml += "<div id='clearGroupMainshadow'></div>";
								groupNamesListInHtml += "<input type='button' name='subscribeToTheseGroups' id='subscribeToTheseGroups' value='Post to Rehand' class='Button1' style='float:right;' />";
								groupNamesListInHtml += "</div>";
								$("#uploadStepsInFinal #contentChangeDiv").append('<div class="clear"></div>');
								$(groupNamesListInHtml).appendTo($("#uploadStepsInFinal #contentChangeDiv"));
								// This is to single subcription button
								$("input[name='grpNameSubscribe']").click(function(){
									var clickedGroupButtonId = $(this).attr('id');											   
									var selectedGrpIdToSubscribe = $(this).attr('id').replace('subscribeToThisGroupId_', '');
									if (!clickedForSubscribedButton){
										clickedForSubscribedButton = true;
										$('#' + clickedGroupButtonId).addClass('subscribeButtonClor');
										$('#' + clickedGroupButtonId).val('subscribed');
										subscriptionGroupIdArray.push(selectedGrpIdToSubscribe);
									}else{
										clickedForSubscribedButton = false;									
										$('#' + clickedGroupButtonId).removeClass('subscribeButtonClor');
										$('#' + clickedGroupButtonId).val('subscribe');
										for(var i=0; i < subscriptionGroupIdArray.length; i++){ 
											if (subscriptionGroupIdArray[i] == selectedGrpIdToSubscribe)
												subscriptionGroupIdArray.splice(i ,1); 
										} 
									}
								});
								// This is for the filtering process for the group names
								$("#groupNamesListWrapper_" + $(ele).attr('id') + " #searchGroupInpInPicUpload").keypress(function(event){
									  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html("");
									  letterWasPressed = String.fromCharCode(event.which).toLowerCase();
									  letterPressedAsArray.push(letterWasPressed);
									  if (event.keyCode != 8){
										  if (letterPressedAsArray.length == 1){
											  var increm = 0;
											  $.each(data, function(a){
												  if (data[a].groupName[0].toLowerCase() == letterPressedAsArray[0]){
													  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).append(data[a].html);			
													  searchResultsAfterLetterPressing[increm] =  {'groupName' : data[a].groupName, 'html' : data[a].html};
													  increm++;
												  }
											  });
										  }else if (letterPressedAsArray.length == 2){
											  if (searchResultsAfterLetterPressing.length > 1){
												  var incerSec = 0;
												  $.each(searchResultsAfterLetterPressing, function(n){
													  if (
														  (searchResultsAfterLetterPressing[n].groupName[0].toLowerCase() == letterPressedAsArray[0]) && 											  	
														  (searchResultsAfterLetterPressing[n].groupName[1].toLowerCase() == letterPressedAsArray[1])
														 ){
															  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).append(searchResultsAfterLetterPressing[n].html);		
															  searchResultsAfterLetterPressingForSecondTime[incerSec] = {'groupName' : searchResultsAfterLetterPressing[n].groupName, 'html' : searchResultsAfterLetterPressing[n].html};
															  incerSec++;	
														  }
												  });
											  }else{
												  if (
													  (searchResultsAfterLetterPressing[0].groupName[0].toLowerCase() == letterPressedAsArray[0]) &&
													  (searchResultsAfterLetterPressing[0].groupName[1].toLowerCase() == letterPressedAsArray[1])
													 ){
															$("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html(searchResultsAfterLetterPressing.html);		
															searchResultsAfterLetterPressingForSecondTime = {'groupName' : searchResultsAfterLetterPressing[0].groupName, 'html' : searchResultsAfterLetterPressing[0].html};
													  }
											  }
										  }else if (letterPressedAsArray.length == 3){
											  if (searchResultsAfterLetterPressingForSecondTime.length > 1){
												  var incremThird = 0;
												  $.each(searchResultsAfterLetterPressingForSecondTime, function(m){
													  if (
														  (searchResultsAfterLetterPressingForSecondTime[m].groupName[0].toLowerCase() == letterPressedAsArray[0]) &&
														  (searchResultsAfterLetterPressingForSecondTime[m].groupName[1].toLowerCase() == letterPressedAsArray[1]) &&
														  (searchResultsAfterLetterPressingForSecondTime[m].groupName[2].toLowerCase() == letterPressedAsArray[2])
														 ){
															  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).append(searchResultsAfterLetterPressingForSecondTime[m].html);	
															  searchResultsAfterLetterPressingForThirdTime[incremThird] = {'groupName' : searchResultsAfterLetterPressingForSecondTime[m].groupName, 'html' : searchResultsAfterLetterPressingForSecondTime[m].html};															
															  incremThird++;
														  }
												  });
											  }else{
												  if (searchResultsAfterLetterPressingForSecondTime[0] == null){
													  if (
														  (searchResultsAfterLetterPressingForSecondTime.groupName[0].toLowerCase() == letterPressedAsArray[0]) &&
														  (searchResultsAfterLetterPressingForSecondTime.groupName[1].toLowerCase() == letterPressedAsArray[1]) &&
														  (searchResultsAfterLetterPressingForSecondTime.groupName[2].toLowerCase() == letterPressedAsArray[2])
														 ){
																$("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html(searchResultsAfterLetterPressingForSecondTime.html);	
																searchResultsAfterLetterPressingForThirdTime = {'groupName' : searchResultsAfterLetterPressingForSecondTime.groupName, 'html' : searchResultsAfterLetterPressingForSecondTime.html};																												
														  }
												  }else{
													  if (
														  (searchResultsAfterLetterPressingForSecondTime[0].groupName[0].toLowerCase() == letterPressedAsArray[0]) &&
														  (searchResultsAfterLetterPressingForSecondTime[0].groupName[1].toLowerCase() == letterPressedAsArray[1]) &&
														  (searchResultsAfterLetterPressingForSecondTime[0].groupName[2].toLowerCase() == letterPressedAsArray[2])
														 ){
																$("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html(searchResultsAfterLetterPressingForSecondTime[0].html);	
																searchResultsAfterLetterPressingForThirdTime = {'groupName' : searchResultsAfterLetterPressingForSecondTime[0].groupName, 'html' : searchResultsAfterLetterPressingForSecondTime[0].html};																												
														  }
												  }	
											  }
										  }else{
											  if (searchResultsAfterLetterPressingForThirdTime.length > 1){
												  $.each(searchResultsAfterLetterPressingForThirdTime, function(r){
													  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).append(searchResultsAfterLetterPressingForThirdTime[r].html);	
												  });
											  }else{
												  if (searchResultsAfterLetterPressingForThirdTime[0] == null){
													  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html(searchResultsAfterLetterPressingForThirdTime.html);	
												  }else{
													  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html(searchResultsAfterLetterPressingForThirdTime[0].html);	
												  }	
											  }
										  }
									  }else{
										  $("#searchGroupInpInPicUpload").val('');
										  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html('');	
										  $("#groupNamesListWrapper_" + $(ele).attr('id') + " #groupNamesList_" + $(ele).attr('id')).html(origianlDataInTheSubscriptionWrapperAsHtml);	
										  letterPressedAsArray = new Array();										
									  }	
								});
								
								$("#groupNamesListWrapper_" + $(ele).attr('id')).click(function(e){
									e.stopPropagation();
								});
								function grabTheDefaultGroupListAfterClearing(){
									$.ajax({  
										type: "GET",
										url: "<?php echo WEB_PATH?>library/ajaxFunctions.php?action=resetTheGroupNamesList", 
										success: function(res){	
											if ((res != "") && (res != null)){
												$("#groupNamesList_" + $(ele).attr('id')).html("");
												$("#groupNamesList_" + $(ele).attr('id')).html(res);																			
											}
										}
									});
								}
								// This is for the reset button
								$("#resetGroupUp").click(function(){
									noOfTimesLetterWasPressed = 0;
									$("#groupNamesList_" + $(ele).attr('id')).html('');	
									filterdGroupNamesAfterLetterWasPressed = new Array();
									$.getJSON("<?php echo WEB_PATH?>library/ajaxFunctions.php?action=resetTheGroupNamesList&webpath=<?php echo WEB_PATH?>", function(res) {						  
										$.each(res, function(i) {	
											  $("#groupNamesList_" + $(ele).attr('id')).append(res[i].html);
											  filterdGroupNamesAfterLetterWasPressed.push(res[i].groupName); 										
										});
										// This is to single subcription button
										$("input[name='grpNameSubscribe']").click(function(){
											var clickedGroupButtonId = $(this).attr('id');											   
											var selectedGrpIdToSubscribe = $(this).attr('id').replace('subscribeToThisGroupId_', '');
											if (!clickedForSubscribedButton){
												clickedForSubscribedButton = true;
												$('#' + clickedGroupButtonId).addClass('subscribeButtonClor');
												$('#' + clickedGroupButtonId).val('subscribed');
												subscriptionGroupIdArray.push(selectedGrpIdToSubscribe);
											}else{
												clickedForSubscribedButton = false;									
												$('#' + clickedGroupButtonId).removeClass('subscribeButtonClor');
												$('#' + clickedGroupButtonId).val('subscribe');
												for(var i=0; i < subscriptionGroupIdArray.length; i++){ 
													if (subscriptionGroupIdArray[i] == selectedGrpIdToSubscribe)
														subscriptionGroupIdArray.splice(i ,1); 
												} 
											}
										});
									});
								});
							}else{
								groupNamesListInHtml += "<h3>No followed groups found !</h3><div class='clearH20'></div>";							
								groupNamesListInHtml += "<input type='button' name='subscribeToTheseGroups' id='subscribeToTheseGroups' value='Post to Rehand' class='Button1' style='float:right;' />";
								groupNamesListInHtml += "</div>";
								$("#uploadStepsInFinal #contentChangeDiv").append('<div class="clear"></div>');
								$(groupNamesListInHtml).appendTo($("#uploadStepsInFinal #contentChangeDiv"));
							}
							// This is to subscribe these buttons and post them at once to rehand database
							$('#subscribeToTheseGroups').click(function(){
								// If new subscriptions array not empty
								if (subscriptionGroupIdArray.length != 0){
									var redirectedPath = "";
									$.ajax({  
										type: "POST",
										url: "<?php echo WEB_PATH?>library/ajaxFunctions.php?action=newSubscriptionsForUploadedPic", 
										data: "picId=" + $(ele).attr('id') + "&subscriptionsAsStr=" + subscriptionGroupIdArray, 
										success: function(data){	
											if (data == '1'){
												setTimeout(function(){
													location.href = '<?php echo WEB_PATH?>';
												}, 2000); 			
											}else{
												setTimeout(function(){
													location.href = '<?php echo WEB_PATH?>users/uploader/?view=add';
												}, 2000); 			
											}
										}
									});
								}else{
									setTimeout(function(){
										location.href = '<?php echo WEB_PATH?>users/uploader/?view=add';
									}, 2000); 			
								}
								$('#steps3').removeClass('steps3');
								$('#steps3').addClass('stepdone');
								$('#uploadStepsInFinal').addClass('hide');												
							});
						});
						$('#finishTaggingButton').addClass('hide');
						$('#steps3').removeClass('step2');
						$('#steps3').addClass('step3');	
					});
					
					// This is for the tagging feature		
					$(ele).TagMe({
						id:$(ele).attr('id'),
						tagpanelElement: '<div class="tm-tagpanel">'+
											 '<span class="tagtitle">Tag an item...</span><div class="clearH15"></div><form id="tm-tagpanel-form">'+
												 '<input name="itemName" value="Item Name" class="TagInputBg">'+
												 '<div class="clearH10"></div><textarea name="itemDescription" class="TagTextareaBg">Item Description</textarea>'+									 
												 '<div class="clearH10"></div><input name="itemPrice" value="Price" class="TagInputBg" style="width:120px;">'+
												 '<div class="floatr"><span class="floatl" style="margin:7px 7px 0 0;color:#545C69;">Free</span><div class="TagFree"><a id="freeNotifying" href="javascript:void(0)" class="floatr"></a></div></div>'+
												 '<button type="button" class="Tagpanelclose"></button><div class="clearTagshadow"></div><div class="clear"></div><button type="submit" class="GreenBut" style="float:right;">Add Item</button>'+
											 '</form>'+
										 '</div>',
						tagEditpanelElement: '<div class="tm-tagpanel">'+
												 '<span class="tagtitle">Edit Tag...</span><div class="clearH15"></div><form id="tm-tagpanel-form">'+
												 '<input name="itemName" value="Item Name" class="TagInputBg">'+
												 '<div class="clearH10"></div><textarea name="itemDescription" class="TagTextareaBg">Item Description</textarea>'+	
												 '<div class="clearH10"></div><input name="itemPrice" value="Price" class="TagInputBg" style="width:120px;">'+
												 '<div class="clearH10"></div><select name="itemStatus" class="DDBg">'+
												 '	<option value="available">Available</option>'+
												 '	<option value="free">Free</option>'+											 
												 '	<option value="sold">Sold</option>'+
												 '</select><div class="clearH5"></div>'+
												 '<button type="button" class="Tagpanelclose"></button><div class="clearTagshadow"></div><div class="clear"></div><button type="submit" class="GreenBut" style="float:right;">Save Item</button>'+
											 '</form>'+
										 '</div>',
						tagpanelAction:{
										url:"<?php echo WEB_PATH?>library/tagMeCall.php",
										submitFormId:'tm-tagpanel-form',
										onProgress:function(ele){},
										onSuccess:function(ele){},
										onFail:function(ele){},
										json:true		
						},
						onTagAdded:function(ele,data){
							ele.removeClass();
							ele.addClass("tagItem-1");
							//var htmlTemp = "<span class='TagItemName'>" + data.itemName + "</span>";
							var htmlTemp = "";							
														
							switch(data.itemStatus){
								case "available":
									ele.addClass("itemToSell");
									htmlTemp += "<span class='TagItemPrice'>$" + data.itemPrice +"</span>";
								break;    
								case "free":
									ele.addClass("itemFree");
									htmlTemp += "<span class='TagItemPrice'>Free</span>";									
								break;
								case "sold":
									ele.addClass("itemSold");    
									htmlTemp += "<span class='TagItemPrice'>Sold</span>";									
								break;
							}
							ele.html(htmlTemp);
							// Add each added tag for the tagged panel
							var spanForJustAddedTag = "<div id='tagWrapper_" + data.tagId + "' class='floatl'><a class='TagsHNameJustAdded TagsH' href='javascript:void(0);'><span class='TagHName' title='" + data.itemDescription + "'>" + data.itemName + "</span>";
								spanForJustAddedTag += "<span class='Price'>$" + data.itemPrice + "</span></a></div>";
							$("#messageToShowForTags").html('Click tag to edit :');
							$('#tagsPanelForJustAdded').append(spanForJustAddedTag);
							// This is to load the related image with pointing tag when some one clicks on tag
							$(".TagsHNameJustAdded").click(function(){
								$('#' + $(this).parent().attr('id').replace('tagWrapper_', '')).trigger('click');
							});
						},
						tagpanelEditAction:{
							url:"<?php echo WEB_PATH?>library/tagMeCall.php",
							submitFormId:'tm-tagpanel-form',
							onProgress:function(res){},
							onSuccess:function(res){
								var ele = $("#" + res.obj.tagId + " .imageHolder");
								ele.removeClass();
								ele.addClass("tagItem-1");
								ele.html("");
								var htmlTemp = "";
								//var htmlTemp = "<span class='TagItemName'>" + res.obj['itemName'] + "</span>";
								switch(res.obj['itemStatus']){
									case "available":
										ele.addClass("itemToSell");
										htmlTemp += "<span class='TagItemPrice'>" + res.obj['itemPrice'] +"</span>";
									break;    
									case "free":
										ele.addClass("itemFree");
										htmlTemp += "<span class='TagItemPrice'>Free</span>";									
									break;
									case "sold":
										ele.addClass("itemSold");    
										htmlTemp += "<span class='TagItemPrice'>Sold</span>";									
									break;
								}
								ele.html(htmlTemp);
								$.fn.TagMe('closeTagBox');
							},
							onFail:function(res){},
							json:true		
						},
						tagpanelDeleteAction:{
							url:"<?php echo WEB_PATH?>library/tagMeCall.php",
							submitFormId:'tm-tagpanel-form',
							onProgress:function(res){},
							onSuccess:function(res,ele){},
							onFail:function(res){},
							json:true		
						},
						tagpanelOnAfterInitPanel:function(ele){
							var iname = ele.find("input[name=itemName]");
							var iprice = ele.find("input[name=itemPrice]");
							var ides = ele.find("textarea[name=itemDescription]");
							var freeCheckBox = ele.find("input[name=freeCheckBox]");
		
							freeCheckBox.click(function(){
								if ($(iprice).val() == 'Price'){
									$(iprice).val('$0.00');
								}else{
									$(iprice).val('Price');
								}	
							});
							iprice.keypress(function(event) {
								if ($.trim(iprice.val()) == '$0.0'){
									if ($.trim(iprice.val()) == '$0.0'){
										$.trim(iprice.val('$0.0'));
									}
									freeCheckBox.prop("checked", true);
								}else{
									freeCheckBox.prop("checked", false);
								}
							});						
							iname.focus(function(){
								$(this).removeClass('fail');
								if($.trim($(this).val()) == "Item Name")
									$(this).val("");
							});
							iname.blur(function(){
								if($.trim($(this).val()) == "")
									$(this).val("Item Name");
							});
							iprice.focus(function(){
								$(this).removeClass('fail');
								if($.trim($(this).val()) == "Price")
									$(this).val("");
							});
							iprice.blur(function(){
								if($.trim($(this).val()) == "")
									$(this).val("Price");
							});
							ides.focus(function(){
								$(this).removeClass('fail');
								if($.trim($(this).val()) == "Item Description")
									$(this).val("");
							});
							ides.blur(function(){
								if($.trim($(this).val()) == "")
									$(this).val("Item Description");
							});
							// This is for the free button activity
							$('#freeNotifying').click(function(){
								if ($(this).attr('class') == 'floatr'){
									$("input[name='itemPrice']").val('0.00');								
									$(this).removeClass('floatr');
									$(this).addClass('floatl');
								}else{
									$("input[name='itemPrice']").val('Price');																
									$(this).removeClass('floatl');
									$(this).addClass('floatr');
								}							   
							});
					},
					validateForm:function(ele){
						var ret =  true;
						var iname = ele.find("input[name=itemName]");
						var iprice = ele.find("input[name=itemPrice]");
						var ides = ele.find("textarea[name=itemDescription]");
	
						if($.trim(iname.val()) == "" || $.trim(iname.val()) == "Item Name" ){
							ret = false;
							iname.addClass('fail');
						}
						if($.trim(iprice.val()) == "" || $.trim(iprice.val()) == "Price" || !$.trim(iprice.val()).match(/^\$?((\d{1,3}(,\d{3})*)|\d+)(\.(\d{2})?)?$/) ){
							if ($("#status").val() != "free"){
								ret = false;
								iprice.addClass('fail');
							}else{
								ret = true;
								iprice.removeClass('fail');
							}
						}
						if($.trim(ides.val()) == "" || $.trim(ides.val()) == "Item Description" ){
							ret = false;
							ides.addClass('fail');
						}
						return ret;							
					}
				});
			}else{
				if (responseJSON.error != ""){
					$("#UploadError").removeClass("hide");
					$("#UploadError").html("Images you upload should be at least 300 pixels in width.");
					setTimeout(function(){
						$("#UploadError").html("");
						$("#UploadError").addClass("hide");
					}, 2000);
					return false;
				}
			}
		},debug: false
	});
}
</script>
<div id="UploadSteps">
    Post items to social groups on Rehand
    <div class="clear"></div>
    <strong>in 3 easy steps</strong>
    <div class="clearH15"></div>
    <div id="steps3" class="step1"></div>
</div>
<div class="clearH"></div>
<div id="uploadStepsInFinal" class="step1_upload">
	<span class="titlelogin">Step 1 - Upload your photo</span><a id="back-to-rehand" href="<?php echo WEB_PATH?>"></a>
    <div id="contentChangeDiv">
    	<div id="uploadedButtonArea">
            <div class="clearH30"></div>
            <img src="<?php echo SITE_IMAGES_PATH?>photo.jpg" />
            <div class="clearH"></div>
            <strong>Upload your photo you want to tag</strong>
            <div class="clearH5"></div>
            Ultricies tempor placerat! Turpis tristique aliquam hac.
            <div class="clearH"></div>
            <div id="UploadError" class="hide"></div>
            <div id="uploadButton"></div>
            <div id="Loader" class="hide">
            	<div id="Loading" class="hide" style="width:0%">0%</div>
            </div>
        </div>    
    </div>  
    <div id="clearH20" class="clearH20"></div>  
    <div id="tagsPanelForJustAdded" class="hide"><span id="messageToShowForTags"></span></div>
    <div id="clearTagMainshadow" class="hide"></div>
    <div id="TagTips" class="hide">Did you know: You can tag more than one item in this picture</div>
    <input type="button" id="finishTaggingButton" class="Button1 hide" style="float:right;" value="Finish Tagging" />
	<div class="clear"></div>
</div>
	<div class="clearH"></div> 
	<div id="userUploadArea" style="float:left;">
        <!--<div id="smallThumbs"></div>-->
        <div id="file-uploader-demo1">
            <noscript>			
                <p>Please enable JavaScript to use file uploader. </p>
            </noscript>         
        </div>
	</div>
<?php endif;?>