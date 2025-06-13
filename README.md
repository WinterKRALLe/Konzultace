### PHP 8.4, MySQL Server 8.0
#### `php.ini` extensions: curl, fileinfo, gd, mbstring, openssl, pdo_mysql, zip

### Development:
```bash
composer install
```
```bash
php -S localhost:8000
```
```bash
npm install "@tailwindcss/cli"
```
```bash
npx @tailwindcss/cli -i ./public/assets/css/global.css -o ./public/assets/css/output.css --watch
```

### Production:
```bash
composer install --no-dev --optimize-autoloader
```