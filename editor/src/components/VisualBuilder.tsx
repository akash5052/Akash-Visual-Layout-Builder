import { useEffect, useMemo, useRef, useState, type CSSProperties, type DragEvent, type MouseEvent, type ReactNode } from "react";

import type { DevicePreview } from "../devicePreview";
import { DEVICE_PREVIEW_LABELS, DEVICE_PREVIEW_WIDTHS } from "../devicePreview";
import type { PageCode } from "../types";
import { openImagePicker } from "../utils/mediaPicker";
import { compileVisualDocument } from "../visual/compile";
import {
  SECTION_PRESETS,
  WIDGET_CATEGORIES,
  WIDGET_ICONS,
  createEmptyDocument,
  normalizeDocument,
  widgetLabel,
} from "../visual/defaults";
import { compileWidgetHtml } from "../visual/compileWidgets";
import { getBuilderClipboard, setBuilderClipboard } from "../visual/clipboard";
import {
  addInnerSection,
  addInnerSectionColumn,
  addSection,
  addSectionColumn,
  addWidgetAt,
  duplicateInnerSection,
  duplicateSection,
  duplicateWidget,
  ensureDropTarget,
  findInnerSection,
  findSection,
  findWidget,
  pasteInnerSection,
  pasteSection,
  pasteWidget,
  pasteWidgetStyle,
  resetWidgetStyle,
  clearColumnSettingsOverrides,
  clearSectionSettingsOverrides,
  clearWidgetStyleOverridesForDevice,
  moveWidget,
  removeInnerSection,
  removeInnerSectionColumn,
  removeSection,
  removeSectionColumn,
  removeWidget,
  updateColumnSettingsForDevice,
  updateSectionSettingsForDevice,
  updateWidget,
  updateWidgetStyleForDevice,
  type DropTarget,
} from "../visual/mutate";
import {
  resolveColumnSettings,
  resolveSectionSettings,
  resolveWidgetStyle,
} from "../visual/responsive";
import type {
  VisualDocument,
  VisualSelection,
  VisualWidget,
  WidgetStyle,
  WidgetType,
} from "../visual/types";
import {
  DEFAULT_COLUMN_SETTINGS,
  DEFAULT_WIDGET_STYLE,
  collapseCssBox,
  expandCssBox,
} from "../visual/types";
import { AdvancedWidgetFields } from "./AdvancedWidgetFields";
import { PostsWidgetPreview } from "./PostsWidgetFields";
import { BuilderContextMenu, type ContextMenuItem } from "./BuilderContextMenu";
import { DeviceSwitcher } from "./DeviceSwitcher";
import { StructureIcon, type StructureKind } from "./StructureIcons";
import { UrlSuggestField } from "./UrlSuggestField";

interface VisualBuilderProps {
  document: VisualDocument;
  onChange: (doc: VisualDocument, code: PageCode) => void;
  devicePreview?: DevicePreview;
  onDevicePreviewChange?: (device: DevicePreview) => void;
  pageId?: number;
}

type DragPayload =
  | { kind: "new"; type: WidgetType }
  | {
      kind: "move";
      sectionId: string;
      columnId: string;
      widgetId: string;
      innerSectionId?: string;
    };

type InspectorTab = "content" | "style";

const DND_MIME = "application/x-epb-widget";

function emit(doc: VisualDocument, onChange: VisualBuilderProps["onChange"]) {
  onChange(doc, compileVisualDocument(doc));
}

function readDragPayload(e: DragEvent): DragPayload | null {
  try {
    const raw = e.dataTransfer.getData(DND_MIME) || e.dataTransfer.getData("text/plain");
    if (!raw) return null;
    return JSON.parse(raw) as DragPayload;
  } catch {
    return null;
  }
}

function writeDragPayload(e: DragEvent, payload: DragPayload) {
  const json = JSON.stringify(payload);
  e.dataTransfer.setData(DND_MIME, json);
  e.dataTransfer.setData("text/plain", json);
  e.dataTransfer.effectAllowed = payload.kind === "new" ? "copy" : "move";
}

type ContextTarget =
  | { kind: "section"; sectionId: string }
  | { kind: "inner-section"; sectionId: string; columnId: string; innerSectionId: string }
  | {
      kind: "widget";
      sectionId: string;
      columnId: string;
      widgetId: string;
      innerSectionId?: string;
      parentColumnId?: string;
    };

function selectionFromTarget(target: ContextTarget): VisualSelection {
  if (target.kind === "section") return { kind: "section", sectionId: target.sectionId };
  if (target.kind === "inner-section") {
    return {
      kind: "inner-section",
      sectionId: target.sectionId,
      columnId: target.columnId,
      innerSectionId: target.innerSectionId,
    };
  }
  return {
    kind: "widget",
    sectionId: target.sectionId,
    columnId: target.columnId,
    widgetId: target.widgetId,
    innerSectionId: target.innerSectionId,
    parentColumnId: target.parentColumnId,
  };
}

function shortcutMod(): string {
  if (typeof navigator !== "undefined" && /Mac|iPhone|iPad/i.test(navigator.platform)) return "⌘";
  return "Ctrl+";
}

