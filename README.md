# Customer Directory - PHP & MySQL Web App

A simple yet feature-rich customer directory web application built using **PHP**, **MySQL**, and **Bootstrap 5**. This application allows you to **search**, **sort**, **paginate**, and **view customer data** in a clean and responsive interface.

---

## 🚀 Features

- 🔍 **Search**: Filter customers by first name, last name, city, or country.
- 🔃 **Sort**: Sort by columns like name, city, country, etc. in ascending or descending order.
- 📄 **Pagination**: Navigate through customer records with pagination.
- 📊 **Dashboard Stats**: View total number of customers, pages, and last updated date.
- 🎨 **Modern UI**: Built with Bootstrap 5, icons from Bootstrap Icons, and Google Fonts.

---

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, Bootstrap 5, Bootstrap Icons
- **Backend**: PHP (MySQLi)
- **Database**: MySQL
- **Fonts & Animations**: Poppins, Animate.css

---

## 📁 File Structure

```bash
project/
│
├── config.php              # Database configuration
├── index.php               # Main application logic and frontend
├── README.md               # Project documentation
└── assets/ (optional)      # For custom CSS, JS, or images
```

---

## ⚙️ Setup Instructions

1. **Clone or download this repository**:
   ```bash
   git clone https://github.com/yourusername/customer-directory.git
   cd customer-directory
   ```

2. **Database Setup**:
   - Import the following SQL to create the `customers` table:

     ```sql
     CREATE TABLE `customers` (
       `id` INT AUTO_INCREMENT PRIMARY KEY,
       `first_name` VARCHAR(100),
       `last_name` VARCHAR(100),
       `city` VARCHAR(100),
       `country` VARCHAR(100),
       `mobile_number` VARCHAR(20),
       `date_n_time` DATETIME DEFAULT CURRENT_TIMESTAMP
     );
     ```

   - Populate it with sample records or your own data.

3. **Update your `config.php`**:
   Set your DB credentials:
   ```php
   $conn = new mysqli("localhost", "username", "password", "database_name");
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   ```

4. **Run the App**:
   - Open `index.php` in a local or remote server environment.
   - Example URL: `http://localhost/customer-directory/index.php`

---

## ✅ To Do / Future Improvements

- Add customer **CRUD operations** (Create, Update, Delete)
- Implement **login authentication**
- Add **CSV/Excel export** option
- Improve **mobile responsiveness**
- Integrate **flag icons** using country codes (optional)

---

## 📸 Screenshot

*(Include a screenshot of your app here)*

---

## 🙋‍♀️ Author

**Mahek Bepari**  
Final Year BCA Student  
Intern @ Ultimez Technology  
GitHub: [yourusername](https://github.com/yourusername)

---

## 📜 License

This project is open-source and free to use for educational and non-commercial purposes.