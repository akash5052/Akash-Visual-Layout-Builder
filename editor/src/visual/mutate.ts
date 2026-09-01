import type {
  ColumnChild,
  InnerSection,
  VisualColumn,
  VisualDocument,
  VisualSection,
  VisualSelection,
  VisualWidget,
  WidgetType,
} from "./types";
import type { DevicePreview } from "../devicePreview";
import { clearWidgetStyleOverrides, patchWidgetStyleForDevice } from "./responsive";
import { cloneInnerSection, cloneSection, cloneWidget } from "./clone";
import { createColumn, createInnerSection, createSection, createWidget } from "./defaults";

function mapSections(doc: VisualDocument, fn: (section: VisualSection) => VisualSection): VisualDocument {
  return { ...doc, sections: doc.sections.map(fn) };
}

export function addSection(doc: VisualDocument, columns = 1, afterId?: string): VisualDocument {
  const section = createSection(columns, []);
  if (!afterId) {
    return { ...doc, sections: [...doc.sections, section] };
  }
  const idx = doc.sections.findIndex((s) => s.id === afterId);
  if (idx < 0) {
    return { ...doc, sections: [...doc.sections, section] };
  }
  const next = [...doc.sections];
  next.splice(idx + 1, 0, section);
  return { ...doc, sections: next };
}

export function removeSection(doc: VisualDocument, sectionId: string): VisualDocument {
  return { ...doc, sections: doc.sections.filter((s) => s.id !== sectionId) };
}

export function ensureDropTarget(
  doc: VisualDocument,
  selection: {
    kind: string;
    sectionId?: string;
    columnId?: string;
    innerSectionId?: string;
  } | null
): { doc: VisualDocument; target: { sectionId: string; columnId: string; innerSectionId?: string; index: number } } {
  let current = doc;
  let sectionId: string | undefined;
  let columnId: string | undefined;
  let innerSectionId: string | undefined;

  if (selection?.kind === "column") {
    sectionId = selection.sectionId;
    columnId = selection.columnId;
    innerSectionId = selection.innerSectionId;
  } else if (selection?.kind === "widget") {
    sectionId = selection.sectionId;
    columnId = selection.columnId;
    innerSectionId = selection.innerSectionId;
  } else if (selection?.kind === "section") {
    sectionId = selection.sectionId;
    columnId = current.sections.find((s) => s.id === sectionId)?.columns[0]?.id;
  } else if (selection?.kind === "inner-section") {
    sectionId = selection.sectionId;
    innerSectionId = selection.innerSectionId;
    const section = current.sections.find((s) => s.id === sectionId);
    const parent = section?.columns.find((c) => c.id === selection.columnId);
    const inner = parent?.children.find((c) => c.type === "inner-section" && c.id === innerSectionId);
    columnId = inner && inner.type === "inner-section" ? inner.columns[0]?.id : undefined;
  }

  if (!sectionId || !columnId) {
    if (current.sections.length === 0) {
      current = addSection(current, 1);
    }
    sectionId = current.sections[0]?.id;
    columnId = current.sections[0]?.columns[0]?.id;
  }

  if (!sectionId || !columnId) {
    throw new Error("Unable to resolve drop target");
  }

  return {
    doc: current,
    target: { sectionId, columnId, innerSectionId, index: 9999 },
  };
}

export function updateSection(
  doc: VisualDocument,
  sectionId: string,
  patch: Partial<VisualSection["settings"]>
): VisualDocument {
  return updateSectionSettingsForDevice(doc, sectionId, "desktop", patch);
}

export function updateSectionSettingsForDevice(
  doc: VisualDocument,
  sectionId: string,
  device: DevicePreview,
  patch: Partial<VisualSection["settings"]>
): VisualDocument {
  return mapSections(doc, (s) => {
    if (s.id !== sectionId) return s;
    if (device === "desktop") {
      return { ...s, settings: { ...s.settings, ...patch } };
    }
    const key = device;
    return {
      ...s,
      settingsOverrides: {
        ...(s.settingsOverrides || {}),
        [key]: { ...(s.settingsOverrides?.[key] || {}), ...patch },
      },
    };
  });
}

