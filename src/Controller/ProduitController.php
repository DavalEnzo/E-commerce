<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/produit/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('photo')->getData();

            // this condition is needed because the 'image' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produit_index');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $produit->setImage($newFilename);
            }

            $produitRepository->save($produit, true);

            $this->addFlash('success', $translator->trans('produit.produitAjoute'));
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produit/show/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit, ContenuPanierRepository $contenuPanierRepository, PanierRepository $panierRepository): Response
    {
        $panier = $panierRepository->findPanierNotPaid(0, $this->getUser());

        if($this->getUser() != null && $panier != null){
            $contenuPanier = $contenuPanierRepository->produitAlreadyInContenuPanier($panier->getId(), $produit->getId());
        } else {
            $contenuPanier = null;
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'contenuPanier' => $contenuPanier,
        ]);
    }

    #[Route('/produit/modifier/{id}', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('photo')->getData();

            // this condition is needed because the 'image' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', $e->getMessage());
                    return $this->redirectToRoute('app_produit_index');
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $produit->setPhoto($newFilename);
            }

            $produitRepository->save($produit, true);

            $this->addFlash('success', $translator->trans('produit.produitModifie'));
            return $this->redirectToRoute('app_produit_show', ['id'=> $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('produit/delete/{id}', name: 'app_produit_delete')]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {
        $produitRepository->remove($produit, true);

        $this->addFlash('success', $translator->trans('produit.produitSupprime'));
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
