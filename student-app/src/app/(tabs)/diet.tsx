import { router } from 'expo-router';
import { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Modal,
  Platform,
  RefreshControl,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { BottomTabInset } from '@/constants/theme';
import { useTheme } from '@/hooks/use-theme';
import { usePrescriptionStore } from '@/store/usePrescriptionStore';
import { DietPrescription, LogbookEntry } from '@/types/prescription';

type DietTab = 'plans' | 'history';

function formatDate(value: string | null): string {
  if (!value) {
    return 'Sem data';
  }

  return new Date(value).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
  });
}

function formatMacro(value: number): string {
  return Number.isInteger(value) ? String(value) : value.toFixed(1);
}

function parseFloatInput(value: string): number | null {
  const parsed = Number.parseFloat(value.replace(',', '.'));

  return Number.isFinite(parsed) ? parsed : null;
}

function statusLabel(prescription: DietPrescription): string {
  if (prescription.delivery_status === 'DELIVERED' || prescription.status === 'sent') {
    return 'Disponível';
  }

  if (prescription.status === 'scheduled') {
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

function completedDietMealsByPrescription(logbooks: LogbookEntry[]): Map<number, Set<number>> {
  const map = new Map<number, Set<number>>();

  logbooks
    .filter((logbook) => logbook.type === 'DIET' && isToday(logbook.logged_at))
    .forEach((logbook) => {
      const source = logbook.metadata?.source;

      if (source !== 'student_diet_meal_complete' && source !== 'student_diet_detail_meal') {
        return;
      }

      const prescriptionId = metadataNumber(logbook, 'prescription_id');
      const mealId = metadataNumber(logbook, 'meal_id');

      if (prescriptionId === null || mealId === null) {
        return;
      }

      const mealIds = map.get(prescriptionId) ?? new Set<number>();
      mealIds.add(mealId);
      map.set(prescriptionId, mealIds);
    });

  return map;
}

function DietCard({
  prescription,
  completedMeals,
  colors,
}: {
  prescription: DietPrescription;
  completedMeals: number;
  colors: ReturnType<typeof useTheme>;
}) {
  const menu = prescription.diet_menu;
  const macros = menu?.macros ?? { calories: 0, protein: 0, carbs: 0, fat: 0 };
  const sentDate = prescription.sent_at ?? prescription.scheduled_at ?? prescription.created_at;
  const mealsCount = menu?.meals_count ?? 0;
  const progress = mealsCount > 0 ? Math.min(100, Math.round((completedMeals / mealsCount) * 100)) : 0;

  return (
    <View style={[styles.card, { backgroundColor: colors.surface, borderColor: colors.border }]}>
      <View style={styles.cardHeader}>
        <View style={[styles.iconBubble, { backgroundColor: colors.backgroundElement }]}>
          <Text style={styles.iconText}>🍽️</Text>
        </View>
        <View style={styles.cardTitleBox}>
          <Text style={[styles.eyebrow, { color: colors.tint }]}>MGTEAM APP</Text>
          <Text style={[styles.cardTitle, { color: colors.text }]} numberOfLines={2}>
            {prescription.title}
          </Text>
        </View>
        <View style={[styles.statusPill, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.statusText, { color: colors.text }]}>{statusLabel(prescription)}</Text>
        </View>
      </View>

      <Text style={[styles.cardDescription, { color: colors.textSecondary }]} numberOfLines={2}>
        {prescription.notes || menu?.description || 'Plano alimentar enviado pelo seu profissional.'}
      </Text>

      <View style={styles.summaryRow}>
        <View style={[styles.summaryPill, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.summaryValue, { color: colors.text }]}>{mealsCount}</Text>
          <Text style={[styles.summaryLabel, { color: colors.textSecondary }]}>refeições</Text>
        </View>
        <View style={[styles.summaryPill, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.summaryValue, { color: colors.text }]}>{formatMacro(macros.calories)}</Text>
          <Text style={[styles.summaryLabel, { color: colors.textSecondary }]}>kcal</Text>
        </View>
        <View style={[styles.summaryPill, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.summaryValue, { color: colors.text }]}>{formatMacro(macros.protein)}g</Text>
          <Text style={[styles.summaryLabel, { color: colors.textSecondary }]}>proteína</Text>
        </View>
      </View>

      <View style={styles.macroGrid}>
        <Text style={[styles.macroText, { color: colors.textSecondary }]}>
          P {formatMacro(macros.protein)}g
        </Text>
        <Text style={[styles.macroText, { color: colors.textSecondary }]}>
          C {formatMacro(macros.carbs)}g
        </Text>
        <Text style={[styles.macroText, { color: colors.textSecondary }]}>G {formatMacro(macros.fat)}g</Text>
      </View>

      <View style={styles.dailyProgressBox}>
        <View style={styles.dailyProgressHeader}>
          <Text style={[styles.dailyProgressLabel, { color: colors.text }]}>Progresso de hoje</Text>
          <Text style={[styles.dailyProgressValue, { color: colors.textSecondary }]}>
            {completedMeals}/{mealsCount} refeições
          </Text>
        </View>
        <View style={[styles.progressTrack, { backgroundColor: colors.backgroundElement }]}>
          <View style={[styles.progressFill, { backgroundColor: colors.tint, width: `${progress}%` }]} />
        </View>
      </View>

      <View style={[styles.cardFooter, { borderTopColor: colors.border }]}>
        <Text style={[styles.footerText, { color: colors.textSecondary }]} numberOfLines={1}>
          {menu?.name || 'Cardápio'} · {formatDate(sentDate)}
        </Text>
        <TouchableOpacity
          style={[styles.primaryButton, { backgroundColor: colors.tint }]}
          onPress={() => router.push(`/diets/${prescription.id}`)}>
          <Text style={styles.primaryButtonText}>Ver dieta</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

function HistoryCard({ item, colors }: { item: LogbookEntry; colors: ReturnType<typeof useTheme> }) {
  return (
    <View style={[styles.historyCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
      <Text style={[styles.historyTitle, { color: colors.text }]}>{item.title}</Text>
      <Text style={[styles.historyMeta, { color: colors.textSecondary }]}>
        {formatDate(item.logged_at)}
        {item.numeric_value ? ` · ${item.numeric_value} ${item.unit ?? ''}` : ''}
      </Text>
    </View>
  );
}

export default function DietScreen() {
  const colors = useTheme();
  const [activeTab, setActiveTab] = useState<DietTab>('plans');
  const diets = usePrescriptionStore((state) => state.diets);
  const logbooks = usePrescriptionStore((state) => state.logbooks);
  const isLoading = usePrescriptionStore((state) => state.isLoading);
  const isRefreshing = usePrescriptionStore((state) => state.isRefreshing);
  const hasLoaded = usePrescriptionStore((state) => state.hasLoaded);
  const error = usePrescriptionStore((state) => state.error);
  const fetchAll = usePrescriptionStore((state) => state.fetchAll);
  const refresh = usePrescriptionStore((state) => state.refresh);
  const createLogbook = usePrescriptionStore((state) => state.createLogbook);
  const isSavingLogbook = usePrescriptionStore((state) => state.isSavingLogbook);
  const [freeMealVisible, setFreeMealVisible] = useState(false);
  const [freeMealTitle, setFreeMealTitle] = useState('');
  const [freeMealCalories, setFreeMealCalories] = useState('');
  const [freeMealComment, setFreeMealComment] = useState('');

  useEffect(() => {
    if (!hasLoaded) {
      fetchAll();
    }
  }, [fetchAll, hasLoaded]);

  const dietHistory = logbooks.filter((item) => item.type === 'DIET');
  const completedMealsByPrescription = useMemo(
    () => completedDietMealsByPrescription(logbooks),
    [logbooks],
  );
  const listData: (DietPrescription | LogbookEntry)[] = activeTab === 'plans' ? diets : dietHistory;

  async function handleSaveFreeMeal(): Promise<void> {
    const title = freeMealTitle.trim() || 'Refeição livre';
    const calories = parseFloatInput(freeMealCalories);

    try {
      await createLogbook({
        type: 'DIET',
        title,
        logged_at: new Date().toISOString(),
        numeric_value: calories,
        unit: calories === null ? null : 'kcal',
        metadata: {
          source: 'student_diet_free_meal',
          meal_name: title,
        },
        comment: freeMealComment.trim() || null,
      });
      setFreeMealTitle('');
      setFreeMealCalories('');
      setFreeMealComment('');
      setFreeMealVisible(false);
      setActiveTab('history');
    } catch {
      return;
    }
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <View style={styles.header}>
        <View style={styles.headerTopRow}>
          <View style={styles.headerTextBox}>
            <Text style={[styles.title, { color: colors.text }]}>Minhas dietas</Text>
            <Text style={[styles.subtitle, { color: colors.textSecondary }]}>
              Refeições, alimentos, macros e check-ins do plano alimentar.
            </Text>
          </View>
          <TouchableOpacity
            style={[styles.quickLogButton, { backgroundColor: colors.tint }]}
            onPress={() => setFreeMealVisible(true)}>
            <Text style={styles.quickLogButtonText}>+ Livre</Text>
          </TouchableOpacity>
        </View>
      </View>

      <View style={[styles.tabRow, { backgroundColor: colors.backgroundElement }]}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'plans' && { backgroundColor: colors.tint }]}
          onPress={() => setActiveTab('plans')}>
          <Text style={[styles.tabText, { color: activeTab === 'plans' ? '#FFFFFF' : colors.textSecondary }]}>
            Dietas
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
          <Text style={[styles.centerText, { color: colors.textSecondary }]}>Carregando dietas...</Text>
        </View>
      ) : (
        <FlatList<DietPrescription | LogbookEntry>
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
              <Text style={styles.emptyIcon}>{activeTab === 'plans' ? '🍽️' : '✅'}</Text>
              <Text style={[styles.emptyTitle, { color: colors.text }]}>
                {activeTab === 'plans' ? 'Nenhuma dieta enviada ainda' : 'Nenhum check-in alimentar'}
              </Text>
              <Text style={[styles.emptyText, { color: colors.textSecondary }]}>
                {activeTab === 'plans'
                  ? 'Quando o profissional prescrever uma dieta, ela aparece aqui com refeições e macros.'
                  : 'Refeições concluídas aparecem aqui automaticamente.'}
              </Text>
            </View>
          }
          renderItem={({ item }) =>
            activeTab === 'plans' ? (
              <DietCard
                prescription={item as DietPrescription}
                completedMeals={completedMealsByPrescription.get((item as DietPrescription).id)?.size ?? 0}
                colors={colors}
              />
            ) : (
              <HistoryCard item={item as LogbookEntry} colors={colors} />
            )
          }
        />
      )}

      <Modal visible={freeMealVisible} transparent animationType="slide" onRequestClose={() => setFreeMealVisible(false)}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.modalOverlay}>
          <TouchableOpacity style={styles.modalBackdrop} activeOpacity={1} onPress={() => setFreeMealVisible(false)} />
          <View style={[styles.sheet, { backgroundColor: colors.surface }]}>
            <View style={[styles.sheetHandle, { backgroundColor: colors.textSecondary }]} />
            <Text style={[styles.sheetTitle, { color: colors.text }]}>Registrar refeição livre</Text>
            <Text style={[styles.sheetSubtitle, { color: colors.textSecondary }]}>
              Use quando comer algo fora do plano ou quiser avisar o coach sobre uma refeição extra.
            </Text>
            <TextInput
              value={freeMealTitle}
              onChangeText={setFreeMealTitle}
              placeholder="Ex: Jantar fora"
              placeholderTextColor={colors.textMuted}
              style={[
                styles.input,
                { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
              ]}
            />
            <TextInput
              value={freeMealCalories}
              onChangeText={setFreeMealCalories}
              placeholder="Calorias aproximadas (opcional)"
              placeholderTextColor={colors.textMuted}
              keyboardType="decimal-pad"
              style={[
                styles.input,
                { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
              ]}
            />
            <TextInput
              value={freeMealComment}
              onChangeText={setFreeMealComment}
              placeholder="Observação opcional"
              placeholderTextColor={colors.textMuted}
              multiline
              style={[
                styles.commentInput,
                { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
              ]}
            />
            {error ? <Text style={[styles.sheetErrorText, { color: '#EF4444' }]}>{error}</Text> : null}
            <TouchableOpacity
              disabled={isSavingLogbook}
              style={[styles.sheetButton, { backgroundColor: colors.tint }]}
              onPress={handleSaveFreeMeal}>
              <Text style={styles.sheetButtonText}>{isSavingLogbook ? 'Salvando...' : 'Salvar no diário'}</Text>
            </TouchableOpacity>
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
  header: {
    paddingHorizontal: 20,
    paddingTop: 8,
    paddingBottom: 14,
  },
  headerTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  headerTextBox: {
    flex: 1,
    minWidth: 0,
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
  quickLogButton: {
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  quickLogButtonText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '900',
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
  statusPill: {
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 7,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '900',
  },
  cardDescription: {
    fontSize: 14,
    lineHeight: 20,
  },
  summaryRow: {
    flexDirection: 'row',
    gap: 8,
  },
  summaryPill: {
    flex: 1,
    borderRadius: 14,
    paddingVertical: 12,
    alignItems: 'center',
    gap: 2,
  },
  summaryValue: {
    fontSize: 16,
    fontWeight: '900',
  },
  summaryLabel: {
    fontSize: 11,
    fontWeight: '700',
  },
  macroGrid: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  macroText: {
    fontSize: 13,
    fontWeight: '800',
  },
  dailyProgressBox: {
    gap: 8,
  },
  dailyProgressHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  dailyProgressLabel: {
    fontSize: 13,
    fontWeight: '900',
  },
  dailyProgressValue: {
    fontSize: 12,
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
    fontSize: 15,
    fontWeight: '700',
  },
  commentInput: {
    minHeight: 98,
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
});
