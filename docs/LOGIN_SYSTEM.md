# How Login Works in MENRO

*Written for a thesis panel. Technical names are in `code style` so they can be found in the source.*

MENRO does not use Laravel's built-in login (`Auth::attempt`, guards, `Auth::user()`). It has its own small system. It keeps three values in the PHP session, and one middleware checks them on every page.

| Piece | File |
|---|---|
| Login, logout, throttling | `app/Http/Controllers/AuthController.php` |
| "Are you signed in and still allowed in?" | `app/Http/Middleware/AuthMiddleware.php` (alias `auth.custom`) |
| "Do you have the right role for this route?" | `app/Http/Middleware/RoleMiddleware.php` (alias `role`) |
| Helper functions (`authUser`, `isAdmin`, `canAccess`, `passwordFingerprint`, `logAudit`) | `app/Helpers/AuthHelper.php` |
| Users and roles | `app/Models/User.php`, `app/Models/Role.php` |
| Routes | `routes/web.php` |
| Existing tests | `tests/Feature/AuthSecurityTest.php` |

**What is stored in the session after login**

| Session key | Meaning |
|---|---|
| `auth_user_id` | Which user this browser is signed in as |
| `auth_role` | Their role name, e.g. `System Administrator` |
| `auth_pw` | A short fingerprint of their password hash (see section 4) |

There are two roles: **System Administrator** and **MENRO Staff**. (Until October 2026 there were six — MENRO Officer, Data Encoder, Field Inspector, Barangay User and Report Viewer were consolidated into MENRO Staff by the `2026_10_11_000001_consolidate_roles_to_two` migration.)

---

## 1. The login flow, step by step

1. **Visit `/`.** `AuthController::redirectRoot` checks whether `auth_user_id` is in the session. If it is, the user goes to `/dashboard`. If not, they go to `/login`.
2. **`GET /login`** shows the form (`resources/views/auth/login.blade.php`). If the user is already signed in, they are sent straight to the dashboard instead.
3. **The user submits the form.** It is a `POST /login` with a hidden CSRF token. The `web` middleware group checks that token. A missing or wrong token gives a 419 error before any of our code runs.
4. **Validation.** `username` and `password` must both be present. If not, the form shows an error.
5. **Lockout check.** The controller asks the rate limiter whether this username and IP address have already failed 5 times in the last minute (section 3). If so, it refuses at once and says how many seconds to wait.
6. **Look up the user.** It finds a row in `users` with this `username` and `status = 'active'`.
7. **Check the password.** It calls `password_verify(typed password, stored hash)`. If there is no such user, or the password is wrong, it records a failed attempt and returns the same message, "Invalid username or password." (section 2).
8. **Success.**
   - The failed-attempt counter for this username and IP is cleared.
   - `session()->regenerate()` issues a brand-new session ID.
   - `auth_user_id`, `auth_role` and `auth_pw` are written to the session.
   - The browser is redirected to `/dashboard`.
9. **Every later page.** Everything inside `Route::middleware('auth.custom')` goes through `AuthMiddleware`. It re-reads the user from the database on every request, so changes made by an administrator apply immediately (section 5).

There is **no "remember me"**. The session lasts `SESSION_LIFETIME` = 120 minutes, and each request renews it. After 2 hours of inactivity the session file expires and the user is sent back to the login form. There is no "forgot password" screen either. An administrator resets passwords from the Users page.

---

## 2. How credentials are checked

- **Passwords are never stored as text.** They are saved as bcrypt hashes in `users.password_hash` (`bcrypt()` when created or changed, `password_verify()` when logging in). Bcrypt is deliberately slow and adds a random salt, so even two users with the same password have different hashes. The `User` model lists `password_hash` under `$hidden`, so it never appears if a user is turned into JSON.
- **Only active accounts can sign in.** The lookup includes `status = 'active'`. An inactive account is treated exactly like a wrong password, so the login screen does not reveal "this account exists but is disabled".
- **One message for every failure.** Unknown username, wrong password and inactive account all return "Invalid username or password." This stops an attacker from using the login form to find out which usernames exist.
- **Password rules.** When a user changes their own password, it must be at least 8 characters and the current password must be entered correctly (`SettingsForm::savePassword`). Administrators set passwords on the user form.

---

## 3. Brute-force protection

Guessing passwords is slowed down by Laravel's `RateLimiter`, in `AuthController`:

