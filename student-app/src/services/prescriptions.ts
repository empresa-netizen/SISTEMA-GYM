import { getApiErrorMessage, studentApi } from '@/services/api';
import {
  CreateLogbookPayload,
  DietPrescription,
  LogbookEntry,
  StudentFeedback,
  StudentFeedbackPayload,
  StudentPrescriptionsResponse,
  WorkoutActivityLogPayload,
  WorkoutPrescription,
} from '@/types/prescription';

type WorkoutActionResponse = {
  message?: string;
  data?: WorkoutPrescription;
  workout?: WorkoutPrescription;
  logbook?: LogbookEntry | null;
};

type LogbookCreateResponse = {
  message?: string;
  data: LogbookEntry;
};

type FeedbackCreateResponse = {
  message?: string;
  data: StudentFeedback;
};

type DietPrintLinkResponse = {
  url: string;
  expires_at: string;
};

function timestamp(value: string | null | undefined): number {
  if (!value) {
    return 0;
  }

  const parsed = new Date(value).getTime();

  return Number.isFinite(parsed) ? parsed : 0;
}

function sortWorkouts(workouts: WorkoutPrescription[]): WorkoutPrescription[] {
  return [...workouts].sort((left, right) => {
    const leftDone = left.status === 'completed' ? 1 : 0;
    const rightDone = right.status === 'completed' ? 1 : 0;

    if (leftDone !== rightDone) {
      return leftDone - rightDone;
    }

    return timestamp(right.workout_date ?? right.created_at) - timestamp(left.workout_date ?? left.created_at);
  });
}

function sortDiets(diets: DietPrescription[]): DietPrescription[] {
  return [...diets].sort((left, right) => {
    const leftHasMeals = left.diet_menu?.meals?.length ? 1 : 0;
    const rightHasMeals = right.diet_menu?.meals?.length ? 1 : 0;

    if (leftHasMeals !== rightHasMeals) {
      return rightHasMeals - leftHasMeals;
    }

    const leftHasMenu = left.diet_menu ? 1 : 0;
    const rightHasMenu = right.diet_menu ? 1 : 0;

    if (leftHasMenu !== rightHasMenu) {
      return rightHasMenu - leftHasMenu;
    }

    return (
      timestamp(right.sent_at ?? right.scheduled_at ?? right.created_at) -
      timestamp(left.sent_at ?? left.scheduled_at ?? left.created_at)
    );
  });
}

export async function fetchStudentPrescriptions(): Promise<StudentPrescriptionsResponse> {
  const response = await studentApi.get<Partial<StudentPrescriptionsResponse>>('/prescriptions');

  return {
    workouts: sortWorkouts(response.data.workouts ?? []),
    diets: sortDiets(response.data.diets ?? []),
  };
}

export async function fetchStudentLogbooks(): Promise<LogbookEntry[]> {
  const response = await studentApi.get<LogbookEntry[]>('/logbooks');

  return response.data ?? [];
}

export async function logWorkoutActivity(
  workoutId: number,
  activityId: number,
  payload: WorkoutActivityLogPayload,
): Promise<WorkoutPrescription> {
  const response = await studentApi.post<WorkoutActionResponse>(
    `/workouts/${workoutId}/activities/${activityId}/log`,
    payload,
  );
  const updatedWorkout = response.data.workout ?? response.data.data;

  if (!updatedWorkout) {
    throw new Error('A API nao retornou o treino atualizado.');
  }

  return updatedWorkout;
}

export async function uncompleteWorkoutActivity(
  workoutId: number,
  activityId: number,
): Promise<WorkoutPrescription> {
  const response = await studentApi.delete<WorkoutActionResponse>(
    `/workouts/${workoutId}/activities/${activityId}/log`,
  );
  const updatedWorkout = response.data.workout ?? response.data.data;

  if (!updatedWorkout) {
    throw new Error('A API nao retornou o treino atualizado.');
  }

  return updatedWorkout;
}

export async function completeWorkout(
  workoutId: number,
  payload?: { comment?: string; rating?: number | null; duration_seconds?: number | null },
): Promise<{ workout: WorkoutPrescription; logbook: LogbookEntry | null }> {
  const response = await studentApi.post<WorkoutActionResponse>(`/workouts/${workoutId}/complete`, {
    comment: payload?.comment?.trim() || undefined,
    rating: payload?.rating ?? undefined,
    duration_seconds: payload?.duration_seconds ?? undefined,
  });
  const updatedWorkout = response.data.workout ?? response.data.data;

  if (!updatedWorkout) {
    throw new Error('A API nao retornou o treino concluido.');
  }

  return {
    workout: updatedWorkout,
    logbook: response.data.logbook ?? null,
  };
}

export async function createStudentLogbook(payload: CreateLogbookPayload): Promise<LogbookEntry> {
  try {
    const response = await studentApi.post<LogbookCreateResponse>('/logbooks', payload);

    return response.data.data;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}

export async function sendStudentFeedback(payload: StudentFeedbackPayload): Promise<StudentFeedback> {
  try {
    const response = await studentApi.post<FeedbackCreateResponse>('/feedbacks', {
      message: payload.message.trim(),
      rating: payload.rating ?? undefined,
      context_type: payload.context_type ?? 'general',
      context_id: payload.context_id ?? undefined,
    });

    return response.data.data;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}

export async function completeDietMeal(
  prescriptionId: number,
  mealId: number,
  comment?: string,
): Promise<LogbookEntry> {
  try {
    const response = await studentApi.post<LogbookCreateResponse>(
      `/diets/${prescriptionId}/meals/${mealId}/complete`,
      {
        comment: comment?.trim() || undefined,
      },
    );

    return response.data.data;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}

export async function fetchDietPrintLink(prescriptionId: number): Promise<string> {
  try {
    const response = await studentApi.get<DietPrintLinkResponse>(`/diets/${prescriptionId}/print-link`);

    if (!response.data.url) {
      throw new Error('A API nao retornou o link da dieta.');
    }

    return response.data.url;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}

export async function uncompleteDietMeal(
  prescriptionId: number,
  mealId: number,
): Promise<{ deleted: number; meal_id: number; prescription_id: number }> {
  try {
    const response = await studentApi.delete<{ deleted: number; meal_id: number; prescription_id: number }>(
      `/diets/${prescriptionId}/meals/${mealId}/complete`,
    );

    return response.data;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}
