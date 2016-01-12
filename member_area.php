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
				<div class="row">
					<div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
						<div class="content-box" id="news_1">
							<div class="row">
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<h4>Message for all users</h4>
									<hr>
									<p>
										Due to new features, we have been forced to remove all posts from the database.<br/>
										We appologies for the inconvinience.<br/>
										<br/>
										Regards,<br/>
										The StudyTeam Developers
									</p>
								</div>
							</div>
						</div>
						<?php
						$feed_post_ids = $student->get_post_ids_for_member_feed();
						$feed_posts = $student->get_posts_for_member_feed();
						if (is_array($feed_posts) && count($feed_posts) > 0)
						{
							$feed_populater_count = 0;
							foreach ($feed_posts as $feed_post)
							{
								if ($feed_populater_count % 5 == 0 && $feed_populater_count != 0)
								{
									$suggested_group_ids = $student->get_group_suggestions_for_feed(2);
									?>
									<div class="content-box" id="suggestions_<?php echo $feed_populater_count ?>">
										<div class="row">
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<h4>Consider joining</h4>
											</div>
											<?php
											foreach ($suggested_group_ids as $suggested_group_id)
											{
												$suggested_group = new Group($suggested_group_id);
												?>
												<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
													<div class="row">
														<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
															<a href="<?php echo BASE ?>group/<?php echo $suggested_group->get_id() ?>/" title="<?php echo $suggested_group->get_name() ?> &#013; &#013;<?php echo $suggested_group->get_description() ?>">
																<div class="group" id="group-<?php echo $suggested_group->get_id() ?>" style="background-image: url(<?php echo $suggested_group->get_category_image() ?>);">
																	<div class="group-header">
																		<h3><?php echo $suggested_group->get_name() ?></h3>
																	</div>
																</div>
															</a>
														</div>
													</div>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php
								}
								$poster = new Student($feed_post['student_id']);
								if ($feed_post['post_type'] === 1)
								{
									$header = " created a new ";
									if ($feed_post['post_public'] === 1)
									{
										$header .= "public";
									}
									else
									{
										$header .= "public";
									}
									$header .= " post in ";
								}
								elseif ($feed_post['post_type'] === 2)
								{
									$header = " posted a new ";
									if ($feed_post['post_public'] === 1)
									{
										$header = "public";
									}
									else
									{
										$header = "private";
									}
									$header .= " image in ";
								}
								?>
								<div class="content-box" id="post_<?php echo $feed_post['post_id'] ?>">
									<div class="row">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<img src="<?php echo $poster->get_avatar() ?>" class="student-avatar-thumb">
											<div class="post-header">
												<a href="<?php echo BASE ?>student/<?php echo strtolower($poster->get_username()) ?>/"><?php echo $poster->get_username() ?></a>
												<?php echo $header ?>
												<a href="<?php echo BASE ?>group/<?php echo $feed_post['group_id'] ?>/"><?php echo $feed_post['group_name'] ?></a>
												<span class="feed-post-user-option">
													<div class="dropdown pull-right">
														<button class="btn btn-default dropdown-toggle" type="button" id="feed-post-dropdownMenu<?php echo $feed_post['post_id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
															<span class="caret"></span>
														</button>
														<ul class="dropdown-menu feed-post-dropdownMenu" aria-labelledby="feed-post-dropdownMenu<?php echo $feed_post['post_id'] ?>">
															<li>
																<a href="<?php echo BASE ?>group/<?php echo $feed_post['group_id'] ?>/#post_<?php echo $feed_post['post_id'] ?>"><span class="fa fa-external-link-square"></span> Go to post</a>
															</li>
														</ul>
													</div>
												</span>
											</div>
											<div class="post-meta">
												<?php
												echo date("Y-m-d H:i", strtotime($feed_post['post_time']));
												?>
											</div>
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
												<div class="post-content">
													<?php echo $feed_post['post_content'] ?>
												</div>
												<?php
												if ($feed_post['post_type'] === 2 && $feed_post['img_path'] !== NULL)
												{
													?>
													<div class="post-image">
														<img src="<?php echo $feed_post['img_path'] ?>" alt="Uploaded user image" data-toggle="modal" data-target="#imageModal<?php echo $feed_post['post_id'] ?>">
													</div>
													<div class="modal fade bs-example-modal-lg modal-inverse" tabindex="-1" role="dialog" id="imageModal<?php echo $feed_post['post_id'] ?>">
														<div class="modal-dialog modal-lg">
															<div class="modal-header">
																<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
																<h4 class="modal-title" style="padding: 0 auto;">
																	Image upload from <a href="<?php echo BASE ?>student/<?php echo strtolower($poster->get_username()) ?>/"><?php echo $poster->get_username() ?></a>
																</h4>
															</div>
															<div class="modal-content">
																<img src="<?php echo $feed_post['img_path'] ?>" width="100%;">
															</div>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<div class="feed-post-options">
												<span class="feed-option">
													<a onclick="tuPost(<?php echo $feed_post['post_id'] ?>);"><span class="fa fa-thumbs-up"></span> Thumbs Up</a>
												</span>
												<span class="feed-option">
													<a onclick="openComment(<?php echo $feed_post['post_id'] ?>);"><span class="fa fa-comment"></span> Comment</a>
												</span>
											</div>
										</div>
									</div>
									<div class="row" id="feedPost<?php echo $feed_post['post_id'] ?>Comment" style="display: none">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
											<div class="feed-comments">
												<form action="" method="POST">
													<textarea name="post-comment-textarea" placeholder="Write a comment" readonly="readonly"></textarea>
													<input type="hidden" name="postId" value="<?php echo $feed_post['post_id'] ?>">
													<input type="submit" class="btn btn-primary pull-right btn-sm" value="Comment" name="create-comment">
												</form>
											</div>
										</div>
									</div>
								</div>
								<?php
								$feed_populater_count++;
							}
						}
						else
						{
							?>
							<div class="content-box" id="suggestions_<?php echo $feed_populater_count ?>">
								<div class="row">
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<h4>Welcome to StudyTeam <?php echo $student->get_username() ?>!</h4>
										<p>As you join groups, your feed will be filled with posts from your StudyTeam Society</p>
									</div>
								</div>
							</div>
							<?php
							$suggested_group_ids = $student->get_group_suggestions_for_feed(2);
							?>
							<div class="content-box" id="suggestions_<?php echo $feed_populater_count ?>">
								<div class="row">
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<h4>Consider joining</h4>
									</div>
									<?php
									foreach ($suggested_group_ids as $suggested_group_id)
									{
										$suggested_group = new Group($suggested_group_id);
										?>
										<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
											<div class="row">
												<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
													<a href="<?php echo BASE ?>group/<?php echo $suggested_group->get_id() ?>/" title="<?php echo $suggested_group->get_name() ?> &#013; &#013;<?php echo $suggested_group->get_description() ?>">
														<div class="group" id="group-<?php echo $suggested_group->get_id() ?>" style="background-image: url(<?php echo $suggested_group->get_category_image() ?>);">
															<div class="group-header">
																<h3><?php echo $suggested_group->get_name() ?></h3>
															</div>
														</div>
													</a>
												</div>
											</div>
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-4 hidden-xs">
						<div class="content-box">
							<h3>Your Groups</h3>
							<hr class="minor-line">
							<?php
							$group_ids = $student->get_group_ids_that_student_is_part_of(5);
							if (is_array($group_ids) && count($group_ids) > 0)
							{
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
							}
							else
							{
								?>
								<p>You are not member of a group yet.</p>
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
		<script>
			function tuPost(postId)
			{
				alert("Let's pretend you just gave thumbs up to post no. " + postId);
			}
			function openComment(postId)
			{
				$("#feedPost" + postId + "Comment").toggle(0);
			}
		</script>
    </body>
</html>