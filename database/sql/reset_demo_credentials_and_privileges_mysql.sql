-- =============================================================================
-- BossSchool — Reset demo passwords + roles + portal pivots (MySQL / MariaDB)
-- =============================================================================
-- Prefer the Artisan command (works on SQLite too, uses Laravel hashing):
--   php artisan demo:reset-credentials --force
--   composer demo-credentials
--
-- This file is optional for DBAs who want raw SQL only.
-- =============================================================================
-- Sets every listed account to password:  password
-- Re-aligns role, tenant_id, student_id, and teacher/parent pivot rows for demo.
--
-- BEFORE YOU RUN
-- 1) You must already have tenant `demo` and seeded academic data (students, classes,
--    subjects). If users are missing entirely, run first:
--       php artisan migrate --force
--       php artisan db:seed --force
-- 2) Regenerate @PWD below so it matches your app (recommended):
--       php artisan tinker --execute="echo \\Illuminate\\Support\\Facades\\Hash::make('password');"
--    Paste the single-line hash into @PWD (keep the quotes).
--
-- RUN (example):
--    mysql -u USER -p DB_NAME < database/sql/reset_demo_credentials_and_privileges_mysql.sql
-- =============================================================================

START TRANSACTION;

SET @PWD = '$2y$12$WCMwTs49QjsdiUwN9XQvNOSfk2oTRIdE7LlYrVFMeIkeIMqJderK2';
SET @DEMO := (SELECT id FROM tenants WHERE subdomain = 'demo' LIMIT 1);

-- If @DEMO is NULL, stop: run `php artisan db:seed` (or TenantSeeder) first.
SELECT @DEMO AS demo_tenant_id;

-- ---------------------------------------------------------------------------
-- users: password + verified + role + tenant + student_id
-- ---------------------------------------------------------------------------
UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'SuperAdmin',
    tenant_id = NULL,
    student_id = NULL
WHERE email = 'superadmin@bossschool.com';

UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'Proprietor',
    tenant_id = @DEMO,
    student_id = NULL
WHERE email = 'proprietor@demo.com' AND tenant_id = @DEMO;

UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'Admin',
    tenant_id = @DEMO,
    student_id = NULL
WHERE email = 'admin@demo.com' AND tenant_id = @DEMO;

UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'Accountant',
    tenant_id = @DEMO,
    student_id = NULL
WHERE email = 'accountant@demo.com' AND tenant_id = @DEMO;

UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'Teacher',
    tenant_id = @DEMO,
    student_id = NULL
WHERE email = 'teacher@demo.com' AND tenant_id = @DEMO;

SET @STUDENT_ROW := (
    SELECT id FROM students
    WHERE tenant_id = @DEMO AND status = 'active'
    ORDER BY id ASC
    LIMIT 1
);

UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'Parent',
    tenant_id = @DEMO,
    student_id = NULL
WHERE email = 'parent@demo.com' AND tenant_id = @DEMO;

UPDATE users
SET
    password = @PWD,
    email_verified_at = COALESCE(email_verified_at, NOW()),
    role = 'Student',
    tenant_id = @DEMO,
    student_id = @STUDENT_ROW
WHERE email = 'student@demo.com' AND tenant_id = @DEMO;

-- ---------------------------------------------------------------------------
-- Parent ↔ children (same logic as RolePortalSeeder)
-- ---------------------------------------------------------------------------
SET @PARENT_UID := (SELECT id FROM users WHERE email = 'parent@demo.com' AND tenant_id = @DEMO LIMIT 1);
SET @GUARDIAN := (
    SELECT parent_name FROM students
    WHERE tenant_id = @DEMO AND status = 'active'
    ORDER BY id ASC
    LIMIT 1
);

UPDATE users SET name = @GUARDIAN WHERE id = @PARENT_UID;

UPDATE users u
INNER JOIN students s ON s.id = @STUDENT_ROW
SET u.name = s.name
WHERE u.email = 'student@demo.com' AND u.tenant_id = @DEMO;

DELETE FROM parent_student WHERE user_id = @PARENT_UID;

INSERT INTO parent_student (user_id, student_id, tenant_id, created_at, updated_at)
SELECT @PARENT_UID, s.id, @DEMO, NOW(), NOW()
FROM students s
WHERE s.tenant_id = @DEMO
  AND s.status = 'active'
  AND s.parent_name = @GUARDIAN;

-- ---------------------------------------------------------------------------
-- Teacher ↔ classes + subjects (first two classes, up to six subjects)
-- ---------------------------------------------------------------------------
SET @TEACHER_UID := (SELECT id FROM users WHERE email = 'teacher@demo.com' AND tenant_id = @DEMO LIMIT 1);

DELETE FROM teacher_class WHERE user_id = @TEACHER_UID AND tenant_id = @DEMO;
DELETE FROM teacher_subject WHERE user_id = @TEACHER_UID AND tenant_id = @DEMO;

INSERT INTO teacher_class (user_id, class_id, tenant_id, created_at, updated_at)
SELECT @TEACHER_UID, c.id, @DEMO, NOW(), NOW()
FROM classes c
WHERE c.tenant_id = @DEMO
ORDER BY c.id ASC
LIMIT 2;

INSERT INTO teacher_subject (user_id, subject_id, tenant_id, created_at, updated_at)
SELECT @TEACHER_UID, s.id, @DEMO, NOW(), NOW()
FROM subjects s
INNER JOIN teacher_class tc
    ON tc.class_id = s.class_id
    AND tc.user_id = @TEACHER_UID
    AND tc.tenant_id = @DEMO
ORDER BY s.id ASC
LIMIT 6;

COMMIT;

-- =============================================================================
-- Quick verify (optional — run in same session before COMMIT if you prefer)
-- =============================================================================
-- SELECT email, role, tenant_id, student_id FROM users
-- WHERE email LIKE '%@demo.com' OR email = 'superadmin@bossschool.com'
-- ORDER BY email;
