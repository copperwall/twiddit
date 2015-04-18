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

      // TODO Make this an array_map
      $redditors = [];
      foreach ($results as $row) {
         $redditors[] = $row['redditor'];
      }
      return $redditors;
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

      $subreddits = [];
      foreach ($results as $row) {
         $subreddits[] = [
            'subreddit' => $row['subreddit'],
            'preferenceValue' => $row['preference_value']
         ];
      }
      return $subreddits;
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

      // What even?
      // TODO Replace a count check or something
      $exists = 0;
      foreach($results as $row) {
          $exists = 1;
      } 

      if ($exists == 0) {
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
      }
      else { 
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
