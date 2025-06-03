# LuckyBet Receipt Module API Documentation

## Overview

The Receipt Module introduces a cart-based workflow that allows tellers to group multiple bets into a single receipt. This document provides comprehensive information about the API endpoints, request/response structures, and real-world usage scenarios for frontend developers.

## Key Changes

1. **New Cart-Based Workflow**: Tellers can now add multiple bets to a draft receipt before finalizing
2. **Paper Saving**: Multiple bets can be printed on a single receipt
3. **Professional Experience**: Customers receive one consolidated receipt instead of multiple tickets
4. **Improved Auditing**: Each receipt has a unique ticket ID and contains all related bets
5. **D4 Sub-Selection Support**: Full compatibility with D4-S2 and D4-S3 bet types

## API Endpoints

| Method | Endpoint                           | Purpose                                       |
|--------|------------------------------------|--------------------------------------------|  
| GET    | `/api/receipts/draft`              | Get or create current teller's draft receipt |
| GET    | `/api/receipts`                    | List finalized receipts with pagination      |
| GET    | `/api/receipts/find`               | Find receipt by receipt number or ticket ID  |
| GET    | `/api/receipts/{receipt}`          | Get details of a specific receipt            |
| POST   | `/api/receipts/{receipt}/bets`     | Add a bet to a draft receipt                 |
| DELETE | `/api/receipts/{receipt}/bets/{bet}` | Remove a bet from a draft receipt          |
| PUT    | `/api/receipts/{receipt}/bets/{bet}` | Update a bet in a draft receipt            |
| POST   | `/api/receipts/{receipt}/place`    | Finalize a receipt (place all bets)          |
| POST   | `/api/receipts/{receipt}/cancel`   | Cancel a draft receipt                       |

## Standard Response Format

All API endpoints follow a consistent response format:

### Success Response
```json
{
  "status": true,
  "message": "Success message",
  "data": { /* Response data */ }
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

## API Endpoints Reference

| Method | Endpoint                           | Purpose                                       |
|--------|------------------------------------|--------------------------------------------|  
| GET    | `/api/receipts/draft`              | Get or create current teller's draft receipt |
| GET    | `/api/receipts`                    | List finalized receipts with pagination      |
| GET    | `/api/receipts/find`               | Find receipt by receipt number or ticket ID  |
| GET    | `/api/receipts/{receipt}`          | Get details of a specific receipt            |
| POST   | `/api/receipts/{receipt}/bets`     | Add a bet to a draft receipt                 |
| DELETE | `/api/receipts/{receipt}/bets/{bet}` | Remove a bet from a draft receipt          |
| PUT    | `/api/receipts/{receipt}/bets/{bet}` | Update a bet in a draft receipt            |
| POST   | `/api/receipts/{receipt}/place`    | Finalize a receipt (place all bets)          |
| POST   | `/api/receipts/{receipt}/cancel`   | Cancel a draft receipt                       |

## Detailed API Documentation

### 1. Get Draft Receipt

Fetches the current teller's draft receipt or creates a new one if none exists.

**Endpoint:** `GET /api/receipts/draft`

**Authentication Required:** Yes

**Response Example:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 12,
    "ticket_id": null,
    "status": "draft",
    "receipt_date": null,
    "receipt_date_formatted": null,
    "total_amount": 0,
    "total_amount_formatted": "0",
    "bets": [],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller",
      "location": {
        "id": 2,
        "name": "Tacurong Branch"
      }
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}
```

### 2. Add Bet to Receipt

Adds a new bet to a draft receipt.

**Endpoint:** `POST /api/receipts/{receipt}/bets`

**Authentication Required:** Yes

**Request Parameters:**
```json
{
  "bet_number": "123",
  "amount": 500,
  "draw_id": 10,
  "game_type_id": 1,
  "customer_id": null,
  "is_combination": false,
  "d4_sub_selection": "S2"  // Optional, only for D4 game type
}
```

