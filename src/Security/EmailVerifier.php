<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        try {
            $this->logger->info('Début de l\'envoi d\'email à : ' . $user->getEmail());

            // Générer une URL signée et sécurisée
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                $verifyEmailRouteName,
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()] // Ajoutez ceci
            );

            // Ajouter les variables manquantes dans le contexte de l'email
            $context = $email->getContext();
            $context['signedUrl'] = $signatureComponents->getSignedUrl();
            $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
            $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

            $email->context($context);

            $this->mailer->send($email);
            
            $this->logger->info('Email envoyé avec succès.');
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            throw $e;
        }
    }
}
