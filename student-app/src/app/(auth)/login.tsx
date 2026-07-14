import { useState } from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { apiBaseUrl } from '@/config/env';
import { useTheme } from '@/hooks/use-theme';
import { useAuthStore } from '@/store/useAuthStore';

export default function LoginScreen() {
  const theme = useTheme();
  const login = useAuthStore((state) => state.login);
  const isLoggingIn = useAuthStore((state) => state.isLoggingIn);
  const error = useAuthStore((state) => state.error);
  const [email, setEmail] = useState('anabeatriz@gmail.com');
  const [password, setPassword] = useState('password');

  const canSubmit = email.trim().length > 0 && password.length > 0 && !isLoggingIn;

  async function handleSubmit(): Promise<void> {
    if (!canSubmit) {
      return;
    }

    try {
      await login({
        email: email.trim(),
        password,
      });
    } catch {
      return;
    }
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.background }]}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={styles.keyboardView}>
        <View style={styles.container}>
          <View style={styles.header}>
            <View style={[styles.logoMark, { backgroundColor: theme.tint }]}>
              <Text style={styles.logoText}>MG</Text>
            </View>
            <Text style={[styles.title, { color: theme.text }]}>Entrar no app</Text>
            <Text style={[styles.subtitle, { color: theme.textSecondary }]}>
              Acesse seus treinos, dieta e conversa com o coach.
            </Text>
          </View>

          <View style={styles.form}>
            <View style={styles.fieldGroup}>
              <Text style={[styles.label, { color: theme.text }]}>Email</Text>
              <TextInput
                autoCapitalize="none"
                autoComplete="email"
                autoCorrect={false}
                inputMode="email"
                keyboardType="email-address"
                onChangeText={setEmail}
                placeholder="seu@email.com"
                placeholderTextColor={theme.textMuted}
                style={[
                  styles.input,
                  {
                    backgroundColor: theme.surface,
                    borderColor: theme.border,
                    color: theme.text,
                  },
                ]}
                value={email}
              />
            </View>

            <View style={styles.fieldGroup}>
              <Text style={[styles.label, { color: theme.text }]}>Senha</Text>
              <TextInput
                autoCapitalize="none"
                onChangeText={setPassword}
                placeholder="Sua senha"
                placeholderTextColor={theme.textMuted}
                secureTextEntry
                style={[
                  styles.input,
                  {
                    backgroundColor: theme.surface,
                    borderColor: theme.border,
                    color: theme.text,
                  },
                ]}
                value={password}
              />
            </View>

            {error ? <Text style={styles.error}>{error}</Text> : null}

            <Pressable
              accessibilityRole="button"
              disabled={!canSubmit}
              onPress={handleSubmit}
              style={({ pressed }) => [
                styles.button,
                { backgroundColor: canSubmit ? theme.tint : theme.disabled },
                pressed && styles.buttonPressed,
              ]}>
              {isLoggingIn ? (
                <ActivityIndicator color="#ffffff" />
              ) : (
                <Text style={styles.buttonText}>Entrar</Text>
              )}
            </Pressable>
          </View>

          <Text style={[styles.endpoint, { color: theme.textMuted }]}>{apiBaseUrl}</Text>
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
  container: {
    flex: 1,
    justifyContent: 'center',
    paddingHorizontal: 24,
    gap: 32,
  },
  header: {
    gap: 12,
  },
  logoMark: {
    width: 56,
    height: 56,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '800',
  },
  title: {
    fontSize: 32,
    lineHeight: 38,
    fontWeight: '800',
  },
  subtitle: {
    fontSize: 16,
    lineHeight: 24,
  },
  form: {
    gap: 18,
  },
  fieldGroup: {
    gap: 8,
  },
  label: {
    fontSize: 14,
    fontWeight: '700',
  },
  input: {
    minHeight: 54,
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 16,
    fontSize: 16,
  },
  error: {
    color: '#dc2626',
    fontSize: 14,
    lineHeight: 20,
  },
  button: {
    minHeight: 54,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  buttonPressed: {
    opacity: 0.85,
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '800',
  },
  endpoint: {
    textAlign: 'center',
    fontSize: 12,
    lineHeight: 18,
  },
});
