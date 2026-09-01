import type { PageCode } from "../types";
import type {
  ColumnChild,
  InnerSection,
  VisualColumn,
  VisualDocument,
  VisualSection,
} from "./types";
import { compileWidgetHtml, VISUAL_WIDGET_CSS } from "./compileWidgets";
import { collectResponsiveCss } from "./responsive";

function sectionStyle(settings: VisualSection["settings"], isInner = false): string {
  const parts = [
    `padding:${settings.padding || "40px 20px"}`,
    `background:${settings.background || "transparent"}`,
    `color:${settings.textColor || "inherit"}`,
  ];
  if (settings.zIndex !== undefined && settings.zIndex !== null && String(settings.zIndex).trim() !== "") {
    parts.push(`z-index:${settings.zIndex}`);
    parts.push("position:relative");
  }
  if (
    settings.minHeight !== undefined &&
    settings.minHeight !== null &&
    settings.minHeight !== "" &&
    settings.minHeight !== 0 &&
    settings.minHeight !== "0" &&
    settings.minHeight !== "0px"
  ) {
    const minH = typeof settings.minHeight === "number" ? `${settings.minHeight}px` : String(settings.minHeight);
    parts.push(`min-height:${minH}`);
  }
  if (settings.backgroundImage) {
    const raw = settings.backgroundImage;
    if (raw.includes("gradient(") || raw.includes("url(")) {
      parts.push(`background-image:${raw}`);
    } else {
      parts.push(`background-image:linear-gradient(rgba(15,23,42,.55),rgba(15,23,42,.35)),url('${raw.replace(/'/g, "%27")}')`);
    }
    parts.push("background-size:cover");
    parts.push("background-position:center");
  }
  if (!isInner && settings.fullWidth) {
    parts.push("width:100%");
  }
  return parts.join(";");
}

function compileChild(child: ColumnChild): string {
  if (child.type === "inner-section") {
    return compileInnerSection(child);
  }
  return compileWidgetHtml(child);
}

function compileColumn(column: VisualColumn): string {
  const align =
    column.settings.verticalAlign === "middle"
      ? "center"
      : column.settings.verticalAlign === "bottom"
        ? "flex-end"
        : "flex-start";
  const style = [
    `flex:0 0 ${column.settings.width}%`,
    `max-width:${column.settings.width}%`,
    `padding:${column.settings.padding || "12px"}`,
    `background:${column.settings.background || "transparent"}`,
    "box-sizing:border-box",
    "display:flex",
    "flex-direction:column",
    `justify-content:${align}`,
  ];
  if (column.settings.zIndex !== undefined && column.settings.zIndex !== null && String(column.settings.zIndex).trim() !== "") {
    style.push(`z-index:${column.settings.zIndex}`);
    style.push("position:relative");
  }
  const body = column.children.map(compileChild).join("\n");
  return `<div class="epb-col" data-epb-id="${column.id}" style="${style.join(";")}">${body}</div>`;
}

function cssLength(value: number | string | undefined | null, fallback: string): string {
  if (value === undefined || value === null || value === "") return fallback;
  if (typeof value === "number") return `${value}px`;
  return String(value);
}

function compileInnerSection(section: InnerSection): string {
  const gap = cssLength(section.settings.gap, "0px");
  const inner = section.columns.map(compileColumn).join("\n");
  return `<div class="epb-inner-section" data-epb-id="${section.id}" style="${sectionStyle(section.settings, true)}"><div class="epb-container" style="display:flex;flex-wrap:wrap;gap:${gap};width:100%;">${inner}</div></div>`;
}

function compileSection(section: VisualSection): string {
  const gap = cssLength(section.settings.gap, "0px");
  const max = cssLength(section.settings.contentWidth, "1140px");
  const cols = section.columns.map(compileColumn).join("\n");
  const container = `<div class="epb-container" style="max-width:${max};margin:0 auto;display:flex;flex-wrap:wrap;gap:${gap};width:100%;">${cols}</div>`;
  return `<section class="epb-section" data-epb-id="${section.id}" style="${sectionStyle(section.settings)}">${container}</section>`;
}

export function compileVisualDocument(doc: VisualDocument): PageCode {
  const html = `<div class="epb-visual-root">\n${doc.sections.map(compileSection).join("\n")}\n</div>`;
  const responsiveCss = collectResponsiveCss(doc);
  const css = `/* WPVisualX — Visual mode */
.epb-visual-root { width: 100%; }
.epb-section { box-sizing: border-box; }
.epb-col { min-width: 0; }
.epb-w-heading, .epb-w-text, .epb-w-button, .epb-w-image, .epb-w-iconbox { word-break: break-word; }
${VISUAL_WIDGET_CSS}
@media (max-width: 768px) {
  .epb-container { flex-direction: column !important; }
  .epb-col { flex: 1 1 100% !important; max-width: 100% !important; }
}
${responsiveCss}`;
  return { html, css, js: "" };
}

const VISUAL_CSS_MARKER = "/* WPVisualX — Visual mode */";

export function mergeVisualCompile(existing: PageCode, compiled: PageCode): PageCode {
  const existingCss = (existing.css || "").trim();
  const compiledCss = (compiled.css || "").trim();
  let css = compiledCss;

  if (existingCss) {
    if (existingCss.includes(VISUAL_CSS_MARKER) || /\/\* .+ — Visual mode \*\//.test(existingCss)) {
      const withoutVisual = existingCss
        .replace(/\n*\/\* .+ — Visual mode \*\/[\s\S]*$/m, "")
        .trim();
      css = withoutVisual ? `${withoutVisual}\n\n${compiledCss}` : compiledCss;
    } else {
      css = compiledCss ? `${existingCss}\n\n${compiledCss}` : existingCss;
    }
  }

  return {
    html: compiled.html,
    css,
    js: "",
  };
}
