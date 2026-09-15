export type StructureKind = "section" | "column" | "inner-section";

interface StructureIconProps {
  kind: StructureKind;
  title: string;
}

export function StructureIcon({ kind, title }: StructureIconProps) {
  return (
    <span className={`akash-visual-layout-builder-visual__structure-icon akash-visual-layout-builder-visual__structure-icon--${kind}`} title={title} aria-label={title}>
      {kind === "section" && (
        <svg viewBox="0 0 20 14" width="16" height="12" fill="none" aria-hidden="true">
          <rect x="1" y="1" width="18" height="12" rx="1.5" stroke="currentColor" strokeWidth="1.6" />
        </svg>
      )}
      {kind === "column" && (
        <svg viewBox="0 0 20 14" width="16" height="12" fill="none" aria-hidden="true">
          <rect x="1" y="1" width="7" height="12" rx="1" stroke="currentColor" strokeWidth="1.6" />
          <rect x="12" y="1" width="7" height="12" rx="1" stroke="currentColor" strokeWidth="1.6" />
        </svg>
      )}
      {kind === "inner-section" && (
        <svg viewBox="0 0 20 14" width="16" height="12" fill="none" aria-hidden="true">
          <rect x="1" y="1" width="18" height="12" rx="1.5" stroke="currentColor" strokeWidth="1.4" strokeDasharray="2.5 2" />
          <rect x="5" y="4" width="4" height="6" rx="0.75" stroke="currentColor" strokeWidth="1.3" />
          <rect x="11" y="4" width="4" height="6" rx="0.75" stroke="currentColor" strokeWidth="1.3" />
        </svg>
      )}
    </span>
  );
}