<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\AddressBook;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddressBookController extends Controller
{
    /**
     * @Route("/address", name="address")
     */
    public function indexAction(Request $request)
    {
        $data = $this->getDoctrine()->getRepository('AppBundle:AddressBook')->findAll();
        return $this->render('address-book/index.html.twig', ['addresses'=>$data ]);
    }

     /**
     * @Route("/address/create", name="create")
     */
    public function createAction(Request $request,LoggerInterface $loger)
    {
        $addressBook = new AddressBook();
        $form = $this->createFormBuilder($addressBook)
        ->add('firstname',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('lastname',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('email',EmailType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('birthday',DateType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('phone',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('zip',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('city',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('country',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('street_and_number',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('picture',FileType::class,['attr'=> ['class'=>'form-control'],'required'=>false])
        ->add('Submit',SubmitType::class,['attr'=> ['class'=>'btn btn-primary mt-3']])
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted()){
            $firstname = $form['firstname']->getData();
            $lastname = $form['lastname']->getData();
            $email = $form['email']->getData();
            $phone = $form['phone']->getData();
            $city = $form['city']->getData();
            $zip = $form['zip']->getData();
            $country = $form['country']->getData();
            $street_and_number = $form['street_and_number']->getData();
            $picture = $form['picture']->getData();
            $birthday = $form['birthday']->getData();

            if($picture){
                $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$picture->guessExtension();
                $picture->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                $picture = $newFilename;
            }

            $addressBook->setFirstname($firstname);
            $addressBook->setLastname($lastname);
            $addressBook->setEmail($email);
            $addressBook->setPhone($phone);
            $addressBook->setCity($city);
            $addressBook->setZip($zip);
            $addressBook->setCountry($country);
            $addressBook->setStreetAndNumber($street_and_number);
            $addressBook->setPicture($picture);
            $addressBook->setBirthday($birthday);//'AppBundle:AddressBook'
            

            $em = $this->getDoctrine();
            $exists = $em->getRepository('AppBundle:AddressBook')->findOneBy(['email'=>$email]);
            if($exists){
                $this->addFlash('error','Email alredy exists');
                return $this->redirectToRoute('create');
            }
            if($form->isValid()){
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($addressBook);
                    $em->flush();
                } catch (\Exception $e) {
                    $loger->info($e->getMessage());
                    $this->addFlash('error','An error occured');
                    return $this->redirectToRoute('create');
                }
                
                $this->addFlash('notice','address successfully added');
            }else{
                $this->addFlash('error',$form->getErrors());
                return $this->redirectToRoute('create');
            }

            return $this->redirectToRoute('address');
        }

        return $this->render('address-book/create.html.twig', ['form'=>$form->createView()]);
    }

     /**
     * @Route("/address/edit/{id}", name="edit")
     */
    public function editAction($id, Request $request, LoggerInterface $loger)
    {
        $addressBook = $this->getDoctrine()->getRepository(AddressBook::class)->find($id);
        if(!$addressBook){
            $this->addFlash('error','Invalid Id passed');
            return $this->redirectToRoute('edit',['id'=>$id]);
        }
        $form = $this->createFormBuilder($addressBook)
        ->add('firstname',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('lastname',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('email',EmailType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('birthday',DateType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('phone',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('zip',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('city',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('country',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('street_and_number',TextType::class,['attr'=> ['class'=>'form-control'],'required'=>true])
        ->add('picture',FileType::class,['attr'=> ['class'=>'form-control'],'required'=>false])
        ->add('Submit',SubmitType::class,['attr'=> ['class'=>'btn btn-primary mt-3']])
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted()){
            $firstname = $form['firstname']->getData();
            $lastname = $form['lastname']->getData();
            $email = $form['email']->getData();
            $phone = $form['phone']->getData();
            $city = $form['city']->getData();
            $zip = $form['zip']->getData();
            $country = $form['country']->getData();
            $street_and_number = $form['street_and_number']->getData();
            $picture = $form['picture']->getData();
            $birthday = $form['birthday']->getData();

            $addressBook->setFirstname($firstname);
            $addressBook->setLastname($lastname);
            $addressBook->setEmail($email);
            $addressBook->setPhone($phone);
            $addressBook->setCity($city);
            $addressBook->setZip($zip);
            $addressBook->setCountry($country);
            $addressBook->setStreetAndNumber($street_and_number);
            $addressBook->setPicture($picture);
            $addressBook->setBirthday($birthday);//'AppBundle:AddressBook'

           
            $em = $this->getDoctrine();
            $exists = $em->getRepository('AppBundle:AddressBook')->mailExists($email,$id);

            if($exists){
                $this->addFlash('error','Email alredy exists');
                return $this->redirectToRoute('edit',['id'=>$id]);
            }
            if($form->isValid()){
                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($addressBook);
                    $em->flush();
                } catch (\Exception $e) {
                    $loger->info($e->getMessage());
                    $this->addFlash('error','An error occured');
                    return $this->redirectToRoute('edit',['id'=>$id]);
                }
                
                $this->addFlash('notice','address successfully updated');
            }else{
                $this->addFlash('error',$form->getErrors());
                return $this->redirectToRoute('edit',['id'=>$id]);
            }

           return  $this->redirectToRoute('address');
        }
        return $this->render('address-book/edit.html.twig', ['address'=>$addressBook,'form'=>$form->createView() ]);
    }

     /**
     * @Route("/address/delete/{id}", name="delete")
     */
    public function deleteAction($id)
    {
        $this->getDoctrine()->getRepository(AddressBook::class)->deleteAddress($id);
        $this->addFlash('notice','Address deleted successfully');
        return $this->redirectToRoute('address');
    }
}
