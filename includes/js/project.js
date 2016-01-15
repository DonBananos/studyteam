//Smooth Scrolling when anchor links are clicked!
$(function () {
	$('a[href*=#]:not([href=#])').click(function () {
		if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
			if (target.length) {
				$('html,body').animate({
					scrollTop: target.offset().top
				}, 1000);
				return false;
			}
		}
	});
});
function tuPost(postId)
{
	alert("Let's pretend you just gave thumbs up to post no. " + postId);
}
function openComment(postId)
{
	$("#feedPost" + postId + "Comment").toggle(0);
}