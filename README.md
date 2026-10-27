# School Management System (SMS)

A modern, role-based school management application built with **Laravel 13**, **Tailwind CSS**, **MySQL**, and **Pest PHP**. Designed for Administrators, Teachers, Students, and Parents with granular permission checks, queued email notifications, and interactive analytics.

---

## Default User Accounts

The database comes pre-seeded with static demo accounts for each role (password for all accounts is `password`):

| Role | Email | Permissions / Scope |
| --- | --- | --- |
| **Admin** | `admin@school.test` | Full access to all modules: Teachers, Students, Parents, Announcements, Roles & Permissions |
| **Teacher** | `teacher@school.test` | Manages students and linked parents; posts announcements with email delivery |
| **Student** | `student@school.test` | Views linked parents and received announcements |
| **Parent** | `parent@school.test` | Views enrolled children and parent announcements |

---

## Core Scenarios & Capabilities

### 1. Admin Authentication & Security
- Secure sign-in at `/login` with session regeneration and branded login layout.
- Rate limiting protection: Global rate limiting middleware restricts all routes to **10 requests per minute** (`throttle:10,1`) with HTTP `429 Too Many Requests`. Additionally, 5 consecutive failed login attempts locks IP/account.
- Permission-based route gating under `routes/admin.php` via Spatie Laravel Permission middleware.

### 2. Teacher Management (CRUD by Admin)
- Admin can create, read, update, and delete Teachers at `/admin/teachers`.
- Captures Teacher Name, Email, Contact Number, Password, and Profile Avatar.
- Direct role & permission assignment: When teachers are created, the `teacher` role and all corresponding permissions are automatically allotted directly.
- Server-side DataTables with instant search, sorting, and pagination.

### 3. Admin Announcements to Teachers (Bulletin-Only, No Emails)
- Admin can broadcast announcements targeted specifically to **Teachers**.
- Per business requirements, announcements to teachers are strictly bulletin-board style: **no emails are queued or sent**.
- Teachers see these notices immediately upon logging into their dashboard and announcements feed.

### 4. Student & Parent Management
- Users with student permissions manage students at `/admin/students`.
- Dynamically link one or more Parents/Guardians per student with contact numbers and relationship tags (`Father`, `Mother`, `Guardian`, `Other`).
- Empty parent row prevention: Adding parent rows requires both Parent Name and Parent Email; empty or incomplete rows block submission on both client and backend with clear error indicators.
- Direct role & permission allotment: When students and parents are created, their roles (`student`, `parent`) and all corresponding role permissions are automatically allotted directly to the user records.
- Parent email uniqueness is strictly validated across students and within the same student submission.
- Uncoupled Student-Teacher Architecture: Students are not assigned to individual teachers; any authorized user with `students.*` permissions can view and manage students seamlessly.

### 5. Teacher Announcements with Email Notifications & Attachments
- Teachers post announcements targeted to **Students**, **Parents**, **Both**, or **Custom Selection**.
- **Queued Email Delivery**: Dispatches queued `AnnouncementNotification` emails with direct file download buttons and attachment copies.
- **Delivery Throttling**: Implemented a 2-second staggered delay between recipients to prevent SMTP/Mailtrap 550 rate limit rejections.
- **Attachment Support**: Upload images, PDFs, or documents up to 10MB.
- **Custom Audience Selector**: Select2-powered multi-select recipient selector with live search, custom user badge formatting (Name, Email, and Role), and "Select All" / "Clear Selection" actions.

### 6. Global Admin Visibility & Role Management
- Admin has unrestricted visibility across all students, teachers, parents, and announcements.
- Full Roles & Permissions matrix editor at `/admin/roles` to customize abilities per role.

### 7. Log Viewer for Debugging
- Opcode's Log Viewer is available at `/log-viewer` for inspecting `storage/logs` in the browser.
- Use it during local development to trace queued announcement emails, validation failures, and HTTP exceptions without tailing log files on the command line.
- Restrict access in production (`LOG_VIEWER_ENABLED=false` or authenticated-only) so log contents are not publicly readable.

