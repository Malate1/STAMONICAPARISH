-- Revert the removed password-reset queue / Resend workflow if the earlier
-- experimental migration had already been applied to an existing database.
-- Safe to run on MySQL/MariaDB even when the table/column is already absent.

DROP TABLE IF EXISTS password_reset_requests;

SET @schema_name = DATABASE();
SET @has_must_change_password = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @schema_name
    AND table_name = 'users'
    AND column_name = 'must_change_password'
);
SET @drop_must_change_password = IF(
  @has_must_change_password > 0,
  'ALTER TABLE users DROP COLUMN must_change_password',
  'SELECT 1'
);
PREPARE stmt FROM @drop_must_change_password;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DELETE FROM system_settings
WHERE setting_key IN ('resend_from_name', 'resend_from_email');
