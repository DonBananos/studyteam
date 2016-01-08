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
			<?php
			$num_buddies_pending = $student->get_number_of_buddies_pending();
			$num_group_invites = $student->get_number_of_pending_invites();
			$pending_total = $num_buddies_pending + $num_group_invites;
			?>
			<ul class="nav navbar-nav navbar-right">
				<li><form style="padding-bottom: 0; margin-bottom: 0; margin-top: 8px;" action="<?php echo BASE ?>search/" method="GET"><input  type="text" name="s" placeholder="Username, Email or Name" class="form-control" placeholder="Search"></form></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle form-inline" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<?php echo $student->get_firstname(); ?>
						<?php
						if ($pending_total > 0)
						{
							echo "($pending_total)";
						}
						?>
						<span class="fa fa-caret-down"></span></a>
					<ul class="dropdown-menu dropdown-inverted">
						<li><a href="<?php echo BASE ?>student/<?php echo strtolower($student->get_username()); ?>/">My Profile</a></li>
						<li><a href="<?php echo BASE ?>buddies/">My Buddies</a></li>
						<?php
						if ($num_buddies_pending > 0)
						{
							?>
							<li><a href="<?php echo BASE ?>buddies/">Buddy Invites (<?php echo $num_buddies_pending ?>)</a></li>
							<?php
						}
						if($num_group_invites > 0)
						{
							?>
							<li><a href="<?php echo BASE ?>group/my-invites/">Group Invites (<?php echo $num_group_invites ?>)</a></li>
							<?php
						}
						?>
						<li><a href="<?php echo BASE ?>settings/">Settings</a></li>
						<?php
						if ($student->get_permission() !== 1)
						{
							?>
							<li role="separator" class="divider"></li>
							<li><a href="<?php echo BASE ?>student/">Students</a></li>
							<li><a href="<?php echo BASE ?>group/">Group</a></li>
							<?php
						}
						?>
						<li role="separator" class="divider"></li>
						<li><a href="<?php echo BASE ?>logout.php">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>