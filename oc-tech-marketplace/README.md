# OC TECH Marketplace

**All Digital Products. One Smart Marketplace.**
*We Bring Your Ideas To Life.*

A full-stack digital marketplace where creators, developers, designers, agencies, and
businesses buy, sell, and manage digital products — competing with ThemeForest,
CodeCanyon, Envato, Gumroad, Creative Market, Lemon Squeezy, and the Apple App Store.

This directory is a self-contained project living alongside the rest of this
repository. It does not share code, database, or deployment with the existing
fintech simulation app at the repo root.

## Status

This is the **foundational scaffold** (Phase 0). It stands up a working backend
and frontend with the core marketplace domain modeled end-to-end (auth, vendors,
categories, products, licenses, orders, reviews, wallets, coupons, support
tickets), so future work has a real codebase to extend rather than a blank
repo. It is not feature-complete against the full vision below — see
[Roadmap](#roadmap).

## Stack

| Layer      | Choice |
|------------|--------|
| Frontend   | Next.js 16 (App Router), React 19, TypeScript, Tailwind CSS v4 |
| Backend    | Laravel 12, PHP 8.4, Laravel Sanctum (API auth) |
| Database   | PostgreSQL |
| Cache/Queue| Redis |
| Search     | Meilisearch |
| Storage    | S3-compatible (S3 / Cloudflare R2) |
| Local dev  | Docker Compose |

## Repository layout

```
oc-tech-marketplace/
├── backend/     Laravel 12 API (auth, vendors, products, orders, ...)
├── frontend/    Next.js storefront (App Router, Tailwind, brand theme)
├── docker-compose.yml
└── README.md    (this file)
```

## Local development

### With Docker

```bash
cd oc-tech-marketplace
docker compose up -d postgres redis meilisearch
cd backend && cp .env.example .env && composer install && php artisan key:generate
php artisan migrate
php artisan serve
```

```bash
cd oc-tech-marketplace/frontend
npm install
npm run dev
```

Backend API: http://localhost:8000/api
Frontend: http://localhost:3000

### What's implemented right now

**Backend (`backend/`)**
- Laravel 12 app with Sanctum token auth (`/api/auth/register`, `/api/auth/login`, `/api/auth/logout`)
- Core schema & Eloquent models: `User` (role enum: customer/vendor/support_agent/moderator/administrator/super_administrator),
  `Vendor`, `Category` (self-referencing), `Product`, `ProductFile` (versioned files), `License`,
  `Order`, `OrderItem`, `Review`, `Wishlist`, `Coupon`, `Wallet`, `WalletTransaction`, `SupportTicket`
- Public API: `GET /api/products` (filterable/paginated), `GET /api/products/{slug}`, `GET /api/categories`

**Frontend (`frontend/`)**
- Next.js App Router project with the brand theme (blue → orange gradient, glassmorphism,
  dark mode variables) wired into Tailwind v4's CSS-based theme
- Branded homepage: hero + search, category grid, featured products, marketplace stats,
  vendor CTA, footer

Everything else below is scoped for later phases.

## Roadmap

The full product vision spans far more ground than one implementation pass can
cover. It's broken down here by phase so it can be picked up incrementally.

### Phase 1 — Core commerce loop
- Vendor onboarding & verification (KYC, portfolio, manual/auto approval)
- Product upload flow with file versioning, screenshots, live demo links
- Cart, checkout, tax calculation, coupons, gift cards
- Payment gateways: Stripe, PayPal, Lemon Squeezy (start with these three)
- Secure/signed downloads with license enforcement and download limits
- Order → payout pipeline with marketplace commission splits

### Phase 2 — Customer & vendor dashboards
- Customer dashboard: orders, downloads, invoices, license keys, wishlist,
  reviews, notifications, saved payment methods
- Vendor dashboard: store settings, analytics, revenue, withdrawals, coupons,
  changelog/version management, store SEO
- Messaging (customer ↔ vendor ↔ admin), support tickets, notifications
  (email first, then push/SMS)

### Phase 3 — Admin & trust/safety
- Admin analytics dashboard (revenue, traffic, conversion, top products/vendors)
- Reviews with verified-purchase badges, vendor replies, abuse reporting
- Security: 2FA, passkeys, rate limiting, audit logs, fraud detection basics
- Search: Meilisearch integration, faceted browse, typo-tolerant search

### Phase 4 — Growth & AI
- AI-powered search (natural language, image search), recommendations,
  product comparison
- AI support assistant / chatbot
- Affiliate & referral programs, email campaigns, flash sales, bundles
- Blog/CMS, SEO automation (sitemap, OG, schema.org)

### Phase 5 — Platform maturity
- Public REST/GraphQL API, developer portal, webhooks, API keys & rate limits
- Mobile apps (iOS/Android) and installable PWA
- Multi-language & RTL support
- Enterprise features: teams/organizations, escrow, advanced fraud detection

### Reference: full category & feature catalog

The complete product spec (all target categories — Websites, Laravel Projects,
UI Kits, WordPress Themes, AI Agents, SaaS Boilerplates, and 30+ more; every
dashboard section; every payment gateway; every AI feature) is preserved in
the original project brief and should be consulted when scoping each phase
above — it is intentionally not duplicated here to avoid the roadmap drifting
out of sync with a second copy of the same list.

## Brand

- **Primary colors:** Blue (`#2563eb`) / Orange (`#f97316`)
- **Theme:** Glassmorphism, modern SaaS, Apple-like UI, rounded components,
  dark & light mode
- Implemented today via Tailwind v4 CSS variables in
  `frontend/src/app/globals.css` (`.glass`, `.brand-gradient`, `.brand-gradient-text`)
  — swap the CSS variables there to retheme.
