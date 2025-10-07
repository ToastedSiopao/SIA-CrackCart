<?php
// This file is a reusable UI component for creating or editing a product.
// It can be included in a modal or a separate page.
// It requires a $producers array to be available in the scope where it's included.
// For editing, it requires a $product object.

$is_edit = isset($product) && $product != null;
$modal_id = $is_edit ? 'editProductModal' . $product['price_id'] : 'addProductModal';
$form_id = $is_edit ? 'editProductForm' . $product['price_id'] : 'addProductForm';

?>
<div class="modal fade" id="<?php echo $modal_id; ?>" tabindex="-1" aria-labelledby="productModalLabel_<?php echo $modal_id; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel_<?php echo $modal_id; ?>"><?php echo $is_edit ? 'Edit Product' : 'Add New Product'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="<?php echo $form_id; ?>" class="needs-validation" novalidate>
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="price_id" value="<?php echo $product['price_id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="producer_id_<?php echo $modal_id; ?>" class="form-label">Producer</label>
                        <select class="form-select" id="producer_id_<?php echo $modal_id; ?>" name="producer_id" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                            <option value="">Select Producer</option>
                            <?php foreach ($producers as $producer): ?>
                                <option value="<?php echo $producer['producer_id']; ?>"
                                    <?php echo ($is_edit && $product['producer_id'] == $producer['producer_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($producer['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Please select a producer.</div>
                    </div>

                    <div class="mb-3">
                        <label for="type_<?php echo $modal_id; ?>" class="form-label">Product Type/Name</label>
                        <input type="text" class="form-control" id="type_<?php echo $modal_id; ?>" name="type" 
                               value="<?php echo $is_edit ? htmlspecialchars($product['type']) : ''; ?>" required>
                        <div class="invalid-feedback">Please enter the product type.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_<?php echo $modal_id; ?>" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="price_<?php echo $modal_id; ?>" name="price" 
                                       value="<?php echo $is_edit ? htmlspecialchars($product['price']) : ''; ?>" step="0.01" min="0" required>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="per_<?php echo $modal_id; ?>" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="per_<?php echo $modal_id; ?>" name="per" 
                                   value="<?php echo $is_edit ? htmlspecialchars($product['per']) : 'tray'; ?>" required>
                            <small class="form-text text-muted">e.g., tray, piece, box</small>
                            <div class="invalid-feedback">Please enter the unit.</div>
                        </div>
                    </div>

                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label for="stock_<?php echo $modal_id; ?>" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock_<?php echo $modal_id; ?>" name="stock" 
                                   value="<?php echo $is_edit ? htmlspecialchars($product['stock']) : '0'; ?>" min="0" required>
                            <div class="invalid-feedback">Please enter a valid stock quantity.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tray_size_<?php echo $modal_id; ?>" class="form-label">Tray Size</label>
                            <select class="form-select" id="tray_size_<?php echo $modal_id; ?>" name="tray_size">
                                <option value="30" <?php echo ($is_edit && $product['tray_size'] == 30) ? 'selected' : ''; ?>>30 (Standard)</option>
                                <option value="12" <?php echo ($is_edit && $product['tray_size'] == 12) ? 'selected' : ''; ?>>12</option>
                            </select>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning" form="<?php echo $form_id; ?>"><?php echo $is_edit ? 'Save Changes' : 'Add Product'; ?></button>
            </div>
        </div>
    </div>
</div>
