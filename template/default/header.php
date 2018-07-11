<?php
/**
 * Header.php
 */

//Navbar

$userID = cr_is_logged_in();

//not mobile site
if(!cr_is_mobile()){

?>
<div id="mainmnu" class="navbar navbar-expand bg-light flex-column sticky-top flex-md-row bd-navbar" style="padding-left: 5%; padding-right: 5%;">
	<label class="navbar-brand"><span class="icon icon-quill brand"></span></label>	
	<?php if($userID){ ?>
	<div class="navbar-nav-scroll" id="navbarNav">
		<ul class="navbar-nav bd-navbar-nav flex-row">
			<li class="nav-item active">
				<a class="nav-link" href="/account.php" id="homemnu"><span class="icon icon-home3"></span>&nbsp;Home</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/notifications.php" id="notifmnu"><span class="icon icon-bell"></span>&nbsp;Notifications <div class="notification-count"><?php echo crActivity::getNumberOfNotifications($SITE_GLOBALS['user']['id']) ?></div></a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/messages.php" id="msgmnu"><span class="icon icon-drawer"></span>&nbsp;Messages</a>
			</li>
		</ul>
	</div>
	<ul class="navbar-nav flex-row ml-md-auto d-none d-md-flex">
		<form class="form-inline" id="search" style="padding-top: 0px; padding-right: 10px;" method="get" action="/search.php">
			<input class="form-control mr-sm-2" type="search" name="searchText" placeholder="Search" aria-label="Search" id="search-bar" autocomplete="off" style="border-radius: 15px; height: 30px;"></input>
		</form>
		<li class="nav-item dropdown" style="padding-right: 10px;">
			<a class="nav-link" href="#" id="AccountMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<?php if(!$SITE_GLOBALS['user']['thumbnail']){ ?>
            <img style="height: 30px !important; width: 30px !important; border-radius: 50%;" src="<?php echo DIR_IMG . '/defaultProfileImage.png' ?>"/>
        	<?php }else{ ?>
            <img style="height: 30px !important; width: 30px !important; border-radius: 50%;" src="<?php echo crUser::getProfileIcon($SITE_GLOBALS['user']['id']) ?>"/>
        	<?php } ?>
        	</a>
			
			<div class="dropdown-menu" aria-labelledby="AccountMenu">
          		<a class="dropdown-item" href="/profile.php?userid=<?php echo $SITE_GLOBALS['user']['id'] ?>">Profile</a>
          		<a class="dropdown-item" href="/manage_albums.php">Photo Albums</a>
				<a class="dropdown-item" href="/myprofile.php">Settings</a>
          		<a class="dropdown-item" href="/logout.php">Logout</a>
        	</div>
		</li>
		<li class="nav-item">
			<?php render_tweet_box(); ?>
			<button class="btn btn-primary" style="height: 34px; margin-top: 5px;" type="button" data-toggle="modal" data-target="#tweetBox" data-whatever="@<?php echo $SITE_GLOBALS['user']['username']?>"><span class="icon icon-quill"> Tweet</span></button>	
			
		</li>
	</ul>
		<?php } else { ?>
	<div class="navbar-nav-scroll" id="navbarNav">
		<ul class="navbar-nav bd-navbar-nav flex-row">
			<li class="nav-item active">
				<a class="nav-link" href="/index.php" id="homemnu"><span class="icon icon-home3"></span>&nbsp;Home</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="#" id="aboutmnu"><span class="icon icon-question"></span>&nbsp;About</a>
			</li>
		</ul>
	</div>
	<ul class="navbar-nav flex-row ml-md-auto d-none d-md-flex">
		<form class="form-inline" id="login" style="padding-right: 10px;" action="/login.php" method="post">
      		<input class="form-control mr-md-2" style="width: 8em;" type="email" required="true" placeholder="Email" name="email" id="email"/>
    		<input class="form-control mr-md-2" style="width: 8em;" type="password" required="true" placeholder="Password" name="password" id="password"/>
      		<button type="submit" class="btn btn-primary" name="login_submit">Log in</button>
    	</form>
    	
    </ul>
		
	<?php } ?>
