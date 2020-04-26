<?php

namespace App\Controller;

use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SuperAdminController extends AbstractController
{
    /**
     * @Route("/super_admin", name="super_admin")
     */
    public function index(CartRepository $repo, UserRepository $repoUser)
    {
        $users = $repoUser->findBy([],['id' => 'DESC']);

        $carts = $repo->findBy(['status' => false]);
        return $this->render('super_admin/index.html.twig', [
            'carts' => $carts,
            'users' => $users
        ]);
    }
}
