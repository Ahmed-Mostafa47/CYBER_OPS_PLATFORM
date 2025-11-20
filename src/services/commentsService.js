import apiClient from "./apiClient";

const COMMENTS_ENDPOINT = "/comments.php";

export const fetchComments = async (userId) => {
  const params = userId ? { user_id: userId } : undefined;
  const { data } = await apiClient.get(COMMENTS_ENDPOINT, { params });
  return data;
};

export const createComment = async ({ userId, content }) => {
  const payload = { user_id: userId, content };
  const { data } = await apiClient.post(COMMENTS_ENDPOINT, payload);
  return data;
};

export const toggleCommentLike = async ({ commentId, userId }) => {
  const { data } = await apiClient.post("/comments/like.php", {
    comment_id: commentId,
    user_id: userId,
  });
  return data;
};

export const deleteComment = async ({ commentId, userId }) => {
  const { data } = await apiClient.delete(`${COMMENTS_ENDPOINT}?id=${commentId}`, {
    data: { user_id: userId },
  });
  return data;
};


