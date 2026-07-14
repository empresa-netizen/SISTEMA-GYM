import { api, studentApi } from '@/services/api';
import { fetchStudentLogbooks, fetchStudentPrescriptions } from '@/services/prescriptions';
import { AuthSessionType } from '@/types/auth';
import { DashboardPayload } from '@/types/dashboard';

export async function fetchDashboard(sessionType?: AuthSessionType): Promise<DashboardPayload> {
  if (sessionType !== 'student') {
    const response = await api.get<{ data: DashboardPayload }>('/dashboard');

    return response.data.data;
  }

  const [prescriptions, logbooks, engagement, feed] = await Promise.all([
    fetchStudentPrescriptions(),
    fetchStudentLogbooks(),
    studentApi.get<{
      logbooks?: number;
      photos?: number;
      feedbacks?: number;
    }>('/engagement'),
    studentApi.get<DashboardPayload['recent']['feed']>('/feed'),
  ]);

  return {
    kpis: {
      workouts_total: prescriptions.workouts.length,
      diets_total: prescriptions.diets.length,
      logbooks: engagement.data.logbooks ?? 0,
      photos: engagement.data.photos ?? 0,
      feedbacks: engagement.data.feedbacks ?? 0,
    },
    recent: {
      feed: feed.data ?? [],
      workouts: prescriptions.workouts.slice(0, 3),
      diets: prescriptions.diets.slice(0, 3),
      logbooks,
    },
    cached_until: null,
  };
}
