<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
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
     * @Route("/product/new",name="product_new")
     * @Route("/product/edit/{id}",name="product_edit")
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
     * @Route("/product/delete/{id}",name="product_delete")
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
     * @Route("/question/detail/{id}",name="product_detail")
     */
    public function detail($id, ProductRepository $repo)
    {

        $product = $repo->find($id);

        return $this->render('product/product_detail.html.twig', [
            'product' => $product,
        ]);
    }
}
