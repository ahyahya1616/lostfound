<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\MessagesRepository;
use Symfony\Component\Security\Core\Security;

class HeaderController extends AbstractController
{
    public function messageBadge(MessagesRepository $messagesRepository)
    {
        $user = $this->getUser();
        $count = 0;
        
        if ($user) {
            $count = $messagesRepository->countUnreadMessages($user);
        }

        return $this->render('_messages_badge.html.twig', [
            'unread_count' => $count
        ]);
    }
}