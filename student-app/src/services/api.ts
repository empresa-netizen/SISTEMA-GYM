import { create, isAxiosError } from 'axios';
import type { AxiosError, AxiosInstance } from 'axios';

import { apiBaseUrl, studentApiBaseUrl } from '@/config/env';
import { getAccessToken } from '@/services/token-storage';

function createApiClient(baseURL: string): AxiosInstance {
  const client = create({
    baseURL,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    timeout: 15000,
  });

  client.interceptors.request.use(async (config) => {
    const token = await getAccessToken();

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  });

  return client;
}

export const api = createApiClient(apiBaseUrl);
export const studentApi = createApiClient(studentApiBaseUrl);

export function getApiErrorMessage(error: unknown): string {
  if (isAxiosError(error)) {
    const axiosError = error as AxiosError<{ message?: string; error?: string }>;

    return (
      axiosError.response?.data?.message ??
      axiosError.response?.data?.error ??
      axiosError.message ??
      'Nao foi possivel concluir a requisicao.'
    );
  }

  if (error instanceof Error) {
    return error.message;
  }

  return 'Nao foi possivel concluir a requisicao.';
}

export function isUnauthorized(error: unknown): boolean {
  return isAxiosError(error) && error.response?.status === 401;
}