export function clearSectionSettingsOverrides(
  doc: VisualDocument,
  sectionId: string,
  device: Exclude<DevicePreview, "desktop">
): VisualDocument {
  return mapSections(doc, (s) => {
    if (s.id !== sectionId || !s.settingsOverrides?.[device]) return s;
    const next = { ...s.settingsOverrides };
    delete next[device];
    return { ...s, settingsOverrides: Object.keys(next).length ? next : undefined };
  });
}

const MAX_SECTION_COLUMNS = 12;

function rebalanceColumnWidths(count: number): number[] {
  const n = Math.max(1, count);
  const base = Math.floor(100 / n);
  return Array.from({ length: n }, (_, i) => (i === n - 1 ? 100 - base * (n - 1) : base));
}

export function setSectionColumns(doc: VisualDocument, sectionId: string, count: number): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    const n = Math.max(1, Math.min(MAX_SECTION_COLUMNS, count));
    const existing = section.columns;
    const widths = rebalanceColumnWidths(n);
    const columns: VisualColumn[] = widths.map((width, i) => {
      if (existing[i]) {
        return { ...existing[i], settings: { ...existing[i].settings, width } };
      }
      return createColumn(width);
    });
    // Merge leftover children into last column
    if (existing.length > n) {
      const extras = existing.slice(n).flatMap((c) => c.children);
      columns[n - 1] = {
        ...columns[n - 1],
        children: [...columns[n - 1].children, ...extras],
      };
    }
    return { ...section, columns };
  });
}

export function addSectionColumn(doc: VisualDocument, sectionId: string): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId || section.columns.length >= MAX_SECTION_COLUMNS) return section;
    const widths = rebalanceColumnWidths(section.columns.length + 1);
    const columns = section.columns.map((col, i) => ({
      ...col,
      settings: { ...col.settings, width: widths[i] },
    }));
    columns.push(createColumn(widths[widths.length - 1]));
    return { ...section, columns };
  });
}

export function removeSectionColumn(doc: VisualDocument, sectionId: string, columnId: string): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId || section.columns.length <= 1) return section;
    const index = section.columns.findIndex((col) => col.id === columnId);
    if (index < 0) return section;
    const removed = section.columns[index];
    const remaining = section.columns.filter((col) => col.id !== columnId);
    const targetIndex = index > 0 ? index - 1 : 0;
    remaining[targetIndex] = {
      ...remaining[targetIndex],
      children: [...remaining[targetIndex].children, ...removed.children],
    };
    const widths = rebalanceColumnWidths(remaining.length);
    const columns = remaining.map((col, i) => ({
      ...col,
      settings: { ...col.settings, width: widths[i] },
    }));
    return { ...section, columns };
  });
}

function mapInnerSection(
  doc: VisualDocument,
  sectionId: string,
  parentColumnId: string,
  innerSectionId: string,
  fn: (inner: Extract<ColumnChild, { type: "inner-section" }>) => Extract<ColumnChild, { type: "inner-section" }>
): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    return {
      ...section,
      columns: section.columns.map((col) => {
        if (col.id !== parentColumnId) return col;
        return {
          ...col,
          children: col.children.map((child) => {
            if (child.type !== "inner-section" || child.id !== innerSectionId) return child;
            return fn(child);
          }),
        };
      }),
    };
  });
}

export function addInnerSectionColumn(
  doc: VisualDocument,
  sectionId: string,
  parentColumnId: string,
  innerSectionId: string
): VisualDocument {
  return mapInnerSection(doc, sectionId, parentColumnId, innerSectionId, (inner) => {
    if (inner.columns.length >= MAX_SECTION_COLUMNS) return inner;
    const widths = rebalanceColumnWidths(inner.columns.length + 1);
    const columns = inner.columns.map((col, i) => ({
      ...col,
      settings: { ...col.settings, width: widths[i] },
    }));
    columns.push(createColumn(widths[widths.length - 1]));
    return { ...inner, columns };
  });
}

