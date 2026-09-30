# Running the tests

The suite runs against its own Postgres database, `menro_test`, so a test run
can never read or write development data. `phpunit.xml` points at it.

## One-time setup

```sh
psql -h 127.0.0.1 -p 6767 -U postgres -c "CREATE DATABASE menro_test"
DB_DATABASE=menro_test php artisan migrate --seed
```

On Windows, `psql` lives in `C:\Program Files\PostgreSQL\16\bin`.

Note the `DB_DATABASE=menro_test` prefix. Do **not** use `--env=testing` here:
there is no `.env.testing` file, so Laravel falls back to `.env` and the command
would migrate and seed your *development* database instead. The `env` values in
`phpunit.xml` apply only while PHPUnit is running, not to artisan commands.

## Running

```sh
php artisan test
```

## Notes

- `FullFlowTest` uses `DatabaseTransactions`, not `RefreshDatabase`: it rolls
  each test back rather than rebuilding the schema, so the seed data must
  already be in `menro_test`. Tests skip themselves when expected seed rows are
  missing, so an unseeded database produces skips rather than failures.
- sqlite is not an option for this suite. The dashboard and analytics queries
  are raw Postgres SQL using `EXTRACT(... FROM ...)`, which sqlite cannot parse.
- After changing migrations, re-run the setup commands above against
  `menro_test` as well as your development database.
