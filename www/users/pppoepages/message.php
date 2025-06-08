<?php
/*
*******************************************************************************************************************
* Warning!!!, Tidak untuk diperjual belikan!, Cukup pakai sendiri atau share kepada orang lain secara gratis
*******************************************************************************************************************
* Dibuat oleh @Maizil https://t.me/maizil41
*******************************************************************************************************************
* © 2024 Mutiara-Net By @Maizil
*******************************************************************************************************************
* Modifikasi oleh @Taufik https://t.me/taufik_n_a
*******************************************************************************************************************
* © 2025 AlphaWireless.net By @Taufik
*******************************************************************************************************************
*/

$admin_number = trim(shell_exec("uci get whatsapp-bot.@whatsapp_bot[0].admin_number"));

if (!$admin_number) {
    header("Location: ./kontak.php?mac=$mac&ip=$ip&message=" . urlencode("Kirim pesan gagal karena nomor admin belum disetting"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ip'], $_POST['mac'], $_POST['name'], $_POST['whatsapp'], $_POST['message'])) {
        $ip = htmlspecialchars(trim($_POST['ip']));
        $mac = htmlspecialchars(trim($_POST['mac']));
        $name = htmlspecialchars(trim($_POST['name']));
        $number = htmlspecialchars(trim($_POST['whatsapp']));
        $message = htmlspecialchars(trim($_POST['message']));

        if (!preg_match('/^\d{10,15}$/', $number)) {
            header("Location: ./kontak.php?mac=$mac&ip=$ip&message=Nomor tidak valid");
            exit();
        }

        $send_message = "*▬▬▬▬▬▬ PESAN PPPoE ▬▬▬▬▬▬*\n*IP Address : $ip*\n*MAC Address : $mac*\n*▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬*\n*Nama : $name*\n*Nomor : $number*\n*Pesan : $message*";

        $url = 'http://localhost:3000/send-message';
        $data = [
            'to' => $admin_number,
            'message' => $send_message,
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
            ],
        ];

        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === FALSE) {
            header("Location: ./kontak.php?mac=$mac&ip=$ip&message=Kirim pesan gagal");
            exit();
        } else {
            header("Location: ./kontak.php?mac=$mac&ip=$ip&message=Pesan terkirim");
            exit();
        }
    } else {
        header("Location: ./kontak.php?mac=$mac&ip=$ip&message=Data tidak valid!");
        exit();
    }
}
?>