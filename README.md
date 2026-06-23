# Mother Art

Laravel 13 gallery for artist works with a public dark gallery, Filament admin panel, bulk photo import, and email order requests.

## Requirements

- PHP 8.3+ with `intl`, `pdo_sqlite` or your production database driver
- Composer
- Node.js and npm

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
```

Run the app:

```bash
composer run dev
```

Admin panel: `/admin`

Admin user is created by `php artisan db:seed` when `ADMIN_PASSWORD` is set in `.env`:

```env
ADMIN_EMAIL=elena-burkaltseva@yandex.ru
ADMIN_NAME="Елена Буркальцева"
ADMIN_PASSWORD=your-secure-password
```

## Email Orders

Set SMTP and recipient values in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
ADMIN_ORDER_EMAIL=
```

Visitors submit a no-payment request from an artwork page. The request is saved in the database and emailed to `ADMIN_ORDER_EMAIL`.

## Bulk Import

1. Open `/admin` and go to the “Импорт фото” page.
2. Select multiple images in the upload form and click “Загрузить и импортировать”.
3. Alternatively, copy images into `storage/app/import-artworks/` and click “Импортировать из папки”.
4. Imported images become unpublished draft artworks.
5. Edit each draft in “Работы”: set section, title, price, description, and publish it.

Supported import extensions: `jpg`, `jpeg`, `png`, `webp`, `gif`.

On Railway and in a single Docker container, the queue worker starts automatically with the web server. In local `docker-compose`, use the separate `queue` service.

## Tests

```bash
php artisan test
```

## Production (Railway)

После привязки домена [elenaburkaltseva.com](https://elenaburkaltseva.com/) в Railway → **Variables** задайте:

```env
APP_URL=https://elenaburkaltseva.com
APP_ENV=production
APP_DEBUG=false
APP_NAME="Елена Буркальцева"
OPENROUTER_HTTP_REFERER=https://elenaburkaltseva.com
```

Локальный `.env` менять не нужно — там остаётся `APP_URL=http://localhost`.

Не требуют смены при смене домена:

- `FILESYSTEM_PUBLIC_URL` — по умолчанию `/storage` (относительные URL)
- `SESSION_DOMAIN` — оставьте пустым (`null`)
- прокси уже настроен в `bootstrap/app.php` (`trustProxies`)

После изменения переменных Railway перезапустит деплой. Проверьте: главная, `/admin`, превью работ в галерее, импорт фото.
