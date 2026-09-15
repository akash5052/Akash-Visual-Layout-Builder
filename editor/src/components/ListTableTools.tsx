import { useCallback, useMemo, useState } from "react";
import type { ListStatusFilter } from "../types";

const STATUS_FILTERS: { id: ListStatusFilter; label: string }[] = [
  { id: "publish", label: "Published" },
  { id: "draft", label: "Draft" },
  { id: "all", label: "All" },
  { id: "trash", label: "Trash" },
];

export function getDefaultListStatus(): ListStatusFilter {
  const value = window.akashVisualLayoutBuilderData?.defaultListStatus;
  if (value === "all" || value === "publish" || value === "draft" || value === "trash") {
    return value;
  }
  return "publish";
}

interface ListStatusFiltersProps {
  value: ListStatusFilter;
  onChange: (value: ListStatusFilter) => void;
}

export function ListStatusFilters({ value, onChange }: ListStatusFiltersProps) {
  return (
    <div className="akash-visual-layout-builder-list-filters" role="tablist" aria-label="Status filter">
      {STATUS_FILTERS.map((filter) => (
        <button
          key={filter.id}
          type="button"
          role="tab"
          aria-selected={value === filter.id}
          className={`akash-visual-layout-builder-list-filters__btn ${value === filter.id ? "akash-visual-layout-builder-list-filters__btn--active" : ""}`}
          onClick={() => {
            if (value !== filter.id) {
              onChange(filter.id);
            }
          }}
        >
          {filter.label}
        </button>
      ))}
    </div>
  );
}

interface ListPaginationProps {
  page: number;
  totalPages: number;
  total: number;
  perPage: number;
  onPageChange: (page: number) => void;
}

export function ListPagination({ page, totalPages, total, perPage, onPageChange }: ListPaginationProps) {
  if (total <= perPage) {
    return total > 0 ? (
      <p className="akash-visual-layout-builder-list-pagination__meta">{total} item{total !== 1 ? "s" : ""}</p>
    ) : null;
  }

  const start = (page - 1) * perPage + 1;
  const end = Math.min(page * perPage, total);

  return (
    <div className="akash-visual-layout-builder-list-pagination">
      <p className="akash-visual-layout-builder-list-pagination__meta">
        Showing {start}–{end} of {total}
      </p>
      <div className="akash-visual-layout-builder-list-pagination__controls">
        <button
          type="button"
          className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm"
          disabled={page <= 1}
          onClick={() => onPageChange(page - 1)}
        >
          ← Previous
        </button>
        <span className="akash-visual-layout-builder-list-pagination__page">
          Page {page} of {totalPages}
        </span>
        <button
          type="button"
          className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm"
          disabled={page >= totalPages}
          onClick={() => onPageChange(page + 1)}
        >
          Next →
        </button>
      </div>
    </div>
  );
}

interface BulkActionBarProps {
  selectedCount: number;
  statusFilter: ListStatusFilter;
  acting: boolean;
  onClear: () => void;
  onTrash: () => void;
  onRestore: () => void;
  onDeletePermanent: () => void;
}

export function BulkActionBar({
  selectedCount,
  statusFilter,
  acting,
  onClear,
  onTrash,
  onRestore,
  onDeletePermanent,
}: BulkActionBarProps) {
  if (selectedCount === 0) {
    return null;
  }

  return (
    <div className="akash-visual-layout-builder-bulk-bar">
      <span className="akash-visual-layout-builder-bulk-bar__count">{selectedCount} selected</span>
      <div className="akash-visual-layout-builder-bulk-bar__actions">
        {statusFilter === "trash" ? (
          <>
            <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm" disabled={acting} onClick={onRestore}>
              Restore
            </button>
            <button
              type="button"
              className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm akash-visual-layout-builder-btn--danger"
              disabled={acting}
              onClick={onDeletePermanent}
            >
              Delete permanently
            </button>
          </>
        ) : (
          <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm akash-visual-layout-builder-btn--danger" disabled={acting} onClick={onTrash}>
            Move to trash
          </button>
        )}
        <button type="button" className="akash-visual-layout-builder-btn akash-visual-layout-builder-btn--ghost akash-visual-layout-builder-btn--sm" disabled={acting} onClick={onClear}>
          Clear selection
        </button>
      </div>
    </div>
  );
}

export function useListSelection<T extends string | number>() {
  const [selected, setSelected] = useState<Set<T>>(new Set());

  const toggle = useCallback((id: T) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  }, []);

  const toggleAll = useCallback((ids: T[], checked: boolean) => {
    setSelected(checked ? new Set(ids) : new Set());
  }, []);

  const clear = useCallback(() => setSelected(new Set()), []);

  const selectedIds = useMemo(() => Array.from(selected), [selected]);

  return { selected, selectedIds, toggle, toggleAll, clear, setSelected };
}

export function formatListDate(value: string) {
  try {
    return new Date(value).toLocaleDateString(undefined, { month: "short", day: "numeric", year: "numeric" });
  } catch {
    return value;
  }
}

export function displayStatus(status: string): string {
  if (status === "publish") return "Published";
  if (status === "trash") return "Trash";
  if (status === "draft" || status === "pending" || status === "private") return "Draft";
  return status;
}

export function statusClass(status: string): string {
  if (status === "publish") return "publish";
  if (status === "trash") return "trash";
  return "draft";
}
