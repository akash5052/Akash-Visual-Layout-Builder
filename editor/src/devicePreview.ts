export type DevicePreview = "desktop" | "tablet" | "mobile";

/** Max viewport width (px) where tablet / mobile overrides apply on the live site. */
export const DEVICE_BREAKPOINTS = {
  tablet: 1024,
  mobile: 767,
} as const;

export const DEVICE_PREVIEW_WIDTHS: Record<DevicePreview, string> = {
  desktop: "100%",
  tablet: "768px",
  mobile: "375px",
};

export const DEVICE_PREVIEW_LABELS: Record<DevicePreview, string> = {
  desktop: "Desktop",
  tablet: "Tablet",
  mobile: "Mobile",
};
