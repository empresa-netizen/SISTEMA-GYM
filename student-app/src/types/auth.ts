export type AuthSessionType = 'v1' | 'student';

export type AuthUser = {
  id: string;
  name: string;
  email?: string | null;
  image?: string | null;
  role?: string | null;
  phone?: string | null;
  status?: string | null;
  coachName?: string | null;
};

export type AuthSession = {
  accessToken: string;
  tokenType: 'Bearer';
  type: AuthSessionType;
};

export type LoginPayload = {
  email: string;
  password: string;
};

export type LoginResponse = {
  access_token?: string;
  token?: string;
  token_type?: string;
  session_type?: AuthSessionType;
  type?: AuthSessionType;
  user?: AuthUser;
  client?: AuthUser;
  data?: AuthUser;
};
