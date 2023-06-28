<?php
class Pemesanan {
    function index($user) {
        $user = json_decode($user, true);
        $id_user = $user['data']['id'];
        $conn = getConnection();
        
        try {
            $query = "SELECT pemesanan.id_pemesanan, hewan.nama_hewan, hewan.datetime
                        FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan
                        WHERE pemesanan.id_user = '$id_user' ORDER BY hewan.`datetime` DESC";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                if (mysqli_num_rows($result) == 0) {
                    mysqli_close($conn);
                    return success($result, 204);
                }

                $data = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }
                // mysqli_free_result($result);

                mysqli_close($conn);
                return success($data);
            } else {
                mysqli_close($conn);
                return error(mysqli_error($conn));
            }

        } catch (mysqli_sql_exception $e) {
            mysqli_close($conn);
            return error(strval($e));
        }
    }

    function store($user) {
        try {
            if (isset($_POST['full_name'], $_POST['email'], $_POST['phone'], $_POST['alamat'],
                    $_POST['nama_hewan'], $_POST['jenis_hewan'], $_POST['tgl_pesan'], $_POST['jumlah']) &&
                    !empty($_POST['full_name']) && !empty($_POST['email']) && !empty($_POST['phone']) && !empty($_POST['alamat']) &&
                    !empty($_POST['nama_hewan']) && !empty($_POST['jenis_hewan']) && !empty($_POST['tgl_pesan']) && !empty($_POST['jumlah']))
            {
                $full_name = $_POST['full_name'];
                $email = $_POST['email'];
                $phone = $_POST['phone'];
                $alamat = $_POST['alamat'];

                $nama_hewan = $_POST['nama_hewan'];
                $jenis_hewan = $_POST['jenis_hewan'];
                $jumlah = $_POST['jumlah'];
                $tgl_pesan = $_POST['tgl_pesan'];

                $now = timeZone();

                $user = json_decode($user, true);
                $id_user = $user['data']['id'];

                $conn = getConnection();
                $query = "INSERT INTO hewan (id_user, jenis, nama_hewan, jumlah, `datetime`) 
                            VALUE ('$id_user', '$jenis_hewan', '$nama_hewan', '$jumlah', '$now')";

                if (mysqli_query($conn, $query)) {
                    $id_hewan = mysqli_insert_id($conn); // Mendapatkan ID hewan yang baru saja diinsert
                    $query = "INSERT INTO pemesanan (id_user, id_hewan, nama_lengkap, tanggal_pemesanan, email, telepon, alamat) 
                                VALUE ('$id_user', '$id_hewan', '$full_name', '$tgl_pesan', '$email', '$phone', '$alamat')";
                    
                    if (mysqli_query($conn, $query)) {
                        mysqli_close($conn);
                        return success("Berhasil diinput, tunggu admin mengkonfirmasi dan lakukan pembayaran", 201);
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
    
}