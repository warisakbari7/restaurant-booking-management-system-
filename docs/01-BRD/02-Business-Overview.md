---

title: Business Overview
document: Business Requirements Document (BRD)
project: Restaurant Booking Management System (RBMS)
version: 1.0
status: Draft
author: Waris Akbari
last_updated: 2026-07-13
------------------------

# 2. Business Overview

## 2.1 Business Profile

Bella Vista Restaurant is a fictional medium-sized restaurant created for this project to simulate a real business environment. The restaurant specializes in Italian and Mediterranean cuisine and offers both lunch and dinner services. It aims to provide customers with a modern dining experience supported by an efficient digital reservation system.

The restaurant currently manages reservations manually through phone calls and handwritten notes. As customer demand has increased, these manual processes have become difficult to manage and have resulted in operational inefficiencies.

---

## 2.2 Business Information

| Item                     | Value                    |
| ------------------------ | ------------------------ |
| Business Name            | Bella Vista Restaurant   |
| Business Type            | Casual Dining Restaurant |
| Number of Branches       | 1                        |
| Floors                   | 2                        |
| Indoor Tables            | 20                       |
| Outdoor Tables           | 5                        |
| Total Tables             | 25                       |
| Maximum Seating Capacity | 96 Guests                |
| Operating Days           | Monday – Sunday          |
| Opening Hours            | 11:00 AM                 |
| Closing Hours            | 11:00 PM                 |
| Reservation Duration     | 2 Hours (Default)        |
| Reservation Grace Period | 15 Minutes               |
| Walk-in Customers        | Supported                |
| Private Events           | Supported                |

---

## 2.3 Business Services

The restaurant provides the following services:

* Table reservations
* Walk-in dining
* Lunch service
* Dinner service
* Private event reservations
* Family dining
* Group reservations

The first version of the Restaurant Booking Management System (RBMS) will focus on reservation and table management. Food ordering, online payments, and delivery management are outside the scope of Version 1.0.

---

## 2.4 Current Business Process

The existing reservation process is entirely manual.

1. A customer contacts the restaurant by phone.
2. A staff member checks the reservation notebook.
3. Available tables are identified manually.
4. Reservation details are written in the notebook.
5. Customers receive verbal confirmation.
6. Upon arrival, staff manually locate the reservation and assign a table.

This process depends heavily on staff experience and provides no real-time overview of table availability.

---

## 2.5 Current Business Challenges

The restaurant experiences several operational challenges due to the manual reservation process.

### Reservation Issues

* Double-booking of tables.
* Missing or incomplete reservation records.
* Difficulty updating reservations.
* Time-consuming reservation management.

### Operational Issues

* Inefficient table utilization.
* Limited visibility of table availability.
* Difficulty managing peak hours.
* No centralized customer information.

### Customer Experience Issues

* Long confirmation times.
* Limited booking channels.
* Lack of reservation history.
* Inconsistent customer communication.

---

## 2.6 Proposed Business Solution

The Restaurant Booking Management System (RBMS) will replace the manual reservation process with a centralized web-based platform.

Customers will be able to:

* Search for available reservation times.
* Create reservations online.
* View upcoming reservations.
* Modify reservations.
* Cancel reservations within the allowed cancellation period.

Restaurant administrators will be able to:

* Manage tables.
* Manage reservations.
* Manage customers.
* Configure restaurant settings.
* View reservation statistics.
* Monitor daily operations through an administrative dashboard.

---

## 2.7 Expected Business Benefits

Implementing the Restaurant Booking Management System is expected to provide the following benefits:

### Operational Benefits

* Reduced manual workload.
* Improved reservation accuracy.
* Better table utilization.
* Faster reservation processing.
* Centralized business information.

### Customer Benefits

* Faster booking process.
* Improved booking accuracy.
* Convenient online reservations.
* Better communication.
* Improved overall customer experience.

### Business Benefits

* Increased operational efficiency.
* Improved reporting capabilities.
* Better resource planning.
* Scalable digital platform for future expansion.

---

## 2.8 Development Impact

This chapter establishes the business context that will guide the remainder of the project.

The information defined here will directly influence:

* Business Rules
* Software Requirements Specification (SRS)
* Database Design
* User Roles and Permissions
* Reservation Workflow
* API Design
* User Interface Design

All future technical decisions should remain consistent with the business model described in this chapter.

