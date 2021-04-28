<?php 

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Phone;
use App\Form\ContactType;
use App\Form\PhoneType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController{

    /**
     * @var ContactRepository
     */
    private $contactRepository;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(ContactRepository $contactRepository, EntityManagerInterface $manager)
    {
        $this->contactRepository = $contactRepository;
        $this->manager = $manager;
    }

    /**
     * @Route("/add", name="create_contact")
     */
    public function addContact(Request $request){
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($contact);
            $this->manager->flush();
            return $this->redirectToRoute('list_contact');
        }
        return $this->render('create.html.twig', ['form'=>$form->createView()]);
    }

    /**
     * @Route("/", name="list_contact")
     */
    public function listContact(Request $request){
        $contactList = $this->contactRepository->findAll();
        return $this->render('list.html.twig', ['contactList'=>$contactList]);
    }

    /**
     * @Route("/{id}", name="detail_contact", requirements={"id"="\d+"})
     */
    public function detailContact(Request $request, int $id){
        $contact = $this->contactRepository->find($id);
        if($contact == null){
            throw new HttpException(404);
        }

        $phone = new Phone();
        $phone->setContact($contact);

        $form = $this->createForm(PhoneType::class, $phone);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($phone);
            $this->manager->flush();
            return $this->redirectToRoute('detail_contact', ['id' => $id]);
        }

        return $this->render("detail.html.twig", ['form' => $form->createView(), 'contact' => $contact]);
    }

    /**
     * @Route("/{id}/delete", name="delete_contact", requirements={"id"="\d+"})
     */
    public function deleteContact(Request $request, int $id){
        $contact = $this->contactRepository->find($id);
        if($contact == null){
            throw new HttpException(404);
        }
        $this->manager->remove($contact);
        $this->manager->flush();
        return $this->redirectToRoute('list_contact');

    }

    /**
     * @Route("/{id}/update", name="update_contact", requirements={"id"="\d+"})
     */
    public function updateContact(Request $request, int $id){
        $contact = $this->contactRepository->find($id);
        if($contact == null){
            throw new HttpException(404);
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($contact);
            $this->manager->flush();
            return $this->redirectToRoute('detail_contact', ['id' => $id]);
        }

        return $this->render('update.html.twig', ['form' => $form->createView()]);
    }
}