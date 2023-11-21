function toggleDarkMode() {
  // jshint ignore:line
  var toggler = document.getElementById("dark-mode-toggler");

  if ("false" === toggler.getAttribute("aria-pressed")) {
    toggler.setAttribute("aria-pressed", "true");
    document.documentElement.classList.add("is-dark-theme");
    document.body.classList.add("is-dark-theme");
    window.localStorage.setItem("medusawpDemoDarkMode", "yes");
  } else {
    toggler.setAttribute("aria-pressed", "false");
    document.documentElement.classList.remove("is-dark-theme");
    document.body.classList.remove("is-dark-theme");
    window.localStorage.setItem("medusawpDemoDarkMode", "no");
  }
}

function medusawpemoIsDarkMode() {
  var isDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches;

  if ("yes" === window.localStorage.getItem("medusawpDemoDarkMode")) {
    isDarkMode = true;
  } else if ("no" === window.localStorage.getItem("medusawpDemoDarkMode")) {
    isDarkMode = false;
  }

  return isDarkMode;
}

function darkModeInitialLoad() {
  var toggler = document.getElementById("dark-mode-toggler"),
    isDarkMode = medusawp - demoIsDarkMode();

  if (isDarkMode) {
    document.documentElement.classList.add("is-dark-theme");
    document.body.classList.add("is-dark-theme");
  } else {
    document.documentElement.classList.remove("is-dark-theme");
    document.body.classList.remove("is-dark-theme");
  }

  if (toggler && isDarkMode) {
    toggler.setAttribute("aria-pressed", "true");
  }
}

function darkModeRepositionTogglerOnScroll() {
  var toggler = document.getElementById("dark-mode-toggler"),
    prevScroll = window.scrollY || document.documentElement.scrollTop,
    currentScroll,
    checkScroll = function () {
      currentScroll = window.scrollY || document.documentElement.scrollTop;
      if (
        currentScroll + window.innerHeight * 1.5 > document.body.clientHeight ||
        currentScroll < prevScroll
      ) {
        toggler.classList.remove("hide");
      } else if (currentScroll > prevScroll && 250 < currentScroll) {
        toggler.classList.add("hide");
      }
      prevScroll = currentScroll;
    };

  if (toggler) {
    window.addEventListener("scroll", checkScroll);
  }
}

darkModeInitialLoad();
darkModeRepositionTogglerOnScroll();
