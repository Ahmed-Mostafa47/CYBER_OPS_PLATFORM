import React, { useState } from 'react';
import { UserPlus, Mail, Users, ChevronRight, Shield, LogIn } from 'lucide-react';
import BinaryRain from '@/features/shared/ui/BinaryRain';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const RegisterPage = ({ onRegister, onSwitchToLogin }) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    fullName: ''
  });
  const [isLoading, setIsLoading] = useState(false);
  const [isHovered, setIsHovered] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

const handleSubmit = async (e) => {
  e.preventDefault();
  setIsLoading(true);

  try {
    const response = await axios.post(
      'http://localhost/graduatoin_project/server/auth/send_verification.php',
      formData,
      { headers: { 'Content-Type': 'application/json' } }
    );

    const data = response.data;

    if (data && data.success) {
      alert("✅ Verification code sent to your email.");
      localStorage.setItem('userEmail', formData.email);
      localStorage.setItem('username', formData.username);
      localStorage.setItem('fullName', formData.fullName); 
      navigate("/verify");
    } else {
      const errorMessage = data?.message || response.data || "❌ Registration failed.";
      alert(errorMessage);
    }
  } catch (error) {
    if (error.response && error.response.data) {
      const errorData = error.response.data;
      const errorMessage = errorData.message || JSON.stringify(errorData);
      alert("⚠️ " + errorMessage);
    } else {
      alert("⚠️ Error connecting to server: " + (error.message || "Network error"));
    }
  } finally {
    setIsLoading(false);
  }
};


  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black flex items-center justify-center p-4 relative overflow-hidden">
      <BinaryRain />

      <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-blue-500/10 via-gray-900 to-black"></div>
      <div className="absolute top-0 left-0 w-full h-1 bg-blue-400 animate-pulse"></div>

      <div className="relative w-full max-w-md">
        <div className="bg-gray-800/90 backdrop-blur-lg rounded-lg shadow-2xl overflow-hidden border border-blue-500/30">
          <div className="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 p-8 text-center relative overflow-hidden border-b border-blue-500/30">
            <div className="absolute inset-0 bg-blue-500/5"></div>
            <div className="relative z-10">
              <div className="inline-flex items-center justify-center w-20 h-20 bg-blue-500/20 backdrop-blur-sm rounded-full mb-4 border border-blue-500/30 mx-auto">
                <UserPlus className="w-10 h-10 text-blue-400" />
              </div>
              <h1 className="text-3xl font-bold text-blue-400 mb-2 font-mono">OPERATIVE_REGISTRATION</h1>
              <p className="text-gray-400 font-mono text-sm">INITIAL_IDENTITY_SETUP</p>
            </div>
          </div>

          <div className="p-8">
            <div className="text-center mb-8">
              <h2 className="text-2xl font-bold text-white mb-2 font-mono">NEW_OPERATIVE</h2>
              <p className="text-gray-400 font-mono text-center">ACCOUNT_CREATION_PHASE_1</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Username */}
              <div className="relative group">
                <label className="block text-sm font-semibold text-gray-400 mb-2 font-mono text-left">
                  OPERATIVE_CODENAME
                </label>
                <div className="relative">
                  <Users className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-400" />
                  <input
                    type="text"
                    name="username"
                    value={formData.username}
                    onChange={handleChange}
                    required
                    className="w-full pl-12 pr-4 py-4 bg-gray-700/50 border-2 border-gray-600 rounded-lg text-white placeholder-gray-500 outline-none focus:border-blue-500 focus:bg-gray-700/80 transition-all duration-300 font-mono"
                    placeholder="ghost_operative"
                  />
                </div>
              </div>

              {/* Email */}
              <div className="relative group">
                <label className="block text-sm font-semibold text-gray-400 mb-2 font-mono text-left">
                  COMMUNICATION_CHANNEL
                </label>
                <div className="relative">
                  <Mail className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-400" />
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                    className="w-full pl-12 pr-4 py-4 bg-gray-700/50 border-2 border-gray-600 rounded-lg text-white placeholder-gray-500 outline-none focus:border-blue-500 focus:bg-gray-700/80 transition-all duration-300 font-mono"
                    placeholder="operative@secure.com"
                  />
                </div>
              </div>

              {/* Full name */}
              <div className="relative group">
                <label className="block text-sm font-semibold text-gray-400 mb-2 font-mono text-left">
                  FULL_IDENTITY
                </label>
                <div className="relative">
                  <UserPlus className="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-blue-400" />
                  <input
                    type="text"
                    name="fullName"
                    value={formData.fullName}
                    onChange={handleChange}
                    required
                    className="w-full pl-12 pr-4 py-4 bg-gray-700/50 border-2 border-gray-600 rounded-lg text-white placeholder-gray-500 outline-none focus:border-blue-500 focus:bg-gray-700/80 transition-all duration-300 font-mono"
                    placeholder="Ahmed Mohammed"
                  />
                </div>
              </div>

              {/* Info */}
              <div className="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                <div className="text-blue-300 font-mono text-sm text-center break-words">
                  ENCRYPTION_KEY_WILL_BE_SET_AFTER_IDENTITY_VERIFICATION
                </div>
              </div>

              <button
                type="submit"
                onMouseEnter={() => setIsHovered(true)}
                onMouseLeave={() => setIsHovered(false)}
                disabled={isLoading}
                className="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-4 rounded-lg font-bold text-lg shadow-lg hover:shadow-blue-500/20 transform hover:scale-105 transition-all duration-300 border border-blue-500/30 font-mono flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
              >
                {isLoading ? (
                  <>
                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    INITIATING_VERIFICATION...
                  </>
                ) : (
                  <>
                    CONTINUE_TO_VERIFICATION
                    <ChevronRight className={`w-5 h-5 transition-transform duration-300 ${isHovered ? 'translate-x-1' : ''}`} />
                  </>
                )}
              </button>
            </form>

            <div className="mt-6 text-center">
              <button
                onClick={onSwitchToLogin}
                className="text-blue-400 hover:text-blue-300 font-mono text-sm transition-colors duration-200 flex items-center justify-center gap-2 mx-auto"
              >
                <LogIn className="w-4 h-4" />
                EXISTING_OPERATIVE_ACCESS
              </button>
            </div>

            <div className="mt-6 bg-gray-700/50 backdrop-blur-sm border border-blue-500/30 rounded-lg p-4">
              <div className="flex items-center justify-center gap-2 mb-2">
                <Shield className="w-4 h-4 text-blue-400" />
                <p className="font-semibold text-white text-sm font-mono">SECURE_REGISTRATION</p>
              </div>
              <p className="text-gray-400 text-xs font-mono text-center break-words">
                IDENTITY_VERIFICATION_REQUIRED_BEFORE_ENCRYPTION_KEY_SETUP
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;
