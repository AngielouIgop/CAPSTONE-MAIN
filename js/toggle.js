document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.getElementById("menu-toggle");
  const headerNav = document.getElementById("header-nav");
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebar-overlay");
  const sidebarToggleBtn = document.getElementById("sidebar-toggle-btn");
  
  // Check if user is in public/guest mode
  const header = document.querySelector('.header-main');
  const isGuest = header && header.getAttribute('data-role') === 'guest';

  // Ensure sidebar starts closed on page load
  if (sidebar) {
    sidebar.classList.remove("active");
    // On desktop, start with sidebar visible (only for logged-in users)
    if (window.innerWidth >= 901 && !isGuest) {
      sidebar.classList.remove("hidden");
    }
  }
  if (sidebarOverlay) {
    sidebarOverlay.classList.remove("active");
  }

  // Header navigation toggle (for mobile menu) - works for all users
  if (menuToggle && headerNav) {
    menuToggle.addEventListener("click", () => {
      headerNav.classList.toggle("active");
    });
  }

  // Sidebar toggle functionality (mobile) - only for logged-in users
  if (menuToggle && sidebar && !isGuest) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("active");
      if (sidebarOverlay) {
        sidebarOverlay.classList.toggle("active");
      }
    });
  }

  // Desktop sidebar toggle functionality - only for logged-in users
  if (sidebarToggleBtn && sidebar && !isGuest) {
    sidebarToggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("hidden");
      sidebarToggleBtn.classList.toggle("active");
      
      // Update body margin for all pages
      document.body.classList.toggle("sidebar-hidden");
      
      // Update content margin
      const content = document.querySelector('.main-content');
      if (content) {
        content.classList.toggle("sidebar-hidden");
      }
    });
  }

  // Close sidebar when clicking overlay - only for logged-in users
  if (sidebarOverlay && !isGuest) {
    sidebarOverlay.addEventListener("click", () => {
      sidebar.classList.remove("active");
      sidebarOverlay.classList.remove("active");
    });
  }

  // Close sidebar when clicking outside (for desktop) - only for logged-in users
  if (!isGuest) {
    document.addEventListener("click", (e) => {
      if (sidebar && sidebar.classList.contains("active")) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          sidebar.classList.remove("active");
          if (sidebarOverlay) {
            sidebarOverlay.classList.remove("active");
          }
        }
      }
    });
  }
});

