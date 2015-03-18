// View logic and helper functions for the main view

var posts = {};
var url = 'http://twiddit.ddns.net:2000';
// Name of users that are being followed and name of subreddits being followed
var users = [], subreddits = [];

$('#logout').click(function() {
   document.cookie = 'user=; expires=Thu, 01 Jan 1970 00:00:00 UTC';
   window.location = '/';
});

// TODO Should add a cool function that abstracts this out.
$('#following_tab').click(function() {
   $('#following_tab').addClass('active');
   $('#subreddits_tab').removeClass('active');
   $('#preferences_tab').removeClass('active');
   $('#messaging_tab').removeClass('active');

   $('#following_feed').show();
   $('#preferences_page').hide();
   $('#subreddits_feed').hide();
   $('#messaging_page').hide();
});

$('#subreddits_tab').click(function() {
   $('#subreddits_tab').addClass('active');
   $('#following_tab').removeClass('active');
   $('#preferences_tab').removeClass('active');
   $('#messaging_tab').removeClass('active');

   $('#subreddits_feed').show();
   $('#preferences_page').hide();
   $('#following_feed').hide();
   $('#messaging_page').hide();
});

$('#preferences_tab').click(function() {
   $('#preferences_tab').addClass('active');
   $('#following_tab').removeClass('active');
   $('#subreddits_tab').removeClass('active');
   $('#messaging_tab').removeClass('active');

   $('#preferences_page').show();
   $('#following_feed').hide();
   $('#subreddits_feed').hide();
   $('#messaging_page').hide();
});

$('#messaging_tab').click(function() {
   $('#messaging_tab').addClass('active');
   $('#preferences_tab').removeClass('active');
   $('#following_tab').removeClass('active');
   $('#subreddits_tab').removeClass('active');

   $('#messaging_page').show();
   $('#preferences_page').hide();
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
   var favoriteIcon = $("<span class=\"favorite glyphicon glyphicon-star-empty\" aria-hidden=\"true\" data-name=\"" + post.name + "\"></span>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.body_html).text();

   return container.append([favoriteIcon, author, subreddit, body]);
};

function subToHTML(post) {
   var container = $("<div class='comment_blurb'></div>");
   var favoriteIcon = $("<span class=\"favorite glyphicon glyphicon-star-empty\" aria-hidden=\"true\" data-name=\"" + post.name + "\"></span>");
   var title = $("<a href='" + post.url + "'><h4 class='title'>" + post.title + "</h4></a>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.selftext_html).text();

   return container.append([favoriteIcon, title, author, subreddit, body]);
};

// Preferences functions
var min = "0", max = "10", defaultVal = "5";

function getSubredditValue(subredditName) {
   return $("#" + subredditName).val();
}

function addSubredditSlider(subreddit) {
   var subredditName = subreddit.subreddit;
   var slider = $("<input>").attr("type", "range")
                            .attr("name", "slider-1")
                            .attr("id", subredditName)
                            .attr("value", subreddit.preferenceValue) // get from database
                            .attr("min", min)
                            .attr("max", max);
   $("#subreddits").append("<label>r/" + subredditName + "</label>")
                   .append(slider);

   slider.on("mouseup", function () {
      var toAdd = {};
      toAdd["subreddit"] = this.id;
      toAdd["preferenceValue"] = this.value;
      var request = $.post('/settings/subreddits', JSON.stringify(toAdd));
   });
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

function sendMessage() {
   // Grab recipient
   var recipient = $("#message_recipient").val();
   // Grab subject
   var subject = $("#message_subject").val();
   // Grab message
   var text = $("#message_body").val();

   var data = {
      to: recipient,
      subject: subject,
      text: text
   }

   // Send off to backend
   var request = $.post('/message', JSON.stringify(data));
   request.done(function(data) {
      // if success, show success label and clear text
      // else, show failure label
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
   $("#uSearch").val("");
});
   
$("#rButton").click(function() {
   var newSubreddit = $("#rSearch").val(), alreadyAdded = false;
   for (i=0; i < subreddits.length && !alreadyAdded; i++) {
      alreadyAdded = subreddits[i] == newSubreddit;
   }
   if (!alreadyAdded) {
      subreddits[subreddits.length] = newSubreddit;
      var toAdd = {};
      toAdd["subreddit"] = newSubreddit;
      toAdd["preferenceValue"] = defaultVal;
      var request = $.post('/settings/subreddits', JSON.stringify(toAdd));
      if (newSubreddit != "") {
         addSubredditSlider(toAdd);
      }
   }
   $("#rSearch").val("");
});