export function VisualBuilder({
  document,
  onChange,
  devicePreview = "desktop",
  onDevicePreviewChange,
  pageId = 0,
}: VisualBuilderProps) {
  const [selection, setSelection] = useState<VisualSelection>(null);
  const [inspectorTab, setInspectorTab] = useState<InspectorTab>("content");
  const [dropKey, setDropKey] = useState<string | null>(null);
  const [dragging, setDragging] = useState(false);
  const [showStructurePicker, setShowStructurePicker] = useState(false);
  const [contextMenu, setContextMenu] = useState<{ x: number; y: number; target: ContextTarget } | null>(null);
  const [railOpen, setRailOpen] = useState(true);
  const [inspectorOpen, setInspectorOpen] = useState(true);
  const doc = document ?? createEmptyDocument();
  const docRef = useRef(doc);
  docRef.current = doc;

  useEffect(() => {
    ensureVisualEditorFonts();
  }, []);

  const selectedSection = useMemo(() => {
    if (!selection || selection.kind !== "section") return null;
    return doc.sections.find((s) => s.id === selection.sectionId) || null;
  }, [doc, selection]);

  const selectedWidget = useMemo(() => {
    if (!selection || selection.kind !== "widget") return null;
    return findWidget(doc, selection);
  }, [doc, selection]);

  const apply = (next: VisualDocument) => {
    const normalized = normalizeDocument(next);
    docRef.current = normalized;
    emit(normalized, onChange);
  };

  const selectionMeta = useMemo(() => {
    if (!selection) return { title: "Nothing selected", subtitle: "Click a section, column, or widget to edit it." };
    if (selection.kind === "section") {
      const index = doc.sections.findIndex((s) => s.id === selection.sectionId) + 1;
      return { title: `Section ${index || ""}`.trim(), subtitle: "Layout, background, and spacing" };
    }
    if (selection.kind === "column") {
      return { title: "Column", subtitle: "Width, padding, and background" };
    }
    if (selection.kind === "inner-section") {
      return { title: "Inner section", subtitle: "Nested columns inside a column" };
    }
    if (selection.kind === "widget" && selectedWidget) {
      return { title: widgetLabel(selectedWidget.type), subtitle: "Edit content and style" };
    }
    return { title: "Settings", subtitle: "" };
  }, [doc.sections, selection, selectedWidget]);

  useEffect(() => {
    const isEditableTarget = (target: EventTarget | null) => {
      const el = target as HTMLElement | null;
      if (!el) return false;
      const tag = el.tagName;
      return tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || el.isContentEditable;
    };

    const handleCopyTarget = (target: ContextTarget) => {
      const current = docRef.current;
      if (target.kind === "section") {
        const section = findSection(current, target.sectionId);
        if (section) setBuilderClipboard({ kind: "section", section: JSON.parse(JSON.stringify(section)) });
        return;
      }
      if (target.kind === "inner-section") {
        const inner = findInnerSection(current, target.sectionId, target.columnId, target.innerSectionId);
        if (inner) setBuilderClipboard({ kind: "inner-section", inner: JSON.parse(JSON.stringify(inner)) });
        return;
      }
      const widget = findWidget(current, target);
      if (widget) setBuilderClipboard({ kind: "widget", widget: JSON.parse(JSON.stringify(widget)) });
    };

    const handleDuplicateTarget = (target: ContextTarget) => {
      const current = docRef.current;
      if (target.kind === "section") apply(duplicateSection(current, target.sectionId));
      else if (target.kind === "inner-section") {
        apply(duplicateInnerSection(current, target.sectionId, target.columnId, target.innerSectionId));
      } else apply(duplicateWidget(current, target));
    };

    const handlePasteTarget = (target: ContextTarget) => {
      const clip = getBuilderClipboard();
      if (!clip) return;
      const current = docRef.current;
      if (clip.kind === "section" && target.kind === "section") {
        apply(pasteSection(current, target.sectionId, clip.section));
      } else if (clip.kind === "inner-section" && target.kind === "inner-section") {
        apply(pasteInnerSection(current, target.sectionId, target.columnId, target.innerSectionId, clip.inner));
      } else if (clip.kind === "widget" && target.kind === "widget") {
        apply(pasteWidget(current, target, clip.widget));
      }
    };

    const onKeyDown = (e: KeyboardEvent) => {
      if (isEditableTarget(e.target)) return;

      const mod = e.metaKey || e.ctrlKey;
      const activeTarget: ContextTarget | null =
        selection?.kind === "section"
          ? { kind: "section", sectionId: selection.sectionId }
          : selection?.kind === "inner-section"
            ? {
                kind: "inner-section",
                sectionId: selection.sectionId,
                columnId: selection.columnId,
                innerSectionId: selection.innerSectionId,
              }
            : selection?.kind === "widget"
              ? {
                  kind: "widget",
                  sectionId: selection.sectionId,
                  columnId: selection.columnId,
                  widgetId: selection.widgetId,
                  innerSectionId: selection.innerSectionId,
                  parentColumnId: selection.parentColumnId,
                }
              : null;

      if (mod && activeTarget) {
        const key = e.key.toLowerCase();
        if (key === "d") {
          e.preventDefault();
          handleDuplicateTarget(activeTarget);
          return;
        }
        if (key === "c") {
          e.preventDefault();
          handleCopyTarget(activeTarget);
          return;
        }
        if (key === "v" && e.shiftKey && activeTarget.kind === "widget") {
          const clip = getBuilderClipboard();
          if (clip?.kind === "widget") {
            e.preventDefault();
            apply(pasteWidgetStyle(docRef.current, activeTarget, clip.widget));
          }
          return;
        }
        if (key === "v" && !e.shiftKey) {
          e.preventDefault();
          handlePasteTarget(activeTarget);
          return;
        }
      }

      if (e.key !== "Delete" && e.key !== "Backspace") return;
      if (!selection) return;
      e.preventDefault();
      if (selection.kind === "widget") {
        apply(removeWidget(docRef.current, selection));
        setSelection(null);
      } else if (selection.kind === "section") {
        apply(removeSection(docRef.current, selection.sectionId));
        setSelection(null);
      } else if (selection.kind === "inner-section") {
        apply(removeInnerSection(docRef.current, selection.sectionId, selection.columnId, selection.innerSectionId));
        setSelection(null);
      }
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [selection, doc]);

  const openContextMenu = (e: MouseEvent, target: ContextTarget) => {
    e.preventDefault();
    e.stopPropagation();
    setSelection(selectionFromTarget(target));
    setInspectorTab("content");
    setContextMenu({ x: e.clientX, y: e.clientY, target });
  };

  const buildContextMenuItems = (target: ContextTarget): ContextMenuItem[] => {
    const clipboard = getBuilderClipboard();
    const mod = shortcutMod();
    let editLabel = "Edit";
    if (target.kind === "section") editLabel = "Edit Section";
    else if (target.kind === "inner-section") editLabel = "Edit Inner Section";
    else {
      const widget = findWidget(docRef.current, target);
      editLabel = widget ? `Edit ${widgetLabel(widget.type)}` : "Edit Widget";
    }

    const canPaste =
      (clipboard?.kind === "section" && target.kind === "section") ||
      (clipboard?.kind === "inner-section" && target.kind === "inner-section") ||
      (clipboard?.kind === "widget" && target.kind === "widget");

    const items: ContextMenuItem[] = [
      {
        id: "edit",
        label: editLabel,
        onClick: () => {
          setSelection(selectionFromTarget(target));
          setInspectorTab("content");
        },
      },
      {
        id: "duplicate",
        label: "Duplicate",
        shortcut: `${mod}D`,
        onClick: () => {
          if (target.kind === "section") apply(duplicateSection(docRef.current, target.sectionId));
          else if (target.kind === "inner-section") {
            apply(duplicateInnerSection(docRef.current, target.sectionId, target.columnId, target.innerSectionId));
          } else apply(duplicateWidget(docRef.current, target));
        },
      },
      {
        id: "copy",
        label: "Copy",
        shortcut: `${mod}C`,
        onClick: () => {
          const current = docRef.current;
          if (target.kind === "section") {
            const section = findSection(current, target.sectionId);
            if (section) setBuilderClipboard({ kind: "section", section: JSON.parse(JSON.stringify(section)) });
          } else if (target.kind === "inner-section") {
            const inner = findInnerSection(current, target.sectionId, target.columnId, target.innerSectionId);
            if (inner) setBuilderClipboard({ kind: "inner-section", inner: JSON.parse(JSON.stringify(inner)) });
          } else {
            const widget = findWidget(current, target);
            if (widget) setBuilderClipboard({ kind: "widget", widget: JSON.parse(JSON.stringify(widget)) });
          }
        },
      },
      {
        id: "paste",
        label: "Paste",
        shortcut: `${mod}V`,
        disabled: !canPaste,
        onClick: () => {
          const clip = getBuilderClipboard();
          if (!clip) return;
          const current = docRef.current;
          if (clip.kind === "section" && target.kind === "section") {
            apply(pasteSection(current, target.sectionId, clip.section));
          } else if (clip.kind === "inner-section" && target.kind === "inner-section") {
            apply(pasteInnerSection(current, target.sectionId, target.columnId, target.innerSectionId, clip.inner));
          } else if (clip.kind === "widget" && target.kind === "widget") {
            apply(pasteWidget(current, target, clip.widget));
          }
        },
      },
    ];

    if (target.kind === "widget") {
      items.push({
        id: "paste-style",
        label: "Paste style",
        shortcut: `${mod}⇧V`,
        disabled: clipboard?.kind !== "widget",
        onClick: () => {
          const clip = getBuilderClipboard();
          if (clip?.kind === "widget") {
            apply(pasteWidgetStyle(docRef.current, target, clip.widget));
          }
        },
      });
      items.push({ id: "sep-style", label: "", separator: true });
      items.push({
        id: "reset-style",
        label: "Reset style",
        onClick: () => apply(resetWidgetStyle(docRef.current, target)),
      });
    } else {
      items.push({ id: "sep-style", label: "", separator: true });
    }

    items.push({
      id: "delete",
      label: "Delete",
      shortcut: "Del",
      danger: true,
      onClick: () => {
        const current = docRef.current;
        if (target.kind === "widget") {
          apply(removeWidget(current, target));
          setSelection(null);
        } else if (target.kind === "section") {
          apply(removeSection(current, target.sectionId));
          setSelection(null);
        } else {
          apply(removeInnerSection(current, target.sectionId, target.columnId, target.innerSectionId));
          setSelection(null);
        }
      },
    });

    return items;
  };

  const handleAddSection = (columns: number) => {
    const after = selection && "sectionId" in selection ? selection.sectionId : undefined;
    apply(addSection(docRef.current, columns, after));
    setShowStructurePicker(false);
  };

  const handleAddWidget = (type: WidgetType) => {
    const { doc: nextDoc, target } = ensureDropTarget(docRef.current, selection);
    apply(addWidgetAt(nextDoc, type, target));
  };

  const handleEmptyDrop = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDropKey(null);
    setDragging(false);
    const payload = readDragPayload(e);
    if (!payload || payload.kind !== "new") return;
    const { doc: nextDoc, target } = ensureDropTarget(docRef.current, null);
    apply(addWidgetAt(nextDoc, payload.type, target));
  };

  const handleDrop = (target: DropTarget, e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDropKey(null);
    setDragging(false);
    const payload = readDragPayload(e);
    if (!payload) return;

    if (payload.kind === "new") {
      apply(addWidgetAt(docRef.current, payload.type, target));
      return;
    }

    apply(
      moveWidget(
        docRef.current,
        {
          sectionId: payload.sectionId,
          columnId: payload.columnId,
          widgetId: payload.widgetId,
          innerSectionId: payload.innerSectionId,
        },
        target
      )
    );
  };

  return (
    <div
      className={`epb-visual ${dragging ? "is-dragging" : ""}${railOpen ? "" : " is-rail-collapsed"}${inspectorOpen ? "" : " is-inspector-collapsed"}`}
    >
      <div className={`epb-visual__side epb-visual__side--left${railOpen ? "" : " is-collapsed"}`}>
        <aside className="epb-visual__rail" aria-label="Elements" aria-hidden={!railOpen}>
        <div className="epb-visual__panel-head">
          <h3>Elements</h3>
          <p className="epb-visual__panel-sub">Add sections &amp; widgets</p>
        </div>

        <div className="epb-visual__rail-block">
          <h4 className="epb-visual__rail-label">Sections</h4>
          <div className="epb-visual__preset-grid">
            {SECTION_PRESETS.map((preset) => (
              <button
                key={preset.label}
                type="button"
                className="epb-visual__preset"
                onClick={() => handleAddSection(preset.columns)}
                title={`Add ${preset.label} section`}
              >
                <span className={`epb-visual__preset-icon epb-visual__preset-icon--${preset.columns}`} aria-hidden="true" />
                <span className="epb-visual__preset-label">{preset.label}</span>
              </button>
            ))}
          </div>
        </div>

        <div className="epb-visual__rail-block">
          <h4 className="epb-visual__rail-label">Widgets</h4>
          <p className="epb-visual__hint">Drag onto the canvas, or click to add.</p>
          {WIDGET_CATEGORIES.map((category) => (
            <div key={category.id} className="epb-visual__widget-cat">
              <h5 className="epb-visual__widget-cat-title">{category.label}</h5>
              <div className="epb-visual__widget-grid epb-visual__widget-grid--tiles">
                {category.items.map((item) => (
                  <button
                    key={item.type}
                    type="button"
                    className="epb-visual__widget-tile"
                    title={`${item.hint} — drag or click`}
                    draggable
                    onDragStart={(e) => {
                      writeDragPayload(e, { kind: "new", type: item.type });
                      setDragging(true);
                    }}
                    onDragEnd={() => {
                      setDragging(false);
                      setDropKey(null);
                    }}
                    onClick={() => handleAddWidget(item.type)}
                  >
                    <span className="epb-visual__widget-tile-icon" aria-hidden="true">
                      {WIDGET_ICONS[item.type] || item.icon}
                    </span>
                    <span className="epb-visual__widget-tile-label">{item.label}</span>
                  </button>
                ))}
              </div>
            </div>
          ))}
        </div>
      </aside>
        <PanelSideToggle
          side="left"
          open={railOpen}
          onToggle={() => setRailOpen((open) => !open)}
          label={railOpen ? "Hide elements panel" : "Show elements panel"}
        />
      </div>

      <div className="epb-visual__canvas-wrap">
        <div className="epb-visual__canvas-toolbar">
          <div className="epb-visual__canvas-toolbar-left">
            <span className="epb-visual__canvas-title">Canvas</span>
            {selection ? (
              <span className="epb-visual__breadcrumb">
                <span className="epb-visual__breadcrumb-sep">/</span>
                {selectionMeta.title}
              </span>
            ) : null}
          </div>
          <div className="epb-visual__canvas-toolbar-right">
            {onDevicePreviewChange ? (
              <DeviceSwitcher value={devicePreview} onChange={onDevicePreviewChange} />
            ) : null}
            <span className={`epb-visual__canvas-note ${dragging ? "is-live" : ""}`}>
              {dragging
                ? "Drop on a blue line or highlighted column"
                : `${DEVICE_PREVIEW_LABELS[devicePreview]} · Click to select · Drag to rearrange`}
            </span>
          </div>
        </div>
        <div
          className={`epb-visual__canvas epb-visual__canvas--${devicePreview}${doc.sections.length === 0 ? " epb-visual__canvas--blank" : ""}`}
          onClick={() => {
            setSelection(null);
            setShowStructurePicker(false);
          }}
        >
          <div
            className="epb-visual__canvas-device"
            style={
              devicePreview === "desktop"
                ? { width: "100%", maxWidth: "100%" }
                : { width: DEVICE_PREVIEW_WIDTHS[devicePreview], maxWidth: "100%" }
            }
          >
          <div className="epb-visual__page">
          {doc.sections.length === 0 ? (
            <div
              className="epb-visual__empty"
              onDragOver={(e) => {
                if (readDragPayload(e)) {
                  e.preventDefault();
                  e.dataTransfer.dropEffect = "copy";
                }
              }}
              onDrop={handleEmptyDrop}
            >
              <div className="epb-visual__empty-zone">
                <div className="epb-visual__empty-actions-row">
                  <button
                    type="button"
                    className="epb-visual__empty-circle epb-visual__empty-circle--add"
                    title="Add section"
                    onClick={(e) => {
                      e.stopPropagation();
                      setShowStructurePicker((open) => !open);
                    }}
                  >
                    +
                  </button>
                </div>
                {showStructurePicker ? (
                  <div className="epb-visual__structure-picker">
                    <p className="epb-visual__structure-picker-title">Select structure</p>
                    <div className="epb-visual__structure-picker-grid">
                      {SECTION_PRESETS.map((preset) => (
                        <button
                          key={preset.label}
                          type="button"
                          className="epb-visual__structure-preset"
                          title={`Add ${preset.label} section`}
                          onClick={(e) => {
                            e.stopPropagation();
                            handleAddSection(preset.columns);
                          }}
                        >
                          <span
                            className={`epb-visual__preset-icon epb-visual__preset-icon--${preset.columns}`}
                            aria-hidden="true"
                          />
                        </button>
                      ))}
                    </div>
                  </div>
                ) : null}
                <p className="epb-visual__empty-hint">Drag widget here</p>
              </div>
            </div>
          ) : (
            doc.sections.map((section, sectionIndex) => {
            const sectionSettings = resolveSectionSettings(
              section.settings,
              section.settingsOverrides,
              devicePreview
            );
            return (
            <div
              key={section.id}
              className={`epb-visual-section ${selection?.kind === "section" && selection.sectionId === section.id ? "is-selected" : ""}`}
              style={{
                background: sectionSettings.background,
                color: sectionSettings.textColor,
                padding: sectionSettings.padding,
                minHeight:
                  !sectionSettings.minHeight || sectionSettings.minHeight === 0 || sectionSettings.minHeight === "0"
                    ? undefined
                    : typeof sectionSettings.minHeight === "number"
                      ? `${sectionSettings.minHeight}px`
                      : sectionSettings.minHeight,
                backgroundImage: sectionSettings.backgroundImage
                  ? sectionSettings.backgroundImage.includes("gradient(") ||
                    sectionSettings.backgroundImage.includes("url(")
                    ? sectionSettings.backgroundImage
                    : `linear-gradient(rgba(15,23,42,0.55), rgba(15,23,42,0.35)), url(${sectionSettings.backgroundImage})`
                  : undefined,
                backgroundSize: "cover",
                backgroundPosition: "center",
                zIndex: sectionSettings.zIndex ? Number(sectionSettings.zIndex) : undefined,
                position: sectionSettings.zIndex ? "relative" : undefined,
              }}
              onClick={(e) => {
                e.stopPropagation();
                setSelection({ kind: "section", sectionId: section.id });
                setInspectorTab("content");
              }}
              onContextMenu={(e) => openContextMenu(e, { kind: "section", sectionId: section.id })}
            >
              <div className="epb-visual-section__chrome">
                <StructureIcon kind="section" title={`Section ${sectionIndex + 1}`} />
                <div className="epb-visual-section__actions">
                  <ChromeIconButton
                    title="Add column"
                    variant="add"
                    onClick={(e) => {
                      e.stopPropagation();
                      apply(addSectionColumn(docRef.current, section.id));
                    }}
                  >
                    +
                  </ChromeIconButton>
                  <ChromeRemoveButton
                    title="Remove section"
                    onClick={(e) => {
                      e.stopPropagation();
                      apply(removeSection(docRef.current, section.id));
                      setSelection(null);
                    }}
                  />
                </div>
              </div>

              <div
                className="epb-visual-section__row"
                style={{
                  maxWidth:
                    typeof sectionSettings.contentWidth === "number"
                      ? sectionSettings.contentWidth
                      : sectionSettings.contentWidth || "1140px",
                  gap:
                    typeof sectionSettings.gap === "number"
                      ? `${sectionSettings.gap}px`
                      : sectionSettings.gap || "0px",
                }}
              >
                {section.columns.map((column) => {
                  const columnSettings = resolveColumnSettings(
                    column.settings,
                    column.settingsOverrides,
                    devicePreview
                  );
                  return (
                  <ColumnDropZone
                    key={column.id}
                    selected={
                      selection?.kind === "column" &&
                      selection.columnId === column.id &&
                      !selection.innerSectionId
                    }
                    style={{
                      flex: `0 0 ${columnSettings.width}%`,
                      maxWidth: `${columnSettings.width}%`,
                      padding: columnSettings.padding,
                      background: columnSettings.background,
                      justifyContent:
                        columnSettings.verticalAlign === "middle"
                          ? "center"
                          : columnSettings.verticalAlign === "bottom"
                            ? "flex-end"
                            : "flex-start",
                      zIndex: columnSettings.zIndex ? Number(columnSettings.zIndex) : undefined,
                      position: columnSettings.zIndex ? "relative" : undefined,
                    }}
                    label={`Column ${Math.round(columnSettings.width)}%`}
                    structureKind="column"
                    dropKey={dropKey}
                    setDropKey={setDropKey}
                    targetBase={{ sectionId: section.id, columnId: column.id }}
                    onSelect={() => {
                      setSelection({ kind: "column", sectionId: section.id, columnId: column.id });
                      setInspectorTab("content");
                    }}
                    onDropAt={handleDrop}
                    onRemove={
                      section.columns.length > 1
                        ? () => apply(removeSectionColumn(docRef.current, section.id, column.id))
                        : undefined
                    }
                    chromeExtra={
                      <ChromeIconButton
                        title="Add nested section"
                        variant="add"
                        onClick={(e) => {
                          e.stopPropagation();
                          apply(addInnerSection(docRef.current, section.id, column.id));
                        }}
                      >
                        +
                      </ChromeIconButton>
                    }
                  >
                    {column.children.length === 0 && <EmptyColumnDropHint />}
                    {column.children.map((child, index) => {
                      if (child.type === "inner-section") {
                        return (
                          <div key={child.id}>
                            <DropSlot
                              slotKey={`${section.id}:${column.id}:${index}`}
                              activeKey={dropKey}
                              setDropKey={setDropKey}
                              onDrop={(e) =>
                                handleDrop({ sectionId: section.id, columnId: column.id, index }, e)
                              }
                            />
                            <div
                              className={`epb-visual-inner ${
                                selection?.kind === "inner-section" && selection.innerSectionId === child.id
                                  ? "is-selected"
                                  : ""
                              }`}
                              onClick={(e) => {
                                e.stopPropagation();
                                setSelection({
                                  kind: "inner-section",
                                  sectionId: section.id,
                                  columnId: column.id,
                                  innerSectionId: child.id,
                                });
                              }}
                              onContextMenu={(e) =>
                                openContextMenu(e, {
                                  kind: "inner-section",
                                  sectionId: section.id,
                                  columnId: column.id,
                                  innerSectionId: child.id,
                                })
                              }
                            >
                              <div className="epb-visual-inner__chrome">
                                <StructureIcon kind="inner-section" title="Inner section" />
                                <div className="epb-visual-inner__actions">
                                  <ChromeIconButton
                                    title="Add column"
                                    variant="add"
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      apply(
                                        addInnerSectionColumn(
                                          docRef.current,
                                          section.id,
                                          column.id,
                                          child.id
                                        )
                                      );
                                    }}
                                  >
                                    +
                                  </ChromeIconButton>
                                  <ChromeRemoveButton
                                    title="Remove inner section"
                                    onClick={(e) => {
                                      e.stopPropagation();
                                      apply(removeInnerSection(docRef.current, section.id, column.id, child.id));
                                      setSelection(null);
                                    }}
                                  />
                                </div>
                              </div>
                              <div
                                className="epb-visual-inner__row"
                                style={{
                                  gap: (() => {
                                    const innerSettings = resolveSectionSettings(
                                      child.settings,
                                      child.settingsOverrides,
                                      devicePreview
                                    );
                                    return typeof innerSettings.gap === "number"
                                      ? `${innerSettings.gap}px`
                                      : innerSettings.gap || "0px";
                                  })(),
                                }}
                              >
                                {child.columns.map((innerCol) => {
                                  const innerColSettings = resolveColumnSettings(
                                    innerCol.settings,
                                    innerCol.settingsOverrides,
                                    devicePreview
                                  );
                                  return (
                                  <ColumnDropZone
                                    key={innerCol.id}
                                    selected={
                                      selection?.kind === "column" &&
                                      selection.innerSectionId === child.id &&
                                      selection.columnId === innerCol.id
                                    }
                                    style={{
                                      flex: `1 1 ${innerColSettings.width}%`,
                                      maxWidth: `${innerColSettings.width}%`,
                                      padding: innerColSettings.padding,
                                      background: innerColSettings.background,
                                    }}
                                    label="Column"
                                    structureKind="column"
                                    dropKey={dropKey}
                                    setDropKey={setDropKey}
                                    targetBase={{
                                      sectionId: section.id,
                                      columnId: innerCol.id,
                                      innerSectionId: child.id,
                                    }}
                                    onSelect={() => {
                                      setSelection({
                                        kind: "column",
                                        sectionId: section.id,
                                        columnId: innerCol.id,
                                        innerSectionId: child.id,
                                        parentColumnId: column.id,
                                      });
                                      setInspectorTab("content");
                                    }}
                                    onDropAt={handleDrop}
                                    onRemove={
                                      child.columns.length > 1
                                        ? () =>
                                            apply(
                                              removeInnerSectionColumn(
                                                docRef.current,
                                                section.id,
                                                column.id,
                                                child.id,
                                                innerCol.id
                                              )
                                            )
                                        : undefined
                                    }
                                  >
                                    {innerCol.children.length === 0 && <EmptyColumnDropHint />}
                                    {innerCol.children.map((w, wIndex) =>
                                      w.type === "inner-section" ? null : (
                                        <div key={w.id}>
                                          <DropSlot
                                            slotKey={`${section.id}:${innerCol.id}:${child.id}:${wIndex}`}
                                            activeKey={dropKey}
                                            setDropKey={setDropKey}
                                            onDrop={(e) =>
                                              handleDrop(
                                                {
                                                  sectionId: section.id,
                                                  columnId: innerCol.id,
                                                  innerSectionId: child.id,
                                                  index: wIndex,
                                                },
                                                e
                                              )
                                            }
                                          />
                                          <WidgetCard
                                            widget={w}
                                            pageId={pageId}
                                            devicePreview={devicePreview}
                                            selected={
                                              selection?.kind === "widget" && selection.widgetId === w.id
                                            }
                                            onSelect={() => {
                                              setSelection({
                                                kind: "widget",
                                                sectionId: section.id,
                                                columnId: innerCol.id,
                                                widgetId: w.id,
                                                innerSectionId: child.id,
                                                parentColumnId: column.id,
                                              });
                                              setInspectorTab("content");
                                            }}
                                            onRemove={() => {
                                              apply(
                                                removeWidget(docRef.current, {
                                                  kind: "widget",
                                                  sectionId: section.id,
                                                  columnId: innerCol.id,
                                                  widgetId: w.id,
                                                  innerSectionId: child.id,
                                                  parentColumnId: column.id,
                                                })
                                              );
                                              setSelection(null);
                                            }}
                                            onDragStart={(e) => {
                                              writeDragPayload(e, {
                                                kind: "move",
                                                sectionId: section.id,
                                                columnId: innerCol.id,
                                                widgetId: w.id,
                                                innerSectionId: child.id,
                                              });
                                              setDragging(true);
                                            }}
                                            onDragEnd={() => {
                                              setDragging(false);
                                              setDropKey(null);
                                            }}
                                            onContextMenu={(e) =>
                                              openContextMenu(e, {
                                                kind: "widget",
                                                sectionId: section.id,
                                                columnId: innerCol.id,
                                                widgetId: w.id,
                                                innerSectionId: child.id,
                                                parentColumnId: column.id,
                                              })
                                            }
                                          />
                                        </div>
                                      )
                                    )}
                                    <DropSlot
                                      slotKey={`${section.id}:${innerCol.id}:${child.id}:end`}
                                      activeKey={dropKey}
                                      setDropKey={setDropKey}
                                      onDrop={(e) =>
                                        handleDrop(
                                          {
                                            sectionId: section.id,
                                            columnId: innerCol.id,
                                            innerSectionId: child.id,
                                            index: innerCol.children.length,
                                          },
                                          e
                                        )
                                      }
                                    />
                                  </ColumnDropZone>
                                  );
                                })}
                              </div>
                            </div>
                          </div>
                        );
                      }

                      return (
                        <div key={child.id}>
                          <DropSlot
                            slotKey={`${section.id}:${column.id}:${index}`}
                            activeKey={dropKey}
                            setDropKey={setDropKey}
                            onDrop={(e) =>
                              handleDrop({ sectionId: section.id, columnId: column.id, index }, e)
                            }
                          />
                          <WidgetCard
                            widget={child}
                            pageId={pageId}
                            devicePreview={devicePreview}
                            selected={selection?.kind === "widget" && selection.widgetId === child.id}
                            onSelect={() => {
                              setSelection({
                                kind: "widget",
                                sectionId: section.id,
                                columnId: column.id,
                                widgetId: child.id,
                              });
                              setInspectorTab("content");
                            }}
                            onRemove={() => {
                              apply(
                                removeWidget(docRef.current, {
                                  kind: "widget",
                                  sectionId: section.id,
                                  columnId: column.id,
                                  widgetId: child.id,
                                })
                              );
                              setSelection(null);
                            }}
                            onDragStart={(e) => {
                              writeDragPayload(e, {
                                kind: "move",
                                sectionId: section.id,
                                columnId: column.id,
                                widgetId: child.id,
                              });
                              setDragging(true);
                            }}
                            onDragEnd={() => {
                              setDragging(false);
                              setDropKey(null);
                            }}
                            onContextMenu={(e) =>
                              openContextMenu(e, {
                                kind: "widget",
                                sectionId: section.id,
                                columnId: column.id,
                                widgetId: child.id,
                              })
                            }
                          />
                        </div>
                      );
                    })}
                    <DropSlot
                      slotKey={`${section.id}:${column.id}:end`}
                      activeKey={dropKey}
                      setDropKey={setDropKey}
                      onDrop={(e) =>
                        handleDrop(
                          { sectionId: section.id, columnId: column.id, index: column.children.length },
                          e
                        )
                      }
                    />
                  </ColumnDropZone>
                  );
                })}
              </div>
            </div>
            );
          })
          )}

          {doc.sections.length > 0 ? (
            <button
              type="button"
              className="epb-visual__add-section"
              onClick={(e) => {
                e.stopPropagation();
                handleAddSection(1);
              }}
            >
              <span aria-hidden="true">+</span> Add section
            </button>
          ) : null}
          </div>
          </div>
        </div>
      </div>

      <div className={`epb-visual__side epb-visual__side--right${inspectorOpen ? "" : " is-collapsed"}`}>
        <PanelSideToggle
          side="right"
          open={inspectorOpen}
          onToggle={() => setInspectorOpen((open) => !open)}
          label={inspectorOpen ? "Hide settings panel" : "Show settings panel"}
        />
        <aside className="epb-visual__inspector" aria-label="Settings" aria-hidden={!inspectorOpen}>
        <div className="epb-visual__panel-head">
          <h3>{selectionMeta.title}</h3>
          {selectionMeta.subtitle ? <p className="epb-visual__panel-sub">{selectionMeta.subtitle}</p> : null}
        </div>

        {!selection && (
          <div className="epb-visual__empty-inspector">
            <div className="epb-visual__empty-inspector-icon" aria-hidden="true">
              ✎
            </div>
            <p>Select anything on the canvas to edit its settings here.</p>
            <ul>
              <li>Click a section edge for layout</li>
              <li>Click a column for width &amp; padding</li>
              <li>Click a widget for content &amp; style</li>
            </ul>
            <p className="epb-visual__empty-inspector-note">
              Page title, layout, featured image, and more are under the <strong>Page Options</strong> tab.
            </p>
          </div>
        )}

        {selectedSection && selection?.kind === "section" && (() => {
          const effectiveSettings = resolveSectionSettings(
            selectedSection.settings,
            selectedSection.settingsOverrides,
            devicePreview
          );
          const patchSection = (patch: Partial<typeof effectiveSettings>) =>
            apply(updateSectionSettingsForDevice(docRef.current, selectedSection.id, devicePreview, patch));
          return (
          <div className="epb-visual__fields">
            <ResponsiveDeviceBanner device={devicePreview} />
            <div className="epb-visual__group">
              <h4 className="epb-visual__group-title">Colors</h4>
              <ColorField
                label="Background"
                value={effectiveSettings.background}
                onChange={(next) => patchSection({ background: next })}
                allowClear
              />
              <ColorField
                label="Text color"
                value={effectiveSettings.textColor}
                onChange={(next) => patchSection({ textColor: next })}
              />
            </div>
            <div className="epb-visual__group">
              <h4 className="epb-visual__group-title">Layout</h4>
              <DimensionsField
                label="Padding"
                values={expandCssBox(effectiveSettings.padding, "0px")}
                onChange={(next) => patchSection({ padding: collapseCssBox(next) })}
              />
              <SizeField
                label="Content width"
                value={
                  typeof effectiveSettings.contentWidth === "number"
                    ? `${effectiveSettings.contentWidth}px`
                    : effectiveSettings.contentWidth || "1140px"
                }
                units={SIZE_UNIT_SETS.size}
                onChange={(next) => patchSection({ contentWidth: next || "1140px" })}
              />
              <SizeField
                label="Min height"
                value={
                  typeof effectiveSettings.minHeight === "number"
                    ? effectiveSettings.minHeight > 0
                      ? `${effectiveSettings.minHeight}px`
                      : ""
                    : effectiveSettings.minHeight || ""
                }
                units={SIZE_UNIT_SETS.size}
                onChange={(next) => patchSection({ minHeight: next || 0 })}
              />
              <SizeField
                label="Column gap"
                value={
                  typeof effectiveSettings.gap === "number"
                    ? `${effectiveSettings.gap}px`
                    : effectiveSettings.gap || "0px"
                }
                units={SIZE_UNIT_SETS.spacing}
                onChange={(next) => patchSection({ gap: next || "0px" })}
              />
              <Field label="Z-index">
                <input
                  type="number"
                  value={effectiveSettings.zIndex || ""}
                  placeholder="auto"
                  onChange={(e) => patchSection({ zIndex: e.target.value })}
                />
              </Field>
            </div>
            <div className="epb-visual__group">
              <h4 className="epb-visual__group-title">Background image</h4>
              <Field label="Image">
                <ImageMediaControl
                  url={effectiveSettings.backgroundImage}
                  onChange={(url) => patchSection({ backgroundImage: url })}
                  title="Select section background"
                />
              </Field>
            </div>
            {devicePreview !== "desktop" && (
              <button
                type="button"
                className="epb-visual__reset-btn"
                onClick={() =>
                  apply(clearSectionSettingsOverrides(docRef.current, selectedSection.id, devicePreview))
                }
              >
                Reset {DEVICE_PREVIEW_LABELS[devicePreview]} overrides
              </button>
            )}
            <InspectorDeleteButton
              label="Delete section"
              onDelete={() => {
                apply(removeSection(docRef.current, selectedSection.id));
                setSelection(null);
              }}
            />
          </div>
          );
        })()}

        {selection?.kind === "column" && (() => {
          const columnSettings = findColumnSettings(doc, selection, devicePreview);
          const patchColumn = (patch: Partial<typeof columnSettings>) =>
            apply(updateColumnSettingsForDevice(docRef.current, selection, devicePreview, patch));
          return (
          <div className="epb-visual__fields">
            <ResponsiveDeviceBanner device={devicePreview} />
            <div className="epb-visual__group">
              <h4 className="epb-visual__group-title">Column</h4>
              <label>
                Width (%)
                <input
                  type="number"
                  min={10}
                  max={100}
                  value={columnSettings.width}
                  onChange={(e) =>
                    patchColumn({
                      width: Math.max(10, Math.min(100, Number(e.target.value) || 50)),
                    })
                  }
                />
              </label>
              <DimensionsField
                label="Padding"
                values={expandCssBox(columnSettings.padding, "0px")}
                onChange={(next) => patchColumn({ padding: collapseCssBox(next) })}
              />
              <ColorField
                label="Background"
                value={columnSettings.background}
                onChange={(next) => patchColumn({ background: next })}
                allowClear
              />
              <Field label="Z-index">
                <input
                  type="number"
                  value={columnSettings.zIndex || ""}
                  placeholder="auto"
                  onChange={(e) => patchColumn({ zIndex: e.target.value })}
                />
              </Field>
            </div>
            {devicePreview !== "desktop" && (
              <button
                type="button"
                className="epb-visual__reset-btn"
                onClick={() => apply(clearColumnSettingsOverrides(docRef.current, selection, devicePreview))}
              >
                Reset {DEVICE_PREVIEW_LABELS[devicePreview]} overrides
              </button>
            )}
          </div>
          );
        })()}

        {selection?.kind === "inner-section" && (
          <div className="epb-visual__fields">
            <p className="epb-visual__panel-sub">Select an inner column or widget inside this nested section to edit it.</p>
            <InspectorDeleteButton
              label="Delete inner section"
              onDelete={() => {
                apply(
                  removeInnerSection(
                    docRef.current,
                    selection.sectionId,
                    selection.columnId,
                    selection.innerSectionId
                  )
                );
                setSelection(null);
              }}
            />
          </div>
        )}

        {selectedWidget && selection?.kind === "widget" && (
          <>
            <div className="epb-visual__tabs" role="tablist">
              <button
                type="button"
                role="tab"
                className={inspectorTab === "content" ? "is-active" : ""}
                aria-selected={inspectorTab === "content"}
                onClick={() => setInspectorTab("content")}
              >
                Content
              </button>
              <button
                type="button"
                role="tab"
                className={inspectorTab === "style" ? "is-active" : ""}
                aria-selected={inspectorTab === "style"}
                onClick={() => setInspectorTab("style")}
              >
                Style
              </button>
            </div>
            {inspectorTab === "content" ? (
              <WidgetContentSettings
                widget={selectedWidget}
                pageId={pageId}
                onChange={(patch) => apply(updateWidget(docRef.current, selection, patch))}
              />
            ) : (
              <WidgetStyleSettings
                widget={selectedWidget}
                devicePreview={devicePreview}
                onChange={(patch) => apply(updateWidget(docRef.current, selection, patch))}
                onPatchStyle={(partial) =>
                  apply(updateWidgetStyleForDevice(docRef.current, selection, devicePreview, partial))
                }
                onClearOverrides={
                  devicePreview !== "desktop"
                    ? () => apply(clearWidgetStyleOverridesForDevice(docRef.current, selection, devicePreview))
                    : undefined
                }
              />
            )}
            <InspectorDeleteButton
              label={`Delete ${widgetLabel(selectedWidget.type).toLowerCase()}`}
              onDelete={() => {
                apply(removeWidget(docRef.current, selection));
                setSelection(null);
              }}
            />
          </>
        )}
      </aside>
      </div>
      {contextMenu ? (
        <BuilderContextMenu
          x={contextMenu.x}
          y={contextMenu.y}
          items={buildContextMenuItems(contextMenu.target)}
          onClose={() => setContextMenu(null)}
        />
      ) : null}
    </div>
  );
}

