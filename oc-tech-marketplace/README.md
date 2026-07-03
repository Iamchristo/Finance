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

**Phases 0–4 are done** (scaffold, core commerce loop, customer & vendor
dashboards, admin & trust/safety, growth & AI). A customer can register,
browse, wishlist, buy with wallet or bank-transfer, download, review, and
raise a support ticket — plus search in natural language, compare products,
see recommendations, buy bundles and flash-sale deals, refer friends for a
wallet bonus, and chat with an AI support assistant. A vendor can apply, get
approved, publish products, edit store settings, run coupons/flash
sales/bundles, watch live sales analytics, and request a payout. An admin has
a dashboard for platform-wide analytics, vendor approvals, order payment
confirmation, withdrawal approvals, review moderation, and a blog CMS. See
[Roadmap](#roadmap) for what's next.

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
- Vendor onboarding: `POST /api/vendor/apply` (self-serve, sets role to vendor, status `pending`),
  admin approval via `POST /api/admin/vendors/{vendor}/approve` (role-gated), `app:promote-user-role`
  artisan command to bootstrap an admin locally
- Vendor product management: `POST /api/vendor/products` (with nested license tiers),
  `POST /api/vendor/products/{product}/files` (versioned file upload), `POST /api/vendor/products/{product}/publish`
  (blocked until the vendor is verified and a file + license exist)
- Checkout (`OrderService`): builds an order with per-item vendor commission splits, applies coupon
  discounts, and settles payment via **wallet balance** (atomic debit/credit, rolls back on
  insufficient funds) or **bank transfer** (order stays pending until an admin confirms it via
  `POST /api/admin/orders/{order}/confirm-payment`) — both fully working without any third-party
  payment gateway credentials
- Secure downloads: `POST /api/downloads/{product}/request` verifies the caller purchased the
  product and is under their license's download limit, then issues a 10-minute `temporarySignedRoute`
  that streams the file — tampered or expired links are rejected with 403
- Wishlist (`/api/wishlist`), reviews gated on a completed purchase (`POST /api/products/{product}/reviews`,
  with vendor replies via `POST /api/reviews/{review}/reply`) — the product's `average_rating` recomputes
  on each new review
- Vendor store settings (`PATCH /api/vendor/me`), coupon CRUD scoped to the vendor's own products
  (`/api/vendor/coupons`), and a revenue/sales analytics endpoint (`GET /api/vendor/analytics`)
- Withdrawal requests: a vendor requests a payout (`POST /api/vendor/withdrawals`), which debits their
  wallet immediately to prevent double-spending the same balance while pending; an admin then
  approves (funds already moved) or rejects (funds refunded) via `/api/admin/withdrawals/*`
- Support tickets with a threaded message history (`support_ticket_messages`) — customers open and
  reply to their own tickets, staff roles (support_agent/moderator/administrator/super_administrator)
  can see and reply to all of them

**Frontend (`frontend/`)**
- Next.js App Router project with the brand theme (blue → orange gradient, glassmorphism,
  dark mode variables) wired into Tailwind v4's CSS-based theme
- Branded homepage: hero + search, category grid, featured products (live from the API),
  marketplace stats, vendor CTA, footer
- Auth: `/login`, `/register` (React Hook Form + Zod), Zustand-backed session persisted to
  localStorage (with a `useAuthHydrated` guard so protected pages don't race the store's
  async rehydration and bounce a logged-in user to `/login` on reload)
- Shopping: `/products` (search), `/products/[slug]` (license picker), `/cart` (coupon field,
  wallet/bank-transfer payment), `/checkout/success`
- Account hub at `/account` linking to: `/account/orders` (history + invoice detail),
  `/account/downloads`, `/account/wishlist`, `/account/wallet` (balance, top-up, transaction
  history), `/account/support` (ticket list, new ticket, threaded reply view)
- Product detail page: wishlist heart button, star-rating review form (gated to purchasers,
  shows a "Verified Purchase" badge and any vendor reply)
- Vendor: `/vendor/apply`, `/vendor/dashboard` (store settings editor, live revenue/sales
  analytics with top products, coupon management, withdrawal requests, plus the existing
  product creation/upload/publish flow)

**Admin (`/admin`, `frontend/`)**
- Role-gated admin layout (`administrator`/`super_administrator`) with an overview showing
  platform-wide analytics: GMV, platform revenue (commission), order/user/vendor/product counts,
  pending-approval counts, and a top-vendors-by-earnings leaderboard (`GET /api/admin/analytics`)
- Vendor approvals: `/admin/vendors` lists vendors with a status filter, approve/reject actions
  (`POST /api/admin/vendors/{vendor}/approve|reject`)
- Order management: `/admin/orders` lists all orders with a status filter and a
  "Confirm Payment" action for pending bank-transfer orders (`POST /api/admin/orders/{order}/confirm-payment`)
- Withdrawal approvals: `/admin/withdrawals` with approve/reject actions
- Review moderation: customers can report a review from the product page
  (`POST /api/reviews/{review}/report`); `/admin/reviews` lists reported reviews with
  dismiss-report / hide actions — a hidden review disappears from the public product page and
  the product's `average_rating` is recomputed
- Audit log: every admin approve/reject/hide/confirm action is recorded (actor, action, subject,
  metadata) via an `AuditLog` model (`GET /api/admin/audit-logs`; no dedicated UI screen yet)
- Auth rate limiting: `POST /api/auth/register` (10/min) and `POST /api/auth/login` (5/min) are
  throttled per IP, each on its own bucket (a distinct `throttle` prefix per route — Laravel's
  default `throttle:N,M` middleware keys solely by IP with no route in the signature, so without
  a prefix every throttled guest route on the same IP shares one counter; found this the hard way
  when register calls were silently eating into login's allowance)

**Growth & AI (`backend/`, `frontend/`)**
- AI-powered search: `POST /api/search/ai` sends the shopper's natural-language query to an LLM
  (OpenAI, via `OpenAiClient`) to infer keywords/category/price ceiling, then runs a normal DB
  query with those filters; if the API key is unset or the request fails for any reason, it falls
  back to a plain keyword search and reports `ai_powered: false` so the UI can say so honestly
  rather than pretending
- AI support chatbot: `POST /api/support/ai-chat` (rate-limited, auth required) sends the
  conversation plus a system prompt describing the marketplace to the same LLM client; falls back
  to a fixed "assistant unavailable, open a ticket" message on failure, same principle as search
- Recommendations: `GET /api/products/{slug}/recommendations` — no AI involved, just
  frequently-bought-together (co-occurring `order_items`) falling back to same-category top
  sellers, so it's fast, free, and doesn't depend on an API key
- Product comparison: `GET /api/products/compare?ids=1,2,3` plus a `/compare` page with a
  side-by-side spec table; a persisted "compare" selection follows the shopper across pages via a
  floating bar
- Affiliate & referral program: every user gets a `referral_code` at signup; registering with
  `?ref=CODE` sets `referred_by_user_id`; the referrer's wallet is credited $5 the moment their
  referred user's *first* order completes (`/account/referrals` shows the code, link, and reward
  history)
- Flash sales: a vendor puts a time-boxed discount on one of their own products
  (`/api/vendor/flash-sales`); an active sale is exposed as `active_flash_sale` on the product and
  the discounted price is what checkout actually charges, not just a display trick
- Bundles: a vendor groups ≥2 of their own published products at a combined price
  (`/api/vendor/bundles`); buying a bundle (`POST /api/checkout/bundle`) allocates the bundle
  price across the included products proportionally to their base price (so vendor earnings still
  split sensibly) and grants full download access to every product in it
- Blog/CMS: admin-authored posts (`/api/admin/blog` CRUD) with public `/blog` and `/blog/[slug]`
  pages, rendered as server components for SEO
- SEO automation: a generated `sitemap.xml` (products, categories, blog posts, bundles) and
  `robots.txt`, per-page `generateMetadata`/Open Graph tags, and JSON-LD structured data
  (`Product`, `Article`) on product and blog detail pages

This was verified with four scripted browser runs. Phase 1's run: register as a vendor, apply,
get approved, create a product with two license tiers, upload a file, publish it, sign in as a
different customer, buy it with wallet funds, and download the exact file that was uploaded.
Phase 2's run extended that same loop through wishlisting a product, leaving a verified-purchase
review, checking the order invoice, opening a support ticket and getting a staff reply, and on
the vendor side: editing store settings, creating a coupon, and requesting (then having an admin
approve) a withdrawal — confirming the vendor's analytics panel reflects each change live.
Phase 3's run signed in as an admin and, entirely through the UI, approved a pending vendor,
confirmed a bank-transfer order's payment, approved a vendor withdrawal, and moderated a
customer-reported review — confirming the hidden review no longer appears on the public product
page in a fresh unauthenticated browser context.
Phase 4's run registered a customer through a referral link, searched (verifying the honest
AI-unavailable fallback badge), compared two products, bought one at its flash-sale price and
confirmed the referrer's wallet got credited $5, bought a bundle, read a blog post, published a
new one from the admin UI, opened the AI chat widget (verifying its graceful fallback message
too), and checked that `sitemap.xml`/`robots.txt` list the right URLs.

