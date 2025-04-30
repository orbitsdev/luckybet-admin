# Betting API Documentation

## Overview

This document provides detailed information about the betting endpoints in the LuckyBet Admin API, including available draws and placing bets.

## Response Format

All API endpoints follow a consistent response format:

### Success Response

```json
{
  "status": true,
  "message": "Success message",
  "data": {
    // Response data here
  }
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

### Validation Error Response

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "Error message for this field"
    ]
  }
}
```

### HTTP Status Codes

| Code | Description                                           |
|------|-------------------------------------------------------|
| 200  | OK - The request was successful                       |
| 201  | Created - A new resource was successfully created     |
| 400  | Bad Request - The request was malformed               |
| 401  | Unauthorized - Authentication is required             |
| 403  | Forbidden - The user doesn't have permission          |
| 404  | Not Found - The requested resource doesn't exist      |
| 422  | Unprocessable Entity - Validation errors              |
| 500  | Server Error - An unexpected error occurred           |

## Authentication

All betting endpoints require authentication via Bearer token. Include the token in the Authorization header:

```
Authorization: Bearer {your_access_token}
```

## Endpoints

### Get Available Draws

Retrieves all available draws for the current day.

**Endpoint:** `GET /api/available-draws`

**Authentication Required:** Yes (Bearer Token)

**Parameters:** None

**Example Request:**

```
GET /api/available-draws
Authorization: Bearer 1|a1b2c3d4e5f6g7h8i9j0...
```

**Success Response (200 OK):**

```json
{
  "status": true,
  "message": "Available draws loaded",
  "data": [
    {
      "id": 1,
      "draw_date": "2025-04-30",
      "draw_time": "12:00:00",
      "type": "S2",
      "is_open": true
    },
    {
      "id": 2,
      "draw_date": "2025-04-30",
      "draw_time": "18:00:00",
      "type": "S3",
      "is_open": true
    }
  ]
}
```

### Place a Bet

Creates a new bet for a specific draw.

**Endpoint:** `POST /api/place-bet`

**Authentication Required:** Yes (Bearer Token)

**Parameters:**

| Parameter      | Type    | Required | Description                                     |
|----------------|---------|----------|-------------------------------------------------|
| bet_number     | string  | Yes      | The number being bet on (max 5 characters)      |
| amount         | numeric | Yes      | Bet amount (minimum 1)                          |
| draw_id        | string  | Yes      | Type of draw (S2, S3, D4, etc.)                 |
| customer_id    | integer | No       | ID of the customer (if applicable)              |
| is_combination | boolean | No       | Whether this is a combination bet (default: false) |

**Example Request:**

```json
POST /api/place-bet
Content-Type: application/json
Authorization: Bearer 1|a1b2c3d4e5f6g7h8i9j0...

{
  "bet_number": "12345",
  "amount": 100,
  "draw_id": 1,
  "customer_id": 5,
  "is_combination": false
}
```

**Success Response (200 OK):**

```json
{
  "status": true,
  "message": "Bet placed successfully",
  "data": {
    "id": 1,
    "bet_number": "12345",
    "amount": 100,
    "status": "active",
    "is_combination": false,
    "ticket_id": "AB12CD34EF",
    "bet_date": "2025-04-30",
    "draw": {
      "id": 1,
      "draw_date": "2025-04-30",
      "draw_time": "12:00:00",
      "type": "S2",
      "is_open": true
    },
    "teller": {
      "id": 2,
      "name": "Teller User",
      "username": "teller1",
      "email": "teller@example.com",
      "phone": null,
      "role": "teller",
      "location": null,
      "is_active": true,
      "profile_photo_url": "https://ui-avatars.com/api/?name=Teller+User&color=7F9CF5&background=EBF4FF"
    },
    "customer": {
      "id": 5,
      "name": "Customer Name",
      "username": "customer1",
      "email": "customer@example.com",
      "phone": null,
      "role": "customer",
      "location": null,
      "is_active": true,
      "profile_photo_url": "https://ui-avatars.com/api/?name=Customer+Name&color=7F9CF5&background=EBF4FF"
    },
    "location": {
      "id": 3,
      "name": "Main Branch",
      "address": "123 Main St",
      "city": "Metro City",
      "province": "Central Province",
      "is_active": true
    }
  }
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
  "status": false,
  "message": "The draw id field is required.",
  "errors": {
    "draw_id": [
      "The draw id field is required."
    ]
  }
}
```

## Bet Status Values

The `status` field in bet responses can have the following values:

| Status     | Description                                           |
|------------|-------------------------------------------------------|
| active     | Bet is active and awaiting draw result                |
| won        | Bet has won after draw                                |
| lost       | Bet has lost after draw                               |
| claimed    | Winning bet has been claimed by customer              |
| cancelled  | Bet was cancelled before draw                         |

## Notes

1. The `ticket_id` is a unique identifier for each bet and can be used for reference.
2. Only tellers with a valid location can place bets.
3. The `is_combination` flag indicates whether the bet is a combination bet.
4. Bets can only be placed on draws that are currently open (`is_open` = true).
5. The `bet_date` is automatically set to the current date when the bet is placed.
