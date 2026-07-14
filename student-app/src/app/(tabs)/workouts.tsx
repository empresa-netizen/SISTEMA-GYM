import { router } from 'expo-router';
import { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { BottomTabInset } from '@/constants/theme';
import { useTheme } from '@/hooks/use-theme';
import { usePrescriptionStore } from '@/store/usePrescriptionStore';
import { LogbookEntry, WorkoutPrescription } from '@/types/prescription';

type WorkoutsTab = 'prescriptions' | 'history';

function formatDate(value: string | null): string {
  if (!value) {
    return 'Sem data';
  }

  return new Date(value).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
  });
}

function formatDurationShort(seconds: number): string {
  const minutes = Math.floor(seconds / 60);

  if (minutes < 60) {
    return `${minutes}min`;
  }

  const hours = Math.floor(minutes / 60);
  const remainingMinutes = minutes % 60;

  return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}min` : `${hours}h`;
}

function metadataNumber(logbook: LogbookEntry, key: string): number | null {
  const value = logbook.metadata?.[key];

  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : null;
  }

  if (typeof value === 'string') {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
  }

  return null;
}

function statusLabel(workout: WorkoutPrescription): string {
  if (workout.status === 'completed') {
    return 'Concluído';
  }

  if (workout.completion_percentage > 0) {
    return 'Em andamento';
  }

  return 'Disponível';
}

function WorkoutCard({
  workout,
  colors,
}: {
  workout: WorkoutPrescription;
  colors: ReturnType<typeof useTheme>;
}) {
  const progress = Math.min(100, Math.max(0, workout.completion_percentage));
  const previewActivities = workout.activities.slice(0, 4);

  return (
    <View style={[styles.card, { backgroundColor: colors.surface, borderColor: colors.border }]}>
      <View style={styles.cardHeader}>
        <View style={[styles.iconBubble, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.iconText, { color: colors.tint }]}>🏋️</Text>
        </View>
        <View style={styles.cardTitleBox}>
          <Text style={[styles.eyebrow, { color: colors.tint }]}>MGTEAM APP</Text>
          <Text style={[styles.cardTitle, { color: colors.text }]} numberOfLines={2}>
            {workout.name}
          </Text>
        </View>
      </View>

      <Text style={[styles.cardDescription, { color: colors.textSecondary }]} numberOfLines={2}>
        {workout.description || workout.notes || 'Treino prescrito pelo seu profissional.'}
      </Text>

      <View style={[styles.metaBox, { backgroundColor: colors.backgroundElement }]}>
        <Text style={[styles.metaLabel, { color: colors.text }]}>Prescrição</Text>
        <Text style={[styles.metaValue, { color: colors.text }]}>
          {formatDate(workout.workout_date ?? workout.created_at)}
        </Text>
      </View>

      <View style={[styles.progressTrack, { backgroundColor: colors.backgroundElement }]}>
        <View style={[styles.progressFill, { backgroundColor: colors.tint, width: `${progress}%` }]} />
      </View>
      <View style={styles.progressRow}>
        <Text style={[styles.progressText, { color: colors.textSecondary }]}>
          {workout.activities_completed}/{workout.activities_total} exercícios
        </Text>
        <Text style={[styles.progressTextStrong, { color: colors.text }]}>
          {statusLabel(workout)}
        </Text>
      </View>

      {previewActivities.length > 0 ? (
        <View style={styles.previewList}>
          <Text style={[styles.sectionLabel, { color: colors.tint }]}>EXERCÍCIOS</Text>
          {previewActivities.map((activity, index) => (
            <Text
              key={activity.id}
              numberOfLines={1}
              style={[styles.previewItem, { backgroundColor: colors.backgroundElement, color: colors.textSecondary }]}>
              {index + 1}. {activity.exercise_name} · {activity.sets ?? '-'}x{activity.reps ?? '-'}
            </Text>
          ))}
          {workout.activities.length > previewActivities.length ? (
            <Text style={[styles.moreText, { color: colors.tint }]}>
              +{workout.activities.length - previewActivities.length} exercício(s)
            </Text>
          ) : null}
        </View>
      ) : null}

      <View style={[styles.cardFooter, { borderTopColor: colors.border }]}>
        <Text style={[styles.footerText, { color: colors.textSecondary }]} numberOfLines={1}>
          {workout.workout_code || 'Treino do aluno'}
        </Text>
        <TouchableOpacity
          style={[styles.primaryButton, { backgroundColor: colors.tint }]}
          onPress={() => router.push(`/workouts/${workout.id}`)}>
          <Text style={styles.primaryButtonText}>Ver treino</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

function HistoryCard({ item, colors }: { item: LogbookEntry; colors: ReturnType<typeof useTheme> }) {
  const durationSeconds = metadataNumber(item, 'duration_seconds');

  return (
    <View style={[styles.historyCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
      <Text style={[styles.historyTitle, { color: colors.text }]}>{item.title}</Text>
      <Text style={[styles.historyMeta, { color: colors.textSecondary }]}>
        {formatDate(item.logged_at)}
        {durationSeconds ? ` · ${formatDurationShort(durationSeconds)}` : ''} · {item.comment || 'Registro de treino'}
      </Text>
    </View>
  );
}

export default function WorkoutsScreen() {
  const colors = useTheme();
  const [activeTab, setActiveTab] = useState<WorkoutsTab>('prescriptions');
  const workouts = usePrescriptionStore((state) => state.workouts);
  const logbooks = usePrescriptionStore((state) => state.logbooks);
  const isLoading = usePrescriptionStore((state) => state.isLoading);
  const isRefreshing = usePrescriptionStore((state) => state.isRefreshing);
  const error = usePrescriptionStore((state) => state.error);
  const hasLoaded = usePrescriptionStore((state) => state.hasLoaded);
  const fetchAll = usePrescriptionStore((state) => state.fetchAll);
  const refresh = usePrescriptionStore((state) => state.refresh);

  useEffect(() => {
    if (!hasLoaded) {
      fetchAll();
    }
  }, [fetchAll, hasLoaded]);

  const workoutHistory = logbooks.filter((item) => item.type === 'TRAINING');
  const listData: (WorkoutPrescription | LogbookEntry)[] =
    activeTab === 'prescriptions' ? workouts : workoutHistory;

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <View style={styles.header}>
        <Text style={[styles.title, { color: colors.text }]}>Meus treinos</Text>
        <Text style={[styles.subtitle, { color: colors.textSecondary }]}>
          Execute, registre e conclua os treinos enviados pelo coach.
        </Text>
      </View>

      <View style={[styles.tabRow, { backgroundColor: colors.backgroundElement }]}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'prescriptions' && { backgroundColor: colors.tint }]}
          onPress={() => setActiveTab('prescriptions')}>
          <Text style={[styles.tabText, { color: activeTab === 'prescriptions' ? '#FFFFFF' : colors.textSecondary }]}>
            Prescrições
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'history' && { backgroundColor: colors.tint }]}
          onPress={() => setActiveTab('history')}>
          <Text style={[styles.tabText, { color: activeTab === 'history' ? '#FFFFFF' : colors.textSecondary }]}>
            Histórico
          </Text>
        </TouchableOpacity>
      </View>

      {isLoading ? (
        <View style={styles.centerState}>
          <ActivityIndicator color={colors.tint} />
          <Text style={[styles.centerText, { color: colors.textSecondary }]}>Carregando treinos...</Text>
        </View>
      ) : (
        <FlatList<WorkoutPrescription | LogbookEntry>
          data={listData}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.listContent}
          showsVerticalScrollIndicator={false}
          refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={refresh} tintColor={colors.tint} />}
          ListHeaderComponent={
            error ? (
              <View style={[styles.errorBox, { backgroundColor: colors.backgroundElement }]}>
                <Text style={[styles.errorText, { color: colors.text }]}>{error}</Text>
              </View>
            ) : null
          }
          ListEmptyComponent={
            <View style={[styles.emptyState, { backgroundColor: colors.surface, borderColor: colors.border }]}>
              <Text style={styles.emptyIcon}>{activeTab === 'prescriptions' ? '🏋️' : '📈'}</Text>
              <Text style={[styles.emptyTitle, { color: colors.text }]}>
                {activeTab === 'prescriptions' ? 'Nenhum treino enviado ainda' : 'Nenhum histórico disponível'}
              </Text>
              <Text style={[styles.emptyText, { color: colors.textSecondary }]}>
                {activeTab === 'prescriptions'
                  ? 'Quando o profissional prescrever um treino, ele aparece aqui automaticamente.'
                  : 'Treinos concluídos pelo app aparecem neste histórico.'}
              </Text>
            </View>
          }
          renderItem={({ item }) =>
            activeTab === 'prescriptions' ? (
              <WorkoutCard workout={item as WorkoutPrescription} colors={colors} />
            ) : (
              <HistoryCard item={item as LogbookEntry} colors={colors} />
            )
          }
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 8,
    paddingBottom: 14,
    gap: 6,
  },
  title: {
    fontSize: 30,
    lineHeight: 36,
    fontWeight: '900',
  },
  subtitle: {
    fontSize: 15,
    lineHeight: 22,
  },
  tabRow: {
    flexDirection: 'row',
    marginHorizontal: 20,
    borderRadius: 22,
    padding: 4,
  },
  tab: {
    flex: 1,
    borderRadius: 18,
    paddingVertical: 10,
    alignItems: 'center',
  },
  tabText: {
    fontSize: 14,
    fontWeight: '800',
  },
  centerState: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
  },
  centerText: {
    fontSize: 14,
    fontWeight: '600',
  },
  listContent: {
    padding: 20,
    paddingBottom: BottomTabInset + 24,
    gap: 14,
  },
  card: {
    borderRadius: 20,
    borderWidth: 1,
    padding: 18,
    gap: 14,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  iconBubble: {
    width: 50,
    height: 50,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconText: {
    fontSize: 24,
  },
  cardTitleBox: {
    flex: 1,
    minWidth: 0,
  },
  eyebrow: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.6,
    marginBottom: 3,
  },
  cardTitle: {
    fontSize: 20,
    lineHeight: 25,
    fontWeight: '900',
  },
  cardDescription: {
    fontSize: 14,
    lineHeight: 20,
  },
  metaBox: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  metaLabel: {
    fontSize: 14,
    fontWeight: '800',
  },
  metaValue: {
    fontSize: 14,
    fontWeight: '800',
  },
  progressTrack: {
    height: 7,
    borderRadius: 999,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 999,
  },
  progressRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
  },
  progressText: {
    fontSize: 13,
    fontWeight: '600',
  },
  progressTextStrong: {
    fontSize: 13,
    fontWeight: '900',
  },
  previewList: {
    gap: 8,
  },
  sectionLabel: {
    fontSize: 12,
    fontWeight: '900',
    letterSpacing: 0.7,
  },
  previewItem: {
    borderRadius: 10,
    paddingHorizontal: 11,
    paddingVertical: 9,
    fontSize: 13,
    fontWeight: '600',
  },
  moreText: {
    fontSize: 12,
    fontWeight: '900',
  },
  cardFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
    paddingTop: 14,
    borderTopWidth: 1,
  },
  footerText: {
    flex: 1,
    minWidth: 0,
    fontSize: 13,
    fontWeight: '700',
  },
  primaryButton: {
    borderRadius: 14,
    paddingHorizontal: 16,
    paddingVertical: 11,
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '900',
  },
  historyCard: {
    borderWidth: 1,
    borderRadius: 16,
    padding: 16,
    gap: 6,
  },
  historyTitle: {
    fontSize: 16,
    fontWeight: '900',
  },
  historyMeta: {
    fontSize: 13,
    lineHeight: 19,
  },
  errorBox: {
    borderRadius: 14,
    padding: 12,
    marginBottom: 10,
  },
  errorText: {
    fontSize: 13,
    fontWeight: '700',
  },
  emptyState: {
    minHeight: 260,
    borderRadius: 20,
    borderWidth: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    gap: 8,
  },
  emptyIcon: {
    fontSize: 34,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '900',
    textAlign: 'center',
  },
  emptyText: {
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },
});