function PanelSideToggle({
  side,
  open,
  onToggle,
  label,
}: {
  side: "left" | "right";
  open: boolean;
  onToggle: () => void;
  label: string;
}) {
  const icon = side === "left" ? (open ? "‹" : "›") : open ? "›" : "‹";
  return (
    <button
      type="button"
      className={`epb-visual__side-toggle epb-visual__side-toggle--${side}`}
      onClick={onToggle}
      aria-label={label}
      aria-expanded={open}
      title={label}
    >
      <span aria-hidden="true">{icon}</span>
    </button>
  );
}

function EmptyColumnDropHint() {
  return (
    <div className="epb-visual-column__empty">
      <span className="epb-visual-column__empty-plus" aria-hidden="true">
        +
      </span>
      <span className="epb-visual-column__empty-hint">Drag widget here</span>
    </div>
  );
}

function InspectorDeleteButton({ label, onDelete }: { label: string; onDelete: () => void }) {
  return (
    <div className="epb-visual__inspector-delete">
      <button type="button" className="epb-visual__inspector-delete-btn" onClick={onDelete}>
        {label}
      </button>
    </div>
  );
}

function ChromeIconButton({
  title,
  onClick,
  children,
  variant = "default",
}: {
  title: string;
  onClick: (e: MouseEvent<HTMLButtonElement>) => void;
  children: ReactNode;
  variant?: "default" | "add" | "remove";
}) {
  return (
    <button
      type="button"
      className={`epb-visual__chrome-btn epb-visual__chrome-btn--${variant}`}
      title={title}
      aria-label={title}
      onClick={onClick}
    >
      {children}
    </button>
  );
}

