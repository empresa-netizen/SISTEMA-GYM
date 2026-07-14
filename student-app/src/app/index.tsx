import { Redirect } from 'expo-router';
import { ActivityIndicator, StyleSheet, View } from 'react-native';

import { useTheme } from '@/hooks/use-theme';
import { useAuthStore } from '@/store/useAuthStore';

export default function HomeScreen() {
  const theme = useTheme();
  const isHydrating = useAuthStore((state) => state.isHydrating);
  const session = useAuthStore((state) => state.session);

  if (isHydrating) {
    return (
      <View style={[styles.container, { backgroundColor: theme.background }]}>
        <ActivityIndicator color={theme.tint} />
      </View>
    );
  }

  return session ? <Redirect href="/(tabs)" /> : <Redirect href="/(auth)/login" />;
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
