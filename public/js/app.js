// View logic and helper functions for the main view

var posts = {};
var url = 'http://twiddit.ddns.net:2000';

$('#following_tab').click(function() {
   $('#following_tab').addClass('active');
   $('#subreddits_tab').removeClass('active');

   $('#following_feed').show();
   $('#subreddits_feed').hide();
});

$('#subreddits_tab').click(function() {
   $('#subreddits_tab').addClass('active');
   $('#following_tab').removeClass('active');

   $('#subreddits_feed').show();
   $('#following_feed').hide();
});

$.getJSON(url + '/feed').done(function(posts){
   posts.forEach(function(post) {
      $('#following_feed .section_body').append(commentToHTML(post));
   });
   $('#following_feed').show();
});

$.getJSON(url + '/subreddits').done(function(posts){
   posts.forEach(function(post) {
      $('#subreddits_feed .section_body').append(subToHTML(post));
   });
});

function commentToHTML(post) {
   var container = $("<div class='comment_blurb'></div>");
   var author = $("<span class='author lead'>by " + post.author + "</span>");
   // var author = $("<h4 class='lead'>" + post.author + "</h4>");
   var subreddit = $("<span class='subreddit text-muted'>in r/" + post.subreddit + "</span>");
   // The jQuery madness happening here is to decode html entities
   var body = $('<p class="comment_body"></p>').html(post.body_html).text();

   return container.append([author, subreddit, body]);
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
