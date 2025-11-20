import { useState } from "react";

export const useAuth = () => {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [currentUser, setCurrentUser] = useState(null);

  const handleLogin = (userData) => {
    setIsLoggedIn(true);
    setCurrentUser(userData);

    // Store user in localStorage for persistence
    localStorage.setItem("cyberops_user", JSON.stringify(userData));
  };

  const handleRegister = (userData) => {
    setIsLoggedIn(true);
    setCurrentUser(userData);

    // Store user in localStorage for persistence
    localStorage.setItem("cyberops_user", JSON.stringify(userData));
  };

  const handleLogout = () => {
    setIsLoggedIn(false);
    setCurrentUser(null);

    // Clear user from localStorage
    localStorage.removeItem("cyberops_user");
  };

  // Check for existing session on app start
  const checkExistingSession = () => {
    const storedUser = localStorage.getItem("cyberops_user");
    if (storedUser) {
      const userData = JSON.parse(storedUser);
      setIsLoggedIn(true);
      setCurrentUser(userData);
    }
  };

  return {
    isLoggedIn,
    currentUser,
    handleLogin,
    handleRegister,
    handleLogout,
    checkExistingSession,
  };
};
