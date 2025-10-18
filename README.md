<div align="center">
  <h1>ACCESS MANAGEMENT</h1>
</div>

<div align="center">
  <img alt="License" src="https://img.shields.io/github/license/TaufikNRA/Access-Management?style=for-the-badge">
  <a target="_blank" href="https://github.com/TaufikNRA/Access-Management/releases"><img src="https://img.shields.io/github/release/TaufikNRA/Access-Management?style=for-the-badge"></a>
  <a target="_blank" href="https://github.com/TaufikNRA/Access-Management/releases"><img src="https://img.shields.io/github/downloads/TaufikNRA/Access-Management/total?style=for-the-badge"></a>
</div>
<hr/>

> ## NOTES
>
> - Aplikasi ini ditujukan untuk firmware donasi Mutiara-Wrt.
> - Sebelum melakukan instalasi, harap memiliki antarmuka LAN di menu antarmuka LuCi agar aplikasi dapat bekerja dengan baik.
> - jika sudah menginstall dan ingin menghapus aplikasi, jangan menggunakan `opkg remove` , tetapi lihat menu di CLI dengan perintah `acmt --help` (tujuanya agar konfigurasi uHTTPd bawaan OpenWrt bisa dipulihkan).

# Mutiara-Wrt ?

- 👉 <a href="https://github.com/Mutiara-Wrt" target="_blank">Mutiara-Wrt</a>

# Kompatibel

- [x] Hanya untuk firmware donasi Mutiara-Wrt V1-6.
- [x] Hanya mendukung CoovaChilli & rp-pppoe-server.
- [x] Bekerja pada Firewall4 (nftables).
- [x] Hanya bekerja dengan IPv4.

# Fitur

- [x] Blokir Pengguna Hotspot & PPPoE dari mengakses port sensitif router RadMonv2 Gateway.
  - [x] Pemblokir Pengguna Hotspot (CoovaChilli).
  - [x] Pemblokir Pengguna PPPoE (rp-pppoe-server).
  - [x] Daftar hitam port Secara otomatis mencantumkan port yang aktif.
  - [x] APP Loop untuk mengendalikan proses CTRL dan untuk pembaruan Daftar Putih MAC dalam hitungan detik.
  - [x] CTRL Loop untuk mengendalikan proses APP dalam hitungan detik.
  - [x] Daftar Putih IP didaftarkan dari sesi aktif secara otomatis.
  - [x] Daftar Putih MAC didaftarkan dari sesi aktif secara otomatis.
  - [x] Konfigurasi database untuk Aplikasi
- [x] Pengaturan server web uHTTPd.
  - [x] Kelola semua konfigurasi server web uHTTPd.
- [x] Otomatisasi pengalihan pemilik ke halaman tujuan dengan port utama.
- [x] Otomatisasi pengalihan Hotspot ke halaman hotspotlogin.
- [x] Otomatisasi pengalihan PPPoE ke Beranda PPPoE.
- [x] Menambahkan halaman Beranda PPPoE dengan tema hotspot kopi.

# Kontributor

<ul>
  <li>Github : <a href="https://github.com/TaufikNRA" target="_blank">TaufikNRA</a></li>
  <li>Telegram : <a href="https://t.me/Taufik_N_A" target="_blank">TAUFIK_N_A</a></li>
</ul>
<ul>
  <li>Github : <a href="https://github.com/Maizil41" target="_blank">Maizil41</a></li>
  <li>Telegram : <a href="https://t.me/Maizil41" target="_blank">Maizil41</a></li>
</ul>

# Qris untuk donasi

  <p>
  <img src="img/qris.png" alt="qris">
 </p>
