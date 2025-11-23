// ========================================
// SIDEBAR & NAVIGATION TOGGLE - ORGANIZED BY FUNCTIONS
// ========================================

// ==================== UTILITY FUNCTIONS ====================

/**
 * Check if the current user is a guest
 * @returns {boolean} True if user is a guest, false otherwise
 */
function isGuestUser() {
  const header = document.querySelector('.header-main');
  return header && header.getAttribute('data-role') === 'guest';
}

/**
 * Set the active link in the sidebar based on current page URL
 */
function setActiveLink() {
  const sidebarLinks = document.querySelectorAll('.sidebar .nav-links a');
  const currentCommand = new URLSearchParams(window.location.search).get('command') || 'home';
  
  sidebarLinks.forEach(link => {
    link.classList.remove('active');
    const linkHref = link.getAttribute('href');
    
    // Check if link matches current command
    if (linkHref && linkHref.includes(`command=${currentCommand}`)) {
      link.classList.add('active');
    }
  });
}

/**
 * Close the sidebar
 * @param {HTMLElement} sidebar - The sidebar element
 * @param {HTMLElement} sidebarOverlay - The sidebar overlay element
 */
function closeSidebar(sidebar, sidebarOverlay) {
  sidebar.classList.remove("active");
  if (sidebarOverlay) {
    sidebarOverlay.classList.remove("active");
  }
}

// ==================== INITIALIZATION ====================

document.addEventListener("DOMContentLoaded", () => {
  // Get DOM elements
  const menuToggle = document.getElementById("menu-toggle");
  const headerNav = document.getElementById("header-nav");
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebar-overlay");
  const sidebarToggleBtn = document.getElementById("sidebar-toggle-btn");
  const isGuest = isGuestUser();

  // ==================== INITIAL STATE SETUP ====================
  
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

  // ==================== HEADER NAVIGATION TOGGLE ====================
  
  // Header navigation toggle (for mobile menu) - works for all users
  if (menuToggle && headerNav) {
    menuToggle.addEventListener("click", () => {
      headerNav.classList.toggle("active");
    });
  }

  // ==================== SIDEBAR TOGGLE FUNCTIONALITY ====================
  
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

  // ==================== SIDEBAR ACTIVE LINK HIGHLIGHTING ====================
  
  // Set active link on page load
  setActiveLink();
  
  // Add click handler to sidebar links to set active state
  const sidebarLinks = document.querySelectorAll('.sidebar .nav-links a');
  sidebarLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Remove active class from all links
      sidebarLinks.forEach(l => l.classList.remove('active'));
      // Add active class to clicked link
      this.classList.add('active');
    });
  });

  // ==================== SIDEBAR CLOSE FUNCTIONALITY ====================
  
  // Close sidebar when clicking overlay - only for logged-in users
  if (sidebarOverlay && !isGuest) {
    sidebarOverlay.addEventListener("click", () => {
      closeSidebar(sidebar, sidebarOverlay);
    });
  }

  // Close sidebar when clicking outside (for desktop) - only for logged-in users
  if (!isGuest) {
    document.addEventListener("click", (e) => {
      if (sidebar && sidebar.classList.contains("active")) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          closeSidebar(sidebar, sidebarOverlay);
        }
      }
    });
  }
});
