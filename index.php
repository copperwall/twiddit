<?php
header("Access-Control-Allow-Origin: *");

// Composer
require 'vendor/autoload.php';

// Our libs
require_once('TwidditDB.php');
require_once('Reddit.php');
require_once('Settings.php');
require_once('View.php');
require_once('Auth.php');

// If request is for the public directory, serve static file (for js/css)
if (stristr($_SERVER['REQUEST_URI'], 'public')) {
   return false;
}

$app = new \Slim\Slim();

$app->get('/', function() use ($app) {
   if(!isset($_COOKIE['user'])) {
      $loginpage = new View('signin.phtml');
      $loginpage->render();
   } else {
      $mainpage = new View('main.phtml');
      $oauthUrl = Auth::buildOAuthRedirectUrl();
      $mainpage->addPageVariable('oauthUrl', $oauthUrl);
      $mainpage->addPageVariable('user', $_COOKIE['user']);
      $mainpage->render();
   }
});

$app->get('/signin', function()  use ($app) {
   $signinpage = new View('signin.phtml');
   $signinpage->render();
});

$app->get('/feed', function() use ($app) {
   $db = TwidditDB::db();
   $username = $_COOKIE['user'];
   $query = <<<EOT
      SELECT `redditor`
      FROM `followingRedditors`
      WHERE `userName` = :username
EOT;

   $statement = $db->prepare($query);
   $statement->bindParam(':username', $username);
   $statement->execute();
   $result = $statement->fetchAll(PDO::FETCH_ASSOC);

   $users = [];
   foreach ($result as $row) {
      $users[] = $row['redditor'];
   }
   
   $comments = Reddit::getComments($users);

   echo json_encode($comments);
});

$app->get('/subreddits', function() use ($app) {
   $db = TwidditDB::db();
   $username = $_COOKIE['user'];
   $query = <<<EOT
      SELECT `subreddit`
      FROM `followingSubreddit`
      WHERE `userName` = :username
EOT;

   $statement = $db->prepare($query);
   $statement->bindParam(':username', $username);
   $statement->execute();
   $results = $statement->fetchAll(PDO::FETCH_ASSOC);

   $subreddits = [];
   foreach ($results as $row) {
      $subreddits[] = $row['subreddit'];
   }
   $data = Reddit::getSubredditPosts($subreddits);

   echo json_encode($data);
});

$app->get('/reddit_callback', function() use ($app) {
   $req = $app->request();
   $state = $req->get('state');
   $code = $req->get('code');
   $response = Auth::getTokenFromAuthCode($code, $state);

   if (array_key_exists('error', $response)) {
      // Do error thing
      echo "OAuth Error: {$response['error']}";
      die();
   }

   Auth::setUserToken($response['access_token'], $response['refresh_token'],
    $response['expires_in']);
   $app->redirect('/');
});

$app->post('/login', function() use ($app) {
   $db = TwidditDB::db();
   $username = $app->request->post('username');
   $password = $app->request->post('password');

   $stmt = $db->prepare("SELECT * FROM  users where userName=':username' and userPassword=':password'");

   if ($db == null) {
      echo 'hi your db is null';
   }

   $stmt->bindParam(':username', $username);
   $stmt->bindParam(':password', $password);
   $stmt->execute();
   $result = $stmt->fetchAll();

   if($result->rowCount() == 0) {
      $failpage = new View('signin.phtml');
      $failpage->addPageVariable('failure', true);
      $failpage->render();
   } else {
     $cookie_name = 'user';
     $cookie_value = $username;
     setcookie($cookie_name, $cookie_value, time() + 36000); // cookie lasts 60 secs
     $app->redirect('/');
   }
});

$app->post('/signup', function() use ($app) {
   $db = TwidditDB::db();
   $username = $app->request->post('username');
   $password = $app->request->post('password');

   $stmt = $db->prepare("SELECT * FROM users WHERE userName = ':username'");

   $stmt->bindParam(':username', $username);
   $stmt->execute();

   $result = $stmt->fetchAll();

   if ($result->rowCount() > 0) {
     $failpage = new View('signin.php');
     $failpage->addPageVariable('signupfail', true);
     $failpage->render();
   } else {
     $stmt = $db->prepare("INSERT INTO users values(':username', ':password')";
     $stmt->bindParam(':username', $username);
     $stmt->bindParam(':password', $password);
     $stmt->execute();
     $result = $stmt->fetchAll();
     $successpage = new View('signin.php');
     $successpage->addPageVariable('signupsuccess', true);
     $successpage->render();
   }
});

// Return a settings object with a following and subreddits array
$app->get('/settings', function() use ($app) {
   $following = Settings::getFollowing();
   $subreddits = Settings::getSubreddits();

   $response = [
      'following' => $following,
      'subreddits' => $subreddits
   ];

   echo json_encode($response);
});

$app->post('/settings/following', function() use ($app) {
   $redditor = $app->request()->getBody();

   Settings::addFollowing($redditor);

   // Return the JSON text of the user's following settings.
   $settings = Settings::getFollowing();
   echo json_encode($settings);
});

$app->post('/settings/subreddits', function() use ($app) {
   $body = $app->request()->getBody();
   $data = json_decode($body, /* assoc */ true);

   Settings::addSubreddit($data['subreddit'], $data['preferenceValue']);

   // Return the JSON text of the user's subreddit settings.
   $settings = Settings::getSubreddits();
   echo json_encode($settings);
});

$app->post('/favorite', function() use ($app) {
   $JSONBody = $app->request()->getBody();
   $data = json_decode($JSONBody, /* assoc */ true);
   Reddit::favorite($data['id']);
});

$app->run();
