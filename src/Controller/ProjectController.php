<?php

namespace App\Controller;

use App\Entity\ContentProject;
use App\Repository\ContentProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index', methods: ['GET'])]
    public function index(ContentProjectRepository $repository): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $project = new ContentProject();
        
        $form = $this->createFormBuilder($project)
            ->add('title', TextType::class, ['label' => 'Titre / Sujet principal'])
            ->add('initialBrief', TextareaType::class, ['label' => 'Brief détaillé'])
            ->add('targetAudience', TextType::class, ['label' => 'Public cible'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            return $this->redirectToRoute('app_workflow_show', ['id' => $project->getId()]);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }
}
