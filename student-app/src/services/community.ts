import { getApiErrorMessage, studentApi } from '@/services/api';
import { StudentCommunityPost, StudentCommunityResponse } from '@/types/community';

type CommunityPostCreateResponse = {
  message?: string;
  data: StudentCommunityPost;
};

export async function fetchStudentCommunity(): Promise<StudentCommunityResponse> {
  try {
    const response = await studentApi.get<StudentCommunityResponse>('/groups');

    return {
      groups: response.data.groups ?? [],
      recent_posts: response.data.recent_posts ?? [],
    };
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}

export async function sendStudentCommunityPost(
  groupId: number,
  content: string,
): Promise<StudentCommunityPost> {
  try {
    const response = await studentApi.post<CommunityPostCreateResponse>(`/groups/${groupId}/posts`, {
      content: content.trim(),
    });

    return response.data.data;
  } catch (error) {
    throw new Error(getApiErrorMessage(error));
  }
}
