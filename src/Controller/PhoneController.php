<?php

namespace App\Controller;

use App\Form\PhoneType;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class PhoneController extends AbstractController
{

    /**
     * @var PhoneRepository
     */
    private $phoneRepository;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(PhoneRepository $phoneRepository, EntityManagerInterface $manager)
    {
        $this->phoneRepository = $phoneRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/phones/{id}/delete", name="delete_phone", requirements={"id"="\d+"})
     */
    public function deletePhone(Request $request, int $id){
        $phone = $this->phoneRepository->find($id);
        $contact_id = $phone->getContact()->getId();
        if($phone == null){
            throw new HttpException(404);
        }

        $this->manager->remove($phone);
        $this->manager->flush();

        return $this->redirectToRoute("detail_contact", ['id' => $contact_id]);
    }

    /**
     * @Route("/phones/{id}/update", name="update_phone", requirements={"id"="\d+"})
     */
    public function updatePhone(Request $request, int $id){
        $phone = $this->phoneRepository->find($id);
        if($phone == null){
            throw new HttpException(404);
        }

        $contact = $phone->getContact();

        $form = $this->createForm(PhoneType::class, $phone);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($phone);
            $this->manager->flush();
            return $this->redirectToRoute('detail_contact', ['id' => $contact->getId()]);
        }

        return $this->render("detail.html.twig", ['form' => $form->createView(), 'contact' => $contact]);
    }
}