<?php
require_once './includes/configuration.php';
$signed_in = FALSE;
if (isset($_SESSION['logged_in']))
{
	if (isset($_SESSION['user_id']))
	{
		$signed_in = TRUE;
	}
}

if ($signed_in === TRUE)
{
	require './member_area.php';
}
else
{
	?>
	<!DOCTYPE html>
	<!--
	To change this license header, choose License Headers in Project Properties.
	To change this template file, choose Tools | Templates
	and open the template in the editor.
	-->
	<html>
	    <head>
	        <meta charset="UTF-8">
	        <title>StudyTeam</title>
			<?php require './includes/header.php'; ?>
	    </head>
	    <body id="index-body">
			<nav class="navbar navbar-default navbar-fixed-top index-navbar">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#studyteam-front-navbar" aria-expanded="false">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="#" id="navbar-brand">StudyTeam</a>
					</div>
					<div class="collapse navbar-collapse" id="studyteam-front-navbar">
						<ul class="nav navbar-nav navbar-right">
							<li><a href="#login">Login</a></li>
							<li><a href="#info">What is StudyTeam?</a></li>
							<li><a href="#sign-up">Join</a></li>
							<li><a href="#contact">Contact</a></li>
						</ul>
					</div>
				</div>
			</nav>
			<div id="login" class="index-section">
				<?php
				require './login.php';
				?>
			</div>
			<div id="info" class="index-section">
				<div class="container">
					<h2>What is StudyTeam?</h2>
					<p class="lead">
						StudyTeam is the place you want to be, when studying in Denmark!<br/>
					</p>
					<p>
						Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</p>
					<p>
						Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</p>
					<p>
						Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</p>
					<p>
						Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</p>
				</div>
			</div>
			<div id="sign-up" class="index-section">
				<?php
				require './create.php';
				?>
			</div>
			<div id="contact">
				<div id="map">
					<iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=Lygten+37,+2400+København+NV;t=m&amp;z=15&amp;iwloc=A&amp;output=embed"></iframe>
				</div>
				<div class="index-section">
					<div class="container">
						<h2>Contact the team</h2>
						<div class="row">
							<form action="" method="POST" name="contact-form">
								<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
									<input type="text" name="contact-name" placeholder="Name" class="form-control">
								</div>
								<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
									<input type="email" name="contact-email" placeholder="Email" class="form-control">
								</div>
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<textarea name="contact-message" class="form-control" placeholder="Message"></textarea>
								</div>
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<input type="submit" class="btn btn-primary btn-lg" value="Send">
								</div>
							</form>
						</div>
						<hr>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="text-center">
									<span class="social">
										<a href="#" class="icon">
											<i class="fa fa-facebook-official fa-2x"></i>
										</a>
									</span>
									<span class="social">
										<a href="#">
											<span class="fa fa-twitter-square fa-2x"></span>
										</a>
									</span>
									<span class="social">
										<a href="#">
											<span class="fa fa-instagram fa-2x"></span>
										</a>
									</span>
									<span class="social">
										<a href="#">
											<span class="fa fa-linkedin-square fa-2x"></span>
										</a>
									</span>
								</div>
							</div>
						</div>
						<?php
						require './includes/footer.php';
						?>
					</div>
				</div>
			</div>
	    </body>
	</html>
	<?php
}