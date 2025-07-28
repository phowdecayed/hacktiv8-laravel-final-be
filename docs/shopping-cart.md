# Shopping Cart API Documentation

## 📋 Ringkasan
Shopping Cart API memungkinkan pengguna untuk mengelola keranjang belanja mereka sebelum melakukan transaksi. API ini menyediakan fitur untuk menambah, mengubah, menghapus, dan melihat item dalam keranjang belanja.

## 🚀 Fitur Utama
- **CRUD Operations**: Create, Read, Update, Delete item dalam keranjang
- **Stock Validation**: Validasi stok otomatis saat menambah/mengubah item
- **Price Calculation**: Kalkulasi harga otomatis berdasarkan quantity
- **User Isolation**: Setiap user memiliki keranjang yang terpisah
- **Soft Deletes**: Item yang dihapus dapat dipulihkan jika diperlukan
- **Batch Operations**: Update quantity multiple item sekaligus

## 🔐 Autentikasi
Semua endpoint memerlukan autentikasi menggunakan Bearer Token:
```
Authorization: Bearer {your_access_token}
```

## 📊 Model Structure

### Shopping Cart Model
```json
{
  "id": 1,
  "user_id": 1,
  "product_id": 1,
  "quantity": 3,
  "price": 150000,
  "total": 450000,
  "created_at": "2024-07-28T10:00:00.000000Z",
  "updated_at": "2024-07-28T10:30:00.000000Z",
  "product": {
    "id": 1,
    "name": "Laptop Gaming",
    "description": "High performance gaming laptop",
    "price": 150000,
    "stock": 10,
    "category_id": 1,
    "images": [...]
  }
}
```

## 📋 Endpoints

### 1. Get Shopping Cart Items
Mendapatkan semua item dalam keranjang belanja user yang sedang login.

**Endpoint:** `GET /api/cart`

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Query Parameters:**
- `sort` (optional): string - Sort by: created_at, updated_at, price, quantity (default: created_at)
- `order` (optional): string - Order direction: asc, desc (default: asc)
- `limit` (optional): integer - Jumlah item per halaman (default: 15, max: 100)

**Response Success (200):**
```json
{
  "message": "Shopping cart items retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 150000,
        "total": 300000,
        "created_at": "2024-07-28T10:00:00.000000Z",
        "updated_at": "2024-07-28T10:30:00.000000Z",
        "product": {
          "id": 1,
          "name": "Laptop Gaming",
          "description": "High performance gaming laptop",
          "price": 150000,
          "stock": 10,
          "category": {
            "id": 1,
            "name": "Electronics"
          },
          "images": [
            {
              "id": 1,
              "image_path": "http://localhost:8000/storage/product_images/laptop1.jpg"
            }
          ]
        }
      }
    ],
    "total": 300000,
    "item_count": 1
  }
}
```

### 2. Add Item to Shopping Cart
Menambahkan item baru ke keranjang belanja.

**Endpoint:** `POST /api/cart`

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Field Validations:**
- `product_id` (required): integer - ID produk yang valid
- `quantity` (required): integer - Minimal 1, maksimal sesuai stok produk

**Response Success (201):**
```json
{
  "message": "Item added to shopping cart successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "product_id": 1,
    "quantity": 2,
    "price": 150000,
    "total": 300000,
    "created_at": "2024-07-28T10:00:00.000000Z",
    "updated_at": "2024-07-28T10:00:00.000000Z",
    "product": {
      "id": 1,
      "name": "Laptop Gaming",
      "price": 150000,
      "stock": 10
    }
  }
}
```

**Response Error - Insufficient Stock (422):**
```json
{
  "message": "Insufficient stock",
  "errors": {
    "quantity": [
      "The requested quantity exceeds available stock."
    ]
  },
  "available_stock": 5
}
```

**Response Error - Product Not Found (404):**
```json
{
  "message": "Product not found"
}
```

**Response Error - Duplicate Item (422):**
```json
{
  "message": "Item already exists in cart. Use update endpoint to change quantity."
}
```

### 3. Update Cart Item
Memperbarui quantity item dalam keranjang.

**Endpoint:** `PUT /api/cart/{id}`

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

