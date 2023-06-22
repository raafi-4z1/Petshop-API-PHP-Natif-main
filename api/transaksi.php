<?php
class Transaksi {
    function index($user) {
        $user = json_decode($user, true);
        $id_user = $user['data']['id'];
        $conn = getConnection();
        
        try {
            $query = "SELECT transaksi.id_transaksi, hewan.nama_hewan, (transaksi.tanggal_bayar) AS `datetime`
                        FROM transaksi INNER JOIN hewan ON transaksi.id_hewan = hewan.id_hewan
                        WHERE transaksi.id_user = '$id_user' ORDER BY transaksi.`tanggal_bayar` DESC";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                mysqli_close($conn);

                $data = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }

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

    function viewByIdTransaksi($user) {
        $user = json_decode($user, true);
        $id_user = $user['data']['id'];

        try {
            if (isset($_POST['id_transaksi'], $_POST['is_pemesanan']) && !empty($_POST['id_transaksi']) && !empty($_POST['is_pemesanan'])) {
                $transaksi = $_POST['id_transaksi'];
                $conn = getConnection();
                $data = array();

                if ($_POST['is_pemesanan'] === "false") {
                    $query = "SELECT penitipan.nama_lengkap, penitipan.tanggal_masuk, penitipan.tanggal_keluar, hewan.nama_hewan, hewan.jenis, hewan.jumlah, transaksi.total_harga
                                FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan INNER JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan 
                                WHERE transaksi.id_transaksi = '$transaksi' AND transaksi.id_user = '$id_user' LIMIT 1";
                } else {
                    $query = "SELECT pemesanan.nama_lengkap, pemesanan.tanggal_pemesanan, hewan.nama_hewan, hewan.jenis, hewan.jumlah, transaksi.total_harga
                                FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan INNER JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan 
                                WHERE transaksi.id_transaksi = '$transaksi' AND transaksi.id_user = '$id_user' LIMIT 1";
                }
                $result = mysqli_query($conn, $query);

                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $data[] = $row;
                    }

                    mysqli_close($conn);
                    return success($data);
                } else {
                    mysqli_close($conn);
                    return error(mysqli_error($conn));
                }

            } else {
                return error("Tidak boleh ada yang kosong");
            }

        }  catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }

    }

    function listPembayaran($user) {
        $user = json_decode($user, true);
        $id_user = $user['data']['id'];

        try {
            $conn = getConnection();
            $data = array();

            $query = "SELECT hewan.nama_hewan, penitipan.tanggal_masuk, transaksi.id_transaksi, transaksi.status
                        FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan WHERE penitipan.id_user = '$id_user' ORDER BY hewan.`datetime` DESC";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }

                $query = "SELECT hewan.nama_hewan, pemesanan.tanggal_pemesanan, transaksi.id_transaksi, transaksi.status
                            FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan 
                            WHERE pemesanan.id_user = '$id_user' ORDER BY hewan.`datetime` DESC";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $data[] = $row;
                    }

                    mysqli_close($conn);
                    return success($data);
                } else {
                    mysqli_close($conn);
                    return error(mysqli_error($conn));
                }

            } else {
                mysqli_close($conn);
                return error(mysqli_error($conn));
            }

        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
        
    }

}