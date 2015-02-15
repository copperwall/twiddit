<?php

define('BASE_URL', 'http://reddit.com');

/**
 * Wrapper class for reddit's API
 */
class Reddit {
   /**
    * Grab the $limit hottest posts from $subreddit.
    * 
    * @param $subreddit - The name of a subreddit.
    * @param $limit (optional) - The number of posts to grab.
    */
   public static function getSubredditPosts($subreddit, $limit = 5) {
	   $items = [];
	   
     // Get content, parse into JSON, and add all chilren to items array
	 $apiResult = file_get_contents(BASE_URL . "/r/$subreddit/hot.json?limit=$limit");
	 $JSONresult = json_decode($apiResult, /* assoc */ true);
	 $children = $JSONresult['data']['children'];

	 foreach ($children as $child) {
		array_push($items, $child['data']);
	 }
	 
	 return $items;
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

   private static function getItems(array $users, $limit, $type) {
      $items = [];

      // Get content, parse into JSON, and add all chilren to items array
      foreach ($users as $user) {
         $apiResult = file_get_contents(BASE_URL . "/user/$user/$type.json?limit=$limit");
         $JSONresult = json_decode($apiResult, /* assoc */ true);
         $children = $JSONresult['data']['children'];

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
