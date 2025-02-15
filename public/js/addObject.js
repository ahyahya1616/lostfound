// Modal handling functions
function openObjectModal() { 
    const modal = document.getElementById('objectModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex', 'modal-open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal() {
    const modal = document.getElementById('objectModal');
    if (modal) {
        modal.classList.remove('flex', 'modal-open');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Wait for DOM to be fully loaded before adding event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Modal click outside handler
    const modal = document.getElementById('objectModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    // Escape key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Image upload handling
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreview');
    let selectedFiles = new DataTransfer();

    if (dropZone && imageInput && previewContainer) {
        function createImagePreview(src, file) {
            const previewWrapper = document.createElement('div');
            previewWrapper.className = 'relative rounded-lg overflow-hidden shadow-md';
            
            const img = document.createElement('img');
            img.src = src;
            img.className = 'w-full h-32 object-cover';

            const removeButton = document.createElement('button');
            removeButton.innerHTML = `
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            `;
            removeButton.className = 'absolute top-2 right-2 bg-red-500 rounded-full p-1 hover:bg-red-600';
            removeButton.addEventListener('click', () => {
                previewWrapper.remove();
                const newDataTransfer = new DataTransfer();
                Array.from(selectedFiles.files)
                    .filter(f => f !== file)
                    .forEach(f => newDataTransfer.items.add(f));
                selectedFiles = newDataTransfer;
                imageInput.files = selectedFiles.files;
            });

            previewWrapper.appendChild(img);
            previewWrapper.appendChild(removeButton);
            return previewWrapper;
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    selectedFiles.items.add(file);
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const preview = createImagePreview(e.target.result, file);
                        previewContainer.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('Seules les images sont autorisées');
                }
            });
            imageInput.files = selectedFiles.files;
        }

        // Add event listeners for drag and drop
        dropZone.addEventListener('click', () => imageInput.click());
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-teal-500');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-teal-500');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-teal-500');
            handleFiles(e.dataTransfer.files);
        });

        imageInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
    }

    // Form submission handling
    const form = document.getElementById('object-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const addObjectUrl = this.dataset.url;
            
            formData.delete('images[]');
            if (selectedFiles.files.length > 0) {
                Array.from(selectedFiles.files).forEach(file => {
                    formData.append('images[]', file);
                });
            }
            
            fetch(addObjectUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'envoi du formulaire');
            });
        });
    }
});