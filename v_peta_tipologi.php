<div id="map" style="width: 100%; height: 90vh;"></div>

<!-- CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-search/dist/leaflet-search.min.css" />
<style>
.container {
    width: 700px;
    height: 500px;
    position: absolute;
    }

.header {
    width: 100%;
    height: 60px;
   }

.content {
    width: 100%;
    height: calc(100% - 60px);
    padding: 20px;
    overflow: auto;
}

#map {
    position: absolute;
    top: 10px;     /* Geser ke bawah sejauh 100px */
    left: -10px;   /* Geser ke kiri sejauh 100px */
    width: calc(100% + 10px); /* Tambah lebar agar tidak terpotong */
    height: calc(100vh - 10px); /* Kurangi tinggi jika ingin menyesuaikan posisi turun */
}

/* Media query untuk layar kecil */
@media screen and (max-width: 1280px) {
    .container {
        width: 100%;
        height: 100vh;
        transform: none;
        top: 0;
        left: 0;
    }
}

.custom-marker .marker-wrapper {
    position: relative;
    width: 35px;
    height: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    padding: 5px;
}

.custom-marker .marker-wrapper img {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

/* Ikon proses dengan rotasi */
.custom-marker .rotate-icon {
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Gambar ikon dengan latar bulat (jika pakai <img>) */
.custom-marker .marker-wrapper img {
    width: 100%;
    height: auto;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
/* Segitiga bawah */
.custom-marker .marker-pointer {
    position: absolute;
    bottom: -10px;
    left: 50%;
    margin-left: -6px;
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 10px solid #1a1a1a; /* ubah dari #3498db ke hitam */
    opacity: 0.8;
}
</style>

<!-- JS -->
<script src="https://unpkg.com/leaflet-control-search/dist/leaflet-search.min.js"></script>

<script>
  // Peta Jalan Google Maps
var googleStreets = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    attribution: '© Google Maps',
    maxZoom: 20,
});

// Peta Satelit Google Maps
var googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
});

// Peta OpenStreetMap
var openStreetMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
});

// Peta CARTO Positron (Light)
var cartoLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    maxZoom: 19,
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
        '<a href="https://carto.com/attributions">CARTO</a>'
});

// Peta Hybrid Google Maps (Satelit + Label)
var googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
    attribution: 'Map data &copy; <a href="https://www.google.com/maps">Google Maps</a>'
});

// Inisialisasi peta
const map = L.map('map', {
    center: [-7.61322701124785, 111.08770607183197],
    zoom: 11,
    layers: [cartoLight]  // Layer default saat peta dimuat
});


// Daftar base map dengan label user-friendly
const baseLayers = {
    'Google Streets': googleStreets,
    'Google Satellite': googleSatellite,
    'OpenStreetMap': openStreetMap,
    'Carto Light': cartoLight,
    'Google Hybrid': googleHybrid
};

// Layer control dengan posisi di kanan atas
const layerControl = L.control.layers(baseLayers, null, {
    position: 'topright' // Pastikan posisi di kanan atas
}).addTo(map);

// Warna per kecamatan
const KecColors = {
    "Colomadu": "#e6d1ff",
    "Gondangrejo": "#e94d7f",
    "Jaten": "#986e90",
    "Jatipuro": "#830272",
    "Jatiyoso": "#2a675e",
    "Jenawi": "#0e4f4a",
    "Jumantono": "#3f0413",
    "Jumapolo": "#a4f8fa",
    "Karanganyar": "#bceee1",
    "Karangpandan": "#0aa20a",
    "Kebakkramat": "#f5b041",
    "Kerjo": "#58d68d",
    "Matesih": "#5dade2",
    "Mojogedang": "#af7ac5",
    "Ngargoyoso": "#f4d03f",
    "Tasikmadu": "#dc7633",
    "Tawangmangu": "#85929e"
};

let desaLayer;

