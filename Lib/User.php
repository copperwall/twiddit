<?php

/**
 * Function for dealing with Users
 */
class User {
   public static function getUserID() {
      $db = TwidditDB::db();

      // TODO Find out what to do if no session 
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
}
