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

Seeded admin user:

- Email: `admin@example.com`
- Password: `password`

Change this password before publishing the site.

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

1. Copy images into `storage/app/import-artworks/`.
2. Open `/admin`.
3. Use the “Импорт фото” page.
4. Imported images become unpublished draft artworks.
5. Edit each draft in “Работы”: set section, title, price, description, and publish it.

Supported import extensions: `jpg`, `jpeg`, `png`, `webp`, `gif`.

## Tests

```bash
php artisan test
```
