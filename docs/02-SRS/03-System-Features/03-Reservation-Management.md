# Reservation Management Module

## Overview

The Reservation Management Module enables customers to create, view, update, and cancel reservations while allowing restaurant staff to manage table assignments and reservation status.

---

# FR-005 – Create Reservation

## Description

The system shall allow an authenticated customer to create a reservation by selecting a reservation date, reservation time, and the number of guests.

---

## Actors

* Authenticated Customer

---

## Preconditions

* The customer is authenticated.
* The customer must not have another active reservation that overlaps with the requested reservation date and time.
* The selected reservation date and time are within restaurant operating hours.

---

## Input Data

| Field            | Required | Validation                     |
| ---------------- | -------- | ------------------------------ |
| Reservation Date | Yes      | Must not be in the past        |
| Reservation Time | Yes      | Must be within operating hours |
| Number of Guests | Yes      | Between 1 and 8                |
| Special Requests | No       | Maximum 500 characters         |

---

## Main Flow

1. The customer opens the reservation page.
2. The customer selects a reservation date.
3. The customer selects an available reservation time.
4. The customer enters the number of guests.
5. The customer optionally enters special requests.
6. The customer submits the reservation.
7. The system validates all input.
8. The system checks that the customer has no active future reservation.
9. The system checks whether a suitable table is available.
10. The system creates the reservation with the status **Pending**.
11. The system displays a confirmation message.

---

## Alternative Flows

### AF-001 – Table Not Available

* No suitable table is available.
* The reservation is not created.
* The system asks the customer to choose another time or date.

### AF-002 – Existing Future Reservation

* The customer already has an active future reservation.
* The reservation is rejected.
* The system informs the customer that only one active future reservation is allowed.

### AF-003 – Validation Failure

* One or more input fields are invalid.
* The reservation is not created.
* The system displays validation errors.

---

## Postconditions

* A new reservation exists in the system.
* The reservation status is set to **Pending**.
* The reservation is associated with the authenticated customer.

---

## Business Rules

* BR-05
* BR-06
* BR-08
* BR-10

---

## Acceptance Criteria

* An authenticated customer can create a reservation.
* Reservations cannot be made in the past.
* Reservations are limited to a maximum of eight guests.
* Customers cannot create overlapping active reservations.
* Reservations are created with the **Pending** status.

---

# FR-006 – View Reservations

## Description

The system shall allow an authenticated customer to view all of their reservations.

---

## Actors

* Authenticated Customer

---

## Preconditions

* The customer is authenticated.

---

## Main Flow

1. The customer opens the Reservations page.
2. The system retrieves all reservations belonging to the authenticated customer.
3. The system displays each reservation with its details, including:

   * Reservation Number
   * Reservation Date
   * Reservation Time
   * Number of Guests
   * Assigned Table (if assigned)
   * Reservation Status
4. The customer can select a reservation to view its full details.

---

## Alternative Flows

### AF-001 – No Reservations Found

* The customer has no reservations.
* The system displays an informative message indicating that no reservations exist.

---

## Postconditions

* The customer can view all current and past reservations associated with their account.

---

## Business Rules

* BR-03
* BR-11

---

## Acceptance Criteria

* Customers can view only their own reservations.
* Reservation details are displayed accurately.
* Customers cannot view reservations belonging to other users.

---

# FR-007 – Update Reservation

## Description

The system shall allow an authenticated customer to update an existing future reservation before the modification deadline.

---

## Actors

* Authenticated Customer

---

## Preconditions

* The customer is authenticated.
* The reservation belongs to the authenticated customer.
* The reservation has not been cancelled or completed.
* The modification request is made at least 2 hours before the reservation time.

---

## Editable Fields

| Field              | Editable |
| ------------------ | -------- |
| Reservation Date   | Yes      |
| Reservation Time   | Yes      |
| Number of Guests   | Yes      |
| Special Requests   | Yes      |
| Reservation Status | No       |
| Assigned Table     | No       |

---

## Main Flow

1. The customer opens the reservation details page.
2. The customer selects **Edit Reservation**.
3. The customer updates one or more editable fields.
4. The customer submits the changes.
5. The system validates the updated information.
6. The system checks that the updated reservation does not overlap with another active reservation belonging to the same customer.
7. The system checks table availability for the new reservation details.
8. The system automatically assigns the most suitable available table.
9. The system saves the updated reservation.
10. The system displays a success message.

---

## Alternative Flows

### AF-001 – Modification Deadline Passed

