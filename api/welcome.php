<?php
class Welcome {
    function login() {
        try {
            if (isset($_POST['username'], $_POST['password']) && !empty($_POST['username']) && !empty($_POST['password']))
            {
                $username = $_POST['username'];
                $password = $_POST['password'];
    
                $conn = getConnection();
                $query = "SELECT `password` FROM `login` WHERE username = '$username' LIMIT 1";
                $query_result = mysqli_fetch_assoc(mysqli_query($conn, $query));
                $result = $query_result['password'] ?? null;
                
                if ($result) {
                    if (password_verify($password, $result)) {
                        $token = md5(time().'.'.md5($username));
                        $token_hash = hash('sha256', $token);
                        $now_local = timeZone();
    
                        $query = "UPDATE `login` SET token = '$token_hash', lastactive = '$now_local' 
                        WHERE username = '$username' && `password` = '$result'";
                        $data = array();
                        $data['token'] = $token;
                        
                        if (mysqli_query($conn, $query)) {
                            mysqli_close($conn);
                            return success($data);
                        } else {
                            mysqli_close($conn);
                            return error(mysqli_error($conn));
                        }

                    } else {
                        mysqli_close($conn);
                        return error("Username atau Password anda salah", 400);
                    }

                } else {
                    mysqli_close($conn);
                    return error("Username atau Password anda salah", 400);
                }
                
            } else {
                return error("Tidak boleh ada yang kosong", 400);
            }

        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
    }
    
    function register() {
        try {
            if (isset($_POST['name'], $_POST['username'], $_POST['phone'], $_POST['password'], $_POST['confirmPassword']) &&
                    !empty($_POST['name']) && !empty($_POST['username']) && !empty($_POST['phone']) && !empty($_POST['password']) && 
                    !empty($_POST['confirmPassword']))
            {
                $result = array();
                $name = $_POST['name'];
                $username = $_POST['username'];

                $phone = $_POST['phone'];
                $password = $_POST['password'];
                $confirm_password = $_POST['confirmPassword'];
    
                if ($password === $confirm_password) {
                    $password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
                    $conn = getConnection();
    
                    $token = md5(time().'.'.md5($username));
                    $token_hash = hash('sha256', $token);
                    $result['token'] = $token;
                    $now_local = timeZone();
    
                    $query = "INSERT INTO `login` (nama, username, `password`, `level`, lastactive, token)
                    VALUES ('$name', '$username', '$password', 'user', '$now_local', '$token_hash')";
                    
                    if (mysqli_query($conn, $query)) {
                        $id_user = mysqli_insert_id($conn); 
                        $query = "INSERT INTO `user` (id_user, username,  nama_lengkap,  telepon)
                                    VALUES ('$id_user', '$username', '$name', '$phone')";
                        
                        if (mysqli_query($conn, $query)) {
                            mysqli_close($conn);
                            return success($result, 201);
                        } else {
                            mysqli_close($conn);
                            return error(mysqli_error($conn));
                        }

                    } else {
                        mysqli_close($conn);
                        return error(mysqli_error($conn));
                    }
                    
                } else {
                    return error("Password dan Confirm Password harus sama", 400);
                }

            } else {
                return error("Tidak boleh ada yang kosong", 400);
            }
            
        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
    }

    function logout($user) {
        $user = json_decode($user, true);
        $id_user = $user['data']['id'];
        $now = timeZone();
        
        try {
            $conn = getConnection();
            $query = "UPDATE `login` SET token = '', lastactive = '$now' WHERE id = '$id_user'";
            
            if (mysqli_query($conn, $query)) {
                mysqli_close($conn);
                return success("Berhasil logout dari aplikasi");
            } else {
                mysqli_close($conn);
                return error(mysqli_error($conn));
            }
            
        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
    }

}