// View logic and helper functions for the main view
var dirtySettings = {
   following: {
      dirty: true,
      endpoint: '/feed'
   },
   subreddits: {
      dirty: true,
      endpoint: '/subreddits'
   },
   settings: {
      dirty: true,
      endpoint: '/settings'
   }
};

// Name of users that are being followed and name of subreddits being followed
var users = [], subreddits = [];

$('#logout').click(function() {
   document.cookie = 'user=; expires=Thu, 01 Jan 1970 00:00:00 UTC';
   window.location = '/';
});

loadContent('following_tab');

function commentToHTML(post) {
   var contextURL = genContextURL(post);
   var container = $("<div class='comment_blurb'></div>");
   var favoriteIcon = $("<span class=\"favorite glyphicon glyphicon-star-empty\" aria-hidden=\"true\" data-name=\"" + post.name + "\"></span>");
   var title = $("<a target=\"_blank\" href='" + contextURL + "'><h5 class='title'>" + post.link_title + "</h5></a><hr>");
   var timeSince = $("<span class=\"timestamp text-muted\">" + getTimeSince(post.created_utc) + "</span>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.body_html).text();

   return container.append([favoriteIcon, author, subreddit, timeSince, title, body]);
};

function subToHTML(post) {
   var container = $("<div class='comment_blurb'></div>");
   var favoriteIcon = $("<span class=\"favorite glyphicon glyphicon-star-empty\" aria-hidden=\"true\" data-name=\"" + post.name + "\"></span>");
   var timeSince = $("<span class=\"timestamp text-muted\">" + getTimeSince(post.created_utc) + "</span>");
   var title = $("<a target=\"_blank\" href='" + post.url + "'><h4 class='title'>" + post.title + "</h4></a>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.selftext_html).text();

   if (post.is_self) {
      subreddit.append("<hr>");
   }

   return container.append([favoriteIcon, timeSince, title, author, subreddit, body]);
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
   $("#subreddit-prefs").append("<label>r/" + subredditName + "</label>")
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

   $("#message_result").hide();
   var data = {
      to: recipient,
      subject: subject,
      text: text
   }

   $(".form-control").attr("readonly", "");
   // Send off to backend
   var request = $.post('/message', JSON.stringify(data));
   request.done(function(data) {
      $(".form-control").attr("readonly", null);
      $(".form-control").val("");
      $("#message_result").removeClass();
      $("#message_result").addClass("glyphicon glyphicon-ok");
      $("#message_result").show();
   });

   request.fail(function() {
      $(".form-control").attr("readonly", null);
      var failMessage = "Ruh roh, your message failed. Please try again</span>";
      $("#message_result").removeClass();
      $("#message_result").addClass('bg-danger');
      $("#message_result").html(failMessage);
      $("#message_result").show();
   });
}

/**
 * Display time difference in days, hours, or minutes.
 */
function getTimeSince(date) {
   var timeSince = (Date.now() / 1000) - date;
   var days = Math.floor(timeSince / (60 * 60 * 24));

   if (days)
      return days + " days ago";

   var hours = Math.floor(timeSince / (60 * 60));

   if (hours)
      return hours + " hours ago";

   var minutes = Math.floor(timeSince / 60);

   return minutes + " minutes ago";
}

function genContextURL(post) {
   var postid = getLinkId(post.link_id);
   var parentid = getLinkId(post.parent_id);
   return 'https://reddit.com/r/' + post.subreddit + '/comments/'
    + postid + '/asdf/' + parentid;
}

/**
 * Strips the "t3_" prefix from post ids.
 */
function getLinkId(parentid) {
   var matches = /^t[0-9]_(.*)$/.exec(parentid);

   if (matches) {
      return matches[1];
   }

   console.error('Parentid: ' + parentid + ' is invalid');
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
   dirtySettings.following.dirty = true;
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
   dirtySettings.subreddits.dirty = true;
});

/**
 * Make the clicked tab button active and show its corresponding view. Hide all
 * other views.
 */
function switchView() {
   var clickedTab = $(this);
   var selectedView = clickedTab.attr('data-viewid');

   var isDirty = Object.keys(dirtySettings).reduce(function(prev, current) {
      if (dirtySettings[current].dirty)
         return true;
      return prev;
   }, false);

   if (isDirty) {
      // reload settings
      loadContent(clickedTab.attr('id'));
   } else {
      clickedTab.addClass('active');
      $("#" + selectedView).show();

      // Hide all other views and make all other tabs inactive
      $(".view_tab").each(function (index, navButton) {
         if ($(navButton).attr('id') != clickedTab.attr('id')) {
            var viewPane = $(navButton).attr('data-viewid');
            $(navButton).removeClass('active');
            $("#" + viewPane).hide();
         }
      })
   }
}

// Show the loading image
// empty subreddit and following content containers (this could be on a per
//    setting basis)
// Fire off requests for each dirty setting category
// Once all requests have finished remove the loading image
function loadContent(viewTab) {
   $('#loading_area').show();
   $('#following_feed').hide();

   // filter out non dirty settings
   var settings = Object.keys(dirtySettings).filter(function(type) {
      return dirtySettings[type].dirty;
   }, []);

   // settings is now an array of ['following', 'subreddits', 'settings']
   // empty the dirty settings areas
   // start requests
   var requests = settings.map(function(setting) {
      var currentSetting = dirtySettings[setting];

      if (setting !== 'settings') {
         var contentSelector = '#' + setting + '_feed .section_body';
         $(contentSelector).html('');
      }

      var request = $.getJSON(currentSetting.endpoint);

      if (setting === 'following') {
         request.done(followingDone);
      } else if (setting === 'subreddits') {
         request.done(subredditsDone);
      } else if (setting === 'settings') {
         request.done(settingsDone);
      }

      return request;
   });

   // After all three requests finish loading, hide the loading div and show the
   // preferences one.
   $.when.apply($, requests).done(function() {
      $('#loading_area').hide();
      $('.favorite').click(favorite);
      $('#message_send').click(sendMessage);
      $(".view_tab").click(switchView);

      // Clear all dirty settings
      dirtySettings.following.dirty = false;
      dirtySettings.subreddits.dirty = false;
      dirtySettings.settings.dirty = false;

      $('#' + viewTab).trigger('click');
   });
}

function followingDone(posts) {
   posts.forEach(function(post) {
      $('#following_feed .section_body').append(commentToHTML(post));
   });
}

function subredditsDone(posts) {
   posts.forEach(function(post) {
      $('#subreddits_feed .section_body').append(subToHTML(post));
   });
}

function settingsDone(settings) {
   users = settings.following;
   subreddits = settings.subreddits;

   users.forEach(function(user) {
      addFollowUser(user);
   });
   subreddits.forEach(function(subreddit) {
      addSubredditSlider(subreddit);
   });
}