// Tampilkan GeoJSON + search + popup
$.getJSON("<?= base_url('geojson/KRA_Fix2.geojson') ?>", function(data) {
    desaLayer = L.geoJson(data, {
        style: function(feature) {
            const namaKec = feature.properties.Kec;
            return {
                color: KecColors[namaKec] || "#999999",
                fillOpacity: 0.7
            };
        },
        onEachFeature: function(feature, layer) {
            const namaDesa = feature.properties.Desa || "Tidak diketahui";
            const namaKec = feature.properties.Kec || "Tidak diketahui";
            layer.bindPopup(`<strong>Desa:</strong> ${namaDesa}<br><strong>Kecamatan:</strong> ${namaKec}`);
        }
    }).addTo(map);

    // 🔍 Search bar (kiri atas)
    const searchControl = new L.Control.Search({
        layer: desaLayer,
        propertyName: 'Desa',
        marker: false,
        moveToLocation: function(latlng, title, map) {
            map.setView(latlng, 14);
        },
        position: 'topleft', // kiri atas
        initial: false,
        textPlaceholder: 'Cari nama desa...'
    });

    map.addControl(searchControl);
});

// 🟨 Legend (kanan bawah)
const legend = L.control({ position: 'bottomright' });

legend.onAdd = function(map) {
    const div = L.DomUtil.create('div', 'info legend');
    div.style.backgroundColor = 'rgba(255, 255, 255, 0.8)'; // putih dengan opacity 0.8
    div.style.padding = '10px';
    div.style.borderRadius = '8px';
    div.style.boxShadow = '0 0 8px rgba(0,0,0,0.2)';

    div.innerHTML += "<strong style='font-size:14px'>Kecamatan</strong><br>";
    for (const kec in KecColors) {
        div.innerHTML +=
            `<i style="background:${KecColors[kec]}; width:16px; height:16px; display:inline-block; margin-right:6px; border-radius:3px;"></i> ${kec}<br>`;
    }
    return div;
};
legend.addTo(map);


// === icon berdasarkan status ===
const iconProses = L.divIcon({
    className: 'custom-marker',
    html: `
        <div class="marker-wrapper" style="background-color: #e74c3c;">
            <div class="rotate-icon">
                <img src="<?= base_url('assets/img/spin2.png') ?>" alt="Proses">
            </div>
            <div class="marker-pointer"></div>
        </div>
    `,
    iconSize: [35, 45],
    iconAnchor: [17, 45],
    popupAnchor: [0, -40]
});

const iconDICABUT = L.divIcon({ 
    className: 'custom-marker',
    html: `
        <div class="marker-wrapper" style="background-color: #3498db;">
            <img src="<?= base_url('assets/img/checkwhite.png') ?>" alt="Dicabut">
            <div class="marker-pointer"></div>
        </div>
    `,
    iconSize: [35, 45],
    iconAnchor: [17, 45],
    popupAnchor: [0, -40]
});

const iconDamai = L.divIcon({
    className: 'custom-marker',
    html: `
        <div class="marker-wrapper" style="background-color: #2ecc71;">
            <img src="<?= base_url('assets/img/checkwhite.png') ?>" alt="Damai">
            <div class="marker-pointer"></div>
        </div>
    `,
    iconSize: [35, 45],
    iconAnchor: [17, 45],
    popupAnchor: [0, -40]
});

const iconPutusan = L.divIcon({
    className: 'custom-marker',
    html: `
        <div class="marker-wrapper" style="background-color: #f1c40f;">
            <img src="<?= base_url('assets/img/checkwhite.png') ?>" alt="Putusan">
            <div class="marker-pointer"></div>
        </div>
    `,
    iconSize: [35, 45],
    iconAnchor: [17, 45],
    popupAnchor: [0, -40]
});

// Fungsi menentukan icon berdasarkan status
function getCustomIcon(status) {
    if (status === "PROSES") {
        return iconProses;
    } else if (status === "DICABUT") {
        return iconDICABUT;
    } else if (status === "DAMAI") {
        return iconDamai;
    } else if (status === "PUTUSAN") {
        return iconPutusan;
    } else {
        return new L.Icon.Default();
    }
}


