# Authentication Module

## Overview

The Authentication Module provides secure user registration, login, logout, and profile access for all users of the Restaurant Booking Management System (RBMS).

---

# FR-001 – User Registration

## Description

The system shall allow a new customer to create an account by providing the required registration information.

---

## Actors

* Guest User

---

## Preconditions

* The user is not authenticated.
* The user does not already have an account with the same email address.

---

## Input Data

| Field            | Required | Validation                    |
| ---------------- | -------- | ----------------------------- |
| First Name       | Yes      | Maximum 100 characters        |
| Last Name        | Yes      | Maximum 100 characters        |
| Email            | Yes      | Valid email format and unique |
| Phone Number     | Yes      | Valid phone number format     |
| Password         | Yes      | Minimum 8 characters          |
| Confirm Password | Yes      | Must match Password           |

---

## Main Flow

1. The guest opens the registration page.
2. The guest enters the required information.
3. The guest submits the registration form.
4. The system validates all input.
5. The system verifies that the email address is unique.
6. The system hashes the password.
7. The system creates a new customer account.
8. The system assigns the **Customer** role.
9. The system displays a success message.
10. The user is redirected to the login page.

---

## Alternative Flows

### AF-001

If the email address already exists:

* The account is not created.
* The system displays an appropriate validation error.

### AF-002

If validation fails:

* The account is not created.
* The system highlights the invalid fields.
* The user can correct the information and try again.

---

## Postconditions

* A new customer account exists in the system.
* The password is securely stored as a hash.
* The customer can log in using the registered email and password.

---

## Business Rules

* BR-01
* BR-02
* BR-22
* BR-24

---

## Acceptance Criteria

* A guest can successfully register with valid information.
* Duplicate email addresses are rejected.
* Passwords are never stored in plain text.
* Required fields are validated before the account is created.
---

# FR-002 – User Login

## Description

The system shall allow a registered customer to securely authenticate using their email address and password.

---

## Actors

* Registered Customer

---

## Preconditions

* The customer has a registered account.
* The customer is not already authenticated.
* The customer account is active.

---

## Input Data

| Field    | Required | Validation           |
| -------- | -------- | -------------------- |
| Email    | Yes      | Valid email format   |
| Password | Yes      | Minimum 8 characters |

---

## Main Flow

1. The customer opens the login page.
2. The customer enters their email address and password.
3. The customer submits the login form.
4. The system validates the input.
5. The system searches for the customer by email.
6. The system verifies the password using the stored password hash.
7. If the credentials are valid, the system authenticates the customer.
8. The system creates a new authenticated session or API token.
9. The customer is redirected to the dashboard.

---

## Alternative Flows

### AF-001 – Invalid Credentials

* The email address or password is incorrect.
* The system displays an error message.
* The customer remains on the login page.

### AF-002 – Validation Failure

* Required fields are missing or invalid.
* The system displays validation errors.
* The customer can correct the input and try again.

---

## Postconditions

* The customer is authenticated.
* A valid session or API token exists.
* The customer can access protected features.

---

## Business Rules

* BR-03
* BR-04
* BR-22

---

## Acceptance Criteria

* A registered customer can log in with valid credentials.
* Invalid credentials are rejected.
* Passwords are never compared in plain text.
* Protected pages require authentication.


