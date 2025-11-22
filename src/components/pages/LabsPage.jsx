import React from 'react';
import { Target } from 'lucide-react';
import { useLabs } from '../../hooks/useLabs';
import { LAB_TYPE_DETAILS } from '../../data/labTypes';
import LabCard from '../ui/LabCard';

const LabsPage = ({
  setCurrentPage,
  selectedLabType,
  isAdmin,
  isInstructor,
  onEditLab,
  onRemoveLab,
}) => {
  const { 
    getLabsByType, 
    setSelectedLab, 
    getLabProgress 
  } = useLabs();

  const labs = getLabsByType(selectedLabType);
  const labTypeDetails = LAB_TYPE_DETAILS[selectedLabType];

  const handleLabSelect = (lab) => {
    setSelectedLab(lab);
    setCurrentPage('lab-detail');
  };

  const getDifficultyColor = (difficulty) => {
    switch(difficulty) {
      case 'easy': return 'from-green-600 to-green-700';
      case 'medium': return 'from-yellow-600 to-yellow-700';
      case 'hard': return 'from-red-600 to-red-700';
      default: return 'from-gray-600 to-gray-700';
    }
  };

  const getDifficultyText = (difficulty) => {
    switch(difficulty) {
      case 'easy': return 'BEGINNER';
      case 'medium': return 'INTERMEDIATE';
      case 'hard': return 'ADVANCED';
      default: return 'UNKNOWN';
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black pt-20 pb-12">
      <div className="max-w-7xl mx-auto px-4">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="flex items-center justify-center gap-4 mb-4">
            <div className={`inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br ${labTypeDetails.color} rounded-lg border border-gray-600`}>
              <span className="text-2xl">{labTypeDetails.icon}</span>
            </div>
            <div>
              <h1 className="text-5xl font-bold text-green-400 mb-2 font-mono tracking-tight">
                {labTypeDetails.name}
              </h1>
              <p className="text-xl text-gray-400 font-mono">
                // {labTypeDetails.subtitle}
              </p>
            </div>
          </div>
          <p className="text-gray-400 max-w-2xl mx-auto font-mono text-sm">
            {labTypeDetails.description}
          </p>
        </div>

        {/* Labs Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {labs.map((lab) => {
            const progress = getLabProgress(lab.lab_id);
            
            return (
              <LabCard
                key={lab.lab_id}
                lab={lab}
                progress={progress}
                difficultyColor={getDifficultyColor(lab.difficulty)}
                difficultyText={getDifficultyText(lab.difficulty)}
                onSelect={handleLabSelect}
                showStaffControls={isAdmin || isInstructor}
                onEditLab={onEditLab}
                onRemoveLab={onRemoveLab}
              />
            );
          })}
        </div>

        {/* Empty State */}
        {labs.length === 0 && (
          <div className="text-center py-12">
            <Target className="w-16 h-16 text-gray-600 mx-auto mb-4" />
            <h3 className="text-2xl font-bold text-gray-400 font-mono mb-2">
              NO_LABS_AVAILABLE
            </h3>
            <p className="text-gray-500 font-mono">
              // CHECK_BACK_FOR_NEW_TRAINING_MODULES
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

export default LabsPage;