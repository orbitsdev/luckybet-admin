# LuckyBet Admin API Documentation

This document provides comprehensive documentation for the LuckyBet Admin API, including endpoints, request parameters, and response formats.

## Table of Contents

1. [Authentication](#authentication)
2. [User Management](#user-management)
3. [Betting Operations](#betting-operations)
4. [Claims Management](#claims-management)
5. [Results Management](#results-management)
6. [Tally Sheet](#tally-sheet)
7. [Coordinator Reports](#coordinator-reports)
8. [Number Flags](#number-flags)

## Base URL

All API requests should be prefixed with your base URL:

```
https://luckybet-admin.orbitsdev.com/api
```

## Response Format

All API responses follow a consistent format:

### Success Response

```json
{
  "status": true,
  "message": "Success message",
  "data": { ... }
}
```

### Error Response

```json
{
  "status": false,
  "message": "Error message",
  "data": null
}
```

### Paginated Response

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
    "next_page_url": "https://your-api-domain.com/api/endpoint?page=2",
    "prev_page_url": null
  }
}
```

## Authentication

### Register

Register a new user account.

- **URL**: `/register`
- **Method**: `POST`
- **Authentication**: None

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Full name of the user |
| username | string | Yes | Unique username |
| email | string | Yes | Unique email address |
| password | string | Yes | Password (min 6 characters) |
| password_confirmation | string | Yes | Confirm password |

**Example Request:**

```json
{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "access_token": "1|LMcaLATEWXYZ123456789abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "role": "teller",
      "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF",
      "location": {
        "id": 1,
        "name": "Main Branch",
        "address": "123 Main St"
      }
    }
  }
}
```

### Login

Authenticate a user and get an access token. Supports login with either email or username.

- **URL**: `/login`
- **Method**: `POST`
- **Authentication**: None

**Request Parameters (Email Login):**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Yes | User's email address |
| password | string | Yes | User's password |

**Example Request (Email Login):**

```json
{
  "email": "john@example.com",
  "password": "secret123"
}
```

**Request Parameters (Username Login):**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| username | string | Yes | User's username |
| password | string | Yes | User's password |

**Example Request (Username Login):**

```json
{
  "username": "johndoe",
  "password": "secret123"
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "access_token": "1|LMcaLATEWXYZ123456789abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "role": "teller",
      "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF",
      "location": {
        "id": 1,
        "name": "Main Branch",
        "address": "123 Main St"
      }
    }
  }
}
```

### Get Current User

Get the authenticated user's information.

- **URL**: `/user`
- **Method**: `GET`
- **Authentication**: Required

**Example Response:**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "role": "teller",
    "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF",
    "location": {
      "id": 1,
      "name": "Main Branch",
      "address": "123 Main St"
    }
  }
}
```

### Logout

Revoke the current access token.

- **URL**: `/logout`
- **Method**: `POST`
- **Authentication**: Required

**Example Response:**

```json
{
  "status": true,
  "message": "User logged out successfully",
  "data": null
}
```

## Betting Operations

### Available Draws

Get a list of available draws for the current day that have not yet occurred (based on the current time).

- **URL**: `/draws/available`
- **Method**: `GET`
- **Authentication**: Required

**Example Response:**

```json
{
  "status": true,
  "message": "Available draws loaded",
  "data": [
    {
      "id": 2,
      "draw_date": "2025-05-05T16:00:00.000000Z",
      "draw_time": "16:00:00",
      "schedule": {
        "id": 1,
        "name": "Afternoon Draw",
        "draw_time": "16:00:00"
      },
      "game_type": {
        "id": 1,
        "code": "S2",
        "name": "Swertres 2-Digit"
      },
      "is_open": true
    },
    {
      "id": 3,
      "draw_date": "2025-05-05T16:00:00.000000Z",
      "draw_time": "21:00:00",
      "schedule": {
        "id": 2,
        "name": "Evening Draw",
        "draw_time": "21:00:00"
      },
      "game_type": {
        "id": 2,
        "code": "S3",
        "name": "Swertres 3-Digit"
      },
      "is_open": true
    }
  ]
}
```

### Place Bet

Place a new bet as a teller. This endpoint allows tellers to submit bets for different game types in the multi-game lottery system.

- **URL**: `/teller/bet`
- **Method**: `POST`
- **Authentication**: Required (Teller access token)

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| bet_number | string | Yes | The bet number (max 5 digits). Length should match the selected game type (2 digits for S2, 3 digits for S3, 4 digits for D4) |
| amount | numeric | Yes | Bet amount (min 1). The amount the customer is betting |
| draw_id | integer | Yes | ID of the draw. This should be obtained from the `/draws/available` endpoint |
| game_type_id | integer | Yes | ID of the game type (1 for S2, 2 for S3, 3 for D4). This should be obtained from the `/game-types` endpoint or from the draw object returned by `/draws/available` |
| customer_id | integer | No | ID of the customer (if applicable). Leave as null for anonymous bets |
| is_combination | boolean | No | Whether this is a combination bet. If true, the system will create all possible combinations of the bet number |

**Example Request:**

```json
{
  "bet_number": "123",
  "amount": 50,
  "draw_id": 1,
  "game_type_id": 2,
  "customer_id": null,
  "is_combination": false
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Bet placed successfully",
  "data": {
    "id": 1,
    "ticket_id": "ABC123XYZ",
    "bet_number": "123",
    "amount": 50,
    "game_type": {
      "id": 2,
      "code": "S3",
      "name": "Swertres 3-Digit"
    },
    "draw": {
      "id": 1,
      "draw_time": "11:00 AM",
      "draw_date": "2025-05-05",
      "schedule": {
        "id": 1,
        "name": "Morning Draw",
        "draw_time": "11:00:00"
      }
    },
    "location": {
      "id": 1,
      "name": "Main Branch"
    },
    "teller": {
      "id": 1,
      "name": "John Doe"
    },
    "customer": null,
    "bet_date": "2025-05-05",
    "status": "active",
    "is_combination": false
  }
}
```

### List Bets

Get a list of bets placed by the authenticated teller.

- **URL**: `/teller/bets`
- **Method**: `GET`
- **Authentication**: Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | Search by ticket ID or bet number |
| status | string | No | Filter by status (active, won, lost, claimed, cancelled) |
| draw_id | integer | No | Filter by draw ID |
| date | date | No | Filter by bet date (YYYY-MM-DD) |
| per_page | integer | No | Number of results per page (default: 20) |
| page | integer | No | Page number |

**Example Response:**

```json
{
  "status": true,
  "message": "Bets retrieved",
  "data": [
    {
      "id": 1,
      "ticket_id": "ABC123XYZ",
      "bet_number": "123",
      "amount": 50,
      "game_type": {
        "id": 2,
        "code": "S3",
        "name": "Swertres 3-Digit"
      },
      "draw": {
        "id": 1,
        "draw_time": "11:00 AM",
        "draw_date": "2025-05-05",
        "schedule": {
          "id": 1,
          "name": "Morning Draw",
          "draw_time": "11:00:00"
        }
      },
      "location": {
        "id": 1,
        "name": "Main Branch"
      },
      "customer": null,
      "bet_date": "2025-05-05",
      "status": "active",
      "is_combination": false
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

Cancel an active bet.

- **URL**: `/teller/bet/cancel`
- **Method**: `POST`
- **Authentication**: Required

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| ticket_id | string | Yes | The ticket ID to cancel |

**Example Request:**

```json
{
  "ticket_id": "ABC123XYZ"
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Bet cancelled successfully",
  "data": {
    "id": 1,
    "ticket_id": "ABC123XYZ",
    "bet_number": "123",
    "amount": 50,
    "game_type": {
      "id": 2,
      "code": "S3",
      "name": "Swertres 3-Digit"
    },
    "draw": {
      "id": 1,
      "draw_time": "11:00 AM",
      "draw_date": "2025-05-05",
      "schedule": {
        "id": 1,
        "name": "Morning Draw",
        "draw_time": "11:00:00"
      }
    },
    "location": {
      "id": 1,
      "name": "Main Branch"
    },
    "teller": {
      "id": 1,
      "name": "John Doe"
    },
    "customer": null,
    "bet_date": "2025-05-05",
    "status": "cancelled",
    "is_combination": false
  }
}
```

## Claims Management

### Submit Claim

Submit a claim for a winning bet.

- **URL**: `/teller/claim`
- **Method**: `POST`
- **Authentication**: Required

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| ticket_id | string | Yes | The winning ticket ID |
| result_id | integer | Yes | The ID of the result |

**Example Request:**

```json
{
  "ticket_id": "ABC123XYZ",
  "result_id": 1
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Claim processed successfully",
  "data": {
    "id": 1,
    "bet": {
      "id": 1,
      "ticket_id": "ABC123XYZ",
      "bet_number": "123",
      "amount": 50,
      "status": "claimed"
    },
    "result": {
      "id": 1,
      "draw_date": "2025-05-05",
      "draw_time": "11:00 AM",
      "s2_winning_number": "42",
      "s3_winning_number": "123",
      "d4_winning_number": "5678"
    },
    "teller": {
      "id": 1,
      "name": "John Doe"
    },
    "amount": 150,
    "commission_amount": 2.5,
    "status": "processed",
    "claimed_at": "2025-05-05T09:30:00.000000Z",
    "qr_code_data": "ABC123XYZ"
  }
}
```

### List Claims

Get a list of claims processed by the authenticated teller.

- **URL**: `/teller/claims`
- **Method**: `GET`
- **Authentication**: Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| search | string | No | Search by ticket ID or bet number |
| date | date | No | Filter by claim date (YYYY-MM-DD) |
| amount_min | numeric | No | Minimum claim amount |
| amount_max | numeric | No | Maximum claim amount |
| per_page | integer | No | Number of results per page (default: 20) |
| page | integer | No | Page number |

**Example Response:**

```json
{
  "status": true,
  "message": "Claims retrieved",
  "data": [
    {
      "id": 1,
      "bet": {
        "id": 1,
        "ticket_id": "ABC123XYZ",
        "bet_number": "123",
        "amount": 50,
        "status": "claimed"
      },
      "result": {
        "id": 1,
        "draw_date": "2025-05-05",
        "draw_time": "11:00 AM",
        "s2_winning_number": "42",
        "s3_winning_number": "123",
        "d4_winning_number": "5678"
      },
      "teller": {
        "id": 1,
        "name": "John Doe"
      },
      "amount": 150,
      "commission_amount": 2.5,
      "status": "processed",
      "claimed_at": "2025-05-05T09:30:00.000000Z"
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

## Results Management

### Submit Result

Submit a new result for a draw (coordinator only).

- **URL**: `/coordinator/result`
- **Method**: `POST`
- **Authentication**: Required

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| draw_id | integer | Yes | The ID of the draw |
| s2_winning_number | string | Yes | The winning number for S2 game type (2 digits) |
| s3_winning_number | string | Yes | The winning number for S3 game type (3 digits) |
| d4_winning_number | string | Yes | The winning number for D4 game type (4 digits) |

**Example Request:**

```json
{
  "draw_id": 1,
  "s2_winning_number": "42",
  "s3_winning_number": "123",
  "d4_winning_number": "5678"
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Result submitted successfully",
  "data": {
    "id": 1,
    "draw_id": 1,
    "draw_date": "2025-05-05",
    "draw_time": "11:00 AM",
    "s2_winning_number": "42",
    "s3_winning_number": "123",
    "d4_winning_number": "5678",
    "coordinator": {
      "id": 2,
      "name": "Jane Smith"
    },
    "created_at": "2025-05-05T11:05:00.000000Z",
    "draw": {
      "id": 1,
      "draw_time": "11:00 AM",
      "draw_date": "2025-05-05"
    }
  }
}
```

### List Results

Get a list of results.

- **URL**: `/results`
- **Method**: `GET`
- **Authentication**: Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | No | Filter by result date (YYYY-MM-DD) |
| type | string | No | Filter by draw type (S2, S3, D4) |
| search | string | No | Search by winning number |
| per_page | integer | No | Number of results per page (default: 20) |
| page | integer | No | Page number |

**Example Response:**

```json
{
  "status": true,
  "message": "Results loaded",
  "data": [
    {
      "id": 1,
      "draw_id": 1,
      "draw_date": "2025-05-05",
      "draw_time": "11:00 AM",
      "s2_winning_number": "42",
      "s3_winning_number": "123",
      "d4_winning_number": "5678",
      "coordinator": {
        "id": 2,
        "name": "Jane Smith",
        "username": "janesmith"
      },
      "created_at": "2025-05-05T11:05:00.000000Z",
      "draw": {
        "id": 1,
        "draw_time": "11:00 AM"
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

## Tally Sheet

### Get Tally Sheet

Get a tally sheet for the authenticated teller.

- **URL**: `/teller/tally-sheet`
- **Method**: `GET`
- **Authentication**: Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | No | Date for the tally sheet (YYYY-MM-DD, default: today) |

**Example Response:**

```json
{
  "status": true,
  "message": "Tally sheet generated successfully",
  "data": {
    "date": "May 5, 2025",
    "totals": {
      "sales": 500,
      "hits": 150,
      "gross": 350,
      "voided": 2
    },
    "draws": [
      {
        "draw_id": 1,
        "time": "11:00 AM",
        "schedule": {
          "id": 1,
          "name": "Morning Draw"
        },
        "game_types": [
          {
            "code": "S2",
            "winning_number": "42",
            "sales": 100,
            "hits": 50,
            "gross": 50,
            "voided": 0
          },
          {
            "code": "S3",
            "winning_number": "123",
            "sales": 200,
            "hits": 100,
            "gross": 100,
            "voided": 1
          }
        ],
        "total_sales": 300,
        "total_hits": 150,
        "total_gross": 150,
        "total_voided": 1
      },
      {
        "draw_id": 2,
        "time": "4:00 PM",
        "schedule": {
          "id": 2,
          "name": "Afternoon Draw"
        },
        "game_types": [
          {
            "code": "S2",
            "winning_number": "--",
            "sales": 100,
            "hits": 0,
            "gross": 100,
            "voided": 0
          },
          {
            "code": "D4",
            "winning_number": "--",
            "sales": 100,
            "hits": 0,
            "gross": 100,
            "voided": 1
          }
        ],
        "total_sales": 200,
        "total_hits": 0,
        "total_gross": 200,
        "total_voided": 1
      }
    ]
  }
}
```

## Coordinator Reports

### Get Summary Report

Get a summary report for a coordinator.

- **URL**: `/coordinator/summary-report`
- **Method**: `GET`
- **Authentication**: Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | No | Date for the report (YYYY-MM-DD, default: today) |

**Example Response:**

```json
{
  "status": true,
  "message": "Coordinator summary loaded",
  "data": {
    "date": "2025-05-05",
    "totals": {
      "sales": 1500,
      "hits": 300,
      "gross": 1200,
      "voided": 5,
      "total_bets": 50
    },
    "tellers": [
      {
        "teller_id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "sales": 500,
        "hits": 150,
        "gross": 350,
        "voided": 2,
        "total_bets": 20,
        "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
      },
      {
        "teller_id": 3,
        "name": "Bob Johnson",
        "username": "bobjohnson",
        "sales": 1000,
        "hits": 150,
        "gross": 850,
        "voided": 3,
        "total_bets": 30,
        "profile_photo_url": "https://ui-avatars.com/api/?name=Bob+Johnson&color=7F9CF5&background=EBF4FF"
      }
    ],
    "game_types": [
      {
        "code": "S3",
        "name": "Swertres 3-Digit",
        "bet_count": 30,
        "total_amount": 900
      },
      {
        "code": "S2",
        "name": "Swertres 2-Digit",
        "bet_count": 20,
        "total_amount": 600
      },
      {
        "code": "D4",
        "name": "Digit 4",
        "bet_count": 10,
        "total_amount": 300
      }
    ]
  }
}
```

## Game Types

### List Game Types

Get a list of all active game types.

- **URL**: `/game-types`
- **Method**: `GET`
- **Authentication**: Required

**Example Response:**

```json
{
  "status": true,
  "message": "Game types loaded",
  "data": [
    {
      "id": 1,
      "name": "Swertres 2-Digit",
      "code": "S2",
      "description": "A 2-digit lottery game",
      "is_active": true
    },
    {
      "id": 2,
      "name": "Swertres 3-Digit",
      "code": "S3",
      "description": "A 3-digit lottery game",
      "is_active": true
    },
    {
      "id": 3,
      "name": "Digit 4",
      "code": "D4",
      "description": "A 4-digit lottery game",
      "is_active": true
    }
  ]
}
```

## Number Flags

### List Number Flags

Get a list of number flags for the user's location.

- **URL**: `/number-flags`
- **Method**: `GET`
- **Authentication**: Required

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | No | Filter by date (YYYY-MM-DD) |
| type | string | No | Filter by type (sold_out, low_win) |
| schedule_id | integer | No | Filter by schedule ID |
| is_active | boolean | No | Filter by active status |
| search | string | No | Search by number |
| per_page | integer | No | Number of results per page (default: 20) |
| page | integer | No | Page number |

**Example Response:**

```json
{
  "status": true,
  "message": "Number flags retrieved successfully",
  "data": [
    {
      "id": 1,
      "number": "123",
      "type": "sold_out",
      "date": "2025-05-05",
      "is_active": true,
      "schedule": {
        "id": 1,
        "name": "Morning"
      },
      "location": {
        "id": 1,
        "name": "Main Branch"
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

Create a new number flag.

- **URL**: `/number-flags`
- **Method**: `POST`
- **Authentication**: Required

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| number | string | Yes | The number to flag |
| schedule_id | integer | Yes | The schedule ID |
| date | date | Yes | The date for the flag (YYYY-MM-DD) |
| type | string | Yes | Flag type (sold_out, low_win) |

**Example Request:**

```json
{
  "number": "123",
  "schedule_id": 1,
  "date": "2025-05-05",
  "type": "sold_out"
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Number flag created successfully",
  "data": {
    "id": 1,
    "number": "123",
    "type": "sold_out",
    "date": "2025-05-05",
    "is_active": true,
    "schedule": {
      "id": 1,
      "name": "Morning"
    },
    "location": {
      "id": 1,
      "name": "Main Branch"
    }
  }
}
```

### Get Number Flag

Get a specific number flag.

- **URL**: `/number-flags/{id}`
- **Method**: `GET`
- **Authentication**: Required

**Example Response:**

```json
{
  "status": true,
  "message": "Number flag retrieved successfully",
  "data": {
    "id": 1,
    "number": "123",
    "type": "sold_out",
    "date": "2025-05-05",
    "is_active": true,
    "schedule": {
      "id": 1,
      "name": "Morning"
    },
    "location": {
      "id": 1,
      "name": "Main Branch"
    }
  }
}
```

### Update Number Flag

Update a number flag.

- **URL**: `/number-flags/{id}`
- **Method**: `PUT`
- **Authentication**: Required

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | No | Flag type (sold_out, low_win) |
| is_active | boolean | No | Active status |

**Example Request:**

```json
{
  "type": "low_win",
  "is_active": true
}
```

**Example Response:**

```json
{
  "status": true,
  "message": "Number flag updated successfully",
  "data": {
    "id": 1,
    "number": "123",
    "type": "low_win",
    "date": "2025-05-05",
    "is_active": true,
    "schedule": {
      "id": 1,
      "name": "Morning"
    },
    "location": {
      "id": 1,
      "name": "Main Branch"
    }
  }
}
```

### Delete Number Flag

Deactivate a number flag (soft delete).

- **URL**: `/number-flags/{id}`
- **Method**: `DELETE`
- **Authentication**: Required

**Example Response:**

```json
{
  "status": true,
  "message": "Number flag deactivated successfully",
  "data": null
}
```
