# DBMS-Course-Project
# Event Registration and Venue Scheduling System

## Project Overview

The Event Registration and Venue Scheduling System is a Database Management System developed as part of the DBMS Course Project at Woxsen University.

The system is designed to manage events, event categories, organizers, venues, schedules, participants, and event registrations in a structured relational database. It provides a centralized system for storing and managing event-related information while reducing data redundancy and improving the efficiency of event and venue management.

## Objectives

- Manage and organize different types of events.
- Maintain organizer and venue information.
- Schedule events at available venues.
- Manage participant information and event registrations.
- Maintain relationships between events, participants, organizers, and venues.
- Provide SQL queries for retrieving and analyzing event-related information.
- Provide a user interface for viewing, inserting, and deleting records.

## Main Features

- Event management
- Event category management
- Organizer management
- Venue management
- Event scheduling
- Participant management
- Event registration
- Registration and payment status tracking
- SQL-based event and registration queries
- Venue scheduling and availability management

## Database Design

The system consists of the following seven main tables:

1. **EVENT_CATEGORIES** – Stores event categories and their descriptions.
2. **ORGANIZERS** – Stores information about event organizers.
3. **VENUES** – Stores venue details such as location, capacity, and type.
4. **EVENTS** – Stores event information and its relationship with categories and organizers.
5. **EVENT_SCHEDULES** – Stores event dates, timings, and assigned venues.
6. **PARTICIPANTS** – Stores participant information.
7. **REGISTRATIONS** – Maintains registrations between participants and events.

The database follows a normalized relational structure with primary keys and foreign keys to maintain data integrity.

## Technologies Used

- **Database:** MySQL
- **Database Management:** phpMyAdmin
- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript
- **Local Server:** XAMPP
- **Development Environment:** Visual Studio Code
- **ER Diagram:** diagrams.net (draw.io)
- **Version Control:** Git and GitHub

## Project Structure

```text
DBMS-Course-Project/
│
├── Presentation/
│   ├── Presentation.pdf
│   ├── ER_Diagram.png
│   ├── SQL_File.sql
│   └── Screenshots/
│
├── Project-Report/
│   └── Project_Report.pdf
│
├── Database/
│   └── event_management.sql
│
├── Source-Code/
│   ├── index.php
│   ├── db.php
│   ├── events.php
│   ├── add_event.php
│   ├── venues.php
│   ├── participants.php
│   ├── registrations.php
│   ├── schedule.php
│   └── css/
│
├── ER-Diagram/
│   ├── ER_Diagram.png
│   └── ER_Diagram.drawio
│
└── README.md
```

## Database Relationships

The major relationships in the database are:

- One event category can have many events.
- One organizer can organize many events.
- One event can have multiple schedules.
- One venue can be assigned to multiple event schedules.
- One event can have multiple registrations.
- One participant can register for multiple events.
- The many-to-many relationship between participants and events is handled through the **REGISTRATIONS** table.

## Database Operations

The project demonstrates the following DBMS operations:

- Database and table creation
- Primary and foreign key constraints
- Data insertion
- Data updating
- Data deletion
- Record retrieval
- Multiple-table joins
- Aggregate functions
- Grouping and filtering
- Event and venue scheduling queries
- Registration management

Team Members
Ananey Sharma - 25WU0102017
Rudra Kumar - 25WU0101113
Prachi Singhania - 25WU0101099
Mithil D. Parikh - 25WU0101072

## Course Information

**Course:** Database Management Systems (DBMS)  
**Project:** DBMS Course Project  
**Project Title:** Design and Implementation of a Database Management System for Event Registration and Venue Scheduling System  
**University:** Woxsen University  
**Academic Year:** 2026–27

## How to Run the Project

1. Install and open **XAMPP**.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open **phpMyAdmin**.
4. Create/import the `event_management` database using the SQL file provided in the `Database` folder.
5. Copy the project source code into the XAMPP `htdocs` directory.
6. Make sure the database connection details in `db.php` match the local MySQL configuration.
7. Open the project in a browser using:

```text
http://localhost/event-management/
```

## Project Documentation

The repository contains:

- ER Diagram
- Database SQL script
- Source code
- Project presentation
- Project report
- UI screenshots

## Conclusion

The project provides a structured relational database solution for managing events, venues, schedules, participants, and registrations. It demonstrates the practical implementation of database design, normalization, SQL queries, constraints, and database connectivity through a web-based interface.
