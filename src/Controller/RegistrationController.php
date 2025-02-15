<?php

namespace App\Controller;

use App\Security\FormLoginAuthenticator;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuthenticator,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        LoggerInterface $logger
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $user->setFullName($form->get('fullName')->getData());
                $user->setToken(bin2hex(random_bytes(32)));
                $user->setRoles(['ROLE_USER']);
                $user->setAuthProvider('email');

                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $newFilename = $this->handleImageUpload($imageFile, $slugger);
                    $user->setImagePathProfile($newFilename);
                }
        
                // Enregistrement
                $entityManager->persist($user);
                $entityManager->flush();
    
                $logger->info('Tentative d\'envoi d\'email de vérification');
                
                // ✉️ Envoi de l'email de vérification
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address('ahyahya1010@gmail.com', 'lost&found - Support'))
                        ->to($user->getEmail())
                        ->subject('Veuillez confirmer votre email')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
    
                $this->addFlash('success', 'Votre compte a été créé. Veuillez vérifier votre email pour l\'activer.');
        
                // Au lieu d'authentifier, rediriger vers login
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $logger->error('Erreur lors de l\'inscription : ' . $e->getMessage());
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription.');
                return $this->redirectToRoute('app_register');
            }
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request, 
        EntityManagerInterface $entityManager,
        VerifyEmailHelperInterface $verifyEmailHelper,
        LoggerInterface $logger
    ): Response {
        $id = $request->query->get('id');
        $logger->debug('ID reçu: ' . $id);
        
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_register');
        }
    
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
            
            $user->setIsVerified(true);
            $entityManager->flush();
            
            $this->addFlash('success', 'Email vérifié avec succès.');
            return $this->redirectToRoute('app_login');
            
        } catch (VerifyEmailExceptionInterface $e) {
            $logger->error('Erreur de vérification: ' . $e->getMessage());
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }
    }

    private function handleImageUpload($imageFile, SluggerInterface $slugger): string
{
    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

    $imageFile->move(
        $this->getParameter('profile_pictures_directory'),
        $newFilename
    );

    return $newFilename;
}
}