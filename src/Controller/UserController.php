<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user")
     */
    public function index(CartRepository $repo) // display user orders info
    {
        $carts = $repo->findBy(['user' => $this->getUser(), 'status' => true]);
        return $this->render('user/index.html.twig', [
            'carts' => $carts
        ]);
    }

    /**
     * @Route("/user/edit/{id}",name="user_edit")
     */
    public function edit(Request $request, User $user, TranslatorInterface $translator) // edit the user
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", $translator->trans('message.profil_update'));
            return $this->redirectToRoute('user', ['id' => '$this->getUser()']);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'formUser' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/cart/{id}",name="user_cart")
     */

    public function showCartBought(CartRepository $repo, $id) // recover the selected order
    {
        $cart = $repo->find($id);
        return $this->render('user/showCart.html.twig', [
            'cart' => $cart
        ]);
    }
}
