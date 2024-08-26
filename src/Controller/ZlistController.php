<?php

namespace App\Controller;

use App\Entity\Zombie;
use App\Form\ZombieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ZlistController extends AbstractController
{
    #[Route('/zlist', name: 'zlistpage')]
    public function zlist(EntityManagerInterface $entityManager): Response
    {
        $zombies = $entityManager->getRepository(Zombie::class)->findAll();

        return $this->render('list/zlist.html.twig', [
            'zombies' => $zombies,
        ]);
    }

    #[Route('/zform', name: 'zformpage')]
    public function addZombie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $zombie = new Zombie();

        $form = $this->createForm(ZombieType::class, $zombie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($zombie->getName())) {
                $this->addFlash('error', 'The name is required.');
                return $this->render('list/zform.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            if (empty($zombie->getDescription())) {
                $zombie->setDescription('No description provided');
            }

            if (empty($zombie->getStatus())) {
                $zombie->setStatus('No status provided');
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $zombie->setImage($newFilename);
            } else {
                $zombie->setImage('zmissing.jpg');
            }

            if (empty($zombie->getSeason())) {
                $zombie->setSeason('Unknown season');
            }

            if (empty($zombie->getEpisode())) {
                $zombie->setEpisode('Unknown episode');
            }

            if (empty($zombie->getLocation())) {
                $zombie->setLocation('Unknown location');
            }

            $entityManager->persist($zombie);
            $entityManager->flush();

            $this->addFlash('success', 'Zombie added successfully!');

            return $this->redirectToRoute('zlistpage');
        }

        return $this->render('list/zform.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}