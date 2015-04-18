<?php
header("Access-Control-Allow-Origin: *");

// Composer
require 'vendor/autoload.php';

define('CONFIG_FILE', 'config.json');

// Our libs
require_once('Lib/HTTP.php');
require_once('Lib/TwidditDB.php');
require_once('Lib/User.php');
require_once('Lib/Reddit.php');
require_once('Lib/Settings.php');
require_once('Lib/View.php');
require_once('Lib/Auth.php');

// If request is for the public directory, serve static file (for js/css)
if (stristr($_SERVER['REQUEST_URI'], 'public')) {
   return false;
}

$app = new \Slim\Slim();

$app->get('/', function() use ($app) {
   // Check to see if their session is in the database and has not expired.
   if (array_key_exists('session', $_COOKIE)
    && User::isValidSession($_COOKIE['session'])) {
      $username = User::getUserName();
      $oauthUrl = Auth::buildOAuthRedirectUrl();

      $mainpage = new View('main.phtml');
      $mainpage->addPageVariable('oauthUrl', $oauthUrl);
      $mainpage->addPageVariable('user', $username);
      $mainpage->render();
   } else {
      $loginpage = new View('signin.phtml');
      $loginpage->render();
   }
});

// TODO Add logout function to remove session from DB and cookies

$app->get('/signin', function()  use ($app) {
   $signinpage = new View('signin.phtml');
   $signinpage->render();
});

$app->get('/feed', function() use ($app) {
   $db = TwidditDB::db();
   $userid = User::getUserID();

   if (!$userid) {
      echo "Error: Invalid User";
      return;
   }

   $query = <<<EOT
      SELECT `redditor`
      FROM `redditors_followed`
      WHERE `userid` = :userid
EOT;
   $statement = $db->prepare($query);
   $statement->bindParam(':userid', $userid);
   $statement->execute();
   $results = $statement->fetchAll(PDO::FETCH_ASSOC);

   $users = array_map(function($row) {return $row['redditor'];} ,$results);
   $comments = Reddit::getComments($users);

   echo json_encode($comments);
});

$app->get('/subreddits', function() use ($app) {
   $db = TwidditDB::db();
   $userid = User::getUserID();

   $query = <<<EOT
      SELECT `subreddit`, `preference_value`
      FROM `subreddits_followed`
      WHERE `userid` = :userid
EOT;
   $statement = $db->prepare($query);
   $statement->bindParam(':userid', $userid);
   $statement->execute();
   $results = $statement->fetchAll(PDO::FETCH_ASSOC);

   $posts = array_reduce($results, function($collector, $row) {
      $subredditPosts = Reddit::getSubredditPosts($row['subreddit'],
       $row['preference_value']);
      return array_merge($collector, $subredditPosts);
   }, []);

   echo json_encode($posts);
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

   $query = <<<EOT
      SELECT `password_hash`
      FROM `users`
      WHERE `username` = :username
EOT;
   $stmt = $db->prepare($query);

   $stmt->bindParam(':username', $username);
   $stmt->execute();
   $result = $stmt->fetch();

   // No user exists
   if (!$result) {
      $failMode = 'noUser';
   }

   if (!isset($failMode)) {
      if (!password_verify($password, $result['password_hash'])) {
         $failMode = 'authFailure';
      } else {
         // Create new session, assign it to the user, redirect to home.
         $userid = User::getUserID($username);
         $sessionid = User::newSession($userid);
         setcookie('session', $sessionid, time() + 2592000); // 30 days
         $app->redirect('/');
      }
   }

   if ($failMode) {
      $failpage = new View('signin.phtml');
      $failpage->addPageVariable($failMode, true);
      $failpage->render();
   }
});

$app->post('/signup', function() use ($app) {
   $db = TwidditDB::db();
   $username = $app->request->post('username');
   $password = $app->request->post('password');
   $hash = password_hash($password, PASSWORD_DEFAULT);

   $userid = User::getUserID($username);

   // Username already exists
   if ($userid) {
      $failpage = new View('signin.phtml');
      $failpage->addPageVariable('userExists', true);
      $failpage->render();
      return;
   }

   // Create new user
   $query = <<<EOT
      INSERT INTO `users`
       (`username`, `password_hash`)
       VALUES (:username, :hash)
EOT;
   $stmt = $db->prepare($query);
   $stmt->bindParam(':username', $username);
   $stmt->bindParam(':hash', $hash);
   $stmt->execute();

   $successpage = new View('signin.phtml');
   $successpage->addPageVariable('signupsuccess', true);
   $successpage->render();
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

$app->post('/message', function() use ($app) {
   $JSONBody = $app->request()->getBody();
   $data = json_decode($JSONBody, true);
   Reddit::message($data['to'],$data['subject'],$data['text']);
});

$app->run();
