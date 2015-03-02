// Preferences functions
var min = "0", max = "10", defaultVal = "5";

function getSubredditValue(subredditName) {
	return $("#" + subredditName).val();
}

function addNewSubredditSlider(subredditName) {
	var slider = $("<input>").attr("type", "range")
							 .attr("name", "slider-1")
							 .attr("id", subredditName)
							 .attr("value", defaultVal)
							 .attr("min", min)
							 .attr("max", max);
	$("#subreddits").append("<label>r/" + subredditName + "</label>")
					.append(slider);
}

function addNewFollowUser(user) {
	$("#following").append("<li>/u/" + user + "</li>")
}

$("#uButton").click(function() {
	addNewFollowUser($("#uSearch").val());
	});
	
$("#rButton").click(function() {
	if ($("#rSearch").val() != "") 
		addNewSubredditSlider($("#rSearch").val());
	});

