document.addEventListener("DOMContentLoaded", function () {
  const currentPage = window.location.pathname.split("/").pop();

  // Loop through all sidebar links
  document.querySelectorAll(".nav-link").forEach(link => {
    const href = link.getAttribute("href");

    if (!href || href.startsWith("http")) return;

    // Check if current link matches the page
    if (href.includes(currentPage)) {
      link.classList.add("active");

      // Expand the parent collapse menu (ui-basic to ui-basic-8)
      const parentCollapse = link.closest(".collapse");
      if (parentCollapse) {
        parentCollapse.classList.add("show");

        // Toggle button for this collapse
        const toggler = document.querySelector(`a.nav-link[data-toggle="collapse"][href="#${parentCollapse.id}"]`);
        if (toggler) {
          toggler.setAttribute("aria-expanded", "true");
        }
      }
    }
  });
});