---

## Enhancements & Fixed Missing Pieces

### 1. Queued Job Resilience on Announcement Deletion (`DeleteWhenMissingModels`)
- **Problem**: When an announcement was created and queued for email delivery, deleting the announcement before queue execution caused worker crashes with `ModelNotFoundException` in `failed_jobs`.
- **Fix**: Added `#[DeleteWhenMissingModels]` and `public bool $deleteWhenMissingModels = true;` to `AnnouncementNotification`. In addition, `AnnouncementRepository::delete()` immediately purges any pending jobs matching the announcement from the `jobs` table.

### 2. Bulletin Announcements for Teachers (No Unnecessary Emails)
- **Problem**: Earlier iterations sent queued emails to all teachers for internal school announcements.
- **Fix**: Scoped `AnnouncementService::dispatchAnnouncementEmails()` to omit email dispatch for `AnnouncementAudience::Teachers`, displaying notices exclusively upon teacher sign-in.

### 3. Enhanced Announcement View Page & Attachment UI
- Replaced the previous edit workflow (which could desynchronize scheduled queues) with a dedicated read-only **Announcement View Page** (`/admin/announcements/{id}`).
- Redesigned the attachment section:
  - Formatted card with file format indicator (Image, PDF, or Document).
  - Cleanly centered image preview with hover zoom overlay ("Click to open full size").
  - Direct "Download Attachment" button linking to `/admin/announcements/{id}/download`.
  - Recipient cards breakdown for custom audience announcements.

### 4. Interactive Role-Based Dashboard with Analytics
- Upgraded `/admin/dashboard` from a static greeting into a dynamic analytics hub:
  - **Admin**: Total teachers, students, parents, and announcements; Announcements by Audience (Doughnut Chart); Monthly Activity Trend (Bar Chart); User Distribution by Role (Doughnut Chart).
  - **Teacher**: Total students count; Linked parents count; Announcements posted; Parent linkage ratio chart; Recent student records.
  - **Student**: Linked parents list; Announcements format breakdown chart (Attachment vs. Text-only).
  - **Parent**: Enrolled children cards; Received notices feed.

### 5. Parent Email Uniqueness & Validation
- Added composite validation ensuring parent emails are globally unique in the users table, and preventing duplicate parent email rows within a single student form submission.

### 6. UI & Notification Polish
- Set Toastr notification opacity to `1` with custom theme colors:
  - Success: `#14A648`
  - Warning: `#EAC048`
  - Danger: `#F84C43`
  - Info: `#5385FF`
- Standardized action buttons (`.action-btn-view`, `.action-btn-edit`, `.action-btn-delete`, `.action-btn-perm`).

---

## Application Routes & Rate Limiting

All application routes are protected by a global **10 requests per minute** rate-limiting middleware (`throttle:10,1`). If the request limit is reached within a 60-second window, the application terminates the request with an HTTP `429 Too Many Requests` status code and standard rate limit headers (`Retry-After`, `X-RateLimit-Limit: 10`, `X-RateLimit-Remaining: 0`).

### Complete Route Directory

#### 1. Public & Authentication Routes (`routes/web.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/` | — | `web`, `throttle:10,1` | Root landing redirect (authenticates to `/admin/dashboard`, guests to `/login`). |
| `GET\|HEAD` | `/login` | `login` | `web`, `throttle:10,1`, `guest` | Displays the login view for unauthenticated users. |
| `POST` | `/login` | `login.store` | `web`, `throttle:10,1`, `guest` | Authenticates credentials, regenerates session, locks account after 5 failed attempts. |
| `POST` | `/logout` | `logout` | `web`, `throttle:10,1`, `auth` | Logs out the current user, invalidates session, regenerates CSRF token. |

