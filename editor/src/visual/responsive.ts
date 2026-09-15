import type { DevicePreview } from "../devicePreview";
import { DEVICE_BREAKPOINTS } from "../devicePreview";
import type {
  ColumnChild,
  ColumnSettings,
  InnerSection,
  ResponsiveOverrides,
  SectionSettings,
  VisualColumn,
  VisualDocument,
  VisualSection,
  VisualWidget,
  WidgetStyle,
} from "./types";
import { mergeWidgetStyle } from "./types";

export type { ResponsiveOverrides };

export type ResponsiveDevice = Exclude<DevicePreview, "desktop">;

export function resolveWidgetStyle(widget: VisualWidget, device: DevicePreview): WidgetStyle {
  const base = mergeWidgetStyle(widget.style);
  if (device === "desktop") return base;
  const tablet = widget.styleOverrides?.tablet || {};
  if (device === "tablet") return mergeWidgetStyle({ ...base, ...tablet });
  return mergeWidgetStyle({ ...base, ...tablet, ...(widget.styleOverrides?.mobile || {}) });
}

export function resolveSectionSettings(
  settings: SectionSettings,
  overrides: ResponsiveOverrides<SectionSettings> | undefined,
  device: DevicePreview
): SectionSettings {
  if (device === "desktop") return settings;
  const tablet = overrides?.tablet || {};
  if (device === "tablet") return { ...settings, ...tablet };
  return { ...settings, ...tablet, ...(overrides?.mobile || {}) };
}

export function resolveColumnSettings(
  settings: ColumnSettings,
  overrides: ResponsiveOverrides<ColumnSettings> | undefined,
  device: DevicePreview
): ColumnSettings {
  if (device === "desktop") return settings;
  const tablet = overrides?.tablet || {};
  if (device === "tablet") return { ...settings, ...tablet };
  return { ...settings, ...tablet, ...(overrides?.mobile || {}) };
}

export function widgetStyleToCssProperties(style: Partial<WidgetStyle>): Record<string, string> {
  const s = mergeWidgetStyle(style);
  const props: Record<string, string> = {};

  const set = (key: string, value?: string | number) => {
    if (value === undefined || value === null || value === "") return;
    props[key] = String(value);
  };

  set("margin-top", s.marginTop);
  set("margin-right", s.marginRight);
  set("margin-bottom", s.marginBottom);
  set("margin-left", s.marginLeft);
  set("padding-top", s.paddingTop);
  set("padding-right", s.paddingRight);
  set("padding-bottom", s.paddingBottom);
  set("padding-left", s.paddingLeft);
  set("font-family", s.fontFamily);
  set("font-size", s.fontSize);
  set("font-weight", s.fontWeight);
  set("line-height", s.lineHeight);
  set("letter-spacing", s.letterSpacing);
  if (s.textTransform !== "none") set("text-transform", s.textTransform);
  set("border-radius", s.borderRadius);
  if (s.borderStyle !== "none" && s.borderWidth && s.borderWidth !== "0px") {
    set("border", `${s.borderWidth} ${s.borderStyle} ${s.borderColor}`);
  }
  if (s.boxShadow !== "none") set("box-shadow", s.boxShadow);
  if (s.opacity !== 1) set("opacity", String(s.opacity));
  if (s.background !== "transparent") set("background", s.background);
  if (s.maxWidth !== "100%") set("max-width", s.maxWidth);
  if (s.zIndex !== undefined && s.zIndex !== null && String(s.zIndex).trim() !== "") {
    set("z-index", s.zIndex);
    set("position", "relative");
  }

  return props;
}

export function cssPropertiesToString(props: Record<string, string>): string {
  return Object.entries(props)
    .map(([key, value]) => `${key}:${value}`)
    .join(";");
}

export function sectionSettingsToCssProperties(settings: Partial<SectionSettings>): Record<string, string> {
  const props: Record<string, string> = {};
  const set = (key: string, value?: string | number) => {
    if (value === undefined || value === null || value === "") return;
    props[key] = String(value);
  };

  if (settings.padding !== undefined) set("padding", settings.padding);
  if (settings.background !== undefined) set("background", settings.background);
  if (settings.textColor !== undefined) set("color", settings.textColor);
  if (settings.gap !== undefined) {
    set("gap", typeof settings.gap === "number" ? `${settings.gap}px` : settings.gap);
  }
  if (
    settings.minHeight !== undefined &&
    settings.minHeight !== null &&
    settings.minHeight !== "" &&
    settings.minHeight !== 0 &&
    settings.minHeight !== "0" &&
    settings.minHeight !== "0px"
  ) {
    set(
      "min-height",
      typeof settings.minHeight === "number" ? `${settings.minHeight}px` : String(settings.minHeight)
    );
  }
  if (settings.contentWidth !== undefined) {
    set(
      "max-width",
      typeof settings.contentWidth === "number" ? `${settings.contentWidth}px` : String(settings.contentWidth)
    );
  }
  if (settings.zIndex !== undefined && settings.zIndex !== null && String(settings.zIndex).trim() !== "") {
    set("z-index", settings.zIndex);
    set("position", "relative");
  }

  return props;
}

