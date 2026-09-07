import type { AiChatTurn } from "../types";

const STORAGE_PREFIX = "av_web_studio_ai_chat_";
const MAX_TURNS = 40;

export function aiHistoryStorageKey(pageId: number): string {
  return `${STORAGE_PREFIX}${pageId}`;
}

export function loadAiChatHistory(pageId: number): AiChatTurn[] {
  try {
    const raw = sessionStorage.getItem(aiHistoryStorageKey(pageId));
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

export function saveAiChatHistory(pageId: number, turns: AiChatTurn[]): void {
  try {
    const trimmed = turns.length > MAX_TURNS ? turns.slice(-MAX_TURNS) : turns;
    sessionStorage.setItem(aiHistoryStorageKey(pageId), JSON.stringify(trimmed));
  } catch {
    // sessionStorage full or unavailable
  }
}

export function clearAiChatHistory(pageId: number): void {
  try {
    sessionStorage.removeItem(aiHistoryStorageKey(pageId));
  } catch {
    // ignore
  }
}

export function createTurnId(): string {
  return `ai_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
}
