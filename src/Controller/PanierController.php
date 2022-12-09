<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('{_locale}')]
class PanierController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route('/panier', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {

        $panier = $panierRepository->findPanierNotPaid(0, $this->getUser());

        if ($panier == null) {
            $this->addFlash('danger', 'Un panier doit être créé avant de pouvoir ajouter un produit');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'contenusPanier' => $panier->getContenuPaniers()->getValues(),
        ]);
    }

    #[Route('/panier/new/{idProduit}', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PanierRepository $panierRepository): Response
    {
        $panier = new Panier();

        $panierNonPaye = $panierRepository->findPanierNotPaid(0, $this->getUser());

        if ($this->getUser() && $panierNonPaye == null) {
            $panier->setUtilisateur($this->getUser());
            $panierRepository->save($panier, true);
        }

        return $this->redirectToRoute('app_contenu_panier_new', ['idPanier' => $panierNonPaye->getId(), 'idProduit' => $request->attributes->get('idProduit')], Response::HTTP_SEE_OTHER);
    }

    #[Route('/panier/modifier/{id}', name: 'app_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, PanierRepository $panierRepository): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $panierRepository->save($panier, true);

            return $this->redirectToRoute('app_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/panier/{id}', name: 'app_panier_delete')]
    public function delete(Request $request, Panier $panier = null, PanierRepository $panierRepository): Response
    {
        if ($panier == null) {
            $this->addFlash('danger', 'Le panier est introuvable');
            return $this->redirectToRoute('app_produit_index');
        }

        $this->addFlash('success', 'Le panier a bien été supprimé');
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/panier/achat/{id}', name: 'app_panier_achat')]
    public function achat(Request $request, Panier $panier = null)
    {

    }
}