function ChromeRemoveButton({
  title,
  onClick,
}: {
  title: string;
  onClick: (e: MouseEvent<HTMLButtonElement>) => void;
}) {
  return (
    <ChromeIconButton title={title} onClick={onClick} variant="remove">
      ×
    </ChromeIconButton>
  );
}

function ColumnDropZone({
  children,
  selected,
  style,
  label,
  structureKind = "column",
  chromeExtra,
  onRemove,
  dropKey,
  setDropKey,
  targetBase,
  onSelect,
  onDropAt,
}: {
  children: ReactNode;
  selected: boolean;
  style?: CSSProperties;
  label: string;
  structureKind?: StructureKind;
  chromeExtra?: ReactNode;
  onRemove?: () => void;
  dropKey: string | null;
  setDropKey: (key: string | null) => void;
  targetBase: { sectionId: string; columnId: string; innerSectionId?: string };
  onSelect: () => void;
  onDropAt: (target: DropTarget, e: DragEvent) => void;
}) {
  const columnKey = `${targetBase.sectionId}:${targetBase.columnId}:${targetBase.innerSectionId || ""}:col`;
  const isDropTarget = dropKey === columnKey;

  return (
    <div
      className={`epb-visual-column ${selected ? "is-selected" : ""} ${isDropTarget ? "is-drop-target" : ""}`}
      style={style}
      onClick={(e) => {
        e.stopPropagation();
        onSelect();
      }}
      onDragEnter={(e) => {
        e.preventDefault();
        setDropKey(columnKey);
      }}
      onDragOver={(e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";
      }}
      onDrop={(e) => {
        // Fallback: drop anywhere in column appends at end
        e.preventDefault();
        e.stopPropagation();
        onDropAt({ ...targetBase, index: 9999 }, e);
        setDropKey(null);
      }}
    >
      <div className="epb-visual-column__chrome">
        <StructureIcon kind={structureKind} title={label} />
        <div className="epb-visual-column__actions">
          {chromeExtra}
          {onRemove ? (
            <ChromeRemoveButton
              title="Remove column"
              onClick={(e) => {
                e.stopPropagation();
                onRemove();
              }}
            />
          ) : null}
        </div>
      </div>
      {children}
    </div>
  );
}

