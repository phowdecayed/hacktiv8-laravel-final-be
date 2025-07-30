# Dashboard API Documentation

## Overview

The Dashboard API provides aggregated statistics and key metrics for the e-commerce platform, offering a quick overview of the system's health and performance. This endpoint is primarily intended for administrative and moderation roles.

⬅️ [Back to Main Page](index.md)

## Features

- **Key Statistics**: Total counts for users, products, categories, and transactions.
- **Sales Overview**: Total revenue generated from all transactions.
- **Recent Activity**: A list of the most recent transactions.
- **Performance Insights**: Top-selling products based on quantity sold.
- **Inventory Management**: List of low stock items.
- **User Activity**: Recent user registrations.

## Endpoints

### 1. Get Dashboard Statistics

**Endpoint:** `GET /api/dashboard/stats`

**Description:** Retrieves various statistics about the e-commerce platform. This endpoint requires `admin` or `moderator` role.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Response Success (200):**
```json
{
  "message": "Dashboard statistics retrieved successfully",
  "data": {
    "total_users": 100,
    "total_products": 500,
    "total_categories": 50,
    "total_transactions": 1200,
    "total_sales": 150000000.00,
    "recent_transactions": [
      {
        "id": 1200,
        "user_id": 5,
        "total_amount": 250000.00,
        "status": "delivered",
        "created_at": "2025-07-30T10:30:00.000000Z",
        "updated_at": "2025-07-30T10:30:00.000000Z"
      },
      {
        "id": 1199,
        "user_id": 12,
        "total_amount": 1200000.00,
        "status": "shipped",
        "created_at": "2025-07-29T18:00:00.000000Z",
        "updated_at": "2025-07-29T18:00:00.000000Z"
      }
    ],
    "top_selling_products": [
      {
        "name": "Laptop Gaming X",
        "total_quantity_sold": 150
      },
      {
        "name": "Smartphone Pro",
        "total_quantity_sold": 120
      }
    ],
    "low_stock_items": [
      {
        "id": 1,
        "name": "Product A",
        "stock": 5
      },
      {
        "id": 2,
        "name": "Product B",
        "stock": 8
      }
    ],
    "recent_user_registrations": [
      {
        "id": 1,
        "name": "New User 1",
        "email": "newuser1@example.com",
        "created_at": "2025-07-30T09:00:00.000000Z"
      },
      {
        "id": 2,
        "name": "New User 2",
        "email": "newuser2@example.com",
        "created_at": "2025-07-29T15:00:00.000000Z"
      }
    ]
  }
}
```

**Response Error (401 - Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

**Response Error (403 - Forbidden):**
```json
{
    "message": "Forbidden. Insufficient permissions.",
    "required_roles": ["admin", "moderator"],
    "your_role": "user"
}
```

## Security Considerations

- **Authentication Required**: This endpoint requires a valid authentication token.
- **Role-Based Access**: Only users with `admin` or `moderator` roles can access this endpoint.
- **Data Aggregation**: Data is aggregated and does not expose sensitive individual user or transaction details beyond what is necessary for statistics.

## Usage Example

### Get Dashboard Statistics
```bash
curl -X GET "http://localhost:8000/api/dashboard/stats" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. Get Sales Overview

**Endpoint:** `GET /api/dashboard/sales`

**Description:** Retrieves a detailed overview of sales data, including total sales, sales by month, and sales by category. This endpoint requires `admin` or `moderator` role.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Response Success (200):**
```json
{
  "message": "Sales overview retrieved successfully",
  "data": {
    "total_sales": 150000000.00,
    "sales_by_month": [
      {
        "month": "2025-01",
        "total_sales": 10000000.00
      },
      {
        "month": "2025-02",
        "total_sales": 12000000.00
      }
    ],
    "sales_by_category": [
      {
        "category_name": "Electronics",
        "total_sales": 80000000.00
      },
      {
        "category_name": "Fashion",
        "total_sales": 30000000.00
      }
    ]
  }
}
```

**Response Error (401 - Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

**Response Error (403 - Forbidden):**
```json
{
    "message": "Forbidden. Insufficient permissions.",
    "required_roles": ["admin", "moderator"],
    "your_role": "user"
}
```

## Error Handling

- **401 Unauthorized**: When authentication token is missing or invalid.
- **403 Forbidden**: When the authenticated user does not have the required `admin` or `moderator` role.

## Best Practices

1. **Access Control**: Ensure proper role-based access control is enforced on the backend.
2. **Performance**: For very large datasets, consider caching strategies for these statistics to improve response times.
3. **Monitoring**: Regularly monitor the performance of this endpoint.

---

⬅️ [Back to Main Page](index.md)
