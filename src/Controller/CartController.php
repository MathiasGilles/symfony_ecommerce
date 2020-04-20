<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Repository\CartRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(CartRepository $repo)
    {

        $cart = $repo->findOneBy(['user' => $this->getUser(), 'status' => false]);
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/cart/delete/{id}",name="cart_product_delete")
     */
    public function delete(CartContent $content = null,TranslatorInterface $translator)
    {
        if ($content != null) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($content);
            $manager->flush();

            $this->addFlash("success",$translator->trans('message.product_deleted'));
        }
        else{
            $this->addFlash("danger",$translator->trans('message.product_not_found'));
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart/buy",name="cart_buy")
     */
    public function buy(CartRepository $repo,TranslatorInterface $translator)
    {
        $cart = $repo ->findOneBy(['user' => $this->getUser(), 'status' => false]);
        $manager = $this->getDoctrine()->getManager();

        $cart
            ->setStatus(true)
            ->setBuyAt(new \DateTime());
        $manager->persist($cart);
        $manager->flush();
        
        $this->addFlash("success",$translator->trans('message.cart_buy'));
        return $this->redirectToRoute('product');
    }
}
