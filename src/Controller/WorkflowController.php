<?php

namespace App\Controller;

use App\Entity\ContentDraft;
use App\Entity\ContentProject;
use App\Entity\ReviewFeedback;
use App\Service\AiGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/workflow')]
class WorkflowController extends AbstractController
{
    #[Route('/{id}', name: 'app_workflow_show', methods: ['GET'])]
    public function show(ContentProject $project): Response
    {
        return $this->render('workflow/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/generate', name: 'app_workflow_generate', methods: ['POST'])]
    public function generate(ContentProject $project, AiGeneratorService $aiGenerator): Response
    {
        if ($project->getDrafts()->isEmpty()) {
            $aiGenerator->generateInitialDraft($project);
        }

        return $this->redirectToRoute('app_workflow_show', ['id' => $project->getId()]);
    }

    #[Route('/draft/{id}/review', name: 'app_workflow_review', methods: ['POST'])]
    public function review(ContentDraft $draft, AiGeneratorService $aiGenerator): Response
    {
        $aiGenerator->reviewDraft($draft);

        return $this->redirectToRoute('app_workflow_show', ['id' => $draft->getProject()->getId()]);
    }

    #[Route('/draft/{id}/improve', name: 'app_workflow_improve', methods: ['POST'])]
    public function improve(ContentDraft $draft, AiGeneratorService $aiGenerator): Response
    {
        $feedbacks = $draft->getFeedbacks();
        $lastFeedback = $feedbacks->last();

        if ($lastFeedback instanceof ReviewFeedback && !$lastFeedback->isApproved()) {
            $aiGenerator->improveDraft($draft, $lastFeedback);
        }

        return $this->redirectToRoute('app_workflow_show', ['id' => $draft->getProject()->getId()]);
    }
}
