//social_register/login
/*
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
                '<button type="button" class="close" data-dismiss="modal">&times;</button><label class="modal-title">Create new account</label></div>'+
                '<div class="modal-body">Welcome '+param.firstName+' '+param.lastName+'! <br/>You do not have Friendblock.net account. Do you want to register? Proceed to register page</div>'+
                '<div class="modal-footer"><a class="btn btn-primary" href="/register.php" class="headerLinks">Register</a></div></div></div></div>';
                
                $('#proceed-modal').remove();
                $('#main_home_section').append(mdl);
                $('#proceed-modal').modal('show');
        
            } else if($(rsp).find('status').text() == 'IDfound') {
                var mdl = '<div id="proceed-modal" class="modal fade" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">'+
                '<button type="button" class="close" data-dismiss="modal">&times;</button><label class="modal-title">Proceed to account</label></div>'+
                '<div class="modal-body">Welcome '+param.firstName+' '+param.lastName+'! <br/>Do you want to proceed to your account page?</div>'+
                '<div class="modal-footer"><form action="/login.php" method="post">'+
                '<input type="hidden" name="login_submit" value="social"/><input type="hidden" name="sID" value="'+id+'" />'+
                '<button type="submit" class="btn btn-primary">Proceed</button></div></form></div></div></div>';
                
                $('#proceed-modal').remove();
                $('#main_home_section').append(mdl);
                $('#proceed-modal').modal('show');
        
            }
            
        } 
    });
    
}
*/