# Faculty Evaluation System

Individual final project for **Web Systems Technologies (PHP and MySQL Development)**.
Built with PHP, MySQL, HTML, CSS and Bootstrap 5.

## Features
- Full CRUD (Create, Read, Update, Delete) for **Evaluations**, **Faculty** and **Students**
- Students rate faculty on 4 criteria (Teaching Skill, Communication, Punctuality, Professionalism) from 1 to 5, plus optional comments
- Dashboard with totals, faculty ranking (average rating) and a Chart.js bar chart
- Search evaluations by faculty, student or subject, and filter by faculty
- Responsive Bootstrap 5 layout (navbar, forms, tables, buttons)
- Prepared statements (SQL injection protection), output escaping (XSS) and CSRF tokens on every form

## Database design
Three related tables (see `database.sql`):

| Table | Purpose | Key fields |
|---|---|---|
| `faculty` | Teachers being evaluated | faculty_id (PK), full_name, department, email, position, date_added |
| `students` | People submitting evaluations | student_id (PK), student_no (UNIQUE), full_name, course, year_level |
| `evaluations` | One student rating one faculty | eval_id (PK), faculty_id (FK), student_id (FK), subject, 4 ratings, comments, date_evaluated |

`evaluations` uses foreign keys with `ON DELETE CASCADE`.

## Run locally (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\faculty_eval`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, create a database named `faculty_eval_db`.
4. Select that database, click **Import**, choose `database.sql`, then **Go**.
5. Open `http://localhost/faculty_eval/`.

## Deploy on InfinityFree
1. Create an account at https://www.infinityfree.net and a new Hosting Account.
2. Control Panel > **MySQL Databases**: create a database and copy the hostname, username, password and database name.
3. Edit `db_connect.php` with those four values.
4. Upload the whole project folder into `htdocs/` using **File Manager**.
5. Open InfinityFree's **phpMyAdmin**, select your database and **Import** `database.sql`.
6. Visit your live link and test every page.

## Project structure
```
db_connect.php        database connection (edit for hosting)
database.sql          tables + sample data
index.php             dashboard
evaluations.php       list / search / filter evaluations
evaluation_form.php   add + edit evaluation
evaluation_delete.php delete evaluation
faculty*.php          list, add/edit, delete faculty
student*.php          list, add/edit, delete students
includes/             init, helper functions, header, footer
```
