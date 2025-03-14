
// Custom JS

$ = jQuery;


// Menus
document.addEventListener('DOMContentLoaded', function () {

    // Vertical menu expand on hover
    const verticalMenu = document.querySelector('.vertical-menu');
    const site = document.querySelector('.site');
    const body = document.querySelector('body');

    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    mobileToggle.addEventListener('click', function () {
        verticalMenu.classList.toggle('vertical-menu-clicked');
        site.classList.toggle('menu-clicked');
        body.classList.toggle('mobile-nav-open');
    });

    function handleVerticalMenuEvents() {
        if (window.matchMedia('(min-width: 768px)').matches) {
            if (verticalMenu && site) {
                verticalMenu.addEventListener('mouseenter', handleMouseEnter);
                verticalMenu.addEventListener('mouseleave', handleMouseLeave);
            }
        } else {
            if (verticalMenu && site) {
                verticalMenu.removeEventListener('mouseenter', handleMouseEnter);
                verticalMenu.removeEventListener('mouseleave', handleMouseLeave);
            }
        }
    }

    function handleMouseEnter() {
        verticalMenu.classList.remove('vertical-menu-clicked');
        site.classList.remove('menu-clicked');
    }

    function handleMouseLeave() {
        verticalMenu.classList.add('vertical-menu-clicked');
        site.classList.add('menu-clicked');
    }

    // Run on initial load
    handleVerticalMenuEvents();

    // Update event listeners on window resize
    window.addEventListener('resize', handleVerticalMenuEvents);

    const ldToggle = document.querySelector('.ld-content-sidebar-nav-toggle');
    const ldSidebar = document.querySelector('.ld-content-sidebar');

    if (ldToggle && ldSidebar) {
        ldToggle.addEventListener('click', function () {
            ldSidebar.classList.toggle('ld-sidebar-open');
        });
    }



});





