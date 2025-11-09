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
    <div class="collection-row" role="list">
        <?php foreach ($collections as $collection): ?>
            <?php
            $layoutParam = preg_replace('/[^0-9]/', '', $collection['icon']);
            ?>
            <a href="index.php?category=keyboard&layout=<?php echo urlencode($layoutParam); ?>" class="collection-card"
                role="listitem">
                <div class="collection-icon"><?php echo $collection['icon']; ?></div>
                <h3><?php echo $collection['name']; ?></h3>
                <p><?php echo $collection['desc']; ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>