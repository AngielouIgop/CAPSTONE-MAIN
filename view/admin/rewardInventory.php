<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rewards</title>
    <link rel="stylesheet" href="css/rewardInventory.css">
</head>
<body>
    <!-- ==================== REWARD MANAGEMENT HEADER ==================== -->
    <div class="reward-section-header">
        Manage Rewards
        <button class="reward-pill-btn reward-add-btn" style="float:right;">Add Reward</button>
    </div>
    
    <!-- ==================== SUCCESS/ERROR MESSAGES ==================== -->
    <div id="messageContainer" style="display: none; margin: 10px 16px; padding: 10px; border-radius: 5px; font-weight: bold;"></div>
    
    <!-- ==================== REWARDS TABLE ==================== -->
    <div class="reward-table-container">
        <table class="reward-custom-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Available Stock</th>
                    <th>Points Required</th>
                    <th>Slot Number</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rewards)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                            <div style="font-size: 1.2rem; margin-bottom: 10px;">No rewards found</div>
                            <div>Click "Add Reward" to create your first reward</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rewards as $reward): ?>
                        <tr>
                            <td>
                                <div class="data image-container">
                                    <img src="<?php echo !empty($reward['rewardImg']) && file_exists($reward['rewardImg']) ? $reward['rewardImg'] : 'images/coming-soon.png'; ?>" 
                                         alt="<?= htmlspecialchars($reward['rewardName']); ?>" 
                                         class="reward-image"
                                         data-rewardid="<?= $reward['rewardID']; ?>">
                                </div>
                            </td>
                            <td class="reward-name"><?= htmlspecialchars($reward['rewardName']) ?></td>
                            <td class="reward-stock"><?= htmlspecialchars($reward['availableStock']) ?></td>
                            <td class="reward-points"><?= htmlspecialchars($reward['pointsRequired']) ?> pts</td>
                            <td class="reward-slot"><?= htmlspecialchars($reward['slotNum']) ?></td>
                            <td>
                                <span class="status-badge <?= $reward['availability'] == 1 ? 'available' : 'unavailable' ?>">
                                    <?= $reward['availability'] == 1 ? 'Available' : 'Unavailable' ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="reward-action-btn reward-edit-btn"
                                    data-rewardid="<?= $reward['rewardID']; ?>"
                                    data-rewardname="<?= htmlspecialchars($reward['rewardName']); ?>"
                                    data-availablestock="<?= htmlspecialchars($reward['availableStock']);?>"
                                    data-pointsrequired="<?= htmlspecialchars($reward['pointsRequired']); ?>"
                                    data-slotnum="<?= htmlspecialchars($reward['slotNum']); ?>"
                                    data-availability="<?= $reward['availability']; ?>"
                                    data-rewardimg="<?= htmlspecialchars($reward['rewardImg']); ?>"
                                >Edit</a>
                                <a href="index.php?command=deleteReward&rewardID=<?= $reward['rewardID']; ?>" 
                                   class="reward-action-btn reward-delete-btn"
                                   onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($reward['rewardName']); ?>? This action cannot be undone.')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ==================== EDIT REWARD MODAL ==================== -->
    <div id="rewardEditModal" class="reward-modal">
        <div class="reward-modal-content">
            <div class="reward-modal-header">
                <h2>Edit Reward Details</h2>
            </div>
            <form id="rewardEditForm" action="index.php?command=updateReward" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" id="reward-edit-id" name="rewardID">

                <!-- Reward Information -->
                <label for="reward-edit-name">Reward Name *</label>
                <input type="text" id="reward-edit-name" name="rewardName" required>

                <label for="reward-edit-stock">Available Stock *</label>
                <input type="number" id="reward-edit-stock" name="availableStock" min="0" required>

                <label for="reward-edit-points">Points Required *</label>
                <input type="number" id="reward-edit-points" name="pointsRequired" min="1" required>

                <label for="reward-edit-slot">Slot Number *</label>
                <input type="number" id="reward-edit-slot" name="slotNum" min="1" required>

                <!-- Current Image Display -->
                <label for="reward-edit-img">Current Image</label>
                <div id="current-image-preview" class="image-preview-container">
                    <img id="current-image" src="" alt="Current image" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                    <div id="no-image" style="display: none; padding: 20px; text-align: center; color: #666; border: 2px dashed #ccc; border-radius: 8px;">
                        No image selected
                    </div>
                </div>

                <label for="reward-edit-availability">Status *</label>
                <select id="reward-edit-availability" name="availability" required>
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>

                <div class="reward-modal-buttons">
                    <button type="submit" class="reward-btn-confirm">Update Reward</button>
                    <button type="button" class="reward-btn-cancel" id="rewardCancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== ADD REWARD MODAL ==================== -->
    <div id="addRewardModal" class="reward-modal">
        <div class="reward-modal-content">
            <div class="reward-modal-header">
                <h2>Add New Reward</h2>
            </div>
            <form id="rewardAdditionForm" action="index.php?command=addReward" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" id="reward-add-id" name="rewardID">

                <!-- Reward Information -->
                <label for="reward-add-name">Reward Name *</label>
                <input type="text" id="reward-add-name" name="rewardName" required>

                <label for="reward-add-stock">Available Stock *</label>
                <input type="number" id="reward-add-stock" name="availableStock" min="0" required>

                <label for="reward-add-points">Points Required *</label>
                <input type="number" id="reward-add-points" name="pointsRequired" min="1" required>

                <label for="reward-add-slot">Slot Number *</label>
                <input type="number" id="reward-add-slot" name="slotNum" min="1" required>

                <!-- Image Upload -->
                <label for="reward-add-img">Reward Image *</label>
                <input type="file" id="reward-add-img" name="rewardImg" accept="image/*" required onchange="previewImage(this, 'add-image-preview')">
                <div id="add-image-preview" class="image-preview-container" style="display: none;">
                    <img id="add-image" src="" alt="Image preview" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                </div>

                <label for="reward-add-availability">Status *</label>
                <select id="reward-add-availability" name="availability" required>
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>

                <div class="reward-modal-buttons">
                    <button type="submit" class="reward-btn-confirm">Add Reward</button>
                    <button type="button" class="reward-btn-cancel" id="addRewardCancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT FUNCTIONS ==================== -->
    <script>
        // ==================== IMAGE PREVIEW FUNCTION ====================
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const img = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // ==================== MESSAGE DISPLAY FUNCTION ====================
        function showMessage(message, type = 'success') {
            const messageContainer = document.getElementById('messageContainer');
            messageContainer.textContent = message;
            messageContainer.style.display = 'block';
            messageContainer.style.backgroundColor = type === 'success' ? '#d4edda' : '#f8d7da';
            messageContainer.style.color = type === 'success' ? '#155724' : '#721c24';
            messageContainer.style.border = `1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'}`;
            
            setTimeout(() => {
                messageContainer.style.display = 'none';
            }, 5000);
        }

        // ==================== EDIT REWARD MODAL HANDLERS ====================
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('rewardEditModal');
            const editBtns = document.querySelectorAll('.reward-edit-btn');
            const cancelBtn = document.getElementById('rewardCancelBtn');

            editBtns.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    // Populate form fields
                    document.getElementById('reward-edit-id').value = this.dataset.rewardid;
                    document.getElementById('reward-edit-name').value = this.dataset.rewardname;
                    document.getElementById('reward-edit-stock').value = this.dataset.availablestock;
                    document.getElementById('reward-edit-points').value = this.dataset.pointsrequired;
                    document.getElementById('reward-edit-slot').value = this.dataset.slotnum;
                    document.getElementById('reward-edit-availability').value = this.dataset.availability;
                    
                    // Handle current image display
                    const currentImage = document.getElementById('current-image');
                    const noImageDiv = document.getElementById('no-image');
                    
                    if (this.dataset.rewardimg && this.dataset.rewardimg !== '') {
                        currentImage.src = this.dataset.rewardimg;
                        currentImage.style.display = 'block';
                        noImageDiv.style.display = 'none';
                    } else {
                        currentImage.style.display = 'none';
                        noImageDiv.style.display = 'block';
                    }
                    
                    modal.style.display = 'block';
                });
            });

            cancelBtn.addEventListener('click', function () {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function (event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // ==================== ADD REWARD MODAL HANDLERS ====================
        document.addEventListener('DOMContentLoaded', function () {
            const addModal = document.getElementById('addRewardModal');
            const addBtn = document.querySelector('.reward-add-btn');
            const addCancelBtn = document.getElementById('addRewardCancelBtn');

            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('rewardAdditionForm').reset();
                document.getElementById('add-image-preview').style.display = 'none';
                addModal.style.display = 'block';
            });

            addCancelBtn.addEventListener('click', function () {
                addModal.style.display = 'none';
            });

            window.addEventListener('click', function (event) {
                if (event.target == addModal) {
                    addModal.style.display = 'none';
                }
            });
        });

        // ==================== FORM VALIDATION ====================
        document.getElementById('rewardEditForm').addEventListener('submit', function(e) {
            const stock = parseInt(document.getElementById('reward-edit-stock').value);
            const points = parseInt(document.getElementById('reward-edit-points').value);
            const slot = parseInt(document.getElementById('reward-edit-slot').value);
            
            if (stock < 0 || points < 1 || slot < 1) {
                e.preventDefault();
                showMessage('Please enter valid values for stock (≥0), points (≥1), and slot (≥1).', 'error');
            }
        });

        document.getElementById('rewardAdditionForm').addEventListener('submit', function(e) {
            const stock = parseInt(document.getElementById('reward-add-stock').value);
            const points = parseInt(document.getElementById('reward-add-points').value);
            const slot = parseInt(document.getElementById('reward-add-slot').value);
            
            if (stock < 0 || points < 1 || slot < 1) {
                e.preventDefault();
                showMessage('Please enter valid values for stock (≥0), points (≥1), and slot (≥1).', 'error');
            }
        });
    </script>
</body>
</html>