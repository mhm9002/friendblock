$(document).ready( function() {

    if($('#more-stream').size() > 0 && $('#Notifications-content').height()<window.innerHeight)
        windowScrollDownNotif();
    
	//accept follow request
	$(document).on('submit','#accept_frequest',function(){
		$(this).find('.loading-wrapper').show();
		$(this).find('.loading-wrapper').css('z-index','1050');
		var aForm = $(this);
		
		$.ajax({
			url: '/add_follow.php',
            type: 'post',
            data: $(this).serialize(),
            dataType: 'xml',
            success: function (rsp){
					aForm.parents('.activityComment').remove();
					aForm.find('.loading-wrapper').hide();
				},
			error: function (err){
                    aForm.find('.loading-wrapper').hide();
                    alet(err.responseText);
                    showMessage(aForm, err.responseText, true);
                    //hideMessage(aForm, 3);
                    
            	}
		})
		
	})
	
//Show More Stream
    $(window).scroll(function (){
        if($(window).scrollTop() >= $(document).height() - $(window).height() - 10)
            windowScrollDownNotif();
    });

    $('body').on('touchmove',function(){
		if($(window).scrollTop() >= $(document).height() - $(window).height() - 400)
			windowScrollDownNotif();
	});

    function windowScrollDownNotif(){
        
        if($('#more-stream').size() > 0 && $('#more-stream').css('display') == 'none'){
            $('#more-stream').show();
            var data = '';
            var pageType = $('#more-stream').attr('data-page');
            var owner = $('#more-stream').attr('data-owner');
            //Get More Photo
            if(pageType=='notification'){
                //Getting last notification's date
                var lastDate = $('.notification-row:last .created-date').val();
                data = 'lastDate=' + lastDate + '&page=' + pageType;   
            }
            
            $.ajax({
                type: "POST", data: data, url: "/get_data.php", success: function (returnHTML){
                    $('#more-stream').hide();
                    if(returnHTML.trim() == '')
                        $('#more-stream').remove();
                    else{
                        if(pageType=='notification')
                            $('.notification-row:last').after(returnHTML);

                        if($('#Notifications-content').height()<window.innerHeight)
			                windowScrollDownNotif();
                    }
                }
            });
        }
    }

});