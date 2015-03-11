<?php

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    // set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    /* echo "Connected successfully"; */
    }
catch(PDOException $e)
    {
       echo "Connection failed: " . $e->getMessage();
    }

