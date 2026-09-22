-- =============================================================
-- Reset ALL loan data to start fresh.
-- IRREVERSIBLE once run — back up first (see step 0 below).
--
-- Clears: loan_payments, loan_appointments, loans, loan_requests,
-- loan_batches, and any loan-related notifications. CBU data,
-- farmers, and users are NOT touched.
--
-- New loan requests will start again at LN-001 (TRUNCATE resets
-- the auto-increment counter).
-- =============================================================

-- STEP 0 (do this first, outside this script):
--   phpMyAdmin: open your database > tick these 5 tables >
--   "Export" > Go, and save the .sql file somewhere safe.
--   (Or via SSH: mysqldump -u USER -p DBNAME loan_payments
--    loan_appointments loans loan_requests loan_batches > backup.sql)

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE loan_payments;
TRUNCATE TABLE loan_appointments;
TRUNCATE TABLE loans;
TRUNCATE TABLE loan_requests;
TRUNCATE TABLE loan_batches;

-- Remove loan-related alerts so farmers/managers don't see stale
-- notifications pointing at deleted loans.
DELETE FROM notifications WHERE type LIKE 'loan\_%' OR loan_id IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- FALLBACK — only use this block instead of the one above if you
-- get an error running it (e.g. "TRUNCATE command denied" because
-- your DB user lacks DROP privilege, or "Unknown column 'loan_id'"
-- because that migration hasn't run on this database yet).
-- =============================================================
-- SET FOREIGN_KEY_CHECKS = 0;
--
-- DELETE FROM loan_payments;
-- DELETE FROM loan_appointments;
-- DELETE FROM loans;
-- DELETE FROM loan_requests;
-- DELETE FROM loan_batches;
-- DELETE FROM notifications WHERE type LIKE 'loan\_%';
--
-- ALTER TABLE loan_payments AUTO_INCREMENT = 1;
-- ALTER TABLE loan_appointments AUTO_INCREMENT = 1;
-- ALTER TABLE loans AUTO_INCREMENT = 1;
-- ALTER TABLE loan_requests AUTO_INCREMENT = 1;
-- ALTER TABLE loan_batches AUTO_INCREMENT = 1;
--
-- SET FOREIGN_KEY_CHECKS = 1;
