# Spécifications du Projet : Générateur de Contenu avec Workflow Éditorial

**Dépôt GitHub** : [https://github.com/Robin-Boissay/ContentGenerator](https://github.com/Robin-Boissay/ContentGenerator)

---

## Document de cadrage (Projet 9)

### Le problème résolu
La génération de contenu (blogs, posts, ...) est très souvent un processus fastidieux avec beaucoup d'itérations et d'étapes pour arriver à un produit final convenable. Cela demande beaucoup de prompt et de génération (allers-retours manuels) pour arriver au produit voulu.

Le problème résolu ici est la **simplification de la création de posts** en créant un outil qui s'auto-évalue et avance de lui-même en partant d'un seul prompt (le brief initial), jusqu'à arriver à un produit final jugé convenable par une IA critique.

### Choix techniques justifiés
- **Backend** : J'utilise le langage **PHP** avec le framework **Symfony 8**. Cela permet de structurer proprement l'application MVC.
- **Frontend** : Je garde les technologies front simples : **JavaScript, CSS natif et Twig** en combinaison avec **AssetMapper**. Cela fonctionne parfaitement avec Symfony sans nécessiter la complexité d'une compilation externe (pas de Node.js).
- **Base de données** : **SQLite**, qui permet un stockage direct dans un fichier sans dépendre d'une infrastructure serveur de base de données complexe.
- **Intelligence Artificielle** : Le SDK OpenAI PHP paramétré pour taper sur les API (Llama-3 via Groq par exemple) pour la génération rapide et structurée (JSON).

### Scope négatif (Ce que je ne ferai PAS)
- Pas de publication automatique sur les réseaux sociaux (pas d'intégration d'API directe vers LinkedIn, X, etc.).
- Pas de génération de médias ou d'images pour accompagner les textes (focus 100% sur la qualité du texte).
- Pas de gestion collaborative multi-utilisateurs complexe (le MVP est pensé pour un auteur unique gérant son propre contenu de A à Z).

### Difficultés anticipées et stratégie de résolution
1. **L'ingénierie de prompt pour la review** : 
   - *Difficulté* : Contraindre l'IA à être véritablement critique et à ne pas valider systématiquement le brouillon par "politesse".
   - *Stratégie* : Utilisation d'un prompt système d'éditeur intransigeant, forçant un retour en format JSON strict comportant `critique`, `suggestions` et le booléen `isApproved`.
2. **La dérive stylistique** : 
   - *Difficulté* : S'assurer que les itérations successives n'effacent pas le style original défini dans les exemples few-shot.
   - *Stratégie* : Réinjection systématique du contexte de base (brief initial) lors de la phase de correction (`improveDraft`) pour maintenir le cap.
3. **Gestion des versions** : 
   - *Difficulté* : Modéliser la base de données pour lier proprement le brief initial, les versions successives et les feedbacks sans créer des données orphelines.
   - *Stratégie* : Utilisation de Doctrine ORM avec un modèle hiérarchique clair (`ContentProject` ➔ `ContentDraft` ➔ `ReviewFeedback`) orchestré par des relations OneToMany persistées et une incrémentation de version automatique.

---

## Vue d'ensemble de l'Architecture de Données (MCD)

- **`ContentProject`** : Centralise la volonté utilisateur (`title`, `initialBrief`, `targetAudience`). 
- **`ContentDraft`** : Historise chaque version de texte générée (`versionNumber`, `content`). Apparaît comme un enfant du Projet.
- **`ReviewFeedback`** : La sanction de l'IA Éditeur sur un Draft précis (`critique`, `suggestions`, `isApproved`). Déclenche éventuellement l'itération suivante.
