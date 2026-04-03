# 🏥 Gestion de Rendez-vous Médicaux Pro
> **Solution Full-Stack moderne pour la digitalisation des flux cliniques.**

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)
![Inertia.js](https://img.shields.io/badge/Inertia.js-1.x-9553E9?style=for-the-badge&logo=inertia)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)
![Deployment](https://img.shields.io/badge/Deployed_on-Railway-0B0D0E?style=for-the-badge&logo=railway)

---

## 🚀 Aperçu du Projet
Ce projet, réalisé dans le cadre de la **Licence 3 Informatique à l'Université Thomas Sankara (UTS)**, vise à résoudre la problématique de la gestion manuelle des consultations. Il offre une plateforme interactive où **Patients**, **Médecins** et **Secrétaires** collaborent en temps réel.

🔗 **Accéder à la démo en direct :** [Visionner l'application sur Railway](https://gestion-medicale-pro-production.up.railway.app)

---

## ✨ Fonctionnalités Clés

### 👤 Espace Patient
- **Inscription & Profil :** Gestion autonome des données personnelles.
- **Réservation Intelligente :** Prise de rendez-vous selon les spécialités et services.
- **Historique :** Suivi complet des consultations passées et à venir.

### 🩺 Espace Médecin
- **Agenda Dynamique :** Visualisation temps réel des rendez-vous via un dashboard dédié.
- **Gestion de Profil :** Mise à jour des spécialités et rattachement aux services cliniques.

### 📑 Espace Secrétariat & Admin
- **Contrôle d'Accès :** Validation et gestion du flux des patients.
- **Supervision :** Gestion des entités (Cliniques, Services, Utilisateurs).

---

## 🛠️ Stack Technique
L'application utilise l'architecture **"Modern Monolith"** pour garantir performance et sécurité :

- **Backend :** Laravel 12 (Robuste & Sécurisé)
- **Frontend :** Vue.js 3 (Réactivité Single Page Application)
- **Pont :** Inertia.js (Communication fluide sans API complexe)
- **Base de données :** MySQL (Hébergé sur Aiven Cloud)
- **Design :** Tailwind CSS (Interface moderne et Responsive)

---

## 📈 Métriques du Projet (cloc)
Une analyse rigoureuse du code source révèle l'envergure du développement :
- **Lignes de code totales :** ~12 075
- **Composants Vue.js :** 48
- **Logique PHP :** 91 fichiers (Contrôleurs, Modèles, Migrations)

---

## ⚙️ Installation Locale

1. **Cloner le dépôt :**
   ```bash
   git clone [https://github.com/avoganaugustin5/gestion-medicale-pro.git](https://github.com/avoganaugustin5/gestion-medicale-pro.git)

2. Installer les dépendances PHP & JS :

Bash
composer install && npm install

3. Configurer l'environnement :
Copier .env.example en .env et configurer votre base de données.

4. Lancer les migrations :

Bash
php artisan migrate --seed

5. Démarrer le serveur :

Bash
php artisan serve & npm run dev
👨‍💻 Développeur
Augustin AVOGAN (Koudjo Augustin Sandaogo)
Étudiant en Licence 3 Informatique - Université Thomas Sankara

© 2026 - Projet de Fin de Cycle S6 - UTS
