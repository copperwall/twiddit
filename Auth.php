<?php

/**
 * This could be an Auth library for storing, doing Oauth stuff, handling 
 * secrets.
 */

define('CLIENT_ID', 'cfRh9fLgIRPyDg');
// NOTE Don't commit this, copy it from the config file
define('CLIENT_SECRET', '');

class Auth {

   // Auth scopes that we need
   private static $scopes = ['save', 'privatemessages'];
   private static $redirect_uri = 'http://twiddit.ddns.net:2000/reddit_callback';

   /**
    * Build the Reddit OAuth link using our ClientID, Redirect URI and permissions.
    */
   public static function buildOAuthRedirectUrl() {
      $scopes = implode(',', self::$scopes);
      $state = 'basic';
      $params = [
         'client_id=' . CLIENT_ID,
         "response_type=code",
         "state=$state",
         "redirect_uri=" . urlencode(self::$redirect_uri),
         "duration=permanent",
         "scope=$scopes"
      ];

      $url = 'https://ssl.reddit.com/api/v1/authorize?';
      $url .= implode('&', $params); 

      return $url;
   }

   public static function getTokenFromAuthCode($code, $state) {
      $opts = [
         'http' => [
            'method' => 'POST',
            'header' => 'Authorization: Basic ' . base64_encode(CLIENT_ID . ':' . CLIENT_SECRET)
                        . "\r\nContent-type: application/x-www-form-urlencoded",
            'content' => "grant_type=authorization_code&code=$code&redirect_uri=" . self::$redirect_uri
         ]
      ];

      $context = stream_context_create($opts);
      $JSONResponse = file_get_contents('https://www.reddit.com/api/v1/access_token', false, $context);
      $response = json_decode($JSONResponse, /* assoc */ true);

      return $response;
   }

   /**
    * This should be used when an existing user authenticates their reddit 
    * account.
    *
    * Should add some row that relates to our "user" and their secret.
    */
   public static function setUserToken($token, $refreshToken, $expires_in) {
      // Need to update the token and the refresh token in the database of said 
      // user.
      $db = TwidditDB::db();
      $user = $_COOKIE['user'];

      $query = <<<EOT
         UPDATE `users` SET
         `redditToken` = :token,
         `redditRefreshToken` = :refreshToken,
         `expires_in` = UNIX_TIMESTAMP() + (:expires_in)
         WHERE `userName` = :user
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':token', $token);
      $statement->bindParam(':refreshToken', $refreshToken);
      $statement->bindParam(':expires_in', $expires_in);
      $statement->bindParam(':user', $user);
      $statement->execute();
   }

   /**
    * This should grab a token for a particular user for user specific things.
    * Needs a check to see if the token has expired.
    * i.e. Saving, messaging, whatever.
    */
   public static function getToken() {
      $db = TwidditDB::db();
      $user = $_COOKIE['user'];

      // If the token has expired, grab a new one with the refresh token and 
      // then reset the token, refresh token, and expiration time.
      if (self::hasExpired()) {
         $authResponse = self::refreshAuthToken();
         // TODO This is not what we need
         self::setUserToken($authResponse['access_token'],
          $authResponse['refresh_token'], $authResponse['expires_in']);
      }

      $query = <<<EOT
         SELECT `redditToken`
         FROM `users`
         WHERE `userName` = :user
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':user', $user);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      return $result['redditToken'];
   }

   /**
    * Grab the refresh token for the current user.
    *
    * This is helpful when the user's auth token has expired.
    */
   private static function getRefreshToken() {
      $db = TwidditDB::db();
      $user = $_COOKIE['user'];

      $query = <<<EOT
         SELECT `redditRefreshToken`
         FROM `users`
         WHERE `userName` = :user
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':user', $user);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      $refreshToken = $result['redditRefreshToken'];
      return $refreshToken;
   }

   private static function hasExpired() {
      $db = TwidditDB::db();
      $user = $_COOKIE['user'];

      $query = <<<EOT
         SELECT `expires_in`, UNIX_TIMESTAMP() AS now
         FROM `users`
         WHERE `userName` = :name
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':name', $user);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      return $result['expires_in'] < $result['now'];
   }

   /**
    * If the user's token has expired, grab a new token and refresh token using
    * the current refresh token.
    */
   private static function refreshAuthToken() {
      $refreshToken = self::getRefreshToken();

      $opts = [
         'http' => [
            'method' => 'POST',
            'header' => 'Authorization: Basic ' . base64_encode(CLIENT_ID . ':' . CLIENT_SECRET)
                        . "\r\nContent-type: application/x-www-form-urlencoded",
            'content' => "grant_type=refresh_token&refresh_token=$refreshToken"
         ]
      ];

      $context = stream_context_create($opts);
      $JSONResponse = file_get_contents('https://www.reddit.com/api/v1/access_token', false, $context);
      $response = json_decode($JSONResponse, /* assoc */ true);
      $response['refresh_token'] = $refreshToken;

      return $response;
   }
}
