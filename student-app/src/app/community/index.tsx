import { router } from 'expo-router';
import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  TextInput,
  View,
  type ListRenderItemInfo,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { BottomTabInset } from '@/constants/theme';
import { useTheme } from '@/hooks/use-theme';
import { fetchStudentCommunity, sendStudentCommunityPost } from '@/services/community';
import {
  StudentCommunityPost,
  StudentCommunityResponse,
} from '@/types/community';

function formatDateTime(value: string | null): string {
  if (!value) {
    return 'Agora';
  }

  return new Date(value).toLocaleString('pt-BR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function initials(name: string): string {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0))
    .join('')
    .toUpperCase() || 'C';
}

export default function StudentCommunityScreen() {
  const colors = useTheme();
  const [community, setCommunity] = useState<StudentCommunityResponse>({
    groups: [],
    recent_posts: [],
  });
  const [selectedGroupId, setSelectedGroupId] = useState<number | null>(null);
  const [content, setContent] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [isPosting, setIsPosting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadCommunity = useCallback(async (showSpinner = false) => {
    if (showSpinner) {
      setIsLoading(true);
    }

    setError(null);

    try {
      const response = await fetchStudentCommunity();

      setCommunity(response);
      setSelectedGroupId((currentGroupId) => {
        if (currentGroupId && response.groups.some((group) => group.id === currentGroupId)) {
          return currentGroupId;
        }

        return response.groups[0]?.id ?? null;
      });
    } catch (loadError) {
      setError(loadError instanceof Error ? loadError.message : 'Nao foi possivel carregar a comunidade.');
    } finally {
      setIsLoading(false);
      setIsRefreshing(false);
    }
  }, []);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      loadCommunity(true);
    }, 0);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [loadCommunity]);

  const selectedGroup = useMemo(
    () => community.groups.find((group) => group.id === selectedGroupId) ?? null,
    [community.groups, selectedGroupId],
  );

  const posts = selectedGroup?.posts.length ? selectedGroup.posts : community.recent_posts;

  async function handleRefresh(): Promise<void> {
    setIsRefreshing(true);
    await loadCommunity(false);
  }

  async function handleSendPost(): Promise<void> {
    if (!selectedGroup) {
      setError('Escolha um grupo para publicar.');
      return;
    }

    const trimmedContent = content.trim();

    if (!trimmedContent) {
      setError('Escreva uma mensagem antes de publicar.');
      return;
    }

    setIsPosting(true);
    setError(null);

    try {
      const post = await sendStudentCommunityPost(selectedGroup.id, trimmedContent);

      setCommunity((currentCommunity) => ({
        recent_posts: [
          post,
          ...currentCommunity.recent_posts.filter((item) => item.id !== post.id),
        ],
        groups: currentCommunity.groups.map((group) =>
          group.id === selectedGroup.id
            ? {
                ...group,
                posts_count: group.posts_count + 1,
                posts: [post, ...group.posts.filter((item) => item.id !== post.id)],
              }
            : group,
        ),
      }));
      setContent('');
    } catch (postError) {
      setError(postError instanceof Error ? postError.message : 'Nao foi possivel publicar no grupo.');
    } finally {
      setIsPosting(false);
    }
  }

  function renderHeader() {
    return (
      <View style={styles.headerWrapper}>
        <View style={styles.topRow}>
          <Pressable
            accessibilityRole="button"
            onPress={() => (router.canGoBack() ? router.back() : router.replace('/(tabs)'))}
            style={({ pressed }) => [
              styles.backButton,
              { backgroundColor: colors.backgroundElement },
              pressed && styles.pressed,
            ]}>
            <Text style={[styles.backButtonText, { color: colors.text }]}>‹</Text>
          </Pressable>
          <View style={styles.titleBox}>
            <Text style={[styles.eyebrow, { color: colors.tint }]}>COMUNIDADE</Text>
            <Text style={[styles.title, { color: colors.text }]}>Grupos do coach</Text>
          </View>
        </View>

        <Text style={[styles.subtitle, { color: colors.textSecondary }]}>
          Publique check-ins, dúvidas e vitórias nos grupos criados pelo seu coach.
        </Text>

        <View style={styles.groupChips}>
          {community.groups.map((group) => {
            const active = group.id === selectedGroupId;

            return (
              <Pressable
                key={group.id}
                accessibilityRole="button"
                onPress={() => setSelectedGroupId(group.id)}
                style={({ pressed }) => [
                  styles.groupChip,
                  {
                    backgroundColor: active ? colors.tint : colors.backgroundElement,
                    borderColor: active ? colors.tint : colors.border,
                  },
                  pressed && styles.pressed,
                ]}>
                <Text style={[styles.groupChipText, { color: active ? '#FFFFFF' : colors.text }]}>
                  {group.name}
                </Text>
                <Text style={[styles.groupChipMeta, { color: active ? '#DFFCF7' : colors.textSecondary }]}>
                  {group.posts_count} posts
                </Text>
              </Pressable>
            );
          })}
        </View>

        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          <View style={[styles.composer, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.composerTitle, { color: colors.text }]}>
              Publicar em {selectedGroup?.name ?? 'um grupo'}
            </Text>
            <TextInput
              multiline
              value={content}
              onChangeText={setContent}
              placeholder="Compartilhe seu check-in, uma dúvida ou uma vitória..."
              placeholderTextColor={colors.textMuted}
              style={[
                styles.input,
                { backgroundColor: colors.backgroundElement, borderColor: colors.border, color: colors.text },
              ]}
            />
            {error ? <Text style={[styles.errorText, { color: '#EF4444' }]}>{error}</Text> : null}
            <Pressable
              accessibilityRole="button"
              disabled={!selectedGroup || isPosting}
              onPress={handleSendPost}
              style={({ pressed }) => [
                styles.primaryButton,
                { backgroundColor: selectedGroup ? colors.tint : colors.disabled },
                pressed && styles.pressed,
              ]}>
              <Text style={styles.primaryButtonText}>{isPosting ? 'Publicando...' : 'Publicar'}</Text>
            </Pressable>
          </View>
        </KeyboardAvoidingView>

        <View style={styles.sectionHead}>
          <Text style={[styles.sectionTitle, { color: colors.text }]}>
            {selectedGroup ? `Posts em ${selectedGroup.name}` : 'Posts recentes'}
          </Text>
          <Text style={[styles.sectionCount, { color: colors.textSecondary }]}>
            {posts.length} item{posts.length === 1 ? '' : 's'}
          </Text>
        </View>
      </View>
    );
  }

  function renderPost({ item }: ListRenderItemInfo<StudentCommunityPost>) {
    return (
      <View style={[styles.postCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
        <View style={styles.postHeader}>
          <View style={[styles.avatar, { backgroundColor: colors.backgroundElement }]}>
            <Text style={[styles.avatarText, { color: colors.tint }]}>{initials(item.author_name)}</Text>
          </View>
          <View style={styles.postIdentity}>
            <Text style={[styles.postAuthor, { color: colors.text }]} numberOfLines={1}>
              {item.author_name}
            </Text>
            <Text style={[styles.postMeta, { color: colors.textSecondary }]} numberOfLines={1}>
              {item.group_name ?? 'Comunidade'} · {formatDateTime(item.created_at)}
            </Text>
          </View>
        </View>
        <Text style={[styles.postContent, { color: colors.text }]}>{item.content}</Text>
        <Text style={[styles.likesText, { color: colors.textSecondary }]}>
          ♥ {item.likes_count} curtida{item.likes_count === 1 ? '' : 's'}
        </Text>
      </View>
    );
  }

  if (isLoading) {
    return (
      <SafeAreaView style={[styles.centered, { backgroundColor: colors.background }]}>
        <ActivityIndicator color={colors.tint} />
        <Text style={[styles.centerText, { color: colors.textSecondary }]}>Carregando comunidade...</Text>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <FlatList
        contentContainerStyle={styles.content}
        data={posts}
        keyExtractor={(item) => String(item.id)}
        ListEmptyComponent={
          <View style={[styles.emptyState, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={styles.emptyIcon}>🤝</Text>
            <Text style={[styles.emptyTitle, { color: colors.text }]}>Nenhum post neste grupo</Text>
            <Text style={[styles.emptyText, { color: colors.textSecondary }]}>
              Inaugure o mural compartilhando um check-in com a comunidade.
            </Text>
          </View>
        }
        ListHeaderComponent={renderHeader}
        refreshControl={
          <RefreshControl refreshing={isRefreshing} onRefresh={handleRefresh} tintColor={colors.tint} />
        }
        renderItem={renderPost}
        showsVerticalScrollIndicator={false}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
  },
  centerText: {
    fontSize: 14,
    fontWeight: '700',
  },
  content: {
    paddingHorizontal: 20,
    paddingTop: 18,
    paddingBottom: BottomTabInset + 24,
    gap: 14,
  },
  headerWrapper: {
    gap: 16,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  backButton: {
    width: 44,
    height: 44,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backButtonText: {
    fontSize: 32,
    lineHeight: 34,
    fontWeight: '800',
  },
  titleBox: {
    flex: 1,
    gap: 3,
  },
  eyebrow: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.7,
  },
  title: {
    fontSize: 28,
    lineHeight: 34,
    fontWeight: '900',
  },
  subtitle: {
    fontSize: 15,
    lineHeight: 22,
  },
  groupChips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  groupChip: {
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 13,
    paddingVertical: 10,
    gap: 3,
  },
  groupChipText: {
    fontSize: 14,
    fontWeight: '900',
  },
  groupChipMeta: {
    fontSize: 11,
    fontWeight: '800',
  },
  composer: {
    borderWidth: 1,
    borderRadius: 20,
    padding: 16,
    gap: 12,
  },
  composerTitle: {
    fontSize: 17,
    fontWeight: '900',
  },
  input: {
    minHeight: 108,
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    lineHeight: 21,
    textAlignVertical: 'top',
  },
  errorText: {
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '800',
  },
  primaryButton: {
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '900',
  },
  sectionHead: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  sectionTitle: {
    flex: 1,
    fontSize: 20,
    lineHeight: 26,
    fontWeight: '900',
  },
  sectionCount: {
    fontSize: 13,
    fontWeight: '800',
  },
  postCard: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
    gap: 12,
  },
  postHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  avatar: {
    width: 42,
    height: 42,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    fontSize: 14,
    fontWeight: '900',
  },
  postIdentity: {
    flex: 1,
  },
  postAuthor: {
    fontSize: 15,
    fontWeight: '900',
  },
  postMeta: {
    marginTop: 2,
    fontSize: 12,
    fontWeight: '700',
  },
  postContent: {
    fontSize: 15,
    lineHeight: 22,
    fontWeight: '600',
  },
  likesText: {
    fontSize: 12,
    fontWeight: '800',
  },
  emptyState: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 24,
    alignItems: 'center',
    gap: 8,
  },
  emptyIcon: {
    fontSize: 34,
  },
  emptyTitle: {
    fontSize: 17,
    fontWeight: '900',
    textAlign: 'center',
  },
  emptyText: {
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  pressed: {
    opacity: 0.76,
  },
});
