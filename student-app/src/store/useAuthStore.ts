import { router } from 'expo-router';
import { create } from 'zustand';

import { authMode } from '@/config/env';
import { api, getApiErrorMessage, isUnauthorized, studentApi } from '@/services/api';
import { disconnectEcho } from '@/services/realtime';
import {
  clearAuthSession,
  getStoredSession,
  saveAuthSession,
} from '@/services/token-storage';
import { useChatStore } from '@/store/useChatStore';
import { usePrescriptionStore } from '@/store/usePrescriptionStore';
import { AuthSession, AuthSessionType, AuthUser, LoginPayload, LoginResponse } from '@/types/auth';

type AuthState = {
  user: AuthUser | null;
  session: AuthSession | null;
  isHydrating: boolean;
  isLoggingIn: boolean;
  error: string | null;
  hydrate: () => Promise<void>;
  login: (payload: LoginPayload) => Promise<void>;
  logout: () => Promise<void>;
};

function normalizeUser(user: AuthUser | undefined): AuthUser {
  return {
    id: String(user?.id ?? ''),
    name: user?.name ?? 'Aluno',
    email: user?.email ?? null,
    image: user?.image ?? null,
    role: user?.role ?? null,
    phone: user?.phone ?? null,
    status: user?.status ?? null,
    coachName: user?.coachName ?? null,
  };
}

function buildSession(response: LoginResponse, type: AuthSessionType): {
  session: AuthSession;
  user: AuthUser;
} {
  const accessToken = response.access_token ?? response.token;
  const user = response.user ?? response.client ?? response.data;
  const sessionType =
    response.session_type === 'student' || response.type === 'student' || response.client
      ? 'student'
      : type;

  if (!accessToken) {
    throw new Error('Token de acesso ausente na resposta da API.');
  }

  return {
    session: {
      accessToken,
      tokenType: 'Bearer',
      type: sessionType,
    },
    user: normalizeUser(user),
  };
}

async function loginWithV1(payload: LoginPayload): Promise<{
  session: AuthSession;
  user: AuthUser;
}> {
  const response = await api.post<LoginResponse>('/login', {
    ...payload,
    device_name: 'student-app',
  });

  return buildSession(response.data, 'v1');
}

async function loginWithStudentApi(payload: LoginPayload): Promise<{
  session: AuthSession;
  user: AuthUser;
}> {
  const response = await studentApi.post<LoginResponse>('/auth/login', {
    ...payload,
    device_name: 'student-app',
  });

  return buildSession(response.data, 'student');
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  session: null,
  isHydrating: true,
  isLoggingIn: false,
  error: null,

  hydrate: async () => {
    try {
      const { session, user } = await getStoredSession();

      set({
        session,
        user,
        isHydrating: false,
        error: null,
      });
    } catch {
      await clearAuthSession();
      set({ session: null, user: null, isHydrating: false });
    }
  },

  login: async (payload) => {
    set({ isLoggingIn: true, error: null });

    try {
      disconnectEcho();
      useChatStore.getState().reset();
      usePrescriptionStore.getState().reset();

      let authResult: { session: AuthSession; user: AuthUser };

      if (authMode === 'student') {
        authResult = await loginWithStudentApi(payload);
      } else {
        try {
          authResult = await loginWithV1(payload);
        } catch (error) {
          if (!isUnauthorized(error)) {
            throw error;
          }

          authResult = await loginWithStudentApi(payload);
        }
      }

      await saveAuthSession(authResult.session, authResult.user);
      set({
        session: authResult.session,
        user: authResult.user,
        isLoggingIn: false,
        error: null,
      });
      router.replace('/(tabs)');
    } catch (error) {
      set({
        isLoggingIn: false,
        error: getApiErrorMessage(error),
      });
      throw error;
    }
  },

  logout: async () => {
    const session = useAuthStore.getState().session;

    try {
      if (session?.type === 'v1') {
        await api.post('/logout');
      }
    } finally {
      await clearAuthSession();
      disconnectEcho();
      useChatStore.getState().reset();
      usePrescriptionStore.getState().reset();
      set({ session: null, user: null, error: null });
      router.replace('/(auth)/login');
    }
  },
}));
