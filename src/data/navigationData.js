export const navItems = [
  { label: "CONTROL_CENTER", page: "home", icon: "Terminal" },
  { label: "MISSION_CONTROL", page: "dashboard", icon: "Cpu" },
  { label: "TRAINING", page: "training", icon: "Code" },
  { label: "NETWORK", page: "comments", icon: "Users" },
];

export const features = [
  {
    title: "TRAINING_MODULES",
    description: "Advanced penetration testing laboratories",
    icon: "Terminal",
    color: "from-green-600 to-green-700",
    page: "training", // Changed from "labs" to "training"
  },
  {
    title: "MISSION_CONTROL",
    description: "Operative progress and performance analytics",
    icon: "Cpu",
    color: "from-blue-600 to-blue-700",
    page: "dashboard",
  },
  {
    title: "OPERATIVE_NETWORK",
    description: "Secure communications and intelligence sharing",
    icon: "Users",
    color: "from-purple-600 to-purple-700",
    page: "comments",
  },
];

export const stats = [
  { value: "12+", label: "ACTIVE_MODULES" },
  { value: "50+", label: "EXPLOIT_VECTORS" },
  { value: "500+", label: "ACTIVE_OPERATIVES" },
  { value: "24/7", label: "MISSION_ACCESS" },
];
