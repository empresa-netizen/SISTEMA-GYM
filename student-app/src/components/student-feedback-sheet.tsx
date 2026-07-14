import { useState } from 'react';
import {
  KeyboardAvoidingView,
  Modal,
  Platform,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';

import { useTheme } from '@/hooks/use-theme';

type StudentFeedbackSheetProps = {
  visible: boolean;
  title: string;
  subtitle: string;
  isSending: boolean;
  error?: string | null;
  onClose: () => void;
  onSubmit: (payload: { message: string; rating: number | null }) => Promise<void>;
};

export function StudentFeedbackSheet({
  visible,
  title,
  subtitle,
  isSending,
  error,
  onClose,
  onSubmit,
}: StudentFeedbackSheetProps) {
  const colors = useTheme();
  const [message, setMessage] = useState('');
  const [rating, setRating] = useState<number | null>(null);

  async function handleSubmit(): Promise<void> {
    const trimmedMessage = message.trim();

    if (!trimmedMessage || isSending) {
      return;
    }

    try {
      await onSubmit({ message: trimmedMessage, rating });
      setMessage('');
      setRating(null);
    } catch {
      return;
    }
  }

  function handleClose(): void {
    if (isSending) {
      return;
    }

    setMessage('');
    setRating(null);
    onClose();
  }

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={handleClose}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.modalOverlay}>
        <TouchableOpacity style={styles.modalBackdrop} activeOpacity={1} onPress={handleClose} />
        <View style={[styles.sheet, { backgroundColor: colors.surface }]}>
          <View style={[styles.sheetHandle, { backgroundColor: colors.textSecondary }]} />
          <Text style={[styles.sheetTitle, { color: colors.text }]}>{title}</Text>
          <Text style={[styles.sheetSubtitle, { color: colors.textSecondary }]}>{subtitle}</Text>

          <View style={styles.ratingRow}>
            {[1, 2, 3, 4, 5].map((ratingOption) => (
              <TouchableOpacity
                key={ratingOption}
                accessibilityRole="button"
                accessibilityLabel={`Dar nota ${ratingOption}`}
                style={styles.ratingButton}
                onPress={() => setRating(ratingOption)}>
                <Text
                  style={[
                    styles.ratingStar,
                    { color: rating && ratingOption <= rating ? '#F59E0B' : colors.textMuted },
                  ]}>
                  ★
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          <TextInput
            value={message}
            onChangeText={setMessage}
            placeholder="Conte ao coach o que aconteceu..."
            placeholderTextColor={colors.textMuted}
            multiline
            style={[
              styles.commentInput,
              { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
            ]}
          />

          {error ? <Text style={[styles.errorText, { color: '#EF4444' }]}>{error}</Text> : null}

          <TouchableOpacity
            disabled={isSending || !message.trim()}
            style={[
              styles.submitButton,
              { backgroundColor: message.trim() ? colors.tint : colors.disabled },
            ]}
            onPress={handleSubmit}>
            <Text style={styles.submitButtonText}>{isSending ? 'Enviando...' : 'Enviar feedback'}</Text>
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  modalOverlay: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  modalBackdrop: {
    ...StyleSheet.absoluteFill,
    backgroundColor: 'rgba(0,0,0,0.45)',
  },
  sheet: {
    borderTopLeftRadius: 26,
    borderTopRightRadius: 26,
    padding: 22,
    gap: 14,
  },
  sheetHandle: {
    width: 46,
    height: 4,
    borderRadius: 999,
    alignSelf: 'center',
    opacity: 0.5,
  },
  sheetTitle: {
    fontSize: 22,
    fontWeight: '900',
  },
  sheetSubtitle: {
    fontSize: 14,
    lineHeight: 21,
  },
  ratingRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 8,
  },
  ratingButton: {
    padding: 4,
  },
  ratingStar: {
    fontSize: 30,
    fontWeight: '900',
  },
  commentInput: {
    minHeight: 112,
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    textAlignVertical: 'top',
  },
  errorText: {
    fontSize: 13,
    fontWeight: '800',
  },
  submitButton: {
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
  },
  submitButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '900',
  },
});
