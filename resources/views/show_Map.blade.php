<!DOCTYPE html>
<html>
<head>
    <title>عرض المسافة على الخريطة</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        * {
            box-sizing: border-box;
            direction: rtl;
        }

        main {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 20px auto;
        }

        #map {
            height: 800px;
            width: 70%;
        }

        #sidebar {
            width: 260px;
            padding: 10px;
            display: flex;
            flex-direction: column;
        }

        h4 {
            text-align: center;
        }

        .lineName {
            border: 1px solid black;
            padding: 10px;
            margin: 5px;
            cursor: pointer;
            text-align: center;
            max-width: 100px;
        }

        .lineName:hover {
            background-color: green;
            color: white;
        }

        #lines {
            display: flex;
            flex-wrap: wrap;
            max-width: 250px;
            max-height: 700px;
            overflow: scroll;
        }
    </style>
    <!-- PWA  -->
<meta name="theme-color" content="#6777ef"/>
<link rel="apple-touch-icon" href="{{ asset('logo.PNG') }}">
<link rel="manifest" href="{{ asset('/manifest.json') }}">
</head>
<body>
    <main>
        <div id="sidebar">
            <h4>كل الخطوط</h4>
            <div id="lines">
                @foreach ($distances as $distance)
                    <div class='lineName' data-id="{{ $distance->id }}">{{ $distance->line_name }}</div>
                @endforeach
            </div>
        </div>
        <div id="map"></div>
    </main>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([34.80816568435577, 10.478897094726562], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var polyline, startMarker, endMarker, midPopup, midMarkers = [];

        document.querySelectorAll('.lineName').forEach(function(div) {
            div.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                fetch(`/getDistance/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        // إزالة العناصر السابقة من الخريطة
                        if (polyline) map.removeLayer(polyline);
                        if (startMarker) map.removeLayer(startMarker);
                        if (endMarker) map.removeLayer(endMarker);
                        if (midPopup) map.removeLayer(midPopup);
                        midMarkers.forEach(marker => map.removeLayer(marker));
                        midMarkers = [];

                        // معالجة البيانات الجديدة
                        var points = JSON.parse(data.points);
                        var coordinates = JSON.parse(data.geometry).coordinates;
                        var latlngs = coordinates.map(coord => [coord[1], coord[0]]);

                        // رسم المسار
                        polyline = L.polyline(latlngs, { color: 'green' }).addTo(map);
                        map.fitBounds(polyline.getBounds());

                        // إضافة نقطة البداية
                        startMarker = L.marker([points[0][1], points[0][0]])
                            .addTo(map)
                            .bindPopup('نقطة البداية').openPopup();

                        // إضافة نقطة النهاية
                        endMarker = L.marker([points[points.length - 1][1], points[points.length - 1][0]])
                            .addTo(map)
                            .bindPopup('نقطة النهاية').openPopup();

                        // إضافة نقاط الوسط
                        for (var i = 1; i < points.length - 1; i++) {
                            var midMarker = L.marker([points[i][1], points[i][0]])
                                .addTo(map)
                                .bindPopup(`نقطة ${i + 1}`);
                            midMarkers.push(midMarker);
                        }

                        // إضافة معلومات عن المسافة في منتصف المسار
                        var midpointIndex = Math.floor(latlngs.length / 2);
                        var midpoint = latlngs[midpointIndex];
                        midPopup = L.popup()
                            .setLatLng(midpoint)
                            .setContent('المسافة: ' + data.distance + ' كم')
                            .addTo(map);
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
    <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if (!navigator.serviceWorker.controller) {
            navigator.serviceWorker.register("/sw.js").then(function (reg) {
                console.log("Service worker has been registered for scope: " + reg.scope);
            });
        }
    </script>
</body>
</html>
