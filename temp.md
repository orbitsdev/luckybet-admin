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
      "draw_time": "4:00 PM",
      "draw_date": "2025-05-05",
      "schedule": {
        "id": 1,
        "name": "Afternoon Draw",
        "draw_time": "16:00:00"
      },
      "is_open": true
    },
    {
      "id": 3,
      "draw_time": "9:00 PM",
      "draw_date": "2025-05-05",
      "schedule": {
        "id": 2,
        "name": "Evening Draw",
        "draw_time": "21:00:00"
      },
      "is_open": true
    }
  ]
}
```

### Place Bet

Place a new bet as a teller.

- **URL**: `/teller/bet`
- **Method**: `POST`
- **Authentication**: Required

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| bet_number | string | Yes | The bet number (max 5 digits) |
| amount | numeric | Yes | Bet amount (min 1) |
| draw_id | integer | Yes | ID of the draw |
| game_type_id | integer | Yes | ID of the game type |
| customer_id | integer | No | ID of the customer (if applicable) |
| is_combination | boolean | No | Whether this is a combination bet |

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
