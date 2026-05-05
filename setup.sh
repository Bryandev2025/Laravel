#!/bin/bash

# 1. Settings

DB_NAME="db"
DB_USER="db"
DB_PASS="king11BRYAN!2025"
DOMAIN="mlgcl.tech"
SERVER_PROJECT_DIR="/var/www/bryan/public"
PROJECT_DIR="/var/www/bryan"


# 2. Install Stack (PHP 8.3, MySQL, Nginx)

export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y
apt install -y php8.3-fpm php8.3-mysql php8.3-{mbstring,xml,bcmath,curl,zip,intl,gd}
apt install -y mysql-server nginx unzip python3-certbot-nginx

# 3. Configure MySQL

mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"

# 4. Configure NGINX

cat > /etc/nginx/sites-available/default <<EOF

server{

    listen 80 default_server;
    root $SERVER_PROJECT_DIR;
    charset utf-8;
    server_name $DOMAIN www.$DOMAIN;
    index index.html index.php;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$  {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
    deny all;
    }

}
EOF

nginx -t && systemctl reload nginx

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

echo "Starting SSL Configuration..."
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

echo "Setup Finished!"
