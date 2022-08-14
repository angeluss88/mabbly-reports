1) Clone the repo
2) `cp .env.example .env`, fill the DATABASE_URL
3) run `composer update`
4) create database `php bin/console doctrine:database:create`
5) run the migrations `php bin/console doctrine:migrations:migrate`
6) generate test data `bin/console load-data --accounts=100 --teams=10`.
You can change accounts and teams count
7) (optional) run the server `symfony server:start`
8) (optional) open `<server_url>/api` route in browser. (E.g. if you did previous step, you probably can run `http://127.0.0.1:8000/api`)