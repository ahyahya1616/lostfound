// Fonction pour afficher les onglets (Objets Perdus / Trouvés)
function showTab(tabName) {
    window.location.href = "/mes-objets?tab=" + tabName;
}

// Fonction de chargement pour la pagination "Charger Plus"
let currentPage = 1;
let currentTab = document.querySelector('.tab-button.active')?.getAttribute('data-tab') || 'lost';

function loadMoreObjects() {
    const btn = document.querySelector('.load-more-btn');
    btn.innerHTML = 'Chargement...';
    btn.disabled = true;

    fetch(`/mes-objets/load-more?page=${++currentPage}&tab=${currentTab}`)
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = 'Charger Plus d\'Objets';
            btn.disabled = false;

            if (data.objects.length > 0) {
                const container = document.querySelector('.objects-list');
                
                data.objects.forEach(object => {
                    const cardHTML = `
                        <article class="object-card group">
                            <div class="object-inner-card">
                                <div class="object-front">
                                    <img src="${object.image}" alt="${object.title}" class="object-image">
                                    <div class="object-info">
                                        <h2 class="object-title">${object.title}</h2>
                                        <p class="object-description">${object.description.slice(0, 50)}...</p>
                                    </div>
                                </div>
                                <div class="object-back">
                                    <div class="object-details">
                                        <p><strong>Publié le :</strong> ${object.createdAt}</p>
                                        <p><strong>Status :</strong> 
                                            <span class="status ${object.status === 'Perdu' ? 'lost' : 'found'}">
                                                ${object.status}
                                            </span>
                                        </p>
                                        <p><strong>Localisation :</strong> ${object.location}</p>
                                        <button class="action-button text-blue-400 hover:bg-blue-800 hover:text-white transition px-4 py-2 rounded">
                                            Contacter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    `;
                    container.insertAdjacentHTML('beforeend', cardHTML);
                });
            }

            if (!data.hasMore) {
                btn.style.display = 'none'; // Cacher le bouton si plus d'objets
            }
        })
        .catch(error => {
            console.error("Erreur lors du chargement des objets :", error);
            btn.innerHTML = 'Charger Plus d\'Objets';
            btn.disabled = false;
        });
}
