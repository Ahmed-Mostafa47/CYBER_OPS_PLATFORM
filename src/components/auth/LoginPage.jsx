import React, { useState } from 'react';
import { Mail, Lock, Key, Eye, EyeOff, ChevronRight, Zap, UserPlus, LogIn, HelpCircle } from 'lucide-react';
import BinaryRain from '../ui/BinaryRain';

const LoginPage = ({ onLogin, onSwitchToRegister, onForgotPassword }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isHovered, setIsHovered] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Login attempted with:', email, password);
    
    setIsLoading(true);

    // Simulate API call
    setTimeout(() => {
      const userData = {
        user_id: Math.floor(Math.random() * 1000) + 1,
        username: email.split('@')[0] || 'operative',
        email: email,
        full_name: 'Security Operative',
        total_points: 0,
        profile_meta: {
          avatar: "💀",
          rank: "OPERATIVE",
          specialization: "PENETRATION_TESTING"
        }
      };
      
      setIsLoading(false);
      onLogin(userData);
    }, 1500);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black flex items-center justify-center p-4 relative overflow-hidden">
      <BinaryRain />
      
      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-green-500/10 via-gray-900 to-black"></div>
      <div className="absolute top-0 left-0 w-full h-1 bg-green-400 animate-pulse"></div>

      <div className="relative w-full max-w-md">
        <div className="bg-gray-800/90 backdrop-blur-lg rounded-lg shadow-2xl overflow-hidden border border-green-500/30">
          <div className="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 p-8 text-center relative overflow-hidden border-b border-green-500/30">
            <div className="absolute inset-0 bg-green-500/5"></div>
            <div className="relative z-10">
              <div className="inline-flex items-center justify-center w-20 h-20 bg-green-500/20 backdrop-blur-sm rounded-full mb-4 border border-green-500/30">
                <LogIn className="w-10 h-10 text-green-400" />
              </div>
              <h1 className="text-4xl font-bold text-green-400 mb-2 font-mono">HACK_ME</h1>
              <p className="text-gray-400 font-mono text-sm">// PENETRATION_TESTING_PLATFORM</p>
            </div>
          </div>

          <div className="p-8">
            <div className="text-center mb-8">
              <h2 className="text-3xl font-bold text-white mb-2 font-mono">ACCESS_CONTROL</h2>
              <p className="text-gray-400 font-mono">AUTHENTICATION_REQUIRED</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="relative group">
                <label className="block text-sm font-semibold text-gray-400 mb-2 font-mono">
                  [USER_IDENTIFIER]
                </label>
                <div className="relative">
                  <Mail className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-400" />
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                    className="w-full pl-12 pr-4 py-4 bg-gray-700/50 border-2 border-gray-600 rounded-lg text-white placeholder-gray-500 outline-none focus:border-green-500 focus:bg-gray-700/80 transition-all duration-300 font-mono"
                    placeholder="user@domain.com"
                  />
                </div>
              </div>

              <div className="relative group">
                <div className="flex justify-between items-center mb-2">
                  <label className="block text-sm font-semibold text-gray-400 font-mono">
                    [ACCESS_KEY]
                  </label>
                  <button
                    type="button"
                    onClick={onForgotPassword}
                    className="text-green-400 hover:text-green-300 font-mono text-xs transition-colors duration-200 flex items-center gap-1"
                  >
                    <HelpCircle className="w-3 h-3" />
                    FORGOT_ACCESS_KEY?
                  </button>
                </div>
                <div className="relative">
                  <Key className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-green-400" />
                  <input
                    type={showPassword ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                    className="w-full pl-12 pr-12 py-4 bg-gray-700/50 border-2 border-gray-600 rounded-lg text-white placeholder-gray-500 outline-none focus:border-green-500 focus:bg-gray-700/80 transition-all duration-300 font-mono"
                    placeholder="********"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 transform -translate-y-1/2 text-green-400 hover:text-green-300 transition"
                  >
                    {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
                  </button>
                </div>
              </div>

              <button
                type="submit"
                onMouseEnter={() => setIsHovered(true)}
                onMouseLeave={() => setIsHovered(false)}
                disabled={isLoading}
                className="w-full bg-gradient-to-r from-green-600 to-green-700 text-white py-4 rounded-lg font-bold text-lg shadow-lg hover:shadow-green-500/20 transform hover:scale-105 transition-all duration-300 relative overflow-hidden border border-green-500/30 font-mono flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
              >
                {isLoading ? (
                  <>
                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    AUTHENTICATING...
                  </>
                ) : (
                  <>
                    INITIATE_SESSION
                    <ChevronRight className={`w-5 h-5 transition-transform duration-300 ${isHovered ? 'translate-x-1' : ''}`} />
                  </>
                )}
              </button>
            </form>

            <div className="mt-6 text-center">
              <button
                onClick={onSwitchToRegister}
                className="text-green-400 hover:text-green-300 font-mono text-sm transition-colors duration-200 flex items-center justify-center gap-2 mx-auto"
              >
                <UserPlus className="w-4 h-4" />
                CREATE_NEW_OPERATIVE_ACCOUNT
              </button>
            </div>

            <div className="mt-6 bg-gray-700/50 backdrop-blur-sm border border-green-500/30 rounded-lg p-4">
              <div className="flex items-center gap-2 mb-2">
                <Zap className="w-4 h-4 text-green-400" />
                <p className="font-semibold text-white text-sm font-mono">DEBUG_MODE</p>
              </div>
              <p className="text-gray-400 text-xs font-mono">ANY_CREDENTIALS_ACCEPTED_FOR_DEMO</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;