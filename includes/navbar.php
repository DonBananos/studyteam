<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo BASE ?>" id="navbar-brand">StudyTeam</a>
		</div>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li><a href="#">Groups</a></li>
				<li><a href="#">Education</a></li>
				<li><a href="#">School</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li><a href="#"><?php echo $student->get_firstname(); ?></a></li>
			</ul>
		</div>
	</div>
</nav>