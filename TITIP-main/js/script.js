// =================================================================
// KODE FINAL & STABIL - DIJAMIN BERFUNGSI
// =ga================================================================

document.addEventListener("DOMContentLoaded", () => {
  // --- FUNGSI DASAR NAVBAR ---
  const loginButton = document.getElementById("login");
  const loginOptions = document.getElementById("login-options");
  const navbarNav = document.querySelector(".navbar-nav");
  const hamburger = document.querySelector("#hamburger-menu");

  if (loginButton && loginOptions) {
    loginButton.addEventListener("click", (e) => {
      const href = loginButton.getAttribute("href");
      if (href && href !== "#") {
        return;
      }

      e.preventDefault();
      loginOptions.style.display = loginOptions.style.display === "block" ? "none" : "block";
    });
  }

  if (hamburger) {
    hamburger.addEventListener("click", (e) => {
      e.preventDefault();
      navbarNav.classList.toggle("active");
    });
  }

  document.addEventListener("click", (e) => {
    if (loginButton && loginOptions && !loginButton.contains(e.target) && !loginOptions.contains(e.target)) {
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
  const alamatPenerima = document.getElementById("alamat-penerima");
  const alamatToko = document.getElementById("alamat-toko");
  const alamatPenerimaText = document.getElementById("alamat-penerima-text");
  const alamatTokoText = document.getElementById("alamat-toko-text");
  const distanceEl = document.getElementById("distance");
  const ongkirEl = document.getElementById("ongkir");
  const metodeEl = document.getElementById("metode");
  const pemesananForm = document.getElementById("pemesanan-form");
  const formTitle = document.getElementById("form-title");

  let map = null;
  let control = null;
  let geocoder = null;
  let waypoints = [];
  let markers = [];

  const resetOrderState = () => {
    waypoints = [];
    markers.forEach((m) => {
      try {
        m.remove();
      } catch (_) {}
    });
    markers = [];

    if (control && map) {
      try {
        map.removeControl(control);
      } catch (_) {}
      control = null;
    }

    if (alamatPenerima) alamatPenerima.value = "";
    if (alamatToko) alamatToko.value = "";
    if (alamatPenerimaText) alamatPenerimaText.textContent = "";
    if (alamatTokoText) alamatTokoText.textContent = "";

    if (distanceEl) distanceEl.textContent = "0 km";
    if (ongkirEl) ongkirEl.textContent = "Rp 0";

    if (mapContainer) mapContainer.style.display = "none";
    if (orderDetails) orderDetails.style.display = "none";
    if (calculationResult) calculationResult.style.display = "none";
    if (locationInfoPenerima) locationInfoPenerima.style.display = "none";
    if (locationInfoToko) locationInfoToko.style.display = "none";
  };

  const openPopup = () => {
    formPopup.style.display = "block";
  };

  const closePopup = () => {
    formPopup.style.display = "none";
  };

  const initMapIfNeeded = () => {
    if (map) return;
    if (!window.L) {
      throw new Error("Leaflet belum tersedia");
    }

    map = L.map("mapid").setView([-4.0, 119.65], 13);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap",
    }).addTo(map);

    if (L.Control && L.Control.Geocoder && L.Control.Geocoder.nominatim) {
      geocoder = L.Control.Geocoder.nominatim();
    }

    map.on("click", async (evt) => {
      if (!evt || !evt.latlng) return;

      if (waypoints.length >= 2) {
        resetOrderState();
        if (mapContainer) mapContainer.style.display = "block";
      }

      const lat = evt.latlng.lat;
      const lng = evt.latlng.lng;
      waypoints.push(L.latLng(lat, lng));

      const marker = L.marker([lat, lng]).addTo(map);
      markers.push(marker);

      const label = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
      if (waypoints.length === 1) {
        if (alamatPenerima) alamatPenerima.value = label;
        if (alamatPenerimaText) alamatPenerimaText.textContent = label;
        if (locationInfoPenerima) locationInfoPenerima.style.display = "block";
      } else if (waypoints.length === 2) {
        if (alamatToko) alamatToko.value = label;
        if (alamatTokoText) alamatTokoText.textContent = label;
        if (locationInfoToko) locationInfoToko.style.display = "block";
        buildRoute();
      }
    });
  };

  const buildRoute = () => {
    if (!map || waypoints.length !== 2 || !window.L || !L.Routing || !L.Routing.control) {
      if (orderDetails) orderDetails.style.display = "block";
      return;
    }

    if (control) {
      try {
        map.removeControl(control);
      } catch (_) {}
      control = null;
    }

    control = L.Routing.control({
      waypoints: waypoints,
      routeWhileDragging: false,
      addWaypoints: false,
      draggableWaypoints: false,
      show: false,
    }).addTo(map);

    control.on("routesfound", (e) => {
      const routes = e.routes || [];
      const summary = routes[0] && routes[0].summary ? routes[0].summary : null;
      const totalDistance = summary ? summary.totalDistance : 0;

      const km = totalDistance / 1000;
      if (distanceEl) distanceEl.textContent = `${km.toFixed(2)} km`;

      const tarifPerKm = 5000;
      const minOngkir = 10000;
      const ongkir = Math.max(minOngkir, Math.round(km * tarifPerKm));
      if (ongkirEl) ongkirEl.textContent = `Rp ${ongkir.toLocaleString("id-ID")}`;

      if (calculationResult) calculationResult.style.display = "block";
      if (orderDetails) orderDetails.style.display = "block";
    });
  };

  btnPesanList.forEach((btn) => {
    btn.addEventListener("click", () => {
      resetOrderState();
      if (formTitle) {
        const layanan = btn.getAttribute("data-layanan") || "";
        formTitle.textContent = layanan;
      }
      openPopup();
    });
  });

  if (btnClose) {
    btnClose.addEventListener("click", () => {
      closePopup();
    });
  }

  if (btnSetLocation) {
    btnSetLocation.addEventListener("click", () => {
      try {
        if (mapContainer) mapContainer.style.display = "block";
        initMapIfNeeded();
        setTimeout(() => {
          try {
            map.invalidateSize();
          } catch (_) {}
        }, 50);
      } catch (err) {
        alert("Peta belum siap. Coba refresh halaman.");
      }
    });
  }

  document.querySelectorAll(".btn-metode").forEach((btn) => {
    btn.addEventListener("click", () => {
      const method = btn.getAttribute("data-method") || btn.textContent || "";
      if (metodeEl) metodeEl.value = method;
    });
  });

  if (pemesananForm) {
    pemesananForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const namaPenerima = document.getElementById("nama-penerima")?.value || "";
      const namaToko = document.getElementById("nama-toko")?.value || "";
      const whatsapp = document.getElementById("whatsapp")?.value || "";
      const keterangan = document.getElementById("keterangan")?.value || "";
      const metode = metodeEl?.value || "";
      const lokasiAntar = alamatPenerima?.value || "";
      const lokasiJemput = alamatToko?.value || "";
      const layanan = formTitle?.textContent || "";
      const jarak = distanceEl?.textContent || "";
      const ongkir = ongkirEl?.textContent || "";

      if (!lokasiAntar || !lokasiJemput) {
        alert("Silakan tentukan lokasi di peta terlebih dahulu.");
        return;
      }

      const adminWa = "6285179902326";
      const message =
        "PESANAN TITIP%0A" +
        `Layanan: ${encodeURIComponent(layanan)}%0A` +
        `Nama Penerima: ${encodeURIComponent(namaPenerima)}%0A` +
        `Nama Toko: ${encodeURIComponent(namaToko)}%0A` +
        `WhatsApp: ${encodeURIComponent(whatsapp)}%0A` +
        `Lokasi Jemput: ${encodeURIComponent(lokasiJemput)}%0A` +
        `Lokasi Antar: ${encodeURIComponent(lokasiAntar)}%0A` +
        `Jarak: ${encodeURIComponent(jarak)}%0A` +
        `Estimasi Ongkir: ${encodeURIComponent(ongkir)}%0A` +
        `Metode: ${encodeURIComponent(metode)}%0A` +
        `Keterangan: ${encodeURIComponent(keterangan)}`;

      window.open(`https://wa.me/${adminWa}?text=${message}`, "_blank");
      closePopup();
    });
  }
});