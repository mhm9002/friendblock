function check_social_login(OauthType, OauthEmail, OauthID, param){
	var result;
	var fdata = 'action=checkSLogin';
	fdata += '&type='+OauthType;
	fdata += '&email='+OauthEmail;
	fdata += '&OauthID='+OauthID;
  
	$.ajax({
	  url:'/check_reg_form.php',
	  type: 'POST',
	  data: fdata,
	  dataType : 'xml',
	  success: function(rsp){
		var id = $(rsp).find('id').text();
  
		if ($(rsp).find('status').text() == 'newID') {
			
			var mdl = '<div id="proceed-modal" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">'+
			'<button type="button" class="close" data-dismiss="modal">&times;</button><label class="modal-title">Link social account</label></div>'+
			'<div class="modal-body">Welcome '+param.firstName+' '+param.lastName+'! <br/>Do you want to link your social account with your friendblock.com account?</div>'+
			'<div class="modal-footer"><form action="/login.php" method="post">'+
			'<input type="hidden" name="login_submit" value="link"/><input type="hidden" name="social-key" value="'+OauthType+'-'+id+'" />'+
			'<input type="hidden" name="email" value="'+OauthEmail+'" /><input type="hidden" name="return" value="/myprofile.php" />'+
			'<button type="submit" class="btn btn-primary">Yes, Proceed</button></div></form></div></div></div>';
			
			$('#proceed-modal').remove();
			$('#main_section').after(mdl);
			$('#proceed-modal').modal('show');
	
		} else if($(rsp).find('status').text() == 'IDfound') {
			
			var mdl = '<div id="proceed-modal" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">'+
			'<button type="button" class="close" data-dismiss="modal">&times;</button><label class="modal-title">Unlink social account</label></div>'+
			'<div class="modal-body">Hi '+param.firstName+' '+param.lastName+'! <br/>You are already linking this social account with your friendblock.com account. Do you want to unlink this social account?</div>'+
			'<div class="modal-footer"><form action="/login.php" method="post">'+
			'<input type="hidden" name="login_submit" value="unlink"/><input type="hidden" name="social-key" value="'+OauthType+'-'+id+'" /><input type="hidden" name="return" value="/myprofile.php" />'+
			'<button type="submit" class="btn btn-primary">Yes, Proceed</button></div></form></div></div></div>';
			
			$('#proceed-modal').remove();
			$('#main_section').after(mdl);
			$('#proceed-modal').modal('show');

		}
		
		$('#newaccount #loading-wrapper').hide();
	  } 
	});
  
}