#### 2. User Profile Management (`routes/web.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/profile` | `profile.show` | `web`, `throttle:10,1`, `auth` | Displays the current user's profile information. |
| `GET\|HEAD` | `/profile/edit` | `profile.edit` | `web`, `throttle:10,1`, `auth` | Profile edit form (name, email, phone number, password change). |
| `PUT` | `/profile` | `profile.update` | `web`, `throttle:10,1`, `auth` | Validates and updates user profile data and/or password. |

#### 3. Administrative Dashboard (`routes/admin.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/admin/dashboard` | `admin.dashboard.index` | `web`, `throttle:10,1`, `auth`, `permission:dashboard.view` | Dynamic analytics hub tailored to Admin, Teacher, Student, or Parent roles. |

#### 4. Teacher Management (`routes/admin.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/admin/teachers` | `admin.teachers.index` | `web`, `throttle:10,1`, `auth`, `permission:teachers.view` | Server-side DataTables list of teachers with instant search and pagination. |
| `GET\|HEAD` | `/admin/teachers/create` | `admin.teachers.create` | `web`, `throttle:10,1`, `auth`, `permission:teachers.create` | Teacher creation form (name, email, phone, avatar, password). |
| `POST` | `/admin/teachers` | `admin.teachers.store` | `web`, `throttle:10,1`, `auth`, `permission:teachers.create` | Validates and creates a new teacher, uploads avatar. |
| `GET\|HEAD` | `/admin/teachers/{teacher}` | `admin.teachers.show` | `web`, `throttle:10,1`, `auth`, `permission:teachers.view` | View teacher profile card and assigned students summary. |
| `GET\|HEAD` | `/admin/teachers/{teacher}/edit` | `admin.teachers.edit` | `web`, `throttle:10,1`, `auth`, `permission:teachers.update` | Edit teacher information and optional avatar replacement. |
| `PUT\|PATCH` | `/admin/teachers/{teacher}` | `admin.teachers.update` | `web`, `throttle:10,1`, `auth`, `permission:teachers.update` | Saves changes to an existing teacher record. |
| `DELETE` | `/admin/teachers/{teacher}` | `admin.teachers.destroy` | `web`, `throttle:10,1`, `auth`, `permission:teachers.delete` | Deletes a teacher record (blocked if enrolled students are assigned). |

#### 5. Student & Parent Management (`routes/admin.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/admin/students` | `admin.students.index` | `web`, `throttle:10,1`, `auth`, `permission:students.view` | Server-side DataTables list of students for authorized users. |
| `GET\|HEAD` | `/admin/students/create` | `admin.students.create` | `web`, `throttle:10,1`, `auth`, `permission:students.create` | Form to create a student with dynamic multi-parent/guardian links. |
| `POST` | `/admin/students` | `admin.students.store` | `web`, `throttle:10,1`, `auth`, `permission:students.create` | Validates student data, creates student, auto-allots role permissions, and links parents. |
| `GET\|HEAD` | `/admin/students/{student}` | `admin.students.show` | `web`, `throttle:10,1`, `auth`, `permission:students.view` | Detailed student view with linked parents. |
| `GET\|HEAD` | `/admin/students/{student}/edit` | `admin.students.edit` | `web`, `throttle:10,1`, `auth`, `permission:students.update` | Edit student form with parent relationships editor. |
| `PUT\|PATCH` | `/admin/students/{student}` | `admin.students.update` | `web`, `throttle:10,1`, `auth`, `permission:students.update` | Updates student details and synchronizes parent linkages. |
| `DELETE` | `/admin/students/{student}` | `admin.students.destroy` | `web`, `throttle:10,1`, `auth`, `permission:students.delete` | Deletes student and cleans up parent pivot associations. |

