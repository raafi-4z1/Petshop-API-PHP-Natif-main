<?php
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

    function error($message, $code = 500)
    {
        return jsonResponse([
            'status' => 'Gagal',
            'code' => $code,
            'message' => $message
        ], $code);
    }