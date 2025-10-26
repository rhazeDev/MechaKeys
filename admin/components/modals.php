<!-- Edit Stock Modal -->
<div id="editStockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Stock</h3>
            <button class="modal-close" onclick="closeModal('editStockModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="edit-stock-message"></div>
            <form id="editStockForm">
                <input type="hidden" name="variation_id" id="edit_variation_id">
                <div class="form-group">
                    <label class="form-label">Product</label>
                    <input type="text" id="edit_product_name" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Variation Details</label>
                    <input type="text" id="edit_variation_details" class="form-input" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">New Stock Quantity <span class="required">*</span></label>
                    <input type="number" name="stock" id="edit_new_stock" class="form-input" placeholder="Enter new quantity" min="0" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editStockModal')">Cancel</button>
            <button type="button" class="btn btn-success" onclick="updateStock()">
                <i class="fas fa-save"></i>
                Update Stock
            </button>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 class="modal-title">Edit Product</h3>
            <button class="modal-close" onclick="closeModal('editProductModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="edit-product-message"></div>
            <form id="editProductForm">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <h3 class="mb-2">Product Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Brand <span class="required">*</span></label>
                        <select name="brand" id="edit_brand" class="form-select" required>
                            <option value="">Select brand</option>
                            <option value="Aula">Aula</option>
                            <option value="Keychron">Keychron</option>
                            <option value="Logitech">Logitech</option>
                            <option value="Monsgeek">Monsgeek</option>
                            <option value="RAKK">RAKK</option>
                            <option value="Royal Kludge">Royal Kludge</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model Name <span class="required">*</span></label>
                        <input type="text" name="model" id="edit_model" class="form-input" placeholder="e.g., F75, K2 Pro" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category <span class="required">*</span></label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="">Select category</option>
                            <option value="keyboard">Keyboard</option>
                            <option value="keycap">Keycaps</option>
                            <option value="switches">Switches</option>
                            <option value="accessories">Accessories</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Description <span class="required">*</span></label>
                    <textarea name="description" id="edit_description" class="form-input" rows="4" placeholder="Enter product description" required></textarea>
                </div>

                <h3 class="mt-3 mb-2">Product Variations</h3>
                <div id="editVariationsContainer">
                    <!-- Variations will be loaded here -->
                </div>
                <button type="button" class="btn-add" onclick="addEditVariation()">
                    <i class="fas fa-plus"></i>
                    Add Variation
                </button>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">Cancel</button>
            <button type="button" class="btn btn-success" onclick="saveProductEdit()">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
        </div>
    </div>
</div>
