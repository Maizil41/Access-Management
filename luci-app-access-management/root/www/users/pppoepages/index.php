<?php
/*
*******************************************************************************************************************
* Warning!!!, Tidak untuk diperjual belikan!, Cukup pakai sendiri atau share kepada orang lain secara gratis
*******************************************************************************************************************
* Original Loginpage untuk Mikrotik dibuat oleh @Badaro
*
* Modifikasi Untuk Halaman Status PPPoE (RadMonv2) oleh @Taufik https://t.me/taufik_n_a
*******************************************************************************************************************
* © 2025 AlphaWireless-Net By @Taufik
*******************************************************************************************************************
*/

if (array_key_exists('HTTP_CF_CONNECTING_IP', $_SERVER)) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
} elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
    $ip = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"])[0];
} elseif (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
    $ip = $_SERVER["HTTP_CLIENT_IP"];
} elseif (array_key_exists('REMOTE_ADDR', $_SERVER)) {
    $ip = $_SERVER['REMOTE_ADDR'];
} else {
    $ip = "";
}

require 'config/mysqli_db.php';

$sql = "SELECT callingstationid FROM radacct WHERE acctstoptime IS NULL AND framedipaddress = ? ORDER BY acctstarttime DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ip);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $mac = $row['callingstationid'];
} else {
    $mac = "";
}

$stmt->close();
$conn->close();

function checkPing($host) {
    $pingStatus = 0;

    $os = PHP_OS_FAMILY;
    $command = ($os === 'Windows')
        ? "ping -n 1 " . escapeshellarg($host)
        : "ping -c 1 " . escapeshellarg($host);

    exec($command, $output, $result);

    if ($result === 0) {
        $pingStatus = 1;
    }

    return [
        'status' => $pingStatus,
        'output' => $output
    ];
}

