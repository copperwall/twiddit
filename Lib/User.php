<?php

/**
 * Function for dealing with Users
 */
class User {
   public static function getUserID($username = '') {
      if ($username) {
         return self::getUserIDFromName($username);
      } else {
         return self::getUserIDFromSession();
      }
   }

   public static function getUserName($userid = '') {
      $db = TwidditDB::db();

      if (!$userid) {
         $userid = User::getUserID();
      }

      $query = <<<EOT
         SELECT `username`
         FROM `users`
         WHERE `userid` = :userid
EOT;
      $stmt = $db->prepare($query);
      $stmt->bindParam(':userid', $userid);
      $stmt->execute();

      $result = $stmt->fetch();
      return $result['username'];
   }

   public static function isValidSession($sessionid) {
      $db = TwidditDB::db();

      $query = <<<EOT
         SELECT COUNT(*) AS `valid_sessions`
         FROM `sessions`
         WHERE `sessionid` = :sessionid
          AND `expires_in` > UNIX_TIMESTAMP()
EOT;
      $stmt = $db->prepare($query);
      $stmt->bindParam(':sessionid', $sessionid);
      $stmt->execute();
      $result = $stmt->fetch();

      return $result['valid_sessions'] == 1;
   }

   /**
    * If a user is authenitcated, give them a new session
    */
   public static function newSession($userid) {
      $db = TwidditDB::db();
      // get username
      $username = User::getUsername($userid);
      // generate hash
      $sessionid = self::generateSession($userid, $username);
      // insert hash into session table with userid.

      $query = <<<EOT
         INSERT INTO `sessions`
         (`sessionid`, `userid`, `expires_in`)
         VALUES (:sessionid, :userid, UNIX_TIMESTAMP() + 2592000)
EOT;
      $stmt = $db->prepare($query);
      $stmt->bindParam(':sessionid', $sessionid);
      $stmt->bindParam(':userid', $userid);
      $stmt->execute();

      return $sessionid;
   }

   private static function getUserIDFromName($username) {
      $db = TwidditDB::db();

      $query = <<<EOT
         SELECT `userid`
         FROM `users`
         WHERE `username` = :username
EOT;
      $stmt = $db->prepare($query);
      $stmt->bindParam(':username', $username);
      $stmt->execute();

      $result = $stmt->fetch();
      return $result['userid'];
   }

   private static function getUserIDFromSession() {
      $db = TwidditDB::db();

      if (!array_key_exists('session', $_COOKIE)) {
         return null;
      }
      $session = $_COOKIE['session'];

      $query = <<<EOT
         SELECT `userid`
         FROM `sessions`
         WHERE `sessionid` = :session
EOT;
      $stmt = $db->prepare($query);
      $stmt->bindParam(':session', $session);
      $stmt->execute();

      // If no userid is found, return null
      $results = $stmt->fetch();
      return $results ? $results['userid'] : null;
   }

   /**
    * Hash a concatenation of userid, username, and the current timestamp.
    */
   private static function generateSession($userid, $username) {
      return hash('sha256', $userid . $username . time());
   }
}
