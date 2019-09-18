<?php

class dbConnect{  
  private $hostName;
  private $userName;
  private $passCode;
  private $connection;
  private $db;

  function makeConnection(){

    $host = 'localhost';
    $db = "";
    $user = '';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $opt = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
      $connection = new PDO($dsn, $user, $pass, $opt);
      return $connection;
    } catch (\PDOException $e) {
      throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
  }
  
  function closeConnection(){
    $connection = null;
  }

} 

	
?>