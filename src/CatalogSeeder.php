<?php
// Idempotent catalog helpers used after deploy.
// - ensureExtraProducts(): INSERT only missing names (never touches is_sold)
// - resyncSoldFromOrders(): mark any product that appears in order_items as sold
class CatalogSeeder {
    public static function ensureExtraProducts(PDO $pdo): int {
        $products = self::products();
        $insert = $pdo->prepare(
            'INSERT INTO products_description (name, description, price, imagePath, is_sold)
             VALUES (:name, :description, :price, :imagePath, 0)'
        );
        $exists = $pdo->prepare(
            'SELECT product_id FROM products_description WHERE name = :name LIMIT 1'
        );

        $added = 0;
        foreach ($products as $product) {
            $exists->execute([':name' => $product['name']]);
            if ($exists->fetchColumn()) {
                continue;
            }
            $insert->execute([
                ':name'        => $product['name'],
                ':description' => $product['description'],
                ':price'       => $product['price'],
                ':imagePath'   => $product['imagePath'],
            ]);
            $added++;
        }

        return $added;
    }

    // Paid checkouts write order_items and set is_sold=1. If a later seed
    // accidentally cleared that flag, rebuild it from the orders ledger.
    public static function resyncSoldFromOrders(PDO $pdo): int {
        try {
            return (int) $pdo->exec(
                'UPDATE products_description p
                 SET is_sold = 1
                 WHERE p.is_sold = 0
                   AND EXISTS (
                       SELECT 1 FROM order_items oi WHERE oi.product_id = p.product_id
                   )'
            );
        } catch (Throwable $e) {
            error_log('CatalogSeeder::resyncSoldFromOrders failed: ' . $e->getMessage());
            return 0;
        }
    }

    private static function products(): array {
        return [
            [
                'name' => 'Silver iPod Classic',
                'description' => 'A timeless silver iPod Classic with a bright screen and click wheel. Fully restored battery and storage ready for everyday listening / Un iPod Classic argenté intemporel avec écran clair et molette. Batterie et stockage restaurés pour une écoute quotidienne.',
                'price' => 99.99,
                'imagePath' => '/images/silver-classic.jpg',
            ],
            [
                'name' => 'Black iPod Classic',
                'description' => 'Matte black iPod Classic for a sleek look. Inspected, restored, and ready to play / Un iPod Classic noir mat au look élégant. Inspecté, restauré et prêt à jouer.',
                'price' => 104.99,
                'imagePath' => '/images/black-classic.jpg',
            ],
            [
                'name' => 'Pink iPod Classic',
                'description' => 'Soft pink iPod Classic with a clean white click wheel. Battery replaced and software refreshed / Un iPod Classic rose doux avec molette blanche. Batterie remplacée et logiciel actualisé.',
                'price' => 96.99,
                'imagePath' => '/images/pink-classic.jpg',
            ],
            [
                'name' => 'Yellow iPod Classic',
                'description' => 'Bright yellow iPod Classic that stands out in any bag. Restored for reliable everyday use / Un iPod Classic jaune vif qui se démarque. Restauré pour un usage quotidien fiable.',
                'price' => 91.99,
                'imagePath' => '/images/yellow-classic.jpg',
            ],
            [
                'name' => 'White iPod Classic',
                'description' => 'Classic white finish with a gray click wheel. A clean collector-friendly restoration / Finition blanche classique avec molette grise. Restauration soignée pour collectionneurs.',
                'price' => 97.99,
                'imagePath' => '/images/white-classic.jpg',
            ],
            [
                'name' => 'Gold iPod Classic',
                'description' => 'Warm gold iPod Classic with premium feel. Tested storage, fresh battery, polished shell / Un iPod Classic doré au toucher premium. Stockage testé, batterie neuve, coque polie.',
                'price' => 109.99,
                'imagePath' => '/images/gold-classic.jpg',
            ],
            [
                'name' => 'Orange iPod Classic',
                'description' => 'Vibrant orange iPod Classic restored in Québec. Ready for playlists on the go / Un iPod Classic orange vif restauré au Québec. Prêt pour vos playlists nomades.',
                'price' => 93.99,
                'imagePath' => '/images/orange-classic.jpg',
            ],
            [
                'name' => 'Space Gray iPod Classic',
                'description' => 'Space gray iPod Classic with a modern quiet look. Fully checked and tuned / Un iPod Classic gris sidéral au look discret. Entièrement vérifié et réglé.',
                'price' => 101.99,
                'imagePath' => '/images/spacegray-classic.jpg',
            ],
            [
                'name' => 'Rose Gold iPod Classic',
                'description' => 'Rose gold iPod Classic with soft metallic glow. Battery and click wheel refreshed / Un iPod Classic or rose au reflet doux. Batterie et molette rafraîchies.',
                'price' => 112.99,
                'imagePath' => '/images/rosegold-classic.jpg',
            ],
            [
                'name' => 'Teal iPod Mini',
                'description' => 'Compact teal iPod Mini — light, colorful, and restored for daily commute playlists / Un iPod Mini sarcelle compact — léger, coloré et restauré pour le quotidien.',
                'price' => 74.99,
                'imagePath' => '/images/teal-mini.jpg',
            ],
            [
                'name' => 'Lavender iPod Mini',
                'description' => 'Lavender iPod Mini with silver wheel. Small form factor, carefully restored internals / Un iPod Mini lavande avec molette argentée. Format compact, intérieur soigneusement restauré.',
                'price' => 76.99,
                'imagePath' => '/images/lavender-mini.jpg',
            ],
            [
                'name' => 'Silver iPod Shuffle',
                'description' => 'Clip-on silver iPod Shuffle for workouts and travel. Tested and restored / Un iPod Shuffle argenté à clipser pour le sport et les voyages. Testé et restauré.',
                'price' => 49.99,
                'imagePath' => '/images/silver-shuffle.jpg',
            ],
        ];
    }
}
