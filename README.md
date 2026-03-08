# Finance Perso (Laravel PWA)

Application web PWA en Laravel Blade pour gerer ses finances personnelles:
- organiser ses revenus/depenses
- budgetiser par categorie
- suivre des objectifs d'epargne et d'investissement

## Stack
- Laravel 12
- PHP 8+
- PostgreSQL (configure dans `.env.example`)
- Blade + Bootstrap 5 (CDN)
- Service Worker + Manifest PWA

## Installation
1. Configurer l'environnement:
   - copier `.env.example` vers `.env`
   - definir vos variables PostgreSQL
2. Generer la cle:
   - `php artisan key:generate`
3. Migrer la base:
   - `php artisan migrate`
4. Lancer l'app:
   - `php artisan serve`

## Fichiers PWA
- `public/manifest.webmanifest`
- `public/sw.js`
- `public/icons/icon.svg`

## Routes
- `GET /` dashboard
- `POST /transactions` ajouter transaction
- `POST /budgets` enregistrer budget
- `POST /goals` ajouter objectif
