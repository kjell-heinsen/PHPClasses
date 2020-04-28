<?php
namespace core\main;

class Session {

   private static $_sessionStarted = false;

   
   // Initialisierung der Session
   public static function init() {
      if (self::$_sessionStarted == false) {
         session_start();
         self::$_sessionStarted = true;
      }
   }

   // Setzen der Session mit einem Schluessel-Wert Paar
   // Definiertes SESSION_PREFIX wird dabei vor den jeweiligen Schluessel gesetzt.
   public static function set($key, $value) {
      return $_SESSION[SESSION_PREFIX . $key] = $value;
   }

   //Abrufen eines Wertes mit dem entsprechenden Schluessel und eventuellem Zweitschluessel
   public static function get($key, $secondkey = false) {
      if ($secondkey == true) {
         if (isset($_SESSION[SESSION_PREFIX . $key][$secondkey])) {
            return $_SESSION[SESSION_PREFIX . $key][$secondkey];
         }
      } else {
         if (isset($_SESSION[SESSION_PREFIX . $key])) {
            return $_SESSION[SESSION_PREFIX . $key];
         }
      }
      return false;
   }

   //Anzeige der Session
   public static function display() {
      return $_SESSION;
   }

   //Entsprechenden Schluessel der Session leeren
   public static function clear($key) {
      unset($_SESSION[SESSION_PREFIX . $key]);
   }

   //Session zerstoeren und aufloesen, aber nur wenn eine Session gestartet wurde.
   public static function destroy() {
      if (self::$_sessionStarted == true) {
         session_unset(); // Entfernt alle gesetzten Variablen einer Session
         session_destroy(); // Beendet die gestartete Session.
      }
   }

}