export function removeInnerSectionColumn(
  doc: VisualDocument,
  sectionId: string,
  parentColumnId: string,
  innerSectionId: string,
  columnId: string
): VisualDocument {
  return mapInnerSection(doc, sectionId, parentColumnId, innerSectionId, (inner) => {
    if (inner.columns.length <= 1) return inner;
    const index = inner.columns.findIndex((col) => col.id === columnId);
    if (index < 0) return inner;
    const removed = inner.columns[index];
    const remaining = inner.columns.filter((col) => col.id !== columnId);
    const targetIndex = index > 0 ? index - 1 : 0;
    remaining[targetIndex] = {
      ...remaining[targetIndex],
      children: [...remaining[targetIndex].children, ...removed.children],
    };
    const widths = rebalanceColumnWidths(remaining.length);
    const columns = remaining.map((col, i) => ({
      ...col,
      settings: { ...col.settings, width: widths[i] },
    }));
    return { ...inner, columns };
  });
}

/** Update column settings for top-level or inner columns. */
export function updateColumnSettings(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "column" }>,
  patch: Partial<VisualColumn["settings"]>
): VisualDocument {
  return updateColumnSettingsForDevice(doc, selection, "desktop", patch);
}

export function updateColumnSettingsForDevice(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "column" }>,
  device: DevicePreview,
  patch: Partial<VisualColumn["settings"]>
): VisualDocument {
  const patchColumn = (column: VisualColumn): VisualColumn => {
    if (column.id !== selection.columnId) return column;
    if (device === "desktop") {
      return { ...column, settings: { ...column.settings, ...patch } };
    }
    const key = device;
    return {
      ...column,
      settingsOverrides: {
        ...(column.settingsOverrides || {}),
        [key]: { ...(column.settingsOverrides?.[key] || {}), ...patch },
      },
    };
  };

  return mapSections(doc, (section) => {
    if (section.id !== selection.sectionId) return section;
    if (!selection.innerSectionId) {
      return { ...section, columns: section.columns.map(patchColumn) };
    }
    return {
      ...section,
      columns: section.columns.map((parentCol) => ({
        ...parentCol,
        children: parentCol.children.map((child) => {
          if (child.type !== "inner-section" || child.id !== selection.innerSectionId) return child;
          return { ...child, columns: child.columns.map(patchColumn) };
        }),
      })),
    };
  });
}

export function clearColumnSettingsOverrides(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "column" }>,
  device: Exclude<DevicePreview, "desktop">
): VisualDocument {
  const clearColumn = (column: VisualColumn): VisualColumn => {
    if (column.id !== selection.columnId || !column.settingsOverrides?.[device]) return column;
    const next = { ...column.settingsOverrides };
    delete next[device];
    return { ...column, settingsOverrides: Object.keys(next).length ? next : undefined };
  };

  return mapSections(doc, (section) => {
    if (section.id !== selection.sectionId) return section;
    if (!selection.innerSectionId) {
      return { ...section, columns: section.columns.map(clearColumn) };
    }
    return {
      ...section,
      columns: section.columns.map((parentCol) => ({
        ...parentCol,
        children: parentCol.children.map((child) => {
          if (child.type !== "inner-section" || child.id !== selection.innerSectionId) return child;
          return { ...child, columns: child.columns.map(clearColumn) };
        }),
      })),
    };
  });
}

export function addWidgetToColumn(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  type: WidgetType,
  innerSectionId?: string
): VisualDocument {
  const widget = createWidget(type);
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    if (!innerSectionId) {
      return {
        ...section,
        columns: section.columns.map((c) =>
          c.id === columnId ? { ...c, children: [...c.children, widget] } : c
        ),
      };
    }
    return {
      ...section,
      columns: section.columns.map((parentCol) => ({
        ...parentCol,
        children: parentCol.children.map((child) => {
          if (child.type !== "inner-section" || child.id !== innerSectionId) return child;
          return {
            ...child,
            columns: child.columns.map((c) =>
              c.id === columnId ? { ...c, children: [...c.children, widget] } : c
            ),
          };
        }),
      })),
    };
  });
}

export function addInnerSection(doc: VisualDocument, sectionId: string, columnId: string): VisualDocument {
  const inner = createInnerSection(1);
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    return {
      ...section,
      columns: section.columns.map((c) =>
        c.id === columnId ? { ...c, children: [...c.children, inner] } : c
      ),
    };
  });
}

