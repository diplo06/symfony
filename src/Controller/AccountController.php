<?php

namespace App\Controller;


use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccountController extends AbstractController
{
    /** 
     * @Route("/register", name="account_register")  
     */ 
     public function register(Request $request, ObjectManager $manager,UserPasswordEncoderInterface $encoder){ 
        $user=new User(); 
        $form = $this->createForm(RegistrationType::class, $user ); 
        // on relie les champs du formulaire aux champs de l'utilisateur
        $form ->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) { 
            
            $password=$user->getHash();     
            $encoded = $encoder->encodePassword($user, $password);
            $user->setHash($encoded); 
        
            $slugify = new Slugify(); 
            $slug=$slugify->slugify("{$user->getFirstName()}-{$user->getLastName()}"); 
            $user->setSlug($slug); 

            $manager->persist($user); // on fait persister dans le temps 
            $manager->flush(); // envoi la requête => ecriture dans la base 

            $this->addFlash( 'success', 'Vous avez bien été enregistrée !' ); 
            return $this->redirectToRoute('account_login'); 
            } 
        return $this->render('account/registration.html.twig', [ 
            'formUser' => $form -> createView(), ]); // envoi du form à twig
    }        

    /** 
     * @Route("/login", name="account_login")  
     */ 
     public function login(AuthenticationUtils $utils) { 
	     // fonction qui permet de récupérer la dernière erreur 
	     $error = $utils-> getLastAuthenticationError(); 
	     return $this->render('account/login.html.twig',[  
		     'hasError' => $error ]); // on renvoit un boolean
	 }

    /** 
     * @Route("/logout", name="account_logout") 
     */ 
    public function logout(){}

     /** 
      * affiche et traite le formulaire de modification de profil 
      * @Route("/account/profile", name="account_profile") 
      */ 
    public function profile(Request $request,ObjectManager $manager) {
        // recupérer l'utilisateur qui est connecté 
        $user= $this->getUser(); 
        $form = $this->createForm(AccountType::class, $user ); 
        // on relie les champs du formulaire aux champs de l'utilisateur
        $form ->handleRequest($request); //traitement de la request  
        if ($form->isSubmitted() && $form->isValid()){  
            $manager->persist($user); // previent doctrine que l'on veut sauver
            $manager->flush(); // envoi la requête
            $this->addFlash( 'success', 'Vous avez bien été enregistrée !',"coucou" );
            // on retourne sur la page de l'article    
            return $this->redirectToRoute('account_profile');     
         } 

        return $this->render('account/profile.html.twig' ,[ 
            'form' => $form -> createView()]); // envoi du form à twig
    }  

    /** 
     * permet de modifier le mot de passe 
     * @Route("/account/password-update", name="account_password") 
     */ 
     public function updatePassword(Request $request, ObjectManager $manager,UserPasswordEncoderInterface $encoder) { 
        // recupérer l'utilisateur qui est connecté 
        $user=$this->getUser();  
        dump($user); 
        $passwordUpdate= new PasswordUpdate(); 
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate ); 
        // on relie les champs du formulaire aux champs de l'utilisateur 
        $form ->handleRequest($request); 
        if ($form->isSubmitted() && $form->isValid()) { 
            // verifie que le oldpassword est le même que celui de l'user hacher 
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())){ 
                $this->addFlash( 'danger', 'L ancien mot de passe est incorrect !' ); 
            } 
            else{ 
                $newPassword = $passwordUpdate->getNewPassword(); 
                $encoded = $encoder->encodePassword($user, $newPassword); //le hachage
                $user->setHash($encoded); 
                $manager->persist($user); // on fait persister dans le temps 
                $manager->flush(); // envoi la requête => ecriture dans la base 
                $this->addFlash( 'success', 'Le mot de passe a été modifié !' ); 
                return $this->redirectToRoute('homepage'); 
            } 
        } 
        return $this->render('account/password.html.twig' ,[ 
            'form' => $form -> createView()]); 
    }

    /** 
     * @Route("/account/", name="account_index")
     */ 
    public function myAccount(){ 
      return $this->render('user/index.html.twig' ,[ 
        'user' => $this->getUser(), // utilisateur connecté 
      ]); 
    }
}
