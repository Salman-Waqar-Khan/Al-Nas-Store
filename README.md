# AL-NAS Perfume Oil Store

An English, responsive single-vendor e-commerce storefront built with Laravel, Tailwind CSS and JavaScript.

## Features

- Luxury perfume-oil storefront and Facebook page link
- JavaScript cart persisted in the browser
- Cash-on-delivery checkout with Chittagong/outside-Chittagong delivery fees
- Orders saved to SQLite
- Password-protected `/admin` dashboard
- Add, edit, hide and delete products
- Add and delete categories
- Manage price, stock, size, fragrance notes and image URL
- Responsive mobile and desktop design

## Local setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. Admin: `http://127.0.0.1:8000/admin`.

Before publishing, set a strong `ADMIN_PASSWORD` in `.env`. Product records included by the seeder are editable demo data; replace their names, prices and image URLs from the admin dashboard with the exact Al-Nas catalog.
