// js/toggle.js
document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector(".header-main");
  const role = header?.getAttribute("data-role") || "guest";

  const menuToggle = document.getElementById("menu-toggle");
  const headerNav = document.getElementById("header-nav");
  const sidebar = document.getElementById("sidebar");
  const sidebarOverlay = document.getElementById("sidebar-overlay");

  if (role === "guest") {
    // 👉 Guest: toggle header nav only
    if (menuToggle && headerNav) {
      menuToggle.addEventListener("click", () => {
        headerNav.classList.toggle("active");
      });
    }
  } else {
    // 👉 Logged-in user/admin: toggle sidebar
    if (menuToggle && sidebar) {
      menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("active");
        if (sidebarOverlay) sidebarOverlay.classList.toggle("active");
      });
    }

    // Overlay click closes sidebar
    if (sidebarOverlay) {
      sidebarOverlay.addEventListener("click", () => {
        if (sidebar) sidebar.classList.remove("active");
        sidebarOverlay.classList.remove("active");
      });
    }
  }
});
