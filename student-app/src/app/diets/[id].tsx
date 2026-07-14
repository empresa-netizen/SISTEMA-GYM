import { router, useLocalSearchParams } from 'expo-router';
import { openBrowserAsync, WebBrowserPresentationStyle } from 'expo-web-browser';
import { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  DimensionValue,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { StudentFeedbackSheet } from '@/components/student-feedback-sheet';
import { useTheme } from '@/hooks/use-theme';
import { fetchDietPrintLink } from '@/services/prescriptions';
import { usePrescriptionStore } from '@/store/usePrescriptionStore';
import { DietMeal, DietPrescription, LogbookEntry } from '@/types/prescription';

type DietFeedbackContext = {
  title: string;
  subtitle: string;
  contextType: 'diet' | 'meal';
  contextId: number;
};

type MacroTotals = {
  calories: number;
  protein: number;
  carbs: number;
  fat: number;
};

function asRouteId(value: string | string[] | undefined): number | null {
  const rawValue = Array.isArray(value) ? value[0] : value;
  const parsed = Number(rawValue);

  return Number.isFinite(parsed) ? parsed : null;
}

function formatMacro(value: number): string {
  return Number.isInteger(value) ? String(value) : value.toFixed(1);
}

function toNumber(value: number | string | null | undefined): number {
  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : 0;
  }

  if (typeof value === 'string') {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : 0;
  }

  return 0;
}

function metadataNumber(logbook: LogbookEntry, key: string): number | null {
  const value = logbook.metadata?.[key];

  if (typeof value === 'number') {
    return value;
  }

  if (typeof value === 'string') {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
  }

  return null;
}

function metadataMacros(logbook: LogbookEntry): MacroTotals {
  const macros = logbook.metadata?.macros;

  if (!macros || typeof macros !== 'object') {
    return {
      calories: toNumber(logbook.numeric_value),
      protein: 0,
      carbs: 0,
      fat: 0,
    };
  }

  return {
    calories: toNumber((macros as Record<string, unknown>).calories as number | string | null),
    protein: toNumber((macros as Record<string, unknown>).protein as number | string | null),
    carbs: toNumber((macros as Record<string, unknown>).carbs as number | string | null),
    fat: toNumber((macros as Record<string, unknown>).fat as number | string | null),
  };
}

function isToday(value: string): boolean {
  return new Date(value).toDateString() === new Date().toDateString();
}

function dietMealLogsForPrescription(
  logbooks: LogbookEntry[],
  prescriptionId: number,
): LogbookEntry[] {
  return logbooks
    .filter((logbook) => logbook.type === 'DIET' && isToday(logbook.logged_at))
    .filter((logbook) => {
      const source = logbook.metadata?.source;

      return (
        metadataNumber(logbook, 'prescription_id') === prescriptionId &&
        (source === 'student_diet_meal_complete' || source === 'student_diet_detail_meal')
      );
    });
}

function completedMealIdsForLogs(logbooks: LogbookEntry[]): Set<number> {
  return new Set(
    logbooks
      .map((logbook) => metadataNumber(logbook, 'meal_id'))
      .filter((mealId): mealId is number => typeof mealId === 'number'),
  );
}

function completedMealLogMap(logbooks: LogbookEntry[]): Map<number, LogbookEntry> {
  const map = new Map<number, LogbookEntry>();

  logbooks.forEach((logbook) => {
    const mealId = metadataNumber(logbook, 'meal_id');

    if (typeof mealId === 'number' && !map.has(mealId)) {
      map.set(mealId, logbook);
    }
  });

  return map;
}

function consumedMacrosFromLogs(logbooks: LogbookEntry[]): MacroTotals {
  return logbooks.reduce<MacroTotals>(
    (totals, logbook) => {
      const macros = metadataMacros(logbook);

      return {
        calories: totals.calories + macros.calories,
        protein: totals.protein + macros.protein,
        carbs: totals.carbs + macros.carbs,
        fat: totals.fat + macros.fat,
      };
    },
    { calories: 0, protein: 0, carbs: 0, fat: 0 },
  );
}

