<?php

/**
 * This could be an Auth library for storing, doing Oauth stuff, handling
 * secrets.
 */

class Auth {
   // Auth scopes that we need
   private static $scopes = ['save', 'privatemessages'];

   /**
    * Build the Reddit OAuth link using our ClientID, Redirect URI and permissions.
    */
   public static function buildOAuthRedirectUrl() {
      $config = self::getCredentials();
      $scopes = implode(',', self::$scopes);
      $state = 'basic';
      $params = [
         "client_id={$config['client_id']}",
         "response_type=code",
         "state=$state",
         "redirect_uri=" . urlencode($config['redirect_uri']),
         "duration=permanent",
         "scope=$scopes"
      ];

      $url = 'https://ssl.reddit.com/api/v1/authorize?';
      $url .= implode('&', $params);

      return $url;
   }

   public static function getTokenFromAuthCode($code, $state) {
      $url = 'https://www.reddit.com/api/v1/access_token';
      $headers = [
         'Authorization' => 'Basic '
          . base64_encode("{$config['client_id']}:{$config['client_secret']}"),
      ];
      $body = "grant_type=authorization_code&code=$code&redirect_uri="
       . $config['redirect_uri'];

      $JSONResponse = HTTP::post($url, $body, $headers);
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
      $userid = User::getUserID();

      $query = <<<EOT
         UPDATE `users` SET
         `reddit_token` = :token,
         `reddit_refresh_token` = :refreshToken,
         `expires_in` = UNIX_TIMESTAMP() + (:expires_in)
         WHERE `userid` = :userid
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':token', $token);
      $statement->bindParam(':refreshToken', $refreshToken);
      $statement->bindParam(':expires_in', $expires_in);
      $statement->bindParam(':userid', $userid);
      $statement->execute();
   }

   /**
    * This should grab a token for a particular user for user specific things.
    * Needs a check to see if the token has expired.
    * i.e. Saving, messaging, whatever.
    */
   public static function getToken() {
      $db = TwidditDB::db();
      $userid = User::getUserID();

      // If the token has expired, grab a new one with the refresh token and
      // then reset the token, refresh token, and expiration time.
      if (self::hasExpired()) {
         $authResponse = self::refreshAuthToken();
         // TODO This is not what we need
         self::setUserToken($authResponse['access_token'],
          $authResponse['refresh_token'], $authResponse['expires_in']);
      }

      $query = <<<EOT
         SELECT `reddit_token`
         FROM `users`
         WHERE `userid` = :userid
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':userid', $userid);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      return $result['reddit_token'];
   }

   /**
    * Grab the refresh token for the current user.
    *
    * This is helpful when the user's auth token has expired.
    */
   private static function getRefreshToken() {
      $db = TwidditDB::db();
      $userid = User::getUserID();

      $query = <<<EOT
         SELECT `reddit_refresh_token`
         FROM `users`
         WHERE `userid` = :userid
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':userid', $userid);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      $refreshToken = $result['reddit_refresh_token'];
      return $refreshToken;
   }

   private static function hasExpired() {
      $db = TwidditDB::db();
      $userid = User::getUserID();

      $query = <<<EOT
         SELECT `expires_in`, UNIX_TIMESTAMP() AS now
         FROM `users`
         WHERE `userid` = :userid
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':userid', $userid);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      return $result['expires_in'] < $result['now'];
   }

   /**
    * If the user's token has expired, grab a new token and refresh token using
    * the current refresh token.
    */
   private static function refreshAuthToken() {
      $config = self::getCredentials();
      $refreshToken = self::getRefreshToken();

      $url = 'https://www.reddit.com/api/v1/access_token';
      $headers = [
         'Authorization' => 'Basic '
          . base64_encode("{$config['client_id']}:{$config['client_secret']}"),
      ];
      $body = "grant_type=refresh_token&refresh_token=$refreshToken";

      $JSONResponse = HTTP::post($url, $body, $headers);
      $response = json_decode($JSONResponse, /* assoc */ true);
      $response['refresh_token'] = $refreshToken;

      return $response;
   }

   /**
    * Get oauth credentials from config.json.
    */
   private static function getCredentials() {
      $JSONConfig = file_get_contents(CONFIG_FILE);
      $config = json_decode($JSONConfig, /* assoc */ true);

      return $config['oauth'];
   }
}