#### 6. Announcements & Notifications (`routes/admin.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/admin/announcements` | `admin.announcements.index` | `web`, `throttle:10,1`, `auth`, `permission:announcements.view` | Announcements listing / feed filtered according to role permissions. |
| `GET\|HEAD` | `/admin/announcements/create` | `admin.announcements.create` | `web`, `throttle:10,1`, `auth`, `permission:announcements.create` | Announcement composer with audience picker and attachment uploader. |
| `POST` | `/admin/announcements` | `admin.announcements.store` | `web`, `throttle:10,1`, `auth`, `permission:announcements.create` | Publishes announcement and dispatches queued emails (2s staggering; bulletin-only for teachers). |
| `GET\|HEAD` | `/admin/announcements/recipients-search` | `admin.announcements.recipients.search` | `web`, `throttle:10,1`, `auth`, `permission:announcements.view` | AJAX search endpoint for debounced recipient selection in custom audience mode. |
| `GET\|HEAD` | `/admin/announcements/{announcement}` | `admin.announcements.show` | `web`, `throttle:10,1`, `auth`, `permission:announcements.view` | Read-only announcement view with inline attachment preview (images, PDFs, documents). |
| `GET\|HEAD` | `/admin/announcements/{announcement}/download` | `admin.announcements.download` | `web`, `throttle:10,1`, `auth`, `permission:announcements.view` | Secure stream download for announcement attachments. |
| `DELETE` | `/admin/announcements/{announcement}` | `admin.announcements.destroy` | `web`, `throttle:10,1`, `auth`, `permission:announcements.delete` | Deletes announcement, purges attachment files, and cancels pending queue jobs. |

#### 7. Role & Permission Management (`routes/admin.php`)

| Method | URI / Path | Route Name | Middleware | Description / Access Scope |
| --- | --- | --- | --- | --- |
| `GET\|HEAD` | `/admin/roles` | `admin.roles.index` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Matrix of system roles with user and permission totals. |
| `GET\|HEAD` | `/admin/roles/create` | `admin.roles.create` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | New role creation form. |
| `POST` | `/admin/roles` | `admin.roles.store` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Stores new custom role. |
| `GET\|HEAD` | `/admin/roles/{role}` | `admin.roles.show` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Displays role details and assigned users. |
| `GET\|HEAD` | `/admin/roles/{role}/edit` | `admin.roles.edit` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Role rename form. |
| `PUT\|PATCH` | `/admin/roles/{role}` | `admin.roles.update` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Updates role name. |
| `DELETE` | `/admin/roles/{role}` | `admin.roles.destroy` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Removes custom role (default core roles protected). |
| `GET\|HEAD` | `/admin/roles/{role}/permissions` | `admin.roles.permissions` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Visual permission assignment editor for the selected role. |
| `PUT` | `/admin/roles/{role}/permissions` | `admin.roles.permissions.update` | `web`, `throttle:10,1`, `auth`, `permission:roles.manage` | Persists updated permission checkboxes to the role. |

#### 8. Log Viewer (`opcodesio/log-viewer`)

| Method | URI / Path | Route Name | Description |
| --- | --- | --- | --- |
| `GET\|HEAD` | `/log-viewer` | `log-viewer.index` | Browser UI for Laravel log files (`storage/logs`). |
| `GET\|HEAD` | `/log-viewer/api/logs` | `log-viewer.logs` | JSON API for listing and filtering log entries. |
| `GET\|HEAD` | `/log-viewer/api/files` | `log-viewer.files` | JSON API for available log files. |

Disable the UI when you do not need it:

```env
LOG_VIEWER_ENABLED=false
```

---

## Automated Test Coverage

The application includes an automated test suite using **Pest PHP** (**66 passing tests**, 300 assertions):

```bash
php vendor/bin/pest
```

