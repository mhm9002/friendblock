//fb
 
window.fbAsyncInit = function() {
    FB.init({
        appId      : 'facebook app id', 
        cookie     : true,
        xfbml      : true,
        version    : 'v2.11'
    });
        
    FB.AppEvents.logPageView();   
        
};

(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));


  // This is called with the results from from FB.getLoginStatus().
  function statusChangeCallback(response) {
    //$('#full-wrapper').show();
    console.log('statusChangeCallback');
    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if (response.status === 'connected') {
      // Logged into your app and Facebook.
      processFBLoginData(); //response.authResponse.userID
    } else {
      // The person is not logged into your app or we are unable to tell.
      document.getElementById('status').innerHTML = 'Please log into this app.';
      //$('#full-wrapper').hide();
    }
  }

  // This function is called when someone finishes with the Login
  // Button.  See the onlogin handler attached to it in the sample
  // code below.
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    }, {scope:'public_profile,email'});
  }

  // Now that we've initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ('connected')
  // 2. Logged into Facebook, but not your app ('not_authorized')
  // 3. Not logged into Facebook and can't tell if they are logged into
  //    your app or not.
  //

  // Here we run a very simple test of the Graph API after login is
  // successful.  See statusChangeCallback() for when this call is made.
  function processFBLoginData() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', {fields: 'id, first_name, last_name, email, picture'}, function(response) {
         
      var param =  {firstName:response.first_name, lastName:response.last_name, email:response.email, image:response.picture.data.url};
        
      check_social_login ('fb', response.email, response.id, param);
      //$('#full-wrapper').hide();
    });
  }

  $(document).on('click','#fb-login', function() {
    //do the login
    FB.login(checkLoginState, {scope: 'email,public_profile', return_scopes: true});
  });

//google

var googleUser = {};

$(document).ready(function(){
  gapi.load('auth2', function(){
    // Retrieve the singleton for the GoogleAuth library and set up the client.
    auth2 = gapi.auth2.init({
      client_id: 'google app id',
      cookiepolicy: 'single_host_origin',
      // Request scopes in addition to 'profile' and 'email'
      scope: 'profile email'
    });
    attachSignin(document.getElementById('g-login'));
  });
});

function attachSignin(element) {
  console.log(element.id);
  //$('#full-wrapper').hide();
  auth2.attachClickHandler(element, {},
      function(googleUser) {
        var profile = googleUser.getBasicProfile();
        var param =  {firstName:profile.getGivenName(), lastName:profile.getFamilyName(), email:profile.getEmail(), image:profile.getImageUrl()};
        //$('#full-wrapper').show();
        check_social_login ('g', profile.getEmail(), profile.getId(), param); 
        //$('#full-wrapper').hide();
      }, function(error) {
        alert(JSON.stringify(error, undefined, 2));
        //$('#full-wrapper').hide();
      });
}

//social_register/login
function check_social_login(OauthType, OauthEmail, OauthID, param){
  var currentPage = window.location.href;

  var returnURL = (currentPage.indexOf('myprofile.php'))?"/myprofile.php":"";
  
  var mdl ='<form action="/check_reg_form.php" method="post" id="form1" style="display:none;">'+
    '<input type="hidden" name="action" value="checkSLogin"/>'+
    '<input type="hidden" name="type" value="'+OauthType+'" />'+
    '<input type="hidden" name="email" value="'+OauthEmail+'" />'+
    '<input type="hidden" name="OauthID" value="'+OauthID+'" />'+
    '<input type="hidden" name="fname" value="'+param.firstName+'" />'+
    '<input type="hidden" name="lname" value="'+param.lastName+'" />'+
    '<input type="hidden" name="image" value="'+param.image+'" />'+
    '<input type="hidden" name="return" value="'+returnURL+'" />'+
    '<button type="submit">Proceed</button></form>';
                
  $('#form1').remove();
  $('body').append(mdl);
  $('#form1').trigger('submit');
}