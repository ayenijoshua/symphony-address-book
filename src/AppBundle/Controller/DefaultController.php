<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $data = $this->getDoctrine()->getRepository('AppBundle:AddressBook')->findAll();
        return $this->render('address-book/index.html.twig', ['addresses'=>$data ]);
    }
}
