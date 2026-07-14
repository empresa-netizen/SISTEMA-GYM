import { create } from 'zustand';

import {
  completeDietMeal as completeDietMealRequest,
  completeWorkout as completeWorkoutRequest,
  createStudentLogbook,
  fetchStudentLogbooks,
  fetchStudentPrescriptions,
  logWorkoutActivity as logWorkoutActivityRequest,
  sendStudentFeedback,
  uncompleteDietMeal as uncompleteDietMealRequest,
  uncompleteWorkoutActivity as uncompleteWorkoutActivityRequest,
} from '@/services/prescriptions';
import {
  DietMeal,
  DietPrescription,
  CreateLogbookPayload,
  LogbookEntry,
  StudentFeedbackPayload,
  WorkoutActivity,
  WorkoutActivityLogPayload,
  WorkoutPrescription,
} from '@/types/prescription';

type PrescriptionState = {
  workouts: WorkoutPrescription[];
  diets: DietPrescription[];
  logbooks: LogbookEntry[];
  isLoading: boolean;
  isRefreshing: boolean;
  error: string | null;
  savingActivityId: number | null;
  savingWorkoutId: number | null;
  savingMealId: number | null;
  isSavingLogbook: boolean;
  isSendingFeedback: boolean;
  hasLoaded: boolean;
  fetchAll: () => Promise<void>;
  refresh: () => Promise<void>;
  logActivity: (
    workout: WorkoutPrescription,
    activity: WorkoutActivity,
    payload?: WorkoutActivityLogPayload,
  ) => Promise<void>;
  uncompleteActivity: (workout: WorkoutPrescription, activity: WorkoutActivity) => Promise<void>;
  completeWorkout: (
    workout: WorkoutPrescription,
    payload?: { comment?: string; rating?: number | null; duration_seconds?: number | null },
  ) => Promise<void>;
  completeDietMeal: (prescription: DietPrescription, meal: DietMeal) => Promise<void>;
  uncompleteDietMeal: (prescription: DietPrescription, meal: DietMeal) => Promise<void>;
  createLogbook: (payload: CreateLogbookPayload) => Promise<void>;
  sendFeedback: (payload: StudentFeedbackPayload) => Promise<void>;
  reset: () => void;
};

function replaceWorkout(
  workouts: WorkoutPrescription[],
  updatedWorkout: WorkoutPrescription,
): WorkoutPrescription[] {
  const exists = workouts.some((workout) => workout.id === updatedWorkout.id);

  if (!exists) {
    return [updatedWorkout, ...workouts];
  }

  return workouts.map((workout) => (workout.id === updatedWorkout.id ? updatedWorkout : workout));
}

function appendLogbook(logbooks: LogbookEntry[], logbook: LogbookEntry | null): LogbookEntry[] {
  if (!logbook) {
    return logbooks;
  }

  if (logbooks.some((item) => item.id === logbook.id)) {
    return logbooks;
  }

  return [logbook, ...logbooks];
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

function metadataNumber(metadata: Record<string, unknown> | null, key: string): number | null {
  const value = metadata?.[key];

  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : null;
  }

  if (typeof value === 'string') {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
  }

  return null;
}

function isDietMealCompletionLogbook(
  logbook: LogbookEntry,
  prescriptionId: number,
  mealId: number,
): boolean {
  const source = logbook.metadata?.source;

  return (
    logbook.type === 'DIET' &&
    (source === 'student_diet_meal_complete' || source === 'student_diet_detail_meal') &&
    metadataNumber(logbook.metadata, 'prescription_id') === prescriptionId &&
    metadataNumber(logbook.metadata, 'meal_id') === mealId &&
    new Date(logbook.logged_at).toDateString() === new Date().toDateString()
  );
}

