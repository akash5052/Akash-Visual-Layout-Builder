import { normalizeWidget } from "./defaults";
import type { ColumnChild, InnerSection, VisualSection, VisualWidget } from "./types";
import { uid } from "./types";

function cloneWidget(widget: VisualWidget): VisualWidget {
  const raw = JSON.parse(JSON.stringify(widget)) as VisualWidget;
  return normalizeWidget({ ...raw, id: uid("w") });
}

function cloneColumnChild(child: ColumnChild): ColumnChild {
  if (child.type === "inner-section") {
    return cloneInnerSection(child);
  }
  return cloneWidget(child);
}

export function cloneInnerSection(inner: InnerSection): InnerSection {
  const raw = JSON.parse(JSON.stringify(inner)) as InnerSection;
  return {
    ...raw,
    id: uid("inner"),
    columns: raw.columns.map((col) => ({
      ...col,
      id: uid("col"),
      children: col.children.map(cloneColumnChild),
    })),
  };
}

export function cloneSection(section: VisualSection): VisualSection {
  const raw = JSON.parse(JSON.stringify(section)) as VisualSection;
  return {
    ...raw,
    id: uid("sec"),
    columns: raw.columns.map((col) => ({
      ...col,
      id: uid("col"),
      children: col.children.map(cloneColumnChild),
    })),
  };
}

export { cloneWidget };
