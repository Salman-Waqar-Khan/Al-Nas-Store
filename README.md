# AL-NAS Perfume Oil Store

An English, responsive single-vendor e-commerce storefront built with Laravel, MySQL, Tailwind CSS and JavaScript.

## Features

- Luxury perfume-oil storefront and Facebook page link
- JavaScript cart persisted in the browser
- Cash-on-delivery checkout with Chittagong/outside-Chittagong delivery fees
- MySQL-backed products, variants, inventory and orders
- Password-protected `/admin` dashboard
- Add, edit, hide and delete products
- Add and delete categories
- Manage price, stock, size, fragrance notes and image URL
- Responsive mobile and desktop design

## Local setup

Create an empty MySQL database named `al_nas_store`, then configure `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=al_nas_store
DB_USERNAME=root
DB_PASSWORD=
```

Install and initialize the application:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. Admin: `http://127.0.0.1:8000/admin`.

For automated tests, create a separate empty MySQL database named `al_nas_store_test`, then run `composer test`. Tests never use the production database.

Before publishing, set a strong `ADMIN_PASSWORD` in `.env`. Product records included by the seeder are editable demo data; replace their names, prices and image URLs from the admin dashboard with the exact Al-Nas catalog. Never commit `.env`.
