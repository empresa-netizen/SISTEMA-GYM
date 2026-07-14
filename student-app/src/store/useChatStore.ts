import { create } from 'zustand';

import { getEchoClient } from '@/services/realtime';
import { studentApi } from '@/services/api';
import { ChatConversation, ChatMessage, MessageSentPayload } from '@/types/chat';

type ChatState = {
  conversationId: string | number | null;
  subscribedConversationId: string | number | null;
  messages: ChatMessage[];
  isLoading: boolean;
  isSending: boolean;
  error: string | null;
  fetchConversation: () => Promise<void>;
  sendMessage: (content: string) => Promise<void>;
  subscribeToConversation: () => Promise<void>;
  markMessagesRead: () => Promise<void>;
  appendMessage: (message: ChatMessage) => void;
  reset: () => void;
};

export const useChatStore = create<ChatState>((set, get) => ({
  conversationId: null,
  subscribedConversationId: null,
  messages: [],
  isLoading: false,
  isSending: false,
  error: null,

  fetchConversation: async () => {
    set({ isLoading: true, error: null });

    try {
      const response = await studentApi.get<ChatConversation | []>('/messages/conversation');

      if (Array.isArray(response.data)) {
        set({ conversationId: null, messages: [], isLoading: false });
        return;
      }

      set({
        conversationId: response.data.id,
        messages: response.data.messages ?? [],
        isLoading: false,
      });

      await get().subscribeToConversation();
      await get().markMessagesRead();
    } catch {
      set({ isLoading: false, error: 'Nao foi possivel carregar a conversa.' });
    }
  },

  sendMessage: async (content) => {
    const trimmedContent = content.trim();

    if (!trimmedContent) {
      return;
    }

    set({ isSending: true, error: null });

    try {
      const response = await studentApi.post<{
        data: ChatMessage;
        conversation_id: string | number;
      }>('/messages/conversation', {
        content: trimmedContent,
      });

      set((state) => ({
        conversationId: response.data.conversation_id,
        messages: [...state.messages, response.data.data],
        isSending: false,
        error: null,
      }));

      await get().subscribeToConversation();
    } catch {
      set({ isSending: false, error: 'Nao foi possivel enviar a mensagem.' });
    }
  },

  subscribeToConversation: async () => {
    const conversationId = get().conversationId;
    const subscribedConversationId = get().subscribedConversationId;

    if (!conversationId) {
      return;
    }

    if (String(conversationId) === String(subscribedConversationId)) {
      return;
    }

    const echo = await getEchoClient();

    echo.private(`chat.${conversationId}`).listen('.MessageSent', (payload: MessageSentPayload) => {
      get().appendMessage(payload.message);
    });

    set({ subscribedConversationId: conversationId });
  },

  markMessagesRead: async () => {
    const conversationId = get().conversationId;

    if (!conversationId) {
      return;
    }

    try {
      await studentApi.post('/messages/conversation/read');
    } catch {
      return;
    }
  },

  appendMessage: (message) => {
    set((state) => {
      const alreadyExists = state.messages.some((item) => String(item.id) === String(message.id));

      if (alreadyExists) {
        return state;
      }

      return {
        messages: [...state.messages, message],
      };
    });

    if (message.sender_type === 'coach') {
      get().markMessagesRead();
    }
  },

  reset: () => {
    set({
      conversationId: null,
      subscribedConversationId: null,
      messages: [],
      isLoading: false,
      isSending: false,
      error: null,
    });
  },
}));