export function updateWidget(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>,
  patch: Partial<VisualWidget>
): VisualDocument {
  const mergeWidget = (widget: VisualWidget): VisualWidget => ({
    ...widget,
    ...patch,
    style: patch.style ? { ...widget.style, ...patch.style } : widget.style,
    styleOverrides: patch.styleOverrides !== undefined ? patch.styleOverrides : widget.styleOverrides,
  } as VisualWidget);

  const patchChild = (child: ColumnChild): ColumnChild => {
    if (child.type === "inner-section") {
      if (selection.innerSectionId && child.id === selection.innerSectionId) {
        return {
          ...child,
          columns: child.columns.map((c) =>
            c.id === selection.columnId
              ? {
                  ...c,
                  children: c.children.map((w) =>
                    w.type !== "inner-section" && w.id === selection.widgetId ? mergeWidget(w as VisualWidget) : w
                  ),
                }
              : c
          ),
        };
      }
      return child;
    }
    if (!selection.innerSectionId && child.id === selection.widgetId) {
      return mergeWidget(child);
    }
    return child;
  };

  return mapSections(doc, (section) => {
    if (section.id !== selection.sectionId) return section;
    if (selection.innerSectionId) {
      return {
        ...section,
        columns: section.columns.map((col) => ({
          ...col,
          children: col.children.map(patchChild),
        })),
      };
    }
    return {
      ...section,
      columns: section.columns.map((col) =>
        col.id === selection.columnId ? { ...col, children: col.children.map(patchChild) } : col
      ),
    };
  });
}

export function updateWidgetStyleForDevice(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>,
  device: DevicePreview,
  partial: Partial<VisualWidget["style"]>
): VisualDocument {
  const widget = findWidget(doc, selection);
  if (!widget) return doc;
  return updateWidget(doc, selection, patchWidgetStyleForDevice(widget, device, partial));
}

export function clearWidgetStyleOverridesForDevice(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>,
  device: Exclude<DevicePreview, "desktop">
): VisualDocument {
  const widget = findWidget(doc, selection);
  if (!widget) return doc;
  return updateWidget(doc, selection, clearWidgetStyleOverrides(widget, device));
}

export function removeWidget(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>
): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== selection.sectionId) return section;
    if (selection.innerSectionId) {
      return {
        ...section,
        columns: section.columns.map((col) => ({
          ...col,
          children: col.children.map((child) => {
            if (child.type !== "inner-section" || child.id !== selection.innerSectionId) return child;
            return {
              ...child,
              columns: child.columns.map((c) =>
                c.id === selection.columnId
                  ? { ...c, children: c.children.filter((w) => w.id !== selection.widgetId) }
                  : c
              ),
            };
          }),
        })),
      };
    }
    return {
      ...section,
      columns: section.columns.map((col) =>
        col.id === selection.columnId
          ? { ...col, children: col.children.filter((c) => c.id !== selection.widgetId) }
          : col
      ),
    };
  });
}

export function removeInnerSection(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  innerSectionId: string
): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    return {
      ...section,
      columns: section.columns.map((col) =>
        col.id === columnId
          ? { ...col, children: col.children.filter((c) => c.id !== innerSectionId) }
          : col
      ),
    };
  });
}

export function findWidget(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>
): VisualWidget | null {
  const section = doc.sections.find((s) => s.id === selection.sectionId);
  if (!section) return null;
  if (selection.innerSectionId) {
    for (const col of section.columns) {
      for (const child of col.children) {
        if (child.type === "inner-section" && child.id === selection.innerSectionId) {
          const innerCol = child.columns.find((c) => c.id === selection.columnId);
          const widget = innerCol?.children.find((w) => w.type !== "inner-section" && w.id === selection.widgetId);
          return (widget as VisualWidget) || null;
        }
      }
    }
    return null;
  }
  const col = section.columns.find((c) => c.id === selection.columnId);
  const widget = col?.children.find((c) => c.type !== "inner-section" && c.id === selection.widgetId);
  return (widget as VisualWidget) || null;
}

export type DropTarget = {
  sectionId: string;
  columnId: string;
  innerSectionId?: string;
  index: number;
};

