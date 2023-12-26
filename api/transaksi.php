<?php
class Transaksi {
    function index($user) {
        $id_user = $user->id;
        $conn = getConnection();
        
        try {
            $now = timeZone('Y-m-d');
            $query = "SELECT hewan.id_hewan, hewan.nama_hewan, hewan.status_pesan, (transaksi.`updated_at`) AS `datetime`, pemesanan.id_pemesanan, penitipan.id_penitipan, transaksi.status
                        FROM transaksi INNER JOIN hewan ON transaksi.id_hewan = hewan.id_hewan 
                        LEFT JOIN penitipan ON penitipan.id_hewan = hewan.id_hewan LEFT JOIN pemesanan ON pemesanan.id_hewan = hewan.id_hewan 
                        WHERE transaksi.id_user = '$id_user' AND ((penitipan.tanggal_keluar  < '$now' OR pemesanan.tanggal_pemesanan < '$now') OR hewan.status_pesan = 'CANCEL') ORDER BY transaksi.`updated_at` DESC";
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
        $id_user = $user->id;

        try {
            $conn = getConnection();
            $data = array();
            $now = timeZone('Y-m-d');

            $query = "SELECT hewan.id_hewan, hewan.nama_hewan, penitipan.tanggal_masuk, transaksi.status
                        FROM penitipan INNER JOIN hewan ON penitipan.id_hewan = hewan.id_hewan 
                        LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan 
                        WHERE penitipan.id_user = '$id_user' AND penitipan.tanggal_keluar >= '$now' AND
                        transaksi.status != 'SUCCESS' AND hewan.status_pesan != 'CANCEL' ORDER BY hewan.`datetime` DESC";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }

                $query = "SELECT hewan.id_hewan, hewan.nama_hewan, pemesanan.tanggal_pemesanan, transaksi.status
                            FROM pemesanan INNER JOIN hewan ON pemesanan.id_hewan = hewan.id_hewan 
                            LEFT JOIN transaksi ON transaksi.id_hewan = hewan.id_hewan 
                            WHERE pemesanan.id_user = '$id_user' AND pemesanan.tanggal_pemesanan >= '$now' AND 
                            transaksi.status != 'SUCCESS' AND hewan.status_pesan != 'CANCEL' ORDER BY hewan.`datetime` DESC";
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

    function transaksiMidtrans($user) {
        try {
            if (isset($_POST['invoice'], $_POST['id_hewan'], $_POST['total_harga']) && !empty($_POST['id_hewan']) && !empty($_POST['invoice']) && !empty($_POST['total_harga'])) {
                $id_user = $user->id;
                $conn = getConnection();
                $now = timeZone();
                
                $id_hewan = $_POST['id_hewan'];
                $invoice = $_POST['invoice'];
                $total_harga = $_POST['total_harga'];
                
                $query = "UPDATE transaksi SET invoice = '$invoice', total_harga = '$total_harga', updated_at = '$now' WHERE id_hewan = '$id_hewan' AND id_user = '$id_user'";
                
                if (mysqli_query($conn, $query)) {
                    mysqli_close($conn);
                    return success("update transaction berhasil");
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

    function midtrans()
    {
        $data = file_get_contents("php://input");
        $n_body = json_decode($data, true);
        
        if (!empty($n_body['order_id']) && !empty($n_body['transaction_id']) &&
                !empty($n_body['status_code']) && !empty($n_body['payment_type']))
        {
            $invoice = $n_body['order_id'];
            
            try {
                $conn = getConnection();
                $now = timeZone();
                
                $query = "SELECT transaction_id FROM transaksi WHERE invoice = '$invoice' LIMIT 1";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) == 0) {
                    mysqli_close($conn);
                    return success($result, 204);
                }
                
                $transaction_id_query = mysqli_fetch_assoc($result)['transaction_id'];
                $transaction_id = $n_body['transaction_id'];

                $payment_type = $n_body['payment_type'];
                $tanggal_bayar = '2020-01-01 00:00:00';
                $va_number = '-';

                switch ($n_body['status_code']) {
                    case '200':
                        $status_code = "SUCCESS";
                        $tanggal_bayar = $n_body['settlement_time'];
                        break;
                    case '201':
                        $status_code = "PENDING";
                        break;
                    case '202':
                        $status_code = "CANCEL";
                        $tanggal_bayar = '-';
                        break;
                }

                if ($transaction_id == $transaction_id_query) {
                    $query = "UPDATE transaksi SET `status` = '$status_code', tanggal_bayar = '$tanggal_bayar', 
                                va_number = '$va_number', updated_at = '$now'  WHERE transaction_id = '$transaction_id'";
                } else {
                    if (strcmp($payment_type, "bank_transfer") == 0) {
                        /**
                         * ! bank_transfer -> BCA, BNI, BRI
                         * * "['va_numbers'][0]['bank']": "...",
                         * * "['va_numbers'][0]['va_number']": "..."
                         * 
                         * ! bank_transfer -> Permata
                         * * "permata_va_number": "..."
                         * */
                        if (!empty($n_body['va_numbers'][0]['bank']) && !empty($n_body['va_numbers'][0]['va_number']))
                        { 
                            $payment_type = $n_body['va_numbers'][0]['bank'];
                            $va_number = $n_body['va_numbers'][0]['va_number'];
                        } else if (!empty($n_body['permata_va_number'])) {
                            $payment_type = "permata";
                            $va_number = $n_body['permata_va_number'];
                        } else {
                            $payment_type = "lakukan transaksi lagi";
                            $va_number = '-';
                        }
                    } elseif (strcmp($payment_type, "echannel") == 0) {
                        /**
                         * ! echannel -> Mandiri Bill
                         * * "biller_code": "...",
                         * * "bill_key": "..."
                         * */
                        $payment_type = "Mandiri Bill";
                        if (!empty($n_body['biller_code']) && !empty($n_body['bill_key']))
                        { 
                            $va_number = "Biller Code (" . $n_body['biller_code'] . "), Bill Key " . $n_body['bill_key'];
                        } else {
                            $va_number = "lakukan transaksi lagi";
                        }
                    } elseif (strcmp($payment_type, "cstore") == 0) {
                        /**
                         * ! cstore -> Alfa / Indo mart
                         * * "payment_code": "..."
                         * ! (SANDBOX) Indo -> Product Code "merchant_id": "..."
                         * */
                        if (!empty($n_body['store']) && !empty($n_body['payment_code'])) {
                            $payment_type = $n_body['store'];
                            $va_number = $n_body['payment_code'];

                            if (strcmp($payment_type, "alfamart")) {
                                $va_number = $va_number . ' (' . $n_body['merchant_id'] . ')';
                            }
                        } else {
                            $payment_type = "lakukan transaksi lagi";
                            $va_number = '-';
                        }
                    }

                    $query = "UPDATE transaksi SET transaction_id = '$transaction_id', `status` = '$status_code', jenis_pembayaran = '$payment_type', 
                                va_number = '$va_number', updated_at = '$now'  WHERE invoice = '$invoice'";
                }

                if (mysqli_query($conn, $query)) {
                    mysqli_close($conn);
                    return success("update transaksi success");
                } else {
                    mysqli_close($conn);
                    return error(mysqli_error($conn));
                }

            } catch (mysqli_sql_exception $e) {
                return error(strval($e));
            }

        } else {
            return error('Data yang diterima kosong');
        }
    }
    
    /**
     * call charge API using Curl
     */
    function midtransChargeAPI()
    {    
        include('../config/Midtrans.php');
        // get the HTTP POST body of the request
        $request_body = file_get_contents('php://input');
        
        $ch = curl_init();
        $curl_options = array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            // Add header to the request, including Authorization generated from server key
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($server_key . ':')
            ),
            CURLOPT_POSTFIELDS => $request_body
        );

        curl_setopt_array($ch, $curl_options);
        $result = array(
            'body' => curl_exec($ch),
            'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );

        return $result;
    }
}
