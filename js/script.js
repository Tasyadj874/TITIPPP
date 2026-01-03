// =================================================================
// KODE FINAL & STABIL - DIJAMIN BERFUNGSI
// =ga================================================================

document.addEventListener("DOMContentLoaded", () => {
  // --- FUNGSI DASAR NAVBAR ---
  const loginButton = document.getElementById("login");
  const loginOptions = document.getElementById("login-options");
  const navbarNav = document.querySelector(".navbar-nav");
  const hamburger = document.querySelector("#hamburger-menu");

  if (loginButton) {
    loginButton.addEventListener("click", (e) => {
      const href = loginButton.getAttribute("href");
      if (!href) {
        return;
      }

      if (!href.startsWith("#")) {
        window.location.href = href;
        return;
      }

      if (!loginOptions) {
        return;
      }

      e.preventDefault();
      loginOptions.classList.toggle("show");
      loginOptions.style.display = loginOptions.classList.contains("show") ? "block" : "none";
    });
  }

  if (hamburger) {
    hamburger.addEventListener("click", (e) => {
      e.preventDefault();
      if (navbarNav) {
        navbarNav.classList.toggle("active");
      }
    });
  }

  document.addEventListener("click", (e) => {
    if (loginButton && loginOptions && !loginButton.contains(e.target) && !loginOptions.contains(e.target)) {
      loginOptions.classList.remove("show");
      loginOptions.style.display = "none";
    }
    if (hamburger && navbarNav && !hamburger.contains(e.target) && !navbarNav.contains(e.target)) {
      navbarNav.classList.remove("active");
    }
  });

  // --- LOGIKA UTAMA FORM & PETA ---
  const formPopup = document.getElementById("form-pemesanan");
  if (!formPopup) return; // Hentikan jika form tidak ada

  const btnPesanList = document.querySelectorAll(".btn-pesan");
  const btnClose = formPopup.querySelector(".btn-close");
  const btnSetLocation = document.getElementById("btn-set-location");
  const mapContainer = document.getElementById("mapid-container");
  const orderDetails = document.getElementById("order-details");
  const calculationResult = document.getElementById("calculation-result");
  const locationInfoPenerima = document.getElementById("location-info-penerima");
  const locationInfoToko = document.getElementById("location-info-toko");

  let map = null;
  let control = null;
  let geocoder = null;
  let waypoints = [];

  if (typeof L !== "undefined" && L.Control && L.Control.Geocoder && typeof L.Control.Geocoder.nominatim === "function") {
    geocoder = L.Control.Geocoder.nominatim();
  }

  const openPopup = () => {
    formPopup.classList.add("active");
  };

  const closePopup = () => {
    formPopup.classList.remove("active");
    if (mapContainer) mapContainer.style.display = "none";
    if (orderDetails) orderDetails.style.display = "none";
    if (calculationResult) calculationResult.style.display = "none";
    if (locationInfoPenerima) locationInfoPenerima.style.display = "none";
    if (locationInfoToko) locationInfoToko.style.display = "none";
  };

  if (btnClose) {
    btnClose.addEventListener("click", () => {
      closePopup();
    });
  }

  const formTitle = document.getElementById("form-title");
  const pemesananForm = document.getElementById("pemesanan-form");
  const alamatPenerimaInput = document.getElementById("alamat-penerima");
  const alamatTokoInput = document.getElementById("alamat-toko");
  const alamatPenerimaText = document.getElementById("alamat-penerima-text");
  const alamatTokoText = document.getElementById("alamat-toko-text");
  const namaPenerimaInput = document.getElementById("nama-penerima");
  const namaTokoInput = document.getElementById("nama-toko");
  const whatsappInput = document.getElementById("whatsapp");
  const keteranganInput = document.getElementById("keterangan");
  const metodeInput = document.getElementById("metode");
  const btnMetodeList = document.querySelectorAll(".btn-metode");

  let selectedLayanan = "";

  const resetPopupState = () => {
    if (formTitle) formTitle.textContent = "";
    if (mapContainer) mapContainer.style.display = "none";
    if (orderDetails) orderDetails.style.display = "block";
    if (calculationResult) calculationResult.style.display = "none";
    if (locationInfoPenerima) locationInfoPenerima.style.display = "none";
    if (locationInfoToko) locationInfoToko.style.display = "none";
    if (alamatPenerimaText) alamatPenerimaText.textContent = "";
    if (alamatTokoText) alamatTokoText.textContent = "";

    if (alamatPenerimaInput) alamatPenerimaInput.value = "";
    if (alamatTokoInput) alamatTokoInput.value = "";
    if (namaPenerimaInput) namaPenerimaInput.value = "";
    if (namaTokoInput) namaTokoInput.value = "";
    if (whatsappInput) whatsappInput.value = "";
    if (keteranganInput) keteranganInput.value = "";

    if (metodeInput) metodeInput.value = "Transfer";
    btnMetodeList.forEach((b) => b.classList.remove("active"));
    const defaultBtn = document.querySelector('.btn-metode[data-method="Transfer"]');
    if (defaultBtn) {
      defaultBtn.classList.add('active');
    }
  };

  btnPesanList.forEach((btn) => {
    btn.addEventListener("click", () => {
      selectedLayanan = btn.getAttribute("data-layanan") || "";
      if (formTitle) {
        formTitle.textContent = selectedLayanan !== "" ? `Form Pemesanan - ${selectedLayanan}` : "Form Pemesanan";
      }
      resetPopupState();
      openPopup();
    });
  });

  btnMetodeList.forEach((btn) => {
    btn.addEventListener("click", () => {
      const method = btn.getAttribute("data-method") || "Transfer";
      if (metodeInput) metodeInput.value = method;
      btnMetodeList.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
    });
  });

  // Map selection (optional)
  if (btnSetLocation) {
    btnSetLocation.addEventListener("click", async () => {
      if (!mapContainer) return;
      mapContainer.style.display = "block";

      if (typeof L === "undefined") {
        return;
      }

      if (!map) {
        map = L.map("mapid").setView([-4.009, 119.629], 13);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          maxZoom: 19,
          attribution: "&copy; OpenStreetMap",
        }).addTo(map);
      }

      waypoints = [];
      let markers = [];

      const setAddress = async (latlng, which) => {
        const fallback = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;
        let addr = fallback;
        if (geocoder && typeof geocoder.reverse === "function") {
          try {
            await new Promise((resolve) => {
              geocoder.reverse(latlng, map.getZoom(), (results) => {
                if (results && results[0] && results[0].name) {
                  addr = results[0].name;
                }
                resolve();
              });
            });
          } catch (e) {
            // ignore
          }
        }

        if (which === "penerima") {
          if (alamatPenerimaInput) alamatPenerimaInput.value = addr;
          if (alamatPenerimaText) alamatPenerimaText.textContent = addr;
          if (locationInfoPenerima) locationInfoPenerima.style.display = "block";
        } else {
          if (alamatTokoInput) alamatTokoInput.value = addr;
          if (alamatTokoText) alamatTokoText.textContent = addr;
          if (locationInfoToko) locationInfoToko.style.display = "block";
        }
      };

      const onMapClick = async (e) => {
        if (!e || !e.latlng) return;

        if (waypoints.length >= 2) {
          waypoints = [];
          markers.forEach((m) => map.removeLayer(m));
          markers = [];
          if (control) {
            try {
              map.removeControl(control);
            } catch (err) {
              // ignore
            }
            control = null;
          }
        }

        waypoints.push(e.latlng);
        const m = L.marker(e.latlng).addTo(map);
        markers.push(m);

        if (waypoints.length === 1) {
          await setAddress(e.latlng, "toko");
        } else if (waypoints.length === 2) {
          await setAddress(e.latlng, "penerima");

          if (typeof L.Routing !== "undefined" && typeof L.Routing.control === "function") {
            control = L.Routing.control({
              waypoints: [waypoints[0], waypoints[1]],
              addWaypoints: false,
              routeWhileDragging: false,
              show: false,
            }).addTo(map);

            control.on("routesfound", (evt) => {
              const route = evt.routes && evt.routes[0];
              if (!route || !route.summary) return;

              const meters = route.summary.totalDistance || 0;
              const km = meters / 1000;
              const distanceEl = document.getElementById("distance");
              const ongkirEl = document.getElementById("ongkir");

              const roundedKm = Math.max(0.1, Math.round(km * 10) / 10);
              const ongkir = Math.round(5000 + roundedKm * 2000);

              if (distanceEl) distanceEl.textContent = `${roundedKm} km`;
              if (ongkirEl) ongkirEl.textContent = `Rp ${ongkir.toLocaleString("id-ID")}`;

              if (calculationResult) calculationResult.style.display = "block";
            });
          }
        }
      };

      map.off("click");
      map.on("click", onMapClick);
    });
  }

  if (pemesananForm) {
    pemesananForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const layanan = selectedLayanan || "TITIP";
      const alamatJemput = (alamatTokoInput && alamatTokoInput.value) ? alamatTokoInput.value.trim() : "";
      const alamatAntar = (alamatPenerimaInput && alamatPenerimaInput.value) ? alamatPenerimaInput.value.trim() : "";
      const namaPenerima = (namaPenerimaInput && namaPenerimaInput.value) ? namaPenerimaInput.value.trim() : "";
      const namaToko = (namaTokoInput && namaTokoInput.value) ? namaTokoInput.value.trim() : "";
      const wa = (whatsappInput && whatsappInput.value) ? whatsappInput.value.trim() : "";
      const ket = (keteranganInput && keteranganInput.value) ? keteranganInput.value.trim() : "";
      const metode = (metodeInput && metodeInput.value) ? metodeInput.value : "Transfer";

      if (namaPenerima === "" || namaToko === "" || wa === "") {
        alert("Lengkapi nama penerima, nama toko, dan WhatsApp.");
        return;
      }

      if (alamatJemput === "" || alamatAntar === "") {
        alert("Lengkapi lokasi jemput dan lokasi antar. Kamu bisa isi manual atau pilih lewat peta.");
        return;
      }

      const lines = [];
      lines.push(`Halo TITIP, saya mau pesan layanan: ${layanan}`);
      lines.push("");
      lines.push(`Nama penerima: ${namaPenerima}`);
      lines.push(`WhatsApp: ${wa}`);
      lines.push(`Nama toko/tempat: ${namaToko}`);
      lines.push("");
      lines.push(`Lokasi jemput: ${alamatJemput}`);
      lines.push(`Lokasi antar: ${alamatAntar}`);
      lines.push("");
      lines.push(`Metode pembayaran: ${metode}`);
      if (ket !== "") {
        lines.push(`Keterangan: ${ket}`);
      }

      const text = encodeURIComponent(lines.join("\n"));
      const phone = "6285179902326";
      window.open(`https://wa.me/${phone}?text=${text}`, "_blank");
    });
  }
});