function getColumnChildren(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  innerSectionId?: string
): ColumnChild[] {
  const section = doc.sections.find((s) => s.id === sectionId);
  if (!section) return [];
  if (!innerSectionId) {
    return section.columns.find((c) => c.id === columnId)?.children || [];
  }
  for (const col of section.columns) {
    for (const child of col.children) {
      if (child.type === "inner-section" && child.id === innerSectionId) {
        return child.columns.find((c) => c.id === columnId)?.children || [];
      }
    }
  }
  return [];
}

function setColumnChildren(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  children: ColumnChild[],
  innerSectionId?: string
): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    if (!innerSectionId) {
      return {
        ...section,
        columns: section.columns.map((c) => (c.id === columnId ? { ...c, children } : c)),
      };
    }
    return {
      ...section,
      columns: section.columns.map((parentCol) => ({
        ...parentCol,
        children: parentCol.children.map((child) => {
          if (child.type !== "inner-section" || child.id !== innerSectionId) return child;
          return {
            ...child,
            columns: child.columns.map((c) => (c.id === columnId ? { ...c, children } : c)),
          };
        }),
      })),
    };
  });
}

export function insertWidgetAt(
  doc: VisualDocument,
  target: DropTarget,
  widget: VisualWidget
): VisualDocument {
  const children = [...getColumnChildren(doc, target.sectionId, target.columnId, target.innerSectionId)];
  const index = Math.max(0, Math.min(target.index, children.length));
  children.splice(index, 0, widget);
  return setColumnChildren(doc, target.sectionId, target.columnId, children, target.innerSectionId);
}

export function moveWidget(
  doc: VisualDocument,
  from: { sectionId: string; columnId: string; widgetId: string; innerSectionId?: string },
  to: DropTarget
): VisualDocument {
  const sourceChildren = getColumnChildren(doc, from.sectionId, from.columnId, from.innerSectionId);
  const widgetIndex = sourceChildren.findIndex((c) => c.id === from.widgetId);
  if (widgetIndex < 0) return doc;
  const widget = sourceChildren[widgetIndex];
  if (widget.type === "inner-section") return doc;

  const sameColumn =
    from.sectionId === to.sectionId &&
    from.columnId === to.columnId &&
    (from.innerSectionId || "") === (to.innerSectionId || "");

  if (sameColumn) {
    const next = [...sourceChildren];
    next.splice(widgetIndex, 1);
    let insertAt = to.index;
    if (widgetIndex < insertAt) insertAt -= 1;
    insertAt = Math.max(0, Math.min(insertAt, next.length));
    next.splice(insertAt, 0, widget);
    return setColumnChildren(doc, from.sectionId, from.columnId, next, from.innerSectionId);
  }

  const without = sourceChildren.filter((c) => c.id !== from.widgetId);
  let nextDoc = setColumnChildren(doc, from.sectionId, from.columnId, without, from.innerSectionId);
  const dest = [...getColumnChildren(nextDoc, to.sectionId, to.columnId, to.innerSectionId)];
  const index = Math.max(0, Math.min(to.index, dest.length));
  dest.splice(index, 0, widget);
  return setColumnChildren(nextDoc, to.sectionId, to.columnId, dest, to.innerSectionId);
}

export function reorderSection(doc: VisualDocument, fromIndex: number, toIndex: number): VisualDocument {
  if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) return doc;
  const sections = [...doc.sections];
  if (fromIndex >= sections.length || toIndex > sections.length) return doc;
  const [item] = sections.splice(fromIndex, 1);
  sections.splice(toIndex > fromIndex ? toIndex - 1 : toIndex, 0, item);
  return { ...doc, sections };
}

export function addWidgetAt(
  doc: VisualDocument,
  type: WidgetType,
  target: DropTarget
): VisualDocument {
  return insertWidgetAt(doc, target, createWidget(type));
}

export function findSection(doc: VisualDocument, sectionId: string): VisualSection | null {
  return doc.sections.find((s) => s.id === sectionId) || null;
}

export function findInnerSection(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  innerSectionId: string
): InnerSection | null {
  const section = findSection(doc, sectionId);
  if (!section) return null;
  const column = section.columns.find((c) => c.id === columnId);
  if (!column) return null;
  const inner = column.children.find((c) => c.type === "inner-section" && c.id === innerSectionId);
  return inner?.type === "inner-section" ? inner : null;
}

