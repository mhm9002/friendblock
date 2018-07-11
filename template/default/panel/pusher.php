<?php
/**
 * Include Pusher Code
 */
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

$userID = cr_is_logged_in();

?>

<input type="hidden" id="uForPusher" value="<?php echo $userID['id'] ?>"></input> 

<script src="https://js.pusher.com/4.1/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher(PUSHER_AUTH, {
      cluster: 'ap2',
      encrypted: true
    });

	var channelID = $(document).find('#uForPusher').attr('value');
	
	//console.log(channelID);
	
  var channel = pusher.subscribe(channelID);
  channel.bind('notifications', function(data) {	
      showMessage('for-document',data.message,false);
      
      var notfCount =  Number($('.notification-count').Text());
      notfCount = notfCount + 1;
      $('.notification-count').Text(notfCount);    
  });
  </script>
