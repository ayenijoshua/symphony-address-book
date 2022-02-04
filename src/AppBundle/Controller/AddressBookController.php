<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AddressBookController extends Controller
{
    /**
     * @Route("/address", name="address")
     */
    public function indexAction(Request $request)
    {
        //('ji');
        // replace this example code with whatever you need
        return $this->render('address-book/index.html.twig', [ ]);
    }

     /**
     * @Route("/address/create", name="create")
     */
    public function createAction(Request $request)
    {
        //('ji');
        // replace this example code with whatever you need
        return $this->render('address-book/create.html.twig', [ ]);
    }

     /**
     * @Route("/address/edit/{id}", name="edit")
     */
    public function editAction($id, Request $request)
    {
        //('ji');
        // replace this example code with whatever you need
        return $this->render('address-book/edit.html.twig', [ ]);
    }

     /**
     * @Route("/address/show/{id}", name="show")
     */
    public function showAction($id)
    {
        //('ji');
        // replace this example code with whatever you need
        return $this->render('address-book/show.html.twig', [ ]);
    }

     /**
     * @Route("/address/delete/{id}", name="delete")
     */
    public function deleteAction($id)
    {
        //('ji');
        // replace this example code with whatever you need
        //return $this->render('address-book/index.html.twig', [ ]);
    }
}