**Response Example:**
```json
{
  "status": true,
  "message": "Bet added to receipt successfully",
  "data": {
    "id": 12,
    "ticket_id": null,
    "status": "draft",
    "receipt_date": null,
    "receipt_date_formatted": null,
    "total_amount": 500,
    "total_amount_formatted": "500",
    "bets": [
      {
        "id": 101,
        "bet_number": "123",
        "amount": "500",
        "amount_formatted": "500",
        "winning_amount": 2500,
        "winning_amount_formatted": "2,500",
        "is_low_win": false,
        "is_claimed": false,
        "is_rejected": false,
        "is_combination": false,
        "is_winner": false,
        "d4_sub_selection": "S2",
        "bet_date": "2025-06-02",
        "bet_date_formatted": "Jun 02, 2025 09:45 AM",
        "game_type": {
          "id": 1,
          "name": "D4",
          "code": "D4"
        },
        "draw": {
          "id": 10,
          "draw_time": "14:00",
          "draw_date": "2025-06-02",
          "is_open": true
        }
      }
    ],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller"
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}
```

**Error Responses:**
- Sold out numbers: `"This number is sold out"`
- Bet cap exceeded: `"This number has reached its maximum bet limit"`
- Invalid draw: `"This draw is no longer accepting bets"`
- Missing winning amount: `"Winning amount is not set for this game type and amount"`

### 3. Remove Bet from Receipt

Removes a bet from a draft receipt.

**Endpoint:** `DELETE /api/receipts/{receipt}/bets/{bet}`

**Authentication Required:** Yes

**Response Example:**
```json
{
  "status": true,
  "message": "Bet removed from receipt successfully",
  "data": {
    "id": 12,
    "ticket_id": null,
    "status": "draft",
    "receipt_date": null,
    "receipt_date_formatted": null,
    "total_amount": 0,
    "total_amount_formatted": "0",
    "bets": [],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller"
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}

```

### 4. Update Bet in Receipt

Updates an existing bet in a draft receipt. Only certain fields can be updated.

**Endpoint:** `PUT /api/receipts/{receipt}/bets/{bet}`

**Authentication Required:** Yes

**Request Parameters:**
```json
{
  "amount": 1000,
  "is_combination": true,
  "d4_sub_selection": "S3"  // Optional, only for D4 game type
}
```

**Response Example:**
```json
{
  "status": true,
  "message": "Bet updated successfully",
  "data": {
    "id": 12,
    "ticket_id": null,
    "status": "draft",
    "receipt_date": null,
    "receipt_date_formatted": null,
    "total_amount": 1000,
    "total_amount_formatted": "1,000",
    "bets": [
      {
        "id": 101,
        "bet_number": "123",
        "amount": "1000",
        "amount_formatted": "1,000",
        "winning_amount": 5000,
        "winning_amount_formatted": "5,000",
        "is_low_win": false,
        "is_claimed": false,
        "is_rejected": false,
        "is_combination": true,
        "is_winner": false,
        "d4_sub_selection": "S3",
        "bet_date": "2025-06-02",
        "bet_date_formatted": "Jun 02, 2025 09:45 AM",
        "game_type": {
          "id": 1,
          "name": "D4",
          "code": "D4"
        },
        "draw": {
          "id": 10,
          "draw_time": "14:00",
          "draw_date": "2025-06-02",
          "is_open": true
        }
      }
    ],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller"
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}
```

### 5. Finalize Receipt

Finalizes a draft receipt, generating a ticket ID and marking all bets as placed.

**Endpoint:** `POST /api/receipts/{receipt}/place`

**Authentication Required:** Yes

**Response Example:**
```json
{
  "status": true,
  "message": "Receipt finalized successfully",
  "data": {
    "id": 12,
    "ticket_id": "LB-250602-0945-A7D4",
    "status": "placed",
    "receipt_date": "2025-06-02",
    "receipt_date_formatted": "Jun 02, 2025",
    "total_amount": 1000,
    "total_amount_formatted": "1,000",
    "bets": [
      {
        "id": 101,
        "ticket_id": "LB-250602-0945-A7D4",
        "bet_number": "123",
        "amount": "1000",
        "amount_formatted": "1,000",
        "winning_amount": 5000,
        "winning_amount_formatted": "5,000",
        "is_low_win": false,
        "is_claimed": false,
        "is_rejected": false,
        "is_combination": true,
        "is_winner": false,
        "d4_sub_selection": "S3",
        "bet_date": "2025-06-02",
        "bet_date_formatted": "Jun 02, 2025 09:45 AM",
        "game_type": {
          "id": 1,
          "name": "D4",
          "code": "D4"
        },
        "draw": {
          "id": 10,
          "draw_time": "14:00",
          "draw_date": "2025-06-02",
          "is_open": true
        }
      }
    ],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller"
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}
```

