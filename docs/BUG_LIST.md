# MENRO Phase 2 Audit: Ranked Bug List

> **Note:** role names in this document predate the October 2026 consolidation to System Administrator + MENRO Staff. They are kept as written because this is a dated record of what was found at the time.

Nothing in the application has been changed yet. This list is the output of Phase 2 and the input to Phase 3.

## How this was checked

| Method | What it covered |
|---|---|
| Baseline commands | `php artisan test`, `pint --test`, `route:list`, `npm run build`, and a read of `storage/logs/laravel.log` |
| Code review | Every Livewire component and controller in `app/Http`, the models used by the ticket module, the migrations' foreign keys, and the three uncommitted areas (login view, layout, violation tickets) |
| HTTP probe (a throwaway Pest test, run in a rolled-back transaction on `menro_test`) | Every GET route for 7 identities (guest + 6 roles), unauthorized Livewire deletes, ticket offense counting, field edge cases, search with special characters, and report export and print with empty and extreme date ranges |
| Real browser (Playwright, Chrome, against a copy of the test DB called `menro_qa`) | 324 page loads (admin and barangay roles x 27 pages x 3 widths x 2 themes) for console errors, failed requests and sideways overflow; plus a delete click, an XSS attempt and the login lockout copy |

"Confirmed" means I saw the behaviour happen. "Code-read" means I found it in the source but did not run it.

## Baseline (before any change)

| Check | Result |
|---|---|
| `php artisan test` (Herd PHP 8.4.20) | **80 passed, 6 skipped, 0 failed** (55 s). The 6 skips are `DataCacheTest` cases for models that do not use SoftDeletes, so they are skipped by design. |
| `pint --test` | **Fails on 118 files**, essentially the whole repo. There is no `pint.json`, so it uses the Laravel preset, which this code never followed (see B19). |
| `route:list` | 52 routes. Every controller and Livewire class resolves, and every view renders for an authorised role. No missing controller methods or views. |
| `npm run build` | Succeeds in 16.6 s. CSS 36 kB (7 kB gzip), JS 162 kB (60 kB gzip). One warning: `postcss.config.js` is parsed as an ES module because `package.json` has no `"type"`. |
| `laravel.log` | 249 entries, all dated 2026-05-15 to 2026-09-30, none new from this session. They are old setup errors (wrong DB credentials, missing tables or columns before migrations ran, a MySQL FK delete error) plus **10 "Maximum execution time of 30 seconds exceeded"** between May and Sep. I could not tell which page caused those, and I did not reproduce a slow page. |
| Browser sweep | **0 JS errors, 0 failed requests** apart from the expected 403s for the barangay role. |

## Critical

