// View logic and helper functions for the main view

var posts = {};
var url = 'http://twiddit.ddns.net:2000';
// Name of users that are being followed and name of subreddits being followed
var users = [], subreddits = [];

$('#logout').click(function() {
   document.cookie = 'user=; expires=Thu, 01 Jan 1970 00:00:00 UTC';
   window.location = '/';
});

$('#following_tab').click(function() {
   $('#following_tab').addClass('active');
   $('#subreddits_tab').removeClass('active');
   $('#preferences_tab').removeClass('active');

   $('#following_feed').show();
   $('#preferences_page').hide();
   $('#subreddits_feed').hide();
});

$('#subreddits_tab').click(function() {
   $('#subreddits_tab').addClass('active');
   $('#following_tab').removeClass('active');
   $('#preferences_tab').removeClass('active');

   $('#subreddits_feed').show();
   $('#preferences_page').hide();
   $('#following_feed').hide();
});

$('#preferences_tab').click(function() {
   $('#preferences_tab').addClass('active');
   $('#following_tab').removeClass('active');
   $('#subreddits_tab').removeClass('active');

   $('#preferences_page').show();
   $('#following_feed').hide();
   $('#subreddits_feed').hide();
});

var feedRequest = $.getJSON(url + '/feed');
var settingsRequest = $.getJSON(url + '/settings');
var subredditRequest = $.getJSON(url + '/subreddits');

feedRequest.done(function(posts){
   posts.forEach(function(post) {
      $('#following_feed .section_body').append(commentToHTML(post));
   });
});

subredditRequest.done(function(posts){
   posts.forEach(function(post) {
      $('#subreddits_feed .section_body').append(subToHTML(post));
   });
});

settingsRequest.done(function(settings){
   users = settings.following;
   subreddits = settings.subreddits;
   
   users.forEach(function(user) {
      addFollowUser(user);
   });
   subreddits.forEach(function(subreddit) {
      addSubredditSlider(subreddit);
   });
});

// After all three requests finish loading, hide the loading div and show the
// preferences one.
$.when.apply($, [feedRequest, subredditRequest, settingsRequest]).done(function() {
   $('#loading_area').hide();
   $('#following_feed').show();
   $('.favorite').click(favorite);
});

function commentToHTML(post) {
   var container = $("<div class='comment_blurb'></div>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   // var author = $("<h4 class='lead'>" + post.author + "</h4>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.body_html).text();
   var favoriteIcon = $("<span class=\"favorite glyphicon glyphicon-star-empty\" aria-hidden=\"true\" data-name=\"" + post.name + "\"></span>");

   return container.append([author, subreddit, body, favoriteIcon]);
};

function subToHTML(post) {
   var container = $("<div class='comment_blurb'></div>");
   var title = $("<a href='" + post.url + "'><h4 class='title'>" + post.title + "</h4></a>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.selftext_html).text();

   return container.append([title, author, subreddit, body]);
};

// Preferences functions
var min = "0", max = "10", defaultVal = "5";

function getSubredditValue(subredditName) {
   return $("#" + subredditName).val();
}

function addSubredditSlider(subredditName) {
   var slider = $("<input>").attr("type", "range")
                            .attr("name", "slider-1")
                            .attr("id", subredditName)
                            .attr("value", defaultVal) // get from database
                            .attr("min", min)
                            .attr("max", max);
   slider.addEventListener("mouseup", function () {
        // add to database
   });
   $("#subreddits").append("<label>r/" + subredditName + "</label>")
                   .append(slider);
}

function addFollowUser(user) {
   $("#following").append("<li>/u/" + user + "</li>")
}

/**
 * Should be called when the favorite icon is clicked. Not sure if we want
 * toggling or not.
 */
function favorite() {
   var self = this;
   var id = $(this).attr('data-name');
   var data = {
      "id": id
   };

   if ($(self).hasClass(('glyphicon-star')))
      return;

   var request = $.post('/favorite', JSON.stringify(data));
   request.done(function() {
      $(self).removeClass('glyphicon-star-empty');
      $(self).addClass('glyphicon-star');
   });
}

$("#uButton").click(function() {
   var newUser = $("#uSearch").val(), alreadyAdded = false;
   for (i=0; i < users.length && !alreadyAdded; i++) {
      alreadyAdded = users[i] == newUser;
   }
   if (!alreadyAdded) {
      users[users.length] = newUser;
      var request = $.post('/settings/following', newUser);
      addFollowUser(newUser);
   }
});
   
$("#rButton").click(function() {
   var newSubreddit = $("#rSearch").val(), alreadyAdded = false;
   for (i=0; i < users.length && !alreadyAdded; i++) {
      alreadyAdded = users[i] == newUser;
   }
   if (!alreadyAdded) {
      subreddits[subreddits.length] = newSubreddit;
      //TODO:Add to database
      if (newSubreddit != "") {
         addSubredditSlider(newSubreddit);
      }
   }
});


