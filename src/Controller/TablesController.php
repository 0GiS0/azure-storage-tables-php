<?php

namespace App\Controller;

use App\Service\TableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TablesController extends AbstractController
{
    /**
     * @Route("/", name="tables")
     */
    public function index(TableService $table)
    {
        return $this->render('tables/index.html.twig', [
            'controller_name' => 'TablesController',
            'entities' => $table->getEntities(),
        ]);
    }

    /**
     * @Route("/add/entity")
     */
    public function create(Request $request, TableService $table)
    {
        $table->createEntity($request->get('firstName'), $request->get('lastName'));

        return $this->redirectToRoute('tables');
    }
}
