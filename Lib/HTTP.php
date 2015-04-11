<?php

/**
 * This is a way to abstract out a lot of the GETs and POSTs the app does.
 */

define('USER_AGENT', 'twiddit:v0.5 (by /u/Zolokar)');

class HTTP {

   /**
    * Performs a GET request and returns the response body.
    *
    * @param $url string - The url to send the request to.
    * @return The response body.
    */
   public static function get($url, $body = '', $headers = []) {
      return self::makeRequest('GET', $url, $body, $headers);
   }

   /**
    * Perform a POST request on a specific url.
    *
    * @param $url string - The url to send the request to.
    * @param $headers Array - An associative array of headers.
    *    (ex. ['Content-type' => 'raw', 'Authorization' => 'Basic user:pass'])
    * @param $body string - The raw text of the request body.
    * @return The response body.
    */
   public static function post($url, $body = '', $headers = []) {
      return self::makeRequest('POST', $url, $body, $headers);
   }

   private static function makeRequest($method, $url, $body, $headers) {
      // Add our user agent to the headers, along with other defaults
      $defaultHeaders = [
         'Content-type' => 'application/x-www-form-urlencoded',
         'User-Agent' => USER_AGENT
      ];
      array_merge($headers, $defaultHeaders);

      $opts = [
         'http' => [
            'method' => $method,
            'header' => self::buildHeaders($headers),
            'content' => $body
         ]
      ];

      $context = stream_context_create($opts);
      return file_get_contents($url, false, $context);
   }

   /**
    * Build the headers assoc. array into a header string.
    */
   private static function buildHeaders(array $headers) {
      $builtHeaders = array_map(function($key, $value) {
         return "$key: $value";
      }, array_keys($headers), $headers);

      return implode($builtHeaders, "\r\n");
   }
}
