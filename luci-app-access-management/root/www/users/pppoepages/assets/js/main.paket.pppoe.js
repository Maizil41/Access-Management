/*
*******************************************************************************************************************
* Warning!!!, Tidak untuk diperjual belikan!, Cukup pakai sendiri atau share kepada orang lain secara gratis
*******************************************************************************************************************
* Dibuat oleh @Taufik https://t.me/taufik_n_a
*******************************************************************************************************************
* © 2025 AlphaWireless.net By @Taufik
*******************************************************************************************************************
*/
document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("paket-container");

    // Warna latar belakang yang berbeda untuk setiap paket
    const bgClasses = ["bg-danger", "bg-success", "bg-secondary", "bg-info", "bg-warning"];

    paketPPPoE.forEach((paket, index) => {
        const paketDiv = document.createElement("div");

        // Pilih warna berdasarkan indeks
        const bgColor = bgClasses[index % bgClasses.length];

        paketDiv.classList.add("card-block", bgColor, "mb-2");
        paketDiv.setAttribute("onclick", `showWifiHomeModal()`);

        paketDiv.innerHTML = `
            <div class="card-main">
                <div class="card-button dropdown">
                    <button type="button" class="btn btn-link btn-icon" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="#ffffff" d="M5.5,7A1.5,1.5 0 0,1 4,5.5A1.5,1.5 0 0,1 5.5,4A1.5,1.5 0 0,1 7,5.5A1.5,1.5 0 0,1 5.5,7M21.41,11.58L12.41,2.58C12.05,2.22 11.55,2 11,2H4C2.89,2 2,2.89 2,4V11C2,11.55 2.22,12.05 2.59,12.41L11.58,21.41C11.95,21.77 12.45,22 13,22C13.55,22 14.05,21.77 14.41,21.41L21.41,14.41C21.78,14.05 22,13.55 22,13C22,12.44 21.77,11.94 21.41,11.58Z" />
                        </svg>
                    </button>
                </div>
                <div class="balance">
                    <span class="label">PAKET</span>
                    <h1 class="title">${paket.nama}</h1>
                </div>
                <div class="in">
                    <div class="card-number">
                        <span class="label">Harga</span>
                        Rp ${paket.harga.toLocaleString("id-ID")} /bulan
                    </div>
                        <stong class="text-center text-light">
                            ${paket.keterangan}
                        </stong>
                </div>
            </div>
        </div>
        `;

        container.appendChild(paketDiv);
    });
});