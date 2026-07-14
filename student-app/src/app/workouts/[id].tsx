import { router, useLocalSearchParams } from 'expo-router';
import { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Modal,
  Platform,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { StudentFeedbackSheet } from '@/components/student-feedback-sheet';
import { useTheme } from '@/hooks/use-theme';
import { usePrescriptionStore } from '@/store/usePrescriptionStore';
import { WorkoutActivity, WorkoutPrescription } from '@/types/prescription';

type WorkoutFeedbackContext = {
  title: string;
  subtitle: string;
  contextType: 'workout' | 'exercise';
  contextId: number;
};

function asRouteId(value: string | string[] | undefined): number | null {
  const rawValue = Array.isArray(value) ? value[0] : value;
  const parsed = Number(rawValue);

  return Number.isFinite(parsed) ? parsed : null;
}

function formatDuration(seconds: number): string {
  const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
  const remainingSeconds = (seconds % 60).toString().padStart(2, '0');

  return `${minutes}:${remainingSeconds}`;
}

function formatRestDuration(seconds: number): string {
  if (seconds < 60) {
    return `${seconds}s`;
  }

  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;

  return remainingSeconds > 0 ? `${minutes}min ${remainingSeconds}s` : `${minutes}min`;
}

function activityPrescription(activity: WorkoutActivity): string {
  const pieces = [
    activity.sets ? `${activity.sets} séries` : null,
    activity.reps ? `${activity.reps} reps` : null,
    activity.weight_kg ? `${activity.weight_kg} kg` : null,
    activity.rest_seconds ? `${activity.rest_seconds}s descanso` : null,
    activity.duration_minutes ? `${activity.duration_minutes} min` : null,
  ].filter(Boolean);

  return pieces.join(' · ') || 'Prescrição a definir';
}

function formatActivityLogTime(value: string | null): string {
  if (!value) {
    return 'agora';
  }

  return new Date(value).toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
  });
}

function activityLogSummary(log: WorkoutActivity['logs'][number], index: number): string {
  const pieces = [
    log.sets ? `${log.sets} série${log.sets === 1 ? '' : 's'}` : null,
    log.reps ? `${log.reps} reps` : null,
    log.weight_kg ? `${log.weight_kg} kg` : null,
  ].filter(Boolean);

  return pieces.join(' · ') || `Registro ${index + 1}`;
}

function parseIntegerInput(value: string): number | null {
  const parsed = Number.parseInt(value.replace(',', '.'), 10);

  return Number.isFinite(parsed) ? parsed : null;
}

function parseFloatInput(value: string): number | null {
  const parsed = Number.parseFloat(value.replace(',', '.'));

  return Number.isFinite(parsed) ? parsed : null;
}

