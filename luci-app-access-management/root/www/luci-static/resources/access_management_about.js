const container = document.getElementById('app-container');

const content = `
    <style>
        .social-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
            gap: 15px;
        }

        .social-links a {
            text-decoration: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1 1 calc(50% - 30px);
            max-width: 160px;
        }

        .social-links a img {
            width: 24px;
            height: 24px;
        }

        .whatsapp { background: #25D366; }
        .telegram { background: #0088cc; }
        .facebook { background: #3f8bff; }
        .youtube { background: #FF0000; }
        .donation-links {
            text-align: center;
            margin-top: auto;
        }
        .donation-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .donation-links a {
            text-decoration: none;
            background: #FFA500;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            flex: 1 1 calc(50% - 30px);
            max-width: 160px;
            text-align: center;
        }

        .qr-section {
            text-align: center;
            margin-top: 20px;
        }
        .qr-section img {
            max-width: 200px;
            margin-top: 10px;
        }
        
        .about a {
            text-decoration: none;
            background: rgb(0, 191, 255, 0.2);
            font: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><center><strong>About</strong></h2>
        <br>
        <p><center class="about"><strong>Access Management</strong> adalah tools OpenWrt yang dikembangkan<br>oleh <strong>AlphaWireless.net</strong> <a href="https://t.me/taufik_n_a">@Taufik</a> untuk keamanan router<br>OpenWrt dari akses users <strong>Hotspot</strong> dan <strong>PPPoE</strong> pada<br><strong>Radius Monitor V2</strong> yang dikembangkan oleh<br><strong>Mutiara-Wrt</strong> <a href="https://t.me/maizil41">@Maizil</a></center></p>
        <br>
        <p><center>Dirancang untuk memberikan keamanan jaringan yang terkontrol
        </p>
        <br>
        <p><center>
        Jika Anda ingin mendukung pengembangan tools Access Management, Anda bisa berdonasi melalui QRIS.
        </p>

        <div class="qr-section">
            <p><center>Scan QRIS</center></p>
            <img src="/luci-static/resources/assets/img/qristaufik.png" alt="QRIS Code">
        </div>
        <br>
        <p><center>
        Jika Anda ingin belajar lebih lanjut tentang <strong>Mutiara-Wrt</strong>, yukk kepoin di bawah ini 👇
        </p>
        <br>
        <h2 class="my-2"><center><strong>Mutiara-Wrt</strong></h2>
        <br>
        <p><center><strong>Mutiara-Wrt</strong> adalah firmware OpenWrt yang dikembangkan untuk memenuhi kebutuhan Hotspot dan PPPoE tanpa <strong>MikroTik</strong> <br> dengan integrasi <em><strong>Freeradius</strong></em> dan <em><strong>Coova-Chilli</strong></em>.</p>
        <br>
        <p><center>Dirancang untuk memberikan pengalaman jaringan yang aman dan terkontrol, <strong>Mutiara-Wrt</strong> memudahkan pengelolaan Hotspot dan PPPoE melalui UI <em><strong>Radius Monitor</strong></em>, memastikan setiap koneksi aman dan terverifikasi.</p>
        <br>
        Base Firmware OpenWrt Source <strong>23.05.5</strong>
        <br>
        OpenWrt Build <strong>23.05.5 Stable</strong>
        </p>
        <br>
        <p><center>
        Jika Anda ingin belajar lebih lanjut tentang <strong>Mutiara-Wrt</strong>, join komunitas kami melalui link di bawah ini.
        </p>
        
        <div class="social-links">
            <a href="https://wa.me/6285372687484" class="whatsapp" target="_blank">
                <img src="/luci-static/resources/assets/img/whatsapp.svg" alt="WhatsApp"> WhatsApp
            </a>
            <a href="https://t.me/mutiarawrt" class="telegram" target="_blank">
                <img src="/luci-static/resources/assets/img/telegram.svg" alt="Telegram"> Telegram
            </a>
            <a href="https://facebook.com/maizil.41" class="facebook" target="_blank">
                <img src="/luci-static/resources/assets/img/facebook.svg" alt="Facebook"> Facebook
            </a>
            <a href="https://youtube.com/@mutiara-wrt" class="youtube" target="_blank">
                <img src="/luci-static/resources/assets/img/youtube.png" alt="YouTube"> YouTube
            </a>
        </div>

        <br>
        <p><center>
        Jika Anda ingin mendukung pengembangan firmware <strong>Mutiara-Wrt</strong>, Anda bisa berdonasi melalui link di bawah ini.
        </p>
        
        <div class="donation-links">
            <a href="https://saweria.co/mutiarawrt" target="_blank">Saweria</a>
            <a href="https://sociabuzz.com/maizil41/tribe" target="_blank">Sociabuzz</a>
        </div>
        
        <div class="qr-section">
            <p><center>Scan QR untuk donasi via DANA</center></p>
            <img src="/luci-static/resources/assets/img/dana.svg" alt="QR Code DANA">
        </div>
    </div>
`;

container.innerHTML = content;
