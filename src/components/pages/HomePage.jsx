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
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 pb-12">
      <div className="max-w-7xl mx-auto px-4">
        {/* Hero Section */}
        <div className="text-center mb-16">
          <div className="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-600 to-green-700 rounded-lg mb-6 border border-green-500/30">
            <Terminal className="w-12 h-12 text-green-400" />
          </div>
          <h1 className="text-6xl font-bold text-green-400 mb-6 font-mono tracking-tight">
            HACK_ME_PLATFORM
          </h1>
          <p className="text-2xl text-gray-400 mb-8 font-mono">
            // ACCESS_GRANTED: WELCOME_OPERATIVE
          </p>
        </div>

        {/* Features Grid */}
        <div className="grid md:grid-cols-3 gap-8 mb-16">
          {features.map((feature, index) => {
            const IconComponent = getIcon(feature.icon);
            return (
              <div
                key={index}
                className="group bg-gray-800/80 backdrop-blur-lg rounded-lg p-8 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer"
                onClick={() => setCurrentPage(feature.page)}
              >
                <div className={`inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br ${feature.color} rounded-lg mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300 border border-gray-600`}>
                  <IconComponent className="w-8 h-8 text-white" />
                </div>
                <h3 className="text-2xl font-bold text-white mb-3 group-hover:text-green-400 transition font-mono">{feature.title}</h3>
                <p className="text-gray-400 mb-6 leading-relaxed font-mono">{feature.description}</p>
                <div className={`w-full bg-gradient-to-r ${feature.color} text-white py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2 border border-gray-600 font-mono`}>
                  ACCESS
                  <ChevronRight className="w-5 h-5" />
                </div>
              </div>
            );
          })}
        </div>

        {/* Stats Section */}
        <div className="grid md:grid-cols-4 gap-6">
          {stats.map((stat, index) => (
            <div key={index} className="bg-gray-800/80 backdrop-blur-lg rounded-lg p-6 border border-gray-700 text-center hover:scale-105 transition-transform duration-300 hover:border-green-500/50">
              <div className="text-4xl font-bold text-green-400 mb-2 font-mono">{stat.value}</div>
              <div className="text-gray-400 font-mono">{stat.label}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default HomePage;