// Fungsi membuat popup content
function generatePopupContent(data) {
    // Menentukan warna background berdasarkan status
    let statusColor;
    if (data.status === "PROSES") {
        statusColor = "#f8d7da"; // Merah untuk status PROSES
    } else if (data.status === "DAMAI" || data.status === "DICABUT" || data.status === "PUTUSAN") {
        statusColor = "#d4edda"; // Hijau untuk status DAMAI, DICABUT, PUTUSAN
    } else {
        statusColor = "#f8d7da"; // Default (merah) jika status tidak dikenali
    }

    return `
    <div style="font-family: Arial, sans-serif; font-size: 13px; line-height: 1.4; width: 250px;">
        <div style="display: flex; align-items: center; margin-bottom: 8px;">
            <img src="https://cdn-icons-png.flaticon.com/512/854/854878.png" alt="Case Icon" width="24" height="24" style="margin-right: 8px;">
            <h4 style="margin: 0; font-size: 15px;">INFORMASI</h4>
        </div>
        <div style="margin-bottom: 5px;">
            <strong>No:</strong> ${data.no}<br>
            <strong>Tanggal:</strong> ${data.tanggal}<br>
            <strong>No. Perkara:</strong> ${data.noPerkara}
        </div>
        <details style="margin-bottom: 8px;">
            <summary style="cursor: pointer; font-weight: bold;">Para Pihak 👤</summary>
            <div style="margin-top: 4px;">
                <strong>Penggugat:</strong><br>
                ${data.penggugat}<br><br>
                <strong>Tergugat:</strong><br>
                ${data.tergugat}
            </div>
        </details>
        <details style="margin-bottom: 8px;">
            <summary style="cursor: pointer; font-weight: bold;">Letak Tanah 📍</summary>
            <div style="margin-top: 4px;">
                <strong>Desa/Kelurahan:</strong> ${data.desa}<br>
                <strong>Kecamatan:</strong> ${data.kecamatan}
            </div>
        </details>
        <div style="margin-bottom: 5px;">
            <strong>No. HM/C:</strong> ${data.hm}<br>
            <strong>Posisi BPN:</strong> ${data.posisiBpn}<br>
        </div>
        <div>
            <strong>Keterangan:</strong> 
            <span style="background-color: ${statusColor}; color: ${data.status === 'PROSES' ? '#721c24' : '#155724'}; padding: 2px 6px; border-radius: 5px; font-weight: bold;">
                ${data.status}
            </span><br>
            <strong>Tipologi:</strong> ${data.tipologi}
        </div>
    </div>`;
}