function WorkoutHeader({
  workout,
  elapsedSeconds,
  colors,
  onOpenFeedback,
}: {
  workout: WorkoutPrescription;
  elapsedSeconds: number;
  colors: ReturnType<typeof useTheme>;
  onOpenFeedback: () => void;
}) {
  const progress = Math.min(100, Math.max(0, workout.completion_percentage));

  return (
    <View style={[styles.hero, { backgroundColor: colors.surface, borderColor: colors.border }]}>
      <View style={styles.topRow}>
        <TouchableOpacity
          style={[styles.backButton, { backgroundColor: colors.backgroundElement }]}
          onPress={() => (router.canGoBack() ? router.back() : router.replace('/(tabs)/workouts'))}>
          <Text style={[styles.backButtonText, { color: colors.text }]}>‹</Text>
        </TouchableOpacity>
        <View style={styles.heroTitleBox}>
          <Text style={[styles.eyebrow, { color: colors.tint }]}>TREINO EM EXECUÇÃO</Text>
          <Text style={[styles.heroTitle, { color: colors.text }]} numberOfLines={2}>
            {workout.name}
          </Text>
        </View>
      </View>

      <View style={styles.heroStats}>
        <View style={[styles.statBox, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.statValue, { color: colors.text }]}>{workout.activities_total}</Text>
          <Text style={[styles.statLabel, { color: colors.textSecondary }]}>exercícios</Text>
        </View>
        <View style={[styles.statBox, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.statValue, { color: colors.text }]}>{formatDuration(elapsedSeconds)}</Text>
          <Text style={[styles.statLabel, { color: colors.textSecondary }]}>tempo</Text>
        </View>
        <View style={[styles.statBox, { backgroundColor: colors.backgroundElement }]}>
          <Text style={[styles.statValue, { color: colors.text }]}>{progress}%</Text>
          <Text style={[styles.statLabel, { color: colors.textSecondary }]}>concluído</Text>
        </View>
      </View>

      <View style={[styles.progressTrack, { backgroundColor: colors.backgroundElement }]}>
        <View style={[styles.progressFill, { width: `${progress}%`, backgroundColor: colors.tint }]} />
      </View>

      {workout.description || workout.notes ? (
        <Text style={[styles.heroNotes, { color: colors.textSecondary }]}>
          {workout.description || workout.notes}
        </Text>
      ) : null}

      <TouchableOpacity
        activeOpacity={0.85}
        style={[styles.heroFeedbackButton, { borderColor: colors.border, backgroundColor: colors.backgroundElement }]}
        onPress={onOpenFeedback}>
        <Text style={[styles.heroFeedbackText, { color: colors.text }]}>Enviar feedback ao coach</Text>
      </TouchableOpacity>
    </View>
  );
}

function ActivityCard({
  activity,
  index,
  expanded,
  saving,
  colors,
  onToggle,
  onAction,
  onFeedback,
  onStartRest,
}: {
  activity: WorkoutActivity;
  index: number;
  expanded: boolean;
  saving: boolean;
  colors: ReturnType<typeof useTheme>;
  onToggle: () => void;
  onAction: () => void;
  onFeedback: () => void;
  onStartRest: () => void;
}) {
  return (
    <TouchableOpacity
      activeOpacity={0.84}
      onPress={onToggle}
      style={[
        styles.activityCard,
        { backgroundColor: colors.surface, borderColor: expanded ? colors.tint : colors.border },
      ]}>
      <View style={styles.activityHeader}>
        <View
          style={[
            styles.activityNumber,
            { backgroundColor: activity.is_completed ? colors.tint : colors.backgroundElement },
          ]}>
          <Text style={[styles.activityNumberText, { color: activity.is_completed ? '#FFFFFF' : colors.text }]}>
            {activity.is_completed ? '✓' : String(index + 1).padStart(2, '0')}
          </Text>
        </View>
        <View style={styles.activityTitleBox}>
          <Text style={[styles.activityName, { color: colors.text }]} numberOfLines={2}>
            {activity.exercise_name}
          </Text>
          <Text style={[styles.activitySub, { color: colors.textSecondary }]} numberOfLines={1}>
            {activityPrescription(activity)}
          </Text>
        </View>
      </View>

      {expanded ? (
        <View style={styles.activityExpanded}>
          {activity.description || activity.notes ? (
            <Text style={[styles.activityDescription, { color: colors.textSecondary }]}>
              {activity.description || activity.notes}
            </Text>
          ) : (
            <Text style={[styles.activityDescription, { color: colors.textSecondary }]}>
              Registre o exercício quando concluir a execução prescrita.
            </Text>
          )}

          <View style={[styles.prescriptionBox, { backgroundColor: colors.backgroundElement }]}>
            <Text style={[styles.prescriptionLabel, { color: colors.textSecondary }]}>PRESCRIÇÃO</Text>
            <Text style={[styles.prescriptionValue, { color: colors.text }]}>
              {activity.details || activityPrescription(activity)}
            </Text>
          </View>

          {activity.logs.length > 0 ? (
            <View style={styles.activityLogList}>
              <Text style={[styles.prescriptionLabel, { color: colors.textSecondary }]}>
                SÉRIES REGISTRADAS
              </Text>
              {activity.logs.map((log, logIndex) => (
                <View
                  key={log.id}
                  style={[styles.activityLogRow, { backgroundColor: colors.backgroundElement }]}>
                  <View style={styles.activityLogTextBox}>
                    <Text style={[styles.activityLogTitle, { color: colors.text }]}>
                      {activityLogSummary(log, logIndex)}
                    </Text>
                    {log.notes ? (
                      <Text style={[styles.activityLogNotes, { color: colors.textSecondary }]} numberOfLines={2}>
                        {log.notes}
                      </Text>
                    ) : null}
                  </View>
                  <View style={styles.activityLogMetaBox}>
                    <Text style={[styles.activityLogTime, { color: colors.textSecondary }]}>
                      {formatActivityLogTime(log.logged_at)}
                    </Text>
                    <Text style={[styles.activityLogStatus, { color: log.is_completed ? colors.tint : colors.textMuted }]}>
                      {log.is_completed ? 'concluído' : 'série'}
                    </Text>
                  </View>
                </View>
              ))}
            </View>
          ) : (
            <Text style={[styles.noLogsText, { color: colors.textSecondary }]}>
              Nenhuma série registrada ainda.
            </Text>
          )}

          <TouchableOpacity
            disabled={saving}
            style={[
              styles.completeExerciseButton,
              { backgroundColor: activity.is_completed ? colors.backgroundElement : colors.tint },
            ]}
            onPress={onAction}>
            <Text style={[styles.completeExerciseText, { color: activity.is_completed ? colors.text : '#FFFFFF' }]}>
              {saving ? 'Salvando...' : activity.is_completed ? 'Desmarcar exercício' : 'Registrar exercício'}
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            disabled={activity.is_completed}
            style={[styles.restButton, { borderColor: colors.border }]}
            onPress={onStartRest}>
            <Text style={[styles.restButtonText, { color: colors.text }]}>
              Iniciar descanso · {formatRestDuration(activity.rest_seconds ?? 60)}
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.exerciseFeedbackButton, { borderColor: colors.border }]}
            onPress={onFeedback}>
            <Text style={[styles.exerciseFeedbackText, { color: colors.text }]}>Reportar ajuste</Text>
          </TouchableOpacity>
        </View>
      ) : null}
    </TouchableOpacity>
  );
}

