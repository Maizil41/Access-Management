/*
*******************************************************************************************************************
* Warning!!!, Tidak untuk diperjual belikan!, Cukup pakai sendiri atau share kepada orang lain secara gratis
*******************************************************************************************************************
* Dibuat oleh @Taufik https://t.me/taufik_n_a
*******************************************************************************************************************
* © 2025 AlphaWireless.net By @Taufik
*******************************************************************************************************************
*/
import { servers } from "../config/server.js";
let statusInterval;

// Fungsi untuk memperbarui status server
async function updateServerStatus() {
    try {
        let response = await fetch("/pppoepages/ping_server.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(servers) // Kirim daftar server ke PHP
        });

        let data = await response.json();
        let container = document.getElementById("statusContainer");
        container.innerHTML = "";

        data.forEach(server => {
            let statusDiv = document.createElement("div");
            statusDiv.classList.add("status-box", server.status.includes("Online") ? "online" : "offline");
            statusDiv.innerHTML = `<strong>${server.name}</strong><br>${server.status} - Ping: ${server.ping}`;
            container.appendChild(statusDiv);
        });
    } catch (error) {
        console.error("Error fetching server status:", error);
    }
}

// Ketika tombol diklik, jalankan update
document.getElementById("checkStatusButton").addEventListener("click", function () {
    let container = document.getElementById("statusContainer");

    // Sembunyikan tombol setelah diklik
    this.style.display = "none";

    if (!container.classList.contains("show")) {
        container.classList.add("show");
        container.style.display = "block";

        if (!statusInterval) {
            statusInterval = setInterval(updateServerStatus, 3000);
        }

        updateServerStatus().then(() => {
            container.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }
});

// Ketika accordion ditutup, hentikan pengecekan
document.getElementById("accordion").addEventListener("hidden.bs.collapse", function () {
    let container = document.getElementById("statusContainer");

    container.classList.remove("show");

    setTimeout(() => {
        container.style.display = "none";

document.getElementById("checkStatusButton").style.display = "block";
    }, 300);

    if (statusInterval) {
        clearInterval(statusInterval);
        statusInterval = null;
    }
});

// Ketika accordion dibuka, sembunyikan status (opsional)
document.getElementById("accordion").addEventListener("shown.bs.collapse", function () {
    let button = document.getElementById("checkStatusButton");
    let container = document.getElementById("statusContainer");

    if (button) {
        button.scrollIntoView({ behavior: "smooth", block: "start" }); // Scroll dengan efek smooth
    }

    container.classList.remove("show");
    container.style.display = "none";
});