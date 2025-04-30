# Authentication API Documentation

## Overview

This document provides detailed information about the authentication endpoints in the LuckyBet Admin API, including registration, login, user profile retrieval, and logout functionality.

## Base URL

All API endpoints should be prefixed with: `/api`

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

Most endpoints require authentication via Bearer token. Include the token in the Authorization header:

```
Authorization: Bearer {your_access_token}
```

## Endpoints

### Register a New User

Creates a new user account and returns an authentication token.

**Endpoint:** `POST /api/register`

**Authentication Required:** No

**Parameters:**

| Parameter  | Type   | Required | Description                                     |
|------------|--------|----------|-------------------------------------------------|
| name       | string | Yes      | Full name of the user                           |
| username   | string | Yes      | Unique username                                 |
| email      | string | Yes      | Valid email address (must be unique)            |
| password   | string | Yes      | Password (minimum 6 characters)                 |
| password_confirmation | string | Yes | Must match the password field             |

**Example Request:**

```json
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john.doe@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Success Response (201 Created):**

```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "access_token": "1|a1b2c3d4e5f6g7h8i9j0...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john.doe@example.com",
      "phone": null,
      "role": "user",
      "location": null,
      "is_active": true,
      "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
    }
  }
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
  "status": false,
  "message": "The username has already been taken.",
  "errors": {
    "username": [
      "The username has already been taken."
    ]
  }
}
```

### User Login

Authenticates a user and returns an access token.

**Endpoint:** `POST /api/login`

**Authentication Required:** No

**Parameters:**

| Parameter | Type   | Required | Description          |
|-----------|--------|----------|----------------------|
| email     | string | Yes      | User's email address |
| password  | string | Yes      | User's password      |

**Example Request:**

```json
POST /api/login
Content-Type: application/json

{
  "email": "john.doe@example.com",
  "password": "secret123"
}
```

**Success Response (200 OK):**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "access_token": "1|a1b2c3d4e5f6g7h8i9j0...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john.doe@example.com",
      "phone": null,
      "role": "user",
      "location": null,
      "is_active": true,
      "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
    }
  }
}
```

**Error Response (422 Unprocessable Entity):**

```json
{
  "status": false,
  "message": "Invalid credentials",
  "errors": null
}
```

### Get Authenticated User

Retrieves the currently authenticated user's information.

**Endpoint:** `GET /api/user`

**Authentication Required:** Yes (Bearer Token)

**Parameters:** None

**Example Request:**

```
GET /api/user
Authorization: Bearer 1|a1b2c3d4e5f6g7h8i9j0...
```

**Success Response (200 OK):**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "john.doe@example.com",
    "phone": null,
    "role": "user",
    "location": null,
    "is_active": true,
    "profile_photo_url": "https://ui-avatars.com/api/?name=John+Doe&color=7F9CF5&background=EBF4FF"
  }
}
```

**Error Response (401 Unauthorized):**

```json
{
  "message": "Unauthenticated."
}
```

### User Logout

Invalidates the current access token.

**Endpoint:** `POST /api/logout`

**Authentication Required:** Yes (Bearer Token)

**Parameters:** None

**Example Request:**

```
POST /api/logout
Authorization: Bearer 1|a1b2c3d4e5f6g7h8i9j0...
```

**Success Response (200 OK):**

```json
{
  "status": true,
  "message": "User logged out successfully",
  "data": null
}
```

**Error Response (401 Unauthorized):**

```json
{
  "message": "Unauthenticated."
}
```

## Notes

1. The profile photo URL in user responses will either:
   - Return the path to the user's uploaded profile photo, or
   - Generate a fallback avatar URL from ui-avatars.com based on the user's name if no profile photo exists

2. All authentication endpoints use Laravel Sanctum for token-based authentication.

3. Tokens do not expire automatically unless configured in the application settings.