export default function WorkoutDetailScreen() {
  const colors = useTheme();
  const { id } = useLocalSearchParams<{ id?: string }>();
  const routeId = asRouteId(id);
  const workouts = usePrescriptionStore((state) => state.workouts);
  const isLoading = usePrescriptionStore((state) => state.isLoading);
  const hasLoaded = usePrescriptionStore((state) => state.hasLoaded);
  const error = usePrescriptionStore((state) => state.error);
  const savingActivityId = usePrescriptionStore((state) => state.savingActivityId);
  const savingWorkoutId = usePrescriptionStore((state) => state.savingWorkoutId);
  const isRefreshing = usePrescriptionStore((state) => state.isRefreshing);
  const fetchAll = usePrescriptionStore((state) => state.fetchAll);
  const refresh = usePrescriptionStore((state) => state.refresh);
  const logActivity = usePrescriptionStore((state) => state.logActivity);
  const uncompleteActivity = usePrescriptionStore((state) => state.uncompleteActivity);
  const completeWorkout = usePrescriptionStore((state) => state.completeWorkout);
  const sendFeedback = usePrescriptionStore((state) => state.sendFeedback);
  const isSendingFeedback = usePrescriptionStore((state) => state.isSendingFeedback);
  const [expandedActivityId, setExpandedActivityId] = useState<number | null | undefined>(undefined);
  const [elapsedSeconds, setElapsedSeconds] = useState(0);
  const [finishVisible, setFinishVisible] = useState(false);
  const [finishComment, setFinishComment] = useState('');
  const [finishRating, setFinishRating] = useState<number | null>(null);
  const [restSecondsRemaining, setRestSecondsRemaining] = useState<number | null>(null);
  const [activeRestExerciseName, setActiveRestExerciseName] = useState<string | null>(null);
  const [selectedActivity, setSelectedActivity] = useState<WorkoutActivity | null>(null);
  const [feedbackContext, setFeedbackContext] = useState<WorkoutFeedbackContext | null>(null);
  const [activityForm, setActivityForm] = useState({
    sets: '',
    reps: '',
    weight: '',
    notes: '',
  });

  const workout = useMemo(
    () => workouts.find((candidate) => candidate.id === routeId) ?? null,
    [routeId, workouts],
  );

  useEffect(() => {
    if (!hasLoaded) {
      fetchAll();
    }
  }, [fetchAll, hasLoaded]);

  useEffect(() => {
    const interval = setInterval(() => setElapsedSeconds((current) => current + 1), 1000);

    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (restSecondsRemaining === null) {
      return undefined;
    }

    const interval = setInterval(() => {
      setRestSecondsRemaining((current) => {
        if (current === null) {
          return null;
        }

        if (current <= 1) {
          setActiveRestExerciseName(null);

          return null;
        }

        return current - 1;
      });
    }, 1000);

    return () => clearInterval(interval);
  }, [restSecondsRemaining]);

  function openActivityLog(activity: WorkoutActivity): void {
    setSelectedActivity(activity);
    setActivityForm({
      sets: activity.sets ? String(activity.sets) : '',
      reps: activity.reps ? String(activity.reps) : '',
      weight: activity.weight_kg ? String(activity.weight_kg) : '',
      notes: activity.notes ?? '',
    });
  }

  async function handleActivityAction(activity: WorkoutActivity): Promise<void> {
    if (!workout) {
      return;
    }

    if (!activity.is_completed) {
      openActivityLog(activity);

      return;
    }

    try {
      await uncompleteActivity(workout, activity);
    } catch {
      return;
    }
  }

  async function handleSaveActivity(shouldComplete: boolean): Promise<void> {
    if (!workout || !selectedActivity || selectedActivity.is_completed) {
      return;
    }

    try {
      await logActivity(workout, selectedActivity, {
        is_completed: shouldComplete,
        sets: parseIntegerInput(activityForm.sets),
        reps: parseIntegerInput(activityForm.reps),
        weight_kg: parseFloatInput(activityForm.weight),
        notes: activityForm.notes.trim() || null,
      });
      setSelectedActivity(null);
    } catch {
      return;
    }
  }

  async function handleFinishWorkout(): Promise<void> {
    if (!workout) {
      return;
    }

    try {
      await completeWorkout(workout, {
        comment: finishComment,
        rating: finishRating,
        duration_seconds: elapsedSeconds,
      });
      setFinishVisible(false);
    } catch {
      return;
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

  function startRest(activity: WorkoutActivity): void {
    setActiveRestExerciseName(activity.exercise_name);
    setRestSecondsRemaining(activity.rest_seconds ?? 60);
  }

  function stopRest(): void {
    setRestSecondsRemaining(null);
    setActiveRestExerciseName(null);
  }

  if (isLoading || !hasLoaded) {
    return (
      <SafeAreaView style={[styles.safeArea, styles.centerState, { backgroundColor: colors.background }]}>
        <ActivityIndicator color={colors.tint} />
        <Text style={[styles.centerText, { color: colors.textSecondary }]}>Carregando treino...</Text>
      </SafeAreaView>
    );
  }

  if (!workout) {
    return (
      <SafeAreaView style={[styles.safeArea, styles.centerState, { backgroundColor: colors.background }]}>
        <Text style={styles.emptyIcon}>🏋️</Text>
        <Text style={[styles.centerTitle, { color: colors.text }]}>Treino não encontrado</Text>
        <Text style={[styles.centerText, { color: colors.textSecondary }]}>
          {error || 'Atualize a lista de treinos e tente novamente.'}
        </Text>
        <TouchableOpacity style={[styles.primaryButton, { backgroundColor: colors.tint }]} onPress={() => router.back()}>
          <Text style={styles.primaryButtonText}>Voltar</Text>
        </TouchableOpacity>
      </SafeAreaView>
    );
  }

  const allDone = workout.activities_total > 0 && workout.activities_completed >= workout.activities_total;
  const visibleExpandedActivityId =
    expandedActivityId === undefined ? workout.activities[0]?.id ?? null : expandedActivityId;

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={<RefreshControl refreshing={isRefreshing} onRefresh={refresh} tintColor={colors.tint} />}
        showsVerticalScrollIndicator={false}>
        <WorkoutHeader
          workout={workout}
          elapsedSeconds={elapsedSeconds}
          colors={colors}
          onOpenFeedback={() =>
            setFeedbackContext({
              title: 'Feedback do treino',
              subtitle: 'Conte se sentiu dor, dificuldade, carga inadequada ou qualquer ajuste necessário.',
              contextType: 'workout',
              contextId: workout.id,
            })
          }
        />

        {error ? (
          <View style={[styles.inlineErrorBox, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
            <Text style={[styles.inlineErrorText, { color: colors.text }]}>{error}</Text>
          </View>
        ) : null}

        <Text style={[styles.sectionTitle, { color: colors.text }]}>Exercícios</Text>
        {workout.activities.length > 0 ? (
          workout.activities.map((activity, index) => (
            <ActivityCard
              key={activity.id}
              activity={activity}
              index={index}
              expanded={visibleExpandedActivityId === activity.id}
              saving={savingActivityId === activity.id}
              colors={colors}
              onToggle={() =>
                setExpandedActivityId(visibleExpandedActivityId === activity.id ? null : activity.id)
              }
              onAction={() => handleActivityAction(activity)}
              onFeedback={() =>
                setFeedbackContext({
                  title: 'Feedback do exercício',
                  subtitle: `Explique o ajuste necessário em ${activity.exercise_name}.`,
                  contextType: 'exercise',
                  contextId: activity.id,
                })
              }
              onStartRest={() => startRest(activity)}
            />
          ))
        ) : (
          <View style={[styles.emptyWorkoutBox, { backgroundColor: colors.surface, borderColor: colors.border }]}>
            <Text style={[styles.centerTitle, { color: colors.text }]}>Treino sem exercícios</Text>
            <Text style={[styles.centerText, { color: colors.textSecondary }]}>
              O profissional enviou o treino, mas ainda não há exercícios estruturados nele.
            </Text>
          </View>
        )}
      </ScrollView>

      <View style={[styles.footer, { backgroundColor: colors.surface, borderTopColor: colors.border }]}>
        {restSecondsRemaining !== null ? (
          <View style={[styles.restBanner, { backgroundColor: colors.backgroundElement, borderColor: colors.border }]}>
            <View style={styles.restBannerTextBox}>
              <Text style={[styles.restBannerLabel, { color: colors.textSecondary }]}>Descanso</Text>
              <Text style={[styles.restBannerTitle, { color: colors.text }]} numberOfLines={1}>
                {activeRestExerciseName ?? 'Exercício'}
              </Text>
            </View>
            <Text style={[styles.restBannerTime, { color: colors.tint }]}>
              {formatDuration(restSecondsRemaining)}
            </Text>
            <TouchableOpacity
              accessibilityRole="button"
              style={[styles.restStopButton, { borderColor: colors.border }]}
              onPress={stopRest}>
              <Text style={[styles.restStopText, { color: colors.text }]}>Parar</Text>
            </TouchableOpacity>
          </View>
        ) : null}
        <View style={styles.footerActionRow}>
          <View>
            <Text style={[styles.footerLabel, { color: colors.textSecondary }]}>Sessão</Text>
            <Text style={[styles.footerTime, { color: colors.text }]}>{formatDuration(elapsedSeconds)}</Text>
          </View>
          <TouchableOpacity
            disabled={savingWorkoutId === workout.id}
            style={[styles.finishButton, { backgroundColor: allDone ? colors.tint : colors.backgroundElement }]}
            onPress={() => setFinishVisible(true)}>
            <Text style={[styles.finishButtonText, { color: allDone ? '#FFFFFF' : colors.text }]}>
              {savingWorkoutId === workout.id ? 'Finalizando...' : allDone ? 'Finalizar treino' : 'Finalizar mesmo assim'}
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      <Modal
        visible={!!selectedActivity}
        transparent
        animationType="slide"
        onRequestClose={() => setSelectedActivity(null)}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.modalOverlay}>
          <TouchableOpacity style={styles.modalBackdrop} activeOpacity={1} onPress={() => setSelectedActivity(null)} />
          <View style={[styles.sheet, { backgroundColor: colors.surface }]}>
            <View style={[styles.sheetHandle, { backgroundColor: colors.textSecondary }]} />
            <Text style={[styles.sheetTitle, { color: colors.text }]}>Registrar exercício</Text>
            <Text style={[styles.sheetSubtitle, { color: colors.textSecondary }]} numberOfLines={2}>
              {selectedActivity?.exercise_name}
            </Text>

            <View style={styles.inputGrid}>
              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>Séries</Text>
                <TextInput
                  value={activityForm.sets}
                  onChangeText={(sets) => setActivityForm((current) => ({ ...current, sets }))}
                  keyboardType="number-pad"
                  placeholder="4"
                  placeholderTextColor={colors.textMuted}
                  style={[
                    styles.compactInput,
                    { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
                  ]}
                />
              </View>
              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>Reps</Text>
                <TextInput
                  value={activityForm.reps}
                  onChangeText={(reps) => setActivityForm((current) => ({ ...current, reps }))}
                  keyboardType="number-pad"
                  placeholder="10"
                  placeholderTextColor={colors.textMuted}
                  style={[
                    styles.compactInput,
                    { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
                  ]}
                />
              </View>
              <View style={styles.inputGroup}>
                <Text style={[styles.inputLabel, { color: colors.textSecondary }]}>Carga kg</Text>
                <TextInput
                  value={activityForm.weight}
                  onChangeText={(weight) => setActivityForm((current) => ({ ...current, weight }))}
                  keyboardType="decimal-pad"
                  placeholder="32.5"
                  placeholderTextColor={colors.textMuted}
                  style={[
                    styles.compactInput,
                    { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
                  ]}
                />
              </View>
            </View>

            <TextInput
              value={activityForm.notes}
              onChangeText={(notes) => setActivityForm((current) => ({ ...current, notes }))}
              placeholder="Nota opcional para o coach"
              placeholderTextColor={colors.textMuted}
              multiline
              style={[
                styles.commentInput,
                { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
              ]}
            />
            {error ? <Text style={[styles.sheetErrorText, { color: '#EF4444' }]}>{error}</Text> : null}

            <View style={styles.sheetActionRow}>
              <TouchableOpacity
                disabled={savingActivityId === selectedActivity?.id}
                style={[styles.secondarySheetButton, { borderColor: colors.border }]}
                onPress={() => handleSaveActivity(false)}>
                <Text style={[styles.secondarySheetButtonText, { color: colors.text }]}>
                  {savingActivityId === selectedActivity?.id ? 'Salvando...' : 'Salvar série'}
                </Text>
              </TouchableOpacity>
              <TouchableOpacity
                disabled={savingActivityId === selectedActivity?.id}
                style={[styles.sheetButton, styles.sheetButtonInline, { backgroundColor: colors.tint }]}
                onPress={() => handleSaveActivity(true)}>
                <Text style={styles.sheetButtonText}>
                  {savingActivityId === selectedActivity?.id ? 'Salvando...' : 'Concluir'}
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>

      <Modal visible={finishVisible} transparent animationType="slide" onRequestClose={() => setFinishVisible(false)}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          style={styles.modalOverlay}>
          <TouchableOpacity style={styles.modalBackdrop} activeOpacity={1} onPress={() => setFinishVisible(false)} />
          <View style={[styles.sheet, { backgroundColor: colors.surface }]}>
            <View style={[styles.sheetHandle, { backgroundColor: colors.textSecondary }]} />
            <Text style={[styles.sheetTitle, { color: colors.text }]}>Finalizar treino</Text>
            <Text style={[styles.sheetSubtitle, { color: colors.textSecondary }]}>
              Avalie a sessão e envie um comentário opcional para o seu coach.
            </Text>
            <View style={styles.ratingRow}>
              {[1, 2, 3, 4, 5].map((rating) => (
                <TouchableOpacity
                  key={rating}
                  accessibilityRole="button"
                  accessibilityLabel={`Avaliar treino com ${rating} estrela${rating === 1 ? '' : 's'}`}
                  style={styles.ratingButton}
                  onPress={() => setFinishRating(rating)}>
                  <Text
                    style={[
                      styles.ratingStar,
                      { color: finishRating && rating <= finishRating ? '#F59E0B' : colors.textMuted },
                    ]}>
                    ★
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
            <TextInput
              value={finishComment}
              onChangeText={setFinishComment}
              placeholder="Ex: treino pesado, senti joelho no agachamento..."
              placeholderTextColor={colors.textMuted}
              multiline
              style={[
                styles.commentInput,
                { backgroundColor: colors.backgroundElement, color: colors.text, borderColor: colors.border },
              ]}
            />
            {error ? <Text style={[styles.sheetErrorText, { color: '#EF4444' }]}>{error}</Text> : null}
            <TouchableOpacity
              disabled={savingWorkoutId === workout.id}
              style={[styles.sheetButton, { backgroundColor: colors.tint }]}
              onPress={handleFinishWorkout}>
              <Text style={styles.sheetButtonText}>
                {savingWorkoutId === workout.id ? 'Enviando...' : 'Concluir treino'}
              </Text>
            </TouchableOpacity>
          </View>
        </KeyboardAvoidingView>
      </Modal>

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
    paddingBottom: 120,
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
  heroStats: {
    flexDirection: 'row',
    gap: 10,
  },
  statBox: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 12,
    alignItems: 'center',
    gap: 2,
  },
  statValue: {
    fontSize: 17,
    fontWeight: '900',
  },
  statLabel: {
    fontSize: 11,
    fontWeight: '700',
  },
  progressTrack: {
    height: 8,
    borderRadius: 999,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 999,
  },
  heroNotes: {
    fontSize: 14,
    lineHeight: 21,
  },
  heroFeedbackButton: {
    borderWidth: 1,
    borderRadius: 16,
    paddingVertical: 14,
    alignItems: 'center',
  },
  heroFeedbackText: {
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
  sectionTitle: {
    fontSize: 20,
    fontWeight: '900',
    marginTop: 6,
  },
  activityCard: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 16,
    gap: 12,
  },
  activityHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  activityNumber: {
    width: 44,
    height: 44,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  activityNumberText: {
    fontSize: 14,
    fontWeight: '900',
  },
  activityTitleBox: {
    flex: 1,
    minWidth: 0,
  },
  activityName: {
    fontSize: 17,
    lineHeight: 22,
    fontWeight: '900',
  },
  activitySub: {
    fontSize: 13,
    marginTop: 3,
    fontWeight: '600',
  },
  activityExpanded: {
    gap: 12,
  },
  activityDescription: {
    fontSize: 14,
    lineHeight: 21,
  },
  prescriptionBox: {
    borderRadius: 14,
    padding: 13,
    gap: 4,
  },
  prescriptionLabel: {
    fontSize: 11,
    fontWeight: '900',
    letterSpacing: 0.7,
  },
  prescriptionValue: {
    fontSize: 15,
    fontWeight: '800',
  },
  activityLogList: {
    gap: 8,
  },
  activityLogRow: {
    borderRadius: 14,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
  },
  activityLogTextBox: {
    flex: 1,
    minWidth: 0,
    gap: 3,
  },
  activityLogTitle: {
    fontSize: 14,
    fontWeight: '900',
  },
  activityLogNotes: {
    fontSize: 12,
    lineHeight: 17,
  },
  activityLogMetaBox: {
    alignItems: 'flex-end',
    gap: 3,
  },
  activityLogTime: {
    fontSize: 12,
    fontWeight: '700',
  },
  activityLogStatus: {
    fontSize: 11,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  noLogsText: {
    fontSize: 13,
    lineHeight: 19,
  },
  completeExerciseButton: {
    borderRadius: 14,
    paddingVertical: 14,
    alignItems: 'center',
  },
  completeExerciseText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '900',
  },
  restButton: {
    borderWidth: 1,
    borderRadius: 14,
    paddingVertical: 13,
    alignItems: 'center',
  },
  restButtonText: {
    fontSize: 14,
    fontWeight: '900',
  },
  exerciseFeedbackButton: {
    borderWidth: 1,
    borderRadius: 14,
    paddingVertical: 13,
    alignItems: 'center',
  },
  exerciseFeedbackText: {
    fontSize: 14,
    fontWeight: '900',
  },
  emptyWorkoutBox: {
    borderWidth: 1,
    borderRadius: 18,
    padding: 22,
    alignItems: 'center',
    gap: 8,
  },
  footer: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    borderTopWidth: 1,
    paddingHorizontal: 20,
    paddingTop: 14,
    paddingBottom: 22,
    gap: 12,
  },
  footerActionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 14,
  },
  footerLabel: {
    fontSize: 12,
    fontWeight: '700',
  },
  footerTime: {
    fontSize: 22,
    fontWeight: '900',
  },
  finishButton: {
    flex: 1,
    borderRadius: 16,
    paddingVertical: 15,
    alignItems: 'center',
  },
  finishButtonText: {
    fontSize: 15,
    fontWeight: '900',
  },
  restBanner: {
    borderWidth: 1,
    borderRadius: 16,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  restBannerTextBox: {
    flex: 1,
    minWidth: 0,
  },
  restBannerLabel: {
    fontSize: 11,
    fontWeight: '900',
    textTransform: 'uppercase',
  },
  restBannerTitle: {
    fontSize: 14,
    fontWeight: '900',
    marginTop: 2,
  },
  restBannerTime: {
    fontSize: 22,
    fontWeight: '900',
  },
  restStopButton: {
    borderWidth: 1,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  restStopText: {
    fontSize: 12,
    fontWeight: '900',
  },
  modalOverlay: {
    flex: 1,
    justifyContent: 'flex-end',
    backgroundColor: 'rgba(0,0,0,0.45)',
  },
  modalBackdrop: {
    position: 'absolute',
    top: 0,
    right: 0,
    bottom: 0,
    left: 0,
  },
  sheet: {
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    padding: 22,
    gap: 12,
  },
  sheetHandle: {
    width: 42,
    height: 4,
    borderRadius: 999,
    alignSelf: 'center',
    marginBottom: 4,
  },
  sheetTitle: {
    fontSize: 22,
    fontWeight: '900',
    textAlign: 'center',
  },
  sheetSubtitle: {
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  sheetErrorText: {
    fontSize: 13,
    lineHeight: 19,
    fontWeight: '800',
    textAlign: 'center',
  },
  commentInput: {
    minHeight: 104,
    borderWidth: 1,
    borderRadius: 16,
    padding: 14,
    fontSize: 15,
    lineHeight: 21,
    textAlignVertical: 'top',
  },
  inputGrid: {
    flexDirection: 'row',
    gap: 10,
  },
  inputGroup: {
    flex: 1,
    gap: 6,
  },
  inputLabel: {
    fontSize: 12,
    fontWeight: '800',
  },
  compactInput: {
    borderWidth: 1,
    borderRadius: 14,
    paddingHorizontal: 12,
    paddingVertical: 12,
    fontSize: 16,
    fontWeight: '800',
  },
  ratingRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 4,
  },
  ratingButton: {
    padding: 4,
  },
  ratingStar: {
    fontSize: 38,
    lineHeight: 42,
    fontWeight: '900',
  },
  sheetButton: {
    borderRadius: 16,
    paddingVertical: 15,
    alignItems: 'center',
  },
  sheetButtonInline: {
    flex: 1,
  },
  sheetActionRow: {
    flexDirection: 'row',
    gap: 10,
  },
  secondarySheetButton: {
    flex: 1,
    borderRadius: 16,
    borderWidth: 1,
    paddingVertical: 15,
    alignItems: 'center',
  },
  secondarySheetButtonText: {
    fontSize: 15,
    fontWeight: '900',
  },
  sheetButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '900',
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
