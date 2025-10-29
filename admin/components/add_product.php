<!-- Add Product Section -->
<section id="add-product" class="section">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Add New Product</h2>
        </div>
        
        <div id="add-product-message"></div>

        <form id="addProductForm" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">
                        Category <span class="required">*</span>
                    </label>
                    <select name="category" id="categorySelect" class="form-select" required>
                        <option value="">Select category</option>
                        <option value="keyboard">Keyboard</option>
                        <option value="keycaps">Keycaps</option>
                        <option value="switches">Switches</option>
                        <option value="accessories">Accessories</option>
                    </select>
                </div>

                <div class="form-group" id="brandFieldGroup">
                    <label class="form-label">
                        Brand <span class="required">*</span>
                    </label>
                    <select name="brand" id="brandSelect" class="form-select" required>
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
                    <label class="form-label">
                        Model Name <span class="required">*</span>
                    </label>
                    <input type="text" name="model" class="form-input" placeholder="Enter model name (e.g., F75, K2 Pro)" required>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">
                        Description <span class="required">*</span>
                    </label>
                    <textarea name="description" class="form-textarea" placeholder="Enter product description" required></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">
                        Product Images <span class="required">*</span>
                    </label>
                    <div class="form-file">
                        <input type="file" name="images[]" id="productImages" multiple accept="image/*" required>
                        <label for="productImages" class="file-label">
                            <i class="fas fa-cloud-upload-alt file-icon"></i>
                            <span class="file-text">Click to upload or drag and drop</span>
                            <span class="file-hint">PNG, JPG up to 10MB (Multiple images allowed)</span>
                        </label>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </div>
            </div>

            <h3 class="mt-3 mb-2">Product Variations</h3>
            <div class="variations-container" id="variationsContainer">
                <div class="variation-item">
                    <div class="form-group keyboard-only-field">
                        <label class="form-label">Layout <span class="required">*</span></label>
                        <select name="variations[0][layout]" class="form-select layout-select" data-index="0" required>
                            <option value="">Select layout</option>
                            <option value="100">100% (Full Size)</option>
                            <option value="96">96%</option>
                            <option value="80">80% (TKL)</option>
                            <option value="75">75%</option>
                            <option value="65">65%</option>
                            <option value="60">60%</option>
                            <option value="40">40%</option>
                            <option value="custom">Custom (Type below)</option>
                        </select>
                    </div>
                    <div class="form-group custom-layout-group keyboard-only-field" id="customLayout0" style="display: none;">
                        <label class="form-label">Custom Layout</label>
                        <input type="text" name="variations[0][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%">
                    </div>
                    <div class="form-group keyboard-only-field">
                        <label class="form-label">Switch Type</label>
                        <input type="text" name="variations[0][switch]" class="form-input" placeholder="e.g., Reaper SW, Cherry MX Red" required>
                    </div>
                    <div class="form-group keyboard-only-field">
                        <label class="form-label">Color</label>
                        <input type="text" name="variations[0][color]" class="form-input" placeholder="e.g., Blue, Black" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" name="variations[0][price]" class="form-input" placeholder="0.00" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="variations[0][stock]" class="form-input" placeholder="0" min="0" required>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-add keyboard-only-field" onclick="addVariation()">
                <i class="fas fa-plus"></i>
                Add Variation
            </button>

            <div class="modal-footer mt-3">
                <button type="button" class="btn btn-secondary" onclick="showSection('dashboard')">
                    Cancel
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Save Product
                </button>
            </div>
        </form>
    </div>
</section>
