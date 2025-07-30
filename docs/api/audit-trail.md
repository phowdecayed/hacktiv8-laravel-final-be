# Audit Trail API Documentation

## Overview

API Audit Trail digunakan untuk mencatat dan melacak semua aktivitas CRUD (Create, Read, Update, Delete) yang dilakukan pada model-model utama seperti Product, Category, dan Transaction. Setiap perubahan akan dicatat dengan detail termasuk user yang melakukan, waktu, IP address, dan perubahan data.

⬅️ [Kembali ke Halaman Utama](index.md)

## Features

- **Complete Activity Tracking**: Mencatat semua aktivitas CRUD
- **User Tracking**: Mengetahui siapa yang melakukan perubahan
- **Data Change Tracking**: Melihat data sebelum dan sesudah perubahan
- **IP Address & User Agent Tracking**: Untuk keamanan dan audit
- **Soft Delete Support**: Mencatat aktivitas restore
- **Filtering & Searching**: Filter berdasarkan model, user, tanggal, dll
- **Pagination**: Support untuk large datasets

## Endpoints

### 1. Get All Audit Trails

**Endpoint:** `GET /api/audit-trails`

**Description:** Mendapatkan semua audit trails dengan filtering dan pagination.

**Query Parameters:**
- `model_type` (optional): Filter berdasarkan tipe model (Product, Category, Transaction)
- `action` (optional): Filter berdasarkan jenis action (created, updated, deleted, restored)
- `user_id` (optional): Filter berdasarkan ID user
- `date_from` (optional): Filter dari tanggal (format: YYYY-MM-DD)
- `date_to` (optional): Filter sampai tanggal (format: YYYY-MM-DD)
- `sort` (optional): Urutkan berdasarkan field (created_at, updated_at) - default: created_at
- `order` (optional): Urutan (asc, desc) - default: desc
- `limit` (optional): Jumlah data per halaman (1-100) - default: 20

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "model_type": "Product",
      "model_id": 1,
      "action": "created",
      "old_values": null,
      "new_values": {
        "name": "Laptop Gaming",
        "price": 15000000,
        "category_id": 1,
        "user_id": 1
      },
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-07-28T13:00:00.000000Z",
      "updated_at": "2024-07-28T13:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "first_page_url": "http://localhost:8000/api/audit-trails?page=1",
  "from": 1,
  "last_page": 3,
  "last_page_url": "http://localhost:8000/api/audit-trails?page=3",
  "next_page_url": "http://localhost:8000/api/audit-trails?page=2",
  "path": "http://localhost:8000/api/audit-trails",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 50
}
```

**Response Error (400 - Bad Request):**
```json
{
    "date_from": [
        "The date from does not match the format Y-m-d."
    ]
}
```

### 2. Get Specific Audit Trail

**Endpoint:** `GET /api/audit-trails/{id}`

**Description:** Mendapatkan detail audit trail berdasarkan ID.

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "model_type": "Product",
  "model_id": 1,
  "action": "updated",
  "old_values": {
    "name": "Old Product Name",
    "price": 10000000
  },
  "new_values": {
    "name": "New Product Name",
    "price": 12000000
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0...",
  "created_at": "2024-07-28T13:00:00.000000Z",
  "updated_at": "2024-07-28T13:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### 3. Get Audit Trails for Specific Model

**Endpoint:** `GET /api/audit-trails/model/{modelType}/{modelId}`

**Description:** Mendapatkan semua audit trails untuk model tertentu.

**Parameters:**
- `modelType`: Tipe model (Product, Category, Transaction)
- `modelId`: ID dari model tersebut

**Query Parameters:**
- `limit` (optional): Jumlah data per halaman (1-100) - default: 20

**Example:** `GET /api/audit-trails/model/Product/1`

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "model_type": "Product",
      "model_id": 1,
      "action": "created",
      "old_values": null,
      "new_values": {
        "name": "Laptop Gaming",
        "price": 15000000
      },
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-07-28T13:00:00.000000Z",
      "updated_at": "2024-07-28T13:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "first_page_url": "http://localhost:8000/api/audit-trails/model/Product/1?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://localhost:8000/api/audit-trails/model/Product/1?page=1",
  "next_page_url": null,
  "path": "http://localhost:8000/api/audit-trails/model/Product/1",
  "per_page": 20,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

### 4. Get My Audit Trails

**Endpoint:** `GET /api/my-audit-trails`

**Description:** Mendapatkan audit trails untuk user yang sedang login.

**Query Parameters:**
- `model_type` (optional): Filter berdasarkan tipe model
- `action` (optional): Filter berdasarkan jenis action
- `date_from` (optional): Filter dari tanggal
- `date_to` (optional): Filter sampai tanggal
- `sort` (optional): Urutkan berdasarkan field - default: created_at
- `order` (optional): Urutan - default: desc
- `limit` (optional): Jumlah data per halaman - default: 20

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "model_type": "Product",
      "model_id": 1,
      "action": "created",
      "old_values": null,
      "new_values": {
        "name": "Laptop Gaming",
        "price": 15000000
      },
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-07-28T13:00:00.000000Z",
      "updated_at": "2024-07-28T13:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "first_page_url": "http://localhost:8000/api/my-audit-trails?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://localhost:8000/api/my-audit-trails?page=1",
  "next_page_url": null,
  "path": "http://localhost:8000/api/my-audit-trails",
  "per_page": 20,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

**Response Error (400 - Bad Request):**
```json
{
    "date_from": [
        "The date from does not match the format Y-m-d."
    ]
}
```

## Model Tracking

### Product Model
- **Tracked Actions**: create, update, delete
- **Tracked Fields**: All fields including name, description, price, category_id
- **Image Changes**: Tracked as part of product updates

### Category Model
- **Tracked Actions**: create, update, delete
- **Tracked Fields**: name, description
- **Product Relationship**: Changes in product-category relationships are tracked

### Transaction Model
- **Tracked Actions**: create, update, delete
- **Tracked Fields**: total_amount, status, notes
- **Status Changes**: All status changes (pending, completed, cancelled) are tracked

## Security Considerations

- **Authentication Required**: All endpoints require valid authentication token
- **User Tracking**: Each action is associated with the authenticated user
- **IP Address Logging**: Client IP address is recorded for security analysis
- **User Agent Logging**: Browser/device information is captured
- **Data Privacy**: Sensitive data in audit logs is handled securely

## Usage Examples

### Get all audit trails for Product model
```bash
curl -X GET "http://localhost:8000/api/audit-trails?model_type=Product" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get audit trails for specific product
```bash
curl -X GET "http://localhost:8000/api/audit-trails/model/Product/1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get audit trails with date filter
```bash
curl -X GET "http://localhost:8000/api/audit-trails?date_from=2024-07-01&date_to=2024-07-31" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Get user's own audit trails
```bash
curl -X GET "http://localhost:8000/api/my-audit-trails" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Error Handling

- **404 Not Found**: When audit trail or model is not found
- **400 Bad Request**: When query parameters are invalid
- **401 Unauthorized**: When authentication token is missing or invalid
- **403 Forbidden**: When user doesn't have permission to access the resource

## Best Practices

1. **Regular Monitoring**: Monitor audit trails for suspicious activities
2. **Data Retention**: Implement data retention policies for audit logs
3. **Performance**: Use pagination for large datasets
4. **Filtering**: Use appropriate filters to reduce data volume
5. **Security**: Regularly review audit logs for security analysis
