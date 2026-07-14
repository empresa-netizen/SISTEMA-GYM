export type StudentPhotoType = 'front' | 'back' | 'side' | 'progress';

export type StudentPhoto = {
  id: number;
  member_id?: number;
  path: string;
  url: string;
  type: StudentPhotoType | string;
  caption: string | null;
  created_at: string | null;
  updated_at?: string | null;
};

export type UploadStudentPhotoPayload = {
  uri: string;
  fileName?: string | null;
  mimeType?: string | null;
  type: StudentPhotoType;
  caption?: string | null;
};
