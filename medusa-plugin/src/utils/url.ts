export function isValidUrl(url: unknown) {
  if (typeof url !== "string" || url.length < 1) {
    return false;
  }

  try {
    new URL(url);
    return true;
  } catch (err) {}

  return false;
}
