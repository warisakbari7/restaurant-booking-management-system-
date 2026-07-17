# 6. Business Rules

## 6.1 Introduction

This chapter defines the business rules that govern how the Restaurant Booking Management System (RBMS) operates. These rules ensure consistent behavior across the application and provide the foundation for system validation and business logic.

---

## 6.2 Customer Rules

BR-01. Every customer must register before creating a reservation.

BR-02. Each customer account must use a unique email address.

BR-03. A customer may have only one active future reservation at a time.

BR-04. Customers may update or cancel only their own future reservations.

BR-05. Customers cannot modify or cancel completed reservations.

---

## 6.3 Reservation Rules

BR-06. Reservations can only be made during the restaurant's opening hours.

BR-07. The default reservation duration is 2 hours.

BR-08. A reservation must include at least 1 guest and no more than 8 guests.

BR-09. The system must verify table availability before confirming a reservation.

BR-10. Double booking of the same table for overlapping time periods is not allowed.

BR-11. Reservations are automatically assigned the **Confirmed** status after successful creation.

BR-12. Customers may cancel a reservation up to 2 hours before the reservation start time.

BR-13. If a customer has not checked in within 15 minutes of the reservation time, staff may mark the reservation as **No Show**.

BR-14. Completed reservations cannot be edited.

---

## 6.4 Table Rules

BR-15. Every table has a unique table number.

BR-16. Each table has a fixed seating capacity.

BR-17. A table can have only one status at any given time.

BR-18. Tables marked **Out of Service** cannot be reserved.

---

## 6.5 Staff Rules

BR-19. Receptionists may create reservations on behalf of customers.

BR-20. Managers have access to all reservation and reporting features.

BR-21. System administrators manage user accounts and system configuration.

---

## 6.6 Security Rules

BR-22. Users must be authenticated before accessing protected features.

BR-23. Access to system functions is controlled by user roles and permissions.

BR-24. Passwords must be stored in encrypted form.

BR-25. Important system activities must be recorded in the audit log.

---

## 6.7 Summary

These business rules define how the Restaurant Booking Management System behaves in everyday operations. They will be implemented through database constraints, backend business logic, authorization policies, and frontend validation during the development phase.

