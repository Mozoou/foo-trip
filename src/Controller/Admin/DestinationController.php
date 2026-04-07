<?php

namespace App\Controller\Admin;

use App\Entity\Destination;
use App\Form\DestinationType;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/destinations')]
#[IsGranted('ROLE_ADMIN')]
class DestinationController extends AbstractController
{
    #[Route('', name: 'app_admin_destination_index', methods: ['GET'])]
    public function index(DestinationRepository $repository): Response
    {
        return $this->render('admin/destination/index.html.twig', [
            'destinations' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_destination_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($destination);
            $em->flush();

            $this->addFlash('success', 'Destination created successfully.');

            return $this->redirectToRoute('app_admin_destination_index');
        }

        return $this->render('admin/destination/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_destination_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destination $destination, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Destination updated successfully.');

            return $this->redirectToRoute('app_admin_destination_index');
        }

        return $this->render('admin/destination/edit.html.twig', [
            'destination' => $destination,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_destination_delete', methods: ['POST'])]
    public function delete(Request $request, Destination $destination, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $destination->getId(), (string) $request->request->get('_token'))) {
            $em->remove($destination);
            $em->flush();

            $this->addFlash('success', 'Destination deleted successfully.');
        }

        return $this->redirectToRoute('app_admin_destination_index');
    }
}
