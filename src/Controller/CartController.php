<?php

namespace App\Controller;

use App\Entity\CartContent;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
    public function delete(CartContent $content = null)
    {
        if ($content != null) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($content);
            $manager->flush();

            $this->addFlash("success","Produit retiré du panier");
        }
        else{
            $this->addFlash("danger","Produit introuvable");
        }

        return $this->redirectToRoute('cart');
    }
}
