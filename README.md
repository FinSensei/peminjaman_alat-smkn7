Cara Setup Projek :
git clone https://github.com/FinSensei/peminjaman_alat-smkn7.git
sudo apt install composer // apt install composer
sudo apt-get update && sudo apt-get install php8.3-xml
sudo apt-get update && sudo apt-get install php8.3-gd
composer install
composer update
cp .env.example .env
php artisan key:generate
docker compose up -d --build
docker exec -it laravel-api php artisan migrate --seed