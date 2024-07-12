cp .env.example .env
composer require laravel/sail --dev : setup laravel sail(install if system can't laravel sail)
php artisan sail:install : setup environment for the first time
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)' : alias for sail
sail artisan migrate : migrate database for the first time
sail artisan storage:link : the the symbolic link storage for the first time
sail artisan db:seed create database seeder(role user) for the first time
sail up: start docker containers
sail down: stop docker containers
sail restart: restart docker containers
sail start
sail stop
