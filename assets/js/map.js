document.addEventListener("DOMContentLoaded", function () {
    var mapContainer = document.getElementById('map');
    if (mapContainer) {
        var map = L.map('map').setView([48.8566, 2.3522], 13); // Paris par défaut

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([48.8566, 2.3522], { draggable: true }).addTo(map);

        // Déplacer le marqueur
        marker.on('dragend', function (event) {
            var position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat.toFixed(6);
            document.getElementById('longitude').value = position.lng.toFixed(6);

            fetchReverseGeocoding(position.lat, position.lng);
        });

        // Géocodage Nominatim API
        document.getElementById('create_event_form_location_street').addEventListener('change', function () {
            var address = this.value;
            if (address.trim()) {
                fetchGeocoding(address);
            }
        });

        // Récupérer les coordonnées d'une adresse
        function fetchGeocoding(address) {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);

                        // MAJ marqueur et carte
                        map.setView([lat, lon], 15);
                        marker.setLatLng([lat, lon]);

                        // Remplir latitude/longitude
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lon.toFixed(6);

                        // Remplir l'adresse
                        document.getElementById('create_event_form_location_name').value = data[0].display_name || '';
                    } else {
                        alert("Adresse non trouvée !");
                    }
                })
                .catch(error => console.error('Map error :', error));
        }

        // Récupérer l'adresse inverse (recherche basée sur gps)
        function fetchReverseGeocoding(lat, lon) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        // Remplir les champs de l'adresse
                        document.getElementById('create_event_form_location_name').value = data.address.road || '';
                        document.getElementById('create_event_form_location_street').value = data.address.neighbourhood || data.address.city || '';
                    }
                })
                .catch(error => console.error('Map reverse geocoding error:', error));
        }

        // Autocomplétion
        var addressInput = document.getElementById('create_event_form_location_street');
        var autocompleteList = document.createElement('ul');
        autocompleteList.classList.add('autocomplete-list');
        autocompleteList.style.width = addressInput.offsetWidth + 'px';

        autocompleteList.style.backgroundColor = 'white';
        autocompleteList.style.color = '#333'; // Texte foncé sur fond blanc
        autocompleteList.style.border = '1px solid #ccc';
        autocompleteList.style.borderRadius = '4px';
        autocompleteList.style.padding = '5px 0';
        autocompleteList.style.position = 'absolute';
        autocompleteList.style.zIndex = '1000';
        autocompleteList.style.listStyle = 'none';
        autocompleteList.style.maxHeight = '200px';
        autocompleteList.style.overflowY = 'auto';

        addressInput.parentNode.appendChild(autocompleteList);
        var debounceTimeout;

        addressInput.addEventListener('input', function () {
            clearTimeout(debounceTimeout);
            var query = addressInput.value.trim();

            if (query.length < 3) {
                autocompleteList.innerHTML = ''; // Efface les suggestions < 3
                return;
            }

            debounceTimeout = setTimeout(function () {
                // Requête API à Nominatim pour l'autocomplétion
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        autocompleteList.innerHTML = ''; // Efface les anciennes suggestions

                        if (data.length > 0) {
                            data.forEach(item => {
                                var listItem = document.createElement('li');
                                listItem.textContent = item.display_name;
                                listItem.style.cursor = 'pointer';
                                listItem.addEventListener('click', function () {
                                    // Lorsque l'utilisateur clique sur une suggestion
                                    addressInput.value = item.display_name;
                                    autocompleteList.innerHTML = ''; // Efface les suggestions

                                    // Récupération des coordonnées de l'adresse
                                    var lat = parseFloat(item.lat);
                                    var lon = parseFloat(item.lon);

                                    // Mise à jour des champs latitude/longitude
                                    document.getElementById('create_event_form_location_latitude').value = lat.toFixed(6);
                                    document.getElementById('create_event_form_location_longitude').value = lon.toFixed(6);
                                });
                                autocompleteList.appendChild(listItem);
                            });
                        } else {
                            autocompleteList.innerHTML = '<li>No results found</li>';
                        }
                    })
                    .catch(error => console.error('Autocomplete error:', error));
            }, 500); // Délais de 500ms
        });

        // Fermer la liste des suggestions lorsqu'on clique ailleurs
        document.addEventListener('click', function (e) {
            if (!autocompleteList.contains(e.target) && e.target !== addressInput) {
                autocompleteList.innerHTML = ''; // Efface les suggestions
            }
        });
    }
});
