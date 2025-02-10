// Custom JS

$ = jQuery;


// Menu
document.addEventListener('DOMContentLoaded', function () {
    const burger = document.querySelector('.navigation__burger');
    const verticalMenu = document.querySelector('.vertical-menu');
    const mainContainer = document.querySelector('.main-container');
    const site = document.querySelector('.site');
    const ldToggle = document.querySelector('.ld-content-sidebar-nav-toggle');
    const ldSidebar = document.querySelector('.ld-content-sidebar');

    
    

    // Function to set a cookie
    function setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
    }

    // Function to get a cookie
    function getCookie(name) {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.startsWith(`${name}=`)) {
                return cookie.substring(name.length + 1);
            }
        }
        return null;
    }

    // Apply saved menu state on load
    const menuState = getCookie('menu_state');
    if (menuState === 'focus') {
        verticalMenu.classList.add('vertical-menu-clicked');
        site.classList.add('menu-clicked');
    }

    if (burger && verticalMenu) {
        burger.addEventListener('click', function () {
            verticalMenu.classList.toggle('vertical-menu-clicked');
            site.classList.toggle('menu-clicked');

            // Save the menu state in a cookie
            if (verticalMenu.classList.contains('vertical-menu-clicked')) {
                setCookie('menu_state', 'focus', 7); // Remember for 7 days
            } else {
                setCookie('menu_state', 'unfocus', 7); 
            }
        });
    }

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