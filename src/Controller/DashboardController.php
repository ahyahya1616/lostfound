<?php
// DashboardController.php
namespace App\Controller;

use App\Form\ObjectFormType;
use App\Entity\User;
use App\Entity\Messages;
use App\Repository\CategoryRepository;
use App\Repository\LostObjectRepository;
use App\Repository\FoundObjectRepository;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DashboardController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,private MessagesRepository $messagesRepository
    ) {}

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        Request $request,
        LostObjectRepository $lostObjectRepository, 
        FoundObjectRepository $foundObjectRepository,
        PaginatorInterface $paginator
    ): Response {
        
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $unreadCount = 0;
    
    if ($user) {
        $unreadCount = $this->messagesRepository->countUnreadMessages($user);
    }


$qbLost = $lostObjectRepository->createQueryBuilder('l')
    ->where('l.user != :user')
    ->setParameter('user', $user)
    ->orderBy('l.createdAt', 'DESC');

$qbFound = $foundObjectRepository->createQueryBuilder('f')
    ->where('f.user != :user')
    ->setParameter('user', $user)
    ->orderBy('f.createdAt', 'DESC');

$allObjects = array_merge(
    $qbLost->getQuery()->getResult(),
    $qbFound->getQuery()->getResult()
);

        
        usort($allObjects, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $this->render('dashboard/index.html.twig', [
            'user' => $this->getUser(),
            'objects' => $paginator->paginate(
                $allObjects,
                $request->query->getInt('page', 1),
                9
            ),
            'show_object_modal' => true,
            'form' => $this->createForm(ObjectFormType::class, null, ['attr' => ['id' => 'dashboard-object-form']])->createView(),
            'categories' => $this->categoryRepository->findAll(),
            ]);
    }


    #[Route('/api/profile/update-password', name: 'app_profile_update_password', methods: ['POST'])]
    public function updatePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw new AccessDeniedException('Utilisateur non connecté');
        }

        // Vérifier si l'utilisateur utilise l'authentification par email
        if ($user->getAuthProvider()=='google' || $user->getAuthProvider()=='facebook') {
            return new JsonResponse([
                'error' => 'La modification du mot de passe n\'est pas autorisée pour les connexions via réseaux sociaux'
            ], 403);
        }

        $data = json_decode($request->getContent(), true);
        $newPassword = $data['newPassword'] ?? null;

        // Valider le nouveau mot de passe
        $constraints = new Assert\Collection([
            'newPassword' => [
                new Assert\NotBlank([
                    'message' => 'Le mot de passe est requis'
                ]),
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères'
                ])
            ]
        ]);

        $violations = $validator->validate(['newPassword' => $newPassword], $constraints);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            // Hasher et enregistrer le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $entityManager->flush();
            
            return new JsonResponse([
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la mise à jour du mot de passe'
            ], 500);
        }
    }
}
