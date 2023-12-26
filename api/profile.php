<?php
class Profile {
    function index($user) {
        $id_user = $user->id;

        try {
            $conn = getConnection();
            $data = [];

            $queries = [
                "penitipan" => "SELECT COUNT(penitipan.id_user) AS jumlah FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan
                                    INNER JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                                    WHERE penitipan.id_user = '$id_user' AND transaksi.status = 'SUCCESS' AND hewan.status_pesan = 'APPROVE'",
                "pemesanan" => "SELECT COUNT(pemesanan.id_user) AS jumlah FROM pemesanan inner join hewan on pemesanan.id_hewan = hewan.id_hewan
                                    inner join transaksi on transaksi.id_hewan = hewan.id_hewan
                                    WHERE pemesanan.id_user = '$id_user' and transaksi.status = 'SUCCESS' and hewan.status_pesan = 'APPROVE'",
                "transaksi" => "SELECT COUNT(id_user) AS jumlah FROM transaksi WHERE id_user = '$id_user' AND transaksi.status = 'SUCCESS'"
            ];
            
            foreach ($queries as $key => $query) {
                $result = mysqli_query($conn, $query);
                if (!$result) {
                    mysqli_close($conn);
                    return error(mysqli_error($conn));
                }

                $data[$key] = mysqli_fetch_assoc($result)['jumlah'];
            }

            mysqli_close($conn);
            return success($data);

        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
    }

    function updateProfile($user) {
        try {
            if (isset($_POST['full_name'], $_POST['email'], $_POST['phone'], $_POST['alamat']) && !empty($_POST['full_name']) &&
                 !empty($_POST['email']) && !empty($_POST['phone']) && !empty($_POST['alamat']))
            {
                $full_name = $_POST['full_name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $alamat = $_POST['alamat'];

                $id_user = $user->id;

                $conn = getConnection();
                $query = "UPDATE `login` SET namalengkap = '$full_name' WHERE id = '$id_user'";

                if (mysqli_query($conn, $query)) {
                    $query = "UPDATE `user` SET  nama_lengkap = '$full_name', telepon = '$phone', 
                                email = '$email', alamat = '$alamat' WHERE id_user = '$id_user'";

                    if (mysqli_query($conn, $query)) {
                        mysqli_close($conn);
                        return success("Data Profile berhasil di-update");
                    } else {
                        mysqli_close($conn);
                        return error(mysqli_error($conn));
                    }

                } else {
                    mysqli_close($conn);
                    return error(mysqli_error($conn));
                }

            } else {
                return error("Tidak boleh ada yang kosong", 400);
            }
        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
    }

    function updatePassword($user) {
        try {
            if (isset($_POST['oldPassword'], $_POST['newPassword'], $_POST['confirmNewPassword']) &&
                !empty($_POST['oldPassword']) && !empty($_POST['newPassword']) && !empty($_POST['confirmNewPassword']))
            {
                $old_password = $_POST['oldPassword'];
                $new_password = $_POST['newPassword'];
                $confirm_new_password = $_POST['confirmNewPassword'];
    
                if ($new_password === $confirm_new_password) {
                    $id_user = $user->id;

                    $conn = getConnection();
                    $query = "SELECT `password` FROM `login` WHERE id = '$id_user' LIMIT 1";
                    $query_result = mysqli_fetch_assoc(mysqli_query($conn, $query));

                    if($query_result['password'] && isset($query_result['password'])) {
                        $result = $query_result['password'];
                    } else {
                        $result = null;
                    }
                    
                    if ($result) {
                        if (password_verify($old_password, $result)) {
                            $new_password = password_hash($new_password, PASSWORD_BCRYPT, array('cost' => 12));
                            $query = "UPDATE `login` SET `password` = '$new_password' WHERE id = '$id_user'";
        
                            if (mysqli_query($conn, $query)) {
                                mysqli_close($conn);
                                return success("Password berhasil di-update");
                            } else {
                                mysqli_close($conn);
                                return error(mysqli_error($conn));
                            }

                        } else {
                            mysqli_close($conn);
                            return error("Password lama anda salah", 400);
                        }

                    } else {
                        mysqli_close($conn);
                        return error($result);
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

}