### 6. Cancel Receipt

Cancels a draft receipt and deletes all associated bets.

**Endpoint:** `POST /api/receipts/{receipt}/cancel`

**Authentication Required:** Yes

**Response Example:**
```json
{
  "status": true,
  "message": "Receipt cancelled successfully",
  "data": null
}
```

### 7. Get Receipt Details

Retrieves detailed information about a specific receipt.

**Endpoint:** `GET /api/receipts/{receipt}`

**Authentication Required:** Yes

**Response Example:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 12,
    "ticket_id": "LB-250602-0945-A7D4",
    "status": "placed",
    "receipt_date": "2025-06-02",
    "receipt_date_formatted": "Jun 02, 2025",
    "total_amount": 1000,
    "total_amount_formatted": "1,000",
    "bets": [
      {
        "id": 101,
        "ticket_id": "LB-250602-0945-A7D4",
        "bet_number": "123",
        "amount": "1000",
        "amount_formatted": "1,000",
        "winning_amount": 5000,
        "winning_amount_formatted": "5,000",
        "is_low_win": false,
        "is_claimed": false,
        "is_rejected": false,
        "is_combination": true,
        "is_winner": false,
        "d4_sub_selection": "S3",
        "bet_date": "2025-06-02",
        "bet_date_formatted": "Jun 02, 2025 09:45 AM",
        "game_type": {
          "id": 1,
          "name": "D4",
          "code": "D4"
        },
        "draw": {
          "id": 10,
          "draw_time": "14:00",
          "draw_date": "2025-06-02",
          "is_open": true
        }
      }
    ],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller"
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}
```

### 8. Find Receipt by Number or Ticket ID

Finds a specific receipt by its receipt number or ticket ID.

**Endpoint:** `GET /api/receipts/find`

**Authentication Required:** Yes

**Query Parameters:**
- `search` (required): The receipt ticket ID or bet ticket ID to search for

**Response Example:**
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 12,
    "ticket_id": "LB-250602-0945-A7D4",
    "status": "placed",
    "receipt_date": "2025-06-02",
    "receipt_date_formatted": "Jun 02, 2025",
    "total_amount": 1000,
    "total_amount_formatted": "1,000",
    "bets": [
      {
        "id": 101,
        "ticket_id": "LB-250602-0945-A7D4",
        "bet_number": "123",
        "amount": "1000",
        "amount_formatted": "1,000",
        "winning_amount": 5000,
        "winning_amount_formatted": "5,000",
        "is_low_win": false,
        "is_claimed": false,
        "is_rejected": false,
        "is_combination": true,
        "is_winner": false,
        "d4_sub_selection": "S3",
        "bet_date": "2025-06-02",
        "bet_date_formatted": "Jun 02, 2025 09:45 AM",
        "game_type": {
          "id": 1,
          "name": "D4",
          "code": "D4"
        },
        "draw": {
          "id": 10,
          "draw_time": "14:00",
          "draw_date": "2025-06-02",
          "is_open": true
        }
      }
    ],
    "teller": {
      "id": 5,
      "name": "Jane Doe",
      "username": "jane_teller",
      "role": "teller"
    },
    "location": {
      "id": 2,
      "name": "Tacurong Branch"
    },
    "created_at": "2025-06-02T01:30:45.000000Z",
    "created_at_formatted": "Jun 02, 2025 1:30 AM"
  }
}
```

**Error Response (Receipt Not Found):**
```json
{
  "status": false,
  "message": "Receipt not found",
  "data": null
}
```

### 9. List Receipts

Retrieves a paginated list of receipts.

**Endpoint:** `GET /api/receipts`

**Authentication Required:** Yes

