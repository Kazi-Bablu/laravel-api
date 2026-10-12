# Laravel Fintech API

A simple Fintech-style REST API built with Laravel.

Features:

- JWT Authentication
- Service Repository Pattern
- CRUD API
- Unit Tests
- Feature Tests
- Seeder
- Clean Architecture

Tech Stack:

- Laravel
- MySQL
- JWT Auth
- PHPUnit

Installation:

git clone https://github.com/Kazi-Bablu/laravel-api.git

cd laravel-fintech-api

composer install

cp .env.example .env

php artisan key:generate

php artisan jwt:secret

php artisan migrate

php artisan db:seed

php artisan serve

API Endpoints:

POST /api/login

GET /api/customers

POST /api/customers

GET /api/customers/{id}

PUT /api/customers/{id}

DELETE /api/customers/{id}
