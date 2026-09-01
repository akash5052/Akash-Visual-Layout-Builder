import type { ReactElement } from "react";
import type { DevicePreview } from "../devicePreview";
import { DEVICE_PREVIEW_LABELS } from "../devicePreview";

interface DeviceSwitcherProps {
  value: DevicePreview;
  onChange: (device: DevicePreview) => void;
  className?: string;
}

function DesktopIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect x="3" y="4" width="18" height="12" rx="1.5" stroke="currentColor" strokeWidth="1.75" />
      <path d="M8 20h8M12 16v4" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  );
}

function TabletIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect x="6" y="3" width="12" height="18" rx="2" stroke="currentColor" strokeWidth="1.75" />
      <circle cx="12" cy="17.5" r="0.9" fill="currentColor" />
    </svg>
  );
}

function MobileIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <rect x="8" y="2.5" width="8" height="19" rx="2" stroke="currentColor" strokeWidth="1.75" />
      <path d="M11 18.5h2" stroke="currentColor" strokeWidth="1.75" strokeLinecap="round" />
    </svg>
  );
}

const DEVICES: { id: DevicePreview; Icon: () => ReactElement }[] = [
  { id: "desktop", Icon: DesktopIcon },
  { id: "tablet", Icon: TabletIcon },
  { id: "mobile", Icon: MobileIcon },
];

export function DeviceSwitcher({ value, onChange, className = "" }: DeviceSwitcherProps) {
  return (
    <div className={`epb-device-switch ${className}`.trim()} role="group" aria-label="Responsive preview">
      {DEVICES.map(({ id, Icon }) => (
        <button
          key={id}
          type="button"
          className={`epb-device-switch__btn ${value === id ? "is-active" : ""}`}
          title={DEVICE_PREVIEW_LABELS[id]}
          aria-label={DEVICE_PREVIEW_LABELS[id]}
          aria-pressed={value === id}
          onClick={() => onChange(id)}
        >
          <Icon />
        </button>
      ))}
    </div>
  );
}
