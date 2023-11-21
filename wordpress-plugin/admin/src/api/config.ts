const root =
  "medusawp" in window &&
  typeof window.medusawp === "object" &&
  window.medusawp &&
  "rest" in window.medusawp &&
  typeof window.medusawp.rest === "object" &&
  window.medusawp.rest &&
  "root" in window.medusawp.rest &&
  typeof window.medusawp.rest.root === "string" &&
  window.medusawp.rest.root
    ? window.medusawp.rest.root
    : "/wp-json";
const nonce =
  "medusawp" in window &&
  typeof window.medusawp === "object" &&
  window.medusawp &&
  "rest" in window.medusawp &&
  typeof window.medusawp.rest === "object" &&
  window.medusawp.rest &&
  "nonce" in window.medusawp.rest &&
  typeof window.medusawp.rest.nonce === "string" &&
  window.medusawp.rest.nonce
    ? window.medusawp.rest.nonce
    : "";

export { root, nonce };
