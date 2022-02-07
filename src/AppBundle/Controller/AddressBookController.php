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
        $form = $this->buildForm($addressBook);

        $form->handleRequest($request);
        if($form->isSubmitted()){
            $data = $this->getData($form);

            if($data['picture']){
                $originalFilename = pathinfo($data['picture']->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$data['picture']->guessExtension();
                $data['picture']->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                $data['picture'] = $newFilename;
            }

            $this->setData($addressBook,$data);
            

            $em = $this->getDoctrine();
            $exists = $em->getRepository('AppBundle:AddressBook')->findOneBy(['email'=>$data['email']]);
            if($exists){
                $this->addFlash('error','Email alredy exists');
                return $this->redirectToRoute('create');
            }
            if($form->isValid()){
                try {
                    $em->getRepository('AppBundle:AddressBook')->updateAddress($addressBook);
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
        $form = $this->buildForm($addressBook,true);

        $form->handleRequest($request);
        if($form->isSubmitted()){
            $data = $this->getData($form);

            $this->setData($addressBook,$data);

            if($data['picture']){
                try {
                    $originalFilename = pathinfo($data['picture']->getClientOriginalName(), PATHINFO_FILENAME);
                    $newFilename = $originalFilename.'-'.uniqid().'.'.$data['picture']->guessExtension();
                    if(! unlink(new \Symfony\Component\HttpFoundation\File\File($this->getParameter('pictures_directory').'/'.$addressBook->getPicture()))){
                        throw new \Exception('Unable to delete old picture');
                    }
                
                    $data['picture']->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );

                    $data['picture'] = $newFilename;
                } catch (\Exception $e) {
                    $loger->info($e->getMessage());
                    $this->addFlash('error','An error occured');
                }  
            }

            $em = $this->getDoctrine();
            $exists = $em->getRepository('AppBundle:AddressBook')->mailExists($data['email'],$id);

            if($exists){
                $this->addFlash('error','Email alredy exists');
                return $this->redirectToRoute('edit',['id'=>$id]);
            }
            if($form->isValid()){
                try {
                    $em->getRepository('AppBundle:AddressBook')->updateAddress($addressBook);
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

    private function buildForm(&$model,$update=true)
    {   
        if($update){
            if($model->getPicture()){
                $model->setPicture(new \Symfony\Component\HttpFoundation\File\File($this->getParameter('pictures_directory').'/'.$model->getPicture()));
            }
        }
       return $this->createFormBuilder($model)
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
    }

    private  function getData($form)
    {
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

       return compact([
            'firstname',
            'lastname',
            'email',
            'phone',
            'city',
            'zip' ,
            'country',
            'street_and_number',
            'picture',
            'birthday',
        ]);    
    }

    private function setData(&$addressBook,$data){
            $addressBook->setFirstname($data['firstname']);
            $addressBook->setLastname($data['lastname']);
            $addressBook->setEmail($data['email']);
            $addressBook->setPhone($data['phone']);
            $addressBook->setCity($data['city']);
            $addressBook->setZip($data['zip']);
            $addressBook->setCountry($data['country']);
            $addressBook->setStreetAndNumber($data['street_and_number']);
            $addressBook->setPicture($data['picture']);
            $addressBook->setBirthday($data['birthday']);//'AppBundle:AddressBook'
    }
}
