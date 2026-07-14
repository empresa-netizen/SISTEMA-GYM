import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';

import { reverbConfig } from '@/config/env';
import { getAccessToken } from '@/services/token-storage';

let echo: Echo<'reverb'> | null = null;

export async function getEchoClient(): Promise<Echo<'reverb'>> {
  const token = await getAccessToken();

  if (!echo) {
    echo = new Echo({
      Pusher,
      broadcaster: 'reverb',
      key: reverbConfig.key,
      wsHost: reverbConfig.host,
      wsPort: reverbConfig.port,
      wssPort: reverbConfig.port,
      forceTLS: reverbConfig.scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: reverbConfig.authEndpoint,
      auth: {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
      },
    });
  }

  return echo;
}

export function disconnectEcho(): void {
  echo?.disconnect();
  echo = null;
}

