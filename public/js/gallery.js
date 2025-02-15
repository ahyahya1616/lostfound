document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du Swiper pour les carousels d'images
    const swiperContainers = document.querySelectorAll('.swiper-container');

    swiperContainers.forEach(container => {
        const objectId = container.dataset.objectId;
        new Swiper(container, {
            // Optional parameters
            direction: 'horizontal',
            loop: true,

            // Navigation arrows
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
              // If we need pagination
            pagination: {
                el: '.swiper-pagination',
            },
        });
    });

     // Gestion de la galerie plein écran
    const galleryModal = document.getElementById('imageGallery');
    const closeGalleryButton = document.getElementById('closeGallery');
    const gallerySwiperContainer = document.querySelector('#imageGallery .swiper-container-gallery');
    let gallerySwiper;

    // Fonction pour ouvrir la galerie plein écran
    function openGallery(images, initialIndex) {
        // Vider le contenu précédent du Swiper
        gallerySwiperContainer.innerHTML = '<div class="swiper-wrapper"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-pagination"></div>';

        const swiperWrapper = gallerySwiperContainer.querySelector('.swiper-wrapper');

        // Ajouter les images au Swiper
        images.forEach(image => {
            const slide = document.createElement('div');
            slide.classList.add('swiper-slide');
            slide.innerHTML = `<img src="${image}" class="w-auto max-h-[80vh] object-contain">`;
            swiperWrapper.appendChild(slide);
        });

        // Initialiser le Swiper pour la galerie plein écran
        gallerySwiper = new Swiper(gallerySwiperContainer, {
            direction: 'horizontal',
            initialSlide: initialIndex,
            loop: false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
         // Afficher la galerie
        galleryModal.classList.remove('hidden');
    }

    // Gestion des clics sur les images (principale et vignettes)
    document.querySelectorAll('[data-action="open-gallery"]').forEach(image => {
        image.addEventListener('click', function() {
            const objectContainer = this.closest('.bg-white'); // Remplacez ceci par le sélecteur correct
            const imageElements = objectContainer.querySelectorAll('img[data-action="open-gallery"]');
            const images = Array.from(imageElements).map(img => img.src);

            const initialIndex = parseInt(this.dataset.imageIndex);

            openGallery(images, initialIndex);
        });
    });

    // Gestion du clic sur le bouton de fermeture de la galerie
    closeGalleryButton.addEventListener('click', function() {
        galleryModal.classList.add('hidden');
        // Destruction de l'instance Swiper
        if (gallerySwiper) {
            gallerySwiper.destroy();
            gallerySwiper = null;
        }
    });
});