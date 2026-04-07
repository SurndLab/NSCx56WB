Module D Laravel 10 - Execution Guide

1) Environment
- PHP 8.1+
- Composer 2+
- Docker (for MySQL)

2) Start MySQL via Docker
- cd laravel
- docker compose up -d --build

MySQL mapping:
- Host: 127.0.0.1
- Port: 33060
- Database: module_d
- User: module_d
- Password: module_d_pass

3) Install and boot project
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan db:seed
- php artisan storage:link
- php artisan serve --host=0.0.0.0 --port=8080

4) URLs
- Login: /XX_module_d/login
- Public API list: /api/XX_module_d/books.json
- Public API detail: /api/XX_module_d/books/{ISBN}.json
- Public book page: /XX_module_d/01/{ISBN}

5) Auth accounts
- Super admin: admin / 1234
- Publisher admins: create via backoffice flow (password hashed)

6) Database schema files
- Laravel migrations: database/migrations
- SQL schema export: database/schema.sql

7) Notes
- This scaffold focuses on backend module and schema correctness.
- UI templates can be implemented in Blade/Vue based on competition styling requirements.
