<?php
define('HOST','localhost');
define('USER','root');
define('DB','petshop');
define('PASS','');

    function getConnection()
    {
        try {
            $conn = new mysqli(HOST, USER, PASS, DB);
            
            if ($conn->connect_error) {
                throw new mysqli_sql_exception($conn->connect_error);
            }
            
            return $conn;
        } catch (mysqli_sql_exception $e) {
            return error('Database connection error: ' . $e->getMessage());
            exit();
        }
    }
