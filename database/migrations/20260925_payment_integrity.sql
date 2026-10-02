-- Sta. Monica Parish Connect
-- Payment integrity hardening
-- Date: 2026-09-25
--
-- Safe for an existing database. The payable uniqueness constraint is added
-- only when there are no duplicate payment rows for the same payable.

SET @schema_name := DATABASE();

SET @has_receipt_index := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @schema_name
    AND table_name = 'payments'
    AND index_name = 'uq_payment_receipt_no'
);

SET @sql := IF(
  @has_receipt_index = 0,
  'ALTER TABLE payments ADD UNIQUE KEY uq_payment_receipt_no (receipt_no)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_payable_index := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @schema_name
    AND table_name = 'payments'
    AND index_name = 'uq_payment_payable'
);

SET @duplicate_payables := (
  SELECT COUNT(*)
  FROM (
    SELECT payable_type, payable_id
    FROM payments
    GROUP BY payable_type, payable_id
    HAVING COUNT(*) > 1
  ) duplicate_groups
);

SET @sql := IF(
  @has_payable_index = 0 AND @duplicate_payables = 0,
  'ALTER TABLE payments ADD UNIQUE KEY uq_payment_payable (payable_type, payable_id)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
