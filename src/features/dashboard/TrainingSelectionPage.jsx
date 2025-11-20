import React from 'react';
import { ChevronRight, Code, Eye, Lock, Shield } from 'lucide-react';
import { LAB_TYPES, LAB_TYPE_DETAILS } from '../../data/labTypes';

const TrainingSelectionPage = ({ setCurrentPage, setSelectedLabType }) => {
  const handleLabTypeSelect = (labType) => {
    setSelectedLabType(labType);
    setCurrentPage('labs');
  };

  const labTypes = [
    {
      type: LAB_TYPES.WHITE_BOX,
      ...LAB_TYPE_DETAILS[LAB_TYPES.WHITE_BOX],
      features: [
        "FULL_SOURCE_CODE_ACCESS",
        "INTERNAL_ARCHITECTURE_KNOWLEDGE", 
        "CODE_ANALYSIS_REQUIRED",
        "STATIC_ANALYSIS_TOOLS"
      ],
      icon: Code
    },
    {
      type: LAB_TYPES.BLACK_BOX,
      ...LAB_TYPE_DETAILS[LAB_TYPES.BLACK_BOX],
      features: [
        "NO_SOURCE_CODE_ACCESS",
        "EXTERNAL_TESTING_ONLY",
        "DYNAMIC_ANALYSIS",
        "REAL_WORLD_SIMULATION"
      ],
      icon: Eye
    }
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-24 pb-12 px-4">
      <div className="max-w-6xl mx-auto w-full">
        <div className="text-center mb-10 sm:mb-14 px-2">
          <div className="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-green-600 to-green-700 rounded-2xl mb-6 border border-green-500/30">
            <Shield className="w-10 h-10 sm:w-12 sm:h-12 text-green-400" />
          </div>
          <h1 className="text-3xl sm:text-5xl font-bold text-green-400 mb-3 sm:mb-4 font-mono tracking-tight leading-tight">
            TRAINING_MODES
          </h1>
          <p className="text-base sm:text-xl text-gray-400 font-mono">
            // SELECT_TESTING_METHODOLOGY
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 mb-10">
          {labTypes.map((labType, index) => (
            <div
              key={labType.type}
              className="group bg-gray-800/80 backdrop-blur-lg rounded-2xl p-6 sm:p-8 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl cursor-pointer"
              onClick={() => handleLabTypeSelect(labType.type)}
            >
              <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
                <div className={`inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br ${labType.color} rounded-xl border border-gray-600 group-hover:scale-110 transition-transform duration-300`}>
                  <labType.icon className="w-7 h-7 sm:w-8 sm:h-8 text-white" />
                </div>
                <div className="text-left">
                  <h2 className="text-xl sm:text-2xl font-bold text-white font-mono">
                    {labType.name}
                  </h2>
                  <p className="text-green-400 text-xs sm:text-sm font-mono tracking-wide">
                    {labType.subtitle}
                  </p>
                </div>
              </div>

              <p className="text-gray-400 mb-6 leading-relaxed font-mono text-sm">
                {labType.description}
              </p>

              <div className="space-y-2 mb-6">
                {labType.features.map((feature, featureIndex) => (
                  <div key={featureIndex} className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-green-400 rounded-full"></div>
                    <span className="text-gray-300 text-sm font-mono">
                      {feature}
                    </span>
                  </div>
                ))}
              </div>

              <div className="flex flex-col sm:flex-row items-center justify-between gap-2 p-4 bg-gray-700/50 rounded-xl border border-gray-600 group-hover:border-green-500/30 transition-all duration-300">
                <span className="text-white font-mono text-sm tracking-wide">
                  INITIATE_TRAINING
                </span>
                <ChevronRight className="w-5 h-5 text-green-400 transform group-hover:translate-x-1 transition-transform duration-300" />
              </div>
            </div>
          ))}
        </div>

        <div className="bg-gray-800/60 rounded-2xl p-6 sm:p-8 border border-gray-700">
          <div className="flex flex-wrap items-center gap-3 mb-4">
            <Lock className="w-6 h-6 text-green-400" />
            <h3 className="text-lg sm:text-xl font-bold text-white font-mono">
              TRAINING_METHODOLOGY
            </h3>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-400 font-mono text-sm">
            <div>
              <h4 className="text-green-400 mb-2 font-semibold">WHITE_BOX_APPROACH</h4>
              <p className="leading-relaxed">
                Complete internal knowledge testing. Analyze source code, architecture diagrams, and internal documentation to identify vulnerabilities.
              </p>
            </div>
            <div>
              <h4 className="text-green-400 mb-2 font-semibold">BLACK_BOX_APPROACH</h4>
              <p className="leading-relaxed">
                External penetration testing simulation. No internal knowledge provided - discover and exploit vulnerabilities from the outside.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TrainingSelectionPage;
