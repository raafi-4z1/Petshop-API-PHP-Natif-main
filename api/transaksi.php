<?php
class Transaksi {
    function index($user) {
        $id_user = $user->data->id;
        $conn = getConnection();
        
        try {
            $query = "SELECT hewan.id_hewan, hewan.nama_hewan, (transaksi.created_at) AS `datetime`, pemesanan.id_pemesanan, penitipan.id_penitipan
                        FROM transaksi INNER JOIN hewan ON transaksi.id_hewan = hewan.id_hewan 
                        LEFT JOIN penitipan ON penitipan.id_hewan = hewan.id_hewan LEFT JOIN pemesanan ON pemesanan.id_hewan = hewan.id_hewan 
                        WHERE transaksi.id_user = '$id_user' AND transaksi.status = 'SUCCESS' ORDER BY transaksi.`created_at` DESC";
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

    function listPembayaran($user) {
        $id_user = $user->data->id;

        try {
            $conn = getConnection();
            $data = array();

            $query = "SELECT hewan.id_hewan, hewan.nama_hewan, penitipan.tanggal_masuk, transaksi.status
                        FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan 
                        LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan WHERE penitipan.id_user = '$id_user' 
                        ORDER BY hewan.`datetime` DESC";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }

                $query = "SELECT hewan.id_hewan, hewan.nama_hewan, pemesanan.tanggal_pemesanan, transaksi.status
                            FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan 
                            LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan 
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