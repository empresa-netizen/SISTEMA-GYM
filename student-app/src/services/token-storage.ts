import * as SecureStore from 'expo-secure-store';

import { AuthSession, AuthUser } from '@/types/auth';

const ACCESS_TOKEN_KEY = 'student_app_access_token';
const AUTH_SESSION_KEY = 'student_app_auth_session';
const AUTH_USER_KEY = 'student_app_auth_user';

export async function getAccessToken(): Promise<string | null> {
  return SecureStore.getItemAsync(ACCESS_TOKEN_KEY);
}

export async function saveAuthSession(session: AuthSession, user: AuthUser): Promise<void> {
  await Promise.all([
    SecureStore.setItemAsync(ACCESS_TOKEN_KEY, session.accessToken),
    SecureStore.setItemAsync(AUTH_SESSION_KEY, JSON.stringify(session)),
    SecureStore.setItemAsync(AUTH_USER_KEY, JSON.stringify(user)),
  ]);
}

export async function getStoredSession(): Promise<{
  session: AuthSession | null;
  user: AuthUser | null;
}> {
  const [sessionPayload, userPayload] = await Promise.all([
    SecureStore.getItemAsync(AUTH_SESSION_KEY),
    SecureStore.getItemAsync(AUTH_USER_KEY),
  ]);

  return {
    session: sessionPayload ? (JSON.parse(sessionPayload) as AuthSession) : null,
    user: userPayload ? (JSON.parse(userPayload) as AuthUser) : null,
  };
}

export async function clearAuthSession(): Promise<void> {
  await Promise.all([
    SecureStore.deleteItemAsync(ACCESS_TOKEN_KEY),
    SecureStore.deleteItemAsync(AUTH_SESSION_KEY),
    SecureStore.deleteItemAsync(AUTH_USER_KEY),
  ]);
}

