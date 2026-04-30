# Data-Driven-Website
Inventory Management website project I created for CIT 253.
# Inventory Management System (Final Project)

## Project Overview

This project is a web-based Inventory Management System developed as a final project for CIT 253 (Data Driven Web Pages). The application allows users to manage inventory items, track item locations, record transactions, and manage shipping addresses through a dynamic and user-friendly interface.

The system is designed to simulate a real-world inventory environment, focusing on organization, usability, and database-driven functionality.

---

## Technologies Used

* PHP (server-side scripting)
* MySQL (database management)
* HTML5 / CSS3 (structure and styling)
* JavaScript (AJAX for dynamic updates)
* Apache (local server environment)

---

## Features

### User Authentication

* Secure login system
* Session-based access control

### Inventory Management

* Add new inventory items
* Deactivate/reactivate items (instead of deleting)
* Assign units of measure
* Maintain clean and structured item records

### Location Tracking

* Assign items to specific locations
* View inventory grouped by location
* Track total supply per location

### Transaction System

* Record inventory transactions (incoming/outgoing)
* View transaction history
* Maintain accurate inventory flow

### Shipping Address Management

* Create and manage shipping addresses
* Update addresses dynamically using AJAX
* Select saved addresses for orders

### AJAX Integration

* Dynamic shipping address updates without page reloads
* Improved user experience and responsiveness

---

## Database Structure

The system uses a relational database design with multiple connected tables, including:

* Users
* Items
* ItemLocations
* Locations
* Transactions
* ShippingAddresses
* UnitsOfMeasure

Key design considerations:

* Separation of concerns (e.g., UnitOfMeasure as its own table)
* Use of relationships (foreign keys) to maintain data integrity
* Support for active/inactive inventory items

---

## How to Run the Project Locally

### Requirements:

* Apache Server (XAMPP, WAMP, or similar)
* MySQL Database

### Steps:

1. Clone or download this repository
2. Move the project folder into your server directory (e.g., `htdocs`)
3. Start Apache and MySQL
4. Import the provided `.sql` file into your database
5. Update database connection settings in the project (if needed)
6. Open your browser and navigate to:

   ```
   http://localhost/your-project-folder
   ```

---

## 📸 Screenshots

*(Add screenshots here for extra credit and clarity)*

* Dashboard
* Inventory View
* Transactions Page
* Shipping Address Management

---

## Key Learning Outcomes

* Building a full-stack web application using PHP and MySQL
* Designing and implementing relational databases
* Using AJAX for improved user experience
* Structuring a multi-page web application
* Applying real-world inventory tracking concepts

---

## Limitations

* Designed for local hosting (not deployed to a live server)
* Basic authentication (no advanced security features)
* No external API integrations

---

## Future Improvements

* Full deployment to a live server
* Enhanced UI/UX design
* Role-based user permissions
* Advanced reporting and analytics
* API integration for shipping or inventory services

---

## Author

Jessie Conn
CIT 253 – Data Driven Web Pages

---

## License

This project is for educational purposes only.
