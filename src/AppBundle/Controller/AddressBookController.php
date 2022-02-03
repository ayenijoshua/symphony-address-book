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
        die('ji');
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [ ]);
    }
}
