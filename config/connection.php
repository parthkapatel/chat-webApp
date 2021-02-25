<?php

class connection
{
    private $serverName = "localhost";
    private $userName = "root";
    private $password = "";
    private $dbName = "chat-webApp";

    private $conn;

    public function __construct()
    {


    }

    public function DBConnect(){


        try {
            $createDB = "CREATE DATABASE IF NOT EXISTS $this->dbName";
            $this->conn = new PDO("mysql:host=$this->serverName",$this->userName,$this->password);
            if($this->conn ->exec($createDB))
                echo '<script>console.log('. json_encode("Database Created", JSON_HEX_TAG) .')</script>';
            if(isset($this->conn))
            {
                if($this->conn ->exec("Use $this->dbName"))
                    echo '<script>console.log('. json_encode("Database Connected", JSON_HEX_TAG) .')</script>';
            }
            $this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

            return $this->conn;
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }
}