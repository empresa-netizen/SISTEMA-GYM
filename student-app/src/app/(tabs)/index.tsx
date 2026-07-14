import { router } from 'expo-router';
import { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Modal,
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
import { fetchDashboard } from '@/services/dashboard';
import { useAuthStore } from '@/store/useAuthStore';
import { usePrescriptionStore } from '@/store/usePrescriptionStore';
import { DashboardFeedItem, DashboardPayload } from '@/types/dashboard';
import { DietPrescription, LogbookEntry, WorkoutPrescription } from '@/types/prescription';

type KpiItem = {
  key: string;
  label: string;
  value: string;
};

function formatCurrency(value?: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    maximumFractionDigits: 0,
  }).format(value ?? 0);
}

function buildKpis(payload: DashboardPayload | null): KpiItem[] {
  const kpis = payload?.kpis ?? {};

  return [
    {
      key: 'members',
      label: kpis.workouts_total === undefined ? 'Alunos ativos' : 'Treinos',
      value: String(kpis.members_active ?? kpis.workouts_total ?? 0),
    },
    {
      key: 'diet',
      label: kpis.diets_total === undefined ? 'Eventos' : 'Dietas',
      value: String(kpis.events_upcoming ?? kpis.diets_total ?? 0),
    },
    {
      key: 'chat',
      label: kpis.feedbacks === undefined ? 'Conversas' : 'Feedbacks',
      value: String(kpis.conversations_unread ?? kpis.feedbacks ?? 0),
    },
    {
      key: 'revenue',
      label: kpis.revenue_month === undefined ? 'Registros' : 'Receita',
      value:
        kpis.revenue_month === undefined
          ? String(kpis.logbooks ?? kpis.photos ?? 0)
          : formatCurrency(kpis.revenue_month),
    },
  ];
}

function formatDate(value: string | null): string {
  if (!value) {
    return 'Sem data';
  }

  return new Date(value).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
  });
}

function parseFloatInput(value: string): number | null {
  const parsed = Number.parseFloat(value.replace(',', '.'));

  return Number.isFinite(parsed) ? parsed : null;
}

function workoutStatus(workout: WorkoutPrescription): string {
  if (workout.status === 'completed') {
    return 'Concluído';
  }

  if (workout.completion_percentage > 0) {
    return `${workout.completion_percentage}% feito`;
  }

  return 'Pronto para iniciar';
}