export function duplicateSection(doc: VisualDocument, sectionId: string): VisualDocument {
  const idx = doc.sections.findIndex((s) => s.id === sectionId);
  if (idx < 0) return doc;
  const clone = cloneSection(doc.sections[idx]);
  const sections = [...doc.sections];
  sections.splice(idx + 1, 0, clone);
  return { ...doc, sections };
}

export function duplicateInnerSection(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  innerSectionId: string
): VisualDocument {
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    return {
      ...section,
      columns: section.columns.map((col) => {
        if (col.id !== columnId) return col;
        const index = col.children.findIndex((c) => c.id === innerSectionId);
        if (index < 0) return col;
        const child = col.children[index];
        if (child.type !== "inner-section") return col;
        const clone = cloneInnerSection(child);
        const children = [...col.children];
        children.splice(index + 1, 0, clone);
        return { ...col, children };
      }),
    };
  });
}

export function duplicateWidget(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>
): VisualDocument {
  const widget = findWidget(doc, selection);
  if (!widget) return doc;
  const children = getColumnChildren(doc, selection.sectionId, selection.columnId, selection.innerSectionId);
  const index = children.findIndex((c) => c.id === selection.widgetId);
  if (index < 0) return doc;
  return insertWidgetAt(
    doc,
    {
      sectionId: selection.sectionId,
      columnId: selection.columnId,
      innerSectionId: selection.innerSectionId,
      index: index + 1,
    },
    cloneWidget(widget)
  );
}

export function pasteSection(doc: VisualDocument, afterSectionId: string | undefined, section: VisualSection): VisualDocument {
  const clone = cloneSection(section);
  if (!afterSectionId) {
    return { ...doc, sections: [...doc.sections, clone] };
  }
  const idx = doc.sections.findIndex((s) => s.id === afterSectionId);
  if (idx < 0) {
    return { ...doc, sections: [...doc.sections, clone] };
  }
  const sections = [...doc.sections];
  sections.splice(idx + 1, 0, clone);
  return { ...doc, sections };
}

export function pasteInnerSection(
  doc: VisualDocument,
  sectionId: string,
  columnId: string,
  afterInnerSectionId: string | undefined,
  inner: InnerSection
): VisualDocument {
  const clone = cloneInnerSection(inner);
  return mapSections(doc, (section) => {
    if (section.id !== sectionId) return section;
    return {
      ...section,
      columns: section.columns.map((col) => {
        if (col.id !== columnId) return col;
        if (!afterInnerSectionId) {
          return { ...col, children: [...col.children, clone] };
        }
        const index = col.children.findIndex((c) => c.id === afterInnerSectionId);
        if (index < 0) {
          return { ...col, children: [...col.children, clone] };
        }
        const children = [...col.children];
        children.splice(index + 1, 0, clone);
        return { ...col, children };
      }),
    };
  });
}

export function pasteWidget(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>,
  widget: VisualWidget
): VisualDocument {
  const children = getColumnChildren(doc, selection.sectionId, selection.columnId, selection.innerSectionId);
  const index = children.findIndex((c) => c.id === selection.widgetId);
  const insertAt = index >= 0 ? index + 1 : children.length;
  return insertWidgetAt(
    doc,
    {
      sectionId: selection.sectionId,
      columnId: selection.columnId,
      innerSectionId: selection.innerSectionId,
      index: insertAt,
    },
    cloneWidget(widget)
  );
}

export function resetWidgetStyle(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>
): VisualDocument {
  const widget = findWidget(doc, selection);
  if (!widget) return doc;
  const defaults = createWidget(widget.type);
  return updateWidget(doc, selection, { style: defaults.style, styleOverrides: undefined });
}

export function pasteWidgetStyle(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "widget" }>,
  source: VisualWidget
): VisualDocument {
  return updateWidget(doc, selection, {
    style: { ...source.style },
    styleOverrides: source.styleOverrides ? JSON.parse(JSON.stringify(source.styleOverrides)) : undefined,
  });
}
