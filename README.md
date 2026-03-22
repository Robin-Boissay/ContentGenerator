# Content Generator IA - Workflow Éditorial Autonome (TP2)

Une application web de génération de contenu textuel itérative basée sur l'intelligence artificielle (Groq/OpenAI), développée dans le cadre du **TP2 — Projet de développement avec IA**.

---

## 📄 Cadrage du Projet

### Le problème résolu
La génération de contenu (blogs, posts...) est souvent fastidieuse. Passer du temps à "prompter" manuellement l'IA en ajustant sans cesse ses instructions est contre-productif. 
Le problème résolu ici est la simplification de cette création de contenu en automatisant le processus : cet outil génère un texte, **s'auto-évalue**, se critique, et s'améliore de lui-même en partant d'un seul brief initial, jusqu'à arriver à un produit convenable.

### Choix techniques justifiés
- **Backend : PHP 8.2 avec Symfony 8** (permet une forte flexibilité avec le composant HTTP Client et une architecture solide MVC/Entités).
- **Frontend : Twig, CSS natif et AssetMapper** (garde le front-end le plus simple et rapide possible, sans usine à gaz Javascript/Node.js à compiler).
- **Base de données : SQLite** via Doctrine ORM (stockage local immédiat, parfait pour un MVP et ne nécessite pas de docker-compose complexe).
- **IA : Llama-3.3 via l'API Groq** (Temps de réponse de moins d'une seconde, crucial pour les itérations, tout en conservant une API compatible avec le standard OpenAI pour switcher facilement vers GPT-4o si besoin).

### Ce que ce projet ne fait PAS (Scope négatif)
- Pas de publication automatique sur les réseaux sociaux (ex: intégration LinkedIn ou X).
- Pas de génération de médias ou d'images associées au texte.
- Pas de système d'authentification ou de gestion collaborative multi-utilisateurs (le MVP est pensé pour un auteur unique gérant son propre contenu).

### Difficultés anticipées & Solutions
- **Dérive stylistique et validation naïve :** L'IA a tendance à systhématiquement s'auto-approuver ou à devenir très lisse.  
  *Solution appliquée :* Séparation forte des responsabilités. Le service `AiGeneratorService` utilise des "system prompts" imposant un rôle "d'éditeur exigeant et impitoyable".
- **Gestion stricte du format retour (JSON) :** Risque que l'IA perde le format dans l'itération. 
  *Solution :* Utilisation de l'API avec contrainte `response_format => json_object` pour forcer la sortie JSON des critiques et logs.

---

## 📦 Installation de zéro

### 1. Cloner le projet
```bash
git clone <votre-url-de-depot>
cd ContentGenerator
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Configurer l'environnement
Copiez le fichier d'exemple pour créer votre configuration locale :
```bash
cp .env.example .env
```
Ouvrez le fichier `.env` et ajoutez votre clé API :
```env
LLM_API_KEY="votre_cle_groq_gratuite"
```

### 4. Initialiser la Base de Données (SQLite)
Générez simplement les tables :
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```
*(Cela va créer le fichier `var/data.db` qui contiendra vos projets et brouillons).*

---

## 🚀 Lancement et Utilisation

Lancez le serveur de développement local fourni par Symfony :
```bash
symfony serve -d
```
Rendez-vous dans votre navigateur à l'adresse **[https://127.0.0.1:8000/](https://127.0.0.1:8000/)**.

### Workflow type :
1. Cliquez sur **Nouveau Projet**. Rentrez votre Brief et la Cible.
2. Cliquez sur **Générer le 1er brouillon**. L'IA travaille puis affiche le texte.
3. Le texte ne vous satisfait pas ? Cliquez sur **Demander une critique (IA Éditeur)**. L'IA se juge elle-même en pointant les défauts.
4. Lisez ses retours. Si le texte est refusé, cliquez sur **Générer la version suivante**.
5. Répétez l'opération jusqu'à l'approbation parfaite.
