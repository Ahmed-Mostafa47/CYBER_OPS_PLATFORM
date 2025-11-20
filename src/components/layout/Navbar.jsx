import React from 'react';
import { Terminal, Cpu, Code, Users, Star, Shield } from 'lucide-react';
import { navItems } from '../../data/navigationData';

const Navbar = ({ setCurrentPage, onLogout, currentPage, currentUser, isAdmin }) => {
  const getIcon = (iconName) => {
    const icons = {
      Terminal: Terminal,
      Cpu: Cpu,
      Code: Code,
      Users: Users
    };
    return icons[iconName] || Terminal;
  };

  const getUserPoints = () => {
    return currentUser?.total_points || 0;
  };

  const getUserInitial = () => {
    return currentUser?.username?.charAt(0)?.toUpperCase() || 'O';
  };

  const getUserName = () => {
    return currentUser?.username || 'OPERATIVE';
  };

  const handleProfileNavigate = () => {
    setCurrentPage('profile');
  };

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-lg border-b border-gray-700">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          <button
            onClick={() => setCurrentPage('home')}
            className="flex items-center gap-2 text-white hover:text-green-400 transition-colors duration-200 group"
          >
            <div className="p-2 bg-gradient-to-br from-green-600 to-green-700 rounded-lg group-hover:scale-110 transition-transform duration-200 border border-green-500/30">
              <Terminal className="w-5 h-5 text-white" />
            </div>
            <span className="text-xl font-bold text-green-400 font-mono tracking-tight">
              HACK_ME
            </span>
          </button>

          <div className="flex items-center gap-2">
            {navItems.map((item) => {
              const IconComponent = getIcon(item.icon);
              return (
                <button
                  key={item.page}
                  onClick={() => setCurrentPage(item.page)}
                  className={`flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200 border font-mono ${
                    currentPage === item.page
                      ? 'bg-gradient-to-r from-green-600 to-green-700 text-white shadow-lg border-green-500/30'
                      : 'text-gray-400 hover:text-white hover:bg-gray-800/50 border-gray-600'
                  }`}
                >
                  <IconComponent className="w-4 h-4" />
                  <span className="hidden md:inline">{item.label}</span>
                </button>
              );
            })}
            {isAdmin && (
              <button
                onClick={() => setCurrentPage('admin')}
                className={`flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200 border font-mono ${
                  currentPage === 'admin'
                    ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white shadow-lg border-purple-500/30'
                    : 'text-gray-400 hover:text-white hover:bg-gray-800/50 border-gray-600'
                }`}
              >
                <Shield className="w-4 h-4" />
                <span className="hidden md:inline">ADMIN_DASHBOARD</span>
              </button>
            )}
          </div>

          <div className="flex items-center gap-4">
            <div className="hidden md:flex items-center gap-2 bg-gray-800/50 px-4 py-2 rounded-lg border border-gray-600">
              <Star className="w-4 h-4 text-green-400" />
              <span className="text-white font-semibold font-mono">{getUserPoints()}_PTS</span>
            </div>
            <button
              onClick={handleProfileNavigate}
              className="flex items-center gap-2 text-left focus:outline-none hover:opacity-80 transition"
            >
              <div className="w-8 h-8 bg-gradient-to-br from-green-600 to-green-700 rounded-lg flex items-center justify-center text-white font-bold border border-green-500/30">
                {getUserInitial()}
              </div>
              <span className="hidden md:inline text-white font-medium font-mono">
                {getUserName()}
              </span>
            </button>
            <button
              onClick={onLogout}
              className="px-4 py-2 bg-red-600/20 text-red-400 rounded-lg hover:bg-red-600/30 border border-red-500/30 hover:border-red-500/50 transition-all duration-200 font-medium font-mono"
            >
              LOGOUT
            </button>
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;