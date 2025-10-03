<?php
// src/Controller/ProfilController.php
namespace App\Controller;

use App\Entity\Membre; // entité pour stocker les membres
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        // Ici on récupère l'utilisateur connecté via Firebase
        $session = $request->getSession();
        $firebaseUser = $session->get('firebase_user');

        if (!$firebaseUser) {
            return $this->redirectToRoute('app_login');
        }

        $membre = $em->getRepository(Membre::class)->findOneBy(['idfirebase' => $firebaseUser['uid']]);

        // Si pas trouvé, on l’ajoute
        if (!$membre) {
            $membre = new Membre();
            $membre->setEmail($firebaseUser['email']);
            $membre->setUsername($firebaseUser['displayName']);
            $membre->setIdfirebase($firebaseUser['uid']);
            $em->persist($membre);
            $em->flush();
        }

        return $this->render('profil/index.html.twig', [
            'membre' => $membre
        ]);
    }
}
