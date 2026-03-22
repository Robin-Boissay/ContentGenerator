# Journal de Développement — Générateur de Contenu IA

Ce journal retrace ma démarche d'implémentation itérative avec l'assistance d'un modèle d'IA pour la construction du MVP.

---

## Session 1 — Objectif : Fondations de l'application et modèles de données

**Prompt de départ :** 
> *"Création d'un outil de génération de contenu via IA avec un workflow de révision autonome, basé sur Symfony (WebApp), Twig, AssetMapper et SQLite pour une approche simple, sans compilation Node.js. Modèles : ContentProject, ContentDraft, ReviewFeedback. Fais-moi le plan d'implémentation."*

**Problème rencontré :** 
L'IA a généré un plan très complet puis a produit les entités (`ContentDraft`, `ReviewFeedback`). Cependant, elle a fait des choix de nommage "standards" sur certaines propriétés (ex: `version` au lieu de `versionNumber`, `comment` au lieu de `critique`), ce qui modifiait le plan de données initial que j'avais fixé.
Si je l'avais laissée faire, cela aurait posé des incohérences massives par la suite lors de la liaison avec le workflow métier de ma base SQLite.

**Solution :** 
J'ai explicitement redonné mes contraintes en coupant l'IA avant toute autre tâche : *"Avant de reprendre le plan d'implémentation, assure-toi d'utiliser les champs exacts : `versionNumber`, `critique`, `suggestions`, `isApproved`."* J'ai exigé l'édition stricte de ces propriétés avant de lancer la migration Doctrine `make:migration`.

**Apprentissage :** 
Il est capital de forcer l'IA à respecter un "data mapping" très pointu dès les premières phrases. Si le modèle s'écarte du schéma de données, il construit une dette technique qui demandera des heures de refactoring. Toujours vérifier les Entités/schémas générés avant d'écrire dans la base.

---

## Session 2 — Objectif : Intégration de l'IA Éditeur, Bug SSL et Routage

**Prompt intermédiaire :** 
> *"Passe à l'étape du service IA. C'est ici le coeur du projet. Je veux un `AiGeneratorService` qui appelle Llama-3 via l'API Groq. Le service gère trois méthodes : générer le draft, le critiquer (cette méthode doit agir comme un éditeur impitoyable et retourner du format JSON strict) et l'améliorer en boucle."*

**Problème rencontré (Certificat SSL) :** 
Lors des premiers tests de génération, le serveur a crashé. L'API Groq renvoyait une erreur `fopen(): SSL operation failed [...] certificate verify failed`. L'environnement local sous Windows n'avait pas le CA Bundle adéquat pour autoriser une requête sortante sécurisée.

**Solution au problème SSL :** 
Au lieu de modifier mon fichier racine `php.ini` (pas toujours possible ni maintenable), j'ai guidé l'IA pour injecter un `Psr18Client` via le composant HttpClient natif de Symfony dans le SDK d'OpenAI/Groq. J'ai demandé au client HTTP de by-passer les certificats locaux (`['verify_peer' => false]`). C'est optimal pour le dev local sans casser la prod.

**Problème rencontré (Compatibilité Symfony 8) :** 
Sur mon navigateur local `127.0.0.1:8000`, les routes du `ProjectController` ne chargeaient pas (erreur 404, j'avais la page par défaut de Symfony).

**Solution au problème Symfony 8 :** 
J'ai analysé avec le debugger (et un bout de discussion avec l'assistant) et je me suis aperçu que l'IA avait utilisé un import déprécié et supprimé dans la V8 : `use Symfony\Component\Routing\Annotation\Route;`. Symfony 8 les ignorait de fait. Constat : il suffisait de remplacer `Annotation` par `Attribute` dans les namespaces.

**Apprentissage :** 
Même les LLM modernes ont un gros biais en faveur des patterns anciens car ils ont "dévoré" beaucoup plus de tutoriels Symfony 4/5/6 que de ressources Symfony 7/8. Il faut toujours challenger le code fourni vis-à-vis des dépréciations d'architectures du framework utilisé.


---

## 📌 Annexes : Prompts Principaux Utilisés
*Ceci est une compilation des consignes de guidage principales utilisées tout au long du travail pour recadrer l'IA.*

**1. Prompt de définition et lancement du projet :**
> *"Projet — Générateur de contenu avec workflow éditorial. Le but est de créer une IA qui s'auto-évalue et corrige ses drafts jusqu'à produire le texte voulu. J'utilise PHP avec Symfony, Twig, AssetMapper et SQLite (pas d'auth). [...] Les tables : ContentProject(id, title, initialBrief, targetAudience), ContentDraft(id, project_id, versionNumber, content), ReviewFeedback(id, draft_id, critique, suggestions, isApproved). Fais le plan et prépare l'initialisation."*

**2. Prompt "Garde-fou" base de données :**
> *"Avant de reprendre le plan d'implémentation, écrit le contenue des fichiers entité ContentDraft et ReviewFeedback. Attention à bien correspondre aux propriétés que je t'ai précisées dans mon dernier prompt, sans faire d'interprétation."*

**3. Prompt de construction du Workflow UI :**
> *"Parfait, fait la suite du plan d'implémentation maintenant. Conçois les contrôleurs ProjectController et WorkflowController, ainsi qu'une interface Twig basique et en pure CSS. Sur l'interface du workflow, on doit y voir la cible, le brief, et chaque draft, avec le bouton pour que l'IA 'critique' le texte, affichant clairement si le draft a été approuvé ou non."*

**4. Prompts de debugging opérationnel :**
> *"J'ai lancé le projet avec `symfony serve`, pourquoi à l'adresse 127.0.0.1:8000 j'ai l'erreur Page not found ? La route est pourtant bien déclarée."*
> *"C'est bon, ça affiche la page par défaut de Symfony 8 "Welcome to Symfony 8", la homepage URL n'est pas reconnue."*
> *"Je me suis connecté à l'IA et au bout de 5 secondes de chargement j'ai eu cette erreur : `fopen(): SSL operation failed with code 1. OpenSSL Error messages: error:0A000086:SSL routines::certificate verify failed` Comment corriger ça via Symfony HTTPClient pour le SDK ?"*
