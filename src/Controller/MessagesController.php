<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\User;
use App\Entity\AbstractObject;
use App\Form\ObjectFormType;
use App\Repository\MessagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class MessagesController extends AbstractController
{
    private MessagesRepository $messagesRepository;

public function __construct(MessagesRepository $messagesRepository)
{
    $this->messagesRepository = $messagesRepository;
}

    #[Route('/messages', name: 'app_messages')]
public function index(MessagesRepository $messagesRepository): Response
{
    $user = $this->getUser();
    $conversations = $messagesRepository->findAllConversations($user);
    
    return $this->render('messages/index.html.twig', [
        'conversations' => $conversations,
        'form' => $this->createForm(ObjectFormType::class, null, ['attr' => ['id' => 'messages-object-form']])->createView()
    ]);
}
    #[Route('/messages/{receiverId<\d+>}/{objectId?}', name: 'app_messages_conversation')]
public function conversation(
    MessagesRepository $messagesRepository,
    EntityManagerInterface $entityManager,
    int $receiverId,
    ?int $objectId = null
): Response {
    /** @var User|null $user */
    $user = $this->getUser();
    $receiver = $entityManager->getRepository(User::class)->find($receiverId);
    $object = $objectId ? $entityManager->getRepository(AbstractObject::class)->find($objectId) : null;
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté.');
    }

    // Vérifier que l'utilisateur connecté est bien impliqué dans la conversation
    if ($object->getUser() !== $user && $object->getUser() !== $receiver) {
        throw $this->createAccessDeniedException('Vous n\'avez pas accès à cet objet.');
    }

    // Récupérer TOUTES les conversations
    $allConversations = $messagesRepository->findAllConversations($user);
    // Dans MessagesController.php
    $unreadMessages = $this->messagesRepository->findUnreadConversationMessages(
        $user,
        $receiver,
        $object
    );
    
    foreach ($unreadMessages as $message) {
        $message->setRead(true);
    }
    $entityManager->flush();
    
    // Récupérer les messages spécifiques à cette conversation
    $messages = $messagesRepository->findBy([
        'sender' => [$user, $receiver],
        'receiver' => [$user, $receiver],
        'object' => $object
    ], ['sentAt' => 'ASC']);

    return $this->render('messages/conversation.html.twig', [
        'receiver' => $receiver,
        'messages' => $messages,
        'form' => $this->createForm(ObjectFormType::class, null, ['attr' => ['id' => 'messages-object-form']])->createView(),
        'conversations' => $allConversations, // Passer toutes les conversations
        'object' => $object
    ]);
}


    #[Route('/messages/send/{receiverId}', name: 'app_send_message', methods: ['POST'])]
    public function sendMessage(
        int $receiverId,
        Request $request,
        EntityManagerInterface $entityManager,
        MessagesRepository $messagesRepository
    ): JsonResponse {
        try {
            $user = $this->getUser();
            if (!$user) {
                throw new AccessDeniedHttpException('Utilisateur non connecté');
            }
    
            $content = $request->request->get('content');
            $objectId = $request->request->get('objectId');
    
            if (empty($content)) {
                throw new \InvalidArgumentException('Le message ne peut pas être vide');
            }
    
            $receiver = $entityManager->getRepository(User::class)->find($receiverId);
            if (!$receiver) {
                throw new NotFoundHttpException('Destinataire introuvable');
            }
    
            $object = $objectId ? $entityManager->getRepository(AbstractObject::class)->find($objectId) : null;
    
            // Vérifier si une conversation existe déjà
            $existingMessages = $messagesRepository->findConversationByUsersAndObject($user, $receiver, $object);
            
            if ($existingMessages) {
                // Si la conversation existe, on l'utilise simplement (on ajoute un message)
                $conversation = reset($existingMessages); 
            } else {
                // Sinon, on crée une nouvelle conversation
                $conversation = new Messages();
                $conversation->setSender($user)
                             ->setReceiver($receiver)
                             ->setObject($object);
            }
    
            // Ajouter le nouveau message
            $message = new Messages();
            $message->setSender($user)
                    ->setReceiver($receiver)
                    ->setObject($object)
                    ->setContent($content)
                    ->setSentAt(new \DateTimeImmutable());
    
            $entityManager->persist($message);
            $entityManager->flush();
    
            return $this->json([
                'success' => true,
                'message' => [
                    'content' => $message->getContent(),
                    'sentAt' => $message->getSentAt()->format('d/m/Y H:i'),
                    'isRead' => $message->isRead()
                ]
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
 
}