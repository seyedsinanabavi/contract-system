# Contract & Work Report Management System

A lightweight, role-based web application for managing **contracts** (with PDF attachments) and **daily work reports**, with an approval workflow for team leads and admins. The UI is in Persian (RTL, Vazirmatn font), built with plain HTML/JS and Bootstrap 5, backed by a PHP + MySQL JSON API.

> **Language note:** The interface and code comments are in Persian. This README is in English for wider accessibility.

---

## Features

- **Authentication** with session cookies and CSRF protection on all state-changing requests
- **Role-based access control** with three roles:
  | Role | Contracts | Work reports | Users |
  |---|---|---|---|
  | `admin` | Create, view, delete | View all, approve/reject | Create, enable/disable, change roles |
  | `team_lead` | View all | View all, approve/reject | View own team count |
  | `user` | — | Create, view own | — |
- **Contract management**: title, counterparty, amount, start/end dates, description, and an optional PDF upload (max 5 MB, MIME-checked with `finfo`)
- **Secure file download**: PDFs are served through an authorized endpoint, never from a public directory
- **Work reports**: date, title, description, hours spent; status flow `pending → approved | rejected` with reviewer notes
- **Dashboard** with per-role statistics and a 7-day activity chart (Chart.js)
- **User administration**: bcrypt-hashed passwords (cost 12), activation toggle, role assignment; admins cannot disable or demote themselves
- **Light/dark theme** toggle and responsive sidebar layout

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, vanilla JavaScript (ES modules-style helpers), Bootstrap 5.3 RTL, Font Awesome 6, SweetAlert2, Chart.js 4 |
| Backend | PHP 8 (`declare(strict_types=1)`, `never` return types, so PHP 8.1+) |
| Database | MySQL / MariaDB via PDO (prepared statements, emulation disabled) |
| Server | Apache or Nginx + PHP-FPM (any server that can run PHP) |

---

## Project Structure

```
contract-system/
├── backend/
│   ├── api/                  # JSON endpoints and page controllers
│   │   ├── index.php         # Entry redirect (dashboard or login)
│   │   ├── login.php         # Server-rendered login form (legacy)
│   │   ├── logout.php        # Logout + redirect
│   │   ├── contracts.php     # GET / POST (create) / DELETE contracts
│   │   ├── reports.php       # GET / POST (create, review) work reports
│   │   ├── users.php         # Admin-only user management
│   │   ├── dashboard.php     # Stats + 7-day chart data
│   │   └── download.php      # Authorized PDF download
│   ├── app/                  # Shared bootstrap code
│   │   ├── config.php        # DB, upload and CORS settings
│   │   ├── db.php            # PDO connection singleton
│   │   ├── helpers.php       # json_ok / json_error / CORS / CSRF helpers
│   │   ├── auth.php          # Auth endpoint (see Known issues)
│   │   ├── bootstrap.php     # Security headers + session start
│   │   ├── header.php        # Shared layout header (sidebar, topbar)
│   │   └── footer.php        # Shared layout footer
│   └── storage/uploads/      # Uploaded contract PDFs (create it, see Setup)
└── frontend/
    ├── index.html            # Login page (SPA-style, calls the API)
    ├── dashboard.html
    ├── contracts.html
    ├── reports.html
    ├── users.html
    └── assets/
        ├── css/style.css     # Theme, layout, RTL styles
        └── js/
            ├── api.js        # apiFetch wrapper, CSRF handling, requireAuth()
            ├── auth.js       # Login/logout logic
            ├── layout.js     # Shared sidebar/topbar rendering per role
            ├── dashboard.js
            ├── contracts.js
            ├── reports.js
            └── users.js
```

---

## API Overview

All endpoints return JSON in the shape:

```json
{ "ok": true,  "data": { ... } }
{ "ok": false, "error": "message" }
```

Mutating requests (`POST`, `DELETE`) must send the `X-CSRF-Token` header, which is returned by `auth.php?action=me` or `auth.php?action=login`.

| Method | Endpoint | Auth | Purpose |
|---|---|---|---|
| `GET` | `/auth.php?action=me` | any | Current user + CSRF token |
| `GET` | `/auth.php?action=csrf` | any | Refresh CSRF token |
| `POST` | `/auth.php?action=login` | — | Body: `{ username, password }` |
| `POST` | `/auth.php?action=logout` | any | End session |
| `GET` | `/contracts.php` | admin, team_lead | List contracts |
| `POST` | `/contracts.php` | admin | Create contract (multipart, optional `file`) |
| `DELETE` | `/contracts.php` | admin | Delete contract and its file (body: `{ id }`) |
| `GET` | `/download.php?id=N` | admin, team_lead | Download contract PDF |
| `GET` | `/reports.php` | any | List reports (own only for `user`) |
| `POST` | `/reports.php` | any | `action=create` or `action=review` (admin/team_lead) |
| `GET` | `/users.php` | admin | List users and teams |
| `POST` | `/users.php` | admin | `action=create` / `toggle` / `role` |
| `GET` | `/dashboard.php` | any | Stats and 7-day chart |

---

## Database Schema

No schema file is included in the repository yet. The tables below are inferred from the queries in the code:

