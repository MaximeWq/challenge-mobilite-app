# Challenge Mobilité - API Laravel

Application web de suivi d'activités de mobilité douce pour encourager les modes de transport écologiques en entreprise.

## 🚀 Technologies utilisées

- **Backend** : Laravel 11 (API REST)
- **Base de données** : MySQL
- **Authentification** : Laravel Sanctum
- **Frontend** : Vue.js (en cours de développement)

## 📋 Fonctionnalités

### Authentification
- Connexion/inscription des utilisateurs
- Gestion des rôles (utilisateur/admin)
- Tokens d'authentification sécurisés

### Déclaration d'activités
- Formulaire de saisie quotidienne
- Types d'activités : Vélo ou marche/course
- Conversion automatique : 1500 pas = 1 km
- Limitation : une seule déclaration par jour par utilisateur

### Statistiques et classements
- Statistiques personnelles
- Classement individuel (top 10)
- Classement par équipe
- Export CSV des données

## 🛠 Installation

### Prérequis
- PHP 8.2+
- Composer
- MySQL (WAMP/XAMPP)
- Node.js (pour le frontend)

### Backend (Laravel)

1. **Cloner le repository**
```bash
git clone <url-du-repo>
cd Projet-xr-repro
```

2. **Installer les dépendances**
```bash
cd backend
composer install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
```

Modifier le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=challenge_mobilite
DB_USERNAME=root
DB_PASSWORD=
```

4. **Générer la clé d'application**
```bash
php artisan key:generate
```

5. **Créer la base de données**
- Créer une base de données MySQL nommée `challenge_mobilite`

6. **Migrer et seeder la base de données**
```bash
php artisan migrate:fresh --seed
```

7. **Lancer le serveur**
```bash
php artisan serve
```

L'API sera accessible sur `http://localhost:8000/api`

### Frontend (Vue.js) - En cours

```bash
cd frontend
npm install
npm run dev
```

## 📊 Données de test

L'application inclut des données de démonstration :

- **5 équipes** avec noms et descriptions
- **15 utilisateurs** répartis dans les équipes
- **1 administrateur** : admin@demo.com / admin1234
- **Activités variées** sur les 30 derniers jours

## 🔌 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentification
Toutes les requêtes protégées nécessitent un token Bearer dans le header :
```
Authorization: Bearer {token}
```

### Endpoints principaux

#### Authentification
- `POST /login` - Connexion utilisateur
- `POST /register` - Inscription utilisateur
- `GET /user` - Récupérer l'utilisateur connecté
- `POST /logout` - Déconnexion

#### Activités
- `GET /activities` - Lister les activités (avec pagination)
- `POST /activities` - Créer une activité
- `GET /activities/{id}` - Récupérer une activité
- `PUT /activities/{id}` - Modifier une activité
- `DELETE /activities/{id}` - Supprimer une activité

#### Statistiques
- `GET /stats/general` - Statistiques générales
- `GET /stats/teams` - Classement par équipe
- `GET /stats/users` - Classement des utilisateurs
- `GET /stats/personal` - Statistiques personnelles
- `GET /stats/export` - Export CSV (admin)

#### Administration
- `GET /admin/users` - Lister tous les utilisateurs
- `PUT /admin/users/{id}` - Modifier un utilisateur
- `DELETE /admin/users/{id}` - Supprimer un utilisateur

### Exemples de requêtes

#### Connexion
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@demo.com",
    "password": "admin1234"
  }'
```

#### Créer une activité
```bash
curl -X POST http://localhost:8000/api/activities \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2025-07-10",
    "type": "velo",
    "distance_km": 15.5
  }'
```

## 🗄 Structure de la base de données

### Tables principales
- **utilisateurs** : id, nom, email, password, equipe_id, is_admin
- **equipes** : id, nom, description
- **activites** : id, utilisateur_id, date, type, distance_km, pas

### Relations
- Un utilisateur appartient à une équipe
- Un utilisateur a plusieurs activités
- Une activité appartient à un utilisateur

## 🧪 Tests

```bash
php artisan test
```

## 📝 Règles métier

1. **Une seule activité par jour par utilisateur**
2. **Impossible de modifier une activité des jours précédents**
3. **Conversion automatique** : 1500 pas = 1 km pour la marche/course
4. **Validation des types** : 'velo' ou 'marche_course'
5. **Droits d'accès** : utilisateurs ne peuvent voir/modifier que leurs activités

## 🚀 Déploiement

### Production
1. Configurer les variables d'environnement
2. Optimiser l'application : `php artisan optimize`
3. Configurer la base de données de production
4. Déployer sur un serveur web (Heroku, DigitalOcean, etc.)

## 📄 Licence

Ce projet est développé dans le cadre d'un challenge technique.

## 👥 Auteur

Développé avec Laravel 11 et Vue.js. 