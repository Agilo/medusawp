/**
 * File primary-navigation.js.
 *
 * Required to open and close the mobile navigation.
 */

/**
 * Toggle an attribute's value
 *
 * @since MedusaWP Demo 1.0
 *
 * @param {Element} el - The element.
 * @param {boolean} withListeners - Whether we want to add/remove listeners or not.
 */
function medusawpDemoToggleAriaExpanded(el, withListeners) {
  if ("true" !== el.getAttribute("aria-expanded")) {
    el.setAttribute("aria-expanded", "true");
    medusawpDemoSubmenuPosition(el.parentElement);
    if (withListeners) {
      document.addEventListener(
        "click",
        medusawpDemoCollapseMenuOnClickOutside
      );
    }
  } else {
    el.setAttribute("aria-expanded", "false");
    if (withListeners) {
      document.removeEventListener(
        "click",
        medusawpDemoCollapseMenuOnClickOutside
      );
    }
  }
}

function medusawpDemoCollapseMenuOnClickOutside(event) {
  if (!document.getElementById("site-navigation").contains(event.target)) {
    document
      .getElementById("site-navigation")
      .querySelectorAll(".sub-menu-toggle")
      .forEach(function (button) {
        button.setAttribute("aria-expanded", "false");
      });
  }
}

/**
 * Changes the position of submenus so they always fit the screen horizontally.
 *
 * @since MedusaWP Demo 1.0
 *
 * @param {Element} li - The li element.
 */
function medusawpDemoSubmenuPosition(li) {
  var subMenu = li.querySelector("ul.sub-menu"),
    rect,
    right,
    left,
    windowWidth;

  if (!subMenu) {
    return;
  }

  rect = subMenu.getBoundingClientRect();
  right = Math.round(rect.right);
  left = Math.round(rect.left);
  windowWidth = Math.round(window.innerWidth);

  if (right > windowWidth) {
    subMenu.classList.add("submenu-reposition-right");
  } else if (document.body.classList.contains("rtl") && left < 0) {
    subMenu.classList.add("submenu-reposition-left");
  }
}

/**
 * Handle clicks on submenu toggles.
 *
 * @since MedusaWP Demo 1.0
 *
 * @param {Element} el - The element.
 */
function medusawpDemoExpandSubMenu(el) {
  // jshint ignore:line
  // Close other expanded items.
  el.closest("nav")
    .querySelectorAll(".sub-menu-toggle")
    .forEach(function (button) {
      if (button !== el) {
        button.setAttribute("aria-expanded", "false");
      }
    });

  // Toggle aria-expanded on the button.
  medusawpDemoToggleAriaExpanded(el, true);

  // On tab-away collapse the menu.
  el.parentNode
    .querySelectorAll("ul > li:last-child > a")
    .forEach(function (linkEl) {
      linkEl.addEventListener("blur", function (event) {
        if (!el.parentNode.contains(event.relatedTarget)) {
          el.setAttribute("aria-expanded", "false");
        }
      });
    });
}

(function () {
  /**
   * Menu Toggle Behaviors
   *
   * @since MedusaWP Demo 1.0
   *
   * @param {string} id - The ID.
   */
  var navMenu = function (id) {
    var wrapper = document.body, // this is the element to which a CSS class is added when a mobile nav menu is open
      mobileButton = document.getElementById(id + "-mobile-menu"),
      navMenuEl = document.getElementById("site-navigation");

    // If there's no nav menu, none of this is necessary.
    if (!navMenuEl) {
      return;
    }

    if (mobileButton) {
      mobileButton.onclick = function () {
        wrapper.classList.toggle(id + "-navigation-open");
        wrapper.classList.toggle("lock-scrolling");
        medusawpDemoToggleAriaExpanded(mobileButton);
        mobileButton.focus();
      };
    }

    /**
     * Trap keyboard navigation in the menu modal.
     * Adapted from Twenty Twenty.
     *
     * @since MedusaWP Demo 1.0
     */
    document.addEventListener("keydown", function (event) {
      var modal,
        elements,
        selectors,
        lastEl,
        firstEl,
        activeEl,
        tabKey,
        shiftKey,
        escKey;
      if (!wrapper.classList.contains(id + "-navigation-open")) {
        return;
      }

      modal = document.querySelector("." + id + "-navigation");
      selectors = "input, a, button";
      elements = modal.querySelectorAll(selectors);
      elements = Array.prototype.slice.call(elements);
      tabKey = event.keyCode === 9;
      shiftKey = event.shiftKey;
      escKey = event.keyCode === 27;
      activeEl = document.activeElement; // eslint-disable-line @wordpress/no-global-active-element
      lastEl = elements[elements.length - 1];
      firstEl = elements[0];

      if (escKey) {
        event.preventDefault();
        wrapper.classList.remove(id + "-navigation-open", "lock-scrolling");
        medusawpDemoToggleAriaExpanded(mobileButton);
        mobileButton.focus();
      }

      if (!shiftKey && tabKey && lastEl === activeEl) {
        event.preventDefault();
        firstEl.focus();
      }

      if (shiftKey && tabKey && firstEl === activeEl) {
        event.preventDefault();
        lastEl.focus();
      }

      // If there are no elements in the menu, don't move the focus
      if (tabKey && firstEl === lastEl) {
        event.preventDefault();
      }
    });

    /**
     * Close menu and scroll to anchor when an anchor link is clicked.
     * Adapted from Twenty Twenty.
     *
     * @since MedusaWP Demo 1.1
     */
    document
      .getElementById("site-navigation")
      .addEventListener("click", function (event) {
        // If target onclick is <a> with # within the href attribute
        if (event.target.hash) {
          wrapper.classList.remove(id + "-navigation-open", "lock-scrolling");
          medusawpDemoToggleAriaExpanded(mobileButton);
          // Wait 550 and scroll to the anchor.
          setTimeout(function () {
            var anchor = document.getElementById(event.target.hash.slice(1));
            if (anchor) {
              anchor.scrollIntoView();
            }
          }, 550);
        }
      });

    navMenuEl
      .querySelectorAll(".menu-wrapper > .menu-item-has-children")
      .forEach(function (li) {
        li.addEventListener("mouseenter", function () {
          this.querySelector(".sub-menu-toggle").setAttribute(
            "aria-expanded",
            "true"
          );
          medusawpDemoSubmenuPosition(li);
        });
        li.addEventListener("mouseleave", function () {
          this.querySelector(".sub-menu-toggle").setAttribute(
            "aria-expanded",
            "false"
          );
        });
      });
  };

  window.addEventListener("load", function () {
    new navMenu("primary");
  });
})();
