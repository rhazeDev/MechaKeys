<!-- Products Section -->
<section id="products" class="section">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">All Products</h2>
            <div class="panel-actions">
                <div class="search-bar" style="flex: 1; max-width: 300px; margin-right: 1rem;">
                    <input 
                        type="text" 
                        id="productsSearch" 
                        class="search-input" 
                        placeholder="Search by product name, brand, or model..." 
                        onkeyup="filterProducts()">
                    <i class="fas fa-search"></i>
                </div>
                <button class="btn btn-primary" onclick="showSection('add-product')">
                    <i class="fas fa-plus"></i>
                    Add New Product
                </button>
            </div>
        </div>
        <div id="products-list">
            <!-- Products will be loaded here -->
        </div>
    </div>
</section>
