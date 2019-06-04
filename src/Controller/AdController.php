<?php

namespace App\Controller;


use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo)
    {
        //$repo=$this->getDoctrine()->getRepository(Ad::class);
        $ads = $repo->findAll();
        //dump($ads);
        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
    * @Route("/ads/new", name="ads_create")
    *@IsGranted("ROLE_USER")
    */
    public function create(Request $request,ObjectManager $manager){
        $ad = new Ad();
        /* on simule le fait que l'annonce a déjà deux images 
        
        $image = new Image(); 
        $image ->setUrl('url image') 
            ->setCaption('titre 1'); 
        $ad->addImage($image);

        $image2 = new Image(); 
        $image2 ->setUrl('url image 2')
            ->setCaption('titre 2'); 
        $ad->addImage($image2);
        */
        $form = $this -> createForm(AnnonceType::class, $ad);
        $form ->handleRequest($request);
        //dump($ad);
        // si le form est soumis et valide (par rapport aux règles en place) 
        if ($form->isSubmitted() && $form->isValid()){
            $ad->setAuthor($this->getUser());
            // génération du slug automatiquement
            if (!$ad->getSlug()){
                $slugify = new Slugify(); 
                $slug=$slugify->slugify($ad->getTitle()); 
                $ad->setSlug($slug);         
            }   
            
            foreach ($ad->getImages() as $image) { 
                $image->setAd($ad); 
                $manager->persist($image); 
            }
            $manager->persist($ad); // previent doctrine que l'on veut sauver 
            $manager->flush(); // envoi la requête à la base de donnée
            //créé un message flash success=>couleur bootstrap voir header.html.twig
            $this->addFlash(
                'success',
                "L'annonce <b>".$ad->getTitle()."</b> a bien été enregistrée !"
            );
            // on retourne sur la page de l'article 
            return $this->redirectToRoute('ads_show',['slug' => $ad->getSlug()]);
        }
        return $this->render('ad/new.html.twig',[
            'form' => $form -> createView()
        ]);
    }

    /**
    * @Route("/ads/{slug}/edit", name="ads_edit")
    *@Security("is_granted('ROLE_USER') and user === ad.getAuthor() " , message ="Cette annonce ne vous appartient pas") 
    */
    public function edit(Request $request,ObjectManager $manager,Ad $ad){    
        $form = $this -> createForm(AnnonceType::class, $ad);
        //gros merd
        $slugify = new Slugify(); 
        $data = $request->request->get('annonce'); 
        $data['slug']=$slugify->slugify($request->request->get('annonce')['title']); 
        $request->request->set('annonce',$data); 

        $form ->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){
            
            if (!$ad->getSlug()){
                $slugify = new Slugify(); 
                $slug=$slugify->slugify($ad->getTitle()); 
                $ad->setSlug($slug);         
            }  
            
            foreach ($ad->getImages() as $image) { 
                $image->setAd($ad); 
                $manager->persist($image); 
            }
            $manager->persist($ad); 
            $manager->flush(); 
            
            $this->addFlash(
                'success',
                "L'annonce <b>".$ad->getTitle()."</b> a bien été modifie !"
            );
            
            return $this->redirectToRoute('ads_show',['slug' => $ad->getSlug()]);
        }
        return $this->render('ad/edit.html.twig',[
            'form' => $form -> createView(),
            'ad'=>$ad
        ]);
    }

    /**
     * @Route("/ads/{slug}", name="ads_show")
     */
    public function show(Ad $ad)
    {
        return $this->render('ad/show.html.twig', [
            'ad' => $ad,
        ]);
    }

    /** 
    * permet de supprimer une annonce 
    * @Route("/ads/{slug}/delete", name="ads_delete") *@Security("is_granted('ROLE_USER') and user === ad.getAuthor() " , message ="Vous n'avez pas le droit d'accéder à cette ressource") 
    */ 
    public function delete(Ad $ad, ObjectManager $manager){ 
        $manager->remove($ad); 
        $manager->flush(); // envoyer l'info à la bdd 
        $this->addFlash( 
            'success', 
            'L\'annonce '.$ad->getTitle().' a bien été supprimée !' 
        ); 
        return $this->redirectToRoute("account_index"); 
    }
}
