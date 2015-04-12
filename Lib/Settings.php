<?php

/**
 * Settings.php
 */

class Settings {
   /**
    * Get the redditors that the current user follows.
    */
   public static function getFollowing() {
      $user = $_COOKIE['user'];
      $db = TwidditDB::db();

      $query = <<<EOT
         SELECT `redditor`
         FROM `redditors_followed`
         WHERE `username` = :username
EOT;
      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
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
      $user = $_COOKIE['user'];
      $db = TwidditDB::db();

      $query = <<<EOT
         SELECT `subreddit`, `preference_value`
         FROM `subreddits_followed`
         WHERE `username` = :username
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
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
      $user = $_COOKIE['user'];
      $db = TwidditDB::db();

      $query = <<<EOT
         INSERT INTO `redditors_followed`
         (`username`, `redditor`)
         VALUES (:username, :redditor)
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
      $statement->bindParam(':redditor', $redditor);
      $statement->execute();
   }

   /**
    * Add a subreddit for a user.
    */
   public static function addSubreddit($subreddit, $preference = 5) {
      $user = $_COOKIE['user'];
      $db = TwidditDB::db();

      $query = <<<EOT
         SELECT * FROM `subreddits_followed`
         WHERE `username` = :username
          AND `subreddit` = :subreddit
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
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
            (`username`, `subreddit`, `preference_value`)
            VALUES (:username, :subreddit, :preferenceValue)
EOT;
         $statement = $db->prepare($query);
         $statement->bindParam(':username', $user);
         $statement->bindParam(':subreddit', $subreddit);
         $statement->bindParam(':preferenceValue', $preference);
         $statement->execute();
      }
      else { 
         $query = <<<EOT
            UPDATE `subreddits_followed`
            SET `preference_value` = :preferenceValue
            WHERE `subreddit` = :subreddit
             AND `username` = :username
EOT;
         $statement = $db->prepare($query);
         $statement->bindParam(':username', $user);
         $statement->bindParam(':subreddit', $subreddit);
         $statement->bindParam(':preferenceValue', $preference);
         $statement->execute();
      }
   }
}
