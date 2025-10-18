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
    <div class="brands-grid">
        <?php foreach ($brands as $brand): ?>
            <div class="brand-card">
                <img src="images/brands/<?php echo $brand['image']; ?>" alt="<?php echo $brand['name']; ?>" class="brand-logo">
            </div>
        <?php endforeach; ?>
    </div>
</section>
