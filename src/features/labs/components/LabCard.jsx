import React from 'react';
import { BookOpen, Clock, Target, Zap, Lock, Play } from 'lucide-react';

const LabCard = ({
  lab,
  progress,
  difficultyColor,
  difficultyText,
  onSelect,
  showStaffControls = false,
  onEditLab,
  onRemoveLab,
}) => {
  const isLocked = lab.status === 'LOCKED';
  const isCompleted = progress.progress === 100;

  const getStatusIcon = () => {
    if (isLocked) return <Lock className="w-5 h-5" />;
    if (isCompleted) return <Target className="w-5 h-5" />;
    return <Play className="w-5 h-5" />;
  };

  const getStatusText = () => {
    if (isLocked) return 'ACCESS_DENIED';
    if (isCompleted) return 'COMPLETED';
    if (progress.progress > 0) return 'CONTINUE';
    return 'INITIATE';
  };

  const getStatusColor = () => {
    if (isLocked) return 'from-gray-600 to-gray-700';
    if (isCompleted) return 'from-green-600 to-green-700';
    return difficultyColor;
  };

  return (
    <div
      className={`group bg-gray-800/80 backdrop-blur-lg rounded-2xl overflow-hidden border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl ${
        isLocked ? 'opacity-60' : 'cursor-pointer'
      }`}
      onClick={() => !isLocked && onSelect(lab)}
    >
      <div className={`bg-gradient-to-r ${getStatusColor()} p-5 sm:p-6 relative overflow-hidden border-b border-gray-600`}>
        <div className="absolute top-0 right-0 text-8xl opacity-10">{lab.icon}</div>
        <div className="relative z-10">
          <div className="flex justify-between items-start mb-2">
            <span className={`inline-block px-3 py-1 rounded text-[10px] sm:text-xs font-semibold font-mono border tracking-wide ${
              isLocked ? 'bg-gray-500/20 text-gray-400 border-gray-500/30' :
              `bg-white/20 text-white border-white/30`
            }`}>
              {difficultyText}
            </span>
            <span className="text-2xl font-bold text-white font-mono">
              {lab.points_total}
            </span>
          </div>
          <div className="text-white text-xs sm:text-sm font-mono">
            BOUNTY
          </div>
        </div>
      </div>

      <div className="p-5 sm:p-6">
        <h3 className="text-lg sm:text-xl font-bold text-white mb-3 group-hover:text-green-400 transition font-mono leading-tight">
          {lab.title}
        </h3>
        <p className="text-gray-400 text-sm mb-6 leading-relaxed font-mono">
          {lab.description}
        </p>

        <div className="mb-6">
          <div className="flex justify-between text-sm mb-2">
            <span className="text-gray-400 font-medium font-mono">MISSION_PROGRESS</span>
            <span className="text-white font-bold font-mono">{progress.progress}%</span>
          </div>
          <div className="h-3 bg-gray-700 rounded-full overflow-hidden">
            <div
              className={`h-full bg-gradient-to-r ${difficultyColor} rounded-full transition-all duration-500 relative overflow-hidden border border-gray-600`}
              style={{ width: `${progress.progress}%` }}
            >
              <div className="absolute inset-0 bg-white/10 animate-pulse"></div>
            </div>
          </div>
          <div className="flex justify-between text-xs text-gray-400 mt-1 font-mono">
            <span>{progress.earnedPoints}PTS_EARNED</span>
            <span>{progress.totalPoints}PTS_TOTAL</span>
          </div>
        </div>

        <button
          className={`w-full bg-gradient-to-r ${getStatusColor()} text-white py-3 rounded-lg font-semibold hover:shadow-lg transform transition-all duration-300 border border-gray-600 font-mono flex items-center justify-center gap-2 text-sm sm:text-base ${
            isLocked ? 'cursor-not-allowed opacity-50' : 'hover:scale-105'
          }`}
          disabled={isLocked}
        >
          {getStatusIcon()}
          {getStatusText()}
        </button>

        {showStaffControls && (
          <div className="mt-4 grid grid-cols-2 gap-3">
            <button
              className="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-blue-500/30 text-blue-400 bg-blue-500/10 font-mono text-xs hover:border-blue-400 transition"
              onClick={(event) => {
                event.stopPropagation();
                onEditLab?.(lab);
              }}
            >
              EDIT
            </button>
            <button
              className="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-red-500/30 text-red-400 bg-red-500/10 font-mono text-xs hover:border-red-400 transition"
              onClick={(event) => {
                event.stopPropagation();
                onRemoveLab?.(lab);
              }}
            >
              REMOVE
            </button>
          </div>
        )}

        <div className="mt-4 p-3 bg-gray-700/30 rounded-xl border border-gray-600">
          <div className="flex items-center gap-2 text-xs text-gray-400 font-mono">
            <Zap className="w-3 h-3" />
            {lab.labtype_id === 1 ? 'SOURCE_CODE_ANALYSIS' : 'EXTERNAL_PENETRATION'}
          </div>
        </div>
      </div>
    </div>
  );
};

export default LabCard;