function dietStatus(diet: DietPrescription): string {
  if (diet.delivery_status === 'DELIVERED' || diet.status === 'sent') {
    return 'Disponível';
  }

  if (diet.status === 'scheduled') {
    return 'Agendada';
  }

  return 'Em preparo';
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

function isToday(value: string): boolean {
  return new Date(value).toDateString() === new Date().toDateString();
}

function completedMealsForDiet(logbooks: LogbookEntry[], dietId: number): number {
  return new Set(
    logbooks
      .filter((logbook) => logbook.type === 'DIET' && isToday(logbook.logged_at))
      .filter((logbook) => {
        const source = logbook.metadata?.source;

        return source === 'student_diet_meal_complete' || source === 'student_diet_detail_meal';
      })
      .filter((logbook) => metadataNumber(logbook, 'prescription_id') === dietId)
      .map((logbook) => metadataNumber(logbook, 'meal_id'))
      .filter((mealId): mealId is number => mealId !== null),
  ).size;
}

function latestWeightLog(logbooks: LogbookEntry[]): LogbookEntry | null {
  return [...logbooks]
    .filter((logbook) => logbook.type === 'WEIGHT')
    .sort((left, right) => new Date(right.logged_at).getTime() - new Date(left.logged_at).getTime())[0] ?? null;
}

export default function DashboardScreen() {
  const theme = useTheme();
  const user = useAuthStore((state) => state.user);
  const sessionType = useAuthStore((state) => state.session?.type);
  const logout = useAuthStore((state) => state.logout);
  const createLogbook = usePrescriptionStore((state) => state.createLogbook);
  const isSavingLogbook = usePrescriptionStore((state) => state.isSavingLogbook);
  const [payload, setPayload] = useState<DashboardPayload | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [weightVisible, setWeightVisible] = useState(false);
  const [weightValue, setWeightValue] = useState('');
  const [weightComment, setWeightComment] = useState('');

  const loadDashboard = useCallback(async (showSpinner = false) => {
    await Promise.resolve();

    if (showSpinner) {
      setIsLoading(true);
    }

    setError(null);

    try {
      const dashboard = await fetchDashboard(sessionType);
      setPayload(dashboard);
    } catch {
      setError('Nao foi possivel carregar o dashboard agora.');
    } finally {
      setIsLoading(false);
    }
  }, [sessionType]);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      loadDashboard();
    }, 0);

    return () => {
      clearTimeout(timeoutId);
    };
  }, [loadDashboard]);

  const kpis = buildKpis(payload);
  const feed = payload?.recent.feed ?? [];
  const workouts = payload?.recent.workouts ?? [];
  const diets = payload?.recent.diets ?? [];
  const logbooks = payload?.recent.logbooks ?? [];
  const nextWorkout = workouts.find((workout) => workout.status !== 'completed') ?? workouts[0] ?? null;
  const nextDiet = diets.find((diet) => diet.diet_menu) ?? diets[0] ?? null;
  const nextDietMealsCount = nextDiet?.diet_menu?.meals_count ?? 0;
  const nextDietCompletedMeals = nextDiet ? completedMealsForDiet(logbooks, nextDiet.id) : 0;
  const latestWeight = latestWeightLog(logbooks);

  async function handleSaveWeight(): Promise<void> {
    const weight = parseFloatInput(weightValue);

    if (weight === null) {
      setError('Informe um peso válido.');

      return;
    }

    try {
      await createLogbook({
        type: 'WEIGHT',
        title: 'Peso corporal',
        logged_at: new Date().toISOString(),
        numeric_value: weight,
        unit: 'kg',
        metadata: {
          source: 'student_weight_quick_log',
        },
        comment: weightComment.trim() || null,
      });
      setWeightValue('');
      setWeightComment('');
      setWeightVisible(false);
      await loadDashboard(false);
    } catch (saveError) {
      setError(saveError instanceof Error ? saveError.message : 'Nao foi possivel registrar o peso.');
    }
  }

  function renderHeader() {
    return (
      <View style={styles.headerWrapper}>
        <View style={styles.headerRow}>
          <View style={styles.headerText}>
            <Text style={[styles.eyebrow, { color: theme.textSecondary }]}>Inicio</Text>
            <Text style={[styles.title, { color: theme.text }]}>Ola, {user?.name ?? 'Aluno'}</Text>
          </View>
          <Pressable
            accessibilityRole="button"
            onPress={logout}
            style={({ pressed }) => [
              styles.logoutButton,
              { borderColor: theme.border },
              pressed && styles.pressed,
            ]}>
            <Text style={[styles.logoutText, { color: theme.text }]}>Sair</Text>
          </Pressable>
        </View>

        <FlatList
          columnWrapperStyle={styles.kpiRow}
          data={kpis}
          keyExtractor={(item) => item.key}
          numColumns={2}
          renderItem={({ item }) => (
            <View
              style={[
                styles.kpiCard,
                { backgroundColor: theme.surface, borderColor: theme.border },
              ]}>
              <Text style={[styles.kpiValue, { color: theme.text }]}>{item.value}</Text>
              <Text style={[styles.kpiLabel, { color: theme.textSecondary }]}>{item.label}</Text>
            </View>
          )}
          scrollEnabled={false}
        />

        <Pressable
          accessibilityRole="button"
          onPress={() => setWeightVisible(true)}
          style={({ pressed }) => [
            styles.weightCard,
            { backgroundColor: theme.surface, borderColor: theme.border },
            pressed && styles.pressed,
          ]}>
          <View>
            <Text style={[styles.weightCardLabel, { color: theme.tint }]}>CHECK-IN RÁPIDO</Text>
            <Text style={[styles.weightCardTitle, { color: theme.text }]}>Registrar peso corporal</Text>
            {latestWeight?.numeric_value ? (
              <Text style={[styles.weightCardMeta, { color: theme.textSecondary }]}>
                Último: {latestWeight.numeric_value} {latestWeight.unit ?? 'kg'}
              </Text>
            ) : null}
          </View>
          <Text style={[styles.weightCardAction, { color: theme.textSecondary }]}>
            {latestWeight ? 'Atualizar' : 'Adicionar'}
          </Text>
        </Pressable>

        <Pressable
          accessibilityRole="button"
          onPress={() => router.push('/photos')}
          style={({ pressed }) => [
            styles.photoShortcutCard,
            { backgroundColor: theme.surface, borderColor: theme.border },
            pressed && styles.pressed,
          ]}>
          <View style={[styles.photoShortcutIcon, { backgroundColor: theme.backgroundElement }]}>
            <Text style={styles.photoShortcutEmoji}>📸</Text>
          </View>
          <View style={styles.photoShortcutText}>
            <Text style={[styles.weightCardLabel, { color: theme.tint }]}>EVOLUÇÃO VISUAL</Text>
            <Text style={[styles.weightCardTitle, { color: theme.text }]}>Fotos de progresso</Text>
            <Text style={[styles.weightCardMeta, { color: theme.textSecondary }]}>
              {payload?.kpis.photos ?? 0} foto{(payload?.kpis.photos ?? 0) === 1 ? '' : 's'} enviadas
            </Text>
          </View>
          <Text style={[styles.weightCardAction, { color: theme.textSecondary }]}>Abrir</Text>
        </Pressable>

        <Pressable
          accessibilityRole="button"
          onPress={() => router.push('/community')}
          style={({ pressed }) => [
            styles.photoShortcutCard,
            { backgroundColor: theme.surface, borderColor: theme.border },
            pressed && styles.pressed,
          ]}>
          <View style={[styles.photoShortcutIcon, { backgroundColor: theme.backgroundElement }]}>
            <Text style={styles.photoShortcutEmoji}>🤝</Text>
          </View>
          <View style={styles.photoShortcutText}>
            <Text style={[styles.weightCardLabel, { color: theme.tint }]}>COMUNIDADE</Text>
            <Text style={[styles.weightCardTitle, { color: theme.text }]}>Grupos do coach</Text>
            <Text style={[styles.weightCardMeta, { color: theme.textSecondary }]}>
              Check-ins, dúvidas e vitórias em grupo
            </Text>
          </View>
          <Text style={[styles.weightCardAction, { color: theme.textSecondary }]}>Abrir</Text>
        </Pressable>

        <Text style={[styles.sectionTitle, { color: theme.text }]}>Seu plano agora</Text>
        <View style={styles.actionGrid}>
          <Pressable
            accessibilityRole="button"
            disabled={!nextWorkout}
            onPress={() =>
              nextWorkout ? router.push(`/workouts/${nextWorkout.id}`) : router.push('/(tabs)/workouts')
            }
            style={({ pressed }) => [
              styles.actionCard,
              { backgroundColor: theme.surface, borderColor: theme.border },
              pressed && styles.pressed,
              !nextWorkout && styles.disabledCard,
            ]}>
            <View style={[styles.actionIcon, { backgroundColor: theme.backgroundElement }]}>
              <Text style={styles.actionEmoji}>🏋️</Text>
            </View>
            <Text style={[styles.actionEyebrow, { color: theme.tint }]}>TREINO</Text>
            <Text style={[styles.actionTitle, { color: theme.text }]} numberOfLines={2}>
              {nextWorkout?.name ?? 'Nenhum treino ativo'}
            </Text>
            <Text style={[styles.actionMeta, { color: theme.textSecondary }]} numberOfLines={2}>
              {nextWorkout
                ? `${workoutStatus(nextWorkout)} · ${nextWorkout.activities_total} exercícios`
                : 'Quando o coach enviar, aparece aqui.'}
            </Text>
          </Pressable>

          <Pressable
            accessibilityRole="button"
            disabled={!nextDiet}
            onPress={() => (nextDiet ? router.push(`/diets/${nextDiet.id}`) : router.push('/(tabs)/diet'))}
            style={({ pressed }) => [
              styles.actionCard,
              { backgroundColor: theme.surface, borderColor: theme.border },
              pressed && styles.pressed,
              !nextDiet && styles.disabledCard,
            ]}>
            <View style={[styles.actionIcon, { backgroundColor: theme.backgroundElement }]}>
              <Text style={styles.actionEmoji}>🍽️</Text>
            </View>
            <Text style={[styles.actionEyebrow, { color: theme.tint }]}>DIETA</Text>
            <Text style={[styles.actionTitle, { color: theme.text }]} numberOfLines={2}>
              {nextDiet?.title ?? 'Nenhuma dieta ativa'}
            </Text>
            <Text style={[styles.actionMeta, { color: theme.textSecondary }]} numberOfLines={2}>
              {nextDiet?.diet_menu
                ? `${nextDietCompletedMeals}/${nextDietMealsCount} refeições hoje · ${dietStatus(nextDiet)}`
                : nextDiet
                  ? `${dietStatus(nextDiet)} · sem cardápio vinculado`
                  : 'Quando o coach enviar, aparece aqui.'}
            </Text>
          </Pressable>
        </View>

        {workouts.length > 0 || diets.length > 0 ? (
          <View style={styles.compactList}>
            {workouts.slice(0, 2).map((workout) => (
              <Pressable
                key={`workout-${workout.id}`}
                onPress={() => router.push(`/workouts/${workout.id}`)}
                style={({ pressed }) => [
                  styles.compactRow,
                  { backgroundColor: theme.surface, borderColor: theme.border },
                  pressed && styles.pressed,
                ]}>
                <Text style={[styles.compactKind, { color: theme.tint }]}>Treino</Text>
                <Text style={[styles.compactTitle, { color: theme.text }]} numberOfLines={1}>
                  {workout.name}
                </Text>
                <Text style={[styles.compactMeta, { color: theme.textSecondary }]}>
                  {workoutStatus(workout)}
                </Text>
              </Pressable>
            ))}
            {diets.slice(0, 2).map((diet) => (
              <Pressable
                key={`diet-${diet.id}`}
                onPress={() => router.push(`/diets/${diet.id}`)}
                style={({ pressed }) => [
                  styles.compactRow,
                  { backgroundColor: theme.surface, borderColor: theme.border },
                  pressed && styles.pressed,
                ]}>
                <Text style={[styles.compactKind, { color: theme.tint }]}>Dieta</Text>
                <Text style={[styles.compactTitle, { color: theme.text }]} numberOfLines={1}>
                  {diet.title}
                </Text>
                <Text style={[styles.compactMeta, { color: theme.textSecondary }]}>
                  {formatDate(diet.sent_at ?? diet.scheduled_at ?? diet.created_at)}
                </Text>
              </Pressable>
            ))}
          </View>
        ) : null}

        <Text style={[styles.sectionTitle, { color: theme.text }]}>Atualizacoes</Text>
      </View>
    );
  }

  function renderFeedItem({ item }: ListRenderItemInfo<DashboardFeedItem>) {
    return (
      <View
        style={[styles.feedCard, { backgroundColor: theme.surface, borderColor: theme.border }]}>
        <Text style={[styles.feedTitle, { color: theme.text }]} numberOfLines={1}>
          {item.title ?? 'Publicacao do coach'}
        </Text>
        <Text style={[styles.feedBody, { color: theme.textSecondary }]} numberOfLines={3}>
          {item.body ?? item.content ?? 'Sem conteudo para exibir.'}
        </Text>
      </View>
    );
  }

  if (isLoading) {
    return (
      <SafeAreaView style={[styles.centered, { backgroundColor: theme.background }]}>
        <ActivityIndicator color={theme.tint} />
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: theme.background }]}>
      <FlatList
        contentContainerStyle={styles.content}
        data={feed}
        keyExtractor={(item, index) => String(item.id ?? index)}
        ListEmptyComponent={
          <Text style={[styles.emptyText, { color: theme.textSecondary }]}>
            {error ?? 'Nenhuma atualizacao recente por enquanto.'}
          </Text>
        }
        ListHeaderComponent={renderHeader}
        refreshControl={
          <RefreshControl
            refreshing={isLoading}
            tintColor={theme.tint}
            onRefresh={() => loadDashboard(true)}
          />
        }
        renderItem={renderFeedItem}
      />

      <Modal visible={weightVisible} transparent animationType="slide" onRequestClose={() => setWeightVisible(false)}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.modalOverlay}>
          <Pressable style={styles.modalBackdrop} onPress={() => setWeightVisible(false)} />
          <View style={[styles.sheet, { backgroundColor: theme.surface }]}>
            <View style={[styles.sheetHandle, { backgroundColor: theme.textSecondary }]} />
            <Text style={[styles.sheetTitle, { color: theme.text }]}>Registrar peso</Text>
            <Text style={[styles.sheetSubtitle, { color: theme.textSecondary }]}>
              Salve seu peso atual para o coach acompanhar sua evolução.
            </Text>
            <TextInput
              value={weightValue}
              onChangeText={setWeightValue}
              placeholder="Ex: 72.5"
              placeholderTextColor={theme.textMuted}
              keyboardType="decimal-pad"
              style={[
                styles.input,
                { backgroundColor: theme.backgroundElement, color: theme.text, borderColor: theme.border },
              ]}
            />
            <TextInput
              value={weightComment}
              onChangeText={setWeightComment}
              placeholder="Observação opcional"
              placeholderTextColor={theme.textMuted}
              multiline
              style={[
                styles.commentInput,
                { backgroundColor: theme.backgroundElement, color: theme.text, borderColor: theme.border },
              ]}
            />
            {error ? <Text style={[styles.sheetErrorText, { color: '#EF4444' }]}>{error}</Text> : null}
            <Pressable
              disabled={isSavingLogbook}
              style={({ pressed }) => [
                styles.sheetButton,
                { backgroundColor: theme.tint },
                pressed && styles.pressed,
              ]}
              onPress={handleSaveWeight}>
              <Text style={styles.sheetButtonText}>{isSavingLogbook ? 'Salvando...' : 'Salvar peso'}</Text>
            </Pressable>
          </View>
        </KeyboardAvoidingView>
      </Modal>
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
  },
  content: {
    paddingHorizontal: 20,
    paddingTop: 18,
    paddingBottom: BottomTabInset + 24,
    gap: 14,
  },
  headerWrapper: {
    gap: 18,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 16,
  },
  headerText: {
    flex: 1,
    gap: 4,
  },
  eyebrow: {
    fontSize: 13,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  title: {
    fontSize: 28,
    lineHeight: 34,
    fontWeight: '800',
  },
  logoutButton: {
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  logoutText: {
    fontSize: 14,
    fontWeight: '800',
  },
  kpiRow: {
    gap: 12,
    marginBottom: 12,
  },
  kpiCard: {
    flex: 1,
    minHeight: 98,
    borderWidth: 1,
    borderRadius: 8,
    padding: 16,
    justifyContent: 'space-between',
  },
  kpiValue: {
    fontSize: 24,
    lineHeight: 30,
    fontWeight: '800',
  },
  kpiLabel: {
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  weightCard: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  weightCardLabel: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.6,
  },
  weightCardTitle: {
    fontSize: 16,
    fontWeight: '900',
    marginTop: 3,
  },
  weightCardMeta: {
    fontSize: 12,
    fontWeight: '800',
    marginTop: 4,
  },
  weightCardAction: {
    fontSize: 13,
    fontWeight: '900',
  },
  photoShortcutCard: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  photoShortcutIcon: {
    width: 46,
    height: 46,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  photoShortcutEmoji: {
    fontSize: 24,
  },
  photoShortcutText: {
    flex: 1,
  },
  sectionTitle: {
    fontSize: 20,
    lineHeight: 26,
    fontWeight: '800',
  },
  actionGrid: {
    flexDirection: 'row',
    gap: 12,
  },
  actionCard: {
    flex: 1,
    minHeight: 188,
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
    gap: 8,
  },
  disabledCard: {
    opacity: 0.72,
  },
  actionIcon: {
    width: 44,
    height: 44,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 2,
  },
  actionEmoji: {
    fontSize: 24,
  },
  actionEyebrow: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.7,
  },
  actionTitle: {
    fontSize: 17,
    lineHeight: 22,
    fontWeight: '900',
  },
  actionMeta: {
    fontSize: 13,
    lineHeight: 19,
    fontWeight: '700',
  },
  compactList: {
    gap: 10,
  },
  compactRow: {
    borderWidth: 1,
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 12,
    gap: 3,
  },
  compactKind: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.6,
    textTransform: 'uppercase',
  },
  compactTitle: {
    fontSize: 15,
    fontWeight: '900',
  },
  compactMeta: {
    fontSize: 12,
    fontWeight: '700',
  },
  feedCard: {
    borderWidth: 1,
    borderRadius: 8,
    padding: 16,
    gap: 8,
  },
  feedTitle: {
    fontSize: 16,
    lineHeight: 22,
    fontWeight: '800',
  },
  feedBody: {
    fontSize: 14,
    lineHeight: 20,
  },
  emptyText: {
    textAlign: 'center',
    fontSize: 14,
    lineHeight: 20,
    paddingVertical: 32,
  },
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
  input: {
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 13,
    fontSize: 16,
    fontWeight: '800',
  },
  commentInput: {
    minHeight: 96,
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    textAlignVertical: 'top',
  },
  sheetErrorText: {
    fontSize: 13,
    fontWeight: '800',
  },
  sheetButton: {
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
  },
  sheetButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '900',
  },
  pressed: {
    opacity: 0.75,
  },
});
