<?php
class Penitipan {
    function index($user, $jadwal = false) {
        $id_user = $user->data->id;
        $conn = getConnection();
        
        try {
            $now = timeZone('Y-m-d');
            
            if ($jadwal) {
                $query = "SELECT hewan.id_hewan, penitipan.id_penitipan, hewan.nama_hewan, penitipan.tanggal_keluar AS `datetime`
                        FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                        WHERE penitipan.id_user = '$id_user' AND penitipan.tanggal_keluar >= '$now' AND hewan.status_pesan != 'CANCEL' AND transaksi.status = 'SUCCESS'
                        ORDER BY hewan.`datetime` DESC";
            } else {
                $query = "SELECT hewan.id_hewan, penitipan.id_penitipan, hewan.nama_hewan, hewan.datetime
                        FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan
                        WHERE penitipan.id_user = '$id_user' AND penitipan.tanggal_keluar < '$now' AND hewan.status_pesan != 'CANCEL' ORDER BY hewan.`datetime` DESC";
            }

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

    function store($user) {
        try {
            if (isset($_POST['full_name'], $_POST['nama_hewan'], $_POST['jenis_hewan'], $_POST['tgl_masuk'], $_POST['jumlah'],
                    $_POST['tgl_keluar']) && !empty($_POST['full_name']) && !empty($_POST['nama_hewan']) && !empty($_POST['jenis_hewan'])
                    && !empty($_POST['tgl_masuk']) && !empty($_POST['jumlah']) && !empty($_POST['tgl_keluar']))
            {
                $full_name = $_POST['full_name'];
                $nama_hewan = $_POST['nama_hewan'];
                $jenis_hewan = $_POST['jenis_hewan'];
                $jumlah = $_POST['jumlah'];
                
                $tgl_masuk = $_POST['tgl_masuk'];
                $tgl_keluar = $_POST['tgl_keluar'];
                
                $now = timeZone();
                $id_user = $user->data->id;

                $conn = getConnection();
                $query = "INSERT INTO hewan (id_user, jenis, nama_hewan, jumlah, `datetime`) 
                            VALUE ('$id_user', '$jenis_hewan', '$nama_hewan', '$jumlah', '$now')";

                if (mysqli_query($conn, $query)) {
                    $id_hewan = mysqli_insert_id($conn);
                    $query = "INSERT INTO penitipan (id_user, id_hewan, nama_lengkap, nama_hewan, tanggal_masuk, tanggal_keluar) 
                                VALUE ('$id_user', '$id_hewan', '$full_name', '$nama_hewan', '$tgl_masuk', '$tgl_keluar')";

                    $transaction_id = 'WAITING-' . $id_user . '-' . $now . '-' . $jumlah;
                    if (mysqli_query($conn, $query)) {
                        $query = "INSERT INTO transaksi (id_user, invoice, id_hewan, transaction_id, created_at, updated_at) 
                                VALUE ('$id_user', 'WAITING', '$id_hewan', '$transaction_id', '$now', '$now')";
                        
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