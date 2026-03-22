<?php

namespace App\Service;

use App\Entity\ContentDraft;
use App\Entity\ContentProject;
use App\Entity\ReviewFeedback;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class AiGeneratorService
{
    private Client $client;

    public function __construct(
        private EntityManagerInterface $em,
        #[Autowire(env: 'LLM_API_KEY')] string $apiKey,
        #[Autowire(env: 'LLM_BASE_URI')] string $baseUri
    ) {
        // Désactivation de la vérification SSL pour le dev local sous Windows
        $httpClient = new Psr18Client(HttpClient::create(['verify_peer' => false, 'verify_host' => false]));

        $this->client = \OpenAI::factory()
            ->withApiKey($apiKey)
            ->withBaseUri($baseUri)
            ->withHttpClient($httpClient)
            ->make();
    }

    public function generateInitialDraft(ContentProject $project): ContentDraft
    {
        $prompt = sprintf(
            "Tu es un rédacteur web expert. Rédige un brouillon de contenu pour le brief suivant.\nBrief :\n%s\n\nPublic cible :\n%s",
            $project->getInitialBrief(),
            $project->getTargetAudience() ?? 'Tous publics'
        );

        $response = $this->client->chat()->create([
            'model' => 'llama-3.3-70b-versatile', // or any default
            'messages' => [
                ['role' => 'system', 'content' => 'Tu rédiges des brouillons qualitatifs simples.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        $content = $response->choices[0]->message->content;

        $draft = new ContentDraft();
        $draft->setProject($project);
        $draft->setContent($content);
        $draft->setVersionNumber(1);

        $this->em->persist($draft);
        $this->em->flush();

        return $draft;
    }

    public function reviewDraft(ContentDraft $draft): ReviewFeedback
    {
        $project = $draft->getProject();
        $prompt = sprintf(
            "Agis comme un éditeur extrêmement critique. Analyse le brouillon suivant à la lumière de son brief initial.\n\nBrief: %s\n\nBrouillon: %s\n\nRéponds UNIQUEMENT au format JSON strict avec les clés : 'critique' (string), 'suggestions' (string), 'isApproved' (boolean). Sois impitoyable et exigeant, n'approuve que si c'est parfait.",
            $project->getInitialBrief(),
            $draft->getContent()
        );

        $response = $this->client->chat()->create([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un éditeur critique. Réponds TOUJOURS en JSON valide, sans markdown, sans blabla.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
        ]);

        $jsonString = $response->choices[0]->message->content;
        $data = json_decode($jsonString, true);

        // Fallback robustesse au cas où le LLM a mal renvoyé le JSON
        if (!$data || !isset($data['critique'], $data['suggestions'], $data['isApproved'])) {
            $data = [
                'critique' => 'Erreur de lecture de la critique: ' . $jsonString,
                'suggestions' => 'Réessayez',
                'isApproved' => false,
            ];
        }

        $feedback = new ReviewFeedback();
        $feedback->setDraft($draft);
        $feedback->setCritique($data['critique']);
        $feedback->setSuggestions($data['suggestions']);
        $feedback->setApproved((bool)$data['isApproved']);

        $this->em->persist($feedback);
        $this->em->flush();

        return $feedback;
    }

    public function improveDraft(ContentDraft $draft, ReviewFeedback $feedback): ContentDraft
    {
        $project = $draft->getProject();
        $prompt = sprintf(
            "Tu es un rédacteur chargé d'améliorer un brouillon en prenant compte des critiques. \nBrief original: %s\n\nBrouillon actuel: %s\n\nCritiques de l'éditeur: %s\n\nSuggestions: %s\n\nRédige la nouvelle version DU TEXTE sans commentaires additionnels.",
            $project->getInitialBrief(),
            $draft->getContent(),
            $feedback->getCritique(),
            $feedback->getSuggestions()
        );

        $response = $this->client->chat()->create([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu appliques les corrections. Retourne UNIQUEMENT le texte corrigé, aucun texte avant ou après.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        $newContent = $response->choices[0]->message->content;

        $newDraft = new ContentDraft();
        $newDraft->setProject($project);
        $newDraft->setContent($newContent);
        $newDraft->setVersionNumber($draft->getVersionNumber() + 1);

        $this->em->persist($newDraft);
        $this->em->flush();

        return $newDraft;
    }
}
