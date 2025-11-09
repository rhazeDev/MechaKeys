<?php
$brands = [
    ['name' => 'Keychron', 'image' => 'keychron.png'],
    ['name' => 'Logitech', 'image' => 'logitech.png'],
    ['name' => 'Aula', 'image' => 'aula.jpg'],
    ['name' => 'Monsgeek', 'image' => 'monsgeek.jpg'],
    ['name' => 'Rakk', 'image' => 'rakk.jpg'],
    ['name' => 'Royal Kludge', 'image' => 'royal-kludge.jpg'],
];
?>

<section class="section">
    <h2 class="section-title">
        Featured Brands
    </h2>
    <div class="brands-row">
        <?php foreach ($brands as $brand): ?>
            <a href="index.php?category=keyboard&brand=<?php echo urlencode($brand['name']); ?>" class="brand-card" title="<?php echo htmlspecialchars($brand['name']); ?>">
                <img src="images/brands/<?php echo $brand['image']; ?>" alt="<?php echo $brand['name']; ?>" class="brand-logo">
            </a>
        <?php endforeach; ?>
    </div>
</section>
