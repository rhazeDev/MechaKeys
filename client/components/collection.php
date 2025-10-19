<?php
$collections = [
    ['icon' => '60%', 'name' => '60% Keyboards', 'desc' => 'Compact'],
    ['icon' => '65%', 'name' => '65% Keyboards', 'desc' => 'Balanced'],
    ['icon' => '75%', 'name' => '75% Keyboards', 'desc' => 'Popular'],
    ['icon' => '80%', 'name' => '80% TKL', 'desc' => 'Spacious'],
    ['icon' => '96%', 'name' => '96% Keyboards', 'desc' => 'Full Size'],
    ['icon' => '100%', 'name' => '104% Keyboards', 'desc' => 'Ultimate'],
];
?>
<section class="section">
    <h2 class="section-title">
        Keyboard Collections
    </h2>
    <div class="collection-grid">
        <?php foreach ($collections as $collection): ?>
            <div class="collection-card">
                <div class="collection-icon"><?php echo $collection['icon']; ?></div>
                <h3><?php echo $collection['name']; ?></h3>
                <p><?php echo $collection['desc']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