- **5 failed attempts** per *username + IP address* pair are allowed (`MAX_ATTEMPTS`).
- After that, the form refuses all attempts for **60 seconds** (`DECAY_SECONDS`) and shows: "Too many sign-in attempts. Please try again in N seconds."
- **A warning comes first.** When 2 or 1 attempts remain, the error adds "N attempt(s) remaining before this account is temporarily locked."
- **Why the key is username + IP.** If it used only the username, anyone could lock the real owner out by typing wrong passwords from anywhere. Including the IP means an attacker only locks themselves out.
- **A successful login clears the counter.**
- The counter is stored in the cache (`CACHE_DRIVER=file`).

The tests `test_repeated_failures_lock_the_account_out` and `test_the_user_is_warned_before_being_locked_out` cover this.

---

## 4. Session handling

- **Regeneration on login.** `session()->regenerate()` gives the user a fresh session ID once they are authenticated. This defeats *session fixation*, where an attacker plants a known session ID in a victim's browser before login and then reuses it afterwards.
- **Cookie settings** (`config/session.php`):
  - `http_only = true`, so JavaScript cannot read the cookie (this limits cookie theft through XSS).
  - `same_site = lax`, so other websites cannot make the browser send the cookie on cross-site form posts.
  - `secure = env('SESSION_SECURE_COOKIE', true)`, so the cookie is HTTPS-only by default. The local `.env` sets it to `false` so it works over `http://localhost`.
  - The session driver is `file`, with a 120-minute lifetime that renews on activity.
- **What `auth_pw` is for.** `passwordFingerprint()` takes the SHA-256 of the stored password hash and keeps the first 32 characters. It is saved in the session at login. On each request, `AuthMiddleware` recomputes it from the current database row and compares the two with `hash_equals`. If an administrator or the user changes the password, the database hash changes, so the fingerprint changes. Every *other* browser still holding the old fingerprint is signed out with "Your password was changed. Please sign in again."
  - The browser that made the change updates its own `auth_pw` straight away (`SettingsForm::savePassword`, and `UserForm::save` when an admin resets their own password), so it stays signed in.
  - This needs no extra database column. The fingerprint is derived from the hash, not the password, so the session never holds anything that could be reversed into the password.
  - A session that has no `auth_pw` key (created before this feature existed) is given one rather than being logged out (`test_a_session_predating_the_check_is_adopted_not_dropped`).
- **Re-checking the account every request.** `AuthMiddleware` calls `authUser(true)`, which reloads the user and their role from the database. If the account was deleted or is no longer `active`, the session is wiped and the user sees "Your account is no longer active." If the role changed, `auth_role` is updated in place, so a promotion or demotion applies without logging in again.

---

## 5. Logout, and how a password change ends other sessions

**Logout.** The sidebar button submits a form (`POST /logout`, with a CSRF token). `AuthController::logout` calls `session()->flush()` (removes every stored value) and then `session()->regenerate()` (new ID, so the old ID is dead), and redirects to `/login`.

**Password change.** As described in section 4: the new hash gives a new fingerprint, and the next request from any other session of that account fails the `hash_equals` check and is signed out. `test_changing_a_password_ends_other_sessions` covers this.

---

## 6. Roles and authorization

Authentication asks "who are you?". Authorization asks "what may you do?". MENRO answers it in three layers:

1. **Signed in at all?** `auth.custom` wraps every page except login and logout.
2. **Route-level role check.** The admin-only group (`/users`, `/audit`, `/archive` and archive downloads) has `role:System Administrator`. `RoleMiddleware` calls `canAccess()`, and anyone else gets **403 Access denied**. This keeps the rule in `routes/web.php` where it is easy to audit, instead of relying on each page to remember it.
3. **Re-checks inside each page.** Almost every Livewire component and controller repeats the check in `mount()` or in its action methods, with `isAdmin()` (administrator only) or `canAccess(...ROLES_STAFF())` (either role). For example, `Entries\EntryForm` lets non-admins edit only entries they encoded themselves (`encoded_by === auth_user_id`).

Why the third layer exists: the Livewire 2 library sends button clicks and form submits to its own endpoint (`/livewire/message/...`), not to the original route. Livewire only re-runs middleware on a short fixed list (its `$persistentMiddleware`), and `auth.custom` and `role` are not on that list. So for those follow-up requests only the in-component checks protect the action. This is the reason the layers are duplicated, and it is also the source of weakness W3 below.

