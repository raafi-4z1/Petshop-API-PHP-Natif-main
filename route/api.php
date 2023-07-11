<?php
    include('../config/Api.php');
    include('../config/ApiHelper.php');
    include('../api/Welcome.php');
    include('../api/Profile.php');
    include('../api/Penitipan.php');
    include('../api/Pemesanan.php');
    include('../api/DetailItem.php');
    include('../api/Transaksi.php');

    $welcome = new Welcome();
    $profile = new Profile();
    $penitipan = new Penitipan();
    $pemesanan = new Pemesanan();
    $detailitem = new DetailItem();
    $transaksi = new Transaksi();

    $petshop_dir = strtolower('/petshop%20-%20Copy/Petshop-API-PHP-Natif-main'); // ? hapus jika tidak diperlukan. ('Nama Folder root')
    $request_path = strtolower($_SERVER['REQUEST_URI']); // * cek!!! apakah sever menerima request seperti /api/...
    $base_path = $petshop_dir . '/api'; // ? '$petshop_dir', hapus jika tidak diperlukan
    $sub_user = '/user';
    
    $api_path = str_replace($base_path, '', $request_path);
    $api_path = rtrim($api_path, '/');

    // Route user/...
    if (strpos($api_path, $sub_user) !== false) {
        $sub_path = str_replace($sub_user, '', $api_path);
        $api_path = str_replace($sub_path, '', $api_path);
    }

    // Handle the API routes
    switch ($api_path) {
        case '/login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo $welcome->login();
            } else {
                invalidHTTP();
            }

            break;
        case '/register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo $welcome->register();
            } else {
                invalidHTTP();
            }

            break;
        case '/logout':
            $token_api = cekToken();
            if ($token_api === null) {
                echo error('Token tidak valid', 401);
            } else {
                echo $welcome->logout(json_decode($token_api));
            }
            
            break;
        case '/user':
            $token_api = cekToken();
            if ($token_api === null) {
                echo error('Token tidak valid', 401);
            } else {
                switch ($sub_path) {
                    case '/home':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $token_api;
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/profile':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $profile->index(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/updateprofile':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $profile->updateProfile(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/updatepassword':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $profile->updatePassword(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/penitipan':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $penitipan->store(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/viewpenitipan':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $penitipan->index(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/pemesanan':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $pemesanan->store(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/viewpemesanan':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $pemesanan->index(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/viewtransaksi':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $transaksi->index(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/listpembayaran':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $transaksi->listPembayaran(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                    case '/detailitem':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            echo $detailitem->index(json_decode($token_api));
                        } else {
                            invalidHTTP();
                        }

                        break;
                        
                    default:
                        invalidRoute($sub_path);

                        break;
                }
            }

            break;
        default:
            invalidRoute($api_path);

            break;
    }

    function invalidHTTP()
    {
        echo error('Invalid HTTP method', 405);
    }

    function invalidRoute($string)
    {
        echo error("API route ($string) not found", 404);
    }

    function cekToken()
    {
        // Mendapatkan token dari header Authorization
        $token = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = extractBearerToken($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $token = extractBearerToken($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $token = extractBearerToken($headers['Authorization']);
            }
        }

        // Memeriksa keaslian token
        if (!empty($token)) {
            return validateToken(hash('sha256', $token));
        } 

        return null;
    }

    // Fungsi untuk memisahkan token dari string "Bearer Token"
    function extractBearerToken($authorization_header) {
        $token = null;
        $header_parts = explode(' ', $authorization_header);
        if (count($header_parts) === 2 && $header_parts[0] === 'Bearer') {
            $token = $header_parts[1];
        }

        return $token;
    }

    // Fungsi untuk memvalidasi token
    function validateToken($token) {
        $conn = getConnection();
        $query = "SELECT `login`.id, `user`.nama_lengkap, `user`.telepon, `user`.email, `user`.alamat
                    FROM `login` INNER JOIN `user` ON `login`.id = `user`.id_user WHERE `login`.token = '$token'";

        $query_result = mysqli_fetch_assoc(mysqli_query($conn, $query));
        mysqli_close($conn);
        $result = $query_result['id'] ?? null;

        if ($result !== null) {
            return success($query_result);
        }

        return null;
    }