export const usePrescriptionStore = create<PrescriptionState>((set) => ({
  workouts: [],
  diets: [],
  logbooks: [],
  isLoading: false,
  isRefreshing: false,
  error: null,
  savingActivityId: null,
  savingWorkoutId: null,
  savingMealId: null,
  isSavingLogbook: false,
  isSendingFeedback: false,
  hasLoaded: false,

  fetchAll: async () => {
    set({ isLoading: true, error: null });

    try {
      const [prescriptions, logbooks] = await Promise.all([
        fetchStudentPrescriptions(),
        fetchStudentLogbooks(),
      ]);

      set({
        workouts: prescriptions.workouts,
        diets: prescriptions.diets,
        logbooks,
        isLoading: false,
        hasLoaded: true,
      });
    } catch (error) {
      set({
        isLoading: false,
        hasLoaded: true,
        error: error instanceof Error ? error.message : 'Nao foi possivel carregar prescricoes.',
      });
    }
  },

  refresh: async () => {
    set({ isRefreshing: true, error: null });

    try {
      const [prescriptions, logbooks] = await Promise.all([
        fetchStudentPrescriptions(),
        fetchStudentLogbooks(),
      ]);

      set({
        workouts: prescriptions.workouts,
        diets: prescriptions.diets,
        logbooks,
        isRefreshing: false,
        hasLoaded: true,
      });
    } catch (error) {
      set({
        isRefreshing: false,
        error: error instanceof Error ? error.message : 'Nao foi possivel atualizar prescricoes.',
      });
    }
  },

  logActivity: async (workout, activity, payload = {}) => {
    set({ savingActivityId: activity.id, error: null });

    try {
      const updatedWorkout = await logWorkoutActivityRequest(workout.id, activity.id, {
        is_completed: payload.is_completed ?? true,
        sets: payload.sets ?? activity.sets,
        reps: payload.reps ?? activity.reps,
        weight_kg: payload.weight_kg ?? toNumber(activity.weight_kg),
        notes: payload.notes ?? activity.notes,
      });

      set((state) => ({
        workouts: replaceWorkout(state.workouts, updatedWorkout),
        savingActivityId: null,
      }));
    } catch (error) {
      set({
        savingActivityId: null,
        error: error instanceof Error ? error.message : 'Nao foi possivel registrar o exercicio.',
      });
      throw error;
    }
  },

  uncompleteActivity: async (workout, activity) => {
    set({ savingActivityId: activity.id, error: null });

    try {
      const updatedWorkout = await uncompleteWorkoutActivityRequest(workout.id, activity.id);

      set((state) => ({
        workouts: replaceWorkout(state.workouts, updatedWorkout),
        savingActivityId: null,
      }));
    } catch (error) {
      set({
        savingActivityId: null,
        error: error instanceof Error ? error.message : 'Nao foi possivel desmarcar o exercicio.',
      });
      throw error;
    }
  },

  completeWorkout: async (workout, payload) => {
    set({ savingWorkoutId: workout.id, error: null });

    try {
      const result = await completeWorkoutRequest(workout.id, payload);

      set((state) => ({
        workouts: replaceWorkout(state.workouts, result.workout),
        logbooks: appendLogbook(state.logbooks, result.logbook),
        savingWorkoutId: null,
      }));
    } catch (error) {
      set({
        savingWorkoutId: null,
        error: error instanceof Error ? error.message : 'Nao foi possivel concluir o treino.',
      });
      throw error;
    }
  },

  completeDietMeal: async (prescription, meal) => {
    set({ savingMealId: meal.id, error: null });

    try {
      const logbook = await completeDietMealRequest(prescription.id, meal.id);

      set((state) => ({
        logbooks: appendLogbook(state.logbooks, logbook),
        savingMealId: null,
      }));
    } catch (error) {
      set({
        savingMealId: null,
        error: error instanceof Error ? error.message : 'Nao foi possivel concluir a refeicao.',
      });
      throw error;
    }
  },

  uncompleteDietMeal: async (prescription, meal) => {
    set({ savingMealId: meal.id, error: null });

    try {
      await uncompleteDietMealRequest(prescription.id, meal.id);

      set((state) => ({
        logbooks: state.logbooks.filter(
          (logbook) => !isDietMealCompletionLogbook(logbook, prescription.id, meal.id),
        ),
        savingMealId: null,
      }));
    } catch (error) {
      set({
        savingMealId: null,
        error: error instanceof Error ? error.message : 'Nao foi possivel desmarcar a refeicao.',
      });
      throw error;
    }
  },

  createLogbook: async (payload) => {
    set({ isSavingLogbook: true, error: null });

    try {
      const logbook = await createStudentLogbook(payload);

      set((state) => ({
        logbooks: appendLogbook(state.logbooks, logbook),
        isSavingLogbook: false,
      }));
    } catch (error) {
      set({
        isSavingLogbook: false,
        error: error instanceof Error ? error.message : 'Nao foi possivel salvar o diario.',
      });
      throw error;
    }
  },

  sendFeedback: async (payload) => {
    set({ isSendingFeedback: true, error: null });

    try {
      await sendStudentFeedback(payload);

      set({ isSendingFeedback: false });
    } catch (error) {
      set({
        isSendingFeedback: false,
        error: error instanceof Error ? error.message : 'Nao foi possivel enviar o feedback.',
      });
      throw error;
    }
  },

  reset: () => {
    set({
      workouts: [],
      diets: [],
      logbooks: [],
      isLoading: false,
      isRefreshing: false,
      error: null,
      savingActivityId: null,
      savingWorkoutId: null,
      savingMealId: null,
      isSavingLogbook: false,
      isSendingFeedback: false,
      hasLoaded: false,
    });
  },
}));
