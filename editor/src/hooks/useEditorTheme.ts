import { useCallback, useEffect, useState } from "react";

export type EditorTheme = "light" | "dark" | "system";
export type ResolvedTheme = "light" | "dark";

const STORAGE_KEY = "akash-visual-layout-builder-editor-theme";

function resolveTheme(theme: EditorTheme): ResolvedTheme {
  if (theme === "light") return "light";
  if (theme === "dark") return "dark";
  return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

function readStoredTheme(): EditorTheme {
  const stored = localStorage.getItem(STORAGE_KEY);
  if (stored === "light" || stored === "dark" || stored === "system") {
    return stored;
  }
  return "system";
}

export function useEditorTheme() {
  const [theme, setThemeState] = useState<EditorTheme>(readStoredTheme);
  const [resolved, setResolved] = useState<ResolvedTheme>(() => resolveTheme(readStoredTheme()));

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, theme);
    setResolved(resolveTheme(theme));

    document.body.classList.remove("akash-visual-layout-builder-theme-light", "akash-visual-layout-builder-theme-dark");
    document.body.classList.add(resolveTheme(theme) === "light" ? "akash-visual-layout-builder-theme-light" : "akash-visual-layout-builder-theme-dark");

    if (theme !== "system") return;

    const mq = window.matchMedia("(prefers-color-scheme: dark)");
    const onChange = () => {
      const next = resolveTheme("system");
      setResolved(next);
      document.body.classList.remove("akash-visual-layout-builder-theme-light", "akash-visual-layout-builder-theme-dark");
      document.body.classList.add(next === "light" ? "akash-visual-layout-builder-theme-light" : "akash-visual-layout-builder-theme-dark");
    };
    mq.addEventListener("change", onChange);
    return () => mq.removeEventListener("change", onChange);
  }, [theme]);

  const setTheme = useCallback((next: EditorTheme) => {
    setThemeState(next);
  }, []);

  return { theme, setTheme, resolved };
}