- `AuthenticationTest`: Login, logout, failed login throttling, session security.
- `RateLimitingTest`: Global 10 requests/minute rate-limiting enforcement for guest and authenticated routes.
- `TeacherManagementTest`: Teacher CRUD, validation, student assignment constraints.
- `StudentManagementTest`: Student CRUD, multi-parent relationships, email uniqueness, teacher scoping.
- `AnnouncementManagementTest`: Audience routing, email queueing, teacher bulletin (no email), custom selection, attachment download, queue cleanup on deletion.
- `DashboardTest`: Permission-gated metrics and charts for Admin, Teacher, Student, and Parent.
- `RolePermissionManagementTest`: Dynamic permission syncing.
- `DirectoryAccessTest`: Cross-role authorization rules.

---

## Local Setup & Installation

### Requirements
- PHP 8.5^
- Composer
- Node.js (v18+) & NPM
- MySQL Database (`sms`)
- Chrome or Chromium (optional, for running browser E2E automated tests)

### Step-by-Step Installation

1. **Clone & Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Configure your database credentials in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

3. **Database Migration & Seeding**
   ```bash
   php artisan migrate --seed
   ```
   *(To wipe and re-seed fresh test data at any time: `php artisan migrate:fresh --seed`)*

4. **Storage Symlink (Required for Avatars & Attachments)**
   Create the symbolic link from `public/storage` to `storage/app/public` so uploaded teacher avatars and announcement attachments are publicly accessible:
   ```bash
   php artisan storage:link
   ```

5. **Build Frontend Assets**
   ```bash
   # Development with hot-reload
   npm run dev

   # Or compile production bundle
   npm run build
   ```

6. **Start Application Server**
   ```bash
   php artisan serve
   ```
   Visit the application at `http://127.0.0.1:8000`.

7. **Log Viewer (Debugging)**
   After signing in locally, open `http://127.0.0.1:8000/log-viewer` to inspect application logs (queue failures, mail errors, and exceptions). Turn the viewer off outside local debugging with `LOG_VIEWER_ENABLED=false`.

---

## Email & Mailing Setup (Queued Notifications)

The system automatically delivers formatted email notifications (`AnnouncementNotification`) when announcements are posted for **Students**, **Parents**, **Both**, or **Custom Selection**. Emails are processed asynchronously through database queues to keep web requests instant and responsive.

### 1. Choosing a Mail Driver

#### Option A: Mailtrap (Recommended for Local Testing & UI Inspection)
Mailtrap captures all outgoing emails into a safe testing inbox without sending messages to real addresses.

1. Sign up for a free account at [mailtrap.io](https://mailtrap.io).
2. Go to **Email Testing** &rarr; **Inboxes** and copy your credentials.
3. Update your `.env` file:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="no-reply@school.test"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

#### Option B: Log Driver (No Internet / Offline Testing)
If you do not have SMTP credentials, route all outgoing mail directly to Laravel log files:
```env
MAIL_MAILER=log
```
Sent emails will be written to `storage/logs/laravel.log`.

---

### 2. Processing the Queue Worker

Because `QUEUE_CONNECTION=database` is configured, outgoing emails are queued in the `jobs` database table and must be processed by a queue worker.

Start the queue worker in a separate terminal:
```bash
php artisan queue:work
```

For real-time development reloading without restarting the worker on code edits:
```bash
php artisan queue:listen
```

> [!NOTE]
> **Built-in Rate-Limiting Protection**: To prevent SMTP providers (such as Mailtrap's free tier rate limits) from dropping messages or throwing HTTP `550 Too many emails per second`, `AnnouncementService` automatically staggers queued deliveries with a **2-second delay per recipient**.

---

### 3. Monitoring & Managing Queues

- **View Failed Jobs**:
  ```bash
  php artisan queue:failed
  ```
- **Retry All Failed Jobs**:
  ```bash
  php artisan queue:retry all
  ```
- **Flush / Clear Failed Jobs**:
  ```bash
  php artisan queue:flush
  ```

---

## Testing & Quality Assurance

### 1. Backend Unit & Feature Tests (Pest PHP)
Run the automated test suite (66 tests, 300 assertions):
```bash
php vendor/bin/pest
```

Log viewer for debugging:
```bash
BASE_URL/log-viewer
```
