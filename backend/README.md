# Challenge Mobilité - Documentation Complète

Application web de suivi d'activités de mobilité douce pour encourager les modes de transport écologiques en entreprise.

---

## 1. Guide d'installation locale

### Prérequis
- PHP 8.2+
- Composer
- MySQL (ou MariaDB)
- Node.js 20+

### Installation Backend (Laravel)
1. **Cloner le repository**
   ```bash
   git clone <url-du-repo>
   cd challenge-mobilite-app/backend
   ```
2. **Installer les dépendances**
   ```bash
   composer install
   ```
3. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   # Modifier les variables DB_*
   ```
4. **Générer la clé d'application**
   ```bash
   php artisan key:generate
   ```
5. **Créer la base de données**
   - Créer une base MySQL nommée `challenge_mobilite`
6. **Lancer les migrations et seeders**
   ```bash
   php artisan migrate:fresh --seed
   ```
7. **Démarrer le serveur**
   ```bash
   php artisan serve
   ```
   L'API sera accessible sur `http://localhost:8000/api`

### Installation Frontend (Vue.js)
1. **Installer les dépendances**
   ```bash
   cd ../frontend
   npm install
   ```
2. **Configurer l'URL de l'API**
   - Créer un fichier `.env` :
     ```
     VITE_API_URL=http://localhost:8000/api
     ```
3. **Démarrer le serveur de dev**
   ```bash
   npm run dev
   ```
   L'application sera accessible sur `http://localhost:5173`

---

## 2. Documentation API complète

### Authentification
- `POST /login` : Connexion utilisateur
- `POST /register` : Inscription utilisateur
- `GET /user` : Infos utilisateur connecté (token requis)
- `POST /logout` : Déconnexion

### Activités
- `GET /activities` : Liste paginée des activités
- `POST /activities` : Créer une activité
- `GET /activities/{id}` : Voir une activité
- `PUT /activities/{id}` : Modifier une activité (jour même)
- `DELETE /activities/{id}` : Supprimer une activité
- `GET /activities/user/{id}` : Activités d'un utilisateur

### Statistiques & Classements
- `GET /stats/general` : Statistiques globales (publique)
- `GET /stats/teams` : Classement par équipe
- `GET /stats/users` : Classement individuel (top 10)
- `GET /stats/personal` : Statistiques personnelles (token requis)
- `GET /stats/export` : Export CSV (admin)

### Administration (admin uniquement)
- `GET /admin/users` : Liste des utilisateurs
- `PUT /admin/users/{id}` : Modifier un utilisateur
- `DELETE /admin/users/{id}` : Supprimer un utilisateur

### Format de réponse standard
```json
{
  "status": "success",
  "data": { ... },
  "meta": { ... }
}
```

### Exemples de requêtes
- **Connexion**
  ```bash
  curl -X POST http://localhost:8000/api/login \
    -H "Content-Type: application/json" \
    -d '{"email": "admin@demo.com", "password": "admin1234"}'
  ```
- **Créer une activité**
  ```bash
  curl -X POST http://localhost:8000/api/activities \
    -H "Authorization: Bearer {token}" \
    -H "Content-Type: application/json" \
    -d '{"date": "2025-07-10", "type": "velo", "distance_km": 15.5}'
  ```

---

## 3. Guide d'utilisation de l'application

### Connexion
- Utiliser l'email et le mot de passe fournis (voir Données de test)
- Un admin peut accéder à la gestion des utilisateurs

### Déclaration d'activité
- Aller sur "Déclarer une activité"
- Choisir la date (aujourd'hui ou jours précédents)
- Sélectionner le type (vélo ou marche/course)
- Saisir la distance (km) ou le nombre de pas
- Une seule déclaration possible par jour

### Statistiques et classements
- Accéder au dashboard pour voir ses stats personnelles
- Voir le classement général et par équipe
- Visualiser l'évolution sur 30 jours (graphique)

### Administration
- Lien "Gestion utilisateurs" visible uniquement pour les admins
- Modifier/supprimer des utilisateurs
- Exporter les données du challenge en CSV

---

## 4. Choix techniques justifiés

- **Laravel (backend)** : framework robuste, sécurisé, adapté aux API REST, gestion native des migrations, seeders, tests, et middleware (auth, CORS, etc.).
- **Sanctum** : gestion simple et sécurisée des tokens d'API pour SPA/mobile.
- **Vue.js (frontend)** : framework moderne, réactif, idéal pour une SPA responsive et intuitive.
- **MySQL** : base relationnelle adaptée aux jointures et agrégations statistiques.
- **Pinia** : gestion d'état moderne pour Vue 3.
- **Chart.js** : visualisation claire des statistiques.
- **Tests unitaires Laravel** : garantissent la robustesse métier (règles, sécurité, validation).
- **Déploiement Railway** : simplicité, rapidité, gestion des variables d'environnement, logs, et base de données intégrée.

---

## 5. Données de démonstration

- **5 équipes** avec noms et descriptions
- **15 utilisateurs** répartis dans les équipes
- **1 administrateur** : admin@demo.com / admin1234
- **Activités variées** sur plusieurs jours

---

## 6. Tests

Lancer tous les tests unitaires et d'API :
```bash
php artisan test
```

---

## 7. Déploiement

- **Backend** : Railway (ou autre plateforme compatible PHP/MySQL)
- **Frontend** : Railway (Static), Netlify, Vercel, etc.
- **Variables d'environnement** à configurer selon la plateforme

---

## 8. Accès démo (exemple)

- **Frontend** : https://frontend-production-xxxx.up.railway.app
- **Backend** : https://backend-production-xxxx.up.railway.app
- **Admin** : admin@demo.com / admin1234
- **Utilisateur** : alice.martin@demo.com / password

