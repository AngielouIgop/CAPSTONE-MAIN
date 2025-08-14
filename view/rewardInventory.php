<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rewards</title>
    <link rel="stylesheet" href="css/rewardInventory.css">
</head>
<body>
    <div class="reward-section-header">
        Manage Rewards
        <button class="reward-pill-btn reward-add-btn" style="float:right;">Add Reward</button>
    </div>
    <div class="reward-table-container">
        <table class="reward-custom-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Available Stock</th>
                    <th>Points Required</th>
                    <th>Slot Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rewards as $reward): ?>
                    <tr>
                        <td>
                            <?php
                            if (!empty($reward['rewardImg'])) {
                                if (file_exists($reward['rewardImg'])) {
                                    $src = $reward['rewardImg'];
                                } else {
                                    $imgData = base64_encode($reward['rewardImg']);
                                    $src = 'data:image/jpeg;base64,' . $imgData;
                                }
                            } else {
                                $src = 'images/coming-soon.png';
                            }
                            ?>
                            <img src="<?= htmlspecialchars($src); ?>" alt="<?= htmlspecialchars($reward['rewardName']); ?>" style="width:40px;height:40px;border-radius:8px;">
                        </td>
                        <td><?= htmlspecialchars($reward['rewardName']) ?></td>
                        <td><?= htmlspecialchars($reward['availableStock'])?></td>
                        <td><?= htmlspecialchars($reward['pointsRequired']) ?> pts</td>
                        <td><?= htmlspecialchars($reward['slotNum']) ?></td>
                        <td>
                            <a href="#" class="reward-action-btn reward-edit-btn"
                                data-rewardid="<?= $reward['rewardID']; ?>"
                                data-rewardname="<?= htmlspecialchars($reward['rewardName']); ?>"
                                data-availablestock="<?= htmlspecialchars($reward['availableStock']);?>"
                                data-pointsrequired="<?= htmlspecialchars($reward['pointsRequired']); ?>"
                                data-slotnum="<?= htmlspecialchars($reward['slotNum']); ?>"
                            >Edit</a>
                            <a href="index.php?command=deleteReward&rewardID=<?= $reward['rewardID']; ?>" class="reward-action-btn"
                                onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($reward['rewardName']); ?>?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Reward Modal -->
    <div id="rewardEditModal" class="reward-modal">
        <div class="reward-modal-content">
            <div class="reward-modal-header">
                <h2>Edit Reward Details</h2>
            </div>
            <form id="rewardEditForm" action="index.php?command=updateReward" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="reward-edit-id" name="rewardID">

                <label for="reward-edit-name">Reward Name</label>
                <input type="text" id="reward-edit-name" name="rewardName" required>

                <label for="reward-edit-stock">Available Stock</label>
                <input type="text" id="reward-edit-stock" name="availableStock" required>

                <label for="reward-edit-points">Points Required</label>
                <input type="number" id="reward-edit-points" name="pointsRequired" required>

                <label for="reward-edit-slot">Slot Number</label>
                <input type="number" id="reward-edit-slot" name="slotNum" required>

                <label for="reward-edit-img">Reward Image</label>
                <input type="file" id="reward-edit-img" name="rewardImg" accept="image/*">

                <label for="reward-availability">Available?</label>
                <select id="reward-availability" name="availability" required>
                  <option value="1">Available</option>
                  <option value="0">Not Available</option>
                </select>

                <div class="reward-modal-buttons">
                    <button type="submit" class="reward-btn-confirm">Confirm</button>
                    <button type="button" class="reward-btn-cancel" id="rewardCancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add reward Modal -->
    <div id="addRewardModal" class="reward-modal">
        <div class="reward-modal-content">
            <div class="reward-modal-header">
                <h2>Add Reward Details</h2>
            </div>
            <form id="rewardAdditionForm" action="index.php?command=addReward" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="reward-add-id" name="rewardID">

                <label for="reward-add-name">Reward Name</label>
                <input type="text" id="reward-add-name" name="rewardName" required>

                <label for="reward-add-stock">Available Stock</label>
                <input type="text" id="reward-add-stock" name="availableStock" required>

                <label for="reward-add-points">Points Required</label>
                <input type="number" id="reward-add-points" name="pointsRequired" required>

                <label for="reward-add-slot">Slot Number</label>
                <input type="number" id="reward-add-slot" name="slotNum" required>

                <label for="reward-add-img">Reward Image</label>
                <input type="file" id="reward-add-img" name="rewardImg" accept="image/*" required>

                <label for="reward-availability">Available?</label>
                <select id="reward-availability" name="availability" required>
                  <option value="1">Available</option>
                  <option value="0">Not Available</option>
                </select>

                <div class="reward-modal-buttons">
                    <button type="submit" class="reward-btn-confirm">Confirm</button>
                    <button type="button" class="reward-btn-cancel" id="addRewardCancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('rewardEditModal');
        const editBtns = document.querySelectorAll('.reward-edit-btn');
        const cancelBtn = document.getElementById('rewardCancelBtn');

        editBtns.forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('reward-edit-id').value = this.dataset.rewardid;
                document.getElementById('reward-edit-name').value = this.dataset.rewardname;
                document.getElementById('reward-edit-stock').value = this.dataset.availablestock;
                document.getElementById('reward-edit-points').value = this.dataset.pointsrequired;
                document.getElementById('reward-edit-slot').value = this.dataset.slotnum;
                document.getElementById('reward-edit-img').value = '';
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
    </script>

   <script>
document.addEventListener('DOMContentLoaded', function () {
    const addModal = document.getElementById('addRewardModal');
    const addBtn = document.querySelector('.reward-add-btn');
    const addCancelBtn = document.getElementById('addRewardCancelBtn');

    addBtn.addEventListener('click', function (e) {
        e.preventDefault();
        document.getElementById('rewardAdditionForm').reset();
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
</script>
</body>
</html>