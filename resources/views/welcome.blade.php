<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel on Ubuntu — deploy tutorial</title>
    <style>
        :root {
            --bg: #0f1419;
            --surface: #1a2332;
            --border: #2d3a4f;
            --text: #e7eef8;
            --muted: #8b9cb3;
            --accent: #38bdf8;
            --accent-dim: rgba(56, 189, 248, 0.15);
            --success: #34d399;
            --font: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            --mono: ui-monospace, "Cascadia Code", "SF Mono", Consolas, monospace;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        .wrap {
            max-width: 52rem;
            margin: 0 auto;
            padding: 2.5rem 1.25rem 4rem;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            letter-spacing: -0.02em;
        }

        .lede {
            color: var(--muted);
            margin: 0 0 2rem;
            font-size: 1rem;
        }

        h2 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 2rem 0 0.75rem;
            color: var(--accent);
        }

        p.hint {
            color: var(--muted);
            font-size: 0.9rem;
            margin: 0.25rem 0 0.5rem;
        }

        .block {
            position: relative;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            margin: 0.75rem 0 0;
            overflow: hidden;
        }

        .block pre {
            margin: 0;
            padding: 1rem 1rem 1rem 1rem;
            overflow-x: auto;
            font-family: var(--mono);
            font-size: 0.8125rem;
            line-height: 1.55;
            white-space: pre;
            tab-size: 4;
        }

        .copy-row {
            display: flex;
            justify-content: flex-end;
            padding: 0.5rem 0.75rem;
            background: rgba(0, 0, 0, 0.25);
            border-bottom: 1px solid var(--border);
            gap: 0.5rem;
            align-items: center;
        }

        .copy-btn {
            font-family: var(--font);
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--accent-dim);
            color: var(--accent);
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }

        .copy-btn:hover {
            background: rgba(56, 189, 248, 0.28);
            border-color: var(--accent);
        }

        .copy-btn:active {
            transform: scale(0.98);
        }

        .copy-btn.copied {
            border-color: var(--success);
            color: var(--success);
            background: rgba(52, 211, 153, 0.12);
        }

        footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.875rem;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Deploy Laravel on Ubuntu (Nginx, PHP 8.3, MySQL)</h1>
        <p class="lede">Step-by-step server setup. Use <strong>Copy</strong> on each block to paste into your terminal or editor.</p>

        <h2>1. Shell &amp; updates</h2>
        <p class="hint">Open a root shell, then update packages.</p>
        <div class="block" data-copy-target="shell-updates">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="shell-updates">Copy</button>
            </div>
            <pre id="shell-updates">sudo -i

sudo apt update &amp;&amp; sudo apt upgrade -y</pre>
        </div>

        <h2>2. MySQL</h2>
        <p class="hint">Install the server, then run SQL as root (<code>sudo mysql</code>) or paste after logging in.</p>
        <div class="block" data-copy-target="mysql-install">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="mysql-install">Copy</button>
            </div>
            <pre id="mysql-install">sudo apt install -y mysql-server</pre>
        </div>
        <div class="block" data-copy-target="mysql-sql">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="mysql-sql">Copy</button>
            </div>
            <pre id="mysql-sql">CREATE DATABASE ee;
CREATE USER 'engot'@@'localhost' IDENTIFIED BY 'nanaylita';
GRANT ALL PRIVILEGES ON ee.* TO 'engot'@@'localhost';
FLUSH PRIVILEGES;
EXIT;</pre>
        </div>

        <h2>3. Nginx &amp; PHP 8.3</h2>
        <div class="block" data-copy-target="nginx-php">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="nginx-php">Copy</button>
            </div>
            <pre id="nginx-php">sudo apt install -y nginx

sudo apt install -y php8.3-fpm php8.3-mysql
sudo apt install -y php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-intl php8.3-gd unzip</pre>
        </div>

        <h2>4. Composer</h2>
        <div class="block" data-copy-target="composer">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="composer">Copy</button>
            </div>
            <pre id="composer">curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer</pre>
        </div>

        <h2>5. App: clone, install, env, migrate</h2>
        <p class="hint">Edit <code>.env</code> for <code>DB_DATABASE</code>, <code>DB_USERNAME</code>, <code>DB_PASSWORD</code> to match MySQL above.</p>
        <div class="block" data-copy-target="app-setup">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="app-setup">Copy</button>
            </div>
            <pre id="app-setup">cd /var/www
sudo git clone https://github.com/mlgclschool/itelect104.git
cd itelect104

composer install
cp .env.example .env
nano .env

php artisan migrate
php artisan key:generate</pre>
        </div>
        <div class="block" data-copy-target="env-example">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="env-example">Copy</button>
            </div>
            <pre id="env-example">DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ee
DB_USERNAME=engot
DB_PASSWORD=nanaylita</pre>
        </div>

        <h2>6. Nginx site config</h2>
        <p class="hint">Replace the default site (e.g. <code>sudo nano /etc/nginx/sites-enabled/default</code>), then test and reload.</p>
        <div class="block" data-copy-target="nginx-site">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="nginx-site">Copy</button>
            </div>
            <pre id="nginx-site">server {
    listen 80 default_server;
    root /var/www/itelect104/public;

    server_name bryandacera.me www.bryandacera.me;

    index index.html index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}</pre>
        </div>
        <div class="block" data-copy-target="nginx-reload">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="nginx-reload">Copy</button>
            </div>
            <pre id="nginx-reload">sudo nginx -t
sudo systemctl restart nginx</pre>
        </div>

        <h2>7. Permissions</h2>
        <div class="block" data-copy-target="perms">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="perms">Copy</button>
            </div>
            <pre id="perms">sudo chown -R $USER:$USER /var/www/itelect104
sudo chown -R $USER:www-data /var/www/itelect104/storage /var/www/itelect104/bootstrap/cache
sudo chmod -R 775 /var/www/itelect104/storage /var/www/itelect104/bootstrap/cache</pre>
        </div>

        <h2>8. HTTPS (Certbot)</h2>
        <div class="block" data-copy-target="certbot">
            <div class="copy-row">
                <button type="button" class="copy-btn" data-copy="certbot">Copy</button>
            </div>
            <pre id="certbot">sudo apt install -y python3-certbot-nginx
sudo certbot --nginx -d bryandacera.me -d www.bryandacera.me</pre>
        </div>

        <footer>
            Tutorial blocks are for your deployment reference. Replace domain, paths, and credentials as needed for your environment.
        </footer>
    </div>

    <script>
        document.querySelectorAll('.copy-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-copy');
                var el = document.getElementById(id);
                if (!el) return;

                var text = el.textContent;
                function done() {
                    btn.classList.add('copied');
                    btn.textContent = 'Copied!';
                    setTimeout(function () {
                        btn.classList.remove('copied');
                        btn.textContent = 'Copy';
                    }, 2000);
                }

                if (navigator.clipboard &amp;&amp; navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(function () {
                        fallbackCopy(text, done);
                    });
                } else {
                    fallbackCopy(text, done);
                }
            });
        });

        function fallbackCopy(text, cb) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                cb();
            } finally {
                document.body.removeChild(ta);
            }
        }
    </script>
</body>
</html>
