export type StudentCommunityPost = {
  id: number;
  group_id: number;
  group_name: string | null;
  member_id: number | null;
  author_name: string;
  author_image: string | null;
  content: string;
  likes_count: number;
  created_at: string | null;
};

export type StudentCommunityGroup = {
  id: number;
  name: string;
  description: string | null;
  members_count: number;
  posts_count: number;
  created_at: string | null;
  posts: StudentCommunityPost[];
};

export type StudentCommunityResponse = {
  groups: StudentCommunityGroup[];
  recent_posts: StudentCommunityPost[];
};
