<!-- Inventory Section -->
<section id="inventory" class="section">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Inventory Management</h2>
            <div class="panel-actions">
                <div class="search-bar" style="flex: 1; max-width: 300px; margin-right: 1rem;">
                    <input 
                        type="text" 
                        id="inventorySearch" 
                        class="search-input" 
                        placeholder="Search by product name, brand, or SKU..." 
                        onkeyup="filterInventory()">
                    <i class="fas fa-search"></i>
                </div>
                <button class="btn btn-secondary" onclick="loadInventory()">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div id="inventory-list">
            <!-- Inventory will be loaded here -->
        </div>
    </div>
</section>
