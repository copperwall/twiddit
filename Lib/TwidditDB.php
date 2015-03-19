<?php

/**
 * Wrapper class for the PDO connection.
 */
class TwidditDB {
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
      $host = $name = $user = $pass = null;
      $db_config = self::getCredentials();
      extract($db_config, EXTR_IF_EXISTS);

      try {
         $connect_string = "mysql:host=$host;dbname=$name";
         self::$db = new PDO($connect_string, $user, $pass);
         // set the PDO error mode to exception
         self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
         echo "Connection failed: " . $e->getMessage();
      }

      return self::$db;
   }

   /**
    * Grab DB credentials from config.json.
    */
   private static function getCredentials() {
      $JSONConfig = file_get_contents(CONFIG_FILE);
      $config = json_decode($JSONConfig, /* assoc */ true);

      return $config['db'];
   }
}
