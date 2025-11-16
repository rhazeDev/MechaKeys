function showCustomConfirm(message, onOk) {
    const dialog = document.getElementById('customConfirmDialog');
    document.getElementById('confirmDialogMessage').innerHTML = message;
    dialog.style.display = 'flex';
    const okBtn = document.getElementById('confirmDialogOkBtn');
    function okHandler() {
        dialog.style.display = 'none';
        okBtn.removeEventListener('click', okHandler);
        onOk();
    }
    okBtn.addEventListener('click', okHandler);
}

function closeCustomConfirm() {
    document.getElementById('customConfirmDialog').style.display = 'none';
}

function closeModal(modalId) {
    if (!modalId) return;
    const modal = document.getElementById(modalId);
    if (!modal) return;

    try {
        if (modal.classList && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    } catch (e) {
    }

    try {
        modal.style.display = 'none';
    } catch (e) {
    }

    const editMsg = document.getElementById('edit-stock-message');
    if (editMsg) editMsg.innerHTML = '';
}
let variationCount = 1;

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function (e) {
        e.preventDefault();
        const section = this.dataset.section;
        showSection(section);

        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');
    });
});

function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });
    const targetSection = document.getElementById(sectionId);
    if (!targetSection) return;
    targetSection.classList.add('active');


    document.querySelectorAll('.nav-item').forEach(nav => {
        if (nav.dataset.section === sectionId) {
            nav.classList.add('active');
        } else {
            nav.classList.remove('active');
        }
    });

    if (sectionId === 'dashboard') {
        loadDashboard();
        loadDashboardCharts();
    } else if (sectionId === 'products') {
        loadProducts();
    } else if (sectionId === 'inventory') {
        loadInventory();
    } else if (sectionId === 'returns') {
        if (typeof loadReturns === 'function') loadReturns();
    } else if (sectionId === 'orders') {
        loadOrders();
    } else if (sectionId === 'delivery-riders') {
        loadDeliveryRiders();
    }

    const sectionEl = document.getElementById(sectionId);
    if (!sectionEl) return;
}

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('layout-select')) {
        const index = e.target.dataset.index;
        const customLayoutGroup = document.getElementById('customLayout' + index);
        const customLayoutInput = customLayoutGroup ? customLayoutGroup.querySelector('input') : null;

        if (e.target.value === 'custom') {
            if (customLayoutGroup) {
                customLayoutGroup.style.display = 'block';
                if (customLayoutInput) customLayoutInput.required = true;
            }
        } else {
            if (customLayoutGroup) {
                customLayoutGroup.style.display = 'none';
                if (customLayoutInput) {
                    customLayoutInput.required = false;
                    customLayoutInput.value = '';
                }
            }
        }
    }

    if (e.target.id === 'categorySelect') {
        const brandFieldGroup = document.getElementById('brandFieldGroup');
        const brandSelect = document.getElementById('brandSelect');
        const keyboardOnlyFields = document.querySelectorAll('.keyboard-only-field');

        if (e.target.value === 'keyboard') {
            brandFieldGroup.style.display = '';
            brandSelect.required = true;

            keyboardOnlyFields.forEach(field => {
                field.style.display = '';
                const inputs = field.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (!input.classList.contains('custom-layout-group')) {
                        input.required = true;
                    }
                });
            });
        } else if (e.target.value) {
            brandFieldGroup.style.display = 'none';
            brandSelect.required = false;
            brandSelect.value = '';

            keyboardOnlyFields.forEach(field => {
                field.style.display = 'none';
                const inputs = field.querySelectorAll('input, select');
                inputs.forEach(input => {
                    input.required = false;
                    if (input.tagName === 'SELECT') {
                        input.value = '';
                    } else if (input.type === 'text') {
                        input.value = '';
                    }
                });
            });
        } else {
            brandFieldGroup.style.display = '';
            brandSelect.required = true;

            keyboardOnlyFields.forEach(field => {
                field.style.display = '';
            });
        }
    }
});

