export type WorkoutActivityLog = {
  id: number;
  workout_activity_id: number;
  sets: number | null;
  reps: number | null;
  weight_kg: number | null;
  is_completed: boolean;
  notes: string | null;
  logged_at: string | null;
  created_at: string | null;
};

export type WorkoutActivity = {
  id: number;
  workout_id: number;
  exercise_name: string;
  description: string | null;
  sets: number | null;
  reps: number | null;
  duration_minutes: number | null;
  rest_seconds: number | null;
  weight_kg: number | null;
  order: number | null;
  is_completed: boolean;
  notes: string | null;
  details?: string | null;
  logs: WorkoutActivityLog[];
  created_at?: string | null;
  updated_at?: string | null;
};

export type WorkoutPrescription = {
  id: number;
  workout_code: string | null;
  member_id: number;
  trainer_id: number | null;
  name: string;
  description: string | null;
  workout_date: string | null;
  status: 'active' | 'completed' | string;
  notes: string | null;
  activities_total: number;
  activities_completed: number;
  completion_percentage: number;
  activities: WorkoutActivity[];
  created_at: string | null;
  updated_at: string | null;
};

export type DietMacroTotals = {
  calories: number;
  protein: number;
  carbs: number;
  fat: number;
};

export type DietMealFood = {
  id: number;
  diet_food_id: number | null;
  name: string | null;
  food_group: string | null;
  quantity_in_grams: number;
  order: number | null;
  notes: string | null;
  macros: DietMacroTotals;
  catalog_per_100g: (DietMacroTotals & { unit: string | null }) | null;
};

export type DietMeal = {
  id: number;
  name: string;
  time_label: string | null;
  order: number | null;
  notes: string | null;
  macros: DietMacroTotals;
  foods: DietMealFood[];
};

export type DietMenu = {
  id: number;
  name: string;
  status: string;
  meals_count: number;
  total_calories: number | null;
  macros: DietMacroTotals;
  description: string | null;
  meals: DietMeal[];
  created_at: string | null;
};

export type DietPrescription = {
  id: number;
  member_id: number;
  diet_menu_id: number | null;
  title: string;
  notes: string | null;
  status: string;
  delivery_status: string;
  scheduled_at: string | null;
  sent_at: string | null;
  diet_menu: DietMenu | null;
  created_at: string | null;
  updated_at: string | null;
};

export type StudentPrescriptionsResponse = {
  workouts: WorkoutPrescription[];
  diets: DietPrescription[];
};

export type LogbookType = 'TRAINING' | 'DIET' | 'WEIGHT';

export type LogbookEntry = {
  id: number;
  parent_id?: number;
  member_id?: number;
  type: LogbookType;
  title: string;
  logged_at: string;
  rating: number | null;
  numeric_value: number | string | null;
  unit: string | null;
  metadata: Record<string, unknown> | null;
  comment: string | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type WorkoutActivityLogPayload = {
  is_completed?: boolean;
  sets?: number | null;
  reps?: number | null;
  weight_kg?: number | null;
  notes?: string | null;
};

export type CreateLogbookPayload = {
  type: LogbookType;
  title: string;
  logged_at?: string;
  date?: string;
  rating?: number | null;
  numeric_value?: number | null;
  unit?: string | null;
  metadata?: Record<string, unknown> | null;
  comment?: string | null;
};

export type StudentFeedbackPayload = {
  message: string;
  rating?: number | null;
  context_type?: 'workout' | 'diet' | 'meal' | 'exercise' | 'general';
  context_id?: number | null;
};

export type StudentFeedback = {
  id: number;
  member_id: number;
  status: 'pending' | 'viewed' | 'resolved' | string;
  message: string | null;
  photo_path: string | null;
  rating: number | null;
  context_type: StudentFeedbackPayload['context_type'] | null;
  context_id: number | null;
  created_at: string | null;
};
