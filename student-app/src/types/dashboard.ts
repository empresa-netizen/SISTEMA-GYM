import { DietPrescription, LogbookEntry, WorkoutPrescription } from '@/types/prescription';

export type DashboardKpis = {
  members_total?: number;
  members_active?: number;
  events_upcoming?: number;
  conversations_unread?: number;
  feedback_pending?: number;
  invoices_open?: number;
  revenue_month?: number;
  workouts_total?: number;
  diets_total?: number;
  logbooks?: number;
  photos?: number;
  feedbacks?: number;
};

export type DashboardFeedItem = {
  id: string | number;
  title?: string | null;
  body?: string | null;
  content?: string | null;
  created_at?: string | null;
};

export type DashboardPayload = {
  kpis: DashboardKpis;
  recent: {
    feed: DashboardFeedItem[];
    workouts?: WorkoutPrescription[];
    diets?: DietPrescription[];
    logbooks?: LogbookEntry[];
    payments?: unknown[];
    events?: unknown[];
  };
  cached_until?: string | null;
};
