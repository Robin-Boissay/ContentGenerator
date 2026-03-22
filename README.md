# Content Generator IA - Workflow Éditorial Autonome

Une application web de génération de contenu (articles, briefs, posts) basée sur **Symfony 8**, utilisant l'intelligence artificielle (ex: **Llama 3** via l'API **Groq**, ou **OpenAI**) pour rédiger, auto-critiquer et corriger ses propres brouillons jusqu'à obtenir un résultat optimal.

## Fonctionnalités 🚀
* **Création de projet :** Définissez un brief initial et votre cible.
* **Génération automatique :** L'IA génère un premier brouillon.
* **Review critique (Éditeur IA) :** Une deuxième passe de l'IA analyse le texte froidement en fonction du brief et émet une critique sévère et des suggestions d'amélioration.
* **Amélioration continue :** Si le texte est rejeté par la critique, l'IA génère une nouvelle version (VS) tenant compte des remarques.
* **Historique complet :** Parcourez facilement l'évolution de vos textes (V1, V2...) grâce à la persistance sous SQLite.

---

## 🛠️ Prérequis

Avant de commencer, assurez-vous d'avoir installé sur votre machine :
- **PHP** 8.2 ou supérieur
- **Composer** (Gestionnaire de dépendances PHP)
- **Symfony CLI** (Pour lancer le serveur local)
- (Optionnel) Git

Vous aurez également besoin d'une **clé API Groq** (gratuite et ultra-rapide) ou d'une clé API OpenAI (ChatGPT).
👉 [Obtenir une clé API Groq gratuite ici](https://console.groq.com/keys)

---

## 📦 Installation de zéro

### 1. Cloner ou télécharger le projet
```bash
# Clonez le dépôt (ou téléchargez l'archive) puis placez-vous dans le bon dossier
git clone <votre-url-de-depot>
cd ContentGenerator/ContentGenerator
```

### 2. Installer les dépendances PHP
```bash
composer install
```

### 3. Configurer l'environnement (Clé API)
Créez un fichier `.env.local` à la racine de l'application (au même niveau que le `.env`) pour y stocker vos variables sensibles en toute sécurité :

```bash
# Dans le fichier .env.local, ajoutez votre clé :
LLM_API_KEY="gsk_votre_cle_groq_ici"
```
*Note : Le projet est préconfiguré pour utiliser **Groq** et son modèle `llama-3.3-70b-versatile`.*

### 4. Initialiser la Base de Données (SQLite)
Le projet utilise SQLite, aucune installation de serveur type MySQL n'est donc requise.
Générez simplement les tables :
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```
*(Cela va créer le fichier `var/data.db` qui contiendra vos projets et textes).*

---

## 🚀 Lancement et Utilisation

Lancez le serveur de développement local fourni par Symfony :
```bash
symfony serve -d
```

Rendez-vous ensuite dans votre navigateur à l'adresse indiquée (généralement **[https://127.0.0.1:8000/](https://127.0.0.1:8000/)**).

### Workflow type :
1. Cliquez sur **Nouveau Projet**. Rentrez votre Brief et votre Cible.
2. Cliquez sur **Générer le 1er brouillon**. L'IA travaille puis affiche le texte.
3. Le texte ne vous satisfait pas ? Cliquez sur **Demander une critique (IA Éditeur)**.
4. Lisez le retour de l'éditeur IA. S'il n'approuve pas le texte, cliquez sur **Générer la version suivante**.
5. Répétez l'opération jusqu'à l'approbation parfaite !

---

## ⚙️ Architecture Code
- `src/Entity/*` : Les entités (`ContentProject`, `ContentDraft`, `ReviewFeedback`).
- `src/Service/AiGeneratorService.php` : C'est ici que se trouve toute "l'intelligence" (appels API, instructions LLM et paramétrage du modèle).
- `src/Controller/*` : Les points d'entrée (Accueil et Workflow).
- `templates/*` : Les interfaces Twig.
- `assets/styles/app.css` : Le design minimaliste et clean.
