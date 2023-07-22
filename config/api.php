<?php
    function getConnection()
    {
        try {
                            // * HOST, USER, PASS, DB
            $conn = new mysqli('localhost', 'root', '', 'id20948273_petshop');
            
            if ($conn->connect_error) {
                throw new mysqli_sql_exception($conn->connect_error);
            }
            
            return $conn;
        } catch (mysqli_sql_exception $e) {
            return error('Database connection error: ' . $e->getMessage());
            exit();
        }
    }
