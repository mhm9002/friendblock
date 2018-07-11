$(document).ready( function() {
    
	fix_youtube_frames();
	
	function fix_youtube_frames(){	
	//fix youtube dims when document load
		var yt_frame = $(document).find('.youtube_iframe');
		yt_frame.css('width', Number(yt_frame.parents('.tweet-content').width())+'px');	
		yt_frame.css('height', (Number(yt_frame.width())*0.5625) +'px');		
	}
	
	//var uploadedPhoto =0;
    var jcropObj = null;
	
	function create_new_upload_button(form){
		var photoCount = form.find('#photoUpload').children().length/2;
		
		if (photoCount>1 || form.find('#photoUpload').children().first().attr('data-whatever')=='loaded') {
			
			form.find('#photoUpload').children().each(function(){
				
				//if all not loaded, then keep one
				if ($(this).attr('data-whatever') == 'notloaded' && form.find('#photoUpload').children().length>2) {		
					$(this).next().remove();
					$(this).remove();
				}		
			})
			
			var nButton = form.find('#photoUpload').children().last().prev().clone();
			
			var SpanID = nButton.attr("id");
			var nForm = form.find('#file-'+SpanID).clone();
			
			//remove the one you have kept and was notloaded
			if (form.find('#photoUpload').children().last().prev().attr('data-whatever')=='notloaded'){
				form.find('#photoUpload').children().last().prev().remove();
				form.find('#photoUpload').children().last().remove();
			}

			SpanIDi = String(Number(SpanID)+1);
			nButton.attr('id',SpanIDi);
			nButton.css('background-image','url(/images/addphoto.png)');
			nButton.attr('data-whatever', 'notloaded');
			
			nForm.val("");
			nForm.attr("name","file-"+SpanIDi);
			nForm.attr("id","file-"+SpanIDi);
			nForm.attr("data-whatever","");
			
			form.find('#photoUpload').append(nButton);
			form.find('#photoUpload').append(nForm);
		} else {
			form.find('#photoUpload').children().first().css('background-image','url(/images/addphoto.png)');
			form.find('#photoUpload').children().first().find('.loading-wrapper').css('display','none');
			form.find('#photoUpload').children().first().next().val("");				
		}
	}
		
	function readURL(input, no, form) {
		if (input.files && input.files[0]) {
			var reader = new FileReader();
			
			reader.onload = function (e) {
				form.find('#'+ no).css('background-image', 'url(' + e.target.result +')' );	
				upload(no, form);		        	
			}
			reader.readAsDataURL(input.files[0]);
		}
	}

	$(document).on('change','.imgInp',function(){
		var form = $(this).parents('.newpostform');
		readURL(this, $(this).attr('id').substr(-1), form);
	});
	
	$(document).on("mouseenter", ".btn-file", function( event ) {
		if ($(this).attr('data-whatever')=='loaded') {
			$(this).find('.remove-photo').addClass("show");
		}
	});

	$(document).on("mouseleave", ".btn-file", function( event ) {
		$(this).find('.remove-photo').removeClass("show");
	});

	//remove photo
	$(document).on('click','.remove-photo',function(event){
		event.preventDefault();
		
		var con = confirm('This will remove the uploaded photo entriely. Please confirm');

		if (!con)
			exit;

		var img = $(this).parent();
		var form = img.parents('.newpostform');
		
		var id= img.next().attr('data-whatever');

		form.find('#rem1').attr('value',form.find('#rem1').attr('value')+id+";");
		img.next().remove();
		img.remove();
	});

	//select photo file
	$(document).on('click','.btn-file',function(){
		var form =$(this).parents('.newpostform');
		var id = $(this).attr('id'); 
		
		if ($(this).attr('data-whatever')=='loaded'){
			//uploadedPhoto -= 1;
			$(this).attr('data-whatever','loading');
		}
		form.find('#file-'+id).trigger('click');
	});	

	function upload(FileNo, form){
		var main_form = $('#stream');
		var file_form = form.find('#file-'+FileNo);
		var token = form.find('#folder_token').attr('value');
		
		form.find('#'+ FileNo).attr('data-whatever', 'loading');
			//Check the user input their commends
		file_form.blur();
		file_form.prev().find('.loading-wrapper').css('position','relative');
			//show the wrapper and remove-photo icon
		file_form.prev().find('.loading-wrapper').show();
		//hideMessage(form);		
				
		file_form.attr('id','selectedphotofile-MN');
			
		files = document.getElementById('selectedphotofile-MN').files;
				
		file_form.attr('id','file-'+FileNo);
				
		var data = new FormData();
		var error = 0;
				
		var file = files[0];
		console.log(file.size);
		if(!file.type.match('image.*')) {
			showMessage(form, 'Select image file', true);
			error = 1;
		}else if(file.size > 4048576){
			showMessage(form, 'The size of the selected image file is bigger than the maximum limit (4 MB)', true)
			error = 1;
		}else{
			data.append('image', file, file.name);
			data.append('folder', token);
			data.append('pID', FileNo);
		}
				
		if(!error){
			var xhr = new XMLHttpRequest();
			xhr.open('POST', '/photo_uploader.php', true);
			xhr.send(data);
			xhr.onload = function () {
				if (xhr.status === 200) {
					//hide wrapper
					var results = document.createElement("p");
					results.innerHTML = xhr.responseText;
					
					form.find('#'+ FileNo).attr('data-whatever', 'loaded');
					form.find('#rem2').attr('value',form.find('#rem2').attr('value')+file_form.attr('data-whatever')+";");
					file_form.attr('data-whatever', $(results).find('#photo').attr('value'));	
					
					file_form.prev().find('.loading-wrapper').hide();
					create_new_upload_button(form);

					if (isMobile)
						form.find('#'+ FileNo).find('.remove-photo').addClass("show");

					//terminate results
					$(results).remove();
					
				} else {
					form.find('#'+ FileNo).attr('data-whatever', 'notloaded');
					create_new_upload_button(form);
					file_form.prev().find('.loading-wrapper').hide();
				}
			};
		} else {
			form.find('#'+ FileNo).attr('data-whatever', 'notloaded');
			create_new_upload_button(form);
			file_form.prev().find('.loading-wrapper').hide();
		}
	
		return false;
	}

	function prepareTweetData(ntForm){
	
		var postType = ntForm.find('#type').val();
		//ntForm.find('#content').val(ntForm.find('.newPost').text());

		//empty post handling
		if(postType == 'text' && ntForm.find('textarea').val() == ''){
			showMessage(ntForm, 'Please write something hoss.', true);
			return false;
		}
		
		if(postType == 'video' && ntForm.find('#youtube_url').val() == ''){
			showMessage(ntForm, 'Please enter a youtube url.', true);
			return false;
		}
		
		if(postType == 'image'){
			var loaded = false;
	
			ntForm.find('.btn-file').each(function(){
				if ($(this).attr('data-whatever') == 'loading'){
					showMessage(ntForm, 'Wait for image upload.', true);
					loaded = false;	
					return false;
				}
				loaded = true;
			});
	
			if (!loaded)
				return false;
			
			if(ntForm.find('.imgInp').length<2){
				showMessage(ntForm, 'Please upload a photo.', true);
				return false;
			}
		}
	
		//show wrapper
		ntForm.find('.loading-wrapper:last').show();
			
		//preapring data for Ajax
		var dataF = new FormData();
		dataF = ntForm.serialize();
		
		var imageParam = '';
		if(postType == 'image'){
			ntForm.find('.imgInp').each(function(){
				if ($(this).attr('data-whatever')!=null)
					imageParam = imageParam + $(this).attr('data-whatever')+';';
			})
			dataF = dataF + '&images='+imageParam;	
		}
		
		var linkContainer = ntForm.find('#link-container');
		if (linkContainer.css('display')=='block'){
			var metaImage= linkContainer.find('img').attr('src');
			var metaURL = linkContainer.find('a').attr('href');
			var metaTitle = linkContainer.find('a').html();
			var metaDescription = linkContainer.find('td:last').html();
	
			dataF = dataF + '&metaImage='+metaImage+'&metaURL='+metaURL+'&metaTitle='+metaTitle+'&metaDescription='+metaDescription;
		}
	
		if (ntForm.find('textarea').css('direction')=='rtl'){
			dataF = dataF + '&rtl=1';
		} else {
			dataF = dataF + '&rtl=0';
		}
	
		return dataF;
	}
	
    //View More Comments
    $(document).on('click', '.show-more-comments', function (){
        var link = $(this);
        if(link.attr('data-last-date') != ''){
            link.addClass('show-more-comments-loading');

            $.ajax({
                url: '/comments.php',
                data: 'action=get-comments&tweetID=' + link.attr('data-post-id') + '&last=' + link.attr('data-last-date'),
                type: 'post',
                dataType: 'xml',
                success: function (rsp){
                    link.removeClass('show-more-comments-loading');
                    link.parent().after($(rsp).find('comment').text());
                    if($(rsp).find('hasmore').text() == 'yes'){
                        link.attr('data-last-date', $(rsp).find('lastdate').text());
                    }else{
                        link.attr('data-last-date', '').hide();
                    }
                },
                error: function (rsp){
                    link.removeClass('show-more-comments-loading');
                }
            })
        }
        return false;
    })

    //Saving Comment using ajax
    $(document).on('submit', 'form.postcommentform', function (){
        var form = $(this);
        //Check the user input their commends
        if(form.find('input[name="comment"]').val() == '' && form.find('input[name="file"]').size() == 0){
            return false;
        }else{
            form.find('input[name="comment"]').blur();
            form.find('.loading-wrapper').show();
            //hideMessage(form);
			var dataExt = '&action=save-comment';

			if (form.find('input[name="comment"]').css('direction')=='rtl')
				dataExt = dataExt + '&rtl=1';
			
			$.ajax({
                url: '/comments.php',
                type: 'post',
                data: form.serialize() + dataExt,
                dataType: 'xml',
                success: function (rsp){
                    form.find('.loading-wrapper').hide();
                    form.find('.cancel-photo').click();
                    //Add New Comment
					if ($(form).parent().parent().find('.comment-item').length !==0){
						$(form).parent().parent().find('.comment-item:last').after($(rsp).find('newcomment').text());
					} else {
						$(form).parent().after($(rsp).find('newcomment').text());
					}
					//Update the comment count
					$(form).parents('.tweet-content').find('.post-like-comment a:eq(2)').html('<span class="icon icon-pen"></span>&nbsp;' + $(rsp).find('count').text())
                    $(form).find('input[name="comment"]').val('');
                },
                error: function (err){
                    form.find('.loading-wrapper').hide();
                    showMessage(form, err.responseText, true);
                    //hideMessage(form, 3);
                }
            })
        }
        return false;
    })

    //Edit post
    $(document).on('click', '.edit-post', function (){
		var link = $(this);

		//delete prevouse box if found
		$('#tweetBox-'+link.attr('data-whatever')).remove();
		uploadedPhoto=0;

		var dataParam = 'action=get_tweet_edit&tweetID='+link.attr('data-whatever');
		link.html('<img src="/images/loading3.gif" alt="..." />');
		$.ajax({
			url: '/tweet.php', 
			data : dataParam,
			type: 'post', 
			success: function(returnHTML) {
				$('#'+link.attr('data-whatever')+'.tweet-item').append(returnHTML);
				$('#tweetBox-'+link.attr('data-whatever')).modal('show');
				$('#tweetBox-'+link.attr('data-whatever')).find('.editpostform').trigger('click');
				$('#tweetBox-'+link.attr('data-whatever')).find('.newPost').trigger('keyup');
				uploadedPhoto = $('#tweetBox-'+link.attr('data-whatever')).find('.btn-file').length-1;
				link.html('Edit');
			},
			error: function(returnHTML){
				link.html('Edit');
			}
		});
        
        return false;
	})

    //Delete Post
    $(document).on('click', '.remove-post-link', function (){

        if(confirm('Are you sure to delete this post?')){
            var link = $(this);
            link.html('<img src="/images/loading3.gif" alt="..." />')
            $.ajax({
				url: link.attr('href'), 
				type: 'get', 
				success: function (rsp){
                    if(rsp.indexOf('success') !== -1){
                        link.parents('.tweet-item').fadeOut(function (){
                            $(this).remove();
							showMessage('for-document','Tweet deleted','success');
						})
                    }else{
                        link.html('Delete');
                        link.parents('.tweet-content').find('.post-like-comment').before('<p class="message error">' + rsp + '</p>')
                    }
                }
            });
        }
        return false;
    })

    //Delete Comment
    $(document).on('click', '.remove-comment-link', function (){
        var link = $(this);
        link.html('<img src="/images/loading3.gif" alt="..." />')
        $.ajax({
            url: link.attr('href'), type: 'get', dataType: 'xml', success: function (rsp){
                link.parents('.tweet-content').find('.post-like-comment a:eq(2)').html('<span class="icon icon-pen"></span>'+$(rsp).find('commentcount').text());
                link.parents('.comment-item').fadeOut(function (){
                    $(this).remove();
                })
            }, error: function (err){
                link.html('Delete');
                link.after('<p class="message error">' + err.responseText + '</p>');
            }
        })
        return false;
    })

    //Link/Unlike Post
    $(document).on('click', '.like-post-link', function (){
        var link = $(this);
        var oldVal = $(this).html();
        if(link.find('img').size() > 0)
            return false;
        link.html('<img src="/images/loading3.gif" alt="..." />');
        $.ajax({
            url: link.attr('href'), type: 'get', dataType: 'xml', success: function (rsp){
                if($(rsp).find('status').text() == 'success'){
                    link.parents('.tweet-content').find('.post-like-comment a:eq(1)').html($(rsp).find('likes').text());
                    if(oldVal.toLowerCase() == '<span class="icon icon-star-empty"></span>'){
                        link.html('<span class="icon icon-star-full"></span>');
						link.attr('href', link.attr('href').replace('likeTweet', 'unlikeTweet'));
						//showMessage('for-document', 'Tweet liked','success');
                    }else{
                        link.html('<span class="icon icon-star-empty"></span>');
                        link.attr('href', link.attr('href').replace('unlikeTweet', 'likeTweet'));
                    }
                }else{
                    link.html(oldVal);
                    link.parent().parent().after($(rsp).find('message').text());
                }
            }, error: function (err){
                link.html(oldVal);
                link.parent().parent().after('<p class="message error">' + err.responseText + '</p>');
            }
        })
        return false;
    })

	//show likers modal	
	$(document).on('click','.likersCount', function(){
		var btn = $(this);
		var likes = $(this).text();
		
		if (btn.next().hasClass('modal')){
				if (btn.next().find('li').count!=likes){	
					btn.next().modal('show');
					return false;
				}else{
					btn.next().remove;		
				}
		}
			
		if (likes != "0"){
                
        	var ids = $(this).attr('data-target').split('-');
            var tID = ids[1];
                
            $.ajax({
            	url:'/comments.php',
            	data: 'action=get-likers&tweetID='+tID+'&likesCount='+likes,
				type: 'post',
				success: function (returnHTML){
					btn.after(returnHTML);
					btn.next().modal('show');
				}  	
            })	
		}		
	})
	        
	//Retweet
	$(document).on('click','.retweetTweet',function(e){
		e.preventDefault();
		if ($(this).css('color')=='green'){
			alert('You have already retweeted this');
			return false;
		}
		
		var c_msg = confirm('Do you want to Retweet this tweet');
		var tweet = $(this).parents('.tweet-item');
		var param = $(this).attr('data-whatever').split('-');
		var retweetLink = $(this);
		
		var oID = param[0];
		var tID = param[1];
		
		if (c_msg){
			$('.loading-wrapper').show();

			$.ajax({
				url: '/retweet.php',
				type: 'POST',
				data: 'tweetID='+tID+'&ownerID='+oID,
				dataType: 'xml',
				success: function(rsp){
					$('.loading-wrapper').hide();
					
					if (!($(rsp).find('status').text() == 'error')) {
						retweetLink.css('color','green');
						retweetLink.find('label').text(Number(retweetLink.find('label').text())+1);
						showMessage(tweet, $(rsp).find('messages').text(), false);	
					} else {
						showMessage(tweet, $(rsp).find('messages').text(), true);
					}
				},
				error: function(){
					$('.loading-wrapper').hide();
					showMessage(tweet, 'Failed to retweet', true);
				}
			});
		}
	})

	//delete retweet
	$(document).on('click','.remove-retweet',function(e){
		e.preventDefault();
		
		var retweetID = $(this).attr('data-whatever');
		var main_tweet = $(this).parents('.tweet-item');
		
		$.ajax({
			url: '/retweet.php',
			type: 'POST',
			data: 'action=remove-retweet&tweetID='+ retweetID,
			success: function(){
				main_tweet.remove();
				showMessage('for-document','Retweet Removed', false);
			}
		});
	})

    //Show More Stream
    $(window).scroll(function(){
		if($(window).scrollTop() >= $(document).height() - $(window).height() - 10)
			windowScrollDown();
	});
	
	$('body').on('touchmove',function(){
		if($(window).scrollTop() >= $(document).height() - $(window).height() - 400)
			windowScrollDown();
	});
	
	function windowScrollDown(){
        
		if($('#more-stream').size() > 0 && $('#more-stream').css('display') == 'none'){
			$('#more-stream').show();
			var data = '';
			var pageType = $('#more-stream').attr('data-page');
			var owner = $('#more-stream').attr('data-owner');
			//Get More Photo
			if(pageType == 'photo' || pageType == 'page-photo'){
				var lastDate = $('a.photo:last img').attr('data-posted-date');
				data = 'lastDate=' + lastDate + '&page=' + pageType;
				data += '&user=' + $('#more-stream').attr('data-user-id');
				if($('#more-stream').attr('data-album-id') != '')
					data += '&albumID=' + $('#more-stream').attr('data-album-id');

				if(pageType == 'page-photo')
					data += '&pageID=' + $('#more-stream').attr('data-page-id');

			}else if(pageType == 'account' || pageType == 'profile'){
				//Getting last post's posted date
				var lastDate = $('.lastDate:last').val();
				data = 'lastDate=' + lastDate + '&page=' + pageType + '&owner=' + owner;
				if($('#more-stream').attr('data-page') == 'post'){
					data += '&user=' + $('#more-stream').attr('data-user-id') + '&type=' + $('#more-stream').attr('post-type');
				}else {
					if($('#more-stream').attr('data-page') == 'page-post'){
						data += '&pageID=' + $('#more-stream').attr('data-page-id');
					}
				}
			}
			
			$.ajax({
				type: "POST", data: data, url: "/get_data.php", success: function (returnHTML){
					$('#more-stream').hide();
					if(returnHTML == '') {
						$('#more-stream').remove();
					} else {
						if(pageType == 'photo' || pageType == 'page-photo'){
							$('a.photo:last').after(returnHTML);
						} else if (pageType == 'account' || pageType == 'profile'){	
							$('.lastDate:last').after(returnHTML);
							fix_youtube_frames();
						}		
						initCommentFileUpoadButton();
					}
				}
			});
		}
	}

    $(window).resize(function(){
        fix_youtube_frames();
    })

    //New Post Nav
    $(document).on('click','.new-post-nav a',function (){
        var form = $(this).parents('.newpostform');
        
        if(!$(this).hasClass('selected')){
            if($(this).hasClass('post-text')){
                form.find('#new-video-url').hide();
                form.find('.file-row').hide();
                form.find('#save-btn').show();
                form.find('#type').val('text');
            }else
                if($(this).hasClass('post-image')){
                    form.find('#new-video-url').hide();
                    form.find('.file-row').css('display','inline-flex');
                    form.find('.file-row').css('display','-webkit-box');

                    form.find('#type').val('image');
                }else
                    if($(this).hasClass('post-video')){
                        form.find('#new-video-url').show();

                        form.find('.file-row').hide();
                        form.find('#save-btn').show();
                        form.find('#type').val('video');
                    }
            form.find('.new-post-nav a.selected').removeClass('selected');
            $(this).addClass('selected');
        }
        return false;
    })    
	
	//Edit existing post
	$(document).on('submit','.editpostform',function(e){
		e.preventDefault();
		
		var edForm = $(this);
		var tweetID = $(this).find('input[name=tID]').val();
		
		var dataF = new FormData;
		dataF = prepareTweetData(edForm);
	
		if (!dataF)
			return;

    	$.ajax ({
            url: "/manage_post.php",
            data: dataF,
        	type: "POST",
           	success: function (returnHTML){	
				var returnDiv = document.createElement('div');
				returnDiv.innerHTML = returnHTML;
									
				var errors = $(returnDiv).find('.err-div');
				
				if (errors.find('p.error').length !== 0){
					errors.find('p.error').each(function(){
						showMessage('for-document', $(this).text(),'error');
					});
					
				} else {
					var new_tweet_box = $(returnDiv).find('.tweet-item');
					
					$('#tweetBox-'+tweetID).remove();
					$('.modal-backdrop').remove();

					$('#'+tweetID+'.tweet-item').html(new_tweet_box.html());

					fix_youtube_frames();

					errors.children().each(function() {
						if ($(this).hasClass('success')){
							showMessage('for-document',$(this).text(),'success');
						} else {
							showMessage('for-document',$(this).text(),'notification');
						}
					});
				}	

				edForm.find('.loading-wrapper:last').hide();
				$("body").css("overflow","scroll");
			},
			error: function (err){
				showMessage('for-document',err.responseText,'error');
				edForm.find('.loading-wrapper:last').hide();
			}
        });
    
    	return;
	})

    //Add New Post
    $(document).on('submit','.newpostform',function (e){
		e.preventDefault();
        
        var ntForm = $(this);
		if (ntForm.hasClass('editpostform'))
			return;
		
		var dataF = new FormData;
		dataF = prepareTweetData(ntForm);

		if (!dataF)
			return;

    	$.ajax ({
            url: "/manage_post.php",
            data: dataF,
        	type: "POST",
           	success: function (returnHTML){
				
				var returnDiv = document.createElement('div');
				returnDiv.innerHTML = returnHTML;
									
				var errors = $(returnDiv).find('.err-div');
				
				if (errors.find('p.error').length !== 0){
					errors.find('p.error').each(function(){
						showMessage('for-document', $(this).text(),'error');
					});
					
				} else {
					var new_box = $(returnDiv).find('.new-post-row').parent();
					var tweet_box = $(returnDiv).find('.tweet-item');
						
					var tStream = $('#stream');
			
					if (tStream) {
						var div = $('#stream').find('.new-post-row').parent();
						div.after(tweet_box);
						div.after(new_box);				
						div.remove();
						fix_youtube_frames();
						
						//detect if the tweet include URL, and render it
						//render_tweet_URL(tweet_box.attr('id'));

						errors.children().each(function() {
							if ($(this).hasClass('success')){
								showMessage('for-document',$(this).text(),'success');
							} else {
								showMessage('for-document',$(this).text(),'notification');
							}
						});
					}
					
					if (ntForm.parent().parent().attr('id') === 'for-modal'){

						var new_box_div = $('#for-modal');
						var modal_new_box = new_box.clone();
						modal_new_box.attr('id','for-modal');
						new_box_div.after(modal_new_box);				
						new_box_div.parents('.modal').modal('hide');
						new_box_div.remove();	
					}
				}	
				ntForm.find('.loading-wrapper:last').hide();
				//$(part).remove();
			},
			error: function (err){
				showMessage('for-document',err.responseText,'error');
				ntForm.find('.loading-wrapper:last').hide();
			}
        });
    
    	return;
    })

    initCommentFileUpoadButton();
})

function initCommentFileUpoadButton(){

}