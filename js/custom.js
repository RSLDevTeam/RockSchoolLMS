
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

// Soundslice embed shizzle
function adjustSoundsliceHeight() {
  const soundsliceContainer = document.querySelector('.soundslice-container');
  const contentActions = document.querySelector('.ld-content-actions');
  
  if (!soundsliceContainer) return;

  // Get the distance from the top of the page to the top of the container
  const containerTop = soundsliceContainer.getBoundingClientRect().top + window.scrollY;

  // Get the height of .ld-content-actions if it exists
  const contentActionsHeight = contentActions ? contentActions.offsetHeight : 0;

  // Calculate the available height from the top of the container to the bottom of the viewport,
  // minus the height of .ld-content-actions
  const availableHeight = window.innerHeight - containerTop - contentActionsHeight;

  // Apply the height dynamically
  soundsliceContainer.style.height = `${availableHeight}px`;
}

adjustSoundsliceHeight();
window.addEventListener('resize', adjustSoundsliceHeight);
document.addEventListener('DOMContentLoaded', adjustSoundsliceHeight);

// Toggle width on expand clicked
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".width-toggle").addEventListener("click", function () {
        document.querySelector("main").classList.toggle("full-width");
    });
});



