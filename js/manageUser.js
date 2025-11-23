// ========================================
// MANAGE USER - ORGANIZED BY FUNCTIONS
// ========================================

// ==================== UTILITY FUNCTIONS ====================

/**
 * Toggle password visibility
 * @param {string} inputId - The ID of the password input field
 * @param {HTMLElement} toggleBtn - The toggle button element
 */
function togglePassword(inputId, toggleBtn) {
  const passwordInput = document.getElementById(inputId);

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    toggleBtn.textContent = "Hide";
    toggleBtn.title = "Hide password";
  } else {
    passwordInput.type = "password";
    toggleBtn.textContent = "Show";
    toggleBtn.title = "Show password";
  }
}

// ==================== PENDING REGISTRATIONS FUNCTIONS ====================

/**
 * Update the pending registrations counter badge
 */
function updatePendingCounter() {
  fetch("endpoints/getPendingRegistrations.php")
    .then((response) => response.json())
    .then((data) => {
      const counter = document.getElementById('pending-counter');
      if (counter) {
        const count = data.length;
        counter.textContent = count;
        
        // Hide badge if no pending registrations
        if (count === 0) {
          counter.classList.add('hidden');
        } else {
          counter.classList.remove('hidden');
        }
      }
    })
    .catch((error) => {
      console.error("Error updating pending counter:", error);
    });
}

/**
 * Load and display pending registrations in the modal
 */
function loadPendingRegistrations() {
  fetch("endpoints/getPendingRegistrations.php")
    .then((response) => response.json())
    .then((data) => {
      const container = document.getElementById("pendingRegistrationsList");
      if (data.length === 0) {
        container.innerHTML = "<p>No pending registrations found.</p>";
        return;
      }

      container.innerHTML = data
        .map(
          (registration) => `
                <div class="pending-registration-item">
                    <div class="registration-info">
                        <h4>${registration.fullName}</h4>
                        <p><strong>Username:</strong> ${registration.username}</p>
                        <p><strong>Email:</strong> ${registration.email}</p>
                        <p><strong>Zone:</strong> ${registration.zone}</p>
                        <p><strong>Brgy ID:</strong> ${registration.brgyIDNum}</p>
                        <p><strong>Contact:</strong> ${registration.contactNumber}</p>
                        <p><strong>Submitted:</strong> ${new Date(registration.submittedAt).toLocaleDateString()}</p>
                    </div>
                    <div class="registration-actions">
                        <button class="btn-approve" onclick="approveRegistration(${registration.id})">Approve</button>
                        <button class="btn-reject" onclick="rejectRegistration(${registration.id})">Reject</button>
                    </div>
                </div>
            `
        )
        .join("");
    })
    .catch((error) => {
      console.error("Error loading pending registrations:", error);
      document.getElementById("pendingRegistrationsList").innerHTML =
        "<p>Error loading pending registrations.</p>";
    });
}

/**
 * Approve a pending registration
 * @param {number} registrationId - The ID of the registration to approve
 */
function approveRegistration(registrationId) {
  if (confirm("Are you sure you want to approve this registration?")) {
    fetch("endpoints/approveRegistration.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `registrationId=${registrationId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Registration approved successfully!");
          loadPendingRegistrations();
          updatePendingCounter();
        } else {
          alert("Error approving registration: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error approving registration.");
      });
  }
}

/**
 * Reject a pending registration
 * @param {number} registrationId - The ID of the registration to reject
 */
function rejectRegistration(registrationId) {
  if (confirm("Are you sure you want to reject this registration?")) {
    fetch("endpoints/rejectRegistration.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `registrationId=${registrationId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Registration rejected successfully!");
          loadPendingRegistrations();
          updatePendingCounter();
        } else {
          alert("Error rejecting registration: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error rejecting registration.");
      });
  }
}

// ==================== MODAL FUNCTIONS ====================

/**
 * Open the pending registrations modal
 */
function openPendingModal() {
  document.getElementById("pendingRegistrationsModal").style.display = "block";
  loadPendingRegistrations();
}

/**
 * Close the pending registrations modal
 */
function closePendingModal() {
  document.getElementById("pendingRegistrationsModal").style.display = "none";
}

/**
 * Close the edit user modal
 */
function closeEditModal() {
  document.getElementById("editUserModal").style.display = "none";
}

// ==================== EVENT LISTENERS & INITIALIZATION ====================

// Edit User Modal Event Listeners
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("editUserModal");
  const editBtns = document.querySelectorAll(".edit-btn");
  const cancelBtn = document.getElementById("cancelBtn");

  editBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      // Populate form with user data
      document.getElementById("edit-userID").value = this.dataset.userid;
      document.getElementById("edit-fullname").value = this.dataset.fullname;
      document.getElementById("edit-email").value = this.dataset.email;
      document.getElementById("edit-zone").value = this.dataset.zone;
      document.getElementById("edit-contactNumber").value = this.dataset.contactnumber;
      document.getElementById("edit-username").value = this.dataset.username;
      document.getElementById("edit-password").value = "";
      document.getElementById("edit-confirmPassword").value = "";

      modal.style.display = "block";
    });
  });

  cancelBtn.addEventListener("click", function () {
    modal.style.display = "none";
  });

  window.addEventListener("click", function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  });
});

// Add Administrator Modal Event Listeners
document.addEventListener("DOMContentLoaded", function () {
  const addAdminModal = document.getElementById("addAdministratorModal");
  const addAdminBtn = document.querySelector(".add-admin-btn");
  const addCancelBtn = addAdminModal.querySelector(".btn-cancel");

  addAdminBtn.addEventListener("click", function (e) {
    e.preventDefault();
    addAdminModal.style.display = "block";
  });

  addCancelBtn.addEventListener("click", function () {
    addAdminModal.style.display = "none";
  });

  window.addEventListener("click", function (event) {
    if (event.target == addAdminModal) {
      addAdminModal.style.display = "none";
    }
  });
});

// Pending Registrations Modal Event Listeners
document.addEventListener("DOMContentLoaded", function () {
  // Update pending counter on page load
  updatePendingCounter();
  
  const pendingRegistrationsModal = document.getElementById("pendingRegistrationsModal");
  const pendingRegistrationsBtn = document.querySelector(".pending-registrations-btn");
  const pendingRegistrationsCancelBtn = pendingRegistrationsModal.querySelector(".btn-cancel");

  pendingRegistrationsBtn.addEventListener("click", function (e) {
    e.preventDefault();
    pendingRegistrationsModal.style.display = "block";
  });

  pendingRegistrationsCancelBtn.addEventListener("click", function () {
    pendingRegistrationsModal.style.display = "none";
  });

  window.addEventListener("click", function (event) {
    if (event.target == pendingRegistrationsModal) {
      pendingRegistrationsModal.style.display = "none";
    }
  });
});
