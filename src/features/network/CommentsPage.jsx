import React, { useCallback, useEffect, useMemo, useState } from "react";
import { Send, Heart, MessageCircle, Loader2, Trash2, RefreshCw } from "lucide-react";
import {
  createComment,
  deleteComment,
  fetchComments,
  toggleCommentLike,
} from "@/services/commentsService";

const MAX_COMMENT_LENGTH = 500;

const CommentsPage = ({ currentUser, isAdmin }) => {
  const [comments, setComments] = useState([]);
  const [newComment, setNewComment] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");

  const userId = currentUser?.user_id ?? null;
  const userAvatar = currentUser?.profile_meta?.avatar || "💀";

  const canPost = useMemo(() => {
    const trimmed = newComment.trim();
    return Boolean(userId && trimmed.length > 0 && trimmed.length <= MAX_COMMENT_LENGTH);
  }, [newComment, userId]);

  const loadComments = useCallback(async () => {
    setIsLoading(true);
    try {
      const { comments: payload } = await fetchComments(userId);
      setComments(payload || []);
      setError("");
    } catch (err) {
      setError(err.message || "Unable to load comments.");
    } finally {
      setIsLoading(false);
    }
  }, [userId]);

  useEffect(() => {
    loadComments();
  }, [loadComments]);

  const formatTimestamp = (timestamp) => {
    if (!timestamp) return "JUST_NOW";
    const date = new Date(timestamp);
    const diffMs = Date.now() - date.getTime();
    const diffMinutes = Math.max(Math.floor(diffMs / (1000 * 60)), 0);

    if (diffMinutes < 1) return "JUST_NOW";
    if (diffMinutes < 60) return `${diffMinutes}_MIN_AGO`;
    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `${diffHours}_HRS_AGO`;
    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays}_DAYS_AGO`;
  };

  const handleAddComment = async () => {
    if (!canPost || isSubmitting) return;
    setIsSubmitting(true);

    try {
      const { comment } = await createComment({
        userId,
        content: newComment.trim(),
      });
      if (comment) {
        setComments((prev) => [comment, ...prev]);
        setNewComment("");
      }
      setError("");
    } catch (err) {
      setError(err.message || "Unable to submit comment.");
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleLike = async (commentId) => {
    if (!userId) return;

    setComments((prev) =>
      prev.map((comment) =>
        comment.id === commentId
          ? {
              ...comment,
              liked_by_current_user: !comment.liked_by_current_user,
              likes: comment.liked_by_current_user
                ? Math.max(comment.likes - 1, 0)
                : comment.likes + 1,
            }
          : comment
      )
    );

    try {
      const { comment } = await toggleCommentLike({
        commentId,
        userId,
      });
      if (comment) {
        setComments((prev) =>
          prev.map((item) => (item.id === comment.id ? comment : item))
        );
      }
    } catch (err) {
      setError(err.message || "Unable to update like status.");
      loadComments();
    }
  };

  const handleDelete = async (commentId) => {
    if (!isAdmin || !userId) return;
    try {
      await deleteComment({ commentId, userId });
      setComments((prev) => prev.filter((comment) => comment.id !== commentId));
    } catch (err) {
      setError(err.message || "Unable to delete comment.");
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 sm:pt-24 pb-8 sm:pb-12 px-3 sm:px-4 md:px-6">
      <div className="mx-auto w-full max-w-5xl">
        <div className="text-center mb-6 sm:mb-8 md:mb-10 px-2 sm:px-4">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold text-green-400 mb-2 sm:mb-3 font-mono tracking-tight">
            OPERATIVE_NETWORK
          </h1>
          <p className="text-sm sm:text-base md:text-lg lg:text-xl text-gray-400 font-mono">// SECURE_COMMUNICATIONS</p>
        </div>

        <div className="bg-gray-800/80 backdrop-blur-lg rounded-xl sm:rounded-2xl p-3 sm:p-4 md:p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 mb-6 sm:mb-8">
          <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-3 sm:mb-4">
            <div className="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 mx-auto sm:mx-0 bg-gradient-to-br from-green-600 to-green-700 rounded-lg flex items-center justify-center text-xl sm:text-2xl border border-green-500/30">
              {userAvatar}
            </div>
            <textarea
              value={newComment}
              onChange={(e) => setNewComment(e.target.value)}
              placeholder="TRANSMIT_INTEL_OR_REQUEST_SUPPORT..."
              maxLength={MAX_COMMENT_LENGTH}
              rows={4}
              className="w-full bg-gray-700/50 border-2 border-gray-600 rounded-lg p-3 sm:p-4 text-white text-xs sm:text-sm md:text-base placeholder-gray-500 outline-none focus:border-green-500 focus:bg-gray-700/80 transition-all duration-300 resize-none font-mono"
            />
          </div>
          <div className="flex flex-col sm:flex-row gap-3 sm:gap-4 sm:items-center justify-between">
            <span className="text-gray-400 text-xs sm:text-sm font-mono text-center sm:text-left">
              {newComment.length}/{MAX_COMMENT_LENGTH}_BYTES
            </span>
            <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
              <button
                type="button"
                onClick={handleAddComment}
                disabled={!canPost || isSubmitting}
                className={`flex items-center justify-center gap-2 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 rounded-lg font-semibold transition-all duration-300 border font-mono text-xs sm:text-sm ${
                  !canPost
                    ? "bg-gray-700/50 text-gray-500 border-gray-600 cursor-not-allowed"
                    : "bg-gradient-to-r from-green-600 to-green-700 text-white hover:shadow-green-500/20 hover:scale-[1.01] border-green-500/30"
                }`}
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="w-3 h-3 sm:w-4 sm:h-4 animate-spin" />
                    <span className="text-xs sm:text-sm">TRANSMITTING</span>
                  </>
                ) : (
                  <>
                    <Send className="w-3 h-3 sm:w-4 sm:h-4" />
                    <span className="text-xs sm:text-sm">TRANSMIT</span>
                  </>
                )}
              </button>
              <button
                type="button"
                onClick={loadComments}
                className="flex items-center justify-center gap-2 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 md:py-3 rounded-lg font-semibold border border-gray-600 text-gray-300 hover:border-green-500/50 hover:text-green-400 transition-all duration-300 font-mono text-xs sm:text-sm"
              >
                <RefreshCw className={`w-3 h-3 sm:w-4 sm:h-4 ${isLoading ? "animate-spin" : ""}`} />
                <span className="text-xs sm:text-sm">SYNC_FEED</span>
              </button>
            </div>
          </div>
          {error && (
            <p className="mt-3 sm:mt-4 text-center text-xs sm:text-sm text-red-400 font-mono break-words">
              {error}
            </p>
          )}
        </div>

        <div className="space-y-4 sm:space-y-5 md:space-y-6">
          {isLoading ? (
            <div className="flex items-center justify-center py-12">
              <Loader2 className="w-6 h-6 animate-spin text-green-400" />
            </div>
          ) : comments.length === 0 ? (
            <div className="text-center bg-gray-800/60 rounded-2xl border border-dashed border-gray-600 p-8 font-mono text-gray-400">
              NO_TRANSMISSIONS_AVAILABLE
            </div>
          ) : (
            comments.map((comment) => (
              <div
                key={comment.id}
                className="bg-gray-800/80 backdrop-blur-lg rounded-xl sm:rounded-2xl p-4 sm:p-5 md:p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:shadow-xl"
              >
                <div className="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-3 sm:mb-4">
                  <div className="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                    <div className="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center text-xl sm:text-2xl border border-blue-500/30">
                      {comment.avatar}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex flex-wrap items-center gap-1 sm:gap-2">
                        <span className="font-bold text-white font-mono text-sm sm:text-base md:text-lg break-words">
                          {comment.user}
                        </span>
                        {comment.rank && (
                          <span className="bg-green-500 text-white text-[10px] sm:text-xs px-1.5 sm:px-2 py-0.5 sm:py-1 rounded font-mono tracking-wide">
                            {comment.rank}
                          </span>
                        )}
                        <span className="text-gray-400 text-[10px] sm:text-xs md:text-sm font-mono">
                          • {formatTimestamp(comment.created_at)}
                        </span>
                      </div>
                      {comment.updated_at && (
                        <p className="text-[10px] sm:text-xs text-gray-500 font-mono">
                          UPDATED • {formatTimestamp(comment.updated_at)}
                        </p>
                      )}
                    </div>
                  </div>
                  {isAdmin && (
                    <button
                      type="button"
                      onClick={() => handleDelete(comment.id)}
                      className="self-start sm:self-auto inline-flex items-center gap-1 sm:gap-2 text-red-400 hover:text-red-300 text-[10px] sm:text-xs font-mono border border-red-500/40 rounded-lg px-2 sm:px-3 py-1 sm:py-1.5 hover:bg-red-500/10 transition"
                    >
                      <Trash2 className="w-3 h-3 sm:w-4 sm:h-4" />
                      PURGE
                    </button>
                  )}
                </div>

                <p className="text-gray-300 leading-relaxed font-mono text-xs sm:text-sm md:text-base break-words">
                  {comment.text}
                </p>

                <div className="flex flex-wrap gap-3 sm:gap-4 mt-4 sm:mt-6">
                  <button
                    type="button"
                    onClick={() => handleLike(comment.id)}
                    className={`flex items-center gap-1 sm:gap-2 text-xs sm:text-sm font-medium font-mono transition-colors duration-200 ${
                      comment.liked_by_current_user
                        ? "text-green-400"
                        : "text-gray-400 hover:text-green-400"
                    }`}
                  >
                    <Heart
                      className={`w-3 h-3 sm:w-4 sm:h-4 ${
                        comment.liked_by_current_user ? "fill-current" : ""
                      }`}
                    />
                    ACK ({comment.likes})
                  </button>
                  <button
                    type="button"
                    className="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm font-medium font-mono text-gray-400 hover:text-green-400 transition-colors duration-200"
                  >
                    <MessageCircle className="w-3 h-3 sm:w-4 sm:h-4" />
                    RESPOND
                  </button>
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default CommentsPage;
