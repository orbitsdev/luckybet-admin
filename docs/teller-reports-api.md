# Teller Reports API Documentation

This document provides detailed information about the Teller Reports API endpoints available in the LuckyBet Admin system. These endpoints are designed to help tellers and administrators access sales data, hits, and other important metrics.

## Table of Contents

- [Authentication](#authentication)
- [Tallysheet Report](#tallysheet-report)
- [Sales Report](#sales-report)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Example Implementations](#example-implementations)

## Authentication

All API endpoints require authentication. Include your API token in the request header:

```
Authorization: Bearer {your_api_token}
```

## Tallysheet Report

The Tallysheet Report provides a comprehensive breakdown of bets, sales, hits, and gross profit by draw.

### Endpoint

```
GET /api/teller/tallysheet
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | string (YYYY-MM-DD) | Yes | The date to generate the report for |
| teller_id | integer | No | Filter by specific teller ID |
| location_id | integer | No | Filter by specific location ID |
| draw_id | integer | No | Filter by specific draw ID |

### Example Request

```
GET /api/teller/tallysheet?date=2025-05-08&teller_id=3
```

### Example Response

```json
{
  "success": true,
  "message": "Tallysheet report generated",
  "data": {
    "date": "2025-05-08",
    "date_formatted": "May 8, 2025",
    "gross": 5000,
    "gross_formatted": "₱5,000.00",
    "sales": 5000,
    "sales_formatted": "₱5,000.00",
    "hits": 1000,
    "hits_formatted": "₱1,000.00",
    "kabig": 4000,
    "kabig_formatted": "₱4,000.00",
    "voided": 0,
    "voided_formatted": "₱0.00",
    "per_draw": [
      {
        "draw_id": 11,
        "type": null,
        "winning_number": "23",
        "draw_time": "14:00:00",
        "draw_time_formatted": "2:00 PM",
        "draw_label": "Draw #11: 2:00 PM",
        "gross": 2000,
        "gross_formatted": "₱2,000.00",
        "sales": 2000,
        "sales_formatted": "₱2,000.00",
        "hits": 500,
        "hits_formatted": "₱500.00",
        "kabig": 1500,
        "kabig_formatted": "₱1,500.00"
      },
      {
        "draw_id": 12,
        "type": null,
        "winning_number": "23",
        "draw_time": "17:00:00",
        "draw_time_formatted": "5:00 PM",
        "draw_label": "Draw #12: 5:00 PM",
        "gross": 3000,
        "gross_formatted": "₱3,000.00",
        "sales": 3000,
        "sales_formatted": "₱3,000.00",
        "hits": 500,
        "hits_formatted": "₱500.00",
        "kabig": 2500,
        "kabig_formatted": "₱2,500.00"
      }
    ]
  }
}
```

### Visualization Example

```
+----------------------------------------------------------+
|                  TALLYSHEET REPORT                       |
|                    May 8, 2025                           |
+----------------------------------------------------------+
| SUMMARY                                                  |
| Total Sales: ₱5,000.00                                   |
| Total Hits:  ₱1,000.00                                   |
| Total Gross: ₱4,000.00                                   |
| Voided Bets: ₱0.00                                       |
+----------------------------------------------------------+
| DRAW BREAKDOWN                                           |
+----------------------------------------------------------+
| Draw #11: 2:00 PM                                        |
| Sales:     ₱2,000.00                                     |
| Hits:      ₱500.00                                       |
| Gross:     ₱1,500.00                                     |
+----------------------------------------------------------+
| Draw #12: 5:00 PM                                        |
| Sales:     ₱3,000.00                                     |
| Hits:      ₱500.00                                       |
| Gross:     ₱2,500.00                                     |
+----------------------------------------------------------+
```

## Sales Report

The Sales Report provides a summary of sales data for the logged-in teller, broken down by draw.

### Endpoint

```
GET /api/teller/sales
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | string (YYYY-MM-DD) | No | The date to generate the report for (defaults to today) |
| draw_id | integer | No | Filter by specific draw ID |

### Example Request

```
GET /api/teller/sales?date=2025-05-08
```

### Example Response

```json
{
  "success": true,
  "message": "Tally sheet generated successfully",
  "data": {
    "date": "2025-05-08",
    "date_formatted": "May 8, 2025",
    "totals": {
      "sales": 5000,
      "sales_formatted": "₱5,000.00",
      "hits": 1000,
      "hits_formatted": "₱1,000.00",
      "gross": 4000,
      "gross_formatted": "₱4,000.00",
      "voided": 2,
      "voided_formatted": "2 bet(s)"
    },
    "draws": [
      {
        "draw_id": 11,
        "time": "2:00 PM",
        "time_formatted": "2:00 PM",
        "game_type_code": "S2",
        "game_type_name": "Swertres 2",
        "winning_number": "23",
        "sales": 2000,
        "sales_formatted": "₱2,000.00",
        "hits": 500,
        "hits_formatted": "₱500.00",
        "gross": 1500,
        "gross_formatted": "₱1,500.00",
        "voided": 1,
        "voided_formatted": "1 bet(s)",
        "draw_label": "Draw #11: 2:00 PM (Swertres 2)"
      },
      {
        "draw_id": 12,
        "time": "5:00 PM",
        "time_formatted": "5:00 PM",
        "game_type_code": "S3",
        "game_type_name": "Swertres 3",
        "winning_number": "123",
        "sales": 3000,
        "sales_formatted": "₱3,000.00",
        "hits": 500,
        "hits_formatted": "₱500.00",
        "gross": 2500,
        "gross_formatted": "₱2,500.00",
        "voided": 1,
        "voided_formatted": "1 bet(s)",
        "draw_label": "Draw #12: 5:00 PM (Swertres 3)"
      }
    ]
  }
}
```

### Visualization Example

```
+----------------------------------------------------------+
|                    SALES REPORT                          |
|                    May 8, 2025                           |
+----------------------------------------------------------+
| SUMMARY                                                  |
| Total Sales: ₱5,000.00                                   |
| Total Hits:  ₱1,000.00                                   |
| Total Gross: ₱4,000.00                                   |
| Voided Bets: 2 bet(s)                                    |
+----------------------------------------------------------+
| DRAW BREAKDOWN                                           |
+----------------------------------------------------------+
| Draw #11: 2:00 PM (Swertres 2)                           |
| Winning Number: 23                                        |
| Sales:     ₱2,000.00                                     |
| Hits:      ₱500.00                                       |
| Gross:     ₱1,500.00                                     |
| Voided:    1 bet(s)                                      |
+----------------------------------------------------------+
| Draw #12: 5:00 PM (Swertres 3)                           |
| Winning Number: 123                                       |
| Sales:     ₱3,000.00                                     |
| Hits:      ₱500.00                                       |
| Gross:     ₱2,500.00                                     |
| Voided:    1 bet(s)                                      |
+----------------------------------------------------------+
```

## Response Format

All API responses follow a consistent format:

```json
{
  "success": true|false,
  "message": "Human-readable message",
  "data": { ... } // Response data object
}
```

## Error Handling

When an error occurs, the API will return an error response:

```json
{
  "success": false,
  "message": "Error message describing what went wrong",
  "errors": { ... } // Optional validation errors
}
```

Common error codes:
- 400: Bad Request (invalid parameters)
- 401: Unauthorized (invalid or missing token)
- 403: Forbidden (insufficient permissions)
- 404: Not Found
- 500: Server Error

## Example Implementations

### Mobile App Implementation

```javascript
// Example using React Native with Axios
import axios from 'axios';

const fetchTallysheet = async (date, tellerId = null) => {
  try {
    const params = { date };
    if (tellerId) params.teller_id = tellerId;
    
    const response = await axios.get('https://api.luckybet.com/api/teller/tallysheet', {
      params,
      headers: {
        'Authorization': `Bearer ${userToken}`
      }
    });
    
    if (response.data.success) {
      // Process the data
      const report = response.data.data;
      
      // Display summary
      console.log(`Date: ${report.date_formatted}`);
      console.log(`Total Sales: ${report.sales_formatted}`);
      console.log(`Total Hits: ${report.hits_formatted}`);
      console.log(`Total Gross: ${report.kabig_formatted}`);
      
      // Display per-draw breakdown
      report.per_draw.forEach(draw => {
        console.log(`\n${draw.draw_label}`);
        console.log(`Sales: ${draw.sales_formatted}`);
        console.log(`Hits: ${draw.hits_formatted}`);
        console.log(`Gross: ${draw.kabig_formatted}`);
      });
    } else {
      console.error(response.data.message);
    }
  } catch (error) {
    console.error('Failed to fetch tallysheet:', error);
  }
};

// Usage
fetchTallysheet('2025-05-08', 3);
```

### Web Dashboard Implementation

```javascript
// Example using Vue.js with Axios
import axios from 'axios';

export default {
  data() {
    return {
      date: new Date().toISOString().split('T')[0],
      salesReport: null,
      loading: false,
      error: null
    };
  },
  methods: {
    async fetchSalesReport() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get('/api/teller/sales', {
          params: { date: this.date }
        });
        
        if (response.data.success) {
          this.salesReport = response.data.data;
        } else {
          this.error = response.data.message;
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch sales report';
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.fetchSalesReport();
  },
  template: `
    <div class="sales-report">
      <h1>Sales Report</h1>
      
      <div class="date-picker">
        <label for="date">Select Date:</label>
        <input type="date" id="date" v-model="date" @change="fetchSalesReport">
      </div>
      
      <div v-if="loading">Loading...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      <div v-else-if="salesReport" class="report-content">
        <div class="report-header">
          <h2>{{ salesReport.date_formatted }}</h2>
        </div>
        
        <div class="report-summary">
          <div class="summary-item">
            <span class="label">Total Sales:</span>
            <span class="value">{{ salesReport.totals.sales_formatted }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Total Hits:</span>
            <span class="value">{{ salesReport.totals.hits_formatted }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Total Gross:</span>
            <span class="value">{{ salesReport.totals.gross_formatted }}</span>
          </div>
          <div class="summary-item">
            <span class="label">Voided Bets:</span>
            <span class="value">{{ salesReport.totals.voided_formatted }}</span>
          </div>
        </div>
        
        <div class="draws-breakdown">
          <h3>Draw Breakdown</h3>
          <div v-for="draw in salesReport.draws" :key="draw.draw_id" class="draw-item">
            <h4>{{ draw.draw_label }}</h4>
            <div class="draw-details">
              <div class="detail-item">
                <span class="label">Winning Number:</span>
                <span class="value">{{ draw.winning_number }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Sales:</span>
                <span class="value">{{ draw.sales_formatted }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Hits:</span>
                <span class="value">{{ draw.hits_formatted }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Gross:</span>
                <span class="value">{{ draw.gross_formatted }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Voided:</span>
                <span class="value">{{ draw.voided_formatted }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `
};
```

## Important Notes for Developers

1. **Game Types**: The system supports multiple game types (S2, S3, D4) with different digit counts. The winning number calculation is based on the game type of each bet.

2. **Formatted Values**: All monetary values are provided in both raw format (for calculations) and formatted format (for display) with the ₱ symbol and proper number formatting.

3. **Draw Labels**: Use the provided `draw_label` field for user-friendly display of draw information.

4. **Date Handling**: Always use the YYYY-MM-DD format for date parameters.

5. **Error Handling**: Implement proper error handling to provide a good user experience when API calls fail.

6. **Caching**: Consider implementing client-side caching for reports to reduce server load and improve performance.