// Data marker dalam array
const markersData = [
    {
        latlng: [-7.566724214619986, 110.87230669890975],
        status: "PROSES",
        no: "01",
        tanggal: "20/12/2024",
        noPerkara: "87/Pdt.G/2024/PN.Krg",
        penggugat: "Leonardo Maracana dan Ivana Gladyasari",
        tergugat: "PT. Triperkasa Jaya Makmur (I),<br>Vincent (II),<br>Aldo Justin (III),<br>Kepala Kantor Pertanahan Karanganyar (IV)",
        desa: "Ngringo",
        kecamatan: "Jaten",
        hm: "HGB 2574",
        posisiBpn: "Tergugat IV",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah"
    },
    {
        latlng: [-7.556942464032274, 110.87325247024907],
        status: "PROSES",
        no: "02",
        tanggal: "24/12/2024",
        noPerkara: "88/Pdt.G/2024/PN.Krg",
        penggugat: "Ny. Maryam dan Ny. Sungkem",
        tergugat: "B. Bapak Gimin alias Suyadi (Tergugat I),<br>Bapak Eko dan Ibu Meyli (Tergugat II),<br>Bapak Dwi (Tergugat III),<br>Bapak Agus (Tergugat IV),<br>Bapak Heru Cahyadi (Tergugat V),<br>Bapak Claudius Ari Wibowo (Tergugat VI),<br>Kepala Kantor ATR/BPN Karanganyar (Tergugat VII)",
        desa: "Ngringo",
        kecamatan: "Jaten",
        hm: "HM 1276",
        posisiBpn: "Tergugat VII",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah"
    },
    {
        latlng: [-7.583439219206739, 110.95847046232312],
        status: "PROSES",
        no: "03",
        tanggal: "02/01/2025",
        noPerkara: "90/Pdt.G/2024/PN.Krg",
        penggugat: "Yuli Kristiyono",
        tergugat: "PT. Bank Rakyat Indonesia, Tbk. KCP Palur Karanganyar (Tergugat I),<br>Kepala Kantor PT. Bank Rakyat Indonesia, Tbk. Kanca Metro Solo Sudirman (Tergugat II),<br>Kepala Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarta (Turut Tergugat I),<br>Kepala ATR/BPN Kabupaten Karanganyar (Turut Tergugat II)",
        desa: "Gaum",
        kecamatan: "Tasikmadu",
        hm: "HM 2657",
        posisiBpn: "Turut Tergugat II",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.551539518946851, 110.88179262390268],
        status: "PROSES",
        no: "04",
        tanggal: "06/01/2025",
        noPerkara: "1/Pdt.G/2025/PN.Krg",
        penggugat: "Suharno",
        tergugat: "Susi Irawati, A.Md.Keb (Tergugat I),<br>Bayu Ari Nugroho, S.Sos (Tergugat II),<br>PT.Bank Tabungan Negara (Persero) Tbk,<br>Cabang Solo (Tergugat III),<br>Kantor Pertanahan/BPN Kab. Karanganyar (Turut Tergugat)",
        desa: "Ngringo",
        kecamatan: "Jaten",
        hm: "HM 11019",
        posisiBpn: "Turut Tergugat",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.530261566174945, 110.75343093079694],
        status: "PROSES",
        no: "05",
        tanggal: "24/01/2025",
        noPerkara: "17/Pdt.G/2025/PN.Smn",
        penggugat: "Endang Murdayani",
        tergugat: "Raden Dito Bimo Prasetyo Hasta Praman (Tergugat I),<br>PT. Permodalan Nasional Madani Kantor Cabang Yogyakarta (Tergugat II),<br>Badan Pertanahan Nasional (BPN) Kabupaten Karanganyar (Tergugat III)",
        desa: "Malangjiwan",
        kecamatan: "Colomadu",
        hm: "HM 3454",
        posisiBpn: "Tergugat III",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.529919239737006, 110.75956576923846],
        status: "PROSES",
        no: "06",
        tanggal: "05/02/2025",
        noPerkara: "13/Pdt.Bth/2025/PN.Krg",
        penggugat: "Deny Fibrata, S.Kom (Pelawan I),<br>dr. Anik Linda Susanti (Pelawan II)",
        tergugat: "Direktur PT. Bank Perkreditan Rakyat Pura Artha Kencana Jatipuro (Terlawan I),<br>Fajar Agung Nugroho (Terlawan II),<br>Kepala Kantor ATR/BPN Karanganyar (Terlawan III),<br>Kapolres Karanganyar (Turut Terlawan)",
        desa: "Gawanan",
        kecamatan: "Colomadu",
        hm: "HM 2836",
        posisiBpn: "Terlawan III",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.619118867757917, 111.04479714806556],
        status: "DICABUT",
        no: "07",
        tanggal: "10/02/2025",
        noPerkara: "14/Pdt.G/2025/PN.Krg",
        penggugat: "Sri Dwi Lasmiyati",
        tergugat: "PT. BKK Tasikmadu (Persero) Kab. Karanganyar (Tergugat I),<br>Kepala Kantor Pelayanan Kekayaan Negera dan Lelang (Tergugat II),<br>Kepala Kantor Otoritas Jasa Keuangan (OJK) Solo (Turut Tergugat I),<br>Kepala Kantor Pertanahan Kabupaten Karanganyar (Turut Tergugat II)",
        desa: "Ngemplak",
        kecamatan: "Karangpandan",
        hm: "HM 234",
        posisiBpn: "Turut Tergugat II",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.611136345770502, 110.93511161842933],
        status: "PROSES",
        no: "08",
        tanggal: "11/02/2025",
        noPerkara: "16/Pdt.G/2025/PN.Krg",
        penggugat: "Titik Sugiyatmi",
        tergugat: "Kepala Bidang Asset (Tergugat I),<br>Kepala Kelurahan Lalung (Tergugat II),<br>Ketua RW.004 Kelurahan Lalung (Tergugat III),<br>Elly Suryanto selaku Ketua RT. 004 RW. 004 Kelurahan Lalung (Tergugat IV),<br>Kepala Kantor Pertanahan Kabupaten Karanganyar (Turut Tergugat)",
        desa: "Lalung",
        kecamatan: "Karanganyar",
        hm: "HM 1087",
        posisiBpn: "Turut Tergugat",
        tipologi: "Letak dan Batas Bidang Tanah"
    },
    {
        latlng: [-7.543277399732883, 110.7899773464897],
        status: "PROSES",
        no: "09",
        tanggal: "-",
        noPerkara: "127/Pdt.G/2025/PA.Kra",
        penggugat: "Helga Anastasia Agusta Binti Djoko Subakto (Penggugat I),<br>Lintang Benowo Sakti Djarko Subakto (Penggugat II)",
        tergugat: "PT. Bank Viktoria Syariah (Tergugat I),<br>PT. Bank Victoria Tbk (Tergugat II),<br>Siti Maryani Binti hadi Purwono (Tergugat III),<br>Krisbiantoro Bin S Nano Edy Purwanto (Tergugat IV),<br>Suwarsi Sukiman, S.H. (Tergugat V),<br>Niek Hanifah (Tergugat VI),<br>Mochamad Rochim, S.H., M.Kn. (Tergugat VII),<br>Andang Tri Sununoko, S.H. (Tergugat VIII),<br>Kepala Kantor ATR/BPN Kabupaten Karanganyar (Turut Tergugat I),<br>Kepala Kantor ATR/BPN Kabupaten Boyolali (Turut Tergugat II),<br>Kepala Kantor ATR/BPN Kota Surakarta (Turut Tergugat III),<br>Kepala Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarta (Turut Tergugat IV)",
        desa: "Baturan",
        kecamatan: "Colomadu",
        hm: "HM 1391, 1174, 3022, 3023, 1053, 1113, 1114, 3127, 2890, 3306, 3904, 3062, 1817, 2400, 1055, 1460",
        posisiBpn: "Turut Tergugat I",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.551023429213298, 110.92458342404906],
        status: "PROSES",
        no: "10",
        tanggal: "14/02/2025",
        noPerkara: "169/Pdt.G/2025/PA.Kra",
        penggugat: "Suginem binti Marto Suwito",
        tergugat: "Harjogino bin Marto Suwito (Tergugat I),<br>Tuginem binti marto Suwito (Tergugat II),<br>Sutaryo bin Marto Suwito (Tergugat III),<br>Sutarman bin Marto Suwito (Tergugat IV),<br>Sumarni binti Sumar (Tergugat V),<br>Muryanto bin Sumar (Tergugat VI),<br>Prasetyo bin Sujiyo (Tergugat VII),<br>Nugrahanto bin Sujito (Tergugat VIII),<br>Kepala Kecamatan Tasikmadu, Kabupaten Karanganyar, Jawa Tengah (Turut Terguat I),<br>Kantor Pertanahan Kabupaten Karanganyar (Turut Tergugat II)",
        desa: "Kaling",
        kecamatan: "Tasikmadu",
        hm: "HM 907",
        posisiBpn: "Turut Tergugat II",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah"
    },
    {
        latlng: [-7.535842101341872, 110.79445156926974],
        status: "DAMAI",
        no: "11",
        tanggal: "27/02/2025",
        noPerkara: "180/Pdt.G/2025/PA.Ska",
        penggugat: "Bryan Prima Susanto, S.H., dan Fx Yuan Setiana, S.H. Selaku Kuasa dari Aji Bayuardi Sekti, S.E. bin Sekti Soetiman (Penggugat)",
        tergugat: "Dr. Galih Ayu Sartika SE, MM binti Ir. H. Sarwono, MM (Tergugat I),<br>Dra. Juli Astuti, S.H., MKn., Notaris & PPAT (Tergugat II),<br>Henry Hanafi Susanto (Turut Tergugat I),<br>Kepala Kantor Badan Pertanahan Karanganyar (Turut Tergugat II)",
        desa: "Klodran",
        kecamatan: "Colomadu",
        hm: "HM 1030",
        posisiBpn: "Turut Tergugat II",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah"
    },
    {
        latlng: [-7.717429270130999, 111.07753962485677],
        status: "PUTUSAN",
        no: "12",
        tanggal: "27/02/2025",
        noPerkara: "19/Pdt.G/2025/PN.Krg",
        penggugat: "Munandar",
        tergugat: "Titik Sri Wahyuningsih (Tergugat I),<br>Retno Dumilah (Terguga II),<br>Retno Wahyu (Tergugat III),<br>Balai Desa Wilayah Sungai Bengawan Solo (BBWSBS) (Turut Tergugat I),<br>Kantor ATR/BPN Kab. Karanganyar (Turut Tergugat II)",
        desa: "Tlobo",
        kecamatan: "Jatiyoso",
        hm: "HM 13",
        posisiBpn: "Turut Tergugat II",
        tipologi: "Pengadaan Tanah"
    },
    {
        latlng: [-7.5866636567662695, 111.02135233236862],
        status: "PROSES",
        no: "13",
        tanggal: "27/02/2025",
        noPerkara: "20/Pdt.G/2025/PN.Krg",
        penggugat: "M Umar Syahid, SE. SH., DKK",
        tergugat: "PT. Bank Mandiri (Persero) Tbk Solo (Tergugat I),<br>Kepala Kantor Badan Pertanahan Nasional Kabupaten Karanganyar (Terguga II),<br>Kementrian Keuangan Republik Indonesia Direktorat Jendral Kekayaan Negara Kantor Wilayah Jawa Timur Kantor Kekayaan Negara dan Lelang (KPKNL) Surakarta (Tergugat III)",
        desa: "Sewurejo",
        kecamatan: "Mojogedang",
        hm: "HM 2090",
        posisiBpn: "Tergugat II",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah"
    },
    {
        latlng: [-7.555239391775463, 111.02681537392944],
        status: "PROSES",
        no: "14",
        tanggal: "06/03/2025",
        noPerkara: "21/Pdt.G/2025/PN.Krg",
        penggugat: "Sumini",
        tergugat: "Satriyanto (Tergugat I),<br>PT.Permodalan Nasional Madani (PMN) - UlaMM (Tergugat II),<br>Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) (Tergugat III),<br>Badan Pertanahan Nasional (BPN) (Tergugat IV)",
        desa: "Pendem",
        kecamatan: "Mojogedang",
        hm: "HM 160",
        posisiBpn: "Tergugat IV",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.521290009807955, 110.82402346631933],
        status: "PROSES",
        no: "15",
        tanggal: "14/03/2025",
        noPerkara: "23/Pdt.G/2025/PN.Krg",
        penggugat: "Tasrif Salama (Penggugat I),<br>Syavira Nur Lita (Penggugat II),<br>Muhammas Shobirin (Penggugat III),<br>Nugroho Indra Kurniawan (Penggugat IV),<br>MG. Meydiana Turnip (Penggugat V),<br>Ichawan Imam Tantowi (Penggugat VI),<br>Sumarni (Penggugat VII)",
        tergugat: "Koperasi Simpan Pinjam Pembiayaan Syariah (KSPPS) Baitul Maal Wat Tamwil (BMT) Madusari (Tergugat I),<br>Sugeng Mulato, SE (Tergugat II),<br>Kantor BadanPertanahan Kabupaten Karanganyar Provinsi Jawa Tengah (Turut Tergugat I),<br>Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) Semarang (Turut Tergugat II)",
        desa: "Wonorejo",
        kecamatan: "Gondangrejo",
        hm: "HM 10379",
        posisiBpn: "Turut Tergugat I",
        tipologi: "Kepemilikan dan Penguasaan Hak atas Tanah"
    },
    {
        latlng: [-7.528264604628122, 110.7890349239684],
        status: "PROSES",
        no: "16",
        tanggal: "09/04/2025",
        noPerkara: "94/Pdt.G/2025/PN.Skt",
        penggugat: "Dra. Umi Sukiyatri",
        tergugat: "PT. Bank Bukopin Tbk Kantor Cabang Utama (KCU) (Tergugat I),<br>Kepala Kantor pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarata (Tergugat II),<br>Kepala Kantor Otoritas Jasa Keuangan (OJK) Solo (Turut Tergugat I),<br>Kepala Kantor Badan Pertanahan (BPN) Kota Surakarta (Turut Tergugat II),<br>Kepala Kantor Badan Pertanahan (BPN) Kabupaten Boyolali (Turut Tergugat III),<br>Kepala Kantor Badan Pertanahan (BPN) Kabupaten Karanganyar (Turut Tergugat IV)",
        desa: "<br>Klodran",
        kecamatan: "<br>Colomadu",
        hm: "<br>HM 826, 827, 828, 829, 131, 525, 1979",
        posisiBpn: "Turut Terguga IV",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.532054703303418, 110.74513414705066],
        status: "PROSES",
        no: "16",
        tanggal: "09/04/2025",
        noPerkara: "94/Pdt.G/2025/PN.Skt",
        penggugat: "Dra. Umi Sukiyatri",
        tergugat: "PT. Bank Bukopin Tbk Kantor Cabang Utama (KCU) (Tergugat I),<br>Kepala Kantor pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarata (Tergugat II),<br>Kepala Kantor Otoritas Jasa Keuangan (OJK) Solo (Turut Tergugat I),<br>Kepala Kantor Badan Pertanahan (BPN) Kota Surakarta (Turut Tergugat II),<br>Kepala Kantor Badan Pertanahan (BPN) Kabupaten Boyolali (Turut Tergugat III),<br>Kepala Kantor Badan Pertanahan (BPN) Kabupaten Karanganyar (Turut Tergugat IV)",
        desa: "<br>Malangjiwan",
        kecamatan: "<br>Colomadu",
        hm: "<br>HM 2797, 2765, 1917",
        posisiBpn: "Turut Terguga IV",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.501756617057214, 110.81597682185514],
        status: "PROSES",
        no: "16",
        tanggal: "09/04/2025",
        noPerkara: "94/Pdt.G/2025/PN.Skt",
        penggugat: "Dra. Umi Sukiyatri",
        tergugat: "PT. Bank Bukopin Tbk Kantor Cabang Utama (KCU) (Tergugat I),<br>Kepala Kantor pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarata (Tergugat II),<br>Kepala Kantor Otoritas Jasa Keuangan (OJK) Solo (Turut Tergugat I),<br>Kepala Kantor Badan Pertanahan (BPN) Kota Surakarta (Turut Tergugat II),<br>Kepala Kantor Badan Pertanahan (BPN) Kabupaten Boyolali (Turut Tergugat III),<br>Kepala Kantor Badan Pertanahan (BPN) Kabupaten Karanganyar (Turut Tergugat IV)",
        desa: "<br>Selokaton",
        kecamatan: "<br>Gondangrejo",
        hm: "<br>HM 269",
        posisiBpn: "Turut Terguga IV",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.606317825964456, 110.98585285382163],
        status: "PROSES",
        no: "17",
        tanggal: "11/04/2025",
        noPerkara: "30/Pdt.G/2025/PN.Krg",
        penggugat: "Santoso Bangun Wibawa (Penggugat I),<br>Nani Nurwina Setyo Kasino (Penggugat II)",
        tergugat: "PT. Bank Mega Syariah (Tergugat I),<br>Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarta (Tergugat II),<br>kepala Kantor Pertanahan kabupaten Karanganyar (Tergugat III)",
        desa: "<br>Popongan",
        kecamatan: "<br>Karanganyar",
        hm: "<br>HM 2550",
        posisiBpn: "Tergugat III",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
    {
        latlng: [-7.607442358162603, 110.96593070998871],
        status: "PROSES",
        no: "17",
        tanggal: "11/04/2025",
        noPerkara: "30/Pdt.G/2025/PN.Krg",
        penggugat: "Santoso Bangun Wibawa (Penggugat I),<br>Nani Nurwina Setyo Kasino (Penggugat II)",
        tergugat: "PT. Bank Mega Syariah (Tergugat I),<br>Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL) Surakarta (Tergugat II),<br>kepala Kantor Pertanahan kabupaten Karanganyar (Tergugat III)",
        desa: "<br>Tegalgede",
        kecamatan: "<br>Karanganyar",
        hm: "<br>HM 3649",
        posisiBpn: "Tergugat III",
        tipologi: "Kepemilkan dan Penguasaan Hak atas tanah (Hutang Pihutang)"
    },
];

