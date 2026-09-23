# Sta. Monica Parish Connect

A CodeIgniter 3 web application for Sta. Monica Parish Church — a public parish
website plus an online services portal for booking sacraments (baptism,
wedding, funeral, confirmation, blessings), requesting certificates with
GCash payment, and a parish-office back end for Admin / Secretary / Priest
roles.

Built per the parish's project proposal, covering Phases 1–3 and parts of
Phase 5 (see **What's included** below for exact scope).

---

## Tech stack

- **Backend:** PHP 8.1+, CodeIgniter 3 (MVC)
- **Database:** MySQL / MariaDB
- **Frontend:** Tailwind CSS (CDN), Google Sans font, Alpine.js (modals/dropdowns),
  Toastr (notifications), SweetAlert2 (confirmations/prompts), jQuery + DataTables
  (server-side processing) for AJAX-driven admin tables, Phosphor Icons

---

## Quick start (XAMPP / local MySQL)

1. **Copy the project** into your web root, e.g. `htdocs/stamonica-parish`.
2. **Create the database** — import the schema:
   ```
   mysql -u root -p < database/schema.sql
   ```
   This creates the `stamonica_parish` database, all tables, and seed data
   (service types, a starter Mass schedule, and a default admin account).
3. **Configure the database connection** in
   `application/config/database.php` if your MySQL username/password differ
   from the defaults (`root` / empty password).
4. **Set your base URL** in `application/config/config.php`:
   ```php
   $config['base_url'] = 'http://localhost/stamonica-parish/';
   ```
5. **Make `uploads/` writable** by the web server (payment proofs, submitted
   requirement documents, GCash QR image, avatars).
6. Visit the site in your browser. Log in as the seeded admin:
   - **Email:** `admin@stamonicaparish.local`
   - **Password:** `Admin@12345`

   Change this password immediately (Accounts panel → Edit, or have the
   admin log in and use a password-reset flow you wire up — see
   **Not included** below).

### Deploying to shared hosting (e.g. InfinityFree, cPanel)

- Upload the whole project; point the domain's document root at the project
  folder (or move `index.php` + this structure to `public_html` and adjust
  the `$system_path` / `$application_folder` variables at the top of
  `index.php` if you relocate `system/`/`application/` outside the web root,
  which is recommended for production).
- Import `database/schema.sql` via phpMyAdmin.
- Update `application/config/database.php` and `config.php` with your
  host's DB credentials and live URL.

---

## Default roles & login

| Role | Access |
|---|---|
| **Administrator** | `/admin` — full control: accounts, service config, mass schedule, announcements, events, ministries, settings, plus everything Secretary can do |
| **Parish Secretary** | `/staff` — daily transactions: review bookings, verify payments, process certificates, sacramental records, mass intentions, walk-ins |
| **Priest** | `/priest` — assigned schedule, booking details, mark completed, block unavailable dates |
| **Parishioner** | `/my/*` — book services, request certificates, pay via GCash, submit Mass intentions |

Create Secretary and Priest accounts from **Admin → Accounts**. New accounts
get a temporary password shown once on creation (share it with the staff
member directly).

---

## What's included

**Public website** — homepage, weekly Mass schedule + special schedules,
sacraments/services catalog, announcements (with publish/expiry window),
events calendar with online registration, ministries directory with
"interested to join" form, priest directory, about, contact form, prayer
request form, donation page, QR certificate verification page.

**Parishioner portal** — register/login, dynamic booking form per service
(baptism, wedding, funeral, confirmation, house/vehicle blessing, counseling)
with requirement file uploads, application status tracking with a full
timeline, certificate requests, GCash payment submission (reference number +
screenshot upload), payment history, Mass intention submission, profile +
password management, in-app notifications.

**Parish office (Secretary)** — booking review/approval workflow (submitted →
under review → missing requirements → requirements complete → [wedding only:
interview/canonical processing → priest review] → awaiting payment → payment
verification → approved → scheduled → completed), requirement document
verification, priest assignment with basic double-booking conflict check,
GCash payment verification with auto-generated OR numbers, certificate
processing (registry search → link record → generate QR → release), searchable
sacramental records registry, Mass intention moderation, walk-in transaction
recording.

**Administration** — everything Secretary has, plus: account management
(create Admin/Secretary/Priest/Parishioner accounts), service & fee & 
requirement-checklist configuration, Mass schedule management (weekly +
special/override schedules), announcements, events, ministries, GCash
account/QR settings, dashboard with KPIs and an audit-log activity feed.

**Priest** — assignment list, per-booking detail, acknowledge/notes, mark
completed, block unavailable date ranges (used for the double-booking check).

All CRUD screens use **server-side DataTables** (search, sort, pagination
all happen in the `datatable()` controller methods via AJAX) and Alpine/
jQuery modals for create-edit forms, matching the requested stack.

---

## Not included / suggested next steps

This is a strong, working foundation, not the full 40-section proposal in
one pass. Deliberately left as extension points:

- **Automated GCash gateway integration** — the current flow is the
  proposal's "Initial Implementation": scan QR → parishioner submits
  reference number + screenshot → staff manually verifies. Swapping in a
  real PSP (Xendit, PayMongo, GCash's own API) is a matter of replacing the
  `parishioner/Payment::submit()` flow with a webhook-driven one.
- **Email/SMS notifications** — the `notifications` table and in-app bell
  already work; wire CodeIgniter's `email` library (or an SMS gateway) into
  `Notification_model::push()` for outbound email/SMS.
- **Password reset flow** — `/forgot-password` is currently a placeholder
  pointing people to the office; add a token-based reset if self-service is
  needed.
- **PDF certificate generation** — certificates are currently tracked as a
  status/QR-token workflow; generating an actual styled PDF (e.g. with
  `dompdf` or `mpdf`) on "prepare" is a natural next step.
- **Cemetery, catechism, household registry, facility reservation modules**
  (proposal §36 "Future Expansion") — not built; the schema and patterns
  here (service_types → bookings → payments) extend cleanly to these.
- **Reports/analytics screens** — the data (bookings, payments, records) is
  all queryable; dedicated report views/exports weren't built out.
- **CSRF protection** is off by default (`config.php`) to keep the AJAX
  forms simple out of the box — turn it on and add the hidden token field to
  each form for production hardening.

---

## Project structure

```
application/
  config/           # database, routes, autoload, app constants (in constants.php)
  core/             # MY_Controller.php — Base_Controller, Public_Controller,
                     # Auth_Controller, Role_Controller (role-gated base classes)
  controllers/
    Auth.php, Home.php, Notifications.php
    admin/          # Administrator controllers
    staff/          # Secretary controllers
    priest/         # Priest controllers
    parishioner/    # Parishioner controllers
  models/           # one model per domain (User, Booking, Certificate, Payment, ...)
  views/
    layouts/        # public.php, app_parishioner.php, app_admin.php (shared by staff/priest)
    partials/       # nav, footer, sidebars, topbar, toastr bridge
    public/ auth/ parishioner/ admin/ staff/ priest/
  helpers/app_helper.php   # peso(), status_badge_class(), role_home_url(), etc.
database/schema.sql
uploads/            # payments, documents, certificates, avatars, announcements, settings
```

---

## Notes on the shared back-office views

Secretary and Admin share several screens (bookings, certificates, payments,
sacramental records) since the day-to-day workflow is identical — only the
menu and a couple of admin-only actions differ. Rather than duplicate every
view, `staff/*` controllers render the same `admin/*` view files; each view
detects its own base path (`admin` vs `staff`) from the current URI so links
and AJAX calls resolve correctly either way. If you want to diverge the UI
further, just point the Staff controller's `render_app()` calls at new
`staff/*` view files.
# STAMONICAPARISH