- **`users`**: `id`, `full_name`, `username` (unique), `email` (unique), `password_hash`, `role` (`admin`|`team_lead`|`user`), `team_id`, `is_active`, `last_login`, `created_at`
- **`teams`**: `id`, `name`
- **`contracts`**: `id`, `title`, `party_name`, `amount`, `start_date`, `end_date`, `description`, `file_path`, `file_original_name`, `file_size`, `mime_type`, `created_by`, `created_at`
- **`work_reports`**: `id`, `user_id`, `title`, `description`, `work_date`, `hours_spent`, `status` (`pending`|`approved`|`rejected`), `reviewed_by`, `review_note`, `reviewed_at`, `created_at`
- **`access_logs`**: `id`, `user_id`, `action`, `ip_address`, `success`, `created_at`

---

## Setup

### Requirements

- PHP 8.1+ with extensions: `pdo_mysql`, `fileinfo`, `session`
- MySQL 8 or MariaDB 10.5+
- A web server (Apache with `mod_rewrite` or Nginx + PHP-FPM)

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/seyedsinanabavi/contract-system.git
   cd contract-system
   ```

2. **Create the database and tables** (schema not yet in the repo, see above), then create a user:
   ```sql
   CREATE DATABASE contract_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'change-me';
   GRANT SELECT, INSERT, UPDATE, DELETE ON contract_system.* TO 'app_user'@'localhost';
   ```

3. **Configure the app**: edit `backend/app/config.php` with your database credentials. Do **not** commit real credentials (see Known issues).

4. **Create the upload directory** and make sure PHP can write to it, but that it is **not** served directly by the web server:
   ```bash
   mkdir -p backend/storage/uploads
   chmod 750 backend/storage/uploads
   ```

5. **Serve the project**. The frontend calls the API at `http://localhost/contract-system/backend/api`, so the easiest local setup is to place the repo under your web root (e.g. XAMPP's `htdocs`) and open `http://localhost/contract-system/frontend/`.

6. **Create the first admin.** Insert a row into `users` with a bcrypt hash generated by PHP:
   ```bash
   php -r "echo password_hash('your-strong-password', PASSWORD_BCRYPT, ['cost' => 12]), PHP_EOL;"
   ```
   ```sql
   INSERT INTO users (full_name, username, email, password_hash, role, is_active)
   VALUES ('Admin', 'admin', 'admin@example.com', '<hash>', 'admin', 1);
   ```

---

## Security

- Passwords are hashed with **bcrypt** (`PASSWORD_BCRYPT`, cost 12)
- All SQL uses **PDO prepared statements** with emulation disabled
- **CSRF tokens** are required for all `POST`/`DELETE` requests
- **Security headers** are set in `bootstrap.php`: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, and a Content-Security-Policy limited to self and the CDNs in use
- **Uploads** are validated by real MIME type (not the client-supplied one), size-limited, and renamed to random 32-character hex names
- **Downloads** require an authenticated session with an allowed role
- CORS is restricted to an explicit origin allow-list in `config.php`

---

## Known Issues

This is an early-stage project. Before deploying it, please address the following:

1. **Missing helper library.** `backend/app/auth.php` currently contains the auth *endpoint* logic instead of the helper functions. The following are called but not defined anywhere in the repo: `start_session()`, `current_user()`, `login_user()`, `logout_user()`, `require_login()`, `require_role()`, `redirect()`, `e()`, `csrf_field()`.
2. **Hardcoded database credentials** in `backend/app/config.php`. Move them to environment variables or a file outside the web root, and rotate the current password.
3. **No schema file.** Add a `schema.sql` so the database can be created reproducibly.
4. **Missing `app.js`.** `footer.php` loads `/assets/js/app.js`, which is not in the repo.
5. **Mixed page architecture.** The repo has both PHP page controllers (`backend/api/login.php`, `logout.php`) and static HTML pages in `frontend/`, with inconsistent links (`/login.php`, `/dashboard.php`, `/index.html`). Pick one approach.
6. **Inconsistent endpoint paths.** `api.js` calls `/auth.php?action=...`, but there is no `auth.php` in `backend/api/`. Route it through `api/index.php` or add the file.
7. **Dead `api/index.php` logic.** It redirects to `/login.php` and `/dashboard.php`, which don't exist as served pages under the current layout.
8. **Some `POST`s use `$_POST` and others use JSON body.** `contracts.php` reads `$_POST`/`$_FILES` (multipart), while other endpoints read JSON. Document which is expected, or standardize.
9. **Access logging is partial.** Only logout writes to `access_logs`. Login attempts (success and failure) should also be logged.
10. **No automated tests.**

---

## Roadmap

- [ ] Add `schema.sql` and a seed script for the first admin
- [ ] Fix the auth helper library and route structure (see Known issues)
- [ ] Move secrets to environment variables
- [ ] Log login attempts and rate-limit login
- [ ] Pagination and search for contracts and reports
- [ ] Export reports to Excel/PDF
- [ ] English UI option (i18n)
- [ ] Automated tests for the API

---

## Contributing

Issues and pull requests are welcome. For larger changes, please open an issue first to discuss the approach.

## License

No license has been specified yet. Until one is added, all rights are reserved by the author.