* The reservation starts in less than 2 hours.
* The system rejects the modification request.
* The customer is informed that the modification deadline has passed.

### AF-002 – No Suitable Table Available

* No suitable table is available for the updated reservation.
* The changes are not saved.
* The customer is asked to choose another date or time.

### AF-003 – Validation Failure

* One or more fields contain invalid data.
* The system displays validation errors.
* The customer can correct the information and try again.

---

## Postconditions

* The reservation information is updated.
* The assigned table is re-evaluated based on the updated reservation details.
* The reservation remains associated with the authenticated customer.

---

## Business Rules

* BR-06
* BR-08
* BR-10
* BR-11

---

## Acceptance Criteria

* Customers can edit only their own future reservations.
* Reservations cannot be modified within 2 hours of the reservation time.
* The system automatically checks table availability after any relevant change.
* Customers cannot modify the reservation status or assigned table.

---

# FR-008 – Cancel Reservation

## Description

The system shall allow an authenticated customer to cancel a future reservation before the cancellation deadline.

---

## Actors

* Authenticated Customer

---

## Preconditions

* The customer is authenticated.
* The reservation belongs to the authenticated customer.
* The reservation has not already been cancelled or completed.
* The cancellation request is made at least 2 hours before the reservation time.

---

## Main Flow

1. The customer opens the reservation details page.
2. The customer selects **Cancel Reservation**.
3. The system asks the customer to confirm the cancellation.
4. The customer confirms the action.
5. The system verifies that the cancellation deadline has not passed.
6. The system changes the reservation status to **Cancelled**.
7. The system releases the assigned table.
8. The system displays a cancellation confirmation message.

---

## Alternative Flows

### AF-001 – Cancellation Deadline Passed

* The reservation starts in less than 2 hours.
* The cancellation request is rejected.
* The system informs the customer that the cancellation deadline has passed.

### AF-002 – Reservation Already Cancelled

* The reservation has already been cancelled.
* The system informs the customer that no further action is required.

---

## Postconditions

* The reservation status is updated to **Cancelled**.
* The assigned table becomes available for future reservations.
* The customer can view the cancelled reservation in their reservation history.

---

## Business Rules

* BR-08
* BR-11
* BR-12

---

## Acceptance Criteria

* Customers can cancel only their own reservations.
* Reservations cannot be cancelled within 2 hours of the reservation time.
* Cancelled reservations remain in the system for historical purposes.
* The assigned table is released after cancellation.



---

# FR-009 – Reservation History

## Description

The system shall allow an authenticated customer to view their complete reservation history, including active, completed, cancelled, and no-show reservations.

---

## Actors

* Authenticated Customer

---

## Preconditions

* The customer is authenticated.

---

## Main Flow

1. The customer opens the Reservation History page.
2. The system retrieves all reservations belonging to the authenticated customer.
3. The system sorts reservations by reservation date in descending order.
4. The system displays the reservation history.

---

## Displayed Information

* Reservation Number
* Reservation Date
* Reservation Time
* Number of Guests
* Reservation Status
* Assigned Table (if applicable)

---

## Alternative Flows

### AF-001 – No Reservation History

* The customer has no reservation history.
* The system displays an informative message.

---

## Postconditions

* The customer can review all previous and current reservations.

---

## Business Rules

* BR-03

---

## Acceptance Criteria

* Customers can view only their own reservation history.
* Reservations are displayed in descending date order.
* Historical reservations cannot be modified.


---

# FR-010 – Check Table Availability

## Description

The system shall determine whether a suitable table is available for the requested reservation date, time, and number of guests before creating or updating a reservation.

---

## Actors

* Authenticated Customer
* Receptionist

---

## Preconditions

* Reservation date, time, and guest count have been provided.

---

## Main Flow

1. The system receives the requested reservation information.
2. The system searches for tables that can accommodate the requested number of guests.
3. The system excludes tables already reserved during the requested time period.
4. The system selects the smallest suitable available table.
5. The system returns the availability result.

---

## Alternative Flows

### AF-001 – No Suitable Table Found

* No available table satisfies the request.
* The system informs the user that no reservation can be made for the selected date and time.

---

## Postconditions

* The availability result is returned.
* If a table is found, it is eligible for assignment during reservation creation or update.

---

## Business Rules

* BR-06
* BR-10
* BR-11

---

## Acceptance Criteria

* The system checks availability before creating or updating a reservation.
* The smallest suitable available table is selected.
* Tables with conflicting reservations are excluded.
* Availability results are based on the latest reservation data.

