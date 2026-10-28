-- Extra catalog units (idempotent: skips names that already exist).
-- Run against the live DB after deploying the new files under /images/.

INSERT INTO products_description (name, description, price, imagePath, is_sold)
SELECT * FROM (
    SELECT
        'Silver iPod Classic' AS name,
        'A timeless silver iPod Classic with a bright screen and click wheel. Fully restored battery and storage ready for everyday listening / Un iPod Classic argenté intemporel avec écran clair et molette. Batterie et stockage restaurés pour une écoute quotidienne.' AS description,
        99.99 AS price,
        '/images/silver-classic.jpg' AS imagePath,
        0 AS is_sold
    UNION ALL SELECT
        'Black iPod Classic',
        'Matte black iPod Classic for a sleek look. Inspected, restored, and ready to play / Un iPod Classic noir mat au look élégant. Inspecté, restauré et prêt à jouer.',
        104.99, '/images/black-classic.jpg', 0
    UNION ALL SELECT
        'Pink iPod Classic',
        'Soft pink iPod Classic with a clean white click wheel. Battery replaced and software refreshed / Un iPod Classic rose doux avec molette blanche. Batterie remplacée et logiciel actualisé.',
        96.99, '/images/pink-classic.jpg', 0
    UNION ALL SELECT
        'Yellow iPod Classic',
        'Bright yellow iPod Classic that stands out in any bag. Restored for reliable everyday use / Un iPod Classic jaune vif qui se démarque. Restauré pour un usage quotidien fiable.',
        91.99, '/images/yellow-classic.jpg', 0
    UNION ALL SELECT
        'White iPod Classic',
        'Classic white finish with a gray click wheel. A clean collector-friendly restoration / Finition blanche classique avec molette grise. Restauration soignée pour collectionneurs.',
        97.99, '/images/white-classic.jpg', 0
    UNION ALL SELECT
        'Gold iPod Classic',
        'Warm gold iPod Classic with premium feel. Tested storage, fresh battery, polished shell / Un iPod Classic doré au toucher premium. Stockage testé, batterie neuve, coque polie.',
        109.99, '/images/gold-classic.jpg', 0
    UNION ALL SELECT
        'Orange iPod Classic',
        'Vibrant orange iPod Classic restored in Québec. Ready for playlists on the go / Un iPod Classic orange vif restauré au Québec. Prêt pour vos playlists nomades.',
        93.99, '/images/orange-classic.jpg', 0
    UNION ALL SELECT
        'Space Gray iPod Classic',
        'Space gray iPod Classic with a modern quiet look. Fully checked and tuned / Un iPod Classic gris sidéral au look discret. Entièrement vérifié et réglé.',
        101.99, '/images/spacegray-classic.jpg', 0
    UNION ALL SELECT
        'Rose Gold iPod Classic',
        'Rose gold iPod Classic with soft metallic glow. Battery and click wheel refreshed / Un iPod Classic or rose au reflet doux. Batterie et molette rafraîchies.',
        112.99, '/images/rosegold-classic.jpg', 0
    UNION ALL SELECT
        'Teal iPod Mini',
        'Compact teal iPod Mini — light, colorful, and restored for daily commute playlists / Un iPod Mini sarcelle compact — léger, coloré et restauré pour le quotidien.',
        74.99, '/images/teal-mini.jpg', 0
    UNION ALL SELECT
        'Lavender iPod Mini',
        'Lavender iPod Mini with silver wheel. Small form factor, carefully restored internals / Un iPod Mini lavande avec molette argentée. Format compact, intérieur soigneusement restauré.',
        76.99, '/images/lavender-mini.jpg', 0
    UNION ALL SELECT
        'Silver iPod Shuffle',
        'Clip-on silver iPod Shuffle for workouts and travel. Tested and restored / Un iPod Shuffle argenté à clipser pour le sport et les voyages. Testé et restauré.',
        49.99, '/images/silver-shuffle.jpg', 0
) AS seed
WHERE NOT EXISTS (
    SELECT 1 FROM products_description p WHERE p.name = seed.name
);
