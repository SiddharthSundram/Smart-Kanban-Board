# Smart Kanban Board

A full-stack, real-time, collaborative Kanban board application built with PHP, MySQL, JavaScript, and Tailwind CSS. This project enables teams to manage tasks across customizable boards and columns, complete with notifications, analytics, and role-based access control.

## 🔍 Table of Contents

1. [Features](#features)  
2. [Getting Started](#getting-started)  
   - [Prerequisites](#prerequisites)  
   - [Installation](#installation)  
   - [Database Setup](#database-setup)  
3. [Usage](#usage)  
4. [Project Structure](#project-structure)  
5. [Contributing](#contributing)  
6. [License](#license)  

## 🚀 Features

- **User Authentication & Profiles**: Secure registration/login, password reset via email, profile photo and bio.  
- **Board & Column Management**: Create, clone, rename, reorder, archive/unarchive, and share boards; add, rename, delete, reorder, and archive columns.  
- **Task Management**: Rich task cards with descriptions, priorities, start/due dates, assignees, labels, file attachments, subtasks with progress bars, and cover images.  
- **Real-Time Collaboration**: AJAX-based updates to sync tasks and columns instantly among multiple users.  
- **Role-Based Access Control**: Owner, Editor, and Viewer roles with board-level permissions and invitation via email.  
- **Notifications**: In-app bell icon with task assignment, due-soon, and stage-change notifications; mark as read functionality.  
- **Analytics Dashboard**: Visualizations for tasks per column, completion rates, burn-down charts, and timeline views.  
- **Email Integration**: PHPMailer for sending password resets and board invitations.  
- **File Uploads**: Profile photos, task attachments, and cover images stored securely under `uploads/`.

## 🛠 Getting Started

### Prerequisites

- PHP 7.4+ or later  
- MySQL 5.7+ or MariaDB  
- Composer  
- Node.js & npm (optional, for Tailwind CLI)  


