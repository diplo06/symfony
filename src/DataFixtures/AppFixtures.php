<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Entity\Image;
use App\Entity\Role;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
	private $encoder; 
	public function __construct(UserPasswordEncoderInterface $encoder){ 
		$this->encoder=$encoder; 
	}

	
    public function load(ObjectManager $manager)
    {	
    	$adminRole= new Role(); 
    	$adminRole ->setTitle('ROLE_ADMIN'); 
    	$manager->persist($adminRole);

    	$adminUser = new User(); 
    	$adminUser ->setFirstName('lolo') 
    	           ->setLastName('diplo') 
    	           ->setEmail('lolo@sql.fr') 
    	           ->setHash($this->encoder->encodePassword($adminUser, "password")) 
    	           ->setPicture('https://via.placeholder.com/64') 
    	           ->setIntroduction('Introduction de lolo') 
    	           ->setDescription('Description de lolo') 
    	           ->setSlug('lolo-diplo') 
    	           ->addUserRole($adminRole); 
    	$manager->persist($adminUser);

        $slugify = new Slugify();
        for ($l=1; $l <5 ; $l++) {
        	$user= new User();
        	$slug=$slugify->slugify("prenom$l-nom$l"); 
        	$user->setFirstName("prenom$l") 
	        	-> setLastName("nom$l") 
	        	->setEmail("test$l@test.fr") 
	        	->setPicture("https://via.placeholder.com/64") 
	        	->setIntroduction("introduction$l") 
	        	->setDescription("description$l") 
	        	->setSlug($slug);
	        $encoded = $this->encoder->encodePassword($user, "pass"); 
	        $user->setHash($encoded);	
	        $manager->persist($user);

        	for ($i=1; $i <=mt_rand(1,5) ; $i++) {
				$ad= new Ad();
				$ad->setTitle("Titre de l'annonce n°$i")
				->setSlug($slugify->slugify($ad->getTitle()))
				->setCoverImage("https://via.placeholder.com/350")
				->setIntroduction("C'est une introduction")
				->setContent("<p>Je suis le contenu</p>")
				->setPrice(mt_rand(40,200))
				->setRooms(mt_rand(1,5))
				->setAuthor($user);

				for ($j=1; $j <=mt_rand(1,5) ; $j++) {
					$image = new Image();
					$image ->setUrl("https://via.placeholder.com/350")
					-> setCaption("Legende de l'image $j")
					-> setAd($ad);
					$manager->persist($image);
				}

				// gestion des réservations 
				for ($j=0; $j <mt_rand(0,10) ; $j++) { 
					$booking= new Booking(); 
					$booking->setCreatedAt(new \DateTime()) 
					        ->setStartDate(new \DateTime("+ 5 days")) 
					        ->setEndDate(new \DateTime("+ 15 days")) 
					        ->setAmount($ad->getPrice()*10) 
					        ->setComment("Commentaire a la réservation $j") 
					        ->setAd($ad)
					        ->setBooker($user); 
					$manager->persist($booking); 
				}

				$manager->persist($ad);
			}
        }
        $manager->flush();
    }
}
