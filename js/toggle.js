document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.getElementById("menu-toggle");
  const headerNav = document.getElementById("header-nav");
  const menuOverlay = document.getElementById("menu-overlay");

  if (menuToggle && headerNav) {
    menuToggle.addEventListener("click", () => {
      headerNav.classList.toggle("active");
      if (menuOverlay) menuOverlay.classList.toggle("active");
    });
  }

  if (menuOverlay) {
    menuOverlay.addEventListener("click", () => {
      headerNav.classList.remove("active");
      menuOverlay.classList.remove("active");
    });
  }
});

