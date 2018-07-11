
//social_register/login
/*
function check_social_login(OauthType, OauthEmail, OauthID, param){
    $('#newaccount #loading-wrapper').show();
    
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
            
            $.notify ('Your social login has been registered. Please proceed with new account creation','notification');
            
            $('#newaccount #firstName').val(param.firstName);
            $('#newaccount #lastName').val(param.lastName);
            $('#newaccount #email').val(param.email);
    
            $('#newaccount #firstName').attr('readonly','readonly');
            $('#newaccount #lastName').attr('readonly','readonly');
            $('#newaccount #email').attr('readonly','readonly');
    
            var img = '<input type="hidden" name="img_url" id="img_url" value="'+ param.image +'" />';
            var social_key = '<input type="hidden" name="social-key" id="social-key" value="'+ OauthType +'-'+ id +'" />';
            $('#newaccount #img_url').remove();
            $('#newaccount #social-key').remove();
            $('#newaccount').append(img);
            $('#newaccount').append(social_key);
        } else if($(rsp).find('status').text() == 'IDfound') {
            var mdl = '<div id="proceed-modal" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">'+
            '<button type="button" class="close" data-dismiss="modal">&times;</button><label class="modal-title">Proceed to account</label></div>'+
            '<div class="modal-body">Welcome '+param.firstName+' '+param.lastName+'! <br/>Do you want to proceed to your account page?</div>'+
            '<div class="modal-footer"><form action="/login.php" method="post">'+
            '<input type="hidden" name="login_submit" value="social"/><input type="hidden" name="sID" value="'+id+'" />'+
            '<button type="submit" class="btn btn-primary">Proceed</button></div></form></div></div></div>';
            
            $('#proceed-modal').remove();
            $('#newaccount').parent().after(mdl);
            $('#proceed-modal').modal('show');
    
        }
        
        $('#newaccount #loading-wrapper').hide();
        } 
    });
    
}
*/

