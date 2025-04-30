# Number Flags API Documentation

## Overview

The Number Flags API allows coordinators and tellers to manage flagged numbers for specific schedules, dates, and locations. Flagged numbers can be marked as "sold out" (no more bets accepted) or "low win" (reduced payout) for risk management.

## Endpoints

### List Number Flags

- **URL**: `/api/number-flags`
- **Method**: `GET`
- **Description**: Get a list of number flags for the current user's location
- **Authentication**: Required
- **Query Parameters**:
  - `date` (optional): Filter by date in Y-m-d format
  - `type` (optional): Filter by type ('sold_out' or 'low_win')
  - `schedule_id` (optional): Filter by schedule ID
  - `is_active` (optional): Filter by active status ('true' or 'false')
  - `search` (optional): Search by number
  - `per_page` (optional): Number of items per page
- **Response**:
  ```json
  {
    "status": true,
    "message": "Number flags retrieved successfully",
    "data": [
      {
        "id": 1,
        "number": "123",
        "schedule_id": 1,
        "date": "2025-04-30",
        "location_id": 1,
        "type": "sold_out",
        "is_active": true,
        "created_at": "2025-04-30T10:30:00.000000Z",
        "updated_at": "2025-04-30T10:30:00.000000Z",
        "schedule": {
          "id": 1,
          "name": "Morning Draw",
          "draw_time": "10:30:00"
        },
        "location": {
          "id": 1,
          "name": "Main Office"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1,
      "next_page_url": null,
      "prev_page_url": null
    }
  }
  ```

### Create Number Flag

- **URL**: `/api/number-flags`
- **Method**: `POST`
- **Description**: Create a new number flag
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "number": "123",
    "schedule_id": 1,
    "date": "2025-04-30",
    "type": "sold_out"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Number flag created successfully",
    "data": {
      "id": 1,
      "number": "123",
      "schedule_id": 1,
      "date": "2025-04-30",
      "location_id": 1,
      "type": "sold_out",
      "is_active": true,
      "created_at": "2025-04-30T10:30:00.000000Z",
      "updated_at": "2025-04-30T10:30:00.000000Z",
      "schedule": {
        "id": 1,
        "name": "Morning Draw",
        "draw_time": "10:30:00"
      },
      "location": {
        "id": 1,
        "name": "Main Office"
      }
    }
  }
  ```

### Get Number Flag

- **URL**: `/api/number-flags/{id}`
- **Method**: `GET`
- **Description**: Get a specific number flag
- **Authentication**: Required
- **Response**:
  ```json
  {
    "status": true,
    "message": "Number flag retrieved successfully",
    "data": {
      "id": 1,
      "number": "123",
      "schedule_id": 1,
      "date": "2025-04-30",
      "location_id": 1,
      "type": "sold_out",
      "is_active": true,
      "created_at": "2025-04-30T10:30:00.000000Z",
      "updated_at": "2025-04-30T10:30:00.000000Z",
      "schedule": {
        "id": 1,
        "name": "Morning Draw",
        "draw_time": "10:30:00"
      },
      "location": {
        "id": 1,
        "name": "Main Office"
      }
    }
  }
  ```

### Update Number Flag

- **URL**: `/api/number-flags/{id}`
- **Method**: `PUT` or `PATCH`
- **Description**: Update a specific number flag
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "type": "low_win",
    "is_active": true
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Number flag updated successfully",
    "data": {
      "id": 1,
      "number": "123",
      "schedule_id": 1,
      "date": "2025-04-30",
      "location_id": 1,
      "type": "low_win",
      "is_active": true,
      "created_at": "2025-04-30T10:30:00.000000Z",
      "updated_at": "2025-04-30T10:30:00.000000Z",
      "schedule": {
        "id": 1,
        "name": "Morning Draw",
        "draw_time": "10:30:00"
      },
      "location": {
        "id": 1,
        "name": "Main Office"
      }
    }
  }
  ```

### Delete Number Flag

- **URL**: `/api/number-flags/{id}`
- **Method**: `DELETE`
- **Description**: Deactivate a number flag (soft delete)
- **Authentication**: Required
- **Response**:
  ```json
  {
    "status": true,
    "message": "Number flag deactivated successfully",
    "data": null
  }
  ```

## Business Rules

1. Number flags are location-specific - users can only see and manage flags for their own location
2. A number can only be flagged once per schedule, date, and location with the same type
3. If a number is already flagged but inactive, creating the same flag will reactivate it
4. Deleting a flag actually performs a soft delete by setting `is_active` to false
5. The two flag types serve different purposes:
   - `sold_out`: No more bets can be placed on this number for the specified schedule and date
   - `low_win`: Bets can still be placed, but payouts may be reduced for risk management

## Integration with Betting Process

When a teller attempts to place a bet, the system should check if the number is flagged as "sold_out" for that schedule, date, and location. If so, the bet should be rejected with an appropriate error message.

For "low_win" flags, the system should accept the bet but may apply special rules for payout calculation when the bet wins.
