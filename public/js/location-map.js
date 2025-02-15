let map;
let marker;
let mapModal;

function initMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error("❌ L'élément map est introuvable !");
        return;
    }

    map = L.map('map').setView([46.227638, 2.213749], 5);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    map.on('click', function(e) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker(e.latlng).addTo(map);
        
        // Effectuer une recherche inverse pour obtenir le nom de la ville
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
        .then(response => response.json())
        .then(data => {
            if (data.address) {
                const city = data.address.city || data.address.town || data.address.village || '';
                const locationInput = document.querySelector('input[name="form[location]"]');
                if (locationInput) {
                    locationInput.value = city;
                } else {
                    console.error("❌ Le champ de localisation est introuvable !");
                }
            }
        });
    });
}

function openMapModal() {
    mapModal = document.getElementById('mapModal');
    if (!mapModal) {
        console.error("❌ Le modal de la carte est introuvable !");
        return;
    }
    
    mapModal.style.display = 'flex';
    if (!map) {
        setTimeout(initMap, 100);
    } else {
        map.invalidateSize();
    }
}

function closeMapModal() {
    if (mapModal) {
        mapModal.style.display = 'none';
    }
}

function confirmLocation() {
    if (marker) {
        const position = marker.getLatLng();
        const latitudeInput = document.querySelector('input[name="form[latitude]"]');
        const longitudeInput = document.querySelector('input[name="form[longitude]"]');

        if (latitudeInput && longitudeInput) {
            latitudeInput.value = position.lat.toFixed(6);
            longitudeInput.value = position.lng.toFixed(6);
            console.log('Coordonnées mises à jour :', position.lat.toFixed(6), position.lng.toFixed(6));
        } else {
            console.error("❌ Les champs latitude et longitude sont introuvables !");
        }
    }
    closeMapModal();
}

document.addEventListener('DOMContentLoaded', function() {
    mapModal = document.getElementById('mapModal');
    if (!mapModal) {
        console.error("❌ Le modal de la carte est introuvable !");
    }

    // Ajouter les écouteurs d'événements
    const openMapButton = document.querySelector('[data-action="open-map"]');
    if (openMapButton) {
        openMapButton.addEventListener('click', openMapModal);
    }

    const confirmButton = document.querySelector('[data-action="confirm-location"]');
    if (confirmButton) {
        confirmButton.addEventListener('click', confirmLocation);
    }
});