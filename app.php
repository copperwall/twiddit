<?php
header("Access-Control-Allow-Origin: *");

require 'connect.inc.php';
require 'vendor/autoload.php';
require_once('Reddit.php');
require_once('View.php');

// If request is for the public directory, serve static file (for js/css)
if (stristr($_SERVER['REQUEST_URI'], 'public')) {
   return false;
}

$app = new \Slim\Slim();

$app->get('/', function() use ($app) {
   include ('headerBar.html');
});

$app->get('/signin', function()  use ($app) {
   include 'signin.html';
});

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

$app->get('/reddit_callback', function() use ($app) {
   $req = $app->request();
   echo "Reddit time\n";
   echo $req->get('state') . "\n";
   echo $req->get('code');
});

$app->post('/login', function() use ($app, $db) {
   $username = $app->request->post('username');
   $password = $app->request->post('password');

   $query = "SELECT * FROM  users where userName='$username' and userPassword='$password'";

    $result = $db->query($query);

    if (empty($query)) {
        echo 'FAILURE';
    } else {
       $app->redirect('/');
    }
});

$app->run();
