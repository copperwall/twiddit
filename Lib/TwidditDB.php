<?php

/**
 * Wrapper class for the PDO connection.
 */
class TwidditDB {
   private static $db_host = "mydbinstance.cqp85dchbqjz.us-west-2.rds.amazonaws.com;port=3306";
   private static $db_name = "twiddit";
   private static $db_user = "stanley";
   private static $db_pass = "ims01337";
   private static $db = null;

   /**
    * Return the PDO instance, or create one if it does not exist.
    */
   public static function db() {
      // Construct if it does not exist.
      return self::$db ?: (self::$db = self::init());
   }

   /**
    * Create a connection to the DB.
    */
   public static function init() {
      try {
         $connect_string = "mysql:host=" . self::$db_host . ";dbname=" . self::$db_name;
         self::$db = new PDO($connect_string, self::$db_user, self::$db_pass);
         // set the PDO error mode to exception
         self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
         echo "Connection failed: " . $e->getMessage();
      }

      return self::$db;
   }
}
