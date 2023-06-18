<?php
class Penitipan {
    function index($user) {
        $user = json_decode($user, true);
        $id_user = $user['data']['id'];
        $conn = getConnection();
        
        try {
            $query = "SELECT penitipan.nama_lengkap, penitipan.tanggal_masuk, penitipan.tanggal_keluar, 
                        hewan.id_hewan, hewan.nama_hewan, hewan.jenis, hewan.jumlah, transaksi.id_transaksi
                        FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan LEFT JOIN transaksi 
                        ON transaksi.id_hewan = hewan.id_hewan WHERE penitipan.id_user = '$id_user' ORDER BY hewan.`datetime` DESC";
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

                $user = json_decode($user, true);
                $id_user = $user['data']['id'];

                $conn = getConnection();
                $query = "INSERT INTO hewan (id_user, jenis, nama_hewan, jumlah, `datetime`) 
                            VALUE ('$id_user', '$jenis_hewan', '$nama_hewan', '$jumlah', '$now')";

                if (mysqli_query($conn, $query)) {
                    $id_hewan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_hewan FROM hewan WHERE id_user = '$id_user' 
                                    ORDER BY id_hewan DESC LIMIT 1"))['id_hewan'];
                    $query = "INSERT INTO penitipan (id_user, id_hewan, nama_lengkap, nama_hewan, tanggal_masuk, tanggal_keluar) 
                                VALUE ('$id_user', '$id_hewan', '$full_name', '$nama_hewan', '$tgl_masuk', '$tgl_keluar')";
                    
                    if (mysqli_query($conn, $query)) {
                        mysqli_close($conn);
                        return success("Berhasil diinput, tunggu admin mengkonfirmasi dan lakukan pembayaran");
                    } else {
                        mysqli_close($conn);
                        return error(mysqli_error($conn));
                    }

                } else {
                    mysqli_close($conn);
                    return error(mysqli_error($conn));
                }

            } else {
                return error("Tidak boleh ada yang kosong");
            }
            
        } catch (mysqli_sql_exception $e) {
            return error(strval($e));
        }
    }

}