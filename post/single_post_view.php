<?php
/*
 * This file is used to display a single post.
 * Before using this file, the header has been created as $header and the post
 * is placed in the array $feed_post
 */

//We'll make an object of the posting student, with the id from the $feed_post array
$poster = new Student($feed_post['student_id']);
$post = new Post($feed_post['post_id']);

if (isset($_POST['give_tu']) && isset($_POST['post_id']))
{
	$post_id = sanitize_int($_POST['post_id']);
	if ($post_id == $post->get_id())
	{
		$post->give_thumbs_up($student->get_id());
		?>
		<script>
			$('html, body').animate({
				scrollTop: $("#post_<?php echo $post_id ?>").offset().top
			}, 2000);
		</script>
		<?php
	}
}
elseif (isset($_POST['rem_tu']) && isset($_POST['post_id']))
{
	$post_id = sanitize_int($_POST['post_id']);
	if ($post_id == $post->get_id())
	{
		$post->remove_thumbs_up($student->get_id());
	}
}
?>
<div class="content-box" id="post_<?php echo $feed_post['post_id'] ?>">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<img src="<?php echo $poster->get_avatar() ?>" class="student-avatar-thumb">
			<div class="post-header">
				<a href="<?php echo BASE ?>student/<?php echo strtolower($poster->get_username()) ?>/"><?php echo $poster->get_username() ?></a>
				<?php
				echo $header;
				if ($view == "student feed")
				{
					?>
					<a href="<?php echo BASE ?>group/<?php echo $feed_post['group_id'] ?>/"><?php echo $feed_post['group_name'] ?></a>
					<?php
				}
				?>
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
				if ($view == "group")
				{
					if ($feed_post['group_public'] === 1)
					{
						if ($feed_post['post_public'] === 1)
						{
							?>
							<span class="fa fa-unlock"></span>
							<?php
						}
						else
						{
							?>
							<span class="fa fa-lock"></span>
							<?php
						}
					}
				}
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
	<?php
	$interaction_allowed = FALSE;
	if ($view == "group")
	{
		if ($membership)
		{
			$interaction_allowed = TRUE;
		}
	}
	elseif ($view == "student feed")
	{
		$interaction_allowed = TRUE;
	}
	if ($interaction_allowed === TRUE)
	{
		?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="feed-post-options">
					<span class="feed-option">
						<?php
						if ($post->check_if_user_has_given_thumbs_up($student->get_id()) === FALSE)
						{
							?>
							<form class="form-inline" action="" method="POST">
								<input type="hidden" name="post_id" value="<?php echo $post->get_id() ?>">
								<button type="submit" name="give_tu" class="btn-post-interaction"><span class="fa fa-thumbs-up"></span> Thumbs Up (<?php echo $post->get_number_of_thumbs_up() ?>)</button>
							</form>
							<?php
						}
						else
						{
							?>
							<form class="form-inline" action="" method="POST">
								<input type="hidden" name="post_id" value="<?php echo $post->get_id() ?>">
								<button type="submit" name="rem_tu" class="btn-post-interaction"><span class="fa fa-thumbs-up"></span> Remove Thumbs Up (<?php echo $post->get_number_of_thumbs_up() ?>)</button>
							</form>
							<?php
						}
						?>
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
						<input type="submit" class="btn btn-primary pull-right btn-sm" value="Comment" name="create_comment">
					</form>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>