function DropSlot({
  slotKey,
  activeKey,
  setDropKey,
  onDrop,
}: {
  slotKey: string;
  activeKey: string | null;
  setDropKey: (key: string | null) => void;
  onDrop: (e: DragEvent) => void;
}) {
  const active = activeKey === slotKey;
  return (
    <div
      className={`epb-visual-drop ${active ? "is-active" : ""}`}
      onDragEnter={(e) => {
        e.preventDefault();
        setDropKey(slotKey);
      }}
      onDragOver={(e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";
        if (activeKey !== slotKey) setDropKey(slotKey);
      }}
      onDragLeave={() => {
        if (activeKey === slotKey) setDropKey(null);
      }}
      onDrop={onDrop}
    />
  );
}

function findColumn(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "column" }>
) {
  const section = doc.sections.find((s) => s.id === selection.sectionId);
  if (!section) return null;
  if (!selection.innerSectionId) {
    return section.columns.find((c) => c.id === selection.columnId) || null;
  }
  for (const col of section.columns) {
    for (const child of col.children) {
      if (child.type === "inner-section" && child.id === selection.innerSectionId) {
        return child.columns.find((c) => c.id === selection.columnId) || null;
      }
    }
  }
  return null;
}

function findColumnSettings(
  doc: VisualDocument,
  selection: Extract<VisualSelection, { kind: "column" }>,
  device: DevicePreview
) {
  const column = findColumn(doc, selection);
  if (!column) return DEFAULT_COLUMN_SETTINGS;
  return resolveColumnSettings(column.settings, column.settingsOverrides, device);
}

function ResponsiveDeviceBanner({ device }: { device: DevicePreview }) {
  if (device === "desktop") return null;
  return (
    <div className="epb-visual__device-banner">
      <strong>{DEVICE_PREVIEW_LABELS[device]} styles</strong>
      <p>Desktop styles stay as the default. Changes here only apply on {DEVICE_PREVIEW_LABELS[device].toLowerCase()} screens.</p>
    </div>
  );
}

