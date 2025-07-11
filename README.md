# Smart Kanban Board  
A feature-rich, real-time collaborative Kanban board system built using **PHP**, **MySQL**, **JavaScript**, and **Tailwind CSS**.

This project enables teams to manage projects efficiently through boards, tasks, real-time updates, notifications, role-based collaboration, and beautiful analytics.

---

## 🚀 Features

✅ User Authentication (Login, Register)  
✅ Profile Management with Bio & Photo  
✅ Create/Clone/Archive Boards & Columns  
✅ Drag-and-Drop Task Management  
✅ Role-Based Sharing (Owner, Editor, Viewer)  
✅ Subtasks, Priorities, Labels, Deadlines  
✅ Real-time Notifications & Updates  
✅ Analytics Dashboard (Burn-down, Timeline, Completion Rate)  
✅ File Uploads (Profile, Task Covers, Attachments)  
✅ Built-in Email Integration via PHPMailer  

---

## 📁 Project Structure

```
Smart-Kanban-Board/
├── analytics/         # Charts & task analytics
├── assets/            # CSS, JS, images (Tailwind)
├── auth/              # Login, register, reset, profile
├── boards/            # Board management & sharing
├── columns/           # Columns for each board
├── config/            # Database connection
├── dashboard/         # Main UI & views
├── database/          # SQL schema
├── email/             # PHPMailer templates
├── notifications/     # Real-time task notifications
├── realtime/          # AJAX polling for live sync
├── tasks/             # Task CRUD, movement, view
├── uploads/           # User and task file storage
├── utils/             # Helper functions
├── vendor/            # Composer libraries
└── README.md
```

---

## 🛠 Getting Started

### ✅ Prerequisites
- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Composer
- Node.js (optional, for Tailwind CLI)

### 📦 Installation

1. **Clone the repo**
```bash
git clone https://github.com/SiddharthSundram/Smart-Kanban-Board.git
cd Smart-Kanban-Board
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Tailwind (optional, for styling updates)**
```bash
npm install
npm run build:css
```

4. **Set up the environment**
   - Create a database (e.g., `smart_kanban`)
   - Import `database/schema.sql`
   - Configure `config/config.php` with your DB and email credentials

---

## ▶️ Usage

1. Start your PHP server (XAMPP, MAMP, or `php -S localhost:8000`)
2. Access via [http://localhost:8000](http://localhost:8000)
3. Register a new user and create your first board

---

## 📊 Dashboard Analytics

- 📌 Task Completion Rate by Board  
- 📈 Burn Down Chart  
- 🕒 Timeline View  
- 🧮 Task Distribution by Column & Priority  

---

## 🔐 Role-Based Access Control

- **Owner**: Full control of board  
- **Editor**: Can modify tasks/columns  
- **Viewer**: Read-only access  
- Board invites are sent via email

---

## 📧 Email Integration

- Board invitations  
- Task assignment alerts (real-time + email-ready)

---

## 🤝 Contributing

Contributions are welcome!

1. Fork the repo  
2. Create your feature branch (`git checkout -b feature/YourFeature`)  
3. Commit your changes (`git commit -m 'Add feature'`)  
4. Push to the branch (`git push origin feature/YourFeature`)  
5. Open a Pull Request

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

**Made with ❤️ by [@SiddharthSundram](https://github.com/SiddharthSundram)**

