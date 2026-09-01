import { useCallback, useEffect, useState } from "react";
import { bulkPages, deletePage, fetchPages, openContentView, restorePage } from "../api/wordpress";
import type { ContentPostType, ListStatusFilter, PageSummary, PaginatedResult } from "../types";
import { ContentQuickEditModal, CreateContentModal } from "./QuickEditModal";
import {
  BulkActionBar,
  ListPagination,
  ListStatusFilters,
  displayStatus,
  formatListDate,
  statusClass,
  getDefaultListStatus,
  useListSelection,
} from "./ListTableTools";

const PER_PAGE = 20;

interface ContentListViewProps {
  postType: ContentPostType;
  onCreate: (title: string) => Promise<void>;
  onOpen: (id: number) => void;
  onNotice: (type: "success" | "error", message: string) => void;
}

export function ContentListView({ postType, onCreate, onOpen, onNotice }: ContentListViewProps) {
  const [statusFilter, setStatusFilter] = useState<ListStatusFilter>(getDefaultListStatus);
  const [page, setPage] = useState(1);
  const [data, setData] = useState<PaginatedResult<PageSummary>>({
    items: [],
    total: 0,
    total_pages: 1,
    page: 1,
    per_page: PER_PAGE,
  });
  const [loading, setLoading] = useState(true);
  const [acting, setActing] = useState(false);
  const [rowBusyId, setRowBusyId] = useState<number | null>(null);
  const [quickEditItem, setQuickEditItem] = useState<PageSummary | null>(null);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const { selected, selectedIds, toggle, toggleAll, clear } = useListSelection<number>();

  const label = postType === "post" ? "Posts" : "Pages";
  const singular = postType === "post" ? "post" : "page";

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const result = await fetchPages({
        post_type: postType,
        status: statusFilter,
        page,
        per_page: PER_PAGE,
      });
      setData(result);
      clear();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : `Failed to load ${label.toLowerCase()}`);
    } finally {
      setLoading(false);
    }
  }, [clear, label, onNotice, page, postType, statusFilter]);

  useEffect(() => {
    load();
  }, [load]);

  const handleFilterChange = (next: ListStatusFilter) => {
    setStatusFilter(next);
    setPage(1);
  };

  const runBulk = async (action: "trash" | "restore" | "delete", force = false) => {
    if (selectedIds.length === 0) return;

    const confirmMessage =
      action === "delete"
        ? `Permanently delete ${selectedIds.length} selected item(s)? This cannot be undone.`
        : action === "trash"
          ? `Move ${selectedIds.length} selected item(s) to trash?`
          : `Restore ${selectedIds.length} selected item(s) from trash?`;

    if (!confirm(confirmMessage)) return;

    setActing(true);
    try {
      const result = await bulkPages(action, selectedIds, force);
      onNotice(result.processed > 0 ? "success" : "error", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Bulk action failed");
    } finally {
      setActing(false);
    }
  };

  const handleRowTrash = async (item: PageSummary) => {
    const name = item.title || `(untitled ${singular})`;
    if (!confirm(`Move "${name}" to trash?`)) return;

    setRowBusyId(item.id);
    try {
      const result = await deletePage(item.id, false);
      onNotice("success", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Delete failed");
    } finally {
      setRowBusyId(null);
    }
  };

  const handleRowRestore = async (item: PageSummary) => {
    setRowBusyId(item.id);
    try {
      const result = await restorePage(item.id);
      onNotice("success", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Restore failed");
    } finally {
      setRowBusyId(null);
    }
  };

  const handleRowDeletePermanent = async (item: PageSummary) => {
    const name = item.title || `(untitled ${singular})`;
    if (!confirm(`Permanently delete "${name}"? This cannot be undone.`)) return;

    setRowBusyId(item.id);
    try {
      const result = await deletePage(item.id, true);
      onNotice("success", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Delete failed");
    } finally {
      setRowBusyId(null);
    }
  };

  const allSelected = data.items.length > 0 && data.items.every((item) => selected.has(item.id));

  return (
    <div className="epb-screen epb-screen--list">
      <header className="epb-screen__header">
        <div>
          <h1 className="epb-screen__title">{label}</h1>
          <p className="epb-screen__subtitle">
            Manage your {label.toLowerCase()} built with WPVisualX.
          </p>
        </div>
        <button type="button" className="epb-btn epb-btn--primary" onClick={() => setShowCreateModal(true)}>
          + New {singular}
        </button>
      </header>

      <ListStatusFilters value={statusFilter} onChange={handleFilterChange} />

      <BulkActionBar
        selectedCount={selectedIds.length}
        statusFilter={statusFilter}
        acting={acting}
        onClear={clear}
        onTrash={() => runBulk("trash")}
        onRestore={() => runBulk("restore")}
        onDeletePermanent={() => runBulk("delete", true)}
      />

      {loading ? (
        <div className="epb-screen__empty">
          <p>Loading {label.toLowerCase()}…</p>
        </div>
      ) : data.items.length === 0 ? (
        <div className="epb-screen__empty">
          <p>
            {statusFilter === "trash"
              ? `No ${label.toLowerCase()} in trash.`
              : `No ${label.toLowerCase()} yet.`}
          </p>
          {statusFilter !== "trash" && (
            <button type="button" className="epb-btn epb-btn--ghost" onClick={() => setShowCreateModal(true)}>
              Create your first {singular}
            </button>
          )}
        </div>
      ) : (
        <div className="epb-content-table-wrap">
          <table className="epb-content-table">
            <thead>
              <tr>
                <th className="epb-content-table__check">
                  <input
                    type="checkbox"
                    aria-label="Select all"
                    checked={allSelected}
                    onChange={(e) => toggleAll(data.items.map((item) => item.id), e.target.checked)}
                  />
                </th>
                <th>Title</th>
                <th>Status</th>
                <th>Modified</th>
                <th />
              </tr>
            </thead>
            <tbody>
              {data.items.map((item) => {
                const isTrashed = item.status === "trash";
                const busy = rowBusyId === item.id;

                return (
                  <tr key={item.id} className={selected.has(item.id) ? "epb-content-table__row--selected" : ""}>
                    <td className="epb-content-table__check">
                      <input
                        type="checkbox"
                        aria-label={`Select ${item.title || "item"}`}
                        checked={selected.has(item.id)}
                        onChange={() => toggle(item.id)}
                      />
                    </td>
                    <td>
                      {isTrashed ? (
                        <span className="epb-content-table__title epb-content-table__title--static">
                          {item.title || "(no title)"}
                        </span>
                      ) : (
                        <button type="button" className="epb-content-table__title" onClick={() => onOpen(item.id)}>
                          {item.title || "(no title)"}
                        </button>
                      )}
                    </td>
                    <td>
                      <span className={`epb-status epb-status--${statusClass(item.status)}`}>
                        {displayStatus(item.status)}
                      </span>
                    </td>
                    <td className="epb-content-table__date">{formatListDate(item.modified)}</td>
                    <td className="epb-content-table__actions">
                      {isTrashed ? (
                        <>
                          <button
                            type="button"
                            className="epb-btn epb-btn--ghost epb-btn--sm"
                            disabled={busy}
                            onClick={() => handleRowRestore(item)}
                          >
                            {busy ? "Restoring…" : "Restore"}
                          </button>
                          <button
                            type="button"
                            className="epb-btn epb-btn--ghost epb-btn--sm epb-btn--danger"
                            disabled={busy}
                            onClick={() => handleRowDeletePermanent(item)}
                          >
                            Delete permanently
                          </button>
                        </>
                      ) : (
                        <>
                          <button
                            type="button"
                            className="epb-btn epb-btn--ghost epb-btn--sm"
                            onClick={() => setQuickEditItem(item)}
                          >
                            Quick Edit
                          </button>
                          <button
                            type="button"
                            className="epb-btn epb-btn--ghost epb-btn--sm"
                            onClick={() => {
                              try {
                                openContentView(item);
                              } catch (err) {
                                onNotice("error", err instanceof Error ? err.message : "Could not open page");
                              }
                            }}
                          >
                            View
                          </button>
                          <button
                            type="button"
                            className="epb-btn epb-btn--ghost epb-btn--sm"
                            onClick={() => onOpen(item.id)}
                          >
                            Edit
                          </button>
                          <button
                            type="button"
                            className="epb-btn epb-btn--ghost epb-btn--sm epb-btn--danger"
                            disabled={busy}
                            onClick={() => handleRowTrash(item)}
                          >
                            {busy ? "Deleting…" : "Trash"}
                          </button>
                        </>
                      )}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      <ListPagination
        page={data.page}
        totalPages={data.total_pages}
        total={data.total}
        perPage={data.per_page}
        onPageChange={setPage}
      />

      {quickEditItem && (
        <ContentQuickEditModal
          item={quickEditItem}
          postType={postType}
          onClose={() => setQuickEditItem(null)}
          onSaved={load}
          onNotice={onNotice}
        />
      )}

      {showCreateModal && (
        <CreateContentModal
          postType={postType}
          onClose={() => setShowCreateModal(false)}
          onCreate={onCreate}
        />
      )}
    </div>
  );
}
