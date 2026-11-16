# School ERP — Mobile Product Specification

> **Product:** School ERP mobile companion app (React Native)
> **Status:** Draft for review
> **Audience:** Product owners, engineering, QA, school staff, evaluators
> **Builds on:** The School ERP web system (Laravel 10 + Sanctum REST API). The mobile app is a full-featured companion, not a read-only viewer.

---

## 1. Product Overview

### 1.1 Pitch

A native mobile app for the School ERP system that gives **every stakeholder** — administrators, teachers, accountants, parents, and students — access to the information they are *allowed* to see, **on and off the internet**.

Schools operate in low-connectivity environments. The app must therefore be **offline-first**: it downloads the relevant data while online, stores it securely on device, and keeps working — including taking attendance, recording payments, and viewing results — when the network drops. When connectivity returns, changes sync to the server with conflict resolution.

### 1.2 Problem statement

- The web ERP is desktop-first and unusable on a phone during the school day.
- Network in many schools is unreliable, so a purely online app is not dependable for core daily workflows (attendance at the gate, fee collection, checking results).
- Different people need different information (a teacher must not see payroll; a student must not see another student's balance). The app must enforce this not just by hiding menus but by validating every request.
- Parents/students shouldn't have to log in repeatedly; logging in once should "stick" for a long period.
- Admins need to keep working while offline: marking attendance, collecting fees, recording discipline incidents, stock counts, etc.

### 1.3 Goals

| # | Goal |
|---|------|
| G1 | Be fully usable **offline** for the core daily workflows of every role. |
| G2 | **Persistent session**: once logged in, a user stays logged in (indefinitely within the same device unless they log out, the account is disabled, or the password is reset). Only re-authenticate when security truly requires it. |
| G3 | **Role- and permission-driven UI**: the app fetches a user's roles/permissions and module enablement state from the backend and renders only what that user may do and see; the backend still authorizes every mutation. |
| G4 | Full feature parity: mirror all web modules in a mobile-native interface. |
| G5 | Automatic sync: created/edited records flush to the server when online; server data changes land on the device by push or pull. |
| G6 | Offline changes must never be silently lost and never silently overwrite newer server data. |

### 1.4 Non-goals (v1)

- Full SaaS billing / multi-tenant signup UI on mobile (the web already owns school onboarding).
- Replacing the web admin for heavy bulk operations (bulk report-card PDF export, large payroll runs) — the phone optimizes for *one record at a time*; bulk entry is available for speed, bulk *export* stays best on web.
- Native SMS sending from the phone (SMS continues to go through the server's provider integrations).
- Offline execution of money movement (payments are recorded offline but reconciled/approved once online).

### 1.5 Product principles

1. **Availability over connectivity** — the app is a tool that never "goes down" because the network did.
2. **Trustworthy numbers** — fee balances, attendance counts, and results shown offline must be accurate and clearly timestamped ("last synced 2 min ago").
3. **Least privilege** — show nothing the user may not see; enforce everything server-side.
4. **One-tap speed** — daily actions (mark present, record payment, log incident) take ≤ 3 taps.
5. **No data loss** — every offline mutation is queued, retried, and surfaced if it fails.

---

## 2. Users & Roles

The app must mirror the web system's RBAC exactly. A user may hold **more than one role**; the app merges the capabilities of all of a user's roles.

| Role | Who | Primary mobile usage | Seed permission scope (from web RBAC) |
|------|-----|----------------------|----------------------------------------|
| Owner | SaaS provider / developer | Audit trail, modules toggles, system logs. Protected + hidden. | Everything incl. `users.*`, `roles.*`, `permissions.*`, `audit-trail.index`. |
| Super Admin | School's top administrator | Everything, incl. user/role/permission management. Protected. | All permissions. |
| Admin | School day-to-day manager | Staff/students, academics, fees, attendance, discipline, reports. | All except users/roles/permissions management. |
| Teacher | Teaching staff | Timetable, class attendance, mark entry, own results, leave requests. | `academics.view`, `academics.attendance.manage`, `exams.marks.enter-own`, `exams.schedule.view`, `exams.results.view-own`, `hr.leave.apply`. |
| Accountant | Finance officer / bursar | Fee collection, receipts, adjustments, finance dashboards, arrears, refunds. | `finance.*` + `fees.*`. |
| Parent | Parent/guardian | Their children's profile, fees & receipts, report cards, attendance, notices. Zero RBAC permissions; access is **ownership-scoped** via policy. |
| Student | Learner | Own profile, own fees & receipts, own report cards, own attendance, notices, timetable, assignments. Ownership-scoped via policy. |
| Staff (generic) | Non-teaching staff (e.g., librarian, nurse, driver, storekeeper) | A staff record may not map 1:1 to a role; the user's assigned role(s) decide what they see. Reuses other roles (e.g., a librarian gets `library.*`). |

**Rules:**

- The app fetches the user's roles + flattened permission set on every session start.
- Menu items, screens, buttons, and even fields are driven by that permission set **and** by module enablement (`modules.is_active` + `is_core`).
- Ownership-scoped roles (Parent, Student) do their authorization with the same logic the web portal uses: a parent may only see their *linked* children; a student only their *own* records.

---

## 3. Core Platform Requirements

### 3.1 Offline-first

The app treats the network as an optimization, not a requirement.

- **Read model:** The app ships with a local on-device database (SQLite) that mirrors the data the user is permitted to see. All screens render from local data first; the network refreshes it.
- **Write model:** Every create/update/delete performed offline is written locally and pushed to a **sync queue** (outbox). When online, the outbox flushes automatically in FIFO order; partial failures retry with backoff and are visible in a "Pending sync" screen.
- **Always online?** Same code path: writes go to the local DB then the outbox immediately.
- **Pinned data:** Fee structures, exam schedules, timetables, and student rosters for the *current term* are auto-downloaded (see §7) so the most-used data is already on-device.
- **Last-synced indicator:** Every dashboard and detail screen shows a low-prominence "synced Xs ago / offline" badge.
- **Recovery:** No silent data loss. If a queued mutation cannot be delivered (auth expired, network permanently gone, validation error), the user sees a banner and can retry or resolve.

### 3.2 Persistent login (the "stays logged in" requirement)

Session handling is the #1 daily UX concern for parents and students. Requirements:

- **Long-lived sessions:** Use Sanctum token pairs. Access tokens short-lived (e.g., 15–60 min), **refresh tokens valid for ≥ 90 days**, sliding — every refresh extends the lifetime so an active user is never logged out.
- **Silent refresh:** The app refreshes the access token in the background *before* it expires; the user never sees a login screen because of an expired token.
- **Secure storage:** Tokens and the user profile live in the OS keychain (iOS Keychain / Android Keystore via `react-native-keychain` or Expo SecureStore). Never in AsyncStorage or logs.
- **Logout semantics:**
  - The only things that terminate the session are (a) explicit logout, (b) the account being disabled/deactivated, (c) a password reset, (d) a server-initiated revocation (e.g., admin force-logout, security issue).
  - App updates and device reboots **do not** log the user out.
- **Biometrics (optional):** Offer "Unlock with fingerprint/Face ID" as an additional gate on top of the persisted session; enable with a switch in Settings. Never replaces server auth.
- **Offline login:** If a user has already authenticated on this device and credentials/refresh remain valid per the device, the app unlocks with cached identity while offline. If the refresh token has truly expired, require online re-login.

### 3.3 Role/permission/menu architecture

- After login the app stores: roles, flattened permissions (`hasPermission` semantics mirror the web's `User::getAllPermissions()`), module list with `is_active` + `is_core`, and the default landing screen for the user.
- **Menu rendering:** Sidebar/bottom tabs are computed from (role ⊕ permission) ∩ (enabled modules). A module disabled in the web `modules` screen must disappear from the phone too.
- **Screen-level guard:** Every screen re-checks permission locally *and* the API enforces it on every call. Hiding a menu is never the security boundary.
- **Field-level visibility:** e.g., an Accountant sees fee amounts; a Teacher recording marks never sees fee data.
- **Multi-role users:** A user with `Admin` + `Teacher` sees the union. The app shows an avatar role switcher only if it actually changes what is rendered.
- **Session bootstrap payload:** One `/api/mobile/bootstrap` call returns identity, roles, permissions, enabled modules, school info, current academic year/term, and the "sync plan" (§6). This single payload defines the whole UI.

### 3.4 Multi-device consistency

- Same account logged in on two phones must stay consistent: server writes win on the server side; local conflicts resolve per §6 rules.
- A device that has been offline > N days (configurable, default 30) prompts the user to re-sync before resuming mutations to avoid stale edits.

### 3.5 Push notifications

- Local notifications for offline-queued events (e.g., marks pending approval reminder on next launch).
- Remote notifications mirror web notifications (fee reminders, results published, school-wide notices, attendance alerts). Delivery uses FCM/APNs; content is per-role (a student never receives "paid salary" notifications).

---

## 4. Role → Information Matrix (who sees/does what)

| Area | Owner | Super Admin | Admin | Teacher | Accountant | Parent | Student |
|------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Dashboard (role-specific) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Users & Roles mgmt | ✅ | ✅ | — | — | — | — | — |
| Administration (modules, audit trail, logs) | ✅ | — | — | — | — | — | — |
| Students | ✅ | ✅ | ✅ | view roster only | view (fees context) | own children | own |
| Staff / HR | ✅ | ✅ | ✅ | self (profile, leave) | view basic | — | — |
| Academics setup (classes, subjects, periods) | ✅ | ✅ | ✅ | view | — | — | view timetable |
| Timetable | ✅ | ✅ | ✅ | own + class | — | — | own class |
| Student attendance | ✅ | ✅ | ✅ (manage) | own class (manage) | — | own children | own |
| Exams / marks | ✅ | ✅ | ✅ | enter own, view own results | — | children results/cards | own results/cards |
| Fees | ✅ | ✅ | ✅ | — | full (payment status + collect) | children fees/receipts | own fees/receipts |
| Finance (expenses, income, banks, budgets) | ✅ | ✅ | ✅ | — | ✅ manage | — | — |
| Library | ✅ | ✅ | ✅ | view | — | — | view catalog/loans |
| Inventory & stock | ✅ | ✅ | ✅ | — | view | — | — |
| Hostel | ✅ | ✅ | ✅ | — | — | children allocation | own allocation |
| Transport | ✅ | ✅ | ✅ | — | — | children route | own route |
| Communication (compose/send SMS/email) | ✅ | ✅ | ✅ | view history | — | receive | receive |
| Discipline | ✅ | ✅ | ✅ | own class view/report | — | own children | own (see own status) |
| Medical incidents | ✅ | ✅ | ✅ | own class view | — | own children | own |
| Notices / messages | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Portal basics (profile, change password) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

Legend: `—` = intentionally hidden (menu absent *and* access denied server-side). Error/success states and empty states must be informative, not "access denied" dead-ends.

---

## 5. Technical Stack

> Informed implementation direction — the exact libraries will be confirmed during spiked design. The backend contract (Laravel Sanctum + JSON API) is already in place.

| Layer | Choice (preferred) | Notes |
|-------|--------------------|-------|
| Framework | React Native (Expo managed) | Expo for tooling + OTA; native config for keychain and background tasks. |
| Navigation | React Navigation (bottom tabs + drawer + native stack) | Tabs computed from permissions. |
| Local database | SQLite via `react-native-sqlite-storage` or WatermelonDB | WatermelonDB preferred for reactive, relationship-rich offline data. |
| Sync engine | Custom outbox + WatermelonDB sync (or `react-native-quick-sqlite`) | Versioned rows, tombstones for deletes, monotonic `updated_at`. |
| Secure storage | `react-native-keychain` / Expo SecureStore | Tokens + cached permissions. |
| HTTP | `axios` + interceptors | Adds `Authorization: Bearer`, CSRF-free, retries with backoff. |
| Background | Expo background tasks + WorkManager/`bgTask` | Silent refresh of cached data + outbox flush on connectivity. |
| Connectivity watch | NetInfo per screen + global banner | Drives the "offline" UI mode. |
| Push | `expo-notifications` / FCM + APNs | Role-filtered content. |
| State | React Query (server cache) + Zustand (client/UI state) | Server state kept separated from offline DB writes. |
| Charts | Pure JS charting (SVG-based), offline-capable | Dashboards render from cached aggregates. |
| Build | EAS Build; OTA via EAS Update | App Store + Play Store, plus testable internal builds. |

### Backend API contract (what we must build on the Laravel side)

The web is a Laravel 10 blade app; the existing `routes/api.php` only exposes `/api/user`. The mobile app needs a proper mobile API. Required additions (REST, JSON, Sanctum token auth):

- `POST /api/mobile/login` → returns `{ access_token, refresh_token, expires_in, user payload }`.
- `POST /api/mobile/refresh` → rotates tokens; rejects if the account is disabled or password changed.
- `POST /api/mobile/logout` → revokes the token pair on all devices or the current one.
- `GET /api/mobile/bootstrap` → identity, roles, permissions (mirror of `User::getAllPermissions()`), enabled modules, school, current academic year/term, sync seed.
- Per-module list/detail/show/index endpoints with `since`/`updated_after` filters for incremental sync; write endpoints accepting the offline mutation payloads (`client_uuid`, `updated_at`, full row snapshot) for idempotent application.
- Server must return `409 Conflict` with the server's current row when a conflict occurs; the client resolves per §6.
- Role/ownership enforcement reuses the existing policies — the API layer adds a thin `auth:sanctum` guard.

---

## 6. Offline Architecture & Synchronization

### 6.1 Local change flow

```
User action
   │
   ▼
Write to local SQLite (optimistic, with client_uuid)
   │
   ▼
Enqueue mutation to OUTBOX (operation + row + timestamp)
   │
   ▼
Network? ── no ──► stays in OUTBOX (visible in "Pending sync", retry on connectivity event)
   │
  yes
   ▼
POST to API (idempotent by client_uuid)
   │
   ▼
Success → ack, remove from OUTBOX, store server version
Failure:
   • 401/403 → surface to user, do NOT auto-retry
   • 409 conflict → run resolution (§6.3)
   • network/5xx → backoff retry
```

### 6.2 Incremental pull

- Each synced table keeps `last_synced_at` and the max `updated_at` it has seen.
- A sync pass requests `?updated_after=<last_seen>`; the server returns changed rows; deletes arrive as tombstones (`deleted_at`).
- Full re-seed is possible via a `GET /api/mobile/sync/plan` that returns "which tables to fetch on first/slow networks."

### 6.3 Conflict resolution policy

1. If only one side changed → last-write-wins is **not** default; keep the *server* version when it changed and local didn't; keep *local* when server didn't change.
2. If both changed → field-level merge for independent fields (e.g., teacher updates remark; admin updates status) when safe; otherwise surface an explicit, user-readable conflict screen with a diff and options (Keep Server / Keep Mine / Custom merge).
3. Attendance and payments are append-only (immutable once created) → no conflicts, only duplicates prevented by `client_uuid` idempotency.
4. Money rows always resolve by keeping the server as authoritative once accepted; a local payment that fails server validation (e.g., already reconciled) is surfaced, not silently re-queued.

### 6.4 Guarantees

- **No loss:** outbox is transactional and durable; app kill/memory pressure never drops a queued write that was acknowledged locally.
- **Idempotency:** every mutation carries `client_uuid`; the API ignores repeats, so retries are safe.
- **Ordering within a session:** server applies mutations in the order sent (per outbox FIFO); cross-device ordering is by server receipt.

### 6.5 Which data is cached per role (seed plan)

| Data | Admin | Teacher | Accountant | Parent | Student |
|------|:---:|:---:|:---:|:---:|:---:|
| Current academic year, terms, classes, sections | ✅ | view | view | — | — |
| Student roster (their class / all) | ✅ all | own class | view (fee context) | children only | self |
| Timetables (current term) | ✅ | ✅ own/classes | — | — | ✅ own class |
| Attendance records (current term) | ✅ | own class | — | children | own |
| Fee structures + assignments + payments | ✅ | — | ✅ | children | own |
| Exam schedules + published results | ✅ | own subjects/batch | — | children | own |
| HR self-service (profile, leave balance) | ✅ | own | own | — | — |
| Notices + messages | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 7. Feature Modules

Each module below includes: purpose, key screens, permission gate, and offline behavior. Where a web module exists, the mobile app mirrors its functions with mobile-first flows.

### 7.1 Authentication & Session
- **Screens:** Welcome/logo, login (email/phone + password), biometric unlock, "session expired" (only when truly expired), change password.
- **Offline:** Unlock with cached identity when session is valid; change-password and login require network. Logout works offline and clears keychain + local DB.
- **Gates:** Public.

### 7.2 Sign-up / Onboarding (admin-invited)
- Admins create users on web or mobile; invite links send a one-time setup code. No self-service account creation on mobile.
- **Gates:** Admin/Super Admin/Owner create; Teacher/Staff self-onboarded via invite.

### 7.3 Dashboard (role-specific)
- **Admin:** today at a glance — attendance taken % for today, fee collections today, pending approvals (adjustments/refunds/requisitions), alerts.
- **Teacher:** my timetable today, my classes' attendance status, pending mark-entry, notices.
- **Accountant:** collections today, arrears, pending adjustments/refunds, bank balance.
- **Parent/Student:** next term dates, notices, fee status, latest results.
- Numbers render from cached data; each metric card shows its last-sync time.
- **Offline:** fully cached dashboards; a pull-to-refresh is disabled while offline with a friendly note.
- **Gates:** all logged-in roles, per gating above.

### 7.4 Notifications & Messages
- In-app notification center (mirrors web `notifications` + `messages`), role-filtered: fee reminders, result publication, notices, disciplinary/medical alerts to parents.
- Offline: cached inbox, send-queue for replies; notifications badge count computed locally.

### 7.5 User & Role Management
- **Screens:** user list/search, create/edit user, assign roles (multi-select), activate/deactivate, reset password, role list; view permissions of a role.
- **Gates:** `users.view/users.manage`, `roles.view/roles.manage` (Owner/Super Admin). Protected-account rules from web apply (no deactivating protected accounts unless bypass).
- **Offline:** users cached for directory; role assignment is online-only v1, queued in later releases.

### 7.6 Academic Management
- **Entities:** academic years, terms, classes, sections, class sections, subjects, class-subjects, teacher-subjects, periods, classrooms, academic calendar/events, term weeks.
- **Screens:** list/combo pickers, calendar with events, detail views.
- **Gates:** `academics.view` (all staff), `academics.settings.manage` (Admin+), Teacher = view only; Student/Parent = read timetable/calendar.
- **Offline:** current year/term + timetable + calendar cached; edits (Admin) use the outbox.

### 7.7 Student Management
- **Screens:** student directory, search (admission no/name), profile (bio, parents, siblings, documents, medical info, transport=True-hostel flags, photo/initials avatar), enrollment/history, transfer registration, unassigned students, ID card preview.
- **Actions governed by permission:** create/edit/import-export; parents & siblings linking; documents upload/download.
- **Gates:** `students.view/manage/import/export`. Parent/Student: ownership-scoped portal views.
- **Offline:** full roster of permitted students cached with photos; document download requires network (shows placeholder + retry); enrollment changes queued.

### 7.8 Teacher Management
- **Screens:** teacher directory, teacher profile (qualifications, employment history, documents, onboarding checklist), teacher-subject assignments, class-teacher assignment, workload view, teacher onboarding wizard (combined staff + user + role).
- **Gates:** `hr.view/hr.manage` (Admin+); `academic-teacher-management` module flag; Teachers see their own record only.
- **Offline:** roster + workloads cached; onboarding flow queued.

### 7.9 Attendance (students & staff)
- **Student attendance:** register for a class in one screen (grid tap toggles present/absent/late) — target ≤ 3 taps per classroom. Bulk "all present", per-student override, remark field. Report view (per class / per student, % present) with calendar selector.
- **Staff attendance:** clock in/out or daily register by an HR officer; own status for staff.
- **Gates:** student → `academics.attendance.manage` (Teacher Admin etc.); staff → HR module + `hr.view`.
- **Offline:** the flagship offline flow. Registration queued in bulk; double-tap toggles a local pending state; server idempotency by `(student, date)`.

### 7.10 Examinations, Marks & Results
- **Screens:** exam types, grading scales (KCSE/CBC seeds), exam schedules (+ auto-generate, rooms, invigilation), mark entry (per exam/class/subject; bulk + per-student; excel template import), marks approval workflow, grade book, mark sheets, report-card templates, generated report cards, exam analysis (performance, subject, rankings), CBC assessments (learning areas, strands, sub-strands), exam reports (individual/bulk PDF).
- **Gates:** Teacher `exams.marks.enter-own/ view-own`; Admin+ `exams.publish, approve, import, results.view-all, analysis, report-cards.export, grading.manage`.
- **Offline:** scheduled exams + own-subject mark grids cached; mark entry queued per-row (idempotent by `client_uuid`); report cards open from cached data; PDF generation in v2.

### 7.11 Fees
- **Screens (Accountant/Admin):** fee dashboard, collect payment (search student → show balance → payment split across fee structures → method select → auto receipt), print/save receipt, payment history, fee assignments + auto-assignment preview, adjustments (request/approve/reject + audit log), discount schemes, terms, fee structure & category management, arrears with CSV/PDF export, payment reversal, refunds (request/approve/complete), reports (expected revenue, collections, payment method, receipt register).
- **Screens (Parent/Student):** children/own fee summary, per-assignment detail with payment history + receipt, balance badge (Paid/Partial/Unpaid/No fee) in the portal colors.
- **Gates:** `fees.*`, `finance.*`; ownership for Parent/Student (like web portal `PortalFeeController`).
- **Offline:** fee collection is queued and reconciled on sync; receipts generated locally with a "pending sync" watermark until server-confirmed number arrives. Balanced/audit fields are read-only after server confirmation.

### 7.12 Human Resources
- **Screens:** HR dashboard, staff directory, departments, job positions, leave types + applications (apply/approve/reject), staff attendance, onboarding checklists, exit clearance, allowances, deductions, qualifications, documents, payroll list, payroll processing wizard (calculate/review/finalize), HR reports (headcount, payroll, leave, attendance).
- **Gates:** `hr.view/manage/approve`, `hr.leave.apply` (self-service), Admin+ manages; Employee self-service for own leave/allowances/payslips.
- **Offline:** roster + own leave balance cached; leave application queued; payroll processing is online-only (financial integrity).

### 7.13 Finance
- **Screens:** finance dashboard, expense categories + expenses (request/approve/pay), income, bank accounts/transactions/reconciliation, budgets + budget-vs-actual, financial years, financial reports (cash flow, P&L).
- **Gates:** `finance.view/manage/approve` (Accountant, Admin+).
- **Offline:** dashboards + recent transactions cached; expense recording queued; bank reconciliation online-only.

### 7.14 Library
- **Screens:** library dashboard, categories, book catalog (+cover placeholder), member management, issue/return with confirm dialog returning modal behavior, overdue list.
- **Gates:** `library.view/manage`; Parent/Student = view catalog + their loans.
- **Offline:** catalog cached; issue/return queued by `client_uuid`.

### 7.15 Inventory
- **Screens:** inventory dashboard, categories, items, add stock, issue stock, adjust stock (with reason), stock movement history, suppliers, requisitions (create/approve), purchase orders (create/receive).
- **Gates:** `inventory.view/manage/approve`; a Storekeeper gets `inventory.*`; Admin approves.
- **Offline:** stock levels + items cached; stock counts at end-of-term are a key offline flow (queued adjustments).

### 7.16 Hostel
- **Screens:** hostels/rooms/vacancy, allocations (single + bulk, transfer form, checkout), hostel reports (vacancy, student list).
- **Gates:** `hostel.view/manage`; Parent/Student = own child/self allocation view.
- **Offline:** occupancy list cached; allocations queued.

### 7.17 Transport
- **Screens:** routes, stops, vehicles, driver info, transport assignments/registrations, student-route assignment (route dropdown populates stops like web), transport dashboard and reports (route-wise, occupancy).
- **Gates:** `transport.view/manage`; Parent/Student = own route/stop.
- **Offline:** route + roster cached; assignments queued.

### 7.18 Communication
- **Screens:** compose (SMS or email; recipient classes; template picker; live recipient count), send history (+detail), providers settings admin (SMS/email providers, daily limit, test send, webhook), auto triggers (toggle/edit), pending confirmations queue (confirm/discard single + bulk).
- **Gates:** `communication.*` (Admin/Owner/Super Admin); others only receive.
- **Offline:** history cached; sending requires network with a friendly notice; pending-confirmations reviewed offline and actioned on sync.

### 7.19 Discipline & Medical
- **Discipline:** record incidents (student, date, offense, action taken, witness), list/filter, follow-up status. Parent sees their child's, Student sees own status.
- **Medical:** log medical incidents, attach student + notes + follow-up; flag linked emergency contacts.
- **Gates:** `discipline.view/manage` (Admin+), Teacher = view/report own class; Parent/Student ownership-scoped.
- **Offline:** fully queued logging — the classic "incident happened in the field" flow.

### 7.20 Administration (Owner-only)
- **Screens:** module toggle list (matching web `modules` screen — enable/disable, core locked), audit trail viewer (read-only), system logs viewer.
- **Gates:** Owner (`isOwner()`); Super Admin excluded. Audit trail cached for review offline; module toggles online-only (they change the sync plan itself).

### 7.21 Portal Self-Service (Parent/Student)
- Profile update, change password, notifications preferences, children switcher (Parent with >1 child), fee receipts download, report-card download, attendance summary, transport/hostel view, notices.
- All ownership-scoped; fully cached offline; downloads queued/network-gated.

---

## 8. UX & Design Principles

- **Mobile-first, thumb-zone:** primary actions in bottom half; floating action button for "collect payment / mark attendance".
- **Trustworthy finance surfaces:** amounts in monospace; status chips mirror web semantics (amber = balance, emerald = paid, rose = overdue); every figure with a "synced Xs ago" caption.
- **Offline visibility is honest:** a strip/icon indicates offline; offline-extended actions show an "⏳ pending sync" chip that the user can tap to see the outbox.
- **≤ 3 taps for daily actions:** attendance, record payment, log incident.
- **Accessibility:** minimum 44pt touch targets, system font scaling, high-contrast role statuses, full TalkBack/VoiceOver coverage.
- **Empty & error states:** describe *what happened* and *what's next* ("No attendance saved yet — tap Present to begin"), never raw permission errors.
- **Brand:** follows the existing brand system (restrained neutrals + indigo primary, amber financial, emerald success, rose danger). Dark mode supported (finance views keep amber semantics intact).
- **Loading:** skeleton screens while seeding local DB, never full-screen spinners that block navigation.

---

## 9. Security

- **Tokens:** access + refresh pairs; refresh rotation with replay detection (reuse → revoke all). Tokens stored only in the keychain.
- **Device-bound logins:** optional "trust this device"; a user can revoke all devices from any device.
- **Server-side authorization is the source of truth:** local permission checks are convenience only.
- **Data at rest:** SQLite encrypted (SQLCipher) where the device supports it; cached PII minimized to what the role needs.
- **Compliance notes (Kenya context):** payments seed M-Pesa references; financial totals must reconcile exactly to server; audit trail includes mobile-originated changes with `source=mobile` + `client_uuid`.
- **Data minimization & deletion:** student/parent records cached on device are removed (with server revocation) when the session is revoked or the account is deactivated.

---

## 10. Performance & Device Support

- **Targets:** Android 8+, iOS 13+; low-RAM Android phones (2 GB) are a first-class target; offline DB sizes capped (purge by academic-year policy).
- **Cold start:** renders a cached dashboard < 2 s on mid-range hardware.
- **Sync budget:** incremental pulls coalesce; full re-seed compresses payloads (JSON, gzip); images streamed with cache + disk quota.
- **Battery:** no background polling loops; connectivity events + geofenced wake-ups only; push replaces polling.

---

## 11. Non-Functional Requirements

| Item | Requirement |
|------|-------------|
| Reliability | No crash loses a queued offline write; outbox survives process kill. |
| Observability | Every screen logs: render time, sync state, API failures. |
| Versioning | API versioned (`/api/mobile/v1`); app requires minimum API version; feature flags for rollout. |
| Localization | English first; currency is KES; dates `DD/MM/YYYY`; numbers Kenyan-formatted. |
| Testing | Unit (sync engine, conflict resolver, permission matrix), integration (API contract tests), real-device offline matrix (airplane mode testing) CI. |

---

## 12. Acceptance Criteria (key scenarios)

1. **Fresh install + login:** Teacher logs in once, is not asked again for ≥ 90 days of daily use (API silently refreshes).
2. **Airplane-mode school day:** Admin marks a morning's attendance, Accountant records 5 payments, Nurse logs 2 incidents — all fully offline; on reconnect everything lands on the server; receipt numbers confirmed; zero data loss.
3. **Role check:** Student and Parent apps never expose another student's records; menus, search results, and even cached rows are scoped to ownership.
4. **Module toggle:** Owner disables `library` on web → library disappears from every phone within one sync cycle.
5. **Conflict:** Teacher edits a remark offline while Admin edits the same row online → app shows the conflict diff screen on next sync; nothing is silently overwritten.
6. **Revocation:** Admin resets a teacher's password → the teacher's phone session ends on next refresh; no silent re-login loop; clear message shown.
7. **Stale device:** Device offline > 30 days → prompts re-sync before mutations resume.

---

## 13. Release Roadmap

| Phase | Scope |
|-------|-------|
| **P0 — Foundation & core flows (v1.0)** | Auth + persistent sessions, bootstrap/permissions, offline DB + outbox + conflict resolver, dashboard, notices, attendance (student+staff), student directory, fees (collect + receipts + portal views), exam results/report cards (portal), profile. |
| **P1 — Full parity (v1.x)** | Academics setup, timetables, mark entry + approval + gradebook + grading scales, HR self-service + leave, expenses/income, library, discipline + medical, transport + hostel read/write, ID cards. |
| **P2 — Heavy admin & analysis** | Payroll wizard, bank reconciliation, budget vs actual, exam analysis, financial reports, admin module toggles + audit trail, bulk imports/exports, PDF generation on device. |
| **P3 — Enrichment** | Push notifications rollout, biometric unlock, multi-school (Owner) support, offline analytics, role-based "report bundles" for head teachers. |

---

## 14. Risks & Open Questions

| Risk / question | Mitigation / decision needed |
|-----------------|------------------------------|
| Conflict-resolution UX complexity for non-technical staff | Keep auto-merge default; conflict screens rare and guided; attendance & money are append-only. |
| Device storage growth | Purge policy by academic year + term rollover; cap images; user-visible "manage storage". |
| Multi-account shared family phone (parent + student share device) | Support switching accounts on one device with separate secure stores; confirm in design spike. |
| Refresh-token expiry vs "never log out" | Policy decision: default 90-day sliding refresh; confirm acceptable configurable window per school. |
| Sanctum token model doesn't natively rotate refresh | Extend with a mobile token table (or switch to Laravel Passport / JWT) during the API spike — decision needed. |
| Offline fee collection reconciliation concurrency | Money rows kept server-authoritative once accepted; receipts watermark "pending" until confirmed. |
| Guest/visitor users | Out of scope v1 (roles fixed list from web). |

---

## 15. Glossary

- **Outbox** — durable local queue of not-yet-synced mutations.
- **Bootstrap** — the single session-start payload defining identity, roles, permissions, modules, and school/term context.
- **Tombstone** — a soft-delete marker propagated by sync so deletions reach every device.
- **Ownership-scoped** — authorization by relationship (a parent is linked to specific students), not by role permission.
- **client_uuid** — the sender-generated idempotency key on every local mutation.
- **Sync plan** — the per-role list of tables seeded to a device plus incremental `updated_after` cursors.