# SITHEMR — Agent Memory (auto-loaded)

## Environment
- Path: `E:\sithemr`. Laravel 12, PHP 8.2.12, XAMPP, SQL Server (MSSQL). DB `sithemr` @ 127.0.0.1:1433, user `root`, pass `P@ssw0rd`.
- Dev server: `php artisan serve` on port 8000 → `http://127.0.0.1:8000`.
- `.env` `APP_URL=http://localhost` (≠ 127.0.0.1). Use relative URLs (e.g. `href="/book-appointment"`) — do NOT rely on `route()` absolute output.
- Admin login: `admin@demohospital.com` / `Admin@12345`.
- Project long-term log: `ragdata.md` (dated `[FEATURE]`/`[BUGFIX]`/`[DOCS]` entries at top). Update it at the end of every session.

## Core architecture (read before editing)
- Multi-tenancy: `tenant_id` = `hospitals.id` (DEMO hospital = id 1). `ScopedByTenant` trait auto-filters from `request()->attributes`.
- History soft delete: `history` column (1 = current) + global scope. When using `withoutGlobalScopes()`, re-add `->where('history', 1)` and `->where('tenant_id', $tid)` manually.
- Models: `$timestamps = false`, `$guarded = ['id']`, audit columns `created_by/created_date/updated_by/updated_date`.
- JWT middleware wraps the whole `web` group. Public routes must be added to the skip list in `app/Http/Middleware/JwtMiddleware.php`; Livewire updates from public pages need a referer-based bypass.
- Do NOT install `doctrine/dbal` — use raw `DB::statement()` for column changes.
- LSP "Undefined method" / CSS errors on Livewire/Blade files are false positives — ignore.

## Livewire 3 rules
- Never use `wire:change="updatedX"` — throws `DirectlyCallingLifecycleHooksNotAllowedException`.
- Cascading UI (selects/date that trigger re-renders) MUST use `wire:model.live`. Plain `wire:model` is deferred and won't re-render.

## Current feature: Patient Appointment Scheduling (COMPLETE, verified)
- Public booking page `/book-appointment` (route `public.book`), no login. Department → Doctor → Date → Slot → Patient info → book. Reference code `APT-00001`.
  - Component: `app/Livewire/Public/AppointmentBooking.php` + `resources/views/livewire/public/appointment-booking.blade.php` (renders with `layouts.guest`).
- Doctor schedule CRUD `/doctors/schedule` (route `doctors.schedule`, permission `doctors.schedule`). Edit/reschedule/mark done/cancel. Non-admin doctors see only their own appointments.
  - Component: `app/Livewire/Doctors/AppointmentScheduleCrud.php` + `resources/views/livewire/doctors/appointment-schedule-crud.blade.php`.
- Table `doctor_appointments`, model `app/Models/DoctorAppointment.php`, config `config/appointment.php` (slot_minutes 30, hours 08:00–17:00).
- Login page has a "Book an Appointment" link (`href="/book-appointment"`).
- **Current DB test data:** APT-00001 = Cancelled, APT-00002 = Completed (both "Overlap Test Patient", 2026-08-11, Dr. Maria Santos id 1). Delete if clean slate wanted.
- **Remaining (optional):** test a non-admin doctor on `/doctors/schedule`; add booking link to other guest pages if desired.
- Test HCP → department mapping: Dr. Maria Santos (1) → Cardiology (1); Dr. Juan Dela Cruz (2) → General Surgery (10); Nurse Beatrice Reyes (3) → Pediatrics (5).

## E2E testing
- Playwright installed locally only (`npm install playwright --no-save` — NOT in package.json). Browser already downloaded (`npx playwright install chromium`).
- Scripts must be `.cjs` (repo `package.json` has `"type": "module"`).
- Write ad-hoc `.cjs` test scripts, run with `node`, then delete them after passing.
