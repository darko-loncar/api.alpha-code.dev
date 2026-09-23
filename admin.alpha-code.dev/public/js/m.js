
// Navigation
function initMenu() {
const menu = document.querySelector("#menu");
const items = menu.querySelectorAll("li");
const mq = window.matchMedia("(min-width: 961px)");

menu.addEventListener("click", (event) => {
  const link = event.target.closest("#menu a");
  if (!link) return;

  if (window.innerWidth > 960) return;

  const item = link.closest("li");
  if (!item) return;

  const hasSubmenu = item.querySelector("ul");

  // If it has submenu → toggle instead of navigating
  if (hasSubmenu) {
    event.preventDefault();
    item.classList.toggle("open");
  }
});
}

// Boot App
async function initApp() {
  initMenu();
}

document.addEventListener("DOMContentLoaded", initApp);