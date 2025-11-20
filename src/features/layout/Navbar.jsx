import React, { useState } from "react";
import { Terminal, Cpu, Code, Users, Star, Shield, Menu, X } from "lucide-react";
import { navItems } from "../../data/navigationData";

const Navbar = ({ setCurrentPage, onLogout, currentPage, currentUser, isAdmin }) => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);

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
    setIsMenuOpen(false);
  };

  const handleNavSelection = (page) => {
    setCurrentPage(page);
    setIsMenuOpen(false);
  };

  const navigationButtons = (
    <>
      {navItems.map((item) => {
        const IconComponent = getIcon(item.icon);
        return (
          <button
            key={item.page}
            onClick={() => handleNavSelection(item.page)}
            className={`flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200 border font-mono ${
              currentPage === item.page
                ? 'bg-gradient-to-r from-green-600 to-green-700 text-white shadow-lg border-green-500/30'
                : 'text-gray-400 hover:text-white hover:bg-gray-800/50 border-gray-600'
            }`}
          >
            <IconComponent className="w-4 h-4" />
            <span className="text-sm sm:text-base">{item.label}</span>
          </button>
        );
      })}
      {isAdmin && (
        <button
          onClick={() => handleNavSelection('admin')}
          className={`flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200 border font-mono ${
            currentPage === 'admin'
              ? 'bg-gradient-to-r from-purple-600 to-purple-700 text-white shadow-lg border-purple-500/30'
              : 'text-gray-400 hover:text-white hover:bg-gray-800/50 border-gray-600'
          }`}
        >
          <Shield className="w-4 h-4" />
          <span className="text-sm sm:text-base">ADMIN_DASHBOARD</span>
        </button>
      )}
    </>
  );

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-lg border-b border-gray-700">
      <div className="max-w-7xl mx-auto px-4">
        <div className="flex items-center justify-between h-16 gap-4">
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

          <div className="hidden md:flex items-center gap-2">
            {navigationButtons}
          </div>

          <div className="flex items-center gap-3">
            <div className="hidden lg:flex items-center gap-2 bg-gray-800/50 px-4 py-2 rounded-lg border border-gray-600">
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
            <button
              type="button"
              className="md:hidden inline-flex items-center justify-center w-10 h-10 border border-gray-600 rounded-lg text-gray-300 hover:text-white hover:border-green-500/50 transition"
              onClick={() => setIsMenuOpen((prev) => !prev)}
            >
              {isMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>

        {isMenuOpen && (
          <div className="md:hidden mt-2 pb-4 border-t border-gray-800">
            <div className="flex flex-col gap-3 pt-4">
              {navigationButtons}
              <div className="flex items-center justify-between px-2 py-2 rounded-lg bg-gray-800/60 border border-gray-700">
                <span className="text-gray-300 font-mono text-sm">{getUserPoints()}_PTS</span>
                <button
                  onClick={onLogout}
                  className="text-red-400 font-mono text-sm border border-red-500/40 px-3 py-1 rounded-lg hover:bg-red-500/10 transition"
                >
                  LOGOUT
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </nav>
  );
};

export default Navbar;
