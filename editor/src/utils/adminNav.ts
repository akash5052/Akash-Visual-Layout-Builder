/** Update the current admin URL query params without reloading the page. */
export function replaceAdminQuery(params: Record<string, string | null | undefined>) {
  const url = new URL(window.location.href);
  for (const [key, value] of Object.entries(params)) {
    if (value === null || value === undefined) {
      url.searchParams.delete(key);
    } else {
      url.searchParams.set(key, value);
    }
  }
  window.history.replaceState({}, "", url.toString());
}

/** Prevent navigation when the target matches the current admin screen URL. */
export function shouldSkipAdminNavigation(targetHref: string): boolean {
  try {
    const current = new URL(window.location.href);
    const target = new URL(targetHref, window.location.origin);
    return (
      current.pathname === target.pathname &&
      current.searchParams.get("page") === target.searchParams.get("page") &&
      current.searchParams.get("page_id") === target.searchParams.get("page_id")
    );
  } catch {
    return false;
  }
}
