import { getApiErrorMessage, studentApi } from '@/services/api';
import { StudentPhoto, UploadStudentPhotoPayload } from '@/types/photo';

type PhotoCreateResponse = {
  message?: string;
  data: StudentPhoto;
};

export async function fetchStudentPhotos(): Promise<StudentPhoto[]> {
  try {
    const response = await studentApi.get<StudentPhoto[]>('/photos');

    return response.data ?? [];
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}

export async function uploadStudentPhoto(payload: UploadStudentPhotoPayload): Promise<StudentPhoto> {
  try {
    const formData = new FormData();

    formData.append('type', payload.type);

    if (payload.caption?.trim()) {
      formData.append('caption', payload.caption.trim());
    }

    formData.append('photo', {
      uri: payload.uri,
      name: payload.fileName ?? `evolucao-${Date.now()}.jpg`,
      type: payload.mimeType ?? 'image/jpeg',
    } as unknown as Blob);

    const response = await studentApi.post<PhotoCreateResponse>('/photos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    return response.data.data;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}
