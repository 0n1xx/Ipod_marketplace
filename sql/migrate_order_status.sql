-- Normalize order status values used by account + admin.
-- Flow: paid → shipped → delivered
UPDATE orders
SET status = 'paid'
WHERE status IS NULL
   OR status = ''
   OR status = 'pending'
   OR status = 'complete'
   OR status = 'completed';

ALTER TABLE orders
    MODIFY status VARCHAR(20) NOT NULL DEFAULT 'paid';
