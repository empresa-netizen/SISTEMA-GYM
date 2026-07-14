const defaultHost = '192.168.0.134';

export const apiBaseUrl =
  process.env.EXPO_PUBLIC_API_BASE_URL ?? `http://${defaultHost}:8000/api/v1`;

export const studentApiBaseUrl =
  process.env.EXPO_PUBLIC_STUDENT_API_BASE_URL ?? `http://${defaultHost}:8000/api`;

export const backendOrigin =
  process.env.EXPO_PUBLIC_BACKEND_ORIGIN ?? apiBaseUrl.replace(/\/api\/v1\/?$/, '');

export const authMode =
  process.env.EXPO_PUBLIC_AUTH_MODE === 'student' ? 'student' : 'auto';

export const reverbConfig = {
  key: process.env.EXPO_PUBLIC_REVERB_APP_KEY ?? 'pjaofk2kggty69regh6a',
  host: process.env.EXPO_PUBLIC_REVERB_HOST ?? defaultHost,
  port: Number(process.env.EXPO_PUBLIC_REVERB_PORT ?? 8080),
  scheme: process.env.EXPO_PUBLIC_REVERB_SCHEME ?? 'http',
  authEndpoint:
    process.env.EXPO_PUBLIC_REVERB_AUTH_ENDPOINT ?? `${backendOrigin}/broadcasting/auth`,
} as const;