// Membuat array baru untuk simpan marker
const markers = [];

markersData.forEach(data => {
    const customIcon = getCustomIcon(data.status);

    const marker = L.marker(data.latlng, { icon: customIcon })
        .addTo(map)
        .bindPopup(generatePopupContent(data));

    markers.push({
        data: data,
        marker: marker
    });
});


// Loop semua marker
markersData.forEach(data => {
    const customIcon = getCustomIcon(data.status);

    const marker = L.marker(data.latlng, { icon: customIcon })
        .addTo(map)
        .bindPopup(generatePopupContent(data));
});

// === Tambahkan LEGEND Status di pojok kiri bawah (popup buka-tutup) ===
const legendStatus = L.control({ position: 'bottomleft' });

legendStatus.onAdd = function (map) {
    const div = L.DomUtil.create('div', 'info legend');
    div.innerHTML = `
        <div style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); font-family: Arial, sans-serif; font-size: 12px; width: 180px;">
            <button id="toggleLegendStatus" style="background-color: #007bff; color: white; border: none; padding: 6px 10px; border-radius: 5px; cursor: pointer; font-size: 12px; width: 100%; margin-bottom: 5px;">
                Tampilkan Status ⬇️
            </button>
            <div id="legendStatusContent" style="display: none;">

                <!-- PROSES -->
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="background-color: #e74c3c; border-radius: 50%; width: 28px; height: 28px; display: flex; justify-content: center; align-items: center; margin-right: 8px;">
                        <img src="<?= base_url('assets/img/spin2.png') ?>" alt="Proses" style="width: 16px; height: 16px;">
                    </div>
                    PROSES
                </div>

                <!-- DICABUT -->
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="background-color: #3498db; border-radius: 50%; width: 28px; height: 28px; display: flex; justify-content: center; align-items: center; margin-right: 8px;">
                        <img src="<?= base_url('assets/img/checkwhite.png') ?>" alt="Dicabut" style="width: 16px; height: 16px;">
                    </div>
                    DICABUT
                </div>

                <!-- DAMAI -->
                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                    <div style="background-color: #2ecc71; border-radius: 50%; width: 28px; height: 28px; display: flex; justify-content: center; align-items: center; margin-right: 8px;">
                        <img src="<?= base_url('assets/img/checkwhite.png') ?>" alt="Damai" style="width: 16px; height: 16px;">
                    </div>
                    DAMAI
                </div>

                <!-- PUTUSAN -->
                <div style="display: flex; align-items: center;">
                    <div style="background-color: #f1c40f; border-radius: 50%; width: 28px; height: 28px; display: flex; justify-content: center; align-items: center; margin-right: 8px;">
                        <img src="<?= base_url('assets/img/checkwhite.png') ?>" alt="Putusan" style="width: 16px; height: 16px;">
                    </div>
                    PUTUSAN
                </div>

            </div>
        </div>
    `;
    L.DomEvent.disableClickPropagation(div);

    setTimeout(() => {
        const toggleBtn = document.getElementById('toggleLegendStatus');
        const legendContent = document.getElementById('legendStatusContent');

        toggleBtn.addEventListener('click', () => {
            const isHidden = legendContent.style.display === "none";
            legendContent.style.display = isHidden ? "block" : "none";
            toggleBtn.innerHTML = isHidden ? "Sembunyikan Status ⬆️" : "Tampilkan Status ⬇️";
        });
    }, 500);

    return div;
};

