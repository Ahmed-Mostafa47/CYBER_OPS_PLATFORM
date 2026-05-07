# 🛡️ HackMe - Gamified Cybersecurity Training Platform

![HackMe Banner](./public/banner.png)

Welcome to **HackMe**, a comprehensive graduation project platform designed for practical cybersecurity learning. This platform provides hands-on experience through controlled labs, exploit-driven scenarios, and a real-time progress tracking system.

---

## 🏗️ Project Architecture

The project is split into two main repositories that work together:

1.  **Platform Core (This Repo):** The central hub for user management, leaderboards, scoring, and lab navigation.
2.  **Labs Repository:** A separate repository containing all the containerized labs (SQLi, XSS, CSRF, etc.).

---

## 🚀 Getting Started

To fully set up the platform, you need to follow the installation steps for both the **Platform** and the **Labs**.

### 1️⃣ Part 1: Platform Setup (Main Repository)

This repository contains the Frontend (React), Backend (PHP), and Notification Server (Node.js).

#### Prerequisites
*   **XAMPP / WAMP / LAMP** (PHP 8.1+, MySQL 8.0+)
*   **Node.js** (v18+) & **npm**
*   **Composer** (for PHP dependencies)

#### Installation Steps
1.  **Clone the Platform Repository:**
    ```bash
    git clone https://github.com/Ahmed-Mostafa47/CYBER_OPS_PLATFORM.git
    cd HackMe
    ```
2.  **Install Frontend Dependencies:**
    ```bash
    npm install
    ```
3.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```
4.  **Configure Environment Variables:**
    *   Rename `.env.example` to `.env`.
    *   Update your Database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, etc.).
    *   Update `MAIL_` settings for email verification features.
5.  **Database Setup:**
    *   Open phpMyAdmin and create a database named `ctf_platform`.
    *   Import the SQL schema located at `server/sql/ctf_platform.sql`.
6.  **Run the Platform:**
    *   Start **Apache** and **MySQL** in XAMPP.
    *   Start the React development server:
        ```bash
        npm run dev
        ```
    *   *(Optional)* Start the Notification Server:
        ```bash
        cd notification-server
        node server.js
        ```

---

### 2️⃣ Part 2: Labs Setup (The "Other" Repository)

The labs are hosted in a separate repository to keep the environment isolated and manageable via Docker.

#### Installation Steps
1.  **Go to the Labs Repository:**
    Navigate to [ABDOHAMDA/Labs](https://github.com/ABDOHAMDA/Labs) on GitHub.
2.  **Clone the Labs Repository:**
    ```bash
    # Move outside the HackMe directory first
    cd ..
    git clone https://github.com/ABDOHAMDA/Labs.git
    cd Labs
    ```
3.  **Deploy a Lab:**
    Each lab folder (e.g., `SQL`, `XSS`) contains its own environment. Most labs use Docker for easy setup.
    *   Navigate to a specific lab:
        ```bash
        cd SQL
        ```
    *   Start the lab environment:
        ```bash
        docker compose up -d
        ```
4.  **Solving Labs:**
    *   Once the lab is running, it will be accessible at a specific port (e.g., `http://localhost:8080`).
    *   Solve the challenge and get the **Flag**.
    *   Submit the flag in the **HackMe Platform** (running on `http://localhost:5173`) to earn points!

---

## 📁 Directory Structure

```text
HackMe/
├── src/                # React Frontend (UI, Components, Pages)
├── server/             # PHP Backend (API, Auth, Logic)
│   ├── api/            # API Endpoints
│   ├── core/           # Database & Core helpers
│   └── sql/            # Database migrations
├── notification-server/# Node.js Socket.IO server
└── public/             # Static assets (Images, Banners)
```

---

## 🛠️ Troubleshooting

*   **API Connection Error:** Ensure the `VITE_API_BASE` in your frontend code (or `.env`) matches your local XAMPP path (e.g., `http://localhost/HackMe/server/api`).
*   **Database Errors:** Verify that the database name in `.env` matches the one created in phpMyAdmin.
*   **Lab Not Accessible:** Make sure Docker Desktop is running before executing `docker compose up`.

---

## 🎓 Contributors
*   **Graduation Project Team** - Faculty of Computers and Artificial Intelligence.

---

### 🌟 Project Status: Active Development
For any issues, please open an issue in the respective repository.