function MacroBar({
  label,
  consumed,
  target,
  color,
  colors,
}: {
  label: string;
  consumed: number;
  target: number;
  color: string;
  colors: ReturnType<typeof useTheme>;
}) {
  const percentage = target > 0 ? Math.min(100, Math.max(0, Math.round((consumed / target) * 100))) : 0;
  const width = `${Math.max(8, percentage)}%` as DimensionValue;

  return (
    <View style={styles.macroItem}>
      <Text style={[styles.macroLabel, { color }]}>{label}</Text>
      <View style={[styles.macroTrack, { backgroundColor: colors.backgroundElement }]}>
        <View style={[styles.macroFill, { backgroundColor: color, width }]} />
      </View>
      <Text style={[styles.macroValue, { color: colors.textSecondary }]}>
        {formatMacro(consumed)}/{formatMacro(target)}g
      </Text>
    </View>
  );
}

function DietHeader({
  prescription,
  consumedMacros,
  openingPrint,
  colors,
  onOpenPrint,
  onOpenFeedback,
}: {
  prescription: DietPrescription;
  consumedMacros: MacroTotals;
  openingPrint: boolean;
  colors: ReturnType<typeof useTheme>;
  onOpenPrint: () => void;
  onOpenFeedback: () => void;
}) {
  const menu = prescription.diet_menu;
  const macros = menu?.macros ?? { calories: 0, protein: 0, carbs: 0, fat: 0 };
  const percentage = macros.calories > 0 ? Math.min(100, Math.round((consumedMacros.calories / macros.calories) * 100)) : 0;

  return (
    <View style={[styles.hero, { backgroundColor: colors.surface, borderColor: colors.border }]}>
      <View style={styles.topRow}>
        <TouchableOpacity
          style={[styles.backButton, { backgroundColor: colors.backgroundElement }]}
          onPress={() => (router.canGoBack() ? router.back() : router.replace('/(tabs)/diet'))}>
          <Text style={[styles.backButtonText, { color: colors.text }]}>‹</Text>
        </TouchableOpacity>
        <View style={styles.heroTitleBox}>
          <Text style={[styles.eyebrow, { color: colors.tint }]}>PLANO ALIMENTAR</Text>
          <Text style={[styles.heroTitle, { color: colors.text }]} numberOfLines={2}>
            {prescription.title}
          </Text>
        </View>
      </View>

      <View style={styles.calorieRow}>
        <View style={[styles.circle, { borderColor: colors.tint }]}>
          <Text style={[styles.circlePercent, { color: colors.text }]}>{percentage}%</Text>
          <Text style={[styles.circleLabel, { color: colors.textSecondary }]}>do dia</Text>
        </View>
        <View style={styles.calorieInfo}>
          <Text style={[styles.calorieText, { color: colors.textSecondary }]}>
            <Text style={[styles.calorieStrong, { color: colors.text }]}>
              {formatMacro(consumedMacros.calories)}
            </Text>{' '}
            / {formatMacro(macros.calories)} kcal
          </Text>
          <MacroBar label="P" consumed={consumedMacros.protein} target={macros.protein} color={colors.tint} colors={colors} />
          <MacroBar label="C" consumed={consumedMacros.carbs} target={macros.carbs} color="#F59E0B" colors={colors} />
          <MacroBar label="G" consumed={consumedMacros.fat} target={macros.fat} color="#EF4444" colors={colors} />
        </View>
      </View>

      {prescription.notes || menu?.description ? (
        <View style={[styles.infoBox, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.infoTitle, { color: colors.text }]}>Observações</Text>
          <Text style={[styles.infoText, { color: colors.textSecondary }]}>
            {prescription.notes || menu?.description}
          </Text>
        </View>
      ) : null}

      <View style={styles.headerActionRow}>
        <TouchableOpacity
          activeOpacity={0.85}
          disabled={openingPrint}
          style={[styles.printButton, { backgroundColor: colors.tint }]}
          onPress={onOpenPrint}>
          <Text style={styles.printButtonText}>
            {openingPrint ? 'Preparando...' : 'Abrir / imprimir'}
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          activeOpacity={0.85}
          style={[styles.feedbackButton, { borderColor: colors.border, backgroundColor: colors.backgroundElement }]}
          onPress={onOpenFeedback}>
          <Text style={[styles.feedbackButtonText, { color: colors.text }]}>Feedback</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

function MealCard({
  meal,
  completed,
  completedLog,
  expanded,
  saving,
  colors,
  onToggle,
  onToggleComplete,
  onFeedback,
}: {
  meal: DietMeal;
  completed: boolean;
  completedLog: LogbookEntry | null;
  expanded: boolean;
  saving: boolean;
  colors: ReturnType<typeof useTheme>;
  onToggle: () => void;
  onToggleComplete: () => void;
  onFeedback: () => void;
}) {
  const preview = meal.foods
    .slice(0, 3)
    .map((food) => food.name)
    .filter(Boolean)
    .join(', ');

  return (
    <View
      style={[
        styles.mealCard,
        { backgroundColor: colors.surface, borderColor: completed ? colors.tint : colors.border },
      ]}>
      <View style={styles.mealHeader}>
        <View style={styles.mealTimeBox}>
          <Text style={[styles.mealTime, { color: colors.tint }]}>{meal.time_label || '--:--'}</Text>
          <Text style={[styles.mealCalories, { color: colors.textSecondary }]}>
            {formatMacro(meal.macros.calories)} kcal
          </Text>
        </View>
        <TouchableOpacity style={styles.mealTitleBox} activeOpacity={0.8} onPress={onToggle}>
          <Text style={[styles.mealName, { color: colors.text }]} numberOfLines={2}>
            {meal.name}
          </Text>
          <Text style={[styles.mealPreview, { color: colors.textSecondary }]} numberOfLines={1}>
            {preview || 'Sem alimentos cadastrados'}
          </Text>
          <Text style={[styles.mealMacros, { color: colors.textSecondary }]}>
            P {formatMacro(meal.macros.protein)}g · C {formatMacro(meal.macros.carbs)}g · G{' '}
            {formatMacro(meal.macros.fat)}g
          </Text>
          {completedLog ? (
            <Text style={[styles.mealCompletedText, { color: colors.tint }]}>
              Concluída às {new Date(completedLog.logged_at).toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
              })}
            </Text>
          ) : null}
          <Text style={[styles.mealActionHint, { color: completed ? colors.textSecondary : colors.tint }]}>
            {completed ? 'Toque no check para desmarcar' : 'Toque no círculo para concluir'}
          </Text>
          <Text style={[styles.toggleText, { color: colors.tint }]}>
            {expanded ? 'Ocultar alimentos ∧' : `Ver ${meal.foods.length} alimento(s) ∨`}
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          disabled={saving}
          activeOpacity={0.75}
          style={[
            styles.checkCircle,
            {
              backgroundColor: completed ? colors.tint : 'transparent',
              borderColor: completed ? colors.tint : colors.textSecondary,
            },
          ]}
          onPress={onToggleComplete}>
          <Text style={[styles.checkText, { color: completed ? '#FFFFFF' : colors.textSecondary }]}>
            {saving ? '…' : completed ? '✓' : ''}
          </Text>
        </TouchableOpacity>
      </View>

      {expanded ? (
        <View style={[styles.foodList, { borderTopColor: colors.border }]}>
          {meal.foods.length > 0 ? (
            meal.foods.map((food) => (
              <View key={food.id} style={styles.foodRow}>
                <View style={[styles.foodBullet, { backgroundColor: colors.tint }]} />
                <View style={styles.foodTextBox}>
                  <Text style={[styles.foodName, { color: colors.text }]}>
                    {formatMacro(food.quantity_in_grams)}g {food.name || 'Alimento'}
                  </Text>
                  <Text style={[styles.foodMeta, { color: colors.textSecondary }]}>
                    {food.food_group || 'Grupo não informado'} · {formatMacro(food.macros.calories)} kcal · P{' '}
                    {formatMacro(food.macros.protein)}g C {formatMacro(food.macros.carbs)}g G{' '}
                    {formatMacro(food.macros.fat)}g
                  </Text>
                  {food.notes ? (
                    <Text style={[styles.foodNotes, { color: colors.textSecondary }]}>{food.notes}</Text>
                  ) : null}
                </View>
              </View>
            ))
          ) : (
            <Text style={[styles.emptyMealText, { color: colors.textSecondary }]}>
              Esta refeição ainda não possui alimentos detalhados.
            </Text>
          )}
          {meal.notes ? (
            <View style={[styles.mealNotesBox, { backgroundColor: colors.backgroundElement }]}>
              <Text style={[styles.infoTitle, { color: colors.text }]}>Observação da refeição</Text>
              <Text style={[styles.infoText, { color: colors.textSecondary }]}>{meal.notes}</Text>
            </View>
          ) : null}
          <TouchableOpacity style={[styles.mealFeedbackButton, { borderColor: colors.border }]} onPress={onFeedback}>
            <Text style={[styles.mealFeedbackText, { color: colors.text }]}>Reportar ajuste nesta refeição</Text>
          </TouchableOpacity>
        </View>
      ) : null}
    </View>
  );
}

