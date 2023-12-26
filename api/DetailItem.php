<?php
class DetailItem {
    function index($user) {
        $id_user = $user->id;
        $conn = getConnection();
        
        $id_item = $_POST['id_hewan'];
        $pemesanan = "false";

        if ($_POST['pemesanan']) {
            $pemesanan = "true";
            $query = "SELECT pemesanan.nama_lengkap, pemesanan.telepon, pemesanan.email, pemesanan.alamat, pemesanan.tanggal_pemesanan,
                        hewan.id_hewan, hewan.nama_hewan, hewan.jenis, hewan.jumlah, transaksi.jenis_pembayaran, pemesanan.harga, transaksi.va_number,
                        transaksi.tanggal_bayar, transaksi.status, hewan.status_pesan
                        FROM pemesanan INNER JOIN `user` ON pemesanan.id_user = `user`.id_user INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan 
                        LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                        WHERE `user`.id_user = '$id_user' AND pemesanan.id_hewan = '$id_item' LIMIT 1";
        } else {
            $query = "SELECT penitipan.nama_lengkap, `user`.telepon, `user`.email, `user`.alamat, penitipan.tanggal_masuk, penitipan.tanggal_keluar,
                        hewan.id_hewan, hewan.nama_hewan, hewan.jenis, hewan.jumlah, transaksi.jenis_pembayaran, penitipan.harga, transaksi.va_number, 
                        transaksi.tanggal_bayar, transaksi.status, hewan.status_pesan
                        FROM penitipan INNER JOIN `user` ON penitipan.id_user = `user`.id_user INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan 
                        LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                        WHERE `user`.id_user = '$id_user' AND penitipan.id_hewan = '$id_item' LIMIT 1";
        }
        
        try {
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                if (mysqli_num_rows($result) == 0) {
                    mysqli_close($conn);
                    return success($result, 204);
                }

                $data = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $data = $row;
                }
                $data['pemesanan'] = $pemesanan;
                
                mysqli_close($conn);
                return success(json_decode(json_encode($data)));
            } else {
                mysqli_close($conn);
                return error(mysqli_error($conn));
            }

        } catch (mysqli_sql_exception $e) {
            mysqli_close($conn);
            return error(strval($e));
        }
    }
    
    function updateCancel($user) {
        try {
            if (isset($_POST['status_pesan'], $_POST['id_hewan']) && !empty($_POST['status_pesan']) && !empty($_POST['id_hewan'])) {
                $id_user = $user->id;
                $conn = getConnection();
                $now = timeZone();

                $id_hewan = $_POST['id_hewan'];
                $status_pesan = $_POST['status_pesan'];
                $query = "UPDATE hewan SET status_pesan = '$status_pesan', `datetime` = '$now' WHERE id_hewan = '$id_hewan' AND id_user = '$id_user'";
                
                if (mysqli_query($conn, $query)) {
                    mysqli_close($conn);
                    return success("order berhasil di-cancel");
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