**URL Parameters:**
- `id` (required): integer - ID item dalam keranjang

**Request Body:**
```json
{
  "quantity": 5
}
```

**Field Validations:**
- `quantity` (required): integer - Minimal 1, maksimal sesuai stok produk

**Response Success (200):**
```json
{
  "message": "Cart item updated successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "product_id": 1,
    "quantity": 5,
    "price": 150000,
    "total": 750000,
    "created_at": "2024-07-28T10:00:00.000000Z",
    "updated_at": "2024-07-28T10:30:00.000000Z",
    "product": {
      "id": 1,
      "name": "Laptop Gaming",
      "price": 150000,
      "stock": 10
    }
  }
}
```

**Response Error - Insufficient Stock (422):**
```json
{
  "message": "Insufficient stock",
  "available_stock": 8
}
```

### 4. Remove Item from Cart
Menghapus item dari keranjang belanja.

**Endpoint:** `DELETE /api/cart/{id}`

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID item dalam keranjang

**Response Success (200):**
```json
{
  "message": "Item removed from shopping cart successfully"
}
```

### 5. Clear Shopping Cart
Menghapus semua item dari keranjang belanja user yang sedang login.

**Endpoint:** `DELETE /api/cart`

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Response Success (200):**
```json
{
  "message": "Shopping cart cleared successfully",
  "cleared_items": 3
}
```

### 6. Update Cart Items in Batch
Memperbarui quantity multiple item sekaligus.

**Endpoint:** `PUT /api/cart/batch`

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "items": [
    {
      "id": 1,
      "quantity": 3
    },
    {
      "id": 2,
      "quantity": 1
    }
  ]
}
```

**Field Validations:**
- `items` (required): array - Array dari item yang akan diupdate
- `items.*.id` (required): integer - ID item dalam keranjang
- `items.*.quantity` (required): integer - Quantity baru

**Response Success (200):**
```json
{
  "message": "Batch update completed successfully",
  "data": {
    "updated": 2,
    "failed": 0,
    "errors": []
  }
}
```

## 🛡️ Validasi Stok

API ini menerapkan validasi stok yang ketat:

1. **Saat Menambah Item**: Stok harus cukup untuk quantity yang diminta
2. **Saat Update Quantity**: Validasi ulang stok untuk quantity baru
3. **Real-time Stock**: Stok produk diperiksa secara real-time
4. **Race Condition**: Menggunakan database transactions untuk menghindari race condition

## 🔄 Error Handling

### Common Error Responses

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated"
}
```

**403 Forbidden:**
```json
{
  "message": "This action is unauthorized"
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "product_id": [
      "The product_id field is required."
    ],
    "quantity": [
      "The quantity must be at least 1."
    ]
  }
}
```

**404 Not Found:**
```json
{
  "message": "Cart item not found"
}
```

## 📊 Usage Examples

### Scenario: Complete Shopping Flow

1. **Lihat Keranjang:**
```bash
curl -X GET http://localhost:8000/api/cart \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Tambah Item:**
```bash
curl -X POST http://localhost:8000/api/cart \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1, "quantity": 2}'
```

3. **Update Quantity:**
```bash
curl -X PUT http://localhost:8000/api/cart/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"quantity": 3}'
```

4. **Checkout (Create Transaction):**
```bash
curl -X POST http://localhost:8000/api/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1, "quantity": 3}'
```

5. **Bersihkan Keranjang:**
```bash
curl -X DELETE http://localhost:8000/api/cart \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🔗 Relasi dengan API Lainnya

Shopping Cart API terintegrasi dengan:
- **Product API**: Untuk informasi produk dan validasi stok
- **Transaction API**: Untuk proses checkout dari keranjang ke transaksi
- **User API**: Untuk user isolation dan tracking

## 📝 Catatan Penting

1. **User Isolation**: Setiap user hanya bisa mengakses keranjangnya sendiri
2. **Stock Sync**: Stok produk akan selalu sinkron dengan database
3. **Soft Delete**: Item yang dihapus masih bisa dipulihkan jika diperlukan
4. **Performance**: Menggunakan eager loading untuk optimasi query
5. **Security**: Semua endpoint dilindungi dengan autentikasi dan authorization