-- Shipping address captured from Stripe Checkout
ALTER TABLE orders
    ADD COLUMN shipping_name VARCHAR(150) NULL,
    ADD COLUMN shipping_line1 VARCHAR(255) NULL,
    ADD COLUMN shipping_line2 VARCHAR(255) NULL,
    ADD COLUMN shipping_city VARCHAR(100) NULL,
    ADD COLUMN shipping_state VARCHAR(100) NULL,
    ADD COLUMN shipping_postal VARCHAR(30) NULL,
    ADD COLUMN shipping_country VARCHAR(2) NULL;