(function ($){
    $('#newaccount').submit(function (e){
        
        e.preventDefault();
        
        var isValid = true;
        var form = $(this);
        var filter = /^([a-zA-Z0-9_+\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9])+$/
        if(form.find('#firstName').val() == ''){
            isValid = false;
            form.find('#firstName').addClass('input-error');
        }
        if(form.find('#lastName').val() == ''){
            isValid = false;
            form.find('#lastName').addClass('input-error');
        }
        if(form.find('#username').val() == ''){
            isValid = false;
            form.find('#username').addClass('input-error');
        }
        if(form.find('#email').val() == ''){
            isValid = false;
            form.find('#email').addClass('input-error');
        }
        if(form.find('#password').val() == ''){
            isValid = false;
            form.find('#password').addClass('input-error');
        }
        if(form.find('#password2').val() == ''){
            isValid = false;
            form.find('#password2').addClass('input-error');
        }
        /* Invite Code
         if( form.find('#inviteCode').val() == '' )
         {
         isValid = false;
         form.find('#inviteCode').addClass('input-error');
         }
         */
        if(!isValid){
            
            showMessage(form, 'Please complete the fields in red.', true);
            return false;
        }
        if(!filter.test(form.find('#email').val())){
            isValid = false;
            form.find('#email').addClass('input-error');
            showMessage(form, 'Please enter a valid E-mail address.', true);
            return false;
        }
        if(form.find('#password').val() != form.find('#password2').val()){
            form.find('#password').addClass('input-error');
            showMessage(form, 'The passwords don\'t match.', true);
            return false;
        }

        if(!validatePasswordStrength(form.find('#password').val())){
            showMessage(form, 'The password should be more than 8 characters and include at least 1 number.', true);
            form.find('#password').addClass('input-error');
            return false;
        }
        
        if(!validateUsername(form.find('#username').val())){
            showMessage(form, 'The username should not include spaces or @ characters', true);
            form.find('#username').addClass('input-error');
            return false;
        }


        if(form.find('#agree_terms').prop('checked') == false){
            isValid = false;
            showMessage(form, 'You must accept the Terms and Conditions.', true);
            return false;
        }
        
        //Create an account by ajax
        form.find('.loading-wrapper').show();
        $.ajax({
            url: '/register.php',
            data: form.serialize() + '&action=create-account',
            type: 'post',
            dataType: 'xml',
            success: function (rsp){
                form.find('.loading-wrapper').hide();
                if($(rsp).find('status').text() == 'success'){
                    showMessage(form, $(rsp).find('message').text(), false);
                    form.find('input[type="text"], input[type="password"]').val('');
                    if ($(rsp).find('message').text().indexOf('You will be redirected to your account page')>=0){
                        var keys = form.find('#social-key').text().split('-');
                        var id = keys[1];
                        
                        var sloginForm = '<form id="sLoginForm" action="/login.php" method="post">'+
                        '<input type="hidden" name="login_submit" value="social"/>'+
                        '<input type="hidden" name="sID" value="'+id+'" /></form>';
                        
                        $('#newaccount').parent().after(sloginForm);
                        $('#sLoginForm').trigger('submit');

                    }
                } else {
                    showMessage(form, $(rsp).find('message').text(), true);
                    javascript:Recaptcha.reload();
                    form.find('#recaptcha_response_field').val('');
                }
            },
            error: function (err){
                form.find('.loading-wrapper').hide();
                showMessage(form, err.responseText, true);
            }
        })
        return false;
    })
    
    $(document).ready(function (){
        $('.goto-forgotpwdform').click(function (){
            $('#loginform').fadeOut('fast', function (){
                $('#forgotpwdform').fadeIn();
            });
            return false;
        });
        $('.goto-loginform').click(function (){
            $('#forgotpwdform').fadeOut('fast', function (){
                $('#loginform').fadeIn();
            });

            return false;
        });

        //Reset Password Form
        $('#resetpwdform').submit(function (){
            var form = $(this);
            var isValid = true;

            if(form.find('#password').val() == ''){
                form.find('#password').addClass('input-error');
                isValid = false;
            }
            if(form.find('#password2').val() == ''){
                form.find('#password2').addClass('input-error');
                isValid = false;
            }
            if(!isValid){
                showMessage(form, 'Please enter your new password.', true);
            }
            if(isValid && form.find('#password').val() != form.find('#password2').val()){
                form.find('#password').addClass('input-error');
                isValid = false;
                showMessage(form, 'New password doesn\'t match.', true);
            }
            return isValid;
        })

        $(document).on('click','#fb_reg',function(){
            checkLoginState();
        })

        function checkLoginState() {
            FB.getLoginStatus(function(response) {
              StatusChangeCallback(response);
              alert(response.status);
            });
        }

        //function fb_statusChangeCallback(response){

        //}

        $(document).on('keyup','#username',function(){
            var field= $(this);
            var txt = field.val();

            $('.notifyjs-arrow').remove();
            $('.notifyjs-container').remove();

            if (txt == ""){
                return false;
            }
                
            if (validateUsername(txt)===false){
                field.notify("Username should not include @ or white spaces");
                return false;
            }

            $.ajax({
                type: "POST", 
                data: "username="+txt+"&action=checkUserName", 
                url: "/check_reg_form.php", 
                success: function (returnHTML){
                    if (returnHTML.indexOf('valid')>=0){
                        field.notify(returnHTML,'success');
                    } else {
                        field.notify(returnHTML,'error');
                    }
                    
                }
            });

        })


        $(document).on('keyup','#newaccount #email',function(){
            var field= $(this);
            var txt = field.val();

            $('.notifyjs-arrow').remove();
            $('.notifyjs-container').remove();

            if (txt == ""){
                return false;
            }
         
            $.ajax({
                type: "POST", 
                data: "email="+txt+"&action=checkEmail", 
                url: "/check_reg_form.php", 
                success: function (returnHTML){
                    if (returnHTML.indexOf('is valid')>=0){
                        field.notify(returnHTML,'success');
                    } else {
                        field.notify(returnHTML,'error');
                    }
                    
                }
            });

        })

        
        $(document).on('keyup','#newaccount #password',function(){
            var field= $(this);
            var txt = field.val();

            $('.notifyjs-arrow').remove();
            $('.notifyjs-container').remove();

            if (txt == ""){
                return false;
            }
         
            if (validatePasswordStrength(txt)===false){
                field.notify("The entered password doesn't meet the required criteria. Please make sure to use letter and numbers and to have 8 characters minimum","error");
            }
            
        })

        $(document).on('keyup','#newaccount #password2',function(){
            var field= $(this);
            var txt = field.val();

            $('.notifyjs-arrow').remove();
            $('.notifyjs-container').remove();

            if (txt == ""){
                return false;
            }
         
            if (txt != $('#newaccount #password').val()){
                field.notify("The entered password doesn't match password in the previous field","error");
            }
            
        })



    })


})(jQuery)