### B1. Any signed-in user can delete generators, entries and inspections, and deleting a generator wipes its entries. *Confirmed*
- **Where:** `GeneratorIndex::delete` ([GeneratorIndex.php:27](../app/Http/Livewire/Generators/GeneratorIndex.php#L27)), `EntryIndex::delete` ([EntryIndex.php:29](../app/Http/Livewire/Entries/EntryIndex.php#L29)), `InspectionIndex::delete` ([InspectionIndex.php:26](../app/Http/Livewire/Inspections/InspectionIndex.php#L26)). The matching views show the delete button to everyone.
- **Repro:** sign in as `barangay` (Barangay User), open `/entries`, click the trash icon on any row.
- **Evidence:**
  - Browser: `waste_entries` went from 1200 to 1199, and `audit_logs` recorded the delete by `barangay`.
  - Probe: Barangay User, Report Viewer and Data Encoder each deleted a generator through the Livewire action. Each generator's 24 to 36 entries were removed with it, because `waste_entries.generator_id` is `ON DELETE CASCADE` and generators are not soft-deleted. Inspections were deleted the same way.
  - The create and edit pages for these modules are already restricted. Only the delete actions are open.
- **Fix idea:** add the same `canAccess(...)` check the sibling deletes already use. Generators and entries are admin/officer, inspections are admin/officer/inspector (confirm in Phase 3, see D1).

## High

### B2. All 17 `wire:confirm` confirmations do nothing, so every delete is instant. *Confirmed*
- **Where:** 17 usages, e.g. [generator-index.blade.php:87](../resources/views/livewire/generators/generator-index.blade.php#L87), [user-index.blade.php:94](../resources/views/livewire/users/user-index.blade.php#L94), [entry-index.blade.php:100](../resources/views/livewire/entries/entry-index.blade.php#L100), plus barangays, collections, compliance, incidents, inspections, notifications, settings (remove photo), violation tickets and violations.
- **Root cause:** `wire:confirm` is a Livewire 3 directive. The app runs Livewire 2.12.8 (`composer.lock`), whose `livewire.js` contains no such directive, and the layout has no handler. Only `ClusterConfig` uses a working `onclick="confirm(...)"`.
- **Repro:** click any delete button. No dialog appears. In the Playwright run, no `dialog` event fired and the entry was deleted at once.
- **Impact:** every delete, including "delete user", "delete all notifications" and "delete generator (and all its entries)", happens on a single click. This turns B1 and any mis-click into permanent data loss.
- **Fix idea:** replace with the Livewire 2 idiom already used in `ClusterConfig` (`onclick="confirm('...') || event.stopImmediatePropagation()"`), naming the record. Phase 4 can later upgrade this to a proper modal.

### B3. A `%` or `_` in a violator's name corrupts the offense count and the fine. *Confirmed*
- **Where:** [ViolationTicketForm.php:79](../app/Http/Livewire/ViolationTickets/ViolationTicketForm.php#L79) counts previous tickets with `where('violator_name', 'ILIKE', <typed name>)`. `ILIKE` treats `%` and `_` as wildcards.
- **Evidence (probe, test DB with 2 other names present):** a brand-new person named `%` is issued as the **3rd offense, ₱2,500**. `J_an Dela Cruz` matches `Juan Dela Cruz` and is issued as the **2nd offense, ₱1,000**. A brand-new name gives the correct 1st offense, ₱500.
- **Impact:** wrong fine amount on an official citation.
- **Related, needs a decision (D3):** soft-deleted tickets are not counted, so deleting a ticket lowers the next offense. The ticket *number* deliberately uses `withTrashed()`, but the offense count does not.
- **Fix idea:** compare with an escaped, case-insensitive exact match (for example `LOWER(violator_name) = ?`).

### B4. `ILIKE` and `SPLIT_PART` only work on PostgreSQL, but the repo is also set up for MySQL. *Code-read, MySQL not run*
- **Where:** `ILIKE` in 11 places (`GeneratorIndex` 40-41, `EntryIndex` 46, `UserIndex` 50-52, `ViolationTicketIndex` 40-41, `BarangayIndex` 268, `ViolationTicketForm` 79). `SPLIT_PART` in [ViolationTicket.php:81](../app/Models/ViolationTicket.php#L81).
- **Why it matters:** `config/database.php` and `.env.example` default to `mysql`, `DEPLOY_HOSTINGER.md` tells the reader to set `DB_CONNECTION=mysql`, and the old log shows MySQL errors from this project. `render.yaml` and your local DB use PostgreSQL.
- **Expected effect on MySQL:** `ILIKE` is a syntax error, so every search box 500s, and issuing a violation ticket 500s (`nextTicketNumber`). I cannot run MySQL here, so this is unverified.
- **Fix idea:** `LOWER(col) LIKE ?` and a portable max-sequence query. Safe on both databases. See D2.

### B5. Violation tickets (and several other lists) are readable by every role. *Confirmed*
- **Where:** `ViolationTicketIndex` has its role check only inside `delete()`, not in `mount()`, so [`/violation-tickets`](../app/Http/Livewire/ViolationTickets/ViolationTicketIndex.php) loads for every role. The new receipt route ([ViolationTicketController.php:9](../app/Http/Controllers/ViolationTicketController.php#L9), uncommitted) checks nothing beyond being signed in.
- **Evidence (route matrix):** `/violation-tickets` and `/violation-tickets/{id}/receipt` return **200 for Data Encoder, Barangay User and Report Viewer**, while `/violation-tickets/create` and `/edit` correctly return 403 for them. The list shows violator names and home addresses of private individuals. The same inconsistency exists for `/violations`, `/compliance`, `/inspections`, `/collections` and `/incidents` (all 200 for every role, while their forms are restricted).
- **Fix idea:** put the roles from each module's form into `mount()` (or a `role:` route middleware). See D1 for which roles.

### B6. `auth.custom` and `role:` do not run on Livewire action requests. *Code-read, not exercised*
- **Where:** Livewire 2's `$persistentMiddleware` list does not include `AuthMiddleware` or `RoleMiddleware` ([LivewireManager.php:19](../vendor/livewire/livewire/src/LivewireManager.php#L19)). See W3 in `docs/LOGIN_SYSTEM.md`.
- **Impact:** a user who is deactivated, demoted or has their password changed keeps working on a page that was already open until they load a new page. Actions without their own role check (see B1, and `UserIndex::delete`) rely entirely on the page `mount()`.
- **Fix idea:** register both middleware with `Livewire::addPersistentMiddleware()`. One small change that protects every component.

## Medium

### B7. Waste-entry quantity of 100,000,000 or more crashes with a 500. *Confirmed*
- **Where:** [EntryForm.php:25](../app/Http/Livewire/Entries/EntryForm.php#L25): `numeric|min:0.01`, no maximum. The column is `decimal(10,2)`, so the database rejects the value: `SQLSTATE[22003] numeric field overflow`. `99999999.99` saves.
- **Fix idea:** add `max:99999999.99` (and a sensible unit-aware limit if you want one). The same check is worth making for `estimated_daily_waste_kg` in `GeneratorForm`.

### B8. A blank "segregation score" is saved as 0, not empty. *Confirmed*
- **Where:** `InspectionForm::save`: `segregation_score => $this->segregation_score === '' ? 0 : (int) ...`. The compliance table shows the score whenever it is not null ([compliance-index.blade.php:169](../resources/views/livewire/compliance/compliance-index.blade.php#L169)), so an inspection with no score displays a red **0/100**.
- **Root cause is partly the schema:** `inspections.segregation_score` is `NOT NULL DEFAULT 0` (migration `2024_01_01_000008`), so "no score" cannot be stored. Fixing it properly needs a migration to make the column nullable, so I am **not** doing it without your approval (D6). A no-migration alternative is to make the score a required field.

### B9. Editing an old inspection overwrites the generator's compliance status. *Code-read*
- **Where:** `InspectionForm::save` always runs `WasteGenerator::where(...)->update(['compliance_status' => ...])`, whichever inspection is being edited.
- **Impact:** correcting a typo on a 2025 inspection can flip today's status. Fix idea: only update the generator when the saved inspection is that generator's latest.

### B10. The whole page scrolls sideways on phones (375 px) on 12 list pages. *Confirmed*
- **Pages and overflow in px:** `/incidents` 479, `/compliance` 415, `/inspections` 415, `/users` 348, `/collections` 347, `/generators` 303, `/entries` 283, `/violation-tickets` 262, `/violations` 261, `/reports` 219, `/archive` 191, `/audit` 191. `/incidents` also overflows 66 px at 768 px.
- **Cause (suspected):** wide tables are not inside an `overflow-x:auto` wrapper, so the page grows instead of the table scrolling. I have not yet inspected each table.
- **Same in light and dark.** Screenshots of all admin pages were captured to the scratchpad `shots/` folder as the "before" set for Phase 4.

### B11. The app uses Philippine dates but runs in UTC. *Code-read*
- **Where:** `config/app.php` has `'timezone' => 'UTC'`. Madrid is UTC+8.
- **Impact:** from midnight to 08:00 Philippine time, "today" (defaults on every date field, "tomorrow's collections", overdue follow-ups) is the previous day, and "this month" KPIs roll over 8 hours late. I did not reproduce this at the right clock time.
- **Needs a decision (D4):** changing the timezone also changes how stored timestamps are read.

### B12. Default password `admin123` for every seeded account. *Code-read*
- **Where:** `UserSeeder` and `FreshDemoSeeder` (also printed to the console by `FreshDemoSeeder`).
- **Impact:** if a seeder ever ran on the deployed database, those accounts are guessable. Please check the production `users` table. I cannot see it.

## Low

| # | Finding | Status |
|---|---|---|
| B13 | Search treats `%` and `_` as wildcards: searching `%` on Generators returns all 15 rows of the first page. No error, no injection (`'; DROP TABLE users;--`, `<script>`, 5,000 characters and `\` all handled without exceptions). | Confirmed |
| B14 | `entry_date` accepts `0001-01-01` and `9999-12-31`; inspection `next_follow_up` accepts a date before the inspection date. | Confirmed |
| B15 | Data Encoder, Barangay User and Report Viewer can open `/entries/{id}/edit` for someone else's entry (the form fills with its data). Saving is correctly refused with 403, but opening it should be too. | Confirmed |
| B16 | The 5th failed login locks the account but still says only "Invalid username or password."; the lock message appears from the 6th try. | Confirmed in browser |
| B17 | The ticket-number retry loop catches every `QueryException` (not only duplicates) and retries 5 times before rethrowing. | Code-read |
| B18 | Login: the loading state (disabled button, "Signing in…") stays if the user comes back with the browser Back button (page cache). The password toggle has no `aria-label`. | Code-read |
| B19 | **Pint:** 118 files fail the default Laravel preset (mostly CRLF line endings, `!` spacing and brace style), and there is no `pint.json`. Running `pint` across the repo would rewrite nearly every file. | Confirmed. See D5 |
| B20 | **Test hygiene:** the suite relies on a pre-seeded `menro_test` database (31 generators, 1,200 entries, six users) and `FullFlowTest` assumes the admin has `user_id` 1. A fresh clone or CI database that is only migrated will fail. The existing `.github` CI config was not reviewed. | Code-read |

## Checked and found fine

- **Output escaping:** no `{!! !!}` in any view except a static SVG icon in `report-index.blade.php:36`. A Livewire search with `<script>` produced no error. My attempt to create a generator named with an `<img onerror>` payload did not save a record, so the browser XSS check is **inconclusive**. The static check is clean.
- **CSRF:** every form is a `POST` through the `web` group with `VerifyCsrfToken` and an empty `$except`.
- **SQL injection:** all searches use bound parameters.
- **Exports:** report export and print return 200 for all four report types with an empty range (1990) and a huge range (1900 to 2100). I checked the status code only, not the contents of the files.
- **Report dates:** `from` after `to` is rejected by validation.
- **Cascade rules:** a barangay with generators, schedules or incidents cannot be deleted (a clear message is shown). Users cannot delete themselves.
- **Login:** lockout, warnings and generic error text behave as designed. The login page shows no console errors in either theme.
- **Uncommitted work:** `ViolationTicketController`, the receipt route and the three images it uses all work. The receipt page rendered 200 for ticket pages. The layout change drops the sidebar logo and shows "MENRO MADRID" as text; I assume that is intentional (the `.brand-tag` CSS is now unused).
- **Probe harness caveat:** inside a single PHP process, Livewire leaves its own redirector bound after a component aborts with 403. This produced false 500s in my first matrix and is not an app bug. Real requests are separate processes.

## Decisions I need from you before Phase 3 touches them

| # | Question | My recommendation |
|---|---|---|
| D1 | Which roles may see the lists and receipts of violation tickets, violations, compliance, inspections, collections and incidents? (B5) Today it is everyone. | Match each module's own form roles: tickets, violations and inspections for Administrator, MENRO Officer and Field Inspector; collections and compliance for Administrator and MENRO Officer. Barangay User keeps incident reporting only. |
| D2 | Is production on MySQL (Hostinger) or PostgreSQL (Render)? (B4) | Make the queries work on both either way. It is low risk. |
| D3 | Should deleting a ticket lower the violator's next offense number? (B3) | No. Count soft-deleted tickets too, as the ticket numbers already do. |
| D4 | Change `app.timezone` to `Asia/Manila`? (B11) | Yes, but only after confirming how existing timestamps should display. I will not change it without your answer. |
| D6 | Make `inspections.segregation_score` nullable (new migration) so a blank score is not stored as 0? (B8) | Yes, a small additive migration. If you would rather avoid a schema change, make the score required instead. |
| D5 | Format with Pint? (B19) | Add a `pint.json` that matches the existing style and format only the files I touch. A repo-wide format would bury the real changes in noise. |

## Not verified

- Behaviour on MySQL (B4), and the 10 historical "30 seconds exceeded" errors (cause and page unknown).
- The browser sweep used the admin and barangay roles. The other four roles were covered by HTTP status checks only, not by browser rendering.
- No clicking through every individual button and form in the browser. Forms and deletes were exercised through Livewire tests, not by hand.
- XLSX and PDF export contents, and archive downloads of real files.
- Production configuration (`APP_DEBUG`, cookie settings, the proxy setup behind the rate limiter, see W2 in `docs/LOGIN_SYSTEM.md`).
- The probe and sweep scripts and the `menro_qa` database are scratch artifacts. I created the `menro_qa` copy of `menro_test` and a PHP server on `127.0.0.1:8123` pointed at it so the real databases stayed untouched. The probe test was moved out of `tests/`.

---

## Resolution status (added after Phase 3 and 4)

| Bug | Status | Covered by |
|---|---|---|
| B1 unauthorized deletes | **Fixed.** Generators and inspections: Administrator and MENRO Officer. Entries: Administrator or the encoder who created them (the same rule as editing). | `BugfixPassTest::test_b1_*` (11 cases) |
| B2 inert confirmations | **Fixed.** A shared accessible dialog names the record. Esc, Cancel and backdrop click all leave it untouched. | `test_b2_*`, browser checks in `QA_REPORT.md` |
| B3 wildcard offense count | **Fixed.** Exact, case-insensitive match. **Deliberately unchanged (D3):** a soft-deleted ticket still does not count toward the next offense number. Deleting a ticket issued by mistake arguably should not count against the person, so this is a business rule for you, not a bug I should decide. | `test_b3_*` |
| B4 PostgreSQL-only SQL | **Fixed in code.** Portable `LOWER(col) LIKE ?` and a `LENGTH()`-based ticket sequence. **Not run on MySQL.** | `test_b4_*` (also scans `app/` for `ILIKE`/`SPLIT_PART`) |
| B5 lists readable by every role | **Fixed for tickets, receipts, violations, inspections, compliance and collections** (route middleware). Incidents list intentionally stays open to all roles. | `test_b5_*` (6 routes x 6 roles) |
| B6 session check skipped on Livewire requests | **Fixed.** Livewire's message route now runs `AuthMiddleware` and answers 419 so the page reloads to the login screen. | `test_b6_*` |
| B7 oversized quantity | **Fixed** (validation limit). | `test_b7_*` |
| B8 blank score stored as 0 | **Fixed** with an additive migration (nullable column). Existing 0s are left alone. | `test_b8_*` |
| B9 old inspection overwrites status | **Fixed** (only the latest inspection updates the generator). | `test_b9_*` (2 cases) |
| B10 sideways scroll at 375 px | **Fixed** (`min-width: 0` on `.main`). | Browser check in `QA_REPORT.md` |
| B11 UTC vs Philippine time | **Not changed.** Needs your decision (D4). | Known limitation |
| B12 `admin123` seeded passwords | **Not changed.** Production data, which I cannot see. | Known limitation |
| B13 `%`/`_` wildcards in search | **Fixed** by the shared `App\Support\Like` helper. | `test_b13_*` |
| B14 absurd dates | **Fixed** (entry date 2000-01-01 to tomorrow; follow-up not before the inspection). | `test_b14_*` |
| B15 opening another user's entry form | **Fixed** (403). | `test_b15_*` |
| B16 fifth failed login wording | **Fixed.** | `test_b16_*` |
| B17 ticket-number retry | **Fixed** (only a duplicate ticket number is retried). | Code review only; hard to trigger on demand |
| B18 login a11y and Back button | **Fixed.** | `test_b18_*` |
| B19 Pint | **Resolved with a small ruleset.** `pint.json` enforces a handful of safe rules the code already meets, and `pint --test` passes. It does **not** enforce a full style: the repo has no consistent one (D5). | `pint --test` |
| B20 test hygiene | **Not changed.** The suite still needs a pre-seeded `menro_test`. | Known limitation |