function parseColorValue(value: string): { hex: string; alpha: number; transparent: boolean } {
  const raw = (value || "").trim();
  if (!raw || raw === "transparent" || raw === "none") {
    return { hex: "#ffffff", alpha: 0, transparent: true };
  }

  const hex8 = raw.match(/^#([0-9a-fA-F]{8})$/);
  if (hex8) {
    const h = hex8[1];
    return {
      hex: `#${h.slice(0, 6).toLowerCase()}`,
      alpha: Math.round((parseInt(h.slice(6, 8), 16) / 255) * 100),
      transparent: false,
    };
  }

  const hex6 = raw.match(/^#([0-9a-fA-F]{6})$/);
  if (hex6) {
    return { hex: `#${hex6[1].toLowerCase()}`, alpha: 100, transparent: false };
  }

  const hex3 = raw.match(/^#([0-9a-fA-F]{3})$/);
  if (hex3) {
    const h = hex3[1];
    return {
      hex: `#${h[0]}${h[0]}${h[1]}${h[1]}${h[2]}${h[2]}`.toLowerCase(),
      alpha: 100,
      transparent: false,
    };
  }

  const rgba = raw.match(/^rgba?\(\s*([\d.]+)\s*,\s*([\d.]+)\s*,\s*([\d.]+)(?:\s*,\s*([\d.]+))?\s*\)$/i);
  if (rgba) {
    const r = Math.max(0, Math.min(255, Math.round(Number(rgba[1]))));
    const g = Math.max(0, Math.min(255, Math.round(Number(rgba[2]))));
    const b = Math.max(0, Math.min(255, Math.round(Number(rgba[3]))));
    const a = rgba[4] === undefined ? 1 : Math.max(0, Math.min(1, Number(rgba[4])));
    return {
      hex: `#${[r, g, b].map((n) => n.toString(16).padStart(2, "0")).join("")}`,
      alpha: Math.round(a * 100),
      transparent: a === 0,
    };
  }

  return { hex: "#ffffff", alpha: 100, transparent: false };
}

function formatColorValue(hex: string, alpha: number): string {
  const clean = /^#[0-9a-fA-F]{6}$/.test(hex) ? hex.toLowerCase() : "#ffffff";
  const pct = Math.max(0, Math.min(100, Math.round(alpha)));
  if (pct <= 0) return "transparent";
  if (pct >= 100) return clean;
  const r = parseInt(clean.slice(1, 3), 16);
  const g = parseInt(clean.slice(3, 5), 16);
  const b = parseInt(clean.slice(5, 7), 16);
  const a = Math.round((pct / 100) * 1000) / 1000;
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

function ColorField({
  label,
  value,
  onChange,
  allowClear = false,
}: {
  label: string;
  value: string;
  onChange: (next: string) => void;
  allowClear?: boolean;
}) {
  const parsed = parseColorValue(value);
  const hexText = parsed.transparent && (!value || value === "transparent") ? "" : parsed.hex;

  return (
    <Field label={label}>
      <div className="epb-visual__color-control">
        <div className="epb-visual__color-row">
          <input
            type="color"
            value={parsed.hex}
            onChange={(e) => onChange(formatColorValue(e.target.value, parsed.alpha <= 0 ? 100 : parsed.alpha))}
            title="Pick color"
          />
          <input
            type="text"
            className="epb-visual__hex-input"
            value={hexText}
            placeholder="#000000"
            spellCheck={false}
            onChange={(e) => {
              const next = e.target.value.trim();
              if (next === "") {
                onChange(allowClear ? "transparent" : parsed.hex);
                return;
              }
              const parsedNext = parseColorValue(next);
              if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(next) || next.startsWith("rgba") || next.startsWith("rgb")) {
                onChange(
                  parsedNext.transparent
                    ? "transparent"
                    : formatColorValue(parsedNext.hex, parsedNext.alpha === 100 && parsed.alpha < 100 ? parsed.alpha : parsedNext.alpha)
                );
              }
            }}
            onBlur={(e) => {
              const next = e.target.value.trim();
              if (!next) {
                onChange(allowClear ? "transparent" : formatColorValue(parsed.hex, parsed.alpha));
                return;
              }
              const parsedNext = parseColorValue(next);
              onChange(formatColorValue(parsedNext.hex, parsedNext.alpha === 100 ? (parsed.alpha || 100) : parsedNext.alpha));
            }}
          />
          {allowClear ? (
            <button type="button" className="epb-visual__link-btn" onClick={() => onChange("transparent")}>
              Clear
            </button>
          ) : null}
        </div>
        <div className="epb-visual__opacity-row">
          <label className="epb-visual__opacity-label" htmlFor={`epb-opacity-${label.replace(/\s+/g, "-").toLowerCase()}`}>
            Opacity
          </label>
          <input
            id={`epb-opacity-${label.replace(/\s+/g, "-").toLowerCase()}`}
            type="range"
            min={0}
            max={100}
            step={1}
            value={parsed.alpha}
            onChange={(e) => onChange(formatColorValue(parsed.hex, Number(e.target.value)))}
          />
          <input
            type="number"
            className="epb-visual__opacity-input"
            min={0}
            max={100}
            value={parsed.alpha}
            onChange={(e) => onChange(formatColorValue(parsed.hex, Number(e.target.value) || 0))}
            aria-label={`${label} opacity percent`}
          />
          <span className="epb-visual__opacity-unit">%</span>
        </div>
      </div>
    </Field>
  );
}

type CssUnit = "px" | "rem" | "em" | "%" | "vw" | "vh" | "";

const SIZE_UNIT_SETS = {
  spacing: ["px", "rem", "em", "%"] as CssUnit[],
  font: ["px", "rem", "em"] as CssUnit[],
  size: ["px", "rem", "em", "%", "vw", "vh"] as CssUnit[],
  border: ["px", "rem", "em"] as CssUnit[],
  lineHeight: ["", "px", "rem", "em"] as CssUnit[],
};

function parseSizeValue(value: string | number | undefined | null, fallbackUnit: CssUnit = "px"): { num: string; unit: CssUnit } {
  if (typeof value === "number" && Number.isFinite(value)) {
    return { num: String(value), unit: fallbackUnit };
  }
  const raw = String(value ?? "").trim();
  if (!raw) return { num: "", unit: fallbackUnit };

  const match = raw.match(/^(-?[\d.]+)\s*(px|rem|em|%|vw|vh)?$/i);
  if (match) {
    const unit = (match[2]?.toLowerCase() || "") as CssUnit;
    return {
      num: match[1],
      unit: unit || (fallbackUnit === "" ? "" : fallbackUnit),
    };
  }

  // Multi-value (e.g. "60px 24px") — treat as custom px display of first token when possible
  const first = raw.split(/\s+/)[0];
  const firstMatch = first.match(/^(-?[\d.]+)(px|rem|em|%|vw|vh)?$/i);
  if (firstMatch) {
    return {
      num: firstMatch[1],
      unit: ((firstMatch[2]?.toLowerCase() || fallbackUnit) as CssUnit) || fallbackUnit,
    };
  }

  return { num: raw, unit: fallbackUnit };
}

function formatSizeValue(num: string, unit: CssUnit): string {
  const n = num.trim();
  if (n === "") return "";
  if (unit === "") return n;
  return `${n}${unit}`;
}

function convertSizeNumber(num: number, from: CssUnit, to: CssUnit): number {
  if (from === to || !Number.isFinite(num)) return num;
  const toPx = (value: number, unit: CssUnit) => {
    if (unit === "px" || unit === "") return value;
    if (unit === "rem" || unit === "em") return value * 16;
    return value;
  };
  const fromPx = (px: number, unit: CssUnit) => {
    if (unit === "px" || unit === "") return px;
    if (unit === "rem" || unit === "em") return Math.round((px / 16) * 1000) / 1000;
    return px;
  };
  // Keep numeric value when switching to/from % / vw / vh.
  if (from === "%" || from === "vw" || from === "vh" || to === "%" || to === "vw" || to === "vh") {
    return num;
  }
  return fromPx(toPx(num, from), to);
}

function SizeField({
  label,
  value,
  onChange,
  units = SIZE_UNIT_SETS.size,
  fallbackUnit = "px",
  step,
  min,
  placeholder = "0",
}: {
  label: string;
  value: string | number;
  onChange: (next: string) => void;
  units?: CssUnit[];
  fallbackUnit?: CssUnit;
  step?: number;
  min?: number;
  placeholder?: string;
}) {
  const available = units.length ? units : SIZE_UNIT_SETS.size;
  const parsed = parseSizeValue(value, fallbackUnit);
  const unit = available.includes(parsed.unit) ? parsed.unit : available[0];
  const numericStep = step ?? (unit === "px" || unit === "" ? 1 : 0.1);

  return (
    <Field label={label}>
      <div className="epb-visual__size-control">
        <input
          type="number"
          className="epb-visual__size-input"
          value={parsed.num}
          placeholder={placeholder}
          step={numericStep}
          min={min}
          onChange={(e) => onChange(formatSizeValue(e.target.value, unit))}
        />
        <select
          className="epb-visual__unit-select"
          value={unit}
          aria-label={`${label} unit`}
          onChange={(e) => {
            const nextUnit = e.target.value as CssUnit;
            if (nextUnit === unit) return;
            const current = Number(parsed.num);
            if (parsed.num !== "" && Number.isFinite(current)) {
              const converted = convertSizeNumber(current, unit, nextUnit);
              onChange(formatSizeValue(String(converted), nextUnit));
            } else {
              onChange(formatSizeValue(parsed.num || "0", nextUnit));
            }
          }}
        >
          {available.map((u) => (
            <option key={u || "none"} value={u}>
              {u === "" ? "—" : u}
            </option>
          ))}
        </select>
      </div>
    </Field>
  );
}

type BoxSides = { top: string; right: string; bottom: string; left: string };
type BoxSideKey = keyof BoxSides;

const DIMENSION_UNITS: CssUnit[] = ["px", "em", "%", "rem"];

function sidesFromValues(values: BoxSides): { nums: Record<BoxSideKey, string>; unit: CssUnit } {
  const keys: BoxSideKey[] = ["top", "right", "bottom", "left"];
  let unit: CssUnit = "px";
  for (const key of keys) {
    const parsed = parseSizeValue(values[key], "px");
    if (parsed.num !== "" && DIMENSION_UNITS.includes(parsed.unit as CssUnit)) {
      unit = parsed.unit as CssUnit;
      break;
    }
  }
  const nums = {} as Record<BoxSideKey, string>;
  for (const key of keys) {
    nums[key] = parseSizeValue(values[key], unit).num;
  }
  return { nums, unit };
}

function DimensionsField({
  label,
  values,
  onChange,
}: {
  label: string;
  values: BoxSides;
  onChange: (next: BoxSides) => void;
}) {
  const { nums, unit } = sidesFromValues(values);
  const [linked, setLinked] = useState(
    () => values.top === values.right && values.right === values.bottom && values.bottom === values.left
  );

  const emitSides = (nextNums: Record<BoxSideKey, string>, nextUnit: CssUnit, forceLinked = linked) => {
    const format = (n: string) => formatSizeValue(n === "" ? "0" : n, nextUnit);
    if (forceLinked) {
      const v = nextNums.top || nextNums.right || nextNums.bottom || nextNums.left || "0";
      const side = format(v);
      onChange({ top: side, right: side, bottom: side, left: side });
      return;
    }
    onChange({
      top: format(nextNums.top),
      right: format(nextNums.right),
      bottom: format(nextNums.bottom),
      left: format(nextNums.left),
    });
  };

  const setSide = (side: BoxSideKey, raw: string) => {
    if (linked) {
      emitSides({ top: raw, right: raw, bottom: raw, left: raw }, unit, true);
      return;
    }
    emitSides({ ...nums, [side]: raw }, unit, false);
  };

  const setUnit = (nextUnit: CssUnit) => {
    if (nextUnit === unit) return;
    const converted = {} as Record<BoxSideKey, string>;
    (["top", "right", "bottom", "left"] as BoxSideKey[]).forEach((key) => {
      const current = Number(nums[key]);
      converted[key] =
        nums[key] !== "" && Number.isFinite(current)
          ? String(convertSizeNumber(current, unit, nextUnit))
          : nums[key] || "0";
    });
    emitSides(converted, nextUnit);
  };

  return (
    <div className="epb-visual__dimensions">
      <div className="epb-visual__dimensions-head">
        <span className="epb-visual__dimensions-label">{label}</span>
        <div className="epb-visual__dimensions-units" role="group" aria-label={`${label} units`}>
          {DIMENSION_UNITS.map((u) => (
            <button
              key={u}
              type="button"
              className={`epb-visual__dimensions-unit ${unit === u ? "is-active" : ""}`}
              onClick={() => setUnit(u)}
            >
              {u}
            </button>
          ))}
        </div>
      </div>
      <div className="epb-visual__dimensions-row">
        {(["top", "right", "bottom", "left"] as BoxSideKey[]).map((side) => (
          <label key={side} className="epb-visual__dimensions-cell">
            <input
              type="number"
              value={nums[side]}
              placeholder="0"
              step={unit === "px" ? 1 : 0.1}
              onChange={(e) => setSide(side, e.target.value)}
              aria-label={`${label} ${side}`}
            />
            <span>{side}</span>
          </label>
        ))}
        <button
          type="button"
          className={`epb-visual__dimensions-link ${linked ? "is-active" : ""}`}
          title={linked ? "Unlink values" : "Link values"}
          aria-label={linked ? "Unlink values" : "Link values"}
          aria-pressed={linked}
          onClick={() => {
            const next = !linked;
            setLinked(next);
            if (next) emitSides(nums, unit, true);
          }}
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            {linked ? (
              <path
                d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 0 0-7.07-7.07L11 4.93M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07L13 19.07"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            ) : (
              <path
                d="M10 13a5 5 0 0 0 7.07 0l1.4-1.4M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 1 0 7.07 7.07l1.4-1.4M8 8l8 8"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            )}
          </svg>
        </button>
      </div>
    </div>
  );
}

const FONT_FAMILY_OPTIONS: { label: string; value: string }[] = [
  { label: "Default", value: "" },
  { label: "System UI", value: "system-ui, -apple-system, Segoe UI, sans-serif" },
  { label: "Georgia", value: "Georgia, 'Times New Roman', serif" },
  { label: "Times New Roman", value: "'Times New Roman', Times, serif" },
  { label: "Courier New", value: "'Courier New', Courier, monospace" },
  { label: "Outfit", value: "'Outfit', system-ui, sans-serif" },
  { label: "DM Sans", value: "'DM Sans', system-ui, sans-serif" },
  { label: "Space Grotesk", value: "'Space Grotesk', system-ui, sans-serif" },
  { label: "IBM Plex Sans", value: "'IBM Plex Sans', system-ui, sans-serif" },
  { label: "IBM Plex Mono", value: "'IBM Plex Mono', ui-monospace, monospace" },
  { label: "Manrope", value: "'Manrope', system-ui, sans-serif" },
  { label: "Sora", value: "'Sora', system-ui, sans-serif" },
  { label: "Fraunces", value: "'Fraunces', Georgia, serif" },
  { label: "Playfair Display", value: "'Playfair Display', Georgia, serif" },
  { label: "Source Serif 4", value: "'Source Serif 4', Georgia, serif" },
];

const FONT_WEIGHT_OPTIONS: { label: string; value: string }[] = [
  { label: "Default", value: "" },
  { label: "300 — Light", value: "300" },
  { label: "400 — Regular", value: "400" },
  { label: "500 — Medium", value: "500" },
  { label: "600 — Semi-bold", value: "600" },
  { label: "700 — Bold", value: "700" },
  { label: "800 — Extra bold", value: "800" },
];

const VISUAL_GOOGLE_FONTS_HREF =
  "https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Space+Grotesk:wght@400;500;600;700&display=swap";

function ensureVisualEditorFonts() {
  if (typeof document === "undefined") return;
  if (!window.epbBuilderData?.pluginSettings?.google_fonts_enabled) return;
  if (document.getElementById("epb-visual-fonts")) return;
  const link = document.createElement("link");
  link.id = "epb-visual-fonts";
  link.rel = "stylesheet";
  link.href = VISUAL_GOOGLE_FONTS_HREF;
  document.head.appendChild(link);
}

function styleOf(widget: VisualWidget, device: DevicePreview = "desktop"): WidgetStyle {
  return resolveWidgetStyle(widget, device);
}

function previewStyle(widget: VisualWidget, device: DevicePreview = "desktop"): CSSProperties {
  const s = styleOf(widget, device);
  return {
    marginTop: s.marginTop || undefined,
    marginRight: s.marginRight || undefined,
    marginBottom: s.marginBottom || undefined,
    marginLeft: s.marginLeft || undefined,
    paddingTop: s.paddingTop || undefined,
    paddingRight: s.paddingRight || undefined,
    paddingBottom: s.paddingBottom || undefined,
    paddingLeft: s.paddingLeft || undefined,
    fontFamily: s.fontFamily || undefined,
    fontSize: s.fontSize || undefined,
    fontWeight: (s.fontWeight as CSSProperties["fontWeight"]) || undefined,
    lineHeight: s.lineHeight || undefined,
    letterSpacing: s.letterSpacing || undefined,
    textTransform: s.textTransform === "none" ? undefined : s.textTransform,
    borderRadius: s.borderRadius || undefined,
    border:
      s.borderStyle !== "none" && s.borderWidth && s.borderWidth !== "0px"
        ? `${s.borderWidth} ${s.borderStyle} ${s.borderColor}`
        : undefined,
    boxShadow: s.boxShadow !== "none" ? s.boxShadow : undefined,
    opacity: s.opacity !== 1 ? s.opacity : undefined,
    background: s.background !== "transparent" ? s.background : undefined,
    maxWidth: s.maxWidth !== "100%" ? s.maxWidth : undefined,
    zIndex: s.zIndex !== "" ? (Number(s.zIndex) as CSSProperties["zIndex"]) : undefined,
    position: s.zIndex !== "" ? "relative" : undefined,
  };
}

function WidgetCard({
  widget,
  pageId = 0,
  devicePreview = "desktop",
  selected,
  onSelect,
  onRemove,
  onDragStart,
  onDragEnd,
  onContextMenu,
}: {
  widget: VisualWidget;
  pageId?: number;
  devicePreview?: DevicePreview;
  selected: boolean;
  onSelect: () => void;
  onRemove: () => void;
  onDragStart: (e: DragEvent) => void;
  onDragEnd: () => void;
  onContextMenu?: (e: MouseEvent) => void;
}) {
  return (
    <div
      className={`epb-visual-widget ${selected ? "is-selected" : ""}`}
      draggable
      onDragStart={(e) => {
        e.stopPropagation();
        onDragStart(e);
      }}
      onDragEnd={onDragEnd}
      onClick={(e) => {
        e.stopPropagation();
        onSelect();
      }}
      onContextMenu={onContextMenu}
    >
      <div className="epb-visual-widget__chrome">
        <span className="epb-visual-widget__drag" title="Drag to move" aria-hidden="true">
          ⋮⋮
        </span>
        <span className="epb-visual-widget__type" title={widgetLabel(widget.type)}>
          <span className="epb-visual-widget__type-icon" aria-hidden="true">
            {WIDGET_ICONS[widget.type]}
          </span>
        </span>
        <ChromeRemoveButton
          title="Remove widget"
          onClick={(e) => {
            e.stopPropagation();
            onRemove();
          }}
        />
      </div>
      <div className="epb-visual-widget__preview" style={previewStyle(widget, devicePreview)}>
        {renderWidgetPreview(widget, pageId)}
      </div>
    </div>
  );
}

function softBreaks(value: string): string {
  return String(value || "")
    .replace(/<br\s*\/?>/gi, "\n")
    .replace(/\\n/g, "\n")
    .replace(/&nbsp;/gi, " ");
}

function renderWidgetPreview(widget: VisualWidget, pageId = 0) {
  switch (widget.type) {
    case "heading": {
      const Tag = widget.tag;
      return (
        <Tag style={{ margin: 0, textAlign: widget.align, color: widget.color, whiteSpace: "pre-line" }}>
          {softBreaks(widget.content)}
        </Tag>
      );
    }
    case "text":
      return (
        <p style={{ margin: 0, textAlign: widget.align, color: widget.color, whiteSpace: "pre-line" }}>
          {softBreaks(widget.content)}
        </p>
      );
    case "button":
      return (
        <div style={{ textAlign: widget.align }}>
          <span
            style={{
              display: widget.fullWidth ? "block" : "inline-block",
              textAlign: "center",
              padding: `${widget.paddingY || "10px"} ${widget.paddingX || "18px"}`,
              borderRadius: styleOf(widget).borderRadius || "0px",
              background: widget.background,
              color: widget.textColor,
              fontWeight: 700,
              fontSize: 13,
            }}
          >
            {widget.label}
          </span>
        </div>
      );
    case "image":
      return (
        <div style={{ textAlign: widget.align }}>
          <img
            src={widget.src}
            alt={widget.alt}
            style={{
              width: widget.width || "100%",
              maxWidth: "100%",
              borderRadius: widget.borderRadius,
              objectFit: widget.objectFit,
              display: "inline-block",
            }}
          />
        </div>
      );
    case "spacer":
      return (
        <div
          style={{
            height: typeof widget.height === "number" ? `${widget.height}px` : widget.height,
            background: "repeating-linear-gradient(90deg,#e2e8f0,#e2e8f0 8px,transparent 8px,transparent 16px)",
          }}
        />
      );
    case "divider":
      return (
        <div style={{ textAlign: widget.align }}>
          <hr
            style={{
              border: "none",
              borderTop: `${typeof widget.thickness === "number" ? `${widget.thickness}px` : widget.thickness || "1px"} solid ${widget.color}`,
              width: widget.width || "100%",
              margin: "0 auto",
              display: "inline-block",
            }}
          />
        </div>
      );
    case "icon-box":
      return (
        <div style={{ textAlign: widget.align }}>
          <div style={{ fontSize: widget.iconSize || "24px" }}>{widget.icon}</div>
          <strong style={{ color: widget.titleColor }}>{widget.title}</strong>
          <p style={{ margin: "6px 0 0", color: widget.textColor || "#64748b", fontSize: 13 }}>{widget.text}</p>
        </div>
      );
    case "html":
      return <p className="epb-visual-widget__preview-text">{widget.content.replace(/<[^>]+>/g, " ").slice(0, 180)}</p>;
    case "posts-grid":
    case "posts-list":
    case "posts-carousel":
      return <PostsWidgetPreview widget={widget} pageId={pageId} />;
    default: {
      const html = compileWidgetHtml({
        ...widget,
        style: {
          ...styleOf(widget),
          marginTop: "0px",
          marginRight: "0px",
          marginBottom: "0px",
          marginLeft: "0px",
        },
      });
      return <div className="epb-visual-widget__compiled" dangerouslySetInnerHTML={{ __html: html }} />;
    }
  }
}

function Field({ label, children }: { label: string; children: ReactNode }) {
  return (
    <div className="epb-visual__field">
      <span className="epb-visual__field-label">{label}</span>
      {children}
    </div>
  );
}

function ImageMediaControl({
  url,
  onChange,
  title = "Select image",
}: {
  url: string;
  onChange: (url: string, meta?: { alt: string; title: string }) => void;
  title?: string;
}) {
  const [showUrl, setShowUrl] = useState(false);

  const pick = () => {
    const opened = openImagePicker(
      (nextUrl, _id, meta) => {
        onChange(nextUrl, meta ? { alt: meta.alt, title: meta.title } : undefined);
      },
      { title, button: "Insert image" }
    );
    if (!opened) {
      setShowUrl(true);
      alert("Media library is not available. Paste an image URL instead.");
    }
  };

  return (
    <div className="epb-visual-media">
      {url ? (
        <div className="epb-visual-media__preview">
          <img src={url} alt="" />
          <div className="epb-visual-media__toolbar">
            <button type="button" onClick={pick}>
              Change
            </button>
            <button type="button" onClick={() => onChange("")}>
              Remove
            </button>
          </div>
        </div>
      ) : (
        <button type="button" className="epb-visual-media__empty" onClick={pick}>
          <span aria-hidden="true">+</span>
          Choose image
        </button>
      )}
      <div className="epb-visual-media__actions">
        {url ? (
          <button type="button" className="epb-visual__link-btn" onClick={pick}>
            Choose from library
          </button>
        ) : null}
        <button type="button" className="epb-visual__link-btn" onClick={() => setShowUrl((v) => !v)}>
          {showUrl ? "Hide URL" : "Or paste URL"}
        </button>
      </div>
      {showUrl && (
        <input
          type="url"
          value={url}
          placeholder="https://example.com/image.jpg"
          onChange={(e) => onChange(e.target.value)}
        />
      )}
    </div>
  );
}

function WidgetContentSettings({
  widget,
  onChange,
  pageId = 0,
}: {
  widget: VisualWidget;
  onChange: (patch: Partial<VisualWidget>) => void;
  pageId?: number;
}) {
  return (
    <div className="epb-visual__fields">
      {widget.type === "heading" && (
        <>
          <Field label="Text">
            <input type="text" value={widget.content} onChange={(e) => onChange({ content: e.target.value })} />
          </Field>
          <Field label="Tag">
            <select value={widget.tag} onChange={(e) => onChange({ tag: e.target.value as typeof widget.tag })}>
              <option value="h1">H1</option>
              <option value="h2">H2</option>
              <option value="h3">H3</option>
              <option value="h4">H4</option>
            </select>
          </Field>
          <Field label="Align">
            <select value={widget.align} onChange={(e) => onChange({ align: e.target.value as typeof widget.align })}>
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </Field>
          <ColorField label="Color" value={widget.color} onChange={(next) => onChange({ color: next })} />
        </>
      )}
      {widget.type === "text" && (
        <>
          <Field label="Text">
            <textarea rows={4} value={widget.content} onChange={(e) => onChange({ content: e.target.value })} />
          </Field>
          <Field label="Align">
            <select value={widget.align} onChange={(e) => onChange({ align: e.target.value as typeof widget.align })}>
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </Field>
          <ColorField label="Color" value={widget.color} onChange={(next) => onChange({ color: next })} />
        </>
      )}
      {widget.type === "button" && (
        <>
          <Field label="Label">
            <input type="text" value={widget.label} onChange={(e) => onChange({ label: e.target.value })} />
          </Field>
          <Field label="URL">
            <UrlSuggestField value={widget.url} onChange={(url) => onChange({ url })} />
          </Field>
          <Field label="Align">
            <select value={widget.align} onChange={(e) => onChange({ align: e.target.value as typeof widget.align })}>
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </Field>
          <ColorField
            label="Background"
            value={widget.background}
            onChange={(next) => onChange({ background: next })}
            allowClear
          />
          <ColorField label="Text color" value={widget.textColor} onChange={(next) => onChange({ textColor: next })} />
          <label className="epb-visual__check">
            <input
              type="checkbox"
              checked={widget.fullWidth}
              onChange={(e) => onChange({ fullWidth: e.target.checked })}
            />
            Full width
          </label>
          <div className="epb-visual__field-row">
            <SizeField
              label="Padding X"
              value={widget.paddingX}
              units={SIZE_UNIT_SETS.spacing}
              onChange={(next) => onChange({ paddingX: next || "0px" })}
            />
            <SizeField
              label="Padding Y"
              value={widget.paddingY}
              units={SIZE_UNIT_SETS.spacing}
              onChange={(next) => onChange({ paddingY: next || "0px" })}
            />
          </div>
        </>
      )}
      {widget.type === "image" && (
        <>
          <Field label="Image">
            <ImageMediaControl
              url={widget.src}
              onChange={(url, meta) =>
                onChange({
                  src: url,
                  alt: meta?.alt || widget.alt || meta?.title || "",
                })
              }
              title="Select image"
            />
          </Field>
          <Field label="Alt text">
            <input type="text" value={widget.alt} onChange={(e) => onChange({ alt: e.target.value })} />
          </Field>
          <SizeField
            label="Width"
            value={widget.width}
            units={SIZE_UNIT_SETS.size}
            onChange={(next) => onChange({ width: next || "100%" })}
          />
          <Field label="Align">
            <select value={widget.align} onChange={(e) => onChange({ align: e.target.value as typeof widget.align })}>
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </Field>
          <Field label="Object fit">
            <select
              value={widget.objectFit}
              onChange={(e) => onChange({ objectFit: e.target.value as typeof widget.objectFit })}
            >
              <option value="cover">Cover</option>
              <option value="contain">Contain</option>
              <option value="fill">Fill</option>
            </select>
          </Field>
          <SizeField
            label="Radius"
            value={widget.borderRadius}
            units={SIZE_UNIT_SETS.border}
            onChange={(next) => onChange({ borderRadius: next || "0px" })}
          />
        </>
      )}
      {widget.type === "spacer" && (
        <SizeField
          label="Height"
          value={typeof widget.height === "number" ? `${widget.height}px` : widget.height}
          units={SIZE_UNIT_SETS.size}
          onChange={(next) => onChange({ height: next || "24px" })}
        />
      )}
      {widget.type === "divider" && (
        <>
          <ColorField label="Color" value={widget.color} onChange={(next) => onChange({ color: next })} />
          <SizeField
            label="Thickness"
            value={typeof widget.thickness === "number" ? `${widget.thickness}px` : widget.thickness}
            units={SIZE_UNIT_SETS.border}
            onChange={(next) => onChange({ thickness: next || "1px" })}
          />
          <SizeField
            label="Width"
            value={widget.width}
            units={SIZE_UNIT_SETS.size}
            onChange={(next) => onChange({ width: next || "100%" })}
          />
          <Field label="Align">
            <select value={widget.align} onChange={(e) => onChange({ align: e.target.value as typeof widget.align })}>
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </Field>
        </>
      )}
      {widget.type === "icon-box" && (
        <>
          <Field label="Icon">
            <input type="text" value={widget.icon} onChange={(e) => onChange({ icon: e.target.value })} />
          </Field>
          <Field label="Title">
            <input type="text" value={widget.title} onChange={(e) => onChange({ title: e.target.value })} />
          </Field>
          <Field label="Text">
            <textarea rows={3} value={widget.text} onChange={(e) => onChange({ text: e.target.value })} />
          </Field>
          <Field label="Align">
            <select value={widget.align} onChange={(e) => onChange({ align: e.target.value as typeof widget.align })}>
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </Field>
          <SizeField
            label="Icon size"
            value={widget.iconSize}
            units={SIZE_UNIT_SETS.font}
            onChange={(next) => onChange({ iconSize: next || "28px" })}
          />
          <ColorField
            label="Title color"
            value={widget.titleColor}
            onChange={(next) => onChange({ titleColor: next })}
          />
          <ColorField label="Text color" value={widget.textColor} onChange={(next) => onChange({ textColor: next })} />
        </>
      )}
      <AdvancedWidgetFields widget={widget} onChange={onChange} pageId={pageId} />
      <WidgetLinkFields widget={widget} onChange={onChange} />
    </div>
  );
}

const WIDGETS_WITH_OWN_LINK_UI = new Set([
  "button",
  "dual-button",
  "video",
  "social-icons",
  "share-buttons",
  "search",
  "tabs",
  "accordion",
  "toggle",
  "html",
  "shortcode",
  "slides",
  "price-table",
  "call-to-action",
  "posts-grid",
  "posts-list",
  "posts-carousel",
]);

function WidgetLinkFields({
  widget,
  onChange,
}: {
  widget: VisualWidget;
  onChange: (patch: Partial<VisualWidget>) => void;
}) {
  if (WIDGETS_WITH_OWN_LINK_UI.has(widget.type)) return null;
  return (
    <div className="epb-visual__group">
      <h4 className="epb-visual__group-title">Link</h4>
      <Field label="URL">
        <UrlSuggestField
          value={widget.linkUrl || ""}
          onChange={(url) => onChange({ linkUrl: url })}
          placeholder="Search pages or paste a URL"
        />
      </Field>
      <p className="epb-visual__hint">Optional. Makes the whole widget clickable.</p>
      <label className="epb-visual__check">
        <input
          type="checkbox"
          checked={!!widget.linkNewTab}
          onChange={(e) => onChange({ linkNewTab: e.target.checked })}
        />
        Open in new tab
      </label>
    </div>
  );
}

function WidgetStyleSettings({
  widget,
  devicePreview,
  onChange,
  onPatchStyle,
  onClearOverrides,
}: {
  widget: VisualWidget;
  devicePreview: DevicePreview;
  onChange: (patch: Partial<VisualWidget>) => void;
  onPatchStyle: (partial: Partial<WidgetStyle>) => void;
  onClearOverrides?: () => void;
}) {
  const style = styleOf(widget, devicePreview);
  const patchStyle = (partial: Partial<WidgetStyle>) => onPatchStyle(partial);

  return (
    <div className="epb-visual__fields">
      <ResponsiveDeviceBanner device={devicePreview} />
      <div className="epb-visual__group">
        <h4 className="epb-visual__group-title">Spacing</h4>
        <DimensionsField
          label="Margin"
          values={{
            top: style.marginTop,
            right: style.marginRight,
            bottom: style.marginBottom,
            left: style.marginLeft,
          }}
          onChange={(next) =>
            patchStyle({
              marginTop: next.top,
              marginRight: next.right,
              marginBottom: next.bottom,
              marginLeft: next.left,
            })
          }
        />
        <DimensionsField
          label="Padding"
          values={{
            top: style.paddingTop,
            right: style.paddingRight,
            bottom: style.paddingBottom,
            left: style.paddingLeft,
          }}
          onChange={(next) =>
            patchStyle({
              paddingTop: next.top,
              paddingRight: next.right,
              paddingBottom: next.bottom,
              paddingLeft: next.left,
            })
          }
        />
        <Field label="Z-index">
          <input
            type="number"
            value={style.zIndex}
            placeholder="auto"
            onChange={(e) => patchStyle({ zIndex: e.target.value })}
          />
        </Field>
      </div>

      <div className="epb-visual__group">
        <h4 className="epb-visual__group-title">Typography</h4>
        <Field label="Font family">
          <select
            value={style.fontFamily || ""}
            onChange={(e) => patchStyle({ fontFamily: e.target.value })}
            style={style.fontFamily ? { fontFamily: style.fontFamily } : undefined}
          >
            {FONT_FAMILY_OPTIONS.map((opt) => (
              <option key={opt.value || "default"} value={opt.value} style={opt.value ? { fontFamily: opt.value } : undefined}>
                {opt.label}
              </option>
            ))}
          </select>
        </Field>

        <div className="epb-visual__field-row">
          <SizeField
            label="Font size"
            value={style.fontSize}
            units={SIZE_UNIT_SETS.font}
            onChange={(next) => patchStyle({ fontSize: next })}
            placeholder="16"
          />
          <Field label="Font weight">
            <select value={style.fontWeight || ""} onChange={(e) => patchStyle({ fontWeight: e.target.value })}>
              {FONT_WEIGHT_OPTIONS.map((opt) => (
                <option key={opt.value || "default"} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </Field>
        </div>

        <div className="epb-visual__field-row">
          <SizeField
            label="Line height"
            value={style.lineHeight}
            units={SIZE_UNIT_SETS.lineHeight}
            fallbackUnit=""
            onChange={(next) => patchStyle({ lineHeight: next })}
            placeholder="1.5"
            step={0.05}
          />
          <SizeField
            label="Letter spacing"
            value={style.letterSpacing}
            units={SIZE_UNIT_SETS.font}
            onChange={(next) => patchStyle({ letterSpacing: next || "0px" })}
          />
        </div>

        <Field label="Text transform">
          <select
            value={style.textTransform}
            onChange={(e) => patchStyle({ textTransform: e.target.value as WidgetStyle["textTransform"] })}
          >
            <option value="none">None</option>
            <option value="uppercase">Uppercase</option>
            <option value="capitalize">Capitalize</option>
            <option value="lowercase">Lowercase</option>
          </select>
        </Field>
      </div>

      <div className="epb-visual__group">
        <h4 className="epb-visual__group-title">Size & background</h4>
        <SizeField
          label="Max width"
          value={style.maxWidth}
          units={SIZE_UNIT_SETS.size}
          onChange={(next) => patchStyle({ maxWidth: next || "100%" })}
        />
        <ColorField
          label="Background"
          value={style.background}
          onChange={(next) => patchStyle({ background: next })}
          allowClear
        />
      </div>

      <div className="epb-visual__group">
        <h4 className="epb-visual__group-title">Border</h4>
        <div className="epb-visual__field-row">
          <Field label="Border style">
            <select
              value={style.borderStyle}
              onChange={(e) => patchStyle({ borderStyle: e.target.value as WidgetStyle["borderStyle"] })}
            >
              <option value="none">None</option>
              <option value="solid">Solid</option>
              <option value="dashed">Dashed</option>
              <option value="dotted">Dotted</option>
            </select>
          </Field>
          <SizeField
            label="Border width"
            value={style.borderWidth}
            units={SIZE_UNIT_SETS.border}
            onChange={(next) => patchStyle({ borderWidth: next || "0px" })}
          />
        </div>
        <ColorField
          label="Border color"
          value={style.borderColor}
          onChange={(next) => patchStyle({ borderColor: next })}
        />
        <SizeField
          label="Radius"
          value={style.borderRadius}
          units={SIZE_UNIT_SETS.border}
          onChange={(next) => patchStyle({ borderRadius: next || "0px" })}
        />
      </div>

      <div className="epb-visual__group">
        <h4 className="epb-visual__group-title">Effects</h4>
        <Field label="Box shadow">
          <select value={style.boxShadow} onChange={(e) => patchStyle({ boxShadow: e.target.value })}>
            <option value="none">None</option>
            <option value="0 1px 3px rgba(15,23,42,.12)">Soft</option>
            <option value="0 8px 24px rgba(15,23,42,.14)">Medium</option>
            <option value="0 16px 40px rgba(15,23,42,.18)">Strong</option>
          </select>
        </Field>

        <Field label={`Element opacity (${Math.round(style.opacity * 100)}%)`}>
          <input
            type="range"
            min={0.1}
            max={1}
            step={0.05}
            value={style.opacity}
            onChange={(e) => patchStyle({ opacity: Number(e.target.value) })}
          />
        </Field>
      </div>

      <button
        type="button"
        className="epb-visual__reset-btn"
        onClick={() =>
          devicePreview === "desktop"
            ? onChange({ style: { ...DEFAULT_WIDGET_STYLE } })
            : onClearOverrides?.()
        }
      >
        {devicePreview === "desktop" ? "Reset style" : `Reset ${DEVICE_PREVIEW_LABELS[devicePreview]} overrides`}
      </button>
    </div>
  );
}
