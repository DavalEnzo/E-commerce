<?php

namespace App\Controller;

use App\Entity\ContenuPanier;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class ContenuPanierController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     */

    // Ajoute un produit au panier
    #[Route('/contenuPanier/new/{idPanier}/{idProduit}', name: 'app_contenu_panier_new')]
    public function new(Request $request, ContenuPanierRepository $contenuPanierRepository, PanierRepository $panierRepository, ProduitRepository $produitRepository, TranslatorInterface $translator): Response
    {

        $contenuPanier = new ContenuPanier();

   // Verification si le panier existe pour cet utilisateur
        if($request->attributes->get('idPanier') != $panierRepository->findPanierNotPaid(0, $this->getUser())->getId()){
            $this->addFlash('danger', $translator->trans('panier.panierInexistant'));
            return $this->redirectToRoute('app_produit_index');
        }

    // Verification si le produit existe
        if($produitRepository->find($request->attributes->get('idProduit')) == null){
            $this->addFlash('danger', $translator->trans('produit.produitInexistant'));
            return $this->redirectToRoute('app_produit_index');
        }

   // Verification si le produit est déjà dans le panier
        $contenuPanierSelectionne = $contenuPanierRepository->produitAlreadyInContenuPanier($request->attributes->get('idPanier'), $request->attributes->get('idProduit'));

    // Ajout de la quantité au produit si déjà présent dans le panier
        if($contenuPanierSelectionne != null){
            $contenuPanierSelectionne->setQuantite($contenuPanierSelectionne->getQuantite() + 1);
            $contenuPanierRepository->save($contenuPanierSelectionne, true);
            $this->addFlash('success', $translator->trans('produit.quantiteModifiee'));
            return $this->redirectToRoute('app_produit_show', ['id'=> $request->attributes->get('idProduit')], Response::HTTP_SEE_OTHER);
        }

        // Ajout du produit au panier
            $contenuPanier->setPanier($panierRepository->find($request->attributes->get('idPanier')));
            $contenuPanier->setProduit($produitRepository->find($request->attributes->get('idProduit')));
            $contenuPanierRepository->save($contenuPanier, true);

            $this->addFlash('success', $translator->trans('panier.produitAjoute'));
            return $this->redirectToRoute('app_produit_index');
    }

    // Supprimer le produit du panier, si le produit était le dernier du panier, on supprime le panier
    #[Route('/contenuPanier/delete/{id}', name: 'app_contenu_panier_delete')]
    public function delete(Request $request, ContenuPanierRepository $contenuPanierRepository, PanierRepository $panierRepository, ContenuPanier $contenuPanier = null, TranslatorInterface $translator): Response
    {
        if($contenuPanier == null){
            $this->addFlash('danger', $translator->trans('panier.produitInexistant'));
            return $this->redirectToRoute('app_produit_index');
        }

        if($contenuPanier->getPanier()->getUtilisateur() != $this->getUser()){
            $this->addFlash('danger', $translator->trans('panier.panierNonAppartient'));
            return $this->redirectToRoute('app_produit_index');
        }

        $contenuPanierRepository->remove($contenuPanier, true);

        if(count($contenuPanier->getPanier()->getContenuPaniers()->getValues()) == 0){
            return $this->redirectToRoute('app_panier_delete', ['id' => $contenuPanier->getPanier()->getId()]);
        }

        $this->addFlash('success', $translator->trans('panier.articleSupprime'));
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
