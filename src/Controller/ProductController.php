<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

     #[Route('/dashboard/mes-produits', name: 'app_mes_produits')]
    public function mesProduits(ProductRepository $productRepository): Response
    {
        // 🔹 Récupérer l'UID Firebase depuis session (après connexion Firebase)
        $firebaseUid = $_SESSION['firebaseUid'] ?? null;

        if (!$firebaseUid) {
            return $this->redirectToRoute('app_login');
        }

        $produits = $productRepository->findBy(['firebaseUid' => $firebaseUid]);

        return $this->render('product/mes_produits.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/product/new', name: 'app_product_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $firebaseUid = $request->request->get('firebaseUid');

            if (empty($firebaseUid)) {
                $this->addFlash('error', 'Vous devez être connecté avec Google pour ajouter un produit.');
                return $this->redirectToRoute('app_login'); // ou autre route de login
            }

            $product->setFirebaseUid($firebaseUid);
            
            // Récupérer les fichiers uploadés
            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                // Déplacer l’image dans le dossier public/uploads/products
                $image->move(
                    $this->getParameter('uploads'),
                    $newFilename
                );

                 // Crée une entité ProductImage reliée au produit
                $productImage = new ProductImage();
                $productImage->setFilename($newFilename);
                $productImage->setProduct($product);

                $em->persist($productImage);
            }

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès ✅');
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
