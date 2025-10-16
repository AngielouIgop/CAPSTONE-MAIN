document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const sidebarToggle = document.getElementById('sidebar-toggle');

  if (!sidebar) return;

  const closeSidebar = () => {
    sidebar.classList.add('collapsed');
    if (overlay) overlay.classList.remove('active');
  };

  const openSidebar = () => {
    sidebar.classList.remove('collapsed');
    if (overlay) overlay.classList.add('active');
  };

  // Initialize collapsed on mobile
  const syncInitialState = () => {
    if (window.matchMedia('(max-width: 900px)').matches) {
      closeSidebar();
    } else {
      // desktop - no overlay
      if (overlay) overlay.classList.remove('active');
    }
  };
  syncInitialState();
  window.addEventListener('resize', syncInitialState);

  // Toggle button inside sidebar
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', (e) => {
      e.preventDefault();
      if (sidebar.classList.contains('collapsed')) {
        openSidebar();
      } else {
        closeSidebar();
      }
    });
  }

  // Overlay click closes sidebar
  if (overlay) {
    overlay.addEventListener('click', () => {
      closeSidebar();
    });
  }
});
