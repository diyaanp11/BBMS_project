🩸 BBMS - Blood Bank Management System
Complete Blood Bank Management System
🚀 Overview
BBMS is a web-based Blood Bank Management System designed to connect blood donors with recipients, ensuring timely access to safe blood. It goes beyond basic record-keeping by providing real-time inventory tracking, role-based dashboards, and an AI-powered chatbot to assist users with blood donation queries .

✨ Features
🩸 Donor Portal - Register, donate blood, view donation history, manage profile

🏥 Recipient Portal - Register, request blood, track request status, manage profile

🔐 Admin Panel - Manage users, donations, blood requests, and inventory

🤖 AI Chatbot - Built-in assistant to answer blood donation questions

📊 Real-time Inventory - Track blood availability across all blood types

📱 Responsive Design - Works on desktop and mobile devices

🔒 Secure - Password hashing, prepared statements, role-based access

🛠️ Tech Stack
Layer	Technology
Backend	PHP (Procedural)
Database	MySQL
Frontend	HTML5, CSS3, JavaScript
Icons	Font Awesome 6.4.0
Fonts	Google Fonts (Poppins)
Server	XAMPP / Apache
📋 Requirements
PHP 8.0 or higher

MySQL 5.7 or higher

Apache (XAMPP recommended)

Web Browser (Chrome, Firefox, Edge)

🚀 Installation
1. Clone the repository

bash
git clone https://github.com/diyaanp11/BBMS_project.git
cd BBMS_project
2. Create Database

Open phpMyAdmin (http://localhost/phpmyadmin) and create a new database:

sql
CREATE DATABASE bbms;
3. Import Schema

Import the SQL file located at Logical_Database/Database_table.sql:

In phpMyAdmin, select your database

Go to the Import tab

Choose Database_table.sql and click Go

4. Configure Database Connection

Update Logical_Database/connection.php with your credentials:

php
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'bbms';
5. Start Server

Open XAMPP Control Panel

Start Apache and MySQL

Navigate to http://localhost/BBMS_project/

🔑 Default Admin Credentials
Field	Value
Email	admin@bbms.com
Password	admin123
📁 Project Structure
text
BBMS_project/
├── Frontend/           # Public-facing pages
│   ├── home.php        # Landing page
│   ├── aboutus.php     # About page
│   ├── contactus.php   # Contact page
│   ├── continue.php    # Role selection page
│   ├── learn.php       # Learn about blood donation
│   ├── header.php      # Navigation header
│   ├── footer.php      # Page footer
│   └── chatbot.php     # AI Chatbot widget
├── Admin/              # Admin panel
│   ├── dashboard.php   # Admin dashboard
│   ├── login.php       # Admin login
│   ├── manage_users.php
│   ├── manage_donations.php
│   ├── manage_requests.php
│   └── blood_inventory.php
├── Donor/              # Donor portal
│   ├── dashboard.php
│   ├── login.php
│   ├── signup.php
│   ├── donate_blood.php
│   ├── donation_history.php
│   └── my_profile.php
├── Recipient/          # Recipient portal
│   ├── dashboard.php
│   ├── login.php
│   ├── signup.php
│   ├── request_blood.php
│   ├── request_status.php
│   └── my_profile.php
├── Logical_Database/   # Database schema & connection
│   ├── connection.php
│   └── Database_table.sql
├── Backend/            # Test/debug scripts
├── .htaccess           # Apache security configuration
├── index.php           # Root redirect
└── README.md           # This file
🔒 Security Notes
Passwords are hashed using password_hash() (bcrypt)

SQL queries use prepared statements (parameterized)

Sessions are used for authentication

Form validation on both client and server side

.htaccess protects sensitive directories and files

👩‍💻 Contributors
Name	Email	GitHub
Diya Sharma	diyaanp11@gmail.com	@diyaanp11
Kabita Pandey	kabitapanday02@gmail.com	@kabitapandey
🙏 Acknowledgements
Supervisor: Mr. Bhuban Panthee

Coordinator: Er. Aashish Neupane

Department of Computer Application, Butwal Kalika Campus

Affiliated to: Tribhuvan University

📄 License
This project is created for educational purposes as a 4th semester college project at Butwal Kalika Campus, affiliated with Tribhuvan University.
