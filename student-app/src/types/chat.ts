export type ChatMessage = {
  id: string | number;
  conversation_id?: string | number;
  sender_type?: 'coach' | 'member' | string;
  content: string;
  read_at?: string | null;
  created_at?: string | null;
};

export type ChatConversation = {
  id: string | number;
  member_id?: string | number;
  last_message?: string | null;
  last_message_at?: string | null;
  messages?: ChatMessage[];
};

export type MessageSentPayload = {
  message: ChatMessage;
};