$host = "google.com";
$pingResult = checkPing($host);
$pingStatus = $pingResult['status'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <title><?= $pingStatus == 1 ? 'ONLINE' : 'OFFLINE' ?> HOMEPAGE</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png" sizes="32x32">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/status.css">
</head>

<body>

    <!-- App Header -->
    <div class="appHeader">
        <div class="left">
            <div class="headerButton" id="header-button">
                <div class="form-check form-switch">
                    <input class="form-check-input dark-mode-switch" type="checkbox" id="darkmodeSwitch">
                    <label class="form-check-label" for="darkmodeSwitch"></label>
                </div>
            </div>
        </div>
        <div class="pageTitle">
            <script src="assets/config/namawifi.js"></script>
        </div>
        <div class="right">
            <a href="#" class="headerButton" data-bs-toggle="modal" data-bs-target="#DialogIconedInfo">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"  width="24" height="24" viewBox="0 0 24 24">
                    <path fill="#3694ff" d="M10 21H14C14 22.1 13.1 23 12 23S10 22.1 10 21M21 19V20H3V19L5 17V11C5 7.9 7 5.2 10 4.3V4C10 2.9 10.9 2 12 2S14 2.9 14 4V4.3C17 5.2 19 7.9 19 11V17L21 19M17 11C17 8.2 14.8 6 12 6S7 8.2 7 11V18H17V11Z" />
                </svg>
                <span class="badge badge-danger">1</span>
            </a>
        </div>
    </div>
    <!-- * App Header -->
	
    <!-- DialogIconedInfo -->
    <div class="modal fade dialogbox" id="DialogIconedInfo" data-bs-backdrop="static" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="48" height="48" viewBox="0 0 24 24">
                        <path fill="#3694ff" d="M12 12C9.97 12 8.1 12.67 6.6 13.8L4.8 11.4C6.81 9.89 9.3 9 12 9S17.19 9.89 19.2 11.4L17.92 13.1C17.55 13.17 17.18 13.27 16.84 13.41C15.44 12.5 13.78 12 12 12M21 9L22.8 6.6C19.79 4.34 16.05 3 12 3S4.21 4.34 1.2 6.6L3 9C5.5 7.12 8.62 6 12 6S18.5 7.12 21 9M12 15C10.65 15 9.4 15.45 8.4 16.2L12 21L13.04 19.61C13 19.41 13 19.21 13 19C13 17.66 13.44 16.43 14.19 15.43C13.5 15.16 12.77 15 12 15M17.75 19.43L16.16 17.84L15 19L17.75 22L22.5 17.25L21.34 15.84L17.75 19.43Z" />
                    </svg>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title"><script src="assets/config/namawifi.js"></script></h5>
                </div>
                <div class="modal-body">
                <?= $pingStatus == 1 ? 'Gunakan internet dengan bijak!!!' : 'Tidak ada koneksi internet!!!' ?>
                </div>
                <div class="modal-footer">
                    <div class="btn-inline">
                        <a href="#" class="btn btn-text-primary" data-bs-dismiss="modal">OKE</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- * DialogIconedInfo -->

    <!-- App Capsule -->
    <div id="appCapsule">
        <div class="section mt-3 text-center">
            <div class="avatar-section">
                <a href="#">
                    <img src="<?= $pingStatus == 1 ? 'assets/img/worldwide.gif' : 'assets/img/no-wifi-icon.png' ?>" alt="icon" class="imaged w100 rounded">
                    <?php if ($pingStatus == 1): ?>
                    <span class="button btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="#ffffff" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1Z" />
                        </svg>
                    </span>
                    <?php endif; ?>
                </a>
             </div>
        </div>
        <div class="section mt-2">
            <div class="wallet-card">
                <font color="#FFFFFF">
                    <div class="left">
                        <center>
                        <?php if ($pingStatus == 1): ?>
                            <h4 class="total">Akses Internet Tanpa Batasan Kuota</h4>
                        <?php endif; ?>
                        </center>
                    </div>
                    <center>
                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <h3 class="<?= $pingStatus == 1 ? 'text-success' : 'text-danger' ?>">
                                <?= $pingStatus == 1 ? 'Anda sekarang sedang online' : 'Anda sekarang sedang offline' ?>
                                </h3>
                            </div>
                        </div>
                    </center>
                </font>
            <div class="wallet-footer">
            </div>
        </div>
    </div>
    <!-- Wallet Card -->

<?php

    if ($pingStatus == 1) {
        require 'config/mysqli_db.php';

        $query = "SELECT 
            detail.username,
            detail.AcctStartTime,
            CASE 
                WHEN detail.AcctStopTime IS NULL THEN TIMESTAMPDIFF(SECOND, detail.AcctStartTime, NOW()) 
                ELSE detail.AcctSessionTime 
            END AS last_AcctSessionTime,
            detail.NASIPAddress,
            detail.CalledStationId,
            detail.FramedIPAddress,
            detail.CallingStationId,
            detail.AcctInputOctets AS last_AcctInputOctets,
            detail.AcctOutputOctets AS last_AcctOutputOctets,
            total.total_AcctSessionTime,
            total.total_AcctInputOctets,
            total.total_AcctOutputOctets
        FROM 
            (SELECT * 
             FROM radacct 
             WHERE FramedIPAddress = '$ip' 
             ORDER BY RadAcctId DESC 
             LIMIT 1) AS detail
        JOIN 
            (SELECT 
                 username,
                 SUM(CASE 
                     WHEN AcctStopTime IS NULL THEN TIMESTAMPDIFF(SECOND, AcctStartTime, NOW()) 
                     ELSE AcctSessionTime 
                 END) AS total_AcctSessionTime,
                 SUM(AcctInputOctets) AS total_AcctInputOctets,
                 SUM(AcctOutputOctets) AS total_AcctOutputOctets
             FROM radacct 
             WHERE FramedIPAddress = '$ip' 
             GROUP BY username
             LIMIT 1
            ) AS total
        ON detail.username = total.username";

        $result = $conn->query($query);

        $data = array();

        $sqlUser = "SELECT username FROM radacct WHERE framedipaddress='$ip' ORDER BY RadAcctId DESC LIMIT 1;";
        $resultUser = mysqli_fetch_assoc(mysqli_query($conn, $sqlUser));
        $user = $resultUser['username'] ?? "";

        $sqlTotalSession = "SELECT g.value as total_session FROM radgroupcheck as g, radusergroup as u WHERE u.username = '$user' AND g.groupname = u.groupname AND g.attribute ='Max-All-Session';";
        $resultTotalSession = mysqli_fetch_assoc(mysqli_query($conn, $sqlTotalSession));
        $totalSession = isset($resultTotalSession['total_session']) ? $resultTotalSession['total_session'] : 0;

        $sqlTotalKuota = "SELECT VALUE AS total_kuota
            FROM radgroupreply
            WHERE ATTRIBUTE = 'ChilliSpot-Max-Total-Octets'
              AND GROUPNAME = (
                SELECT GROUPNAME
                FROM radusergroup
                WHERE USERNAME = '$user'
              )";

        $resultTotalKuota = mysqli_fetch_assoc(mysqli_query($conn, $sqlTotalKuota));
        if (is_array($resultTotalKuota) && isset($resultTotalKuota['total_kuota'])) {
            $totalKuota = $resultTotalKuota['total_kuota'];
        } else {
            $totalKuota = 0;
        }

        $sqlKuotaDigunakan = "SELECT SUM(acctinputoctets + acctoutputoctets) as kuota_terpakai FROM radacct WHERE username = '$user';";
        $resultKuotaDigunakan = mysqli_fetch_assoc(mysqli_query($conn, $sqlKuotaDigunakan));
        $KuotaDigunakan = $resultKuotaDigunakan['kuota_terpakai'];

        $sqlFirstLogin = "SELECT acctstarttime AS first_login FROM radacct WHERE username='$user' ORDER BY acctstarttime ASC LIMIT 1;";
        $resultFirstLogin = mysqli_fetch_assoc(mysqli_query($conn, $sqlFirstLogin));
        $firstLogin = $resultFirstLogin['first_login'] ?? "";

        $duration = $totalSession;
        $expiryTime = strtotime($firstLogin) + $duration;
        $sisaKuota = $totalKuota - $KuotaDigunakan;
        $remainingTime = $expiryTime - time();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                $username = $row['username'];
                $userIPAddress = $row['FramedIPAddress'];
                $userMacAddress = $row['CallingStationId'];
                $firstLogin = $firstLogin;
                $latestLogin = $row['AcctStartTime'];
                $userUpload = toxbyte($row['last_AcctInputOctets']);
                $userDownload = toxbyte($row['last_AcctOutputOctets']);
                $userTraffic = toxbyte($row['last_AcctOutputOctets'] + $row['last_AcctInputOctets']);
                $userOnlineTime = time2str($row['last_AcctSessionTime']);
                $userExpired = time2str($remainingTime);
                $UserKuota = toxbyte($sisaKuota);
                $totalUserUpload = toxbyte($row['total_AcctInputOctets']);
                $totalUserDownload = toxbyte($row['total_AcctOutputOctets']);
                $totalUserTraffic = toxbyte($row['total_AcctOutputOctets'] + $row['total_AcctInputOctets']);
                $totalUserOnlineTime = time2str($row['total_AcctSessionTime']);

                $data[] = array(
                'username' => $username,
                'userIPAddress' => $userIPAddress,
                'userMacAddress' => $userMacAddress,
                'firstLogin' => $firstLogin,
                'latestLogin' => $latestLogin,
                'userUpload' => $userUpload,
                'userDownload' => $userDownload,
                'userTraffic' => $userTraffic,
                'userOnlineTime' => $userOnlineTime,
                'userExpired' => $userExpired,
                'userKuota' => $UserKuota,
                'totalUserUpload' => $totalUserUpload,
                'totalUserDownload' => $totalUserDownload,
                'totalUserTraffic' => $totalUserTraffic,
                'totalUserOnlineTime' => $totalUserOnlineTime
                );
            }
        }

        $conn->close();

        foreach ($data as $row) {
            echo '<div class="listview-title mt-1">Account</div>
            <ul class="listview image-listview text inset">
                <li><div class="item"><div class="in"><div>Username</div><div>' . $row['username'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>IP Address</div><div>' . $row['userIPAddress'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>MAC Address</div><div>' . $row['userMacAddress'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>First Login</div><div>' . $row['firstLogin'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>Latest Login</div><div>' . $row['latestLogin'] . '</div></div></div></li>
            </ul>

            <div class="listview-title mt-1">Usage</div>
            <ul class="listview image-listview text inset">';
            
            if ($totalKuota >= 1) {
                echo '<li><div class="item"><div class="in"><div>Quota</div><div id="userKuota">' . $row['userKuota'] . '</div></div></div></li>';
            }

            echo '<li><div class="item"><div class="in"><div>Upload</div><div id="userUpload">' . $row['userUpload'] . '</div><div>|</div><div>Total Upload</div><div id="totalUserUpload">' . $row['totalUserUpload'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>Download</div><div id="userDownload">' . $row['userDownload'] . '</div><div>|</div><div>Total Download</div><div id="totalUserDownload">' . $row['totalUserDownload'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>Traffic</div><div id="userTraffic">' . $row['userTraffic'] . '</div><div>|</div><div>Total Traffic</div><div id="totalUserTraffic">' . $row['totalUserTraffic'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>Connected</div><div id="onlineTime">' . $row['userOnlineTime'] . '</div></div></div></li>
                <li><div class="item"><div class="in"><div>Total Connected</div><div id="totalOnlineTime">' . $row['totalUserOnlineTime'] . '</div></div></div></li>';

            if ($userExpired >= 1) {
                echo '<li><div class="item"><div class="in"><div>Remaining</div><div id="userExpired">' . $row['userExpired'] . '</div></div></div></li>';
            }

            echo '</ul>';
        }

        echo '<div class="section inset mt-2">
            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#accordion">
                            Cek Ping Server
                        </button>
                    </h2>
                    <div id="accordion" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <button id="checkStatusButton" class="btn btn-primary btn-lg btn-block">Cek Ping</button>
                                <div id="statusContainer">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    } elseif ($pingStatus == 0) {
        echo '<div class="section mt-2 mb-2">
                <div class="card">
                    <div class="card-body">
                        <div class="p-1">
                            <div class="text-center">
                                <h2 class="text-warning">Mohon Maaf</h2>
                                <p class="card-text" id="alamat-container">
                                    <p class="modal-title"><h4>Pelanggan WiFi  <strong class="text-primary"><script src="assets/config/namawifi.js"></script></strong>  yang terhormat, <br><br>
                                    Kami informasikan bahwa layanan internet anda saat ini <b>terisolir (dimatikan sementara)</b>. <br><br>
                                    Dimohon untuk <b>melakukan pembayaran tagihan</b>, supaya internet <b>kembali normal</b>.<br><br>
                                    Guna menghindari ketidaknyamanan ini, <br>
                                    dimohon untuk melakukan pembayaran sebelum Jatuh Tempo.<br><br>
                                    Terimakasih</h4></p>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        
    }

    function toxbyte($size) {
        if ($size > 1073741824) {
            return round($size / 1073741824, 2) . " GB";
        } elseif ($size > 1048576) {
            return round($size / 1048576, 2) . " MB";
        } elseif ($size > 1024) {
            return round($size / 1024, 2) . " KB";
        } else {
            return $size . " B";
        }
    }

    function time2str($time) {
        $str = "";
        $time = floor($time);
        if (!$time) return "0 detik";
        $d = $time/86400;
        $d = floor($d);
        if ($d){
            $str .= "$d hari, ";
            $time = $time % 86400;
        }
        $h = $time/3600;
        $h = floor($h);
        if ($h){
            $str .= "$h jam, ";
            $time = $time % 3600;
        }
        $m = $time/60;
        $m = floor($m);
        if ($m){
            $str .= "$m menit, ";
            $time = $time % 60;
        }
        if ($time) $str .= "$time detik, ";
	$str = preg_replace("/, $/",'',$str);
	return $str;
    }
?>

    <!-- app footer -->
    <div class="appFooter mt-2">
        <div class="footer-title">
            Support by <script src="assets/config/supported.js"></script>
        </div>
    </div>
    <!-- * app footer -->

    </div>
    <!-- * App Capsule -->

    <!-- Menu Bawah -->
    <div class="appBottomMenu">
        <a href="#" class="item active">
            <div class="col">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"  width="24" height="24" viewBox="0 0 24 24">
				<path fill="#3694ff" d="M17 14H19V17H22V19H19V22H17V19H14V17H17V14M5 20V12H2L12 3L22 12H17V10.19L12 5.69L7 10.19V18H12C12 18.7 12.12 19.37 12.34 20H5Z" />
				</svg>
				<strong>Beranda</strong>
            </div>
        </a>
        <a href="paket.php?mac=<?php echo $mac; ?>&ip=<?php echo $ip; ?>" class="item">
            <div class="col">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"  width="24" height="24" viewBox="0 0 24 24">
				<path fill="#3694ff" d="M19 23.3L18.4 22.8C16.4 20.9 15 19.7 15 18.2C15 17 16 16 17.2 16C17.9 16 18.6 16.3 19 16.8C19.4 16.3 20.1 16 20.8 16C22 16 23 16.9 23 18.2C23 19.7 21.6 20.9 19.6 22.8L19 23.3M18 2C19.1 2 20 2.9 20 4V13.08L19 13L18 13.08V4H13V12L10.5 9.75L8 12V4H6V20H13.08C13.2 20.72 13.45 21.39 13.8 22H6C4.9 22 4 21.1 4 20V4C4 2.9 4.9 2 6 2H18Z" />
				</svg>
				<strong>Paket</strong>
            </div>
        </a>
		<a href="faq.php?mac=<?php echo $mac; ?>&ip=<?php echo $ip; ?>" class="item">
            <div class="col">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"  width="24" height="24" viewBox="0 0 24 24">
				<path fill="#3694ff" d="M18,15H6L2,19V3A1,1 0 0,1 3,2H18A1,1 0 0,1 19,3V14A1,1 0 0,1 18,15M23,9V23L19,19H8A1,1 0 0,1 7,18V17H21V8H22A1,1 0 0,1 23,9M8.19,4C7.32,4 6.62,4.2 6.08,4.59C5.56,5 5.3,5.57 5.31,6.36L5.32,6.39H7.25C7.26,6.09 7.35,5.86 7.53,5.7C7.71,5.55 7.93,5.47 8.19,5.47C8.5,5.47 8.76,5.57 8.94,5.75C9.12,5.94 9.2,6.2 9.2,6.5C9.2,6.82 9.13,7.09 8.97,7.32C8.83,7.55 8.62,7.75 8.36,7.91C7.85,8.25 7.5,8.55 7.31,8.82C7.11,9.08 7,9.5 7,10H9C9,9.69 9.04,9.44 9.13,9.26C9.22,9.08 9.39,8.9 9.64,8.74C10.09,8.5 10.46,8.21 10.75,7.81C11.04,7.41 11.19,7 11.19,6.5C11.19,5.74 10.92,5.13 10.38,4.68C9.85,4.23 9.12,4 8.19,4M7,11V13H9V11H7M13,13H15V11H13V13M13,4V10H15V4H13Z" />
				</svg>
				<strong>FAQ</strong>
            </div>
        </a>
        <a href="kontak.php?mac=<?php echo $mac; ?>&ip=<?php echo $ip; ?>" class="item">
            <div class="col">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"  width="24" height="24" viewBox="0 0 24 24">
				<path fill="#3694ff" d="M6,17C6,15 10,13.9 12,13.9C14,13.9 18,15 18,17V18H6M15,9A3,3 0 0,1 12,12A3,3 0 0,1 9,9A3,3 0 0,1 12,6A3,3 0 0,1 15,9M3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3H5C3.89,3 3,3.9 3,5Z" />
				</svg>
				<strong>Kontak</strong>
            </div>
        </a>
    </div>
    <!-- Menu Bawah -->

    <script src="assets/js/jquery-3.6.3.min.js"></script>
    <script>
        $(document).ready(function () {
            setInterval(function(){
                $("#userUpload").load(window.location.href + " #userUpload");
                $("#userDownload").load(window.location.href + " #userDownload");
                $("#userTraffic").load(window.location.href + " #userTraffic");
                $("#userKuota").load(window.location.href + " #userKuota");
                $("#totalUserUpload").load(window.location.href + " #totalUserUpload");
                $("#totalUserDownload").load(window.location.href + " #totalUserDownload");
                $("#totalUserTraffic").load(window.location.href + " #totalUserTraffic");
            },3000);

            setInterval(function(){
                $("#onlineTime").load(window.location.href + " #onlineTime");
                $("#userExpired").load(window.location.href + " #userExpired");
                $("#totalOnlineTime").load(window.location.href + " #totalOnlineTime");
            },1000);
        });
    </script>

    <!-- ========= JS Files =========  -->
    <!-- New Custom Js File -->
    <script src="assets/config/darkmode.js"></script>
    <script type="module" src="assets/js/update.status.js"></script>
    <!-- Bootstrap -->
    <script src="assets/js/lib/bootstrap.bundle.min.js"></script>
    <!-- Base Js File -->
    <script src="assets/js/base.js"></script>
</body>
</html>