import React from 'react';

const BinaryRain = () => {
  return (
    <div className="absolute inset-0 overflow-hidden opacity-20">
      {[...Array(20)].map((_, i) => (
        <div
          key={i}
          className="absolute text-green-400 text-xs font-mono animate-binary-rain"
          style={{
            left: `${Math.random() * 100}%`,
            animationDelay: `${Math.random() * 5}s`,
            animationDuration: `${10 + Math.random() * 10}s`
          }}
        >
          {Math.random() > 0.5 ? '1' : '0'}
        </div>
      ))}
    </div>
  );
};

export default BinaryRain;
