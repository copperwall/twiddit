<?php

/**
 * Settings.php
 */

class Settings {
   /**
    * Get the redditors that the current user follows.
    */
   public static function getFollowing() {
      $db = TwidditDB::db();
      $userid = User::getUserID();

      $query = <<<EOT
         SELECT `redditor`
         FROM `redditors_followed`
         WHERE `userid` = :userid
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':userid', $userid);
      $statement->execute();
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);

      return array_map(function($row) {
         return $row['redditor'];
      }, $results);
   }

   /**
    * Get the subreddits and their preferences of the current user.
    */
   public static function getSubreddits() {
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

      return array_map(function($row) {
         return [
            'subreddit' => $row['subreddit'],
            'preferenceValue' => $row['preference_value']
         ];
      }, $results);
   }

   /**
    * Add a redditor to follow for a user.
    */
   public static function addFollowing($redditor) {
      $db = TwidditDB::db();
      $userid = User::getUserID();

      $query = <<<EOT
         INSERT INTO `redditors_followed`
         (`userid`, `redditor`)
         VALUES (:userid, :redditor)
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':userid', $userid);
      $statement->bindParam(':redditor', $redditor);
      $statement->execute();
   }

   /**
    * Add a subreddit for a user.
    */
   public static function addSubreddit($subreddit, $preference = 5) {
      $db = TwidditDB::db();
      $userid = User::getUserID();

      $query = <<<EOT
         SELECT * FROM `subreddits_followed`
         WHERE `userid` = :userid
          AND `subreddit` = :subreddit
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':userid', $userid);
      $statement->bindParam(':subreddit', $subreddit);
      $statement->execute();
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);

      if (!count($results)) {
         $query = <<<EOT
            INSERT INTO `subreddits_followed`
            (`userid`, `subreddit`, `preference_value`)
            VALUES (:userid, :subreddit, :preferenceValue)
EOT;
         $statement = $db->prepare($query);
         $statement->bindParam(':userid', $userid);
         $statement->bindParam(':subreddit', $subreddit);
         $statement->bindParam(':preferenceValue', $preference);
         $statement->execute();
      } else {
         $query = <<<EOT
            UPDATE `subreddits_followed`
            SET `preference_value` = :preferenceValue
            WHERE `subreddit` = :subreddit
             AND `userid` = :userid
EOT;
         $statement = $db->prepare($query);
         $statement->bindParam(':userid', $userid);
         $statement->bindParam(':subreddit', $subreddit);
         $statement->bindParam(':preferenceValue', $preference);
         $statement->execute();
      }
   }
}
