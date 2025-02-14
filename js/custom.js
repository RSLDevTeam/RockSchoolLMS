
// Custom JS

$ = jQuery;


// Menus
document.addEventListener('DOMContentLoaded', function () {

    // Vertical menu expand on hover
    const verticalMenu = document.querySelector('.vertical-menu');
    const site = document.querySelector('.site');

    if (verticalMenu && site) {
        verticalMenu.addEventListener('mouseenter', function () {
            verticalMenu.classList.remove('vertical-menu-clicked');
            site.classList.remove('menu-clicked');
        });

        verticalMenu.addEventListener('mouseleave', function () {
            verticalMenu.classList.add('vertical-menu-clicked');
            site.classList.add('menu-clicked');
        });
    }

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

// Adjust on load
adjustSoundsliceHeight();

// Adjust on resize
window.addEventListener('resize', adjustSoundsliceHeight);

// Adjust on DOMContentLoaded (in case of deferred elements above it)
document.addEventListener('DOMContentLoaded', adjustSoundsliceHeight);