<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\PanierRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('{_locale}')]
class UserController extends AbstractController
{
    #[Route('/compte', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/compte/{id}', name: 'app_user_show', methods: ['GET', 'POST'])]
    public function show(Request $request, User $user, UserRepository $userRepository, PanierRepository $panierRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_show', ['id'=> $user->getId()], Response::HTTP_SEE_OTHER);
        }

        $paniers = $panierRepository->findBy(['utilisateur' => $user->getId(), 'etat' => 1]);

        return $this->render('user/show.html.twig', [
            'paniers' => $paniers,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/compte/modifier/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            $this->addFlash('success', $translator->trans('utilisateur.compteModifie'));
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/compte/delete/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    // Accès à la page admin
    #[Route('/backoffice', name: 'app_user_backoffice', methods: ['GET'])]
    public function backoffice(UserRepository $userRepository, PanierRepository $panierRepository): Response
    {
        $paniersNonPayes = $panierRepository->findBy(['etat' => 0]);

        $users = $userRepository->findAllUsersByCroissantOrder();

        return $this->render('user/backoffice.html.twig', [
            'users' => $users,
            'paniers' => $paniersNonPayes,
        ]);
    }
}
