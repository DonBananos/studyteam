<?php
require_once './includes/configuration.php';
require './student/student.php';
require './group/group_controller.php';
require './group/group.php';
require_once './post/post.php';

if (!isset($_SESSION['logged_in']))
{
	?>
	<script>window.location = "<?php echo BASE ?>";</script>
	<?php
	die();
}
$student = new Student($_SESSION['user_id']);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $student->get_firstname() ?> (Member Area)</title>
		<?php require './includes/header.php'; ?>
    </head>
    <body>
		<?php
		require './includes/navbar.php';
		?>
		<div class="page">
			<div class="container">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="row">
						<div class="page-header">
							<h1>Welcome <?php echo $student->get_firstname() ?>!</h1>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
						<?php
						$feed_post_ids = $student->get_post_ids_for_member_feed();
						if (is_array($feed_post_ids) && count($feed_post_ids) > 0)
						{
							foreach ($feed_post_ids AS $feed_post_id)
							{
								$feed_post = new Post($feed_post_id);
								$feed_poster = new Student($feed_post->get_student_id());
								$feed_post_group = new Group($feed_post->get_group_id());
								?>
								<div class="content-box" id="post_<?php echo $feed_post_id ?>">
									<div class="row">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<img src="<?php echo $feed_poster->get_avatar() ?>" class="student-avatar-thumb">
											<div class="post-header">
												<a href="<?php echo BASE ?>student/<?php echo strtolower($feed_poster->get_username()) ?>/"><?php echo $feed_poster->get_username() ?></a> created a post in <a href="<?php echo BASE ?>group/<?php echo $feed_post_group->get_id() ?>/"><?php echo $feed_post_group->get_name() ?></a>
											</div>
											<div class="post-meta">
												<?php echo date("Y-m-d H:i", strtotime($feed_post->get_time())); ?>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<div class="post-content">
													<?php echo $feed_post->get_post() ?>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-4 hidden-xs">
						<div class="content-box">
							<h3>Your Groups</h3>
							<hr class="minor-line">
							<?php
							$group_ids = $student->get_group_ids_that_student_is_part_of(5);
							foreach ($group_ids as $group_id)
							{
								$group = new Group($group_id);
								?>
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<div class="row">
										<a href="<?php echo BASE ?>group/<?php echo $group->get_id() ?>/">
											<div class="group" id="group-1" style="background-image: url(<?php echo $group->get_category_image() ?>); background-size: cover; background-position: center">
												<div class="small-group-header">
												</div>
											</div>
											<div class="small-group-data">
												<?php echo $group->get_name() ?>
											</div>
										</a>
									</div>
								</div>
								<?php
							}
							?>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		require './includes/footer.php';
		?>
    </body>
</html>