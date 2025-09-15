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

// Pending Registrations Functions
function openPendingModal() {
  document.getElementById("pendingRegistrationsModal").style.display = "block";
  loadPendingRegistrations();
}

function closePendingModal() {
  document.getElementById("pendingRegistrationsModal").style.display = "none";
}

function closeEditModal() {
  document.getElementById("editUserModal").style.display = "none";
}

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
                        <p><strong>Username:</strong> ${
                          registration.username
                        }</p>
                        <p><strong>Email:</strong> ${registration.email}</p>
                        <p><strong>Zone:</strong> ${registration.zone}</p>
                        <p><strong>Brgy ID:</strong> ${registration.brgyID}</p>
                        <p><strong>Contact:</strong> ${
                          registration.contactNumber
                        }</p>
                        <p><strong>Submitted:</strong> ${new Date(
                          registration.submittedAt
                        ).toLocaleDateString()}</p>
                    </div>
                    <div class="registration-actions">
                        <button class="btn-approve" onclick="approveRegistration(${
                          registration.id
                        })">Approve</button>
                        <button class="btn-reject" onclick="rejectRegistration(${
                          registration.id
                        })">Reject</button>
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

document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("editUserModal");
  const editBtns = document.querySelectorAll(".edit-btn");
  const cancelBtn = document.getElementById("cancelBtn");

  editBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      // Populate form
      document.getElementById("edit-userID").value = this.dataset.userid;
      document.getElementById("edit-fullname").value = this.dataset.fullname;
      document.getElementById("edit-email").value = this.dataset.email;
      document.getElementById("edit-zone").value = this.dataset.zone;
      document.getElementById("edit-contactNumber").value =
        this.dataset.contactnumber;
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

document.addEventListener("DOMContentLoaded", function () {
  const addAdminModal = document.getElementById("addAdministratorModal");
  const addAdminBtn = document.querySelector(".add-admin-btn"); // or use '#addAdminBtn' if you used id
  const addCancelBtn = addAdminModal.querySelector(".btn-cancel");

  // Open modal on button click
  addAdminBtn.addEventListener("click", function (e) {
    e.preventDefault();
    addAdminModal.style.display = "block";
  });

  // Close modal on cancel
  addCancelBtn.addEventListener("click", function () {
    addAdminModal.style.display = "none";
  });

  // Close modal when clicking outside
  window.addEventListener("click", function (event) {
    if (event.target == addAdminModal) {
      addAdminModal.style.display = "none";
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const pendingRegistrationsModal = document.getElementById(
    "pendingRegistrationsModal"
  );
  const pendingRegistrationsBtn = document.querySelector(
    ".pending-registrations-btn"
  ); // or use '#addAdminBtn' if you used id
  const pendingRegistrationsCancelBtn =
    pendingRegistrationsModal.querySelector(".btn-cancel");

  // Open modal on button click
  pendingRegistrationsBtn.addEventListener("click", function (e) {
    e.preventDefault();
    pendingRegistrationsModal.style.display = "block";
  });

  // Close modal on cancel
  pendingRegistrationsCancelBtn.addEventListener("click", function () {
    pendingRegistrationsModal.style.display = "none";
  });

  // Close modal when clicking outside
  window.addEventListener("click", function (event) {
    if (event.target == pendingRegistrationsModal) {
      pendingRegistrationsModal.style.display = "none";
    }
  });
});