$(document).ready( function() {
	
	var selectedFile='';
	var selectedID='';
	var albumModalBody='';
		
	var imageCropWidth = 0;
	var imageCropHeight = 0;
	var cropPointX = 0;
	var cropPointY = 0;
	var isOk = false;

	$(document).on('change','.social-toggle',function(){
		
		var type="g";
		if ($(this).attr('id')=='fb-l'){
			type="fb"
		}

		if ($(this).is(':checked')){
			$("#"+type+"-login").trigger('click');
		} else {
			var mdl= '<form action="/login.php" id="unlink-form" style="display:none;" method="post">'+
			'<input type="hidden" name="login_submit" value="unlink"/>'+
			'<input type="hidden" name="social-key" value="'+type+'-0" />'+
			'<input type="hidden" name="return" value="/myprofile.php" />'+
			'<button type="submit" class="btn btn-primary">Yes, Proceed</button></div></form>';

			$('#unlink-form').remove();
			$(this).after(mdl);
			$('#unlink-form').trigger('submit');

		}
	});


	//render Users_photo_modal
	$(document).on('click','#userPhotosBtn', function(){
		var btn = $(this);
		var data = '';
		var html = '';
		
		if (btn.next().hasClass('modal')){
			$('#userPhotos').modal('show');
			return false;
		}
		
		data='userID='+ btn.attr('data-whatever');
		data+= '&action=getalbums';
		
		$('.loading-wrapper').show();
		
		$.ajax({
			type: "POST", data: data, url: "/get_userphotos.php", success: function (returnHTML){
				if(returnHTML == '')
					returnHTML='<label>No photos found</label>';
		
				albumModalBody='<table id="phototable">'+ 
									'<tr><th>Album Name</th><th>Photo count</th><th>Date created</th></tr>'+ 
									returnHTML +
								'</table>'; 
		
				html = '<div class="modal fade" id="userPhotos" tabindex="-1" role="dialog" aria-labelledby="userPhotosTitle" aria-hidden="true" style="display: none;">'+
	'<div class="modal-dialog modal-lg" role="document">'+
		'<div class="modal-content">'+
				'<div class="modal-header">'+
					'<h5 class="modal-title" id="userPhotosTitle">User Photos</h5>'+
					'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
				'</div>'+
				'<div class="modal-body" style="overflow:none;">'+ albumModalBody +'</div>'+             
				'<div class="modal-footer">'+
					'<button type="button" class="btn btn-primary" id="selectProfilePhoto">Select</button>'+	
				'</div></div></div></div>';
				
				btn.after(html);
				$('.loading-wrapper').hide();
				
				$('#userPhotos').modal('show');
			}
		})
		return false;
	
	})		
	
	//return to albums
	$(document).on('click','.img-thumb-up',function(){
		var body = $(this).parents('.modal-body');
		body.empty();
		body.append(albumModalBody);
	})

	//select photo
	$(document).on('click','.img-thumb',function(){
		var selected = $(this);
		var table = $(this).parents('#phototable');
		
		table.find('.img-thumb').css('border','1px solid #000');
		table.find('.img-thumb').css('box-shadow','none');
		selected.css('border', '1px solid #00F');
		selected.css('box-shadow', '0px 0px 10px #00F');

		selectedFile = selected.attr('data-whatever');
		selectedID = selected.attr('id');
	})
	
	//select profile photo
	$(document).on('click','#selectProfilePhoto',function(){
		//$(this).preventDefault();
		
		var sbt = $(this);
		
		if (selectedFile == '')
			return false;
		
		var data = '';
		
		data = 'photo=' + selectedFile;
		data +='&action=setprofilephoto';
		
		$.ajax({
			type: "POST", data: data, dataType: 'xml', url: "/get_userphotos.php", success: function (rsp){
				
				var pUrl = $(rsp).find('url').text();
				var msg = $(rsp).find('thumb').text();
				
				if (msg == 'Resize'){
					
					if (sbt.parents('td').find('#photo-edit-modal').attr('id')=="photo-edit-modal") {
						$('#original_image').attr('src',selectedFile);								
						if ($('#original_image').data('Jcrop')) {
							$('#original_image').data('Jcrop').destroy();
						}
					} else {				
						var edtModal= '<div class="modal fade" id="photo-edit-modal" tabindex="-1" role="dialog" aria-labelledby="photo-edit-Title" aria-hidden="true">'+
		'<div class="modal-dialog modal-lg" role="document">'+
			'<div class="modal-content">'+
					'<div class="modal-header">'+
						'<h5 class="modal-title" id="photo-edit-Title">Resize for profile</h5>'+
						'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
					'</div>'+
					'<div class="modal-body" style="overflow:none;"><img id="original_image" style="width:100%;" src="'+selectedFile+'" /><label>Photo size exceeds the allowed profile photo size. Do you want to crop/resize this photo for your profile</label></div>'+             
					'<div class="modal-footer">'+
						'<button class="btn btn-primary" id="btn-resize">Resize</button>'+	
		'</div></div></div></div>';
						sbt.parents('td').append(edtModal);	
					}
					
					$('#userPhotos').modal('hide');
					$('.modal-backdrop').hide();				
					$('#photo-edit-modal').modal('show');
						
					$('#original_image').on('load', function(){
						
						var img = document.getElementById('original_image'); 
						var iW = img.naturalWidth;
						var iH = img.naturalHeight;

						if (iW>750){
							iH = 750*(iH/iW);
							iW = 750;
						}

						if (iW<iH){
							if (window.innerHeight<window.innerWidth){
								iW *= 0.5;
								iH *= 0.5;
							}
						}

						$(this).Jcrop({
							onChange: setCoordsAndImgSize,
							aspectRatio: 1,
							minSize: [50,50],
							boxHeight : iH,
							boxWidth: iW,
							bgOpacity: 0.25,
							bgColor: 'black',
							borderOpacity: 1,
							handleOpacity: 1,
							setSelect: [50,50,150,150],
							addClass: 'jcrop-normal'
						});

						//alert (iW+':'+iH);

						$('.jcrop-active').css('margin','auto');

					})
						
				} else {
					
					$('.loading-wrapper').show();		
					$('#userPhotos').modal('hide');
					$('.modal-backdrop').hide();				
						
					$(document).find('img').attr('src',pUrl);
					$('.loading-wrapper').hide();		
				}
			},
			error: function (err){
				showMessage(sbt, err.responseText, true);
				$('.loading-wrapper').hide();		
			}
			
		}) 
	})

	//getting album photos
	$(document).on('click','.albumLink',function(){
		var btn = $(this);
		var idParts = btn.attr('id').split('-');
		var albumID = idParts[idParts.length-1]; 
		var table = btn.parents('#phototable');

		getPhotos(albumID,table);
	})

	$(document).on('click','#loadmore',function(e){
		e.preventDefault();
		getPhotos($(this).attr('data-whatever'),$(this).parents('#phototable'));
	})

	function getPhotos(albumID, table){
		
		var data = 'albumID=' + albumID;
		data +='&action=getphotos';
				
		if ($('#loadmore').length !==0){
			data += '&lastDate=' + $('#loadmore').attr('data-date');		
		}

		$.ajax({
			type: "POST", data: data, url: "/get_userphotos.php", success: function (returnHTML){
				if(returnHTML == '')
					returnHTML='<label>No photos found</label>';
				
				if (table.find('.img-thumb').length !== 0){
					$('#loadmore').parent().parent().remove();
				} else {
					table.empty();
				}

				table.append(returnHTML);
			}
		}) 
	}

	function setCoordsAndImgSize(c) {

		imageCropWidth = c.w;
		imageCropHeight = c.h;

		cropPointX = c.x;
		cropPointY = c.y;

		if (c.w <= 10 || c.h <= 10) {
			$("#btn-resize").prop('disabled',true);
			isOk = false;
		}
		else {
			$("#btn-resize").prop('disabled',false);
			isOk = true;
		}
	}

	$(document).on('click','#btn-resize',function(){
		cropImage();	
	})

	function cropImage() {

		if (imageCropWidth == 0 && imageCropHeight == 0) {
			alert("Please, select an area.");
			return;
		}

		var pic = $("#original_image");
		// need to remove these in of case img-element has set width and height
		pic.removeAttr("width");
		pic.removeAttr("height");

		var Fdata = "action=setprofilephoto"+
			"&photo="+$("#original_image").attr("src")+
			"&cX="+cropPointX+
			"&cY="+cropPointY+
			"&iW="+imageCropWidth+
			"&iH="+imageCropHeight;

		if (isOk == true) {
			$.ajax({
				url: '/get_userphotos.php',
				type: 'POST',
				data: Fdata,
				success: function (data) {
					var pUrl = $(data).find('url').text();
					$(document).find('img').attr('src',pUrl);
				},
				error: function (err){
					showMessage('for-document', err.responseText, true);
				},
				fail: function (data) {
					showMessage('for-document', data, true);
				}
			});
		} else { 
			$(this).preventDefault();
			alert("Selected area is not enough!"); 
		}
	}

})