**Role matrix as coded:** Administrator is allowed everywhere. MENRO Staff is allowed everywhere except user management (`/users`), the audit trail (`/audit`) and the archive (`/archive`), which are the only pages the two roles differ on. Every module route carries the same guard, `role:System Administrator,MENRO Staff`, which `ROLES_STAFF()` in `app/Helpers/AuthHelper.php` mirrors for the in-component checks.

One restriction is *not* role-based and survived the consolidation: a staff user may edit or delete only the waste entries they encoded themselves (`encoded_by === auth_user_id`), while an administrator may correct any entry. That is now the only fine-grained rule in the app.

---

## 7. Sequence diagrams

### Login

```
Browser                    Laravel                          DB / Cache
   |  GET /                    |                                 |
   |-------------------------->| session has auth_user_id? no    |
   |<-- 302 /login ------------|                                 |
   |  GET /login               |                                 |
   |<-- form + CSRF token -----|                                 |
   |  POST /login (user, pw, token)                              |
   |-------------------------->| verify CSRF token               |
   |                           | validate required fields        |
   |                           | RateLimiter: >= 5 fails? -------> cache
   |<-- "try again in N s" ----|   yes: stop here                |
   |                           | SELECT user WHERE username=?    |
   |                           |   AND status='active' ----------> users
   |                           | password_verify(pw, hash)       |
   |<-- "Invalid username or password" (+ hit counter) ----------|   no match
   |                           | match: clear counter -----------> cache
   |                           | session()->regenerate()         |
   |                           | store auth_user_id/role/auth_pw |
   |<-- 302 /dashboard --------|                                 |
   |  GET /dashboard           |                                 |
   |-------------------------->| AuthMiddleware:                 |
   |                           |  reload user + role ------------> users
   |                           |  active? fingerprint matches?   |
   |                           |  role route check (if any)      |
   |<-- 200 dashboard ---------|                                 |
```

### Password change ends the other session

```
Browser A (changes pw)        Server                    Browser B (old session)
   | save new password ------->| update password_hash          |
   |                           | A's auth_pw := new fingerprint|
   |<-- stays signed in -------|                               |
   |                           |        GET /any-page          |
   |                           |<------------------------------|
   |                           | stored hash -> fingerprint    |
   |                           | != B's auth_pw -> flush session
   |                           |-- 302 /login "password changed" ->|
```

---

## 8. Weaknesses, gaps and edge cases (not fixed yet)

Severity is my own judgement. Items marked **verify** are what I expect from reading the code and still need to be confirmed against the running app in Phase 2.

