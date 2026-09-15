import { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";

import type { AiChatTurn } from "../types";
import type { ResolvedTheme } from "../hooks/useEditorTheme";

interface AiPanelProps {
  enabled: boolean;
  aiReady: boolean;
  settingsUrl?: string;
  defaultOpen?: boolean;
  theme: ResolvedTheme;
  history: AiChatTurn[];
  activeTurnId: string | null;
  pendingPrompt: string | null;
  onGenerate: (prompt: string) => Promise<void>;
  onRestore: (turnId: string) => Promise<void>;
  onRestoreBefore: (turnId: string) => Promise<void>;
  onClearHistory: () => void;
  isLoading: boolean;
}

function SparkleIcon() {
  return (
    <svg className="akash-visual-layout-builder-ai-toggle__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path
        d="M12 2l1.4 4.2L17.6 8 13.4 9.4 12 13.6 10.6 9.4 6.4 8l4.2-1.8L12 2z"
        fill="currentColor"
        opacity="0.9"
      />
      <path
        d="M19 11l.8 2.4L22.2 14l-2.4.6L19 17l-.8-2.4-2.4-.6 2.4-.6L19 11z"
        fill="currentColor"
        opacity="0.7"
      />
      <path
        d="M5 14l.6 1.8L7.4 16l-1.8.4L5 18.2l-.6-1.8-1.8-.4 1.8-.4L5 14z"
        fill="currentColor"
        opacity="0.7"
      />
    </svg>
  );
}

function formatTime(ts: number): string {
  return new Date(ts).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

export function AiPanel({
  enabled,
  aiReady,
  settingsUrl,
  defaultOpen = false,
  theme,
  history,
  activeTurnId,
  pendingPrompt,
  onGenerate,
  onRestore,
  onRestoreBefore,
  onClearHistory,
  isLoading,
}: AiPanelProps) {
  const [prompt, setPrompt] = useState("");
  const [isOpen, setIsOpen] = useState(defaultOpen);
  const historyEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (isOpen) {
      historyEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }
  }, [isOpen, history.length, pendingPrompt, isLoading]);

  if (!enabled) {
    return null;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!aiReady || !prompt.trim() || isLoading) return;
    await onGenerate(prompt.trim());
    setPrompt("");
  };

  const suggestions = [
    { label: "Coffee shop page", prompt: "Build a complete landing page for my coffee shop with images and animations" },
    { label: "Fitness studio", prompt: "Create a fitness studio website with pricing, gallery, and testimonials" },
    { label: "SaaS landing", prompt: "Build a SaaS product landing page with features and pricing" },
    { label: "Image gallery", prompt: "Add a modern gallery section with hover animations" },
    { label: "Scroll motion", prompt: "Add scroll animations to all sections" },
  ];

  return createPortal(
    <div className={`akash-visual-layout-builder-ai-panel ${isOpen ? "akash-visual-layout-builder-ai-panel--open" : ""}`} data-theme={theme}>
      {isOpen && (
        <div className="akash-visual-layout-builder-ai-content" role="dialog" aria-label="AI assistant">
          <div className="akash-visual-layout-builder-ai-content__accent" aria-hidden="true" />

          <div className="akash-visual-layout-builder-ai-header">
            <div className="akash-visual-layout-builder-ai-header__title">
              <span className="akash-visual-layout-builder-ai-header__icon" aria-hidden="true">
                <SparkleIcon />
              </span>
              <div>
                <h4>AI Assistant</h4>
                <p className="akash-visual-layout-builder-ai-header__status">
                  <span className={`akash-visual-layout-builder-ai-status ${aiReady ? "akash-visual-layout-builder-ai-status--ok" : "akash-visual-layout-builder-ai-status--warn"}`}>
                    <span className="akash-visual-layout-builder-ai-status__dot" />
                    {aiReady ? "Ready" : "Setup required"}
                  </span>
                </p>
              </div>
            </div>
            <div className="akash-visual-layout-builder-ai-header__actions">
              {history.length > 0 && (
                <button
                  type="button"
                  className="akash-visual-layout-builder-ai-history-clear"
                  onClick={onClearHistory}
                  disabled={!aiReady || isLoading}
                  title="Clear chat history"
                >
                  Clear
                </button>
              )}
              <button
                type="button"
                className="akash-visual-layout-builder-ai-close"
                onClick={() => setIsOpen(false)}
                aria-label="Close AI assistant"
              >
                ×
              </button>
            </div>
          </div>

          <div className="akash-visual-layout-builder-ai-body">
            {!aiReady && (
              <div className="akash-visual-layout-builder-ai-history__empty akash-visual-layout-builder-ai-history__empty--setup">
                <strong>Enable AI in Settings</strong>
                <p>
                  Turn on the AI assistant in{" "}
                  {settingsUrl ? (
                    <a href={settingsUrl}>Akash Visual Layout Builder → Settings</a>
                  ) : (
                    "Akash Visual Layout Builder → Settings"
                  )}
                  . Cloud models use{" "}
                  <strong>Settings → Connectors</strong> in WordPress 7.0+.
                </p>
              </div>
            )}

            <div className="akash-visual-layout-builder-ai-history" aria-label="AI chat history">
              {history.length === 0 && !pendingPrompt && aiReady && (
                <div className="akash-visual-layout-builder-ai-history__empty">
                  <strong>Ask anything about this page</strong>
                  <p>Generate sections, swap images, or add animations. You can restore any earlier version.</p>
                </div>
              )}

              {history.map((turn, index) => {
                const isActive = turn.id === activeTurnId;
                return (
                  <div key={turn.id} className={`akash-visual-layout-builder-ai-turn ${isActive ? "akash-visual-layout-builder-ai-turn--active" : ""}`}>
                    <div className="akash-visual-layout-builder-ai-msg akash-visual-layout-builder-ai-msg--user">
                      <span className="akash-visual-layout-builder-ai-msg__label">You</span>
                      <p>{turn.prompt}</p>
                      <time className="akash-visual-layout-builder-ai-msg__time">{formatTime(turn.createdAt)}</time>
                    </div>

                    <div className="akash-visual-layout-builder-ai-msg akash-visual-layout-builder-ai-msg--assistant">
                      <span className="akash-visual-layout-builder-ai-msg__label">Assistant</span>
                      <p>{turn.explanation || "Page updated."}</p>
                      <div className="akash-visual-layout-builder-ai-turn__actions">
                        <button
                          type="button"
                          className={`akash-visual-layout-builder-ai-restore ${isActive ? "akash-visual-layout-builder-ai-restore--active" : ""}`}
                          onClick={() => onRestore(turn.id)}
                          disabled={isLoading || isActive}
                          title="Restore page to this version"
                        >
                          {isActive ? "Current version" : "Restore version"}
                        </button>
                        {index > 0 && (
                          <button
                            type="button"
                            className="akash-visual-layout-builder-ai-restore akash-visual-layout-builder-ai-restore--ghost"
                            onClick={() => onRestoreBefore(turn.id)}
                            disabled={!aiReady || isLoading}
                            title="Restore to state before this change"
                          >
                            Before this
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                );
              })}

              {pendingPrompt && (
                <div className="akash-visual-layout-builder-ai-turn akash-visual-layout-builder-ai-turn--pending">
                  <div className="akash-visual-layout-builder-ai-msg akash-visual-layout-builder-ai-msg--user">
                    <span className="akash-visual-layout-builder-ai-msg__label">You</span>
                    <p>{pendingPrompt}</p>
                  </div>
                  <div className="akash-visual-layout-builder-ai-msg akash-visual-layout-builder-ai-msg--assistant akash-visual-layout-builder-ai-msg--loading">
                    <span className="akash-visual-layout-builder-ai-generate__spinner" aria-hidden="true" />
                    <span>Generating…</span>
                  </div>
                </div>
              )}

              <div ref={historyEndRef} />
            </div>

            {aiReady && history.length === 0 && !isLoading && (
              <div className="akash-visual-layout-builder-ai-suggestions">
                <span className="akash-visual-layout-builder-ai-suggestions__label">Quick ideas</span>
                <div className="akash-visual-layout-builder-ai-suggestions__list">
                  {suggestions.map((s) => (
                    <button
                      key={s.label}
                      type="button"
                      className="akash-visual-layout-builder-ai-chip"
                      onClick={() => setPrompt(s.prompt)}
                      disabled={!aiReady || isLoading}
                      title={s.prompt}
                    >
                      {s.label}
                    </button>
                  ))}
                </div>
              </div>
            )}

            <form className="akash-visual-layout-builder-ai-composer" onSubmit={handleSubmit}>
              <div className="akash-visual-layout-builder-ai-composer__shell">
                <textarea
                  value={prompt}
                  onChange={(e) => setPrompt(e.target.value)}
                  placeholder="Describe what to build or change…"
                  rows={3}
                  disabled={!aiReady || isLoading}
                  onKeyDown={(e) => {
                    if (e.key === "Enter" && !e.shiftKey) {
                      e.preventDefault();
                      if (aiReady && prompt.trim() && !isLoading) {
                        void onGenerate(prompt.trim()).then(() => setPrompt(""));
                      }
                    }
                  }}
                />
                <div className="akash-visual-layout-builder-ai-composer__footer">
                  <span className="akash-visual-layout-builder-ai-composer__hint">Enter to send · Shift+Enter for new line</span>
                  <button type="submit" className="akash-visual-layout-builder-ai-generate" disabled={!aiReady || isLoading || !prompt.trim()}>
                    {isLoading ? (
                      <>
                        <span className="akash-visual-layout-builder-ai-generate__spinner" aria-hidden="true" />
                        Generating
                      </>
                    ) : (
                      <>
                        <SparkleIcon />
                        Generate
                      </>
                    )}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      )}

      <button
        type="button"
        className="akash-visual-layout-builder-ai-toggle"
        onClick={() => setIsOpen(!isOpen)}
        aria-expanded={isOpen}
        aria-label={isOpen ? "Close AI assistant" : "Open AI assistant"}
      >
        <span className="akash-visual-layout-builder-ai-toggle__glow" aria-hidden="true" />
        <SparkleIcon />
        <span className="akash-visual-layout-builder-ai-toggle__label">
          {isOpen ? "Close" : history.length > 0 ? `AI (${history.length})` : "AI Assistant"}
        </span>
      </button>
    </div>,
    document.body
  );
}