export function columnSettingsToCssProperties(settings: Partial<ColumnSettings>): Record<string, string> {
  const props: Record<string, string> = {};
  const set = (key: string, value?: string | number) => {
    if (value === undefined || value === null || value === "") return;
    props[key] = String(value);
  };

  if (settings.width !== undefined) {
    set("flex", `0 0 ${settings.width}%`);
    set("max-width", `${settings.width}%`);
  }
  if (settings.padding !== undefined) set("padding", settings.padding);
  if (settings.background !== undefined) set("background", settings.background);
  if (settings.zIndex !== undefined && settings.zIndex !== null && String(settings.zIndex).trim() !== "") {
    set("z-index", settings.zIndex);
    set("position", "relative");
  }

  return props;
}

function appendResponsiveRules(
  rules: string[],
  selector: string,
  device: ResponsiveDevice,
  props: Record<string, string>
) {
  const css = cssPropertiesToString(props);
  if (!css) return;
  rules.push(`@media (max-width: ${DEVICE_BREAKPOINTS[device]}px) { ${selector} { ${css} } }`);
}

function visitWidget(widget: VisualWidget, rules: string[]) {
  const selector = `[data-akash-visual-layout-builder-id="${widget.id}"]`;
  if (widget.styleOverrides?.tablet) {
    appendResponsiveRules(rules, selector, "tablet", widgetStyleToCssProperties(widget.styleOverrides.tablet));
  }
  if (widget.styleOverrides?.mobile) {
    appendResponsiveRules(rules, selector, "mobile", widgetStyleToCssProperties(widget.styleOverrides.mobile));
  }
}

function visitColumn(column: VisualColumn, rules: string[]) {
  const selector = `[data-akash-visual-layout-builder-id="${column.id}"]`;
  if (column.settingsOverrides?.tablet) {
    appendResponsiveRules(
      rules,
      selector,
      "tablet",
      columnSettingsToCssProperties(column.settingsOverrides.tablet)
    );
  }
  if (column.settingsOverrides?.mobile) {
    appendResponsiveRules(
      rules,
      selector,
      "mobile",
      columnSettingsToCssProperties(column.settingsOverrides.mobile)
    );
  }
  column.children.forEach((child) => visitChild(child, rules));
}

function visitInnerSection(section: InnerSection, rules: string[]) {
  const selector = `[data-akash-visual-layout-builder-id="${section.id}"]`;
  if (section.settingsOverrides?.tablet) {
    appendResponsiveRules(
      rules,
      selector,
      "tablet",
      sectionSettingsToCssProperties(section.settingsOverrides.tablet)
    );
  }
  if (section.settingsOverrides?.mobile) {
    appendResponsiveRules(
      rules,
      selector,
      "mobile",
      sectionSettingsToCssProperties(section.settingsOverrides.mobile)
    );
  }
  section.columns.forEach((column) => visitColumn(column, rules));
}

function visitChild(child: ColumnChild, rules: string[]) {
  if (child.type === "inner-section") {
    visitInnerSection(child, rules);
    return;
  }
  visitWidget(child, rules);
}

function visitSection(section: VisualSection, rules: string[]) {
  const selector = `[data-akash-visual-layout-builder-id="${section.id}"]`;
  if (section.settingsOverrides?.tablet) {
    appendResponsiveRules(
      rules,
      selector,
      "tablet",
      sectionSettingsToCssProperties(section.settingsOverrides.tablet)
    );
  }
  if (section.settingsOverrides?.mobile) {
    appendResponsiveRules(
      rules,
      selector,
      "mobile",
      sectionSettingsToCssProperties(section.settingsOverrides.mobile)
    );
  }
  section.columns.forEach((column) => visitColumn(column, rules));
}

export function collectResponsiveCss(doc: VisualDocument): string {
  const rules: string[] = [];
  doc.sections.forEach((section) => visitSection(section, rules));
  return rules.length ? `\n${rules.join("\n")}\n` : "";
}

export function patchWidgetStyleForDevice(
  widget: VisualWidget,
  device: DevicePreview,
  partial: Partial<WidgetStyle>
): VisualWidget {
  if (device === "desktop") {
    return { ...widget, style: mergeWidgetStyle({ ...widget.style, ...partial }) };
  }
  const key = device;
  const nextOverride = { ...(widget.styleOverrides?.[key] || {}), ...partial };
  return {
    ...widget,
    styleOverrides: {
      ...(widget.styleOverrides || {}),
      [key]: nextOverride,
    },
  };
}

export function clearWidgetStyleOverrides(widget: VisualWidget, device: ResponsiveDevice): VisualWidget {
  if (!widget.styleOverrides?.[device]) return widget;
  const next = { ...widget.styleOverrides };
  delete next[device];
  return {
    ...widget,
    styleOverrides: Object.keys(next).length ? next : undefined,
  };
}
