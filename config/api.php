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
            echo error('Database connection error: ' . $e->getMessage(), 500);
            exit();
        }
    }

    function jsonResponse($data, $code = 200)
    {
        header("Content-Type: application/json");
        http_response_code($code);

        return json_encode($data);
    }

    function timeZone()
    {
        $timezone = new DateTimeZone('Asia/Jakarta');
        $now = new DateTime('now', $timezone);
        
        return $now->format('Y-m-d H:i:s');
    }
    
    function success($data, $code = "200")
    {
        return jsonResponse([
            'status' => 'Berhasil',
            'code' => $code,
            'data' => $data
        ], $code);
    }

    function error($message, $code = 400)
    {
        return jsonResponse([
            'status' => 'Gagal',
            'code' => $code,
            'message' => $message
        ], $code);
    }