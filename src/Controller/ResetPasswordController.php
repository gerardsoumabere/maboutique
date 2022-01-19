<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/mot-de-pass-oublie', name: 'reset_password')]
    public function index(Request $request): Response
    {
        if ($this->getUser()){
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')){
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));


            if ($user){

                // 1 Enregistrer en base la demande de reset_password avec user , token , createdAt.
                $reset_password = new ResetPassword();
                $reset_password ->setUser($user);
                $reset_password ->setToken(uniqid());
                $reset_password ->setCreatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // 2 Envoyer une email à l'utilisateur avec un lien lui permettant de mettre à jour son mot de passe.
                $url = $this->generateUrl('update_password',[
                    'token'=>$reset_password->getToken()
                    ]);
                $content = 'Bonjour '.$user->getFirstname().'</br>'.'Vous avez demandé à réinitialiser votre mot de passe sur le site de Maboutique'.'</br>'.'</br>';
                $content.= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='$url'>mettre à jour votre mot de passe</a>.";
                $mail = new Mail();
                $mail->send($user->getEmail(),$user->getFirstname().' '.$user->getLastname(),'Réinitialiser votre mot de passe sur Maboutique',$content);
                $this->addFlash('notice','Vous allez recevoir dans quelques instants ,un mail contenant un lien pour reinitialiser votre mot de passe.');
            } else {
                $this->addFlash('notice','Cette adresse email est inconnue');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('modifier-mon-mot-de-passe/{token}', name: 'update_password')]
    public function update(Request $request, $token,UserPasswordHasherInterface $hasher)
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if(!$reset_password){
            return $this->redirectToRoute('reset_password');
        }

        // Vérifier si le createdAt = now - 1h
        $now = new \DateTimeImmutable();
        if($now > $reset_password->getCreatedAt()->modify('+ 1 hour')){
            $this->addFlash('notice','Votre demande de mot de passe a expiré.Merci de la renouveller.');
            return $this->redirectToRoute('reset_password');
        }

        // Rendre une vue avec mot de passe et confirmer votre mot de passe.
        $form = $this->createForm(ResetPasswordType::class);
        $form -> handleRequest($request);

        if ($form -> isSubmitted() && $form -> isValid()){
            $new_pwd = $form->get('new_password')->getData();

            // Encodage des motd de passe
            $password = $hasher->hashPassword($reset_password->getUser(),$new_pwd);
            $reset_password->getUser()->setPassword($password);

            // Flush en base de données
            $this->entityManager->flush();


            // Redirection de l'utilisateur vers la page de connexion.
            $this->addFlash('notice','Votre mot de passe à bien été mis à jour');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('reset_password/update.html.twig',[
            'form' => $form->createView()
        ]);


    }


}
