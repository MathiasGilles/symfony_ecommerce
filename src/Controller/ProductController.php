<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContent;
use App\Entity\Product;
use App\Form\CartContentType;
use App\Form\ProductType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(ProductRepository $repo)
    {
        $products = $repo->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/new/product",name="product_new")
     * @Route("/edit/product/{id}",name="product_edit")
     */
    public function new(Product $product = null, Request $request)
    {
        if (!$product) {
            $product = new Product();
        }
        $manager = $this->getDoctrine()->getManager();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $fichier = $form->get('photo')->getData();
            //Si un fichier a été uploadé
            if ($fichier) {
                // On renomme le fichier
                $nomFicher = uniqid() . '.' . $fichier->guessExtension();
                try {
                    // on essaie de deplacer le fichier
                    $fichier->move(
                        $this->getParameter('upload_dir'),
                        $nomFicher
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', "Impossible d'uploader le fichier");
                    return $this->redirectToRoute('product');
                }
                $product->setPhoto($nomFicher);
            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash("success", "Produit sauvegardé");
            return $this->redirectToRoute('product');
        }

        return $this->render('product/product_new.html.twig', [
            'formProduct' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/product/{id}",name="product_delete")
     */
    public function delete($id = null, ProductRepository $repo)
    {

        if ($id != null) {
            $product = $repo->find($id);
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($product);
            $manager->flush();
        }
        $this->addFlash("success", "Product supprimé");
        return $this->redirectToRoute('product');
    }

    /**
     * @Route("/product/detail/{id}",name="product_detail")
     * @Route("/product/add/{id}",name="product_add_to_cart")
     */
    public function detail($id, ProductRepository $repo,Request $request,CartRepository $repoCart)
    {

        $product = $repo->find($id);
        $cart = $repoCart->findOneBy(['user' => $this->getUser(), 'status' => false]);
        $manager = $this->getDoctrine()->getManager();

        if ($cart === null) {
            $cart = new Cart();
            $cart->setUser($this->getUser());
            $manager->persist($cart);
            
        }

        $content = new CartContent();
        
        $form = $this->createForm(CartContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content->setAddedAt(new \DateTime())
                ->setProduct($product)
                ->setCart($cart);

            $manager->persist($content);
            $manager->flush();

            $this->addFlash("success", "Article ajouté au panier");
            return $this->redirectToRoute("product");
        }

        return $this->render('product/product_detail.html.twig', [
            'product' => $product,
            'formCartContent' => $form->createView()
        ]);
    }
}
