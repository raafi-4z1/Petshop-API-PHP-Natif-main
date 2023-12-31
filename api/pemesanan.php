<?php
class Pemesanan {
    function index($user, $jadwal = false) {
        $id_user = $user->id;
        $conn = getConnection();
        
        try {
            $now = timeZone('Y-m-d');

            if ($jadwal) {
                $query = "SELECT hewan.id_hewan, pemesanan.id_pemesanan, hewan.nama_hewan, pemesanan.tanggal_pemesanan AS `datetime`
                        FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                        WHERE pemesanan.id_user = '$id_user' AND pemesanan.tanggal_pemesanan >= '$now' AND hewan.status_pesan != 'CANCEL' AND transaksi.status = 'SUCCESS'
                        ORDER BY hewan.`datetime` DESC";
            } else { 
                if (isset($_POST['tanggal_terpilih']) && !empty($_POST['tanggal_terpilih'])) {
                    $tanggal_terpilih = $_POST['tanggal_terpilih'];
                    if ($tanggal_terpilih == "all") {
                        $query = "SELECT hewan.id_hewan, pemesanan.id_pemesanan, hewan.nama_hewan, hewan.datetime, hewan.status_pesan, transaksi.status
                                FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                                WHERE pemesanan.id_user = '$id_user' ORDER BY hewan.`datetime` DESC";
                    } else {
                        $query = "SELECT hewan.id_hewan, pemesanan.id_pemesanan, hewan.nama_hewan, hewan.datetime, hewan.status_pesan, transaksi.status
                                FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan
                                WHERE pemesanan.id_user = '$id_user' AND hewan.datetime >= '$tanggal_terpilih' ORDER BY hewan.`datetime` DESC";
                    }
                }
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
                $id_user = $user->id;

                $conn = getConnection();
                $query = "INSERT INTO hewan (id_user, jenis, nama_hewan, jumlah, `datetime`) 
                            VALUE ('$id_user', '$jenis_hewan', '$nama_hewan', '$jumlah', '$now')";

                if (mysqli_query($conn, $query)) {
                    $query_result = mysqli_query($conn, "SELECT id_hewan, status_pesan FROM hewan WHERE id_user = '$id_user' GROUP BY id_hewan DESC LIMIT 1");

                    if ($query_result) {
                        $row = mysqli_fetch_assoc($query_result);
                    
                        if ($row && isset($row['id_hewan'])) {
                            $id_hewan = $row['id_hewan'];
                            $status_pesan = $row['status_pesan'];
                            $query = "INSERT INTO pemesanan (id_user, id_hewan, nama_lengkap, tanggal_pemesanan, email, telepon, alamat) 
                                VALUE ('$id_user', '$id_hewan', '$full_name', '$tgl_pesan', '$email', '$phone', '$alamat')";
                    
                            $transaction_id = 'WAITING-' . $id_user . '-' . $now . '-' . $jumlah;
                            if (mysqli_query($conn, $query)) {
                                $query = "INSERT INTO jadwal (id_pemesanan, namalengkap, tanggal_pemesanan, status)
                                            VALUE ('$id_hewan', '$full_name', '$tgl_pesan', '$status_pesan')";
                                
                                if (mysqli_query($conn, $query)) {
                                    $query = "INSERT INTO transaksi (id_user, invoice, id_hewan, transaction_id, jenis_transaksi, created_at, updated_at) 
                                            VALUE ('$id_user', 'WAITING', '$id_hewan', '$transaction_id', 'pemesanan', '$now', '$now')";
                                    
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
                            mysqli_close($conn);
                            return success($row, 204);
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