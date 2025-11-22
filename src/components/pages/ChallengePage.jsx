import React, { useState } from 'react';
import { ChevronLeft, Flag, FileText, Zap, Send, Check, X } from 'lucide-react';
import { useLabs } from '../../hooks/useLabs';

const ChallengePage = ({ setCurrentPage }) => {
  const { selectedChallenge, submitFlag } = useLabs();
  const [flagInput, setFlagInput] = useState('');
  const [submissionResult, setSubmissionResult] = useState(null);

  if (!selectedChallenge) {
    setCurrentPage('lab-detail');
    return null;
  }

  const handleBack = () => {
    setCurrentPage('lab-detail');
  };

  const handleSubmitFlag = () => {
    const result = submitFlag(selectedChallenge.challenge_id, flagInput);
    setSubmissionResult(result);
    if (result.success) {
      setFlagInput('');
      // Refresh the page after 2 seconds to show updated progress
      setTimeout(() => {
        window.location.reload();
      }, 2000);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 pb-12">
      <div className="max-w-4xl mx-auto px-4">
        {/* Header */}
        <div className="flex items-start gap-6 mb-8">
          <button
            onClick={handleBack}
            className="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors duration-200 font-mono mt-2"
          >
            <ChevronLeft className="w-5 h-5" />
            BACK_TO_LAB
          </button>
          
          <div className="flex-1">
            <h1 className="text-4xl font-bold text-green-400 mb-2 font-mono tracking-tight">
              {selectedChallenge.title}
            </h1>
            <div className="flex items-center gap-4 text-sm text-gray-400 font-mono mb-4">
              <span>SCORE: {selectedChallenge.max_score}PTS</span>
              <span>DIFFICULTY: {selectedChallenge.difficulty.toUpperCase()}</span>
            </div>
          </div>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Challenge Details */}
          <div className="lg:col-span-2 space-y-6">
            {/* Statement */}
            <div className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700">
              <h2 className="text-xl font-bold text-white mb-4 font-mono flex items-center gap-2">
                <FileText className="w-5 h-5 text-green-400" />
                MISSION_BRIEFING
              </h2>
              <p className="text-gray-300 leading-relaxed font-mono">
                {selectedChallenge.statement}
              </p>
            </div>

            {/* Whitebox Files */}
            {selectedChallenge.whitebox_files_ref && selectedChallenge.whitebox_files_ref.length > 0 && (
              <div className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700">
                <h2 className="text-xl font-bold text-white mb-4 font-mono flex items-center gap-2">
                  <FileText className="w-5 h-5 text-blue-400" />
                  SOURCE_FILES
                </h2>
                <div className="space-y-2">
                  {selectedChallenge.whitebox_files_ref.map((file, index) => (
                    <div key={index} className="flex items-center gap-3 p-3 bg-gray-700/50 rounded border border-gray-600">
                      <FileText className="w-4 h-4 text-gray-400" />
                      <span className="text-gray-300 font-mono text-sm">{file}</span>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* Hints */}
            {selectedChallenge.hints && selectedChallenge.hints.length > 0 && (
              <div className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700">
                <h2 className="text-xl font-bold text-white mb-4 font-mono flex items-center gap-2">
                  <Zap className="w-5 h-5 text-yellow-400" />
                  INTELLIGENCE_HINTS
                </h2>
                <div className="space-y-3">
                  {selectedChallenge.hints.map((hint, index) => (
                    <div key={hint.hint_id} className="p-4 bg-yellow-500/10 rounded border border-yellow-500/30">
                      <div className="flex justify-between items-start mb-2">
                        <span className="text-yellow-400 font-mono font-semibold">
                          HINT_{index + 1}
                        </span>
                        <span className="text-yellow-300 text-sm font-mono">
                          -{hint.penalty_points}PTS
                        </span>
                      </div>
                      <p className="text-yellow-200 font-mono text-sm">
                        {hint.text}
                      </p>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>

          {/* Flag Submission */}
          <div className="lg:col-span-1">
            <div className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 sticky top-24">
              <h2 className="text-xl font-bold text-white mb-4 font-mono flex items-center gap-2">
                <Flag className="w-5 h-5 text-green-400" />
                FLAG_SUBMISSION
              </h2>

              {/* Submission Result */}
              {submissionResult && (
                <div className={`p-4 rounded-lg border mb-4 ${
                  submissionResult.success 
                    ? 'bg-green-500/20 border-green-500/30 text-green-400' 
                    : 'bg-red-500/20 border-red-500/30 text-red-400'
                }`}>
                  <div className="flex items-center gap-2 font-mono">
                    {submissionResult.success ? (
                      <>
                        <Check className="w-5 h-5" />
                        <span>SUCCESS: {submissionResult.message}</span>
                        {submissionResult.points && (
                          <span className="ml-auto font-bold">+{submissionResult.points}PTS</span>
                        )}
                      </>
                    ) : (
                      <>
                        <X className="w-5 h-5" />
                        <span>FAILED: {submissionResult.message}</span>
                      </>
                    )}
                  </div>
                </div>
              )}

              <div className="space-y-4">
                <div>
                  <label className="block text-gray-400 text-sm font-mono mb-2">
                    ENTER_FLAG
                  </label>
                  <input
                    type="text"
                    value={flagInput}
                    onChange={(e) => setFlagInput(e.target.value)}
                    placeholder="FLAG{...}"
                    className="w-full bg-gray-700/50 border-2 border-gray-600 rounded-lg p-4 text-white placeholder-gray-500 outline-none focus:border-green-500 focus:bg-gray-700/80 transition-all duration-300 font-mono"
                  />
                </div>

                <button
                  onClick={handleSubmitFlag}
                  disabled={!flagInput.trim()}
                  className="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-4 rounded-lg font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300 border border-green-500/30 font-mono flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                  <Send className="w-5 h-5" />
                  SUBMIT_FLAG
                </button>

                <div className="text-center text-gray-400 text-xs font-mono">
                  FORMAT: FLAG{`{...}`}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ChallengePage;