Everything else below is scoped for later phases.

## Roadmap

The full product vision spans far more ground than one implementation pass can
cover. It's broken down here by phase so it can be picked up incrementally.

### Phase 1 — Core commerce loop ✅ done
- Vendor onboarding & verification (manual admin approval — KYC/portfolio review still open)
- Product upload flow with file versioning (screenshots/live demo links still open)
- Cart, checkout, coupons (tax calculation and gift cards still open)
- Payment: wallet balance and bank transfer are live; Stripe/PayPal/Lemon Squeezy are not
  wired up yet — deferred rather than half-implemented, since they can't be exercised without
  real gateway credentials
- Secure/signed downloads with license download-limit enforcement
- Order → payout pipeline with marketplace commission splits, including vendor-initiated
  withdrawal requests with admin approve/reject (still bank_transfer/manual payout, not a real
  payment-gateway payout API)

### Phase 2 — Customer & vendor dashboards ✅ done
- Customer dashboard: orders + invoices, downloads, wishlist, reviews, support tickets
  (license keys, notifications, and saved payment methods are still open — the last two need
  real email/push infra and a card-on-file gateway, respectively)
- Vendor dashboard: store settings, analytics, revenue, withdrawals, coupons (changelog/version
  announcements and store SEO settings are still open)
- Support tickets with threaded replies are live; customer↔vendor direct messaging and
  email/push/SMS notifications are not — those need real-time infra (websockets/Reverb) and a
  mail/push provider, deferred rather than stubbed