export default function DietDetailScreen() {
  const colors = useTheme();
  const { id } = useLocalSearchParams<{ id?: string }>();
  const routeId = asRouteId(id);
  const diets = usePrescriptionStore((state) => state.diets);
  const logbooks = usePrescriptionStore((state) => state.logbooks);
  const isLoading = usePrescriptionStore((state) => state.isLoading);
  const hasLoaded = usePrescriptionStore((state) => state.hasLoaded);
  const error = usePrescriptionStore((state) => state.error);
  const savingMealId = usePrescriptionStore((state) => state.savingMealId);
  const isRefreshing = usePrescriptionStore((state) => state.isRefreshing);
  const fetchAll = usePrescriptionStore((state) => state.fetchAll);
  const refresh = usePrescriptionStore((state) => state.refresh);
  const completeDietMeal = usePrescriptionStore((state) => state.completeDietMeal);
  const uncompleteDietMeal = usePrescriptionStore((state) => state.uncompleteDietMeal);
  const sendFeedback = usePrescriptionStore((state) => state.sendFeedback);
  const isSendingFeedback = usePrescriptionStore((state) => state.isSendingFeedback);
  const [expandedMeals, setExpandedMeals] = useState<Record<number, boolean>>({});
  const [openingPrint, setOpeningPrint] = useState(false);
  const [printError, setPrintError] = useState<string | null>(null);
  const [feedbackContext, setFeedbackContext] = useState<DietFeedbackContext | null>(null);

  const prescription = useMemo(
    () => diets.find((candidate) => candidate.id === routeId) ?? null,
    [diets, routeId],
  );
  const dietMealLogs = useMemo(
    () => (prescription ? dietMealLogsForPrescription(logbooks, prescription.id) : []),
    [logbooks, prescription],
  );
  const completedMealIds = useMemo(() => completedMealIdsForLogs(dietMealLogs), [dietMealLogs]);
  const completedLogsByMealId = useMemo(() => completedMealLogMap(dietMealLogs), [dietMealLogs]);
  const consumedMacros = useMemo(
    () => consumedMacrosFromLogs(dietMealLogs),
    [dietMealLogs],
  );

  useEffect(() => {
    if (!hasLoaded) {
      fetchAll();
    }
  }, [fetchAll, hasLoaded]);

  async function handleToggleMealCompletion(meal: DietMeal): Promise<void> {
    if (!prescription) {
      return;
    }

    try {
      if (completedMealIds.has(meal.id)) {
        await uncompleteDietMeal(prescription, meal);
      } else {
        await completeDietMeal(prescription, meal);
      }
    } catch {
      return;
    }
  }

  async function handleOpenPrint(): Promise<void> {
    if (!prescription) {
      return;
    }

    setOpeningPrint(true);
    setPrintError(null);

    try {
      const url = await fetchDietPrintLink(prescription.id);
      await openBrowserAsync(url, {
        presentationStyle: WebBrowserPresentationStyle.AUTOMATIC,
      });
    } catch (openError) {
      setPrintError(openError instanceof Error ? openError.message : 'Nao foi possivel abrir a dieta.');
    } finally {
      setOpeningPrint(false);
    }
  }

  async function handleSubmitFeedback(payload: { message: string; rating: number | null }): Promise<void> {
    if (!feedbackContext) {
      return;
    }

    await sendFeedback({
      message: payload.message,
      rating: payload.rating,
      context_type: feedbackContext.contextType,
      context_id: feedbackContext.contextId,
    });
    setFeedbackContext(null);
  }

  if (isLoading || !hasLoaded) {
    return (
      <SafeAreaView style={[styles.safeArea, styles.centerState, { backgroundColor: colors.background }]}>
        <ActivityIndicator color={colors.tint} />
        <Text style={[styles.centerText, { color: colors.textSecondary }]}>Carregando dieta...</Text>
      </SafeAreaView>
    );
  }

  if (!prescription) {
    return (
      <SafeAreaView style={[styles.safeArea, styles.centerState, { backgroundColor: colors.background }]}>
        <Text style={styles.emptyIcon}>🍽️</Text>
        <Text style={[styles.centerTitle, { color: colors.text }]}>Dieta não encontrada</Text>
        <Text style={[styles.centerText, { color: colors.textSecondary }]}>
          {error || 'Atualize a lista de dietas e tente novamente.'}
        </Text>
        <TouchableOpacity style={[styles.primaryButton, { backgroundColor: colors.tint }]} onPress={() => router.back()}>
          <Text style={styles.primaryButtonText}>Voltar</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  const menu = prescription.diet_menu;

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={refresh} tintColor={colors.tint} />}
        showsVerticalScrollIndicator={false}>
        <DietHeader
          prescription={prescription}
          consumedMacros={consumedMacros}
          openingPrint={openingPrint}
          colors={colors}
          onOpenPrint={handleOpenPrint}
          onOpenFeedback={() =>
            setFeedbackContext({
              title: 'Feedback da dieta',
              subtitle: 'Avise o coach se alguma refeição, alimento ou horário não encaixou.',
              contextType: 'diet',
              contextId: prescription.id,
            })
          }
        />

        {error || printError ? (
          <View style={[styles.inlineErrorBox, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
            <Text style={[styles.inlineErrorText, { color: colors.text }]}>{error || printError}</Text>
          </View>
        ) : null}

        <View style={styles.quickInfoGrid}>
          <View style={[styles.quickInfoCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.quickInfoValue, { color: colors.text }]}>{menu?.meals_count ?? 0}</Text>
            <Text style={[styles.quickInfoLabel, { color: colors.textSecondary }]}>refeições</Text>
          </View>
          <View style={[styles.quickInfoCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.quickInfoValue, { color: colors.text }]}>{completedMealIds.size}</Text>
            <Text style={[styles.quickInfoLabel, { color: colors.textSecondary }]}>feitas hoje</Text>
          </View>
          <View style={[styles.quickInfoCard, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.quickInfoValue, { color: colors.text }]}>
              {Math.max((menu?.meals_count ?? 0) - completedMealIds.size, 0)}
            </Text>
            <Text style={[styles.quickInfoLabel, { color: colors.textSecondary }]}>pendentes</Text>
          </View>
        </View>

        <Text style={[styles.sectionTitle, { color: colors.text }]}>Minhas refeições</Text>
        {menu?.meals.length ? (
          menu.meals.map((meal, index) => {
            const defaultExpanded = index === 0;
            const expanded = expandedMeals[meal.id] ?? defaultExpanded;

            return (
              <MealCard
                key={meal.id}
                meal={meal}
                completed={completedMealIds.has(meal.id)}
                completedLog={completedLogsByMealId.get(meal.id) ?? null}
                expanded={expanded}
                saving={savingMealId === meal.id}
                colors={colors}
                onToggle={() =>
                  setExpandedMeals((current) => ({
                    ...current,
                    [meal.id]: !(current[meal.id] ?? defaultExpanded),
                  }))
                }
                onToggleComplete={() => handleToggleMealCompletion(meal)}
                onFeedback={() =>
                  setFeedbackContext({
                    title: 'Feedback da refeição',
                    subtitle: `Explique o ajuste necessário em ${meal.name}.`,
                    contextType: 'meal',
                    contextId: meal.id,
                  })
                }
              />
            );
          })
        ) : (
          <View style={[styles.emptyDietBox, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.centerTitle, { color: colors.text }]}>Dieta sem refeições</Text>
            <Text style={[styles.centerText, { color: colors.textSecondary }]}>
              O cardápio foi enviado, mas ainda não há refeições estruturadas nele.
            </Text>
          </View>
        )}
      </ScrollView>

      <StudentFeedbackSheet
        visible={!!feedbackContext}
        title={feedbackContext?.title ?? 'Feedback'}
        subtitle={feedbackContext?.subtitle ?? 'Conte ao coach o que precisa ser ajustado.'}
        isSending={isSendingFeedback}
        error={error}
        onClose={() => setFeedbackContext(null)}
        onSubmit={handleSubmitFeedback}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  centerState: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    gap: 10,
  },
  centerTitle: {
    fontSize: 20,
    fontWeight: '900',
    textAlign: 'center',
  },
  centerText: {
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },
  emptyIcon: {
    fontSize: 40,
  },
  content: {
    padding: 20,
    paddingBottom: 40,
    gap: 14,
  },
  hero: {
    borderWidth: 1,
    borderRadius: 24,
    padding: 18,
    gap: 16,
  },
  topRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  backButton: {
    width: 42,
    height: 42,
    borderRadius: 21,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backButtonText: {
    fontSize: 34,
    lineHeight: 36,
    fontWeight: '500',
  },
  heroTitleBox: {
    flex: 1,
    minWidth: 0,
  },
  eyebrow: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.7,
    marginBottom: 4,
  },
  heroTitle: {
    fontSize: 23,
    lineHeight: 29,
    fontWeight: '900',
  },
  calorieRow: {
    flexDirection: 'row',
    gap: 18,
    alignItems: 'center',
  },
  circle: {
    width: 88,
    height: 88,
    borderRadius: 44,
    borderWidth: 5,
    alignItems: 'center',
    justifyContent: 'center',
  },
  circlePercent: {
    fontSize: 21,
    fontWeight: '900',
  },
  circleLabel: {
    fontSize: 11,
    fontWeight: '700',
  },
  calorieInfo: {
    flex: 1,
    minWidth: 0,
    gap: 7,
  },
  calorieText: {
    fontSize: 15,
  },
  calorieStrong: {
    fontSize: 25,
    fontWeight: '900',
  },
  macroItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  macroLabel: {
    width: 16,
    fontSize: 13,
    fontWeight: '900',
  },
  macroTrack: {
    flex: 1,
    height: 5,
    borderRadius: 999,
    overflow: 'hidden',
  },
  macroFill: {
    height: '100%',
    borderRadius: 999,
  },
  macroValue: {
    width: 82,
    fontSize: 12,
    fontWeight: '700',
    textAlign: 'right',
  },
  infoBox: {
    borderRadius: 16,
    padding: 14,
    gap: 4,
  },
  infoTitle: {
    fontSize: 13,
    fontWeight: '900',
  },
  infoText: {
    fontSize: 13,
    lineHeight: 19,
  },
  headerActionRow: {
    flexDirection: 'row',
    gap: 10,
  },
  printButton: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  printButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '900',
  },
  feedbackButton: {
    borderWidth: 1,
    borderRadius: 16,
    paddingHorizontal: 18,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  feedbackButtonText: {
    fontSize: 14,
    fontWeight: '900',
  },
  inlineErrorBox: {
    borderWidth: 1,
    borderRadius: 16,
    padding: 12,
  },
  inlineErrorText: {
    fontSize: 13,
    lineHeight: 19,
    fontWeight: '800',
  },
  quickInfoGrid: {
    flexDirection: 'row',
    gap: 10,
  },
  quickInfoCard: {
    flex: 1,
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
    alignItems: 'center',
    gap: 3,
  },
  quickInfoValue: {
    fontSize: 22,
    fontWeight: '900',
  },
  quickInfoLabel: {
    fontSize: 12,
    fontWeight: '700',
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: '900',
    marginTop: 4,
  },
  mealCard: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
  },
  mealHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
  },
  mealTimeBox: {
    width: 58,
    gap: 3,
  },
  mealTime: {
    fontSize: 15,
    fontWeight: '900',
  },
  mealCalories: {
    fontSize: 11,
    fontWeight: '700',
  },
  mealTitleBox: {
    flex: 1,
    minWidth: 0,
  },
  mealName: {
    fontSize: 17,
    lineHeight: 22,
    fontWeight: '900',
  },
  mealPreview: {
    fontSize: 13,
    marginTop: 2,
  },
  mealMacros: {
    fontSize: 13,
    marginTop: 4,
    fontWeight: '700',
  },
  mealCompletedText: {
    fontSize: 12,
    marginTop: 5,
    fontWeight: '900',
  },
  mealActionHint: {
    fontSize: 12,
    marginTop: 4,
    fontWeight: '800',
  },
  toggleText: {
    fontSize: 13,
    marginTop: 6,
    fontWeight: '900',
  },
  checkCircle: {
    width: 30,
    height: 30,
    borderRadius: 15,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
  },
  checkText: {
    fontSize: 16,
    fontWeight: '900',
  },
  foodList: {
    marginTop: 14,
    paddingTop: 14,
    borderTopWidth: 1,
    gap: 13,
  },
  foodRow: {
    flexDirection: 'row',
    gap: 10,
  },
  foodBullet: {
    width: 9,
    height: 9,
    borderRadius: 5,
    marginTop: 6,
  },
  foodTextBox: {
    flex: 1,
    minWidth: 0,
    gap: 3,
  },
  foodName: {
    fontSize: 15,
    fontWeight: '900',
  },
  foodMeta: {
    fontSize: 12,
    lineHeight: 18,
  },
  foodNotes: {
    fontSize: 12,
    lineHeight: 18,
    fontStyle: 'italic',
  },
  emptyMealText: {
    fontSize: 13,
    lineHeight: 19,
  },
  mealNotesBox: {
    borderRadius: 14,
    padding: 12,
    gap: 4,
  },
  mealFeedbackButton: {
    borderWidth: 1,
    borderRadius: 14,
    paddingVertical: 13,
    alignItems: 'center',
  },
  mealFeedbackText: {
    fontSize: 14,
    fontWeight: '900',
  },
  emptyDietBox: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 22,
    alignItems: 'center',
    gap: 8,
  },
  primaryButton: {
    borderRadius: 14,
    paddingHorizontal: 18,
    paddingVertical: 12,
  },
  primaryButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '900',
  },
});
