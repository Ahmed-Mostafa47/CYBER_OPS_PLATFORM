import React, { useState } from 'react';
import { Send, Heart, MessageCircle } from 'lucide-react';
import { initialComments } from '../../data/commentsData';

const CommentsPage = () => {
  const [comments, setComments] = useState(initialComments);
  const [newComment, setNewComment] = useState('');

  const handleAddComment = () => {
    if (newComment.trim() === '') return;

    const comment = {
      id: comments.length + 1,
      user: "OPERATIVE",
      text: newComment,
      time: "JUST_NOW",
      likes: 0,
      avatar: "💀"
    };

    setComments([comment, ...comments]);
    setNewComment('');
  };

  const handleLike = (commentId) => {
    setComments(comments.map(comment =>
      comment.id === commentId
        ? { ...comment, likes: comment.likes + 1 }
        : comment
    ));
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 pb-12">
      <div className="max-w-4xl mx-auto px-4">
        <div className="text-center mb-12">
          <h1 className="text-5xl font-bold text-green-400 mb-3 font-mono tracking-tight">
            OPERATIVE_NETWORK
          </h1>
          <p className="text-xl text-gray-400 font-mono">// SECURE_COMMUNICATIONS</p>
        </div>

        {/* Add Comment */}
        <div className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 mb-8">
          <div className="flex gap-4 mb-4">
            <div className="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-lg flex items-center justify-center text-2xl border border-green-500/30">
              💀
            </div>
            <textarea
              value={newComment}
              onChange={(e) => setNewComment(e.target.value)}
              placeholder="TRANSMIT_INTEL_OR_REQUEST_SUPPORT..."
              className="flex-1 bg-gray-700/50 border-2 border-gray-600 rounded-lg p-4 text-white placeholder-gray-500 outline-none focus:border-green-500 focus:bg-gray-700/80 transition-all duration-300 resize-none font-mono"
              rows="4"
            />
          </div>
          <div className="flex justify-between items-center">
            <span className="text-gray-400 text-sm font-mono">
              {newComment.length}/500_BYTES
            </span>
            <button
              onClick={handleAddComment}
              disabled={newComment.trim() === ''}
              className={`flex items-center gap-2 px-6 py-3 rounded-lg font-semibold transition-all duration-300 border font-mono ${
                newComment.trim() === ''
                  ? 'bg-gray-700/50 text-gray-500 border-gray-600 cursor-not-allowed'
                  : 'bg-gradient-to-r from-green-600 to-green-700 text-white hover:shadow-green-500/20 transform hover:scale-105 border-green-500/30'
              }`}
            >
              <Send className="w-4 h-4" />
              TRANSMIT
            </button>
          </div>
        </div>

        {/* Comments List */}
        <div className="space-y-6">
          {comments.map((comment) => (
            <div
              key={comment.id}
              className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:shadow-xl"
            >
              <div className="flex gap-4 mb-4">
                <div className="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center text-2xl border border-blue-500/30">
                  {comment.avatar}
                </div>
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2">
                    <span className="font-bold text-white font-mono">{comment.user}</span>
                    {comment.user === 'OPERATIVE' && (
                      <span className="bg-green-500 text-white text-xs px-2 py-1 rounded font-mono">ACTIVE</span>
                    )}
                    <span className="text-gray-400 text-sm font-mono">• {comment.time}</span>
                  </div>
                  <p className="text-gray-300 leading-relaxed font-mono">{comment.text}</p>
                </div>
              </div>

              <div className="flex gap-4 ml-16">
                <button
                  onClick={() => handleLike(comment.id)}
                  className="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors duration-200 font-mono"
                >
                  <Heart className="w-4 h-4" />
                  <span className="text-sm font-medium">ACK ({comment.likes})</span>
                </button>
                <button className="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors duration-200 font-mono">
                  <MessageCircle className="w-4 h-4" />
                  <span className="text-sm font-medium">RESPOND</span>
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default CommentsPage;