import React from 'react';
import { Terminal, Cpu, Users, ChevronRight } from 'lucide-react';
import { features, stats } from '../../data/navigationData';

const HomePage = ({ setCurrentPage }) => {
  const getIcon = (iconName) => {
    const icons = {
      Terminal: Terminal,
      Cpu: Cpu,
      Users: Users
    };
    return icons[iconName] || Terminal;
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-24 pb-12 px-4">
      <div className="max-w-7xl mx-auto w-full">
        <div className="text-center mb-12 sm:mb-16 px-4">
          <div className="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-green-600 to-green-700 rounded-lg mb-6 border border-green-500/30">
            <Terminal className="w-10 h-10 sm:w-12 sm:h-12 text-green-400" />
          </div>
          <h1 className="text-3xl sm:text-5xl lg:text-6xl font-bold text-green-400 mb-4 sm:mb-6 font-mono tracking-tight leading-tight">
            HACK_ME_PLATFORM
          </h1>
          <p className="text-base sm:text-xl text-gray-400 mb-8 font-mono">
            // ACCESS_GRANTED: WELCOME_OPERATIVE
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 sm:gap-8 mb-12 sm:mb-16 px-2">
          {features.map((feature, index) => {
            const IconComponent = getIcon(feature.icon);
            return (
              <div
                key={index}
                className="group bg-gray-800/80 backdrop-blur-lg rounded-2xl p-6 sm:p-8 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-pointer"
                onClick={() => setCurrentPage(feature.page)}
              >
                <div className={`inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br ${feature.color} rounded-xl mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300 border border-gray-600`}>
                  <IconComponent className="w-7 h-7 sm:w-8 sm:h-8 text-white" />
                </div>
                <h3 className="text-xl sm:text-2xl font-bold text-white mb-3 group-hover:text-green-400 transition font-mono">
                  {feature.title}
                </h3>
                <p className="text-gray-400 mb-6 leading-relaxed font-mono text-sm sm:text-base">
                  {feature.description}
                </p>
                <div className={`w-full bg-gradient-to-r ${feature.color} text-white py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2 border border-gray-600 font-mono text-sm sm:text-base`}>
                  ACCESS
                  <ChevronRight className="w-5 h-5" />
                </div>
              </div>
            );
          })}
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 px-2">
          {stats.map((stat, index) => (
            <div
              key={index}
              className="bg-gray-800/80 backdrop-blur-lg rounded-2xl p-4 sm:p-6 border border-gray-700 text-center hover:scale-[1.02] transition-transform duration-300 hover:border-green-500/50"
            >
              <div className="text-2xl sm:text-4xl font-bold text-green-400 mb-1 sm:mb-2 font-mono">
                {stat.value}
              </div>
              <div className="text-xs sm:text-sm text-gray-400 font-mono tracking-wide">
                {stat.label}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default HomePage;
