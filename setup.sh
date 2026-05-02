#!/bin/bash

# 1. Settings

DB_NAME="db"
DB_USER="db"
DB_PASS="king11BRYAN!2025"
DOMAIN="bryandacera.dev"
PROJECT_DIR=$(pwd)


# 2. Install Stack (PHP 8.3, MySQL, Nginx)

export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y
apt install -y php8.3-fpm php8.3-mysql php8.3-{mbstring,xml,bcmath,curl,zip,intl,gd}
apt install mysql-server nginx unzip python3-certbot-nginx

# 3. Configure MySQL

mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
mysql -e "CREATE USER IF NOT EXIST '$DB_SER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"

# 4. Configure NGINX

cat > /etc/nginx/sites-available/$DOMAIN <<EOF

server{

    listen 80;
    server_name $DOMAIN www.$DOMAIN;
    root $PROJECT_DIR/public;
    charset utf-8;
    index index.php;
    location / { try_file \$uri \$uri/ /index.php?\$query_string; }
    location ~ \.php$ { include snippets /fastcgi-php.conf; fastcgi_pass unix:/run/php/php8.3-fpm.sock; }

}
EOF

ls -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
systemctl restart nginx

# 5. Composer and Laravel Setup

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
cp .env.example .env

# Fix Laravel 11 .env (Uncomment and set to MySQL)

sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i "s/# DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/" .env
sed -i "s/# DB_PORT=3306/DB_PORT=3306/" .env
sed -i "s/# DB_DATABASE=laravel/DB_DATABASE=$DB_NAME/" .env
sed -i "s/# DB_USERNAME=root/DB_USERNAME=$DB_USER/" .env
sed -i "s/# DB_PASSWORD=/DB_PASSWORD=$DB_PASS/" .env

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate

# 6. Permission 

chown -R $USER:$USER $PROJECT_DIR
chown -R $USER:www-data $PROJECT_DIR/storage $PROJECT_DIR/bootstrap/cache
chmod -R 775 $PROJECT_DIR/storage $PROJECT_DIR/bootstrap/cache


echo "Setup Finished!"
