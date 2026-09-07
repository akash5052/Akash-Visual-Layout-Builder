import { useCallback, useEffect, useState } from "react";
import { bulkPopups, deletePopup, fetchPopups, restorePopup } from "../api/wordpress";
import type { StudioPopup, ListStatusFilter, PaginatedResult } from "../types";
import { PopupQuickEditModal } from "./QuickEditModal";
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

interface PopupsListViewProps {
  onCreate: () => void;
  onOpen: (id: string) => void;
  onNotice: (type: "success" | "error", message: string) => void;
}

export function PopupsListView({ onCreate, onOpen, onNotice }: PopupsListViewProps) {
  const [statusFilter, setStatusFilter] = useState<ListStatusFilter>(getDefaultListStatus);
  const [page, setPage] = useState(1);
  const [data, setData] = useState<PaginatedResult<StudioPopup>>({
    items: [],
    total: 0,
    total_pages: 1,
    page: 1,
    per_page: PER_PAGE,
  });
  const [loading, setLoading] = useState(true);
  const [acting, setActing] = useState(false);
  const [rowBusyId, setRowBusyId] = useState<string | null>(null);
  const [quickEditPopup, setQuickEditPopup] = useState<StudioPopup | null>(null);
  const { selected, selectedIds, toggle, toggleAll, clear } = useListSelection<string>();

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const result = await fetchPopups({
        status: statusFilter,
        page,
        per_page: PER_PAGE,
      });
      setData(result);
      clear();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Failed to load popups");
    } finally {
      setLoading(false);
    }
  }, [clear, onNotice, page, statusFilter]);

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
        ? `Permanently delete ${selectedIds.length} selected popup(s)? This cannot be undone.`
        : action === "trash"
          ? `Move ${selectedIds.length} selected popup(s) to trash?`
          : `Restore ${selectedIds.length} selected popup(s) from trash?`;

    if (!confirm(confirmMessage)) return;

    setActing(true);
    try {
      const result = await bulkPopups(action, selectedIds, force);
      onNotice(result.processed > 0 ? "success" : "error", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Bulk action failed");
    } finally {
      setActing(false);
    }
  };

  const handleRowTrash = async (popup: StudioPopup) => {
    if (!confirm(`Move "${popup.name}" to trash?`)) return;

    setRowBusyId(popup.id);
    try {
      const result = await deletePopup(popup.id, false);
      onNotice("success", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Delete failed");
    } finally {
      setRowBusyId(null);
    }
  };

  const handleRowRestore = async (popup: StudioPopup) => {
    setRowBusyId(popup.id);
    try {
      const result = await restorePopup(popup.id);
      onNotice("success", result.message);
      await load();
    } catch (err) {
      onNotice("error", err instanceof Error ? err.message : "Restore failed");
    } finally {
      setRowBusyId(null);
    }
  };

  const handleRowDeletePermanent = async (popup: StudioPopup) => {
    if (!confirm(`Permanently delete "${popup.name}"? This cannot be undone.`)) return;

    setRowBusyId(popup.id);
    try {
      const result = await deletePopup(popup.id, true);
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
    <div className="av-web-studio-screen av-web-studio-screen--list">
      <header className="av-web-studio-screen__header">
        <div>
          <h1 className="av-web-studio-screen__title">Popups</h1>
          <p className="av-web-studio-screen__subtitle">Manage popup campaigns, triggers, and display rules.</p>
        </div>
        <button type="button" className="av-web-studio-btn av-web-studio-btn--primary" onClick={onCreate}>
          + New popup
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
        <div className="av-web-studio-screen__empty">
          <p>Loading popups…</p>
        </div>
      ) : data.items.length === 0 ? (
        <div className="av-web-studio-screen__empty">
          <p>{statusFilter === "trash" ? "No popups in trash." : "No popups yet."}</p>
          {statusFilter !== "trash" && (
            <button type="button" className="av-web-studio-btn av-web-studio-btn--ghost" onClick={onCreate}>
              Create your first popup
            </button>
          )}
        </div>
      ) : (
        <div className="av-web-studio-content-table-wrap">
          <table className="av-web-studio-content-table">
            <thead>
              <tr>
                <th className="av-web-studio-content-table__check">
                  <input
                    type="checkbox"
                    aria-label="Select all"
                    checked={allSelected}
                    onChange={(e) => toggleAll(data.items.map((item) => item.id), e.target.checked)}
                  />
                </th>
                <th>Name</th>
                <th>Status</th>
                <th>Modified</th>
                <th />
              </tr>
            </thead>
            <tbody>
              {data.items.map((popup) => {
                const isTrashed = popup.status === "trash";
                const busy = rowBusyId === popup.id;

                return (
                  <tr key={popup.id} className={selected.has(popup.id) ? "av-web-studio-content-table__row--selected" : ""}>
                    <td className="av-web-studio-content-table__check">
                      <input
                        type="checkbox"
                        aria-label={`Select ${popup.name}`}
                        checked={selected.has(popup.id)}
                        onChange={() => toggle(popup.id)}
                      />
                    </td>
                    <td>
                      {isTrashed ? (
                        <span className="av-web-studio-content-table__title av-web-studio-content-table__title--static">{popup.name}</span>
                      ) : (
                        <button type="button" className="av-web-studio-content-table__title" onClick={() => onOpen(popup.id)}>
                          {popup.name}
                        </button>
                      )}
                    </td>
                    <td>
                      <span className={`av-web-studio-status av-web-studio-status--${statusClass(popup.status)}`}>
                        {displayStatus(popup.status)}
                      </span>
                    </td>
                    <td className="av-web-studio-content-table__date">{formatListDate(popup.modified)}</td>
                    <td className="av-web-studio-content-table__actions">
                      {isTrashed ? (
                        <>
                          <button
                            type="button"
                            className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm"
                            disabled={busy}
                            onClick={() => handleRowRestore(popup)}
                          >
                            {busy ? "Restoring…" : "Restore"}
                          </button>
                          <button
                            type="button"
                            className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm av-web-studio-btn--danger"
                            disabled={busy}
                            onClick={() => handleRowDeletePermanent(popup)}
                          >
                            Delete permanently
                          </button>
                        </>
                      ) : (
                        <>
                          <button
                            type="button"
                            className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm"
                            onClick={() => setQuickEditPopup(popup)}
                          >
                            Quick Edit
                          </button>
                          <button
                            type="button"
                            className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm"
                            onClick={() => onOpen(popup.id)}
                          >
                            Edit
                          </button>
                          <button
                            type="button"
                            className="av-web-studio-btn av-web-studio-btn--ghost av-web-studio-btn--sm av-web-studio-btn--danger"
                            disabled={busy}
                            onClick={() => handleRowTrash(popup)}
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

      {quickEditPopup && (
        <PopupQuickEditModal
          popup={quickEditPopup}
          onClose={() => setQuickEditPopup(null)}
          onSaved={load}
          onNotice={onNotice}
        />
      )}
    </div>
  );
}