### Phase 3 — Admin & trust/safety ✅ done
- Admin dashboard UI: platform overview, vendor approvals, order payment confirmation, and
  withdrawal approvals, all driven through real screens (not just curl/CLI)
- Admin analytics dashboard: GMV, platform revenue, order/user/vendor/product counts, top vendors
  by earnings (traffic/conversion funnels are still open — they need real pageview tracking)
- Review abuse reporting: customers can report a review, admins can dismiss or hide it, with the
  product's average rating recomputed on hide
- Security: rate limiting on register/login and an admin-action audit log are live; 2FA, passkeys,
  and automated fraud detection are still open — deferred rather than half-built, since they need
  a real second factor / device-risk provider to be worth anything
- Search: Meilisearch integration, faceted browse, typo-tolerant search — still open, deferred to
  Phase 4 alongside AI-powered search

### Phase 4 — Growth & AI ✅ done
- AI-powered search is live via `OpenAiClient` (OpenAI) with an honest keyword-search fallback;
  image search is still open — it needs a vision/embeddings pipeline, a bigger lift than the
  text-only search this phase shipped
- AI support assistant/chatbot is live with the same LLM-unavailable fallback pattern
- Recommendations (algorithmic, no AI needed) and product comparison are live
- Affiliate & referral programs (referral codes, wallet bonus on a referred user's first order)
  and flash sales & bundles are live; email campaigns are still open — they need real SMTP/ESP
  credentials to be worth building beyond a log-driver stub, so deferred rather than faked
- Blog/CMS and SEO automation (sitemap, robots.txt, per-page OG tags, JSON-LD) are live
- Note: this environment's network egress policy blocks `api.openai.com`, so the two AI features
  are implemented and wired end-to-end but could only be verified via their fallback paths here —
  set `OPENAI_API_KEY` in `backend/.env` and lift that restriction to exercise the real LLM calls

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
