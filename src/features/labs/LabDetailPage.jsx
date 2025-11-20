import React from 'react';
import { ChevronLeft, Target, Clock, FileText, Flag, Play } from 'lucide-react';
import { useLabs } from '../../hooks/useLabs';

const LabDetailPage = ({ setCurrentPage }) => {
  const { selectedLab, setSelectedChallenge, getChallengesByLab, getLabProgress } = useLabs();
  
  if (!selectedLab) {
    setCurrentPage('labs');
    return null;
  }

  const challenges = getChallengesByLab(selectedLab.lab_id);
  const progress = getLabProgress(selectedLab.lab_id);

  const handleChallengeSelect = (challenge) => {
    setSelectedChallenge(challenge);
    setCurrentPage('challenge');
  };

  const handleBack = () => {
    setCurrentPage('labs');
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-24 pb-12 px-4">
      <div className="max-w-6xl mx-auto w-full">
        <div className="flex flex-col md:flex-row items-start gap-4 md:gap-6 mb-8">
          <button
            onClick={handleBack}
            className="flex items-center gap-2 text-gray-400 hover:text-green-400 transition-colors duration-200 font-mono"
          >
            <ChevronLeft className="w-5 h-5" />
            BACK_TO_LABS
          </button>
          
          <div className="flex-1">
            <h1 className="text-3xl sm:text-4xl font-bold text-green-400 mb-2 font-mono tracking-tight leading-tight">
              {selectedLab.title}
            </h1>
            <p className="text-gray-400 font-mono text-base sm:text-lg mb-4">
              {selectedLab.description}
            </p>
            
            <div className="flex flex-col sm:flex-row sm:items-center gap-4 text-sm text-gray-400 font-mono">
              <div className="flex items-center gap-2">
                <Target className="w-4 h-4" />
                <span>PROGRESS: {progress.progress}%</span>
              </div>
              <div className="flex items-center gap-2">
                <Flag className="w-4 h-4" />
                <span>BOUNTY: {progress.earnedPoints}/{progress.totalPoints}PTS</span>
              </div>
            </div>
          </div>
        </div>

        <div className="grid gap-4">
          {challenges.map((challenge, index) => (
            <div
              key={challenge.challenge_id}
              className="bg-gray-800/80 backdrop-blur-lg rounded-2xl p-5 sm:p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 cursor-pointer"
              onClick={() => handleChallengeSelect(challenge)}
            >
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-gradient-to-br from-green-600 to-green-700 rounded-xl flex items-center justify-center border border-green-500/30">
                    <span className="text-white font-mono font-bold">{index + 1}</span>
                  </div>
                  
                  <div>
                    <h3 className="text-lg sm:text-xl font-bold text-white font-mono mb-1">
                      {challenge.title}
                    </h3>
                    <p className="text-gray-400 text-sm font-mono">
                      {challenge.statement}
                    </p>
                    
                    <div className="flex flex-wrap items-center gap-4 mt-2 text-xs text-gray-400 font-mono">
                      <span>SCORE: {challenge.max_score}PTS</span>
                      <span>DIFFICULTY: {challenge.difficulty.toUpperCase()}</span>
                      {challenge.whitebox_files_ref && (
                        <span>FILES: {challenge.whitebox_files_ref.length}</span>
                      )}
                    </div>
                  </div>
                </div>
                
                <div className="flex items-center gap-3">
                  <div className="text-right">
                    <div className="text-green-400 font-mono font-bold">
                      {challenge.max_score}PTS
                    </div>
                    <div className="text-gray-400 text-xs font-mono">
                      BOUNTY
                    </div>
                  </div>
                  <Play className="w-5 h-5 text-green-400" />
                </div>
              </div>
            </div>
          ))}
        </div>

        {challenges.length === 0 && (
          <div className="text-center py-12">
            <FileText className="w-16 h-16 text-gray-600 mx-auto mb-4" />
            <h3 className="text-2xl font-bold text-gray-400 font-mono mb-2">
              NO_CHALLENGES_AVAILABLE
            </h3>
            <p className="text-gray-500 font-mono">
              // CHECK_BACK_FOR_NEW_CHALLENGES
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

export default LabDetailPage;