const productImagesEl = document.getElementById('productImages');
if (productImagesEl) {
    productImagesEl.addEventListener('change', function (e) {
        const preview = document.getElementById('imagePreview');
        if (preview) preview.innerHTML = '';

        Array.from(this.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="preview-remove" onclick="removeImage(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                if (preview) preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
}

function removeImage(index) {
    const input = document.getElementById('productImages');
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    files.splice(index, 1);
    files.forEach(file => dt.items.add(file));
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}

function addVariation() {
    const container = document.getElementById('variationsContainer');
    const categorySelect = document.getElementById('categorySelect');
    const isKeyboard = categorySelect.value === 'keyboard';

    const div = document.createElement('div');
    div.className = 'variation-item';
    div.innerHTML = `
        <div class="form-group keyboard-only-field" ${!isKeyboard ? 'style="display: none;"' : ''}>
            <label class="form-label">Layout <span class="required">*</span></label>
            <select name="variations[${variationCount}][layout]" class="form-select layout-select" data-index="${variationCount}" ${isKeyboard ? 'required' : ''}>
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
        <div class="form-group custom-layout-group keyboard-only-field" id="customLayout${variationCount}" style="display: none;">
            <label class="form-label">Custom Layout</label>
            <input type="text" name="variations[${variationCount}][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%">
        </div>
        <div class="form-group keyboard-only-field" ${!isKeyboard ? 'style="display: none;"' : ''}>
            <label class="form-label">Switch Type</label>
            <input type="text" name="variations[${variationCount}][switch]" class="form-input" placeholder="e.g., Reaper SW, Cherry MX Red" ${isKeyboard ? 'required' : ''}>
        </div>
        <div class="form-group keyboard-only-field" ${!isKeyboard ? 'style="display: none;"' : ''}>
            <label class="form-label">Color</label>
            <input type="text" name="variations[${variationCount}][color]" class="form-input" placeholder="e.g., Blue, Black" ${isKeyboard ? 'required' : ''}>
        </div>
        <div class="form-group">
            <label class="form-label">Price (₱)</label>
            <input type="number" name="variations[${variationCount}][price]" class="form-input" placeholder="0.00" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="variations[${variationCount}][stock]" class="form-input" placeholder="0" min="0" required>
        </div>
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
    variationCount++;
}

const addProductFormEl = document.getElementById('addProductForm');
if (addProductFormEl) {
    addProductFormEl.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        const layoutSelects = document.querySelectorAll('.layout-select');
        layoutSelects.forEach((select, index) => {
            if (select.value === 'custom') {
                const customLayoutInput = document.querySelector(`input[name="variations[${index}][custom_layout]"]`);
                if (customLayoutInput && customLayoutInput.value) {
                    formData.set(`variations[${index}][layout]`, customLayoutInput.value);
                }
            }
        });

        const messageDiv = document.getElementById('add-product-message');

        try {
            const response = await fetch('api/add_product.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                messageDiv.innerHTML = `
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    ${result.message}
                </div>
            `;
                this.reset();
                document.getElementById('imagePreview').innerHTML = '';
                variationCount = 1;

                const container = document.getElementById('variationsContainer');
                container.innerHTML = `
                <div class="variation-item">
                    <div class="form-group">
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
                    <div class="form-group custom-layout-group" id="customLayout0" style="display: none;">
                        <label class="form-label">Custom Layout</label>
                        <input type="text" name="variations[0][custom_layout]" class="form-input" placeholder="e.g., 1800, 68%">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Switch Type</label>
                        <input type="text" name="variations[0][switch]" class="form-input" placeholder="e.g., Reaper SW, Cherry MX Red" required>
                    </div>
                    <div class="form-group">
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
            `;

                setTimeout(() => {
                    showSection('dashboard');
                    messageDiv.innerHTML = '';
                }, 2000);
            } else {
                messageDiv.innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    ${result.message}
                </div>
            `;
            }
        } catch (error) {
            messageDiv.innerHTML = `
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                An error occurred. Please try again.
            </div>
        `;
        }

        window.scrollTo(0, 0);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    showSection('dashboard');
});





