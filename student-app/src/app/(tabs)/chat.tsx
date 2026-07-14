import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { useTheme } from '@/hooks/use-theme';
import { useChatStore } from '@/store/useChatStore';
import { ChatMessage } from '@/types/chat';

export default function ChatScreen() {
  const theme = useTheme();
  const messages = useChatStore((state) => state.messages);
  const fetchConversation = useChatStore((state) => state.fetchConversation);
  const sendMessage = useChatStore((state) => state.sendMessage);
  const isSending = useChatStore((state) => state.isSending);
  const isLoading = useChatStore((state) => state.isLoading);
  const error = useChatStore((state) => state.error);
  const [content, setContent] = useState('');

  useEffect(() => {
    fetchConversation();
  }, [fetchConversation]);

  async function handleSend(): Promise<void> {
    const message = content.trim();

    if (!message) {
      return;
    }

    setContent('');
    await sendMessage(message);

    if (useChatStore.getState().error) {
      setContent(message);
    }
  }

  function renderMessage({ item }: { item: ChatMessage }) {
    const isMine = item.sender_type === 'member';

    return (
      <View
        style={[
          styles.messageBubble,
          {
            alignSelf: isMine ? 'flex-end' : 'flex-start',
            backgroundColor: isMine ? theme.tint : theme.surface,
            borderColor: isMine ? theme.tint : theme.border,
          },
        ]}>
        <Text style={[styles.messageText, { color: isMine ? '#ffffff' : theme.text }]}>
          {item.content}
        </Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.background }]}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.keyboardView}>
        <View style={styles.header}>
          <Text style={[styles.title, { color: theme.text }]}>Chat</Text>
          <Text style={[styles.subtitle, { color: theme.textSecondary }]}>
            Mensagens em tempo real com seu coach.
          </Text>
        </View>

        <FlatList
          contentContainerStyle={styles.messagesContent}
          data={messages}
          keyExtractor={(item, index) => String(item.id ?? index)}
          ListEmptyComponent={
            <View style={styles.emptyState}>
              {isLoading ? <ActivityIndicator color={theme.tint} /> : null}
              <Text style={[styles.emptyText, { color: theme.textSecondary }]}>
                {isLoading ? 'Carregando conversa...' : error ?? 'Nenhuma mensagem ainda.'}
              </Text>
            </View>
          }
          renderItem={renderMessage}
        />

        <View style={[styles.composer, { borderColor: theme.border }]}>
          <TextInput
            multiline
            onChangeText={setContent}
            placeholder="Digite sua mensagem"
            placeholderTextColor={theme.textMuted}
            style={[
              styles.composerInput,
              {
                backgroundColor: theme.surface,
                borderColor: theme.border,
                color: theme.text,
              },
            ]}
            value={content}
          />
          <Pressable
            accessibilityRole="button"
            disabled={isSending}
            onPress={handleSend}
            style={({ pressed }) => [
              styles.sendButton,
              { backgroundColor: isSending ? theme.disabled : theme.tint },
              pressed && styles.pressed,
            ]}>
            <Text style={styles.sendButtonText}>Enviar</Text>
          </Pressable>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  keyboardView: {
    flex: 1,
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 16,
    paddingBottom: 12,
    gap: 4,
  },
  title: {
    fontSize: 28,
    lineHeight: 34,
    fontWeight: '800',
  },
  subtitle: {
    fontSize: 14,
    lineHeight: 20,
  },
  messagesContent: {
    padding: 20,
    gap: 10,
  },
  messageBubble: {
    maxWidth: '82%',
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  messageText: {
    fontSize: 15,
    lineHeight: 21,
  },
  emptyText: {
    textAlign: 'center',
    fontSize: 14,
  },
  emptyState: {
    paddingVertical: 40,
    gap: 10,
    alignItems: 'center',
  },
  composer: {
    borderTopWidth: 1,
    flexDirection: 'row',
    alignItems: 'flex-end',
    padding: 12,
    gap: 10,
  },
  composerInput: {
    flex: 1,
    minHeight: 44,
    maxHeight: 120,
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 15,
  },
  sendButton: {
    minHeight: 44,
    borderRadius: 8,
    justifyContent: 'center',
    paddingHorizontal: 16,
  },
  sendButtonText: {
    color: '#ffffff',
    fontSize: 14,
    fontWeight: '800',
  },
  pressed: {
    opacity: 0.82,
  },
});
