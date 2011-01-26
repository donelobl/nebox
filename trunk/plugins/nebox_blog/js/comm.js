$(document).ready(function() {
	$('#comments-form').hide();
	$('#add-comment-toggle').click(function() {
		$('#comments-form').toggle(400);
		return false;
	});
});