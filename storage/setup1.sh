#!/bin/bash


# 1. Setup Settings

DB_NAME="db"
DB_USER="db"
DB_PASS="king11BRYAN!2025"

PROJECT_URL="https://mlgcl.tech"

DOMAIN="mlgcl.tech"

SERVER_PROJECT_DIR="/var/www/bryan/public"
PERMISSION_DIR="/var/www/bryan"

# 2. Setup Stacks

export DEBIAN_FRONTEND=noninteractive
apt update && apt upgrade -y
apt install -y php8.3-fpm php8.3-mysql php8.3-{mbstring,xml,bcmath,curl,zip,intl,gd}
apt intsll -y mysql-server nginx unzippython3-cerbot-nginx

# 3. Configure MySQL

mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'LOCALHOST' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"


# 4. Configure NGINX

cat > /etc/nginx/sites-available/default <<EOF

server{

    listen 80 default_server;
    root $SERVER_PROJECT_DIR;
    charset utf-8;
    server_name $DOMAIN www.$DOMAIN;
    index index.html index.php;

    location / { try_files \$uri \$uri/ /index.php?\$query_string; }

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
php artisan migrate


# Configure Laravel .env (Uncommit & Change)

sed - "s/APP_URL=http://localhost/APP_URL=$PROJECT_URL/" .env 

sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i "s/# DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/" .env
sed -i "s/# DB_PORT=3306/DB_PORT=3360/" .env
sed -i "s/# DB_DATABASE=laravel/DB_DATABASE=$DB_NAME/" .env
sed -i "s/# DB_USERNAME=root/DB_USERNAME=$DB_USER/" .env
sed -i "s/# DB_PASSWORD=/DB_PASSWORD=$DB_PASS/" .env

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate

# 6. Permissions

chown -R $USER:$USER $PERMISSION_DIR
chown -R $USER:www-data $PERMISSION_DIR/storage $PERMISSION_DIR/bootstrap/cache
chmod -R 775 $PERMISSION_DIR/storage $PERMISSION_DIR/bootstrap/cache

echo "Configuring SSL Certificate.........."
certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN

echo "Setup Finished!"