<?php

namespace App\Controller;

use App\Entity\LostObject;
use App\Entity\FoundObject;
use App\Entity\Matches;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Entity\ImageFile;
use App\Form\ObjectFormType;
use App\Repository\LostObjectRepository;
use App\Repository\FoundObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\StatusFoundObjectEnum;
use App\Enum\StatusLostObjectEnum;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ObjectController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {}



    #[Route('/mes-objets', name: 'app_my_objects', methods: ['GET'])]
    public function myObjects(
        Request $request,
        LostObjectRepository $lostObjectRepository,
        FoundObjectRepository $foundObjectRepository,
        PaginatorInterface $paginator
    ): Response {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        $currentTab = $request->query->get('tab', 'lost');
    
        // Récupérer uniquement les objets de l'utilisateur
        $objects = $currentTab === 'lost'
            ? $lostObjectRepository->findBy(['user' => $user])
            : $foundObjectRepository->findBy(['user' => $user]);
    
        // Vérifier si l'utilisateur n'a aucun objet (perdu ou trouvé)
        $hasObjects = count($lostObjectRepository->findBy(['user' => $user])) > 0 ||
                      count($foundObjectRepository->findBy(['user' => $user])) > 0;
    
        // Pagination
        $page = $request->query->getInt('page', 1);
        $paginatedObjects = $paginator->paginate($objects, $page, 9);
        $hasMore = count($objects) > 9; // Vérifie s'il y a plus de 9 objets pour afficher le bouton
    
        return $this->render('dashboard/my_objects.html.twig', [
            'objects' => $paginatedObjects,
            'currentTab' => $currentTab,
            'hasObjects' => $hasObjects, // Indique si l'utilisateur a posté des objets
            'categories' => $this->categoryRepository->findAll(),
            'form' => $this->createForm(ObjectFormType::class, null, ['attr' => ['id' => 'object-form']])->createView(),
            'hasMore' => $hasMore // Indique si on doit afficher "Charger Plus"
        ]);
    }
    
    #[Route('/add-object', name: 'app_add_object', methods: ['POST'])]
    public function addObject(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $form = $this->createForm(ObjectFormType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $categoryId = $form->get('category')->getData();
                /** @var Category|null $category */
        $category = $entityManager->getRepository(Category::class)->find($categoryId);
                 // Récupération du type d'objet
                $objectType = $form->get('objectType')->getData();
    
                // Création de l'objet approprié
                $object = $objectType === 'lost' ? new LostObject() : new FoundObject();
    
                // Configuration du statut
                if ($objectType === 'lost') {
                    $object->setStatus(StatusLostObjectEnum::PERDU);
                } else {
                    $object->setStatus(StatusFoundObjectEnum::TROUVE);
                }
    
                // Configuration des données de base
                $object
                    ->setTitle($form->get('title')->getData())
                    ->setDescription($form->get('description')->getData())
                    ->setCategory($category)
                    ->setLocation($form->get('location')->getData())
                    ->setLatitude($form->get('latitude')->getData())
                    ->setLongitude($form->get('longitude')->getData())
                    ->setCreatedAt(new \DateTimeImmutable())
                    ->setUser($this->getUser());
    
                    /** @var UploadedFile[] $imageFiles */
                $imageFiles = $request->files->get('images');
    
                if ($imageFiles) {
                  foreach ($imageFiles as $imageFile) {
                      if ($imageFile && $imageFile->isValid()) {
                            $newFilename = $this->handleImageUpload($imageFile, $slugger);

                            $imageEntity = new ImageFile();
                            $imageEntity->setFilePath('uploads/images/' . $newFilename)
                                        ->setCreatedAt(new \DateTimeImmutable())
                                        ->setObject($object);

                            $entityManager->persist($imageEntity);
                       }
                     }
                }
    
                $entityManager->persist($object);
                $entityManager->flush();
    
                return $this->json([
                   'success' => true,
                   'message' => 'Objet ajouté avec succès !',
                    'redirect' => $this->generateUrl('app_dashboard')
               ]);
            } catch (\Exception $e) {
                return $this->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue : ' . $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    
        return $this->json([
            'success' => false,
            'message' => 'Le formulaire contient des erreurs.'
        ], Response::HTTP_BAD_REQUEST);
    }
    

    #[Route('/mes-objets/load-more', name: 'app_load_more_objects', methods: ['GET'])]
public function loadMoreObjects(
    Request $request, 
    LostObjectRepository $lostObjectRepository, 
    FoundObjectRepository $foundObjectRepository, 
    PaginatorInterface $paginator
): JsonResponse {
    $currentTab = $request->query->get('tab', 'lost'); // Valeur par défaut : 'lost'
    $page = $request->query->getInt('page', 1);
    $user = $this->getUser(); // Récupère l'utilisateur connecté
    $objects = $currentTab === 'lost' ? $lostObjectRepository->findBy(['user' => $user]) : $foundObjectRepository->findBy(['user' => $user]);


    // Paginer les objets (9 objets par page)
    $paginatedObjects = $paginator->paginate($objects, $page, 9);

    // Transformer les objets en JSON pour l'API
    $data = [];
    foreach ($paginatedObjects as $object) {
        $data[] = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'description' => $object->getDescription(),
            'image' => $object->getImageFiles()[0]->getFilePath(),
            'status' => $object->getStatus()->value == 'perdu' ? 'Perdu' : 'Trouvé',
            'location' => $object->getLocation(),
            'createdAt' => $object->getCreatedAt()->format('d M Y à H:i'),
        ];
    }

    return new JsonResponse([
        'objects' => $data,
        'hasMore' => count($paginatedObjects) === 9, // Vérifier s'il y a encore des objets
    ]);
}

    private function getFormErrors($form): array
{
    $errors = [];
    foreach ($form->getErrors(true) as $error) {
        $errors[] = $error->getMessage();
    }
    return $errors;
}
    
    private function handleImageUpload($imageFile, SluggerInterface $slugger): string
    {
         if (!$imageFile) {
            throw new \InvalidArgumentException('Fichier image non valide');
        }

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
        try {
            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );
        } catch (\Exception $e) {
             throw new \Exception('Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
         }
        return $newFilename;
    }
    #[Route('/update-object/{id}', name: 'app_update_object', methods: ['POST'])]
    public function updateObject(
        Request $request, 
        EntityManagerInterface $em, 
        int $id,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        // Rechercher l'objet dans les deux repositories
        $object = $em->getRepository(LostObject::class)->find($id);
        if (!$object) {
            $object = $em->getRepository(FoundObject::class)->find($id);
        }
        
        if (!$object) {
            return new JsonResponse(['success' => false, 'message' => 'Objet non trouvé'], 404);
        }
    
        // Vérifier que l'utilisateur est propriétaire de l'objet
        if ($object->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Non autorisé'], 403);
        }
    
        try {
            // Récupérer les données du formulaire
            $title = $request->request->get('title');
            $description = $request->request->get('description');
            $location = $request->request->get('location');
            $latitude = $request->request->get('latitude');
            $longitude = $request->request->get('longitude');
            $categoryId = $request->request->get('category');
    
            // Validation basique
            if (!$title || !$description || !$location) {
                return new JsonResponse([
                    'success' => false, 
                    'message' => 'Tous les champs requis doivent être remplis'
                ], 400);
            }
    
            // Mettre à jour l'objet
            $object->setTitle($title);
            $object->setDescription($description);
            $object->setLocation($location);
            $object->setLatitude($latitude);
            $object->setLongitude($longitude);
    
            // Mettre à jour la catégorie si elle a changé
            if ($categoryId) {
                $category = $categoryRepository->find($categoryId);
                if ($category) {
                    $object->setCategory($category);
                }
            }
    
            $em->flush();
    
            return new JsonResponse([
                'success' => true,
                'message' => 'Objet mis à jour avec succès',
                'object' => [
                    'id' => $object->getId(),
                    'title' => $object->getTitle(),
                    'description' => $object->getDescription(),
                    'location' => $object->getLocation(),
                    'status' => $object->getStatus()->value
                ]
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }






    #[Route('/search-objects', name: 'app_search_objects', methods: ['GET'])]
    public function searchObjects(
        Request $request, 
        LostObjectRepository $lostObjectRepository, 
        FoundObjectRepository $foundObjectRepository,
        PaginatorInterface $paginator
    ): Response {
        $query = $request->query->get('query', '');
        $category = $request->query->get('category', '');
        $location = $request->query->get('location', '');
        $type = $request->query->get('type', '');
        $page = $request->query->getInt('page', 1);
    
        $allObjects = match($type) {
            'perdu' => $lostObjectRepository->searchObjects($query, $category, $location),
            'trouvé' => $foundObjectRepository->searchObjects($query, $category, $location),
            default => array_merge(
                $lostObjectRepository->searchObjects($query, $category, $location),
                $foundObjectRepository->searchObjects($query, $category, $location)
            )
        };
    
        usort($allObjects, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
    
        $paginatedObjects = $paginator->paginate(
            $allObjects,
            $page,
            9
        );
    
        return $this->render('dashboard/_objects_list.html.twig', [
            'objects' => $paginatedObjects,
            'searchQuery' => [
                'query' => $query,
                'category' => $category,
                'location' => $location,
                'type' => $type
            ]
        ]);
    }
    
    #[Route('/object/{id}/delete', name: 'app_delete_object', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        LostObjectRepository $lostObjectRepository,
        FoundObjectRepository $foundObjectRepository
    ): JsonResponse {
        // Chercher l'objet dans les deux repositories
        $object = $lostObjectRepository->find($id) ?? $foundObjectRepository->find($id);

        if (!$object) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Objet non trouvé'
            ], 404);
        }

        // Vérifier les permissions
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $object->getUser()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous n\'avez pas la permission de supprimer cet objet'
            ], 403);
        }

        try {
            $objectId = $object->getId();
            $entityManager->remove($object);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'objectId' => $objectId,
                'message' => 'Objet supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

#[Route('/object/{id}', name: 'app_object_show', methods: ['GET'])]
public function show(Object $object): Response
{
    return $this->render('object/show.html.twig', [
        'object' => $object
    ]);
}



#[Route('/object/{id}/update-status', name: 'app_object_update_status', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateStatus(
        int $id,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            // Rechercher l'objet
            $object = $entityManager->getRepository(LostObject::class)->find($id);
            if (!$object) {
                $object = $entityManager->getRepository(FoundObject::class)->find($id);
            }

            if (!$object) {
                throw new \Exception('Objet non trouvé');
            }

            // Créer un nouveau match pour enregistrer qui a retrouvé/rendu l'objet
            $match = new Matches();
            $match->setObject($object);
            $match->setUser($this->getUser());
            
            // Mettre à jour le statut via la méthode du match
            $match->markObjectAsReturnedOrRetrieved();
            
            $entityManager->persist($match);
            $entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

}  