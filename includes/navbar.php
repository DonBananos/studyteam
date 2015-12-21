<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#studyteam-member-navbar" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo BASE ?>" id="navbar-brand">StudyTeam</a>
		</div>
		<div class="collapse navbar-collapse" id="studyteam-member-navbar">
			<ul class="nav navbar-nav">
				<li><a href="<?php echo BASE ?>group/">Groups</a></li>
				<li><a href="#">Education</a></li>
				<li><a href="#">School</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><form style="padding-bottom: 0; margin-bottom: 0; margin-top: 8px;" action="<?php echo BASE ?>student/search.php" method="GET"><input  type="text" name="s" placeholder="Username, Email or Name" class="form-control" placeholder="Search"></form></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $student->get_firstname(); ?><span class="caret"></span></a>
					<ul class="dropdown-menu dropdown-inverted">
						<li><a href="<?php echo BASE ?>student/details.php?id=<?php echo $student->get_id(); ?>">My Profile</a></li>
						<li><a href="<?php echo BASE ?>logout.php">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>