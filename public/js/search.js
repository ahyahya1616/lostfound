document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const categoryInput = document.getElementById('category');
    const locationInput = document.getElementById('location');
    const typeInput = document.getElementById('type');
    const searchResults = document.getElementById('search-results');
    const initialContent = document.getElementById('initial-content');
    
    let debounceTimer;
    
    // Fonction pour initialiser Swiper de manière sécurisée
    function initSwiper(container) {
        if (!container) return;
        
        const slides = container.querySelectorAll('.swiper-slide');
        if (slides.length < 2) {
            // Si un seul slide, désactiver le loop
            return new Swiper(container, {
                direction: 'horizontal',
                loop: false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.swiper-pagination',
                }
            });
        }
        
        return new Swiper(container, {
            direction: 'horizontal',
            loop: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
            }
        });
    }

    // Fonction pour gérer la suppression d'objets
    function attachDeleteHandlers() {
        document.querySelectorAll('[onclick^="deleteObject"]').forEach(button => {
            const originalOnClick = button.getAttribute('onclick');
            button.onclick = (e) => {
                e.preventDefault();
                const deleteUrl = originalOnClick.match(/'([^']+)'/)[1];
                deleteObject(deleteUrl);
            };
        });
    }

    // Fonction pour gérer la galerie d'images
    function attachGalleryHandlers() {
        document.querySelectorAll('[data-action="open-gallery"]').forEach(image => {
            image.addEventListener('click', function() {
                const objectContainer = this.closest('article');
                const imageElements = objectContainer.querySelectorAll('img[data-action="open-gallery"]');
                const images = Array.from(imageElements).map(img => img.src);
                const initialIndex = parseInt(this.dataset.imageIndex);
                if (typeof openGallery === 'function') {
                    openGallery(images, initialIndex);
                }
            });
        });
    }

  

    // Fonction pour supprimer un objet
    function deleteObject(deleteUrl) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet objet ?')) {
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur réseau');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const articleElement = document.querySelector(`article:has([onclick*="${deleteUrl}"])`);
                    if (articleElement) {
                        articleElement.remove();
                    }
                    showNotification('Objet supprimé avec succès', 'success');
                } else {
                    showNotification(data.message || 'Erreur lors de la suppression', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Erreur lors de la suppression', 'error');
            });
        }
    }

    // Fonction pour afficher les notifications en dark mode
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 p-4 rounded-lg ${
            type === 'success' ? 'bg-green-600' : 'bg-red-600'
        } text-white z-50`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    function reinitializeComponents() {
        // Réinitialiser Swiper
        if (typeof Swiper !== 'undefined') {
            const swiperContainers = document.querySelectorAll('.swiper-container');
            swiperContainers.forEach(container => {
                // Détruire l'ancienne instance si elle existe
                if (container.swiper) {
                    container.swiper.destroy();
                }
                // Créer une nouvelle instance
                initSwiper(container);
            });
        }

      

        // Réinitialiser les gestionnaires d'événements
        attachEventHandlers();
    }

    // Fonction pour attacher tous les gestionnaires d'événements
    function attachEventHandlers() {
        // Gestionnaire pour la pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = new URL(link.href);
                const page = url.searchParams.get('page') || 1;
                await performSearch(page);
                window.scrollTo(0, 0);
            });
        });

        attachDeleteHandlers();
        attachGalleryHandlers();
    }

    // Fonction principale de recherche
    async function performSearch(page = 1) {
        const hasSearchCriteria = [searchInput, categoryInput, locationInput, typeInput].some(
            input => input?.value && input.value !== '' && input.value !== 'tous' && input.value !== 'Toutes les catégories'
        );
    
        if (!hasSearchCriteria) {
            searchResults.style.display = 'none';
            initialContent.style.display = 'block';
            return;
        }
    
        try {
            const params = new URLSearchParams({
                query: searchInput?.value || '',
                category: categoryInput?.value || '',
                location: locationInput?.value || '',
                type: typeInput?.value || '',
                page: page
            });
    
            searchResults.innerHTML = `
                <div class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-300"></div>
                </div>
            `;
            searchResults.style.display = 'block';
            initialContent.style.display = 'none';
    
            const response = await fetch(`/search-objects?${params}`);
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            
            const html = await response.text();
            searchResults.innerHTML = html;
    
            reinitializeComponents();
            
            // Mettre à jour l'URL
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
        } catch (error) {
            console.error('Search error:', error);
            searchResults.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-red-400 text-xl mb-4">Une erreur est survenue lors de la recherche.</div>
                    <button onclick="window.performSearch(1)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Réessayer
                    </button>
                </div>
            `;
        }
    }

    // Rendre performSearch accessible globalement pour le bouton "Réessayer"
    window.performSearch = performSearch;

    function debounceSearch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => performSearch(1), 300);
    }

    // Attacher les écouteurs d'événements pour la recherche
    [searchInput, categoryInput, locationInput, typeInput].forEach(input => {
        if (input) {
            input.addEventListener('input', debounceSearch);
            input.addEventListener('change', debounceSearch);
        }
    });

    // Gérer le retour en arrière du navigateur
    window.addEventListener('popstate', function(e) {
        const url = new URL(window.location.href);
        const page = url.searchParams.get('page') || 1;
        performSearch(page);
    });
});