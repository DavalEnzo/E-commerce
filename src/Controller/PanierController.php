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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class PanierController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */
    #[Route('/panier', name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository, TranslatorInterface $translator): Response
    {

        $panier = $panierRepository->findPanierNotPaid(0, $this->getUser());

        if ($panier == null) {
            $this->addFlash('danger', $translator->trans('panier.panierDoitCree'));
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'contenusPanier' => $panier->getContenuPaniers()->getValues(),
        ]);
    }

    #[Route('/panier/show/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier = null, TranslatorInterface $translator): Response
    {
        if ($panier == null) {
            $this->addFlash('danger', $translator->trans('panier.panierInexistant'));
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

        return $this->redirectToRoute('app_contenu_panier_new', ['idPanier' => $panierRepository->findPanierNotPaid(0, $this->getUser())->getId(), 'idProduit' => $request->attributes->get('idProduit')], Response::HTTP_SEE_OTHER);
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
    public function delete(Request $request, Panier $panier = null, PanierRepository $panierRepository, TranslatorInterface $translator): Response
    {
        if ($panier == null) {
            $this->addFlash('danger', $translator->trans('panier.panierInexistant'));
            return $this->redirectToRoute('app_produit_index');
        }

        $panierRepository->remove($panier, true);

        $this->addFlash('success', $translator->trans('panier.panierSupprime'));
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/panier/achat/{id}', name: 'app_panier_achat')]
    public function achat(PanierRepository $panierRepository, TranslatorInterface $translator)
    {

        $panierNonPaye = $panierRepository->findPanierNotPaid(0, $this->getUser());

        // Si le panier n'existe pas, on redirige vers la page d'accueil
        if ($panierNonPaye == null) {
            $this->addFlash('danger', $translator->trans('panier.panierInexistant'));
            return $this->redirectToRoute('app_produit_index');
        }

        // Verification si l'utilisateur est le propriétaire du panier
        if($this->getUser() != $panierNonPaye->getUtilisateur()){
            $this->addFlash('danger', $translator->trans('panier.panierNonAppartient'));
            return $this->redirectToRoute('app_produit_index');
        }

        $panierNonPaye->setEtat(1);
        $panierNonPaye->setDate(new \DateTime());
        $panierRepository->save($panierNonPaye, true);

        $this->addFlash('success', $translator->trans('panier.panierPaye'));
        return $this->redirectToRoute('app_produit_index');
    }
}
