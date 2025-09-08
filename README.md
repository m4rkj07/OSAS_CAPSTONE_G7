# SCHOOL MANAGEMENT SYSTEM: OFFICE OF THE SAFETY AND SECURITY, THE DEVELOPMENT OF REPORTING MANAGEMENT SYSTEM AND SCHOOL MAPPING FOR SECURE LEARNING ENVIRONMENT.
 

A web-based system designed to streamline the process of reporting, real time school mapping, managing, and monitoring incidents such as bullying and misconduct within an organization. This project was developed as part of the **OSAS Capstone Project** to provide a secure, efficient, and user-friendly platform for students, employees, and administrators.  

## 🚀 Features  

- **User Roles**  
  - **User**: Submit incident reports, track report status.  
  - **Admin**: Manage reports, update status, monitor trends.  
  - **Super Admin**: Full system access, role and account management.  

- **Reporting Module**  
  - Submit incident reports with details (type, description, location, evidence).  
  - Track status updates (pending, in-progress, completed).  
  - Support for ESI severity levels.  

- **Comment System**  
  - Users and admins can exchange comments within reports.  
  - Keeps a clear communication thread for each case.  

- **Dashboard & Analytics**  
  - Monthly summaries of report statuses.  
  - Growth rate comparison by month.  
  - Severity level breakdowns with trend insights.  
  - Incident type distribution and charts.  

- **Prefect Module**  
  - Specialized module for incidents tagged as **Prefect-related**.  

- **Notifications**  
  - Real-time updates via WebSockets.  
  - Status and comment alerts.  

## 🛠️ Technologies Used  

- **Framework**: Laravel 11 (PHP 8.2)  
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js, SweetAlert2  
- **Database**: MySQL  
- **Realtime**: Laravel WebSockets / Pusher  
- **Authentication**: Laravel Breeze (roles & permissions)  


## 📂 Project Structure  

OSAS_CAPSTONE_G7/
│── app/                # Application logic (Models, Controllers, Policies)
│── resources/views/    # Blade templates (Admin & User views)
│── public/             # Assets (CSS, JS, Images)
│── database/           # Migrations, Seeders, Factories
│── routes/             # Web & API routes
│── config/             # Laravel configurations
│── .env.example        # Example environment file
│── composer.json       # PHP dependencies
│── package.json        # JS dependencies

⚙️ Getting Started

Prerequisites
* PHP 8.2+
* Composer
* Node.js & NPM
* MySQL

Installation
# Clone the repository
git clone https://github.com/m4rkj07/OSAS_CAPSTONE_G7.git

cd OSAS_CAPSTONE_G7

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install && npm run dev

# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Setup database in .env then run migrations
php artisan migrate --seed

# Start the server
php artisan serve

📊 Usage

*Register or log in as a user to create reports.
*Admins and Super Admins can view all submitted reports in their dashboards.
*Use the filter/search options in the report table to organize and locate incidents.
*Access charts and analytics on the admin dashboard for report insights.

🧪 Testing

# Run feature and unit tests
php artisan test

🤝 Contributing
*Fork the repository
*Create your feature branch (git checkout -b feature/YourFeature)
*Commit your changes (git commit -m 'Add some feature')
*Push to the branch (git push origin feature/YourFeature)
*Create a Pull Request

📜 License
This project is licensed under the MIT License.

📧 Contact
Developer: Mark Joseph Villena

Email: bcposas@gmail.com.com

GitHub: m4rkj07