</div>

<?php

}else{
//mobile site

?>
<div id="mainmnu" class="navbar navbar-expand bg-light sticky-top bd-navbar" style="height: 200px; padding-left: 5%; padding-right: 5%;">
		
	
		<ul class="navbar-nav bd-navbar-nav mobile-menu-grid1">
			<!--main menu!-->
			<li class="mobile nav-item" style="overflow: hidden;font-size: 45px; grid-column-start: 1; grid-column-end: 1;">
					<span class="icon icon-quill brand"></span>&nbsp;Friendblock.net
			</li>
		
	
			<?php if($userID) { ?>
		
			<li class="nav-item" style="grid-column-start: 2; grid-column-end; 2;">
				<form class="nav-item" id="search" style="overflow: hidden; height: 60px; text-align: center; margin-top: 25px;" method="get" action="/search.php">
					<a class="mobile nav-link" id="mobile-search-link" href="#">
						<span class="icon icon-search"></span>
					</a>

					<input style="width: 80%;" class="mobile mobile-search" type="search" name="searchText" placeholder="Search" aria-label="Search" id="search-bar" autocomplete="off"></input>
				</form>
			</li>
			<?php } ?>

			<li class="nav-item" style="grid-column-start: 3; grid-column-end: 3;">
				<a class="nav-link" href="#" id="profile-menu" data-menu="accountMenu">
					<?php if($userID && $SITE_GLOBALS['user']['thumbnail']){ ?>
						<img style="height: 90px !important; width: 90px !important; border-radius: 50%;" src="<?php echo crUser::getProfileIcon($SITE_GLOBALS['user']['id']) ?>"/>
					<?php }else{ ?>
						<img style="height: 90px !important; width: 90px !important; border-radius: 50%;" src="<?php echo DIR_IMG . '/defaultProfileImage.png' ?>"/>						
					<?php } ?>
				</a>		
				<div class="mobile-menu" id="accountMenu">
				<?php if($userID){ ?>
					<a class="mobile-menu-item" href="/account.php" id="homemnu"><span class="icon icon-home3"></span>&nbsp;Home</a>
					<a class="mobile-menu-item" href="/notifications.php" id="notifmnu"><span class="icon icon-bell"></span>&nbsp;Notifications <div class="notification-count"><?php echo crActivity::getNumberOfNotifications($SITE_GLOBALS['user']['id']) ?></div></a>
					<a class="mobile-menu-item" href="/messages.php" id="msgmnu"><span class="icon icon-drawer"></span>&nbsp;Messages</a>
					<a class="mobile-menu-item" href="/profile.php?userid=<?php echo $SITE_GLOBALS['user']['id'] ?>"><span class="icon icon-profile"></span>&nbsp;Profile</a>
					<a class="mobile-menu-item" href="/myprofile.php"><span class="icon icon-cog"></span>&nbsp;Settings</a>
					<a class="mobile-menu-item" href="/logout.php"><span class="icon icon-exit"></span>&nbsp;Logout</a>	
		
				<?php } else {  ?>
					<a class="mobile-menu-item" href="/index.php" id="homemnu"><span class="icon icon-home3"></span>&nbsp;Home</a>
					<a class="mobile-menu-item" href="#" id="aboutmnu"><span class="icon icon-question"></span>&nbsp;About</a>
					<form id="login" style="display: block;" action="/login.php" method="post">
						<input class="mobile form-control" style="width: 90% !important; height: 60px; font-size: 40px; margin-top: 15px;" type="email" required="true" placeholder="Email" name="email" id="email"/>
						  <input class="mobile form-control" style="width: 90% !important; height: 60px; font-size: 40px; margin-top: 15px;" type="password" required="true" placeholder="Password" name="password" id="password"/>
						<button type="submit" class="mobile btn-primary" name="login_submit">Log in</button>
					  </form>
				<?php } ?>
        		</div>
			</li>
			
		</ul>
	
			
</div>
		
<?php 
} 