legendStatus.addTo(map);
// === SEARCH MANUAL MARKER ===
document.getElementById('btnNavbarSearch').addEventListener('click', function() {
    const keyword = document.getElementById('searchMarkerInput').value.toLowerCase().trim();
    if (!keyword) {
        alert('Masukkan kata kunci pencarian!');
        return;
    }

    let found = false;

    markers.forEach(obj => {
        const data = obj.data; // data JSON
        const marker = obj.marker; // marker leaflet

        const searchFields = [
            data.no,
            data.tanggal,
            data.noPerkara,
            data.penggugat,
            data.tergugat,
            data.desa,
            data.kecamatan,
            data.hm,
            data.status,
            data.tipologi
        ].join(' ').toLowerCase();

        if (searchFields.includes(keyword) && !found) {
            // Zoom dan center ke marker dengan animasi
            map.flyTo(data.latlng, 18, {
                animate: true,
                duration: 2
            });

            marker.openPopup(); // Buka popup marker
            bounceMarker(marker); // Panggil fungsi bounce
            found = true;
        }
    });

    if (!found) {
        alert('Data tidak ditemukan!');
    }
});

// Tekan Enter langsung cari
document.getElementById('searchMarkerInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        document.getElementById('btnNavbarSearch').click();
    }
});

// === RESET ZOOM BUTTON ===
// Kita tambahkan tombol ke dalam legendStatus

setTimeout(() => {
    const legendContentDiv = document.getElementById('legendStatusContent').parentElement; // ambil container luar
    const resetButton = document.createElement('button');

    resetButton.innerHTML = 'Reset Zoom';
    resetButton.style.backgroundColor = '#28a745'; // Warna hijau biar beda
    resetButton.style.color = 'white';
    resetButton.style.border = 'none';
    resetButton.style.padding = '6px 10px';
    resetButton.style.borderRadius = '5px';
    resetButton.style.cursor = 'pointer';
    resetButton.style.fontSize = '12px';
    resetButton.style.width = '100%';
    resetButton.style.marginTop = '5px'; // kasih jarak atas dikit

    // Tambahkan tombol ke dalam div legend
    legendContentDiv.appendChild(resetButton);

    // Fungsi reset zoom (kembali ke center awal)
    resetButton.addEventListener('click', function() {
        map.setView([-7.61322701124785, 111.08770607183197], 11); // Center + zoom 11
    });
}, 500); // tunggu sampai legend sudah terbuat

</script>
