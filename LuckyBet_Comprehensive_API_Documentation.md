# LuckyBet Comprehensive API Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [System Architecture](#system-architecture)
3. [Authentication](#authentication)
4. [Betting Operations](#betting-operations)
5. [Claims Management](#claims-management)
6. [Results Management](#results-management)
7. [Reporting](#reporting)
8. [Error Handling](#error-handling)
9. [Data Flow Diagrams](#data-flow-diagrams)
10. [API Response Structure](#api-response-structure)

## Introduction

LuckyBet is a comprehensive betting management system designed for lottery-style number games. The system allows tellers to place bets on behalf of customers, coordinators to manage results, and provides robust reporting capabilities for financial tracking.

### Key Features

- **User Management**: Registration, authentication, and role-based access control
- **Betting Operations**: Place, list, and cancel bets
- **Results Management**: Submit and retrieve draw results
- **Claims Processing**: Process winning tickets and calculate payouts
- **Reporting**: Generate tally sheets and coordinator summary reports

### User Roles

- **Teller**: Places bets, processes claims, and views their own tally sheets
- **Coordinator**: Manages results and views summary reports for all tellers in their location
- **Administrator**: Manages users, locations, and system settings (via Filament admin panel)

## System Architecture

LuckyBet is built on a modern tech stack:

- **Backend**: Laravel PHP framework with RESTful API endpoints
- **Frontend**: Flutter mobile application
- **Authentication**: Laravel Sanctum for token-based authentication
- **Database**: MySQL relational database
- **Admin Panel**: Filament for administrative operations

### Data Model

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    Users    │     │   Locations │     │    Draws    │
├─────────────┤     ├─────────────┤     ├─────────────┤
│ id          │     │ id          │     │ id          │
│ name        │     │ name        │     │ draw_date   │
│ email       │     │ address     │     │ draw_time   │
│ username    │     │ city        │     │ type        │
│ password    │     │ status      │     │ is_open     │
│ role        │     └──────┬──────┘     └──────┬──────┘
│ location_id │            │                   │
└──────┬──────┘            │                   │
       │                   │                   │
       │                   │                   │
┌──────┴──────┐     ┌──────┴──────┐     ┌──────┴──────┐
│     Bets    │     │   Results   │     │   Claims    │
├─────────────┤     ├─────────────┤     ├─────────────┤
│ id          │     │ id          │     │ id          │
│ ticket_id   │     │ draw_date   │     │ bet_id      │
│ bet_number  │     │ draw_time   │     │ result_id   │
│ amount      │     │ type        │     │ teller_id   │
│ draw_id     │     │ winning_num │     │ amount      │
│ teller_id   │     │ coordinator │     │ commission  │
│ location_id │     └─────────────┘     │ claimed_at  │
│ status      │                         └─────────────┘
└─────────────┘
```

## Authentication

All API endpoints (except registration and login) require authentication using a Bearer token. The token is obtained during login and must be included in the Authorization header of all subsequent requests.

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

### Authentication Flow

```
┌──────────┐                 ┌────────────┐                ┌────────────┐
│  Client  │                 │  API       │                │  Database  │
└────┬─────┘                 └─────┬──────┘                └─────┬──────┘
     │                             │                             │
     │ POST /api/register          │                             │
     │ or POST /api/login          │                             │
     │ ─────────────────────────► │                             │
     │                             │ Validate credentials        │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Return user data            │
     │                             │ ◄───────────────────────────│
     │                             │                             │
     │                             │ Generate token              │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │ Return token                │                             │
     │ ◄─────────────────────────  │                             │
     │                             │                             │
     │ Subsequent requests         │                             │
     │ with Bearer token           │                             │
     │ ─────────────────────────► │                             │
     │                             │ Validate token              │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Return user                 │
     │                             │ ◄───────────────────────────│
     │                             │                             │
     │ Return response             │ Process request             │
     │ ◄─────────────────────────  │                             │
     │                             │                             │
```

## Betting Operations

The Betting module is the core of the LuckyBet system, allowing tellers to place bets on behalf of customers. This section covers all operations related to bets, including placing bets, listing bets, and canceling bets.

### Game Types

LuckyBet supports multiple game types:

- **S2**: Swertres 2-digit game (2 numbers)
- **S3**: Swertres 3-digit game (3 numbers)
- **D4**: Digit 4-digit game (4 numbers)

### Bet Status Lifecycle

```
┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
│  Active  │────►│   Won    │────►│ Claimed  │     │  Lost    │
└──────────┘     └──────────┘     └──────────┘     └──────────┘
      │                                                  ▲
      │                                                  │
      │                ┌──────────┐                      │
      └───────────────►│ Cancelled│                      │
                       └──────────┘                      │
                                                         │
                           Results                       │
                           Submission                    │
                           ─────────────────────────────┘
```

### Available Draws

- **URL**: `/api/draws/available`
- **Method**: `GET`
- **Description**: Get a list of available draws for placing bets
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
- **Description**: Place a new bet for a customer
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

### Betting Process Flow

```
┌──────────┐                 ┌────────────┐                ┌────────────┐
│  Teller  │                 │  API       │                │  Database  │
└────┬─────┘                 └─────┬──────┘                └─────┬──────┘
     │                             │                             │
     │ GET /api/draws/available   │                             │
     │ ─────────────────────────► │                             │
     │                             │ Query available draws      │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │ Return available draws      │ Return draws data           │
     │ ◄─────────────────────────  │ ◄───────────────────────────│
     │                             │                             │
     │ POST /api/teller/bet       │                             │
     │ ─────────────────────────► │                             │
     │                             │ Validate bet data           │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Create bet record           │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │ Return bet details          │ Return created bet          │
     │ ◄─────────────────────────  │ ◄───────────────────────────│
     │                             │                             │
```

## Claims Management

The Claims module allows tellers to process winning tickets and pay out winnings to customers. This section covers all operations related to claims, including submitting claims and listing claims.

### Claim Process

1. Customer presents a winning ticket to the teller
2. Teller verifies the ticket against the results
3. Teller submits a claim through the API
4. System validates the claim and calculates the payout
5. Teller pays the customer the winning amount

### Payout Calculation

The payout is calculated based on the bet type and amount:

- **S2 (Regular)**: 2x the bet amount
- **S2 (Combination)**: 1.5x the bet amount
- **S3 (Regular)**: 3x the bet amount
- **S3 (Combination)**: 2x the bet amount
- **D4 (Regular)**: 4x the bet amount
- **D4 (Combination)**: 2.5x the bet amount

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

### Claim Process Flow

```
┌──────────┐                 ┌────────────┐                ┌────────────┐
│  Teller  │                 │  API       │                │  Database  │
└────┬─────┘                 └─────┬──────┘                └─────┬──────┘
     │                             │                             │
     │ POST /api/teller/claim     │                             │
     │ ─────────────────────────► │                             │
     │                             │ Validate ticket & result    │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Calculate payout            │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Create claim record         │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Update bet status           │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │ Return claim details        │ Return created claim        │
     │ ◄─────────────────────────  │ ◄───────────────────────────│
     │                             │                             │
```

## Results Management

The Results module allows coordinators to submit winning numbers for draws and retrieve results. This section covers all operations related to results, including submitting results and listing results.

### Result Submission Process

1. Coordinator waits for the official draw results
2. Coordinator submits the winning number through the API
3. System validates the result and updates the draw status
4. System automatically marks bets as won or lost based on the winning number

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

### Result Submission Flow

```
┌──────────────┐          ┌────────────┐          ┌────────────┐
│  Coordinator │          │  API       │          │  Database  │
└──────┬───────┘          └─────┬──────┘          └─────┬──────┘
       │                        │                       │
       │ POST /api/coordinator/result                  │
       │ ────────────────────► │                       │
       │                        │ Validate draw & number│
       │                        │ ─────────────────────►│
       │                        │                       │
       │                        │ Close draw            │
       │                        │ ─────────────────────►│
       │                        │                       │
       │                        │ Create result record  │
       │                        │ ─────────────────────►│
       │                        │                       │
       │                        │ Update bet statuses   │
       │                        │ ─────────────────────►│
       │                        │                       │
       │ Return result details  │ Return created result │
       │ ◄────────────────────  │ ◄─────────────────────│
       │                        │                       │
```

## Reporting

The Reporting module provides financial and operational insights for tellers and coordinators. This section covers all operations related to reports, including tally sheets and coordinator summary reports.

### Tally Sheet

Tally sheets provide a summary of a teller's daily operations, including sales, hits (winning bets), and gross (net sales after paying out winnings).

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

Coordinator summary reports provide an overview of all tellers' operations within a location, including sales, hits, and gross by teller and draw type.

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

### Reporting Process Flow

```
┌──────────┐                 ┌────────────┐                ┌────────────┐
│  User    │                 │  API       │                │  Database  │
└────┬─────┘                 └─────┬──────┘                └─────┬──────┘
     │                             │                             │
     │ GET /api/teller/tally-sheet│                             │
     │ or GET /api/coordinator/    │                             │
     │ summary-report              │                             │
     │ ─────────────────────────► │                             │
     │                             │ Query bets, claims, results │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Aggregate data              │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │                             │ Calculate totals            │
     │                             │ ───────────────────────────►│
     │                             │                             │
     │ Return formatted report     │ Return aggregated data      │
     │ ◄─────────────────────────  │ ◄───────────────────────────│
     │                             │                             │
```

## Error Handling

All API endpoints follow a consistent error handling approach to provide clear and actionable feedback to clients.

### Error Response Format

All error responses follow this standard format:

```json
{
  "status": false,
  "message": "Error message describing what went wrong",
  "data": null
}
```

For validation errors, additional details are provided in the `errors` field:

```json
{
  "status": false,
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message for this field"
    ]
  },
  "data": null
}
```

### Common HTTP Status Codes

- `400`: Bad Request - Invalid input data
- `401`: Unauthorized - Authentication required
- `403`: Forbidden - Not authorized to access the resource
- `404`: Not Found - Resource not found
- `409`: Conflict - Resource already exists or other conflict
- `422`: Unprocessable Entity - Validation errors
- `500`: Internal Server Error - Server-side error

### Error Handling Examples

#### Authentication Error

```json
{
  "status": false,
  "message": "Unauthenticated.",
  "data": null
}
```

#### Validation Error

```json
{
  "status": false,
  "message": "The given data was invalid.",
  "errors": {
    "bet_number": [
      "The bet number must be a valid format for the selected draw type."
    ],
    "amount": [
      "The amount must be at least 1."
    ]
  },
  "data": null
}
```

#### Resource Not Found

```json
{
  "status": false,
  "message": "Ticket not found.",
  "data": null
}
```

#### Business Logic Error

```json
{
  "status": false,
  "message": "This draw is already closed for betting.",
  "data": null
}
```

## Data Flow Diagrams

### Overall System Architecture

```
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│ Flutter Mobile  │     │ Laravel API    │     │ MySQL Database │
│ Application     │     │ Backend        │     │                │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘
         │                   │                   │
         │  HTTP Requests     │                   │
         └───────────────►│                   │
                              │  Database Queries  │
                              └───────────────►│
                              │                   │
                              │  Query Results     │
                              │ ◄───────────────┘
         │                   │                   │
         │  JSON Responses    │                   │
         │ ◄───────────────┘                   │
         │                   │                   │
```

### Complete Betting Lifecycle

```
┌───────────────┐     ┌───────────────┐     ┌───────────────┐
│ Teller          │     │ Coordinator     │     │ System         │
└──────┬───────┘     └──────┬───────┘     └──────┬───────┘
         │                   │                   │
         │                   │                   │
         │  Place Bet         │                   │
         └────────────────────────────────────►│
                                                  │
                                                  │
                              │  Submit Result     │
                              └───────────────►│
                                                  │
                                                  │
                                                  │ Update Bet Statuses
                                                  │ (won/lost)
                                                  │
         │                   │                   │
         │  Submit Claim      │                   │
         └────────────────────────────────────►│
                                                  │
                                                  │ Update Bet Status
                                                  │ (claimed)
                                                  │
         │                   │                   │
         │  Generate Reports  │  Generate Reports  │
         └───────────────►│ └───────────────►│
                              │                   │
```

## API Response Structure

All API responses follow a consistent structure to make client-side processing predictable and straightforward.

### Success Response Format

```json
{
  "status": true,
  "message": "Success message describing the operation",
  "data": { ... }
}
```

### Pagination Format

For endpoints that return lists of items, pagination information is included:

```json
{
  "status": true,
  "message": "Success message",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "next_page_url": "https://api.example.com/endpoint?page=2",
    "prev_page_url": null
  }
}
```

### Profile Photo URLs

User profile photos are served through a custom implementation that provides fallback to generated avatars when no photo is uploaded. The URL is included in user data responses as `profile_photo_url`.

```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "role": "teller",
    "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
  }
}
```

If a user has uploaded a profile photo, the URL will point to the stored image. Otherwise, it will generate an avatar based on the user's name using the ui-avatars.com service.

## Conclusion

This comprehensive API documentation provides all the information needed to integrate with the LuckyBet Admin system. The API follows RESTful principles and provides a consistent structure for requests and responses. All endpoints require authentication except for registration and login.

For any questions or issues, please contact the development team.
