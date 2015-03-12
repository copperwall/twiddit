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
   if(!isset($_COOKIE['user'])) {
      include 'signin.php';
   } else {
      include ('headerBar.html');
   }
});

$app->get('/signin', function()  use ($app) {
   include 'signin.php';
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

   if ($db == null) {
      echo 'hi your db is null';
   }
   $result = $db->query($query);

   if($result->rowCount() == 0) {
     echo 'FAILURE';
   } else {
     $cookie_name = 'user';
     $cookie_value = $username;
     setcookie($cookie_name, $cookie_value, time() + 60); // cookie lasts 60 secs
     $app->redirect('/');
   }
});


$app->post('/signup', function() use ($app, $db) {
   echo 'wutwut';
   $username = $app->request->post('username');
   $password = $app->request->post('password');

   $query = "SELECT * FROM  users where userName='$username'";
   echo $query;
   $result = $db->query($query);
   
   if ($result->rowCount() > 0) {
     // set error msg param "user already exists or something"
   } else {
     $insert = "INSERT INTO users values('$username', '$password')";
     $result = $db->exec($insert);
     // set success message "user created"
   }
   
   $app->redirect('/signin');
});

$app->get('/app.js', function() use ($app) {
   echo file_get_contents('app.js');
});

$app->get('/base.css', function() use ($app) {
   echo file_get_contents('base.css');
});

$app->run();
