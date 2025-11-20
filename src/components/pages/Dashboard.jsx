import React from 'react';
import { Trophy, Target, Award, Bug, Clock } from 'lucide-react';
import { statsData, leaderboardData, activityData } from '../../data/statsData';

const Dashboard = () => {
  const getIcon = (iconName) => {
    const icons = {
      Trophy: Trophy,
      Target: Target,
      Award: Award,
      Bug: Bug,
      Clock: Clock
    };
    return icons[iconName] || Trophy;
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 pb-12">
      <div className="max-w-7xl mx-auto px-4">
        {/* Welcome Header */}
        <div className="mb-8 text-center">
          <h1 className="text-5xl font-bold text-green-400 mb-3 font-mono tracking-tight">
            DASHBOARD
          </h1>
          <p className="text-xl text-gray-400 font-mono">// MISSION_PROGRESS_TRACKING</p>
        </div>

        {/* Stats Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {statsData.map((stat, index) => {
            const IconComponent = getIcon(stat.icon);
            return (
              <div
                key={index}
                className="group relative bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl overflow-hidden"
              >
                <div className="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-10 transition-opacity duration-300" style={{backgroundImage: `linear-gradient(to bottom right, var(--tw-gradient-stops))`}}></div>
                <div className="relative z-10">
                  <div className={`inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br ${stat.color} rounded-lg mb-4 shadow-lg border border-gray-600`}>
                    <IconComponent className={`w-7 h-7 ${stat.iconColor}`} />
                  </div>
                  <h3 className="text-4xl font-bold text-white mb-2 font-mono">{stat.value}</h3>
                  <p className="text-gray-400 text-sm font-medium font-mono">{stat.label}</p>
                </div>
              </div>
            );
          })}
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Leaderboard */}
          <div className="lg:col-span-1 bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 bg-gradient-to-br from-green-600 to-green-700 rounded-lg border border-green-500/30">
                <Trophy className="w-6 h-6 text-green-400" />
              </div>
              <h2 className="text-2xl font-bold text-white font-mono">OPERATIVE_RANKING</h2>
            </div>
            <div className="space-y-3">
              {leaderboardData.map((player, index) => (
                <div
                  key={index}
                  className={`flex justify-between items-center p-4 rounded-lg transition-all duration-300 ${
                    player.name === 'YOU'
                      ? 'bg-green-500/20 border-2 border-green-500/50 shadow-lg scale-105'
                      : 'bg-gray-700/50 hover:bg-gray-700/80 border border-gray-600'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <span className="text-2xl">{player.avatar}</span>
                    <div>
                      <span className={`font-bold font-mono ${player.name === 'YOU' ? 'text-green-400 text-lg' : 'text-gray-300'}`}>
                        {player.name}
                      </span>
                      {player.name === 'YOU' && (
                        <span className="ml-2 text-xs bg-green-500 text-white px-2 py-1 rounded font-mono">ACTIVE</span>
                      )}
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="font-bold text-green-400 text-lg font-mono">{player.points}</div>
                    <div className="text-gray-400 text-xs font-mono">POINTS</div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Recent Activity */}
          <div className="lg:col-span-2 bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300">
            <div className="flex items-center gap-3 mb-6">
              <div className="p-2 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg border border-blue-500/30">
                <Clock className="w-6 h-6 text-blue-400" />
              </div>
              <h2 className="text-2xl font-bold text-white font-mono">MISSION_LOG</h2>
            </div>
            <div className="space-y-4">
              {activityData.map((activity, index) => (
                <div
                  key={index}
                  className="bg-gray-700/50 hover:bg-gray-700/80 border border-gray-600 rounded-lg p-5 transition-all duration-300 hover:shadow-xl group"
                >
                  <div className="flex justify-between items-start mb-3">
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">{activity.icon}</span>
                      <div>
                        <h3 className="font-semibold text-white group-hover:text-green-400 transition font-mono">{activity.lab}</h3>
                        <p className="text-gray-400 text-sm font-mono">{activity.time}</p>
                      </div>
                    </div>
                    <span
                      className={`px-3 py-1 rounded text-xs font-semibold font-mono border ${
                        activity.status === 'COMPLETED'
                          ? 'bg-green-500/20 text-green-400 border-green-500/30'
                          : activity.status === 'IN_PROGRESS'
                          ? 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'
                          : 'bg-gray-500/20 text-gray-400 border-gray-500/30'
                      }`}
                    >
                      {activity.status}
                    </span>
                  </div>
                  <div className="h-2 bg-gray-600 rounded-full overflow-hidden">
                    <div
                      className={`h-full rounded-full transition-all duration-500 ${
                        activity.status === 'COMPLETED'
                          ? 'bg-gradient-to-r from-green-400 to-green-500 w-full'
                          : activity.status === 'IN_PROGRESS'
                          ? 'bg-gradient-to-r from-yellow-400 to-yellow-500 w-2/3'
                          : 'bg-gradient-to-r from-gray-400 to-gray-500 w-1/4'
                      }`}
                    ></div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;