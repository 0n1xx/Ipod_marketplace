-- Run once on an existing database that was created before user_cart existed.
CREATE TABLE IF NOT EXISTS user_cart (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products_description(product_id) ON DELETE CASCADE
);
