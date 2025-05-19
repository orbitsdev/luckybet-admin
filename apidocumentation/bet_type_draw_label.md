# Bet Type Draw Label Logic (`bet_type_draw_label`)

## Overview
The `bet_type_draw_label` field is returned on every bet object from the API. This field provides a human-friendly string for displaying the bet type, draw time, and (if applicable) D4 sub-selection, following these rules:

### Rules
1. **Standard D4 bet:**  
   - Label: `[DrawTime][GameType]`  
   - Example: `9PMD4`
2. **D4 with sub-selection (S2/S3):**  
   - Label: `[DrawTime][GameType]-[SubSelection]`  
   - Example: `9PMD4-S2`, `2PMD4-S3`
3. **Standalone S2/S3 bet:**  
   - Label: `[DrawTime][GameType]`  
   - Example: `2PMS2`
4. **S2/S3 bet that is a child of a D4 parent (combination bet):**  
   - Label: `[ParentDrawTime]D4-[S2 or S3]`  
   - Example:  
     - Customer bets `1234` (D4) with S2 combo `12`  
     - Child bet for `12` will have label: `9PMD4-S2`

### Implementation
#### **Backend (Laravel BetResource)**
- The API now returns a `bet_type_draw_label` field for every bet.
- This field is computed based on the bet’s type, draw, sub-selection, and parent relationship.

#### **Frontend (Flutter)**
- Use the `betTypeDrawLabel` property from the API for displaying the bet type in your table/list.
- No need to manually recompute the label on the frontend—just use the backend-provided value.

### **Real-world Scenarios**

#### **Scenario 1: Regular D4 bet**
- Bet: 1234, Game Type: D4, Draw: 9PM, No sub-selection
- Label: `9PMD4`

#### **Scenario 2: D4 with S2 sub-selection (combination)**
- Parent Bet: 1234 (D4), Draw: 9PM, Sub-selection: S2
- Child Bet: 12 (S2), Parent: 1234, Draw: 9PM
- Parent Label: `9PMD4-S2`
- Child Label: `9PMD4-S2`

#### **Scenario 3: Standalone S2 bet**
- Bet: 22, Game Type: S2, Draw: 2PM, No parent
- Label: `2PMS2`

#### **Scenario 4: S2 bet from D4 combination**
- Bet: 12, Game Type: S2, Draw: 9PM, Parent: 1234 (D4)
- Label: `9PMD4-S2`

### **How to Use in Frontend**
1. **Parse `bet_type_draw_label` from API response.**
2. **Display it directly in your bet list/table under “Bet Type”.**
3. **No need for custom logic in Flutter—just use the field as-is.**

---

## Example API Response

```json
{
  "id": 1,
  "bet_number": "12",
  "game_type": { "code": "S2" },
  "draw": { "draw_time_simple": "9PM" },
  "d4_sub_selection": null,
  "parent_id": 10,
  "bet_type_draw_label": "9PMD4-S2"
}
```

### More Example API Responses

#### 1. Regular D4 bet
```json
{
  "id": 2,
  "bet_number": "1234",
  "game_type": { "code": "D4" },
  "draw": { "draw_time_simple": "9PM" },
  "d4_sub_selection": null,
  "parent_id": null,
  "bet_type_draw_label": "9PMD4"
}
```

#### 2. D4 with sub-selection (e.g. S2)
```json
{
  "id": 3,
  "bet_number": "1234",
  "game_type": { "code": "D4" },
  "draw": { "draw_time_simple": "9PM" },
  "d4_sub_selection": "S2",
  "parent_id": null,
  "bet_type_draw_label": "9PMD4-S2"
}
```

#### 3. Standalone S2 bet
```json
{
  "id": 4,
  "bet_number": "22",
  "game_type": { "code": "S2" },
  "draw": { "draw_time_simple": "2PM" },
  "d4_sub_selection": null,
  "parent_id": null,
  "bet_type_draw_label": "2PMS2"
}
```

#### 4. S2 from D4 combination (child of D4)
```json
{
  "id": 5,
  "bet_number": "12",
  "game_type": { "code": "S2" },
  "draw": { "draw_time_simple": "9PM" },
  "d4_sub_selection": null,
  "parent_id": 3,
  "bet_type_draw_label": "9PMD4-S2"
}
```

#### 5. Standalone S3 bet
```json
{
  "id": 6,
  "bet_number": "345",
  "game_type": { "code": "S3" },
  "draw": { "draw_time_simple": "2PM" },
  "d4_sub_selection": null,
  "parent_id": null,
  "bet_type_draw_label": "2PMS3"
}
```

#### 6. S3 from D4 combination (child of D4)
```json
{
  "id": 7,
  "bet_number": "456",
  "game_type": { "code": "S3" },
  "draw": { "draw_time_simple": "9PM" },
  "d4_sub_selection": null,
  "parent_id": 3,
  "bet_type_draw_label": "9PMD4-S3"
}
```

  "parent_id": 10,
  "bet_type_draw_label": "9PMD4-S2"
}
```
