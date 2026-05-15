WiredWise POS - Multi-Tenant Point of Sale System
<p align="center"> <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="WiredWise POS Logo"> </p><p align="center"> <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a> <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a> <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a> <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a> </p>


## Key Features

Multi-Tenancy Architecture

Complete Data Isolation: Row-level security ensures tenants never see each other's data

Tenant Types Support: Separate configurations for bars, restaurants, chemists, and supermarkets

Super Admin Controls: Only superadmin@pos.com can manage tenants

User-Tenant Relationship: Users can belong to multiple tenants with different roles

## POS Capabilities
Product Management: Create, edit, delete products with stock tracking

Customer Management: Track customers, loyalty points, and purchase history

Invoice System: Generate invoices with automatic tax and discount calculations

Payment Tracking: Support for cash, card, mobile money, and credit payments

Real-time Calculations: Automatic total updates as items are added

## Technical Features
PostgreSQL Database: Robust relational database with Row Level Security (RLS)

Filament Admin Panel: Modern, responsive admin interface

Livewire Components: Dynamic, reactive UI without JavaScript frameworks

Soft Deletes: Safe record deletion with recovery options

Automated Tenant Scoping: Automatic filtering of data by current tenant

## Requirements
PHP 8.2 or higher

PostgreSQL 12 or higher

Composer

Node.js & NPM (for asset compilation)

Laravel 13.9.0

Filament 5.6.0

## Installation

1. Clone the Repository

git clone https://github.com/wamwagii/wiredwise-pos.git

cd wiredwise-pos

2. Install Dependencies

composer install
npm install

3. Environment Configuration

cp .env.example .env
Update your .env file with database credentials:

env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wiredwise_pos
DB_USERNAME=your_username
DB_PASSWORD=your_password

4. Generate Application Key

php artisan key:generate
5. Run Migrations

php artisan migrate:fresh --seed
6. Create Admin User

php artisan make:filament-user
7. Install Filament Assets

php artisan filament:install --panels
php artisan filament:assets

8. Compile Assets

npm run build
9. Start Development Server

php artisan serve
Visit http://localhost:8000/admin to access the admin panel.

## Architecture
Multi-Tenancy Implementation
WiredWise POS uses a shared database with row-level security approach:


## Shared Database Schema
tenants (central tenant registry)
tenant_user (user-tenant assignments)
products (tenant-scoped)
customers (tenant-scoped)
invoices (tenant-scoped)
invoice_items (tenant-scoped)

## Key Components
Tenant Model: Represents each business (bar, restaurant, chemist, supermarket)

User Model: Implements HasTenants interface for Filament tenancy

Global Scopes: Automatically filter queries by current tenant

RLS Policies: Database-level security (optional but available)

## Database Schema
Tenants Table
sql
- id (primary key)
- uuid (unique identifier)
- name (business name)
- domain (unique domain/subdomain)
- type (bar, restaurant, chemist, supermarket)
- settings (JSON configuration)
- is_active (boolean)
- soft_deletes
- timestamps

Products Table
sql
- id (primary key)
- tenant_id (foreign key)
- name, sku, barcode
- purchase_price, selling_price
- stock_quantity, min_stock_threshold
- category, sub_category, unit
- is_active
- soft_deletes
- timestamps

Customers Table
sql
- id (primary key)
- tenant_id (foreign key)
- name, phone, email
- loyalty_card_number, loyalty_points
- address (JSON)
- is_active
- soft_deletes
- timestamps
Invoices Table
sql
- id (primary key)
- tenant_id (foreign key)
- customer_id (foreign key)
- user_id (foreign key)
- invoice_number (unique)
- subtotal, tax_amount, discount_amount, total_amount
- payment_method, payment_status
- paid_at
- notes
- soft_deletes
- timestamps

## Security

Tenant Isolation
Global Scopes: All tenant-aware models have global scopes that filter by tenant_id

Route Binding: Resource routes automatically scope to current tenant

Form Validation: tenant_id is automatically set on create operations

Super Admin Restrictions: Only superadmin@pos.com can manage tenants

Authentication & Authorization
Filament Authentication: Built-in authentication system

Tenant Access Control: Users can only access tenants they're assigned to

Role-Based Permissions: Different roles (admin, manager, staff, cashier)


## Testing
Run Tests

php artisan test
Test Multi-Tenancy Isolation

php artisan tinker


// Test data isolation
$barTenant = Tenant::where('name', 'Sunset Bar')->first();
$chemistTenant = Tenant::where('name', 'Health Plus Chemist')->first();

echo "Bar products: " . Product::where('tenant_id', $barTenant->id)->count();
echo "Chemist products: " . Product::where('tenant_id', $chemistTenant->id)->count();
Performance Optimization
Indexed Columns: tenant_id columns are indexed for faster queries

Global Scopes: Efficient filtering at database level

Eager Loading: Proper relationship loading to prevent N+1 queries

Caching: Optional Redis cache for frequently accessed data

## Deployment
Production Requirements
Web Server: Nginx or Apache with PHP 8.2+

Database: PostgreSQL 12+ with proper indexes

Queue Driver: Redis or database for background jobs

Cache Driver: Redis or Memcached for better performance

Deployment Steps
bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Compile assets
npm run production

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
Contributing
Contributions are welcome. Please follow these steps:

Fork the repository

Create a feature branch (git checkout -b feature/amazing-feature)

Commit your changes (git commit -m 'Add amazing feature')

Push to the branch (git push origin feature/amazing-feature)

Open a Pull Request

## License
This project is licensed under the MIT License. See the LICENSE file for details.

## Support
For support, email dev@wiredwise.com or create an issue in the GitHub repository.

## Acknowledgments
Laravel - The PHP framework

Filament - The admin panel framework

PostgreSQL - Database system

Tailwind CSS - CSS framework
