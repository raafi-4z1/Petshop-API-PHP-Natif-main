<?php
class DetailItem {
    function index($user) {
        $id_user = $user->data->id;
        $conn = getConnection();

         // di program transaksi masuk dimana tergantung id_pemesanan atau idd_penitipan
         // dan transaksi hanya muncul jika bayar lunas
         // begitu juga dengan penitipan dan pemesanan jika sudah melewati tanggal terakhir dari batas waktu penitipan, 
         // jika belum maka masuk ke jadwal
        $id_item = $_POST['id_hewan'];
        // yang belum tinggal masukkan transaksi, sebelumnya dicoba dulu transaksinya untuk mengetahui
        // lebih detail tentang cara transaksi dan pembayarannya termasuk pakai qrcode
        // cari midtrans yg support minSdk 21
        $pemesanan = "false";
        if ($_POST['pemesanan']) {
            $pemesanan = "true";
            $query = "SELECT pemesanan.nama_lengkap, pemesanan.telepon, pemesanan.tanggal_pemesanan, hewan.id_hewan,
                        hewan.nama_hewan, hewan.jenis, hewan.jumlah, hewan.harga, transaksi.jenis_pembayaran, transaksi.created_at, transaksi.status
                        FROM pemesanan INNER JOIN `user` ON pemesanan.id_user = `user`.id_user INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan 
                        LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                        WHERE `user`.id_user = '$id_user' AND pemesanan.id_hewan = '$id_item' LIMIT 1";
        } else {
            $query = "SELECT penitipan.nama_lengkap, `user`.telepon, penitipan.tanggal_masuk, penitipan.tanggal_keluar, hewan.id_hewan,
                        hewan.nama_hewan, hewan.jenis, hewan.jumlah, hewan.harga, transaksi.jenis_pembayaran, transaksi.created_at, transaksi.status
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
}