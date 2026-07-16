---

title: Project Scope
document: Business Requirements Document (BRD)
project: Restaurant Booking Management System (RBMS)
version: 1.0
status: Draft
author: Waris Akbari
last_updated: 2026-07-16
------------------------

# 6. Project Scope

## 6.1 Introduction

This chapter defines the scope of Version 1.0 of the Restaurant Booking Management System (RBMS). It identifies the features that will be delivered in the first release and clearly distinguishes them from features planned for future versions.

Clearly defining the project scope ensures that development remains focused on delivering a complete, stable, and maintainable Minimum Viable Product (MVP).

---

## 6.2 Project Scope Statement

The Restaurant Booking Management System (RBMS) will provide a centralized web-based platform that enables customers to reserve restaurant tables online while allowing restaurant staff to manage reservations, tables, customers, and restaurant settings through an administrative dashboard.

The system will replace the restaurant's manual reservation process with an efficient digital solution.

---

## 6.3 In Scope (Version 1.0)

### Customer Features

* User registration
* User authentication
* Customer profile management
* Search available reservation times
* Create reservation
* View reservation history
* Modify reservation
* Cancel reservation

---

### Reservation Management

* Reservation calendar
* Reservation validation
* Reservation status management
* Reservation search
* Reservation filtering

---

### Table Management

* Create tables
* Edit table information
* Delete tables
* Table capacity management
* Table status management

---

### Customer Management

* Customer directory
* Customer search
* Customer details
* Reservation history

---

### Restaurant Administration

* Restaurant information
* Opening hours
* Holiday management
* Reservation duration settings

---

### Reporting

* Daily reservation summary
* Reservation statistics
* Customer statistics
* Table utilization overview

---

### Security

* Authentication
* Authorization
* Role-based access control
* Password management
* Activity logging

---

## 6.4 Out of Scope (Version 1.0)

The following features are intentionally excluded from the initial release:

* Online food ordering
* Online payment processing
* Delivery management
* Takeaway order management
* Kitchen order management
* Employee scheduling
* Loyalty program
* Customer reward points
* Mobile application
* Multi-branch management
* QR code menu ordering
* AI-based table recommendations
* Third-party reservation platform integration

These features may be considered for future releases.

---

## 6.5 Assumptions

The project assumes that:

* The restaurant operates from a single location.
* Internet connectivity is available during business hours.
* Staff members receive basic system training.
* Customers have access to an internet-enabled device for online reservations.
* Restaurant operating hours are managed by administrators.

---

## 6.6 Project Constraints

The first version of the system is constrained by the following factors:

* Single restaurant branch only.
* Web application only.
* English language interface.
* No external payment gateway integration.
* No third-party reservation synchronization.

---

## 6.7 Scope Validation

The project scope will be considered complete when:

* Customers can successfully create, modify, and cancel reservations.
* Staff can manage reservations and tables.
* Administrators can configure restaurant settings.
* Reservation conflicts are prevented through system validation.
* Reports provide meaningful operational information.
* All core business objectives defined in the BRD are supported.

---

## 6.8 Development Impact

The project scope defined in this chapter establishes the boundaries for Version 1.0.

This chapter directly influences:

* Software Requirements Specification (SRS)
* Database Design
* API Specification
* User Interface Design
* Development Roadmap
* Testing Strategy

Any feature not listed in the "In Scope" section should not be implemented during Version 1.0 unless the scope is formally revised.

