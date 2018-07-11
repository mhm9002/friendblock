function showMessage(form, message, error){
	var type = 'success';
    
    if (error===true){
		type = 'error';
    }
    
    if (message.indexOf('<p class=') !== -1){
        message = $(message).text();
    }
        
    if (isMobile()){    
            $.notify(message,{style: "mobile", className: type, position: "top center"});
    } else {
        if (form == 'for-document'){
            $.notify(message,type);
        } else {
            $(form).notify(message, type);
        }
    }
}

function getMessages(){

    $.ajax({
        url:'/get_messages.php',
        type: 'post',
        data: 'action=getMessages',
        success: function(rsp){
            $(rsp).find('.message').each (function(){
                showMessage("for-document",element.text(),element.hasClass('error')?true:false);
            })
        }
    })
}

function generateRandomColor(hue, options){
    //defaults
    var lvlStart =30;
    var lvlDelta =35;
    
    var satStart = 30;
    var satDelta = 70;
    
    var hueVar = 10;
    
    //options
    switch (options){
      case "light":
          lvlStart = 60;
          lvlDelta = 25;
          break;  
      case "dark":
          lvlStart = 20;
          lvlDelta = 25;
          break;
      case "desat":
          satStart = 5;
          satDelta = 15;
          break;
      case "sat":
          satStart = 85;
          satDelta = 15;
          break;
      case "varies":
          hueVar = 120;
          break;
    }
    
    //generate random values
    var lvl = lvlStart + (Math.floor(Math.random()*lvlDelta));
    var sat = satStart + (Math.floor(Math.random()*satDelta));
    var rndHue = hue + (Math.floor(Math.random()*hueVar*2)-hueVar);
    
    return 'hsla('+rndHue+','+sat+'%,'+lvl+'%,1)';

}
 
function isMobile() {
    var Uagent = navigator.userAgent||navigator.vendor||window.opera;
      return(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(Uagent)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(Uagent.substr(0,4))); 
  };

function validatePasswordStrength(password){
    if(password.length < 8 || !password.match(/[0-9]+/)){
        return false;
    }
    return true;
}

function validateUsername(username){
    if (username.match(/\s/) || username.match(/@/))
        return false;

    if (username=="")
        return false;

    return true; //good user input
}

