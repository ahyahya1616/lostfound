// Fonction pour ouvrir la modal avec les données de l'objet
function openEditModal(id, title, description, location, latitude, longitude, categoryId) {
    // Remplir le formulaire avec les données existantes
    document.getElementById('editObjectId').value = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editDescription').value = description;
    document.getElementById('editLocation').value = location;
    document.getElementById('editLatitude').value = latitude;
    document.getElementById('editLongitude').value = longitude;
    
    // Sélectionner la bonne catégorie
    const categorySelect = document.getElementById('editCategory');
    if (categorySelect) {
        categorySelect.value = categoryId;
    }

    // Afficher la modal
    const modal = document.getElementById('editObjectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Fonction pour fermer la modal
function closeEditModal() {
    const modal = document.getElementById('editObjectModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Gestionnaire de soumission du formulaire
document.getElementById('editObjectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const objectId = document.getElementById('editObjectId').value;

    try {
        const response = await fetch(`/update-object/${objectId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Une erreur est survenue');
        }

        if (data.success) {
            // Afficher un message de succès
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg';
            successMessage.textContent = 'Modification réussie !';
            document.body.appendChild(successMessage);

            // Fermer la modal et recharger la page après un délai
            setTimeout(() => {
                closeEditModal();
                location.reload();
            }, 1500);
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert(error.message || 'Une erreur est survenue lors de la modification');
    }
});

// Fermer la modal si on clique en dehors
document.getElementById('editObjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Empêcher la propagation du clic depuis le contenu de la modal
document.querySelector('.modal-content').addEventListener('click', function(e) {
    e.stopPropagation();
});