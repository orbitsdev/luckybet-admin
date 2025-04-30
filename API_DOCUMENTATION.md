# LuckyBet Admin API Documentation

This document outlines the available API endpoints for the LuckyBet Admin application.

## Authentication

### Register
- **URL**: `/api/register`
- **Method**: `POST`
- **Description**: Register a new user
- **Request Body**:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "password": "password",
    "password_confirmation": "password"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "User registered successfully",
    "data": {
      "access_token": "1|abcdefghijklmnopqrstuvwxyz",
      "token_type": "Bearer",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "username": "johndoe",
        "role": "teller",
        "location_id": 1,
        "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
      }
    }
  }
  ```

### Login
- **URL**: `/api/login`
- **Method**: `POST`
- **Description**: Login a user
- **Request Body**:
  ```json
  {
    "email": "john@example.com",
    "password": "password"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Success",
    "data": {
      "access_token": "1|abcdefghijklmnopqrstuvwxyz",
      "token_type": "Bearer",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "username": "johndoe",
        "role": "teller",
        "location_id": 1,
        "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
      }
    }
  }
  ```

### Get Current User
- **URL**: `/api/user`
- **Method**: `GET`
- **Description**: Get the current authenticated user
- **Authentication**: Required
- **Response**:
  ```json
  {
    "status": true,
    "message": "User retrieved successfully",
    "data": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "username": "johndoe",
      "role": "teller",
      "location_id": 1,
      "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
    }
  }
  ```

### Logout
- **URL**: `/api/logout`
- **Method**: `POST`
- **Description**: Logout the current user
- **Authentication**: Required
- **Response**:
  ```json
  {
    "status": true,
    "message": "Logged out successfully",
    "data": null
  }
  ```

## Betting

### Available Draws
- **URL**: `/api/draws/available`
- **Method**: `GET`
- **Description**: Get a list of available draws
- **Authentication**: Required
- **Query Parameters**:
  - `date` (optional): Date in Y-m-d format
- **Response**:
  ```json
  {
    "status": true,
    "message": "Available draws retrieved successfully",
    "data": [
      {
        "id": 1,
        "draw_date": "2025-04-30",
        "draw_time": "10:30:00",
        "type": "S3",
        "is_open": true
      }
    ]
  }
  ```

### Place Bet
- **URL**: `/api/teller/bet`
- **Method**: `POST`
- **Description**: Place a new bet
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "draw_id": 1,
    "bet_number": "123",
    "amount": 50.00,
    "is_combination": false
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Bet placed successfully",
    "data": {
      "id": 1,
      "ticket_id": "ABC123XYZ",
      "draw_id": 1,
      "bet_number": "123",
      "amount": 50.00,
      "status": "active",
      "bet_date": "2025-04-30",
      "is_combination": false,
      "draw": {
        "id": 1,
        "draw_date": "2025-04-30",
        "draw_time": "10:30:00",
        "type": "S3"
      }
    }
  }
  ```

### List Bets
- **URL**: `/api/teller/bets`
- **Method**: `GET`
- **Description**: Get a list of bets for the current teller
- **Authentication**: Required
- **Query Parameters**:
  - `date` (optional): Date in Y-m-d format
  - `status` (optional): Filter by status (active, won, lost, cancelled)
  - `search` (optional): Search by ticket_id or bet_number
  - `per_page` (optional): Number of items per page
- **Response**:
  ```json
  {
    "status": true,
    "message": "Bets retrieved successfully",
    "data": [
      {
        "id": 1,
        "ticket_id": "ABC123XYZ",
        "draw_id": 1,
        "bet_number": "123",
        "amount": 50.00,
        "status": "active",
        "bet_date": "2025-04-30",
        "is_combination": false,
        "draw": {
          "id": 1,
          "draw_date": "2025-04-30",
          "draw_time": "10:30:00",
          "type": "S3"
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

### Cancel Bet
- **URL**: `/api/teller/bet/cancel`
- **Method**: `POST`
- **Description**: Cancel an existing bet
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "ticket_id": "ABC123XYZ"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Bet cancelled successfully",
    "data": {
      "id": 1,
      "ticket_id": "ABC123XYZ",
      "status": "cancelled"
    }
  }
  ```

## Claims

### Submit Claim
- **URL**: `/api/teller/claim`
- **Method**: `POST`
- **Description**: Submit a claim for a winning bet
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "ticket_id": "ABC123XYZ",
    "result_id": 1
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Claim submitted successfully",
    "data": {
      "id": 1,
      "bet_id": 1,
      "result_id": 1,
      "amount": 50.00,
      "teller_id": 1,
      "status": "processed",
      "bet": {
        "id": 1,
        "ticket_id": "ABC123XYZ",
        "bet_number": "123",
        "amount": 50.00
      },
      "result": {
        "id": 1,
        "draw_date": "2025-04-30",
        "draw_time": "10:30:00",
        "type": "S3",
        "winning_number": "123"
      }
    }
  }
  ```

### List Claims
- **URL**: `/api/teller/claims`
- **Method**: `GET`
- **Description**: Get a list of claims for the current teller
- **Authentication**: Required
- **Query Parameters**:
  - `date` (optional): Date in Y-m-d format
  - `status` (optional): Filter by status
  - `per_page` (optional): Number of items per page
- **Response**:
  ```json
  {
    "status": true,
    "message": "Claims retrieved successfully",
    "data": [
      {
        "id": 1,
        "bet_id": 1,
        "result_id": 1,
        "amount": 50.00,
        "teller_id": 1,
        "status": "processed",
        "created_at": "2025-04-30T10:30:00.000000Z",
        "bet": {
          "id": 1,
          "ticket_id": "ABC123XYZ",
          "bet_number": "123",
          "amount": 50.00
        },
        "result": {
          "id": 1,
          "draw_date": "2025-04-30",
          "draw_time": "10:30:00",
          "type": "S3",
          "winning_number": "123"
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

## Results

### Submit Result
- **URL**: `/api/coordinator/result`
- **Method**: `POST`
- **Description**: Submit a new result for a draw
- **Authentication**: Required (Coordinator role)
- **Request Body**:
  ```json
  {
    "draw_id": 1,
    "winning_number": "123"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Result submitted successfully",
    "data": {
      "id": 1,
      "draw_date": "2025-04-30",
      "draw_time": "10:30:00",
      "type": "S3",
      "winning_number": "123",
      "coordinator": {
        "id": 2,
        "name": "Coordinator Name",
        "username": "coordinator1"
      }
    }
  }
  ```

### List Results
- **URL**: `/api/results`
- **Method**: `GET`
- **Description**: Get a list of results
- **Authentication**: Required
- **Query Parameters**:
  - `date` (optional): Date in Y-m-d format
  - `type` (optional): Filter by draw type
  - `search` (optional): Search by winning number
  - `per_page` (optional): Number of items per page
- **Response**:
  ```json
  {
    "status": true,
    "message": "Results loaded",
    "data": [
      {
        "id": 1,
        "draw_date": "2025-04-30",
        "draw_time": "10:30:00",
        "type": "S3",
        "winning_number": "123",
        "coordinator": {
          "id": 2,
          "name": "Coordinator Name",
          "username": "coordinator1"
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

## Reports

### Tally Sheet
- **URL**: `/api/teller/tally-sheet`
- **Method**: `GET`
- **Description**: Get a tally sheet for the current teller
- **Authentication**: Required
- **Query Parameters**:
  - `date` (optional): Date in Y-m-d format
- **Response**:
  ```json
  {
    "status": true,
    "message": "Tally sheet generated successfully",
    "data": {
      "date": "2025-04-30",
      "teller": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe"
      },
      "summary": {
        "total_sales": 500.00,
        "total_hits": 100.00,
        "total_gross": 400.00,
        "total_voided": 2
      },
      "draws": [
        {
          "draw_id": 1,
          "draw_time": "10:30:00",
          "type": "S3",
          "winning_number": "123",
          "sales": 200.00,
          "hits": 50.00,
          "gross": 150.00,
          "voided": 1
        },
        {
          "draw_id": 2,
          "draw_time": "14:00:00",
          "type": "S3",
          "winning_number": "456",
          "sales": 300.00,
          "hits": 50.00,
          "gross": 250.00,
          "voided": 1
        }
      ]
    }
  }
  ```

### Coordinator Summary Report
- **URL**: `/api/coordinator/summary-report`
- **Method**: `GET`
- **Description**: Get a summary report for the coordinator
- **Authentication**: Required (Coordinator role)
- **Query Parameters**:
  - `date` (optional): Date in Y-m-d format
- **Response**:
  ```json
  {
    "status": true,
    "message": "Coordinator summary loaded",
    "data": {
      "date": "2025-04-30",
      "totals": {
        "sales": 1500.00,
        "hits": 300.00,
        "gross": 1200.00,
        "voided": 5,
        "total_bets": 50
      },
      "tellers": [
        {
          "teller_id": 1,
          "name": "John Doe",
          "username": "johndoe",
          "sales": 500.00,
          "hits": 100.00,
          "gross": 400.00,
          "voided": 2,
          "total_bets": 20,
          "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
        },
        {
          "teller_id": 3,
          "name": "Jane Smith",
          "username": "janesmith",
          "sales": 1000.00,
          "hits": 200.00,
          "gross": 800.00,
          "voided": 3,
          "total_bets": 30,
          "profile_photo_url": "https://ui-avatars.com/api/?name=Jane+Smith&color=7F9CF5&background=EBF4FF"
        }
      ],
      "draw_types": [
        {
          "type": "S3",
          "bet_count": 30,
          "total_amount": 1000.00
        },
        {
          "type": "S2",
          "bet_count": 20,
          "total_amount": 500.00
        }
      ]
    }
  }
  ```

## Error Responses

All API endpoints will return an error response in the following format:

```json
{
  "status": false,
  "message": "Error message describing what went wrong",
  "data": null
}
```

Common HTTP status codes:
- `400`: Bad Request - Invalid input data
- `401`: Unauthorized - Authentication required
- `403`: Forbidden - Not authorized to access the resource
- `404`: Not Found - Resource not found
- `409`: Conflict - Resource already exists or other conflict
- `422`: Unprocessable Entity - Validation errors
- `500`: Internal Server Error - Server-side error

During development, detailed error messages will be provided. In production, user-friendly error messages will be shown.