(function ($){
    $(document).ready(function (){
        
        if (isMobile()){
            $.notify.addStyle('mobile', {
                html: "<div><span data-notify-text/></div>",
                classes: {
                  base: {
                    "white-space": "pre-wrap",
                    "background-color": "lightblue",
                    "padding": "30px",
                    "font-size": "30px",
                    "font-weight":"bold",
                    "color": "white",
                    "margin-top": "220px",
                    "opacity": "0.7"
                  },
                  success: {
                    "background-color": "green" 
                  },
                  error: {
                    "background-color": "red" 
                  },
                  notification: {
                    "background-color": "yellow" 
                  }
                }
              });
        }

        getMessages();
        //colorize();

        //Frame Buster
        if(top != self){
            document.write('<p>You are viewing this page in a unauthorized frame window</p>');
            return;
        }
		
        function colorize() {
    
            var hue = Math.floor(Math.random()*360); 

            document.documentElement.style.setProperty('--primaryColor',    generateRandomColor(hue,"sat"));
            document.documentElement.style.setProperty('--secondaryColor',  generateRandomColor(hue,"light") );
            document.documentElement.style.setProperty('--inputColor',      generateRandomColor(hue,"light"));
            document.documentElement.style.setProperty('--secondaryColorLight', generateRandomColor(hue,"light"));
            document.documentElement.style.setProperty('--bootstrap1',      generateRandomColor(hue,"sat"));
            document.documentElement.style.setProperty('--bootstrap2',      generateRandomColor(hue,"sat"));
            document.documentElement.style.setProperty('--bootstrap3',      generateRandomColor(hue,"light"));
            document.documentElement.style.setProperty('--bootstrap5',      generateRandomColor(hue,"light"));
            document.documentElement.style.setProperty('--shadowColor',     generateRandomColor(hue,"light"));
            
            //console.log($(':root').css('--primaryColor'));
        }

        $(document).on('focus','.newcomment', function(){
			var commentbox = $(this);
			commentbox.css('height','60px');
			return true;
		})

		$(document).on('blur','.newcomment', function(){
			var commentbox = $(this);
			if (commentbox.val() == ""){	
				commentbox.css('height','30px');
			}
			return true;		
		})
		
        $('.input').focus(function (){
            $(this).removeClass('input-error');
        })
        $('.select').change(function (){
            $(this).parent().find('select').removeClass('select-error');
        })
        $('.textarea').focus(function (){
            $(this).removeClass('input-error');
        })

        if($('#footer_menu').size() > 0){
            //Footer menu
            $('#footer_menu').on('mouseover', 'li.has-submenu', function (){
                $(this).addClass('hover');
            });
            $('#footer_menu').on('mouseout', 'li.has-submenu', function (){
                $(this).removeClass('hover');
            });


            $('#footer_menu').click(function (e){
                e.stopPropagation();
            })
            $(window).click(function (){
                $('#footer_menu li.hover').removeClass('hover');
            })
        }
        
        $('.table #chk_all').click(function (){
            if(this.checked)
                $(this).parents('.table').find('.tr .td-chk input[type="checkbox"]').prop('checked', true);else
                $(this).parents('.table').find('.tr .td-chk input[type="checkbox"]').prop('checked', false);
        })
        $('table #chk_all').click(function (){
            if(this.checked)
                $(this).parents('table').find('.td-chk input[type="checkbox"]').prop('checked', true);else
                $(this).parents('table').find('.td-chk input[type="checkbox"]').prop('checked', false);
        })

        if($('#forum-left-bar').size() > 0 && $('#forum-left-bar').height() > $('#forum-content-wrapper').height()){
            $('#forum-content-wrapper').height($('#forum-left-bar').height());
        }

    })

    //Process Ajax Actions
    $('body').on('click', 'a[data-type="buckys-ajax-link"]', function (){
        var oLink = $(this);
        var oldText = oLink.html();

        oLink.html('<img src="/images/loading3.gif" />');

        $.ajax({
            type: 'get', url: oLink.attr('href') + '&buckys_ajax=1', dataType: 'xml', success: function (rsp){
                if($(rsp).find('status').text() == 'success'){
                    oLink.html($(rsp).find('html').text());
                    oLink.attr('href', $(rsp).find('link').text());

                    //Process Extra Action
                    if(typeof (buckys_ajax_action_success) != 'undefined'){
                        buckys_ajax_action_success(oLink);
                    }else{
                        if($(rsp).find('action').text()){
                            switch($(rsp).find('action').text()){
                                case 'send-friend-request':
                                case 'delete-friend-request':
                                case 'unfriend':
                                    break;
                                case 'accept-friend-request':
                                case 'decline-friend-request':
                                    oLink.parent().find('a, br:gt(0)').not(oLink).fadeOut('fast', function (){
                                        $(this).remove()
                                    });
                                    break;
                            }
                        }
                    }
                }else{
                    oLink.html(oldText);
                    alert($(rsp).find('message').text());
                }
            }, error: function (err){
                alert(err.responseText);
            }
        })

        return false;
    })

    jQuery.fn.selectText = function (){
        var doc = document;
        var element = this[0];

        if(doc.body.createTextRange){
            var range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
        }else
            if(window.getSelection){
                var selection = window.getSelection();
                var range = document.createRange();
                range.selectNodeContents(element);
                selection.removeAllRanges();
                selection.addRange(range);
            }
    };

    $(".onclick-select-all").click(function (){
        $(this).selectText();
    });

    $(document).ready(function (){
        if(window.location.hash == '#bottom'){
            window.scrollTo(0, document.body.scrollHeight);
        }
    });

	$('.tab').click(function (){
        //if(!$(this).hasClass('active')){
        	mTab = $(this).parent();
        	selected = $(this).attr('data-whatever');
        	
        	mTab.children().each (function(){
        		$(this).removeClass('active');
        		$($(this).attr('data-whatever')).removeClass('show');
        	})
        	
        	$(selected).addClass('show');        
            $(this).addClass('active');
        //}
        return false;
    })    

    /*
	$('#search-bar').on('keyup',function(){
		var sBar = $(this);
		var searchText =$(this).val();
		var coord = $(this).position();

		$('.search-results').remove();

		$.ajax({
			url: '/search.php', 
			type: 'post', 
			data: {'searchText': searchText},
			success: function (returnHTML){

				var x = returnHTML.search('searchIcons'); 
								
				if (x>-1){
					var searchBox = '<div class="search-results" style="position: absolute; top:'+(coord.top+50)+'px; left:'+(coord.left+20)+'px; display:block; background-color:#fff; box-shadow: 0 0 5px #888; z-index: 1050;">'+returnHTML+'</div>';
				
					sBar.after(searchBox);
				}
			},
			error: function(err){
				console.log(err);
			}
		})		
	})
    */
    /*
    $('#search-bar').on('keyup',function(){
        $('#search-bar').suggest('m',{
            data: function(request, response) {
                if (request.length<1)
                    return;
                    
                $.ajax({
                    type: "post",
                    url: "/search.php",
                    data: { 'searchText': request},
                    dataType: "json",
                    success: function(data){
                        response(data);
                    }
                });
            },
            map: function(data){
                return {
                    value: data.label,
                    text: '<a href="' + data.value + '" ><img src="' + data.thumb + '" class="searchIcons" />&nbsp;' + data.label + '&nbsp;<span style="color: #888; font-size: 12px;">'+ ((data.username==null)?data.count+' times used':'@'+data.username)+'</span></a>'
                }
            }
        });
    })
    */
    
    $('#search-bar').autocomplete({
        appendTo: this, source: function (request, response){
            $.ajax({
                url: '/search.php',
                data: {'searchText': request.term},
                dataType: 'json',
                type: 'post',
                success: response
            })  
        }, search: function (){
            // custom minLength
            var term = this.value;
            if(term.length < 1){
                return false;
            }
        }, focus: function (){
            // prevent value inserted on focus
            return false;
        }, select: function (event, ui){
            return false;
        }, close: function (){
            //$('#search-bar').val('');
            return false;
        }
    }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        
        var inner_html = '<a href="' + item.value + '" ><img src="' + item.thumb + '" class="searchIcons" />&nbsp;' + item.label + '&nbsp;<span class="search-autocomplete-tag">'+ ((item.username==null)?item.count+' times used':'@'+item.username)+'</span></a>';
        return $( "<li></li>" )
            .data( "item.autocomplete", item )
            .append(inner_html)
            .appendTo( ul );
    };
    
    /*
	$('#search-bar').on('blur',function(){
		$('#search-results').remove();
	})

	$('#search-bar').on('focus',function(){
		$(this).trigger('keyup');
	})
*/	
	//show tweet in modal	
	$(document).on('click','.tweetPage', function(e){
		e.preventDefault();
		
		var btn = $(this);
		var param = $(this).attr('data-whatever').split('-');
		
		$(document).find('#'+btn.attr('data-whatever')).remove();
		
		var uID = param[0];
		var tID = param[1];
		
		$.ajax({
			url: '/tweet.php',
			data: 'action=get_single_tweet&userID='+uID+'&tweetID='+tID,
			type: 'post',
			success: function(returnHTML) {
				btn.parent().after(returnHTML);
				
				$(document).find('#'+btn.attr('data-whatever')).modal('show');				
			}
			
		})
		
	})
	
	$(document).on('click', '.newpostform', function(){
		var tbox = $(this).find(".newPost");
		tbox.css('height','90px');
		$(this).find('.new-post-nav').show();
		return true;
    })
    
    //thumbs mouse over
    $(document).on('mouseover','a.thumb', function(){
        var thumb = $(this);
        var link= thumb.attr('href');
        var param = link.split("?");
        var user = param[1];
        var pos = thumb.offset();
        
        show_links_panel(user,pos);
    })

    $(document).on('mouseout','a.thumb', function(){
        setTimeout(function (){
            $(document).find('.user-info').remove();
        }, 500);
    })

    $(document).on('mouseover','a.tweet-thumb', function(){
        
        var thumb = $(this);
        var link= thumb.attr('href');
        var param = link.split("?");
        var user = param[1];
        var pos = thumb.offset();
        
        show_links_panel(user,pos);
    })

    $(document).on('mouseout','a.tweet-thumb', function(){
        setTimeout(function (){
            $(document).find('.user-info').remove();
        }, 500);
    })

    //related to mouse over thumbs
    function show_links_panel(user, pos){
        $(document).find('.user-info').remove();
        
        $.ajax({
            url: '/get_userinfo.php',
            type: 'post',
            data: user,
            dataType: 'xml',
            success: function (rsp){
                if ($(rsp).find('status').text()=='success'){
                    $('#right_side').after($(rsp).find('content').text());
                    var info = $('#right_side').next(); 
                    
                    console.log(pos.top);
                    console.log(pos.left);
                    pos.top = pos.top + 50;
                    pos.left = pos.left + 10;
        
                    info.css('top', pos.top);
                    info.css('left',pos.left);
                }
            },
            error: function (err){
                showMessage('for-document', err.responseText, true);
            }
        })
    }

    //for mobile menu ; toggling
    $(document).on('click','#profile-menu',function(e){
        e.preventDefault();
        var menu = $(this).attr('data-menu');
        var wdth = $(document).width();

        if ($('#'+menu).css('display')=='none') {
            $('#'+menu).css('height', $(window).height());
            $('#'+menu).css('left',parseInt(wdth));
            $('#'+menu).css('display','block');

            $('#'+menu).animate({'left':0},500,function(){});
            
        } else {
            $('#'+menu).css('left',0);
            $('#'+menu).animate({'left':parseInt(wdth)},500,function(){
                $('#'+menu).css('display','none');
            });
        }
    })

    //search toggle
    $(document).on('click','#mobile-search-link', function (e){
        e.preventDefault();
        var form = $(this).parent();
        var li = form.parent();
        var ul = li.parent();
        if(ul.hasClass('mobile-menu-grid2')==false){
            
            //form.find('.mobile-search').css('display','inline-block');
            ul.addClass('mobile-menu-grid2');
            li.prev().css('display', 'none');
            form.find('input').focus();
            
        } else {
            
            li.prev().css('display', 'block');
            
            ul.removeClass('mobile-menu-grid2');
            //form.find('.mobile-search').css('display','none');
        }
    })

    //toggle menu on touch
    var x_loc = 0;
    var deltaX = 0;

    $('body').on('touchstart',function(e){
        x_loc = e.originalEvent.touches[0].pageX;
    })

    $('body').on('touchmove',function(e){
        deltaX = e.originalEvent.touches[0].pageX - x_loc;
    })

    $('body').on('touchend',function(e){
        
	    var limit = $(window).width() * 0.6;
        
        if (Math.abs(deltaX) > limit) {	
	        if ((deltaX < 0 && $('#accountMenu').css('display')=='none')||(deltaX > 0 && $('#accountMenu').css('display')=='block'))
                $('#profile-menu').trigger('click');
        } 
        
        x_loc = 0;
        deltaX = 0;
    
    })

    //detect URL input in tweet
    var link="";

    $(document).on('keyup','.newPost',function(){
        var postBox = $(this);
        var text=$(this).val();

        //detect URL entry
        
        var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]) /ig;

        if (!text.match(urlRegex)){
            postBox.parent().find('#link-container').empty();
            postBox.parent().find('#link-container').css('display','none');            
        } else {

            //check if the link changed
            var currentLink="";
            currentLink = text.match(urlRegex);
            currentLink = currentLink.toString();

            var notchanged = (currentLink==link)?true:false;
            if (notchanged && link.length>0)
                return;
            
            postBox.parent().find('#link-container').empty();
            postBox.parent().find('#link-container').css('display','none');

            link = currentLink;
            var xLink = link;

            $.get('https://cors-anywhere.herokuapp.com/'+xLink, function(data) {

                //use the async nature of the function to compare the used xLink with global link if updated
                if (xLink.indexOf(link.trim())==-1)
                    return;

                data= $(data.trim());    
        
                var desc_meta= data.filter('meta[property="og:description"]'); 
            
                if (!desc_meta)
                    desc_meta = data.filter('meta[name="Description"]');    
                
                desc = desc_meta.attr("content");
                
                var ttl= data.filter('meta[property="og:title"]').attr("content");
                
                if (!ttl)
                    ttl = data.filter('title').text();    
        
                var img = data.filter('meta[property="og:image"]').attr("content");
        
                if (!img)
                    img = data.find('img:first').attr("src");
        
                if (ttl || desc){
                    var html="";
                    if (img){

                        html='<table><tr><td rowspan="2"><img width="150" src="'+img+'" /></td>';
                        html+='<td><a href="'+link+'">'+ttl+'</a></td></tr><tr><td>'+desc+'</td></tr></table>';
                    } else {
                        html='<table><tr><td><a href="'+link+'">'+ttl+'</a></td></tr><tr><td>'+desc+'</td></tr></table>';
                    }   
                    postBox.parent().find('#link-container').html(html);
                    postBox.parent().find('#link-container').css('display','block');
                }  
            });
        }
            
        postBox.suggest('@',{
            data: function(request, response) {
                if (request.length<1)
                    return;
                    
                $.ajax({
                    type: "post",
                    url: "/search.php",
                    data: { 'searchText': request, 'flag':'1'},
                    dataType: "json",
                    success: function(data){
                        response(data);
                    }
                });
            },
            map: function(user){
                return {
                    value: user.username,
                    text: '<img src="' + user.thumb + '" class="searchIcons" />&nbsp;<b>'+user.label+'</b>&nbsp;'+user.username
                }
            }
        });
        
        postBox.suggest('#',{
            data: function(request, response) {
                if (request.length<1)
                    return;
                    
                $.ajax({
                    type: "post",
                    url: "/search.php",
                    data: { 'searchText': request, 'flag':'2'},
                    dataType: "json",
                    success: function(data){
                        response(data);
                    }
                });
            },
            map: function(tag){
                return {
                    value: tag.label,
                    text: '<b>'+tag.label+'</b>'
                }
            }
        });
        

        //detect person entry
        /*
        var urlRegex =/@([a-zA-Z0-9])+([a-zA-Z0-9]+)/ig;

        if (text.match(urlRegex)){
            //alert('hi');
            postBox.autocomplete({
                appendTo: this, source: function (request, response){
                    var trm = request.term.match(urlRegex).toString();
                    trm = trm.substr(1);
                    $.ajax({
                        url: '/search_people.php',
                        data: {'searchText': trm},
                        dataType: 'json',
                        type: 'post',
                        success: response
                    })  
                    
                }, search: function (){
                    // custom minLength
                    var term = this.value;
                    if(term.length < 2){
                        return false;
                    }
                }, focus: function (){
                    // prevent value inserted on focus
                    return false;
                }, select: function (event, ui){
                    this.val(this.val + ui.item.label);
                    return false;
                }, close: function (){
                    //$('#search-bar').val('');
                    return false;
                }
            }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
                var inner_html = '<img src="' + item.thumb + '" class="searchIcons" />&nbsp;' + item.label + '&nbsp;<span style="color: #888; font-size: 12px;">@'+item.username+'</span>';
                return $( "<li></li>" )
                    .data( "item.autocomplete", item )
                    .append(inner_html)
                    .appendTo( ul );
            
            };
            
        }
        */

    })

    /*             
    $('.newPost').tagging({
        source: function(request, response) {
            var urlRegex =/@([a-zA-Z0-9])+([a-zA-Z0-9]+)/ig;
            var trm = request.term.match(urlRegex).toString();
            trm = trm.substr(1);
            
            $.ajax({
                type: "post",
                url: "/search_people.php",
                data: { 'searchText': trm },
                dataType: "json",
                success: function(data){
                    response(data);
                }
            });
        }
    });
    */

})(jQuery)