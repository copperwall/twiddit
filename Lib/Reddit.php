<?php

define('BASE_URL', 'http://reddit.com');

/**
 * Wrapper class for reddit's API
 */
class Reddit {
   /**
    * Grab the $limit hottest posts from $subreddit.
    * 
    * @param $subreddits - An array of subreddit names.
    * @param $limit (optional) - The number of posts to grab.
    */
   public static function getSubredditPosts($subreddit, $limit = 5) {
      return self::getItems([$subreddit], $limit, 'subreddit');
   }
   
   /**
    * Grab the most recent $limit comments from $users.
    *
    * @param $users array - An array of usernames.
    * @param $limit (optional) - The number of comments to grab.
    */
   public static function getComments(array $users, $limit = 5) {
      return self::getItems($users, $limit, 'comments');
   }

   /**
    * Grab the most recent $limit submissions from $users.
    *
    * @param $users array - An array of usernames.
    * @param $limit (optional) - The number of submissions (posts) to grab.
    */
   public static function getSubmissions(array $users, $limit = 5) {
      return self::getItems($users, $limit, 'submitted');
   }

   /**
    * Favorite a post using Reddit's API.
    */
   public static function favorite($id) {
      $token = Auth::getToken();
      $url = 'https://oauth.reddit.com/api/save';
      $headers = [
         'Authorization' => "bearer $token",
      ];
      $body = "id=$id";

      HTTP::post($url, $body, $headers);
   }

   /**
    * Send message using Reddit's API.
    */
   public static function message($to, $subject, $text) {
      $token = Auth::getToken();

      $url = 'https://oauth.reddit.com/api/compose';
      $headers = [
         'Authorization' => "bearer $token",
      ];
      $body = "to=$to&subject=$subject&text=$text";

      HTTP::post($url, $body, $headers);
   }

   /**
    * A private helper function to grab a list of subreddits, comments, or
    * submissions.
    *
    * @param $sources - An array of sources (comments, submissions, subreddits).
    * @param $limit - The number of items to grab from each source.
    * @param $type - Enum ('subreddit', 'comments', 'submitted')
    */
   private static function getItems(array $sources, $limit, $type) {
      $items = [];

      // Get content, parse into JSON, and add all chilren to items array
      foreach ($sources as $source) {
         // Determine the URL path based on what source are grabbing data from.
         if ($type === 'subreddit') {
            $url_path = "/r/$source/hot.json?limit=$limit";
         } else {
            $url_path = "/user/$source/$type.json?limit=$limit";
         }

         $JSONResponse = null;

         try {
            $JSONResponse = HTTP::get(BASE_URL . $url_path);
         } catch (ErrorException $e) {
            // Shhh no tears, only sleep
            continue;
         }
         $response = json_decode($JSONResponse, /* assoc */ true);
         $children = $response['data']['children'];

         foreach ($children as $child) {
            array_push($items, $child['data']);
         }
      }

      usort($items, "self::sortByTime");
      return $items;
   }

   // Sort items in chronoogical order
   private static function sortByTime($a, $b) {
      return $b['created_utc'] - $a['created_utc'];
   }
}