**Query Parameters:**
- `status` (optional): Filter by receipt status (`placed`, `draft`, `cancelled`)  
- `date` (optional): Filter by specific date (format: YYYY-MM-DD)
- `from_date` (optional): Filter by start date (format: YYYY-MM-DD)
- `to_date` (optional): Filter by end date (format: YYYY-MM-DD)
- `search` (optional): Search for receipts by receipt ticket ID or bet ticket ID
- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page (default: 15, max: 100)

**Note:** If no date filter is provided and no search term is used, the API defaults to showing receipts from today's date only. When searching with the `search` parameter, the date filter is not applied.

**Response Example:**
```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 12,
      "ticket_id": "LB-250602-0945-A7D4",
      "status": "placed",
      "receipt_date": "2025-06-02",
      "receipt_date_formatted": "Jun 02, 2025",
      "total_amount": 1000,
      "total_amount_formatted": "1,000",
      "teller": {
        "id": 5,
        "name": "Jane Doe",
        "username": "jane_teller",
        "role": "teller"
      },
      "location": {
        "id": 2,
        "name": "Tacurong Branch"
      },
      "created_at": "2025-06-02T01:30:45.000000Z",
      "created_at_formatted": "Jun 02, 2025 1:30 AM"
    },
    {
      "id": 11,
      "ticket_id": "LB-250602-0930-B2C1",
      "status": "placed",
      "receipt_date": "2025-06-02",
      "receipt_date_formatted": "Jun 02, 2025",
      "total_amount": 1500,
      "total_amount_formatted": "1,500",
      "teller": {
        "id": 5,
        "name": "Jane Doe",
        "username": "jane_teller",
        "role": "teller"
      },
      "location": {
        "id": 2,
        "name": "Tacurong Branch"
      },
      "created_at": "2025-06-02T01:15:22.000000Z",
      "created_at_formatted": "Jun 02, 2025 1:15 AM"
    }
  ],
  "meta": {
    "total": 25,
    "per_page": 15,
    "current_page": 1,
    "last_page": 2
  }
}
```

## D4 Sub-Selection Support

The Receipt Module fully supports the D4 sub-selection feature that was recently added to the betting system. This feature allows tellers to place D4 bets with specific sub-selections (S2 or S3).

### Key Points About D4 Sub-Selection

1. **What it is**: D4 sub-selections allow bettors to win if either:
   - The full 4-digit number matches (standard D4 win)
   - The last 2 digits match (D4-S2 sub-selection)
   - The last 3 digits match (D4-S3 sub-selection)

2. **How it works in the API**:
   - When adding a bet to a receipt, include the `d4_sub_selection` field with value `"S2"` or `"S3"` for D4 game types
   - The field is optional and should only be included for D4 game types
   - The winning logic automatically handles sub-selections when results are posted

3. **Validation rules**:
   - The API validates that `d4_sub_selection` is only used with D4 game types
   - Valid values are only `"S2"` or `"S3"`

### Example D4 Sub-Selection Bet

```json
{
  "bet_number": "123",
  "amount": 500,
  "draw_id": 10,
  "game_type_id": 1,  // Assuming 1 is D4 game type
  "d4_sub_selection": "S2"
}
```

## Real-World Usage Scenarios

### Scenario 1: Teller Serves a Customer with Multiple Bets

1. **Customer Approach**: A customer approaches the teller and wants to place multiple bets for the 2PM draw.

2. **Teller Actions**:
   - Teller opens the betting app
   - App automatically calls `GET /api/receipts/draft` to fetch or create a draft receipt
   - For each bet the customer requests:
     - Teller enters bet details (number, amount, game type)
     - App calls `POST /api/receipts/{receipt}/bets` to add the bet
     - UI updates to show all bets in the current receipt
   - When all bets are added, teller confirms with the customer
   - Teller finalizes the receipt by calling `POST /api/receipts/{receipt}/place`
   - App prints a single receipt with all bets listed

3. **Result**: Customer receives one consolidated receipt instead of multiple tickets, saving paper and providing a more professional experience.

### Scenario 2: Teller Makes a Mistake and Needs to Edit

1. **Error Occurs**: While entering bets, the teller realizes they entered the wrong amount for a bet.

2. **Correction Process**:
   - Teller selects the incorrect bet from the list
   - App shows edit options for the selected bet
   - Teller updates the amount
   - App calls `PUT /api/receipts/{receipt}/bets/{bet}` to update the bet
   - Receipt total is automatically recalculated

