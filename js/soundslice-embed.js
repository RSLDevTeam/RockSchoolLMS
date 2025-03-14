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