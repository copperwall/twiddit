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
         FROM `followingRedditors`
         WHERE `userName` = :username
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
      $statement->execute();
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);

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
         SELECT `subreddit`, `preferenceValue`
         FROM `followingSubreddit`
         WHERE `userName` = :username
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
      $statement->execute();
      $results = $statement->fetchAll(PDO::FETCH_ASSOC);

      $subreddits = [];
      foreach ($results as $row) {
         $subreddits[] = [$row['subreddit'] => $row['preferenceValue']];
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
         INSERT INTO `followingRedditors`
         (`userName`, `redditor`)
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
         INSERT INTO `followingSubreddit`
         (`userName`, `subreddit`, `preferenceValue`)
         VALUES (:username, :subreddit, :preferenceValue)
EOT;

      $statement = $db->prepare($query);
      $statement->bindParam(':username', $user);
      $statement->bindParam(':subreddit', $subreddit);
      $statement->bindParam(':preference', $preference);
      $statement->execute();
   }
}
