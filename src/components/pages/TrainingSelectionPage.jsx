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
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 pb-12">
      <div className="max-w-6xl mx-auto px-4">
        {/* Header */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-600 to-green-700 rounded-lg mb-6 border border-green-500/30">
            <Shield className="w-12 h-12 text-green-400" />
          </div>
          <h1 className="text-5xl font-bold text-green-400 mb-4 font-mono tracking-tight">
            TRAINING_MODES
          </h1>
          <p className="text-xl text-gray-400 font-mono">
            // SELECT_TESTING_METHODOLOGY
          </p>
        </div>

        {/* Lab Type Selection Grid */}
        <div className="grid md:grid-cols-2 gap-8 mb-12">
          {labTypes.map((labType, index) => (
            <div
              key={labType.type}
              className="group bg-gray-800/80 backdrop-blur-lg rounded-lg p-8 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer"
              onClick={() => handleLabTypeSelect(labType.type)}
            >
              {/* Header */}
              <div className="flex items-center gap-4 mb-6">
                <div className={`inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br ${labType.color} rounded-lg border border-gray-600 group-hover:scale-110 transition-transform duration-300`}>
                  <labType.icon className="w-8 h-8 text-white" />
                </div>
                <div className="text-left">
                  <h2 className="text-2xl font-bold text-white font-mono">
                    {labType.name}
                  </h2>
                  <p className="text-green-400 text-sm font-mono">
                    {labType.subtitle}
                  </p>
                </div>
              </div>

              {/* Description */}
              <p className="text-gray-400 mb-6 leading-relaxed font-mono text-sm">
                {labType.description}
              </p>

              {/* Features */}
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

              {/* Action Button */}
              <div className="flex items-center justify-between p-4 bg-gray-700/50 rounded-lg border border-gray-600 group-hover:border-green-500/30 transition-all duration-300">
                <span className="text-white font-mono text-sm">
                  INITIATE_TRAINING
                </span>
                <ChevronRight className="w-5 h-5 text-green-400 transform group-hover:translate-x-1 transition-transform duration-300" />
              </div>
            </div>
          ))}
        </div>

        {/* Info Section */}
        <div className="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
          <div className="flex items-center gap-3 mb-4">
            <Lock className="w-6 h-6 text-green-400" />
            <h3 className="text-xl font-bold text-white font-mono">
              TRAINING_METHODOLOGY
            </h3>
          </div>
          <div className="grid md:grid-cols-2 gap-6 text-gray-400 font-mono text-sm">
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