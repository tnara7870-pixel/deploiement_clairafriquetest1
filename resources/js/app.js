import "./bootstrap";

const staffSidebar = document.getElementById("staff-sidebar");
const staffMenuToggle = document.querySelector("[data-staff-menu-toggle]");
const staffMenuBackdrop = document.querySelector("[data-staff-menu-backdrop]");

if (staffSidebar && staffMenuToggle && staffMenuBackdrop) {
    const setStaffMenuOpen = (open) => {
        staffSidebar.classList.toggle("-translate-x-full", !open);
        staffMenuBackdrop.classList.toggle("hidden", !open);
        staffMenuToggle.setAttribute("aria-expanded", String(open));
        staffMenuToggle.setAttribute(
            "aria-label",
            open ? "Fermer le menu" : "Ouvrir le menu",
        );
    };

    staffMenuToggle.addEventListener("click", () => {
        setStaffMenuOpen(
            staffMenuToggle.getAttribute("aria-expanded") !== "true",
        );
    });
    staffMenuBackdrop.addEventListener("click", () => setStaffMenuOpen(false));
    window.addEventListener("resize", () => {
        if (window.innerWidth >= 768) setStaffMenuOpen(false);
    });
}
