// sidebar.js

export function initSidebar() {
    const highlightSidebarLink = () => {
        const links = document.querySelectorAll(".sidebar .nav-link, .offcanvas-body .nav-link");
        const currentPage = window.location.pathname.split("/").pop();

        links.forEach(link => {
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active");
            }
        });
    };

    // Initial call to highlight the link on page load
    highlightSidebarLink();

    // Optional: If you have dynamic content loading that changes the URL without a full page reload,
    // you might need to call highlightSidebarLink() again.
}
