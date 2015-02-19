<?php
header("Access-Control-Allow-Origin: *");

require 'vendor/autoload.php';
require_once('Reddit.php');
require_once('View.php');

$app = new \Slim\Slim();
$app->get('/feed', function() use ($app) {
   $users = ['kn0thing', 'zolokar', 'xiongchiamiov'];
   $comments = Reddit::getComments($users);

   echo json_encode($comments);
});
$app->get('/subreddits', function() use ($app) {
   $subreddit = 'python';
   $data = Reddit::getSubredditPosts($subreddit);

   echo json_encode($data);
});
$app->post('/signin', function()  use ($app) {
   echo $app->request->post();
});

$app->get('/reddit_callback', function() use ($app) {
   $req = $app->request();
   echo "Reddit time\n";
   echo $req->get('state') . "\n";
   echo $req->get('code');
});
$app->run();