3. **Alternative**: If the bet was completely wrong:
   - Teller selects the incorrect bet and clicks "Remove"
   - App calls `DELETE /api/receipts/{receipt}/bets/{bet}` to remove the bet
   - Teller can then add the correct bet

### Scenario 3: Customer Changes Mind or Leaves

1. **Customer Leaves**: A customer changes their mind or leaves without completing the transaction.

2. **Teller Actions**:
   - Teller clicks "Cancel Receipt"
   - App calls `POST /api/receipts/{receipt}/cancel`
   - All bets in the draft are deleted
   - System is ready for the next customer

## Workflow Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Customer  │     │    Teller   │     │     App     │     │     API     │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │                   │
       │    Place Bets     │                   │                   │
       │ ─────────────────>│                   │                   │
       │                   │                   │                   │
       │                   │     Open App      │                   │
       │                   │ ─────────────────>│                   │
       │                   │                   │  GET /receipts/draft
       │                   │                   │ ─────────────────>│
       │                   │                   │                   │
       │                   │                   │<─ ─ ─ ─ ─ ─ ─ ─ ─│
       │                   │                   │   Draft Receipt   │
       │                   │                   │                   │
       │                   │  Enter Bet #1     │                   │
       │                   │ ─────────────────>│                   │
       │                   │                   │  POST /receipts/{id}/bets
       │                   │                   │ ─────────────────>│
       │                   │                   │                   │
       │                   │                   │<─ ─ ─ ─ ─ ─ ─ ─ ─│
       │                   │                   │  Updated Receipt  │
       │                   │                   │                   │
       │                   │  Enter Bet #2     │                   │
       │                   │ ─────────────────>│                   │
       │                   │                   │  POST /receipts/{id}/bets
       │                   │                   │ ─────────────────>│
       │                   │                   │                   │
       │                   │                   │<─ ─ ─ ─ ─ ─ ─ ─ ─│
       │                   │                   │  Updated Receipt  │
       │                   │                   │                   │
       │                   │ Finalize Receipt  │                   │
       │                   │ ─────────────────>│                   │
       │                   │                   │  POST /receipts/{id}/place
       │                   │                   │ ─────────────────>│
       │                   │                   │                   │
       │                   │                   │<─ ─ ─ ─ ─ ─ ─ ─ ─│
       │                   │                   │ Finalized Receipt │
       │                   │                   │                   │
       │                   │    Print Receipt  │                   │
       │                   │<─ ─ ─ ─ ─ ─ ─ ─ ─│                   │
       │                   │                   │                   │
       │  Receipt with     │                   │                   │
       │  Multiple Bets    │                   │                   │
       │<─ ─ ─ ─ ─ ─ ─ ─ ─│                   │                   │
       │                   │                   │                   │
```

## Implementation Notes for Frontend Developers

### Key Points

1. **Single Draft Receipt**: The system only allows one draft receipt per teller at a time. When a teller opens the app, always fetch the current draft or create a new one.

2. **Automatic Ticket ID Generation**: Ticket IDs are automatically generated when a receipt is finalized. The format is `LB-YYMMDD-HHMM-XXXX` where XXXX is a random 4-character string.

3. **Validation**: All bet validation (sold out, bet cap, winning amount, etc.) happens on the server side. The frontend should handle error responses appropriately.

4. **Backward Compatibility**: The original `placeBet` API endpoint is still available for backward compatibility with older apps.

5. **Error Handling**: Always check the `status` field in the response to determine if the operation was successful.

### Frontend Implementation Tips

1. **Receipt State Management**: Maintain the current receipt state in your application and update it after each API call.

2. **Optimistic Updates**: For better UX, you can optimistically update the UI before receiving the API response, but be prepared to revert changes if the API returns an error.

3. **Polling**: If your app needs to stay in sync with the server, consider polling the draft receipt endpoint periodically.

4. **Error Messages**: Display user-friendly error messages based on the API response.

5. **Receipt Printing**: Format the receipt data for printing based on the finalized receipt response.

## Contact

For any questions or issues regarding the Receipt Module API, please contact the development team.

**Last Updated:** June 2, 2025