| # | Severity | Finding |
|---|---|---|
| W1 | **High** | **Default password in the seeders.** `UserSeeder` and `FreshDemoSeeder` create every account with `admin123` and the usernames `admin`, `barangay` and similar. If the seeder was run in production, those accounts are guessable. Check the live users table. |
| W2 | **High** | **The rate limit can be dodged if the IP can be faked.** `TrustProxies` is set to `$proxies = '*'`, so the app trusts `X-Forwarded-For` from any client. If the server is reachable directly (not only through the host's proxy), an attacker can send a different fake IP with each request and never reach the 5-attempt limit. Trusting `*` is only safe when the proxy is the single way in. **verify** against the deployment (Render / Hostinger). |
| W3 | **Medium** | **`auth.custom` and `role` do not run on Livewire action requests.** After a page has loaded, an administrator who deactivates a user, or a changed password, does not stop that user's already-open page from calling Livewire actions such as `delete`, until they load a new page. Some actions (for example `UserIndex::delete`) have no `isAdmin()` check of their own and rely on the page's `mount()`. A user who was never allowed to open the page cannot easily obtain a valid component payload, so this mainly affects *demoted or deactivated* users with a page already open. **verify**. A fix would be to register `AuthMiddleware` and `RoleMiddleware` with `Livewire::addPersistentMiddleware()`. |
| W4 | **Medium** | **No limit on spreading guesses across many usernames.** The key is username + IP, so one IP can try 5 passwords against each of many usernames per minute. There is no per-IP cap. |
| W5 | **Medium** | **Violation-ticket receipt has no role or ownership check.** `ViolationTicketController::receipt` (new, uncommitted) only requires being signed in. Any role, including Barangay User and Report Viewer, can open any ticket's receipt by guessing its numeric ID, while the ticket pages themselves are limited to Administrator/Officer/Inspector. It also uses `findOrFail`, so the response shows which IDs exist. |
| W6 | **Medium** | **Timing difference reveals which usernames exist.** For an unknown or inactive username, `password_verify` is never run, so the response is noticeably faster than for a real account with a wrong password. The error text hides this, but response time does not. A common fix is to verify against a dummy hash when no user is found. |
| W7 | **Medium** | **An administrator can lock everyone out.** `UserForm` lets the signed-in administrator set their own status to inactive or change their own role. `UserIndex::delete` blocks deleting yourself, but nothing stops deleting or demoting the *last* administrator. Also, `UserIndex::delete` compares `$id === session('auth_user_id')` with strict `===`. If Livewire passes the ID as a string and the session holds an integer, the "cannot delete yourself" guard never triggers. **verify**. |
| W8 | **Low–Medium** | **Sessions have no absolute lifetime.** The 120-minute lifetime renews on every request, so a session that is used regularly never expires, and there is no "sign out everywhere" button. There is an idle timeout, but no maximum session age. |
| W9 | **Low–Medium** | **Throttle state is in the `file` cache.** This is fine for a single server. With several servers or containers, each one has its own counter, so the effective limit is multiplied. Sessions are in files for the same reason. |
| W10 | **Low** | **Weak password policy.** The only rule is 8 characters minimum (administrator-set passwords too, **verify** `UserForm`). There is no complexity check and no check against common passwords. |
| W11 | **Low** | **Logout leaves no record.** `logAudit` is not called on login, failed login or logout, so the audit trail cannot show who signed in, when, or from where, and cannot show an attack in progress. The `logAudit` helper also swallows every exception silently. |
| W12 | **Low** | **No lockout notice to the account owner**, and the lockout is the same 60 seconds each time. It does not grow after repeated lockouts. |
| W13 | **Low** | **`APP_DEBUG=true` in the local `.env`.** This is fine locally but must be `false` in production, or errors expose paths and queries. Production settings were not checked here and I will not touch `.env`. |
| W14 | **Info** | **Dead and duplicate pieces.** `app/Http/Middleware/Authenticate.php` and `RedirectIfAuthenticated.php` are Laravel defaults that this app does not use (`RedirectIfAuthenticated` refers to `RouteServiceProvider::HOME` and Laravel's guard system, which MENRO never uses). The login view and `/login` check the session directly instead of using the `guest` middleware. |
| W15 | **Info** | **Not a weakness:** logout is a `POST` and is CSRF-protected (`VerifyCsrfToken::$except` is empty), session ID regeneration happens on both login and logout, and inactive users are cut off on their next request (`test_deactivating_a_user_ends_their_session`). |

Not found: GET-based logout, a missing CSRF token on the login form, or `{!! !!}` in the login view. The full `{!! !!}` sweep across every view belongs to Phase 5.

---

## 9. Evidence and limits of this review

- This is a **read-only code review**. I did not run the app, the tests or the browser in this phase, so every statement above comes from reading the source. The test file `AuthSecurityTest.php` documents the intended behaviour but I have not run it yet.
- The working tree has uncommitted changes to `login.blade.php`, `layouts/app.blade.php`, the violation-ticket views and `routes/web.php`, plus a new `ViolationTicketController`. I read them, and they are covered in Phase 2.
- I confirmed the Livewire persistent-middleware list in `vendor/livewire/livewire/src/LivewireManager.php`, and that `AuthMiddleware` and `RoleMiddleware` are not on it.
- The login page's countdown timer, caps-lock hint and show/hide password toggle are not part of this explanation. They belong to Phase 4.

---

## 10. Status after the QA pass (see QA_REPORT.md)

| Weakness | Status |
|---|---|
| W3 session checks skipped on Livewire requests | **Fixed.** Livewire's message route now runs `AuthMiddleware` (419 on a dead session). |
| W5 ticket receipt open to every role | **Fixed.** Limited to Administrator, MENRO Officer and Field Inspector. |
| Lockout wording (5th attempt) | **Fixed**, and the login page now shows a live countdown. |
| W1 default `admin123` passwords | **Not changed.** Check the production users table. |
| W2 `TrustProxies` trusts `*` | **Not changed.** Needs the real proxy address. |
| W4, W6 to W13 | **Not changed.** Listed as follow-ups. |
