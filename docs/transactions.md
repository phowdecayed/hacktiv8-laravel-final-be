# Transactions API

Dokumentasi lengkap untuk manajemen transaksi dalam sistem e-commerce.

⬅️ [Kembali ke Halaman Utama](index.md)

## 🎯 Fitur Utama
- CRUD lengkap untuk transaksi
- Soft deletes untuk audit trail
- User tracking untuk accountability
- Status transaksi yang terstruktur
- Validasi stok dan harga otomatis
- Relasi dengan produk dan user
- Eager loading untuk performa optimal

## 📋 Endpoints Overview

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/transactions` | Mendapatkan semua transaksi (Admin) |
| POST | `/api/transactions` | Membuat transaksi baru |
| GET | `/api/transactions/{id}` | Mendapatkan detail transaksi |
| PUT | `/api/transactions/{id}` | Memperbarui transaksi |
| DELETE | `/api/transactions/{id}` | Menghapus transaksi (soft delete) |
| GET | `/api/my-transactions` | Mendapatkan transaksi user login |

## 🔐 Authentication

**Semua endpoints memerlukan Bearer Token untuk autentikasi.**

**Header yang diperlukan:**
```
Authorization: Bearer {your_access_token}
```

## 📊 Data Structure

### Transaction Model
```
{
    id: integer,
    user_id: integer,
    product_id: integer,
    quantity: integer,
    total_price: decimal,
    status: string,
    created_at: timestamp,
    updated_at: timestamp,
    deleted_at: timestamp|null,
    user: {
        id: integer,
        name: string,
        email: string
    },
    product: {
        id: integer,
        name: string,
        price: decimal,
        description: string,
        stock: integer
    }
}
```

## 🚦 Status Transaksi

| Status | Deskripsi | Keterangan |
|--------|-----------|------------|
| `pending` | Menunggu konfirmasi | Transaksi baru dibuat |
| `processing` | Sedang diproses | Admin sedang memproses |
| `shipped` | Dikirim | Barang sudah dikirim |
| `delivered` | Diterima | Barang sudah sampai |
| `cancelled` | Dibatalkan | Transaksi dibatalkan |
| `refunded` | Direfund | Uang dikembalikan |

## 💰 Price Calculation

### Automatic Calculation
- **Formula:** `total_price = quantity × product.price`
- **Validasi:** Stok harus cukup untuk quantity yang diminta
- **Rounding:** 2 decimal places untuk precision
- **Currency:** IDR (Indonesian Rupiah)

### Example Calculation
```
Product: Laptop Gaming - Rp 15,000,000
Quantity: 2
Total Price: 2 × 15,000,000 = Rp 30,000,000
```

## 🔍 Endpoints Detail

### 1. Get All Transactions (Admin Only)

**Endpoint:** `GET /api/transactions`

**Deskripsi:** Mendapatkan semua transaksi dari semua user. Hanya admin yang bisa mengakses.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Query Parameters (optional):**
- `status`: Filter berdasarkan status transaksi
- `user_id`: Filter berdasarkan user ID
- `product_id`: Filter berdasarkan product ID
- `date_from`: Filter transaksi dari tanggal (YYYY-MM-DD)
- `date_to`: Filter transaksi sampai tanggal (YYYY-MM-DD)
- `sort`: Sort berdasarkan kolom (created_at, total_price, quantity)
- `order`: Urutan sort (asc, desc)
- `limit`: Jumlah data per halaman (default: 10)

**Response Success (200):**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "product_id": 1,
            "quantity": 2,
            "total_price": 30000000,
            "status": "pending",
            "created_at": "2024-01-15T08:00:00.000000Z",
            "updated_at": "2024-01-15T08:00:00.000000Z",
            "deleted_at": null,
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "product": {
                "id": 1,
                "name": "Laptop Gaming",
                "price": 15000000,
                "description": "High-performance gaming laptop",
                "stock": 50
            }
        },
        {
            "id": 2,
            "user_id": 2,
            "product_id": 3,
            "quantity": 1,
            "total_price": 5000000,
            "status": "processing",
            "created_at": "2024-01-15T09:00:00.000000Z",
            "updated_at": "2024-01-15T09:00:00.000000Z",
            "deleted_at": null,
            "user": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "product": {
                "id": 3,
                "name": "Gaming Mouse",
                "price": 5000000,
                "description": "RGB gaming mouse",
                "stock": 100
            }
        }
    ]
}
```

### 2. Create New Transaction

**Endpoint:** `POST /api/transactions`

**Deskripsi:** Membuat transaksi baru untuk user yang sedang login.

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
    "quantity": 2,
    "status": "pending"
}
```

**Field Validations:**
- `product_id` (required): integer - ID produk yang valid
- `quantity` (required): integer - Minimal 1, maksimal sesuai stok produk
- `status` (optional): string - Salah satu dari: pending, processing, shipped, delivered, cancelled, refunded

**Response Success (201):**
```json
{
    "message": "Transaction created successfully",
    "data": {
        "id": 3,
        "user_id": 1,
        "product_id": 1,
        "quantity": 2,
        "total_price": 30000000,
        "status": "pending",
        "created_at": "2024-01-15T10:00:00.000000Z",
        "updated_at": "2024-01-15T10:00:00.000000Z",
        "deleted_at": null,
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "product": {
            "id": 1,
            "name": "Laptop Gaming",
            "price": 15000000,
            "description": "High-performance gaming laptop",
            "stock": 48
        }
    }
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "product_id": [
            "The product id field is required."
        ],
        "quantity": [
            "The quantity must be at least 1."
        ],
        "product_id": [
            "The selected product id is invalid."
        ],
        "quantity": [
            "The requested quantity exceeds available stock."
        ]
    }
}
```

**Response Error (400 - Stock):**
```json
{
    "message": "Insufficient stock for this product",
    "data": {
        "available_stock": 5,
        "requested_quantity": 10
    }
}
```

### 3. Get Transaction Detail

**Endpoint:** `GET /api/transactions/{id}`

**Deskripsi:** Mendapatkan detail transaksi berdasarkan ID.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID transaksi yang akan dilihat

**Response Success (200):**
```json
{
    "data": {
        "id": 1,
        "user_id": 1,
        "product_id": 1,
        "quantity": 2,
        "total_price": 30000000,
        "status": "pending",
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T08:00:00.000000Z",
        "deleted_at": null,
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "product": {
            "id": 1,
            "name": "Laptop Gaming",
            "price": 15000000,
            "description": "High-performance gaming laptop",
            "stock": 50,
            "images": [
                {
                    "id": 1,
                    "image_path": "products/laptop_1.jpg",
                    "is_primary": true
                }
            ],
            "categories": [
                {
                    "id": 1,
                    "name": "Electronics",
                    "description": "Electronic devices"
                }
            ]
        }
    }
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Transaction not found"
}
```

### 4. Update Transaction

**Endpoint:** `PUT /api/transactions/{id}`

**Deskripsi:** Memperbarui transaksi yang sudah ada. Hanya user yang membuat transaksi yang bisa mengupdate.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

**URL Parameters:**
- `id` (required): integer - ID transaksi yang akan diupdate

**Request Body:**
```json
{
    "quantity": 3,
    "status": "processing"
}
```

**Field Validations:**
- `quantity` (optional): integer - Minimal 1, maksimal sesuai stok produk
- `status` (optional): string - Salah satu dari: pending, processing, shipped, delivered, cancelled, refunded

**Response Success (200):**
```json
{
    "message": "Transaction updated successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "product_id": 1,
        "quantity": 3,
        "total_price": 45000000,
        "status": "processing",
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T11:00:00.000000Z",
        "deleted_at": null,
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "product": {
            "id": 1,
            "name": "Laptop Gaming",
            "price": 15000000,
            "description": "High-performance gaming laptop",
            "stock": 47
        }
    }
}
```

**Response Error (403 - Forbidden):**
```json
{
    "message": "You do not have permission to update this transaction"
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "quantity": [
            "The quantity must be at least 1."
        ],
        "status": [
            "The selected status is invalid."
        ]
    }
}
```

### 5. Delete Transaction

**Endpoint:** `DELETE /api/transactions/{id}`

**Deskripsi:** Soft delete transaksi. Transaksi tidak akan hilang permanen dari database.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID transaksi yang akan dihapus

**Response Success (200):**
```json
{
    "message": "Transaction deleted successfully"
}
```

**Response Error (403 - Forbidden):**
```json
{
    "message": "You do not have permission to delete this transaction"
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Transaction not found"
}
```

### 6. Get User's Transactions

**Endpoint:** `GET /api/my-transactions`

**Deskripsi:** Mendapatkan semua transaksi milik user yang sedang login.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Query Parameters (optional):**
- `status`: Filter berdasarkan status transaksi
- `date_from`: Filter transaksi dari tanggal (YYYY-MM-DD)
- `date_to`: Filter transaksi sampai tanggal (YYYY-MM-DD)
- `sort`: Sort berdasarkan kolom (created_at, total_price, quantity)
- `order`: Urutan sort (asc, desc)

**Response Success (200):**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "product_id": 1,
            "quantity": 2,
            "total_price": 30000000,
            "status": "pending",
            "created_at": "2024-01-15T08:00:00.000000Z",
            "updated_at": "2024-01-15T08:00:00.000000Z",
            "deleted_at": null,
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "product": {
                "id": 1,
                "name": "Laptop Gaming",
                "price": 15000000,
                "description": "High-performance gaming laptop",
                "stock": 50,
                "images": [
                    {
                        "id": 1,
                        "image_path": "products/laptop_1.jpg",
                        "is_primary": true
                    }
                ]
            }
        }
    ]
}
```

## 📝 Notes & Best Practices

### Soft Deletes
- Transaksi yang dihapus tidak akan hilang permanen dari database
- Data tetap tersimpan dengan `deleted_at` timestamp untuk audit trail
- Untuk restore transaksi, perlu akses langsung ke database atau endpoint khusus
- Implementasi keamanan: user hanya bisa restore transaksi miliknya sendiri

### User Tracking
- Setiap transaksi mencatat `user_id` dari user yang membuatnya
- User hanya bisa melihat dan mengupdate transaksi miliknya sendiri
- Admin bisa melihat semua transaksi untuk monitoring dan reporting
- Implementasi keamanan: authorization middleware untuk setiap endpoint

### Stock Management
- **Auto-decrement:** Stok produk berkurang otomatis saat transaksi dibuat
- **Auto-increment:** Stok produk bertambah saat transaksi dibatalkan atau dihapus
- **Stock validation:** Validasi stok sebelum transaksi dibuat
- **Stock alert:** Notifikasi saat stok rendah (opsional)

### Price Calculation
- **Automatic calculation:** Total harga dihitung otomatis berdasarkan quantity dan harga produk
- **Price locking:** Harga tetap ter-lock saat transaksi dibuat, meskipun harga produk berubah
- **Currency handling:** Support untuk IDR dengan 2 decimal places
- **Tax calculation:** Tax bisa ditambahkan sebagai field tambahan (opsional)

### Status Management
- **Status flow:** Pending → Processing → Shipped → Delivered
- **Status validation:** Validasi transisi status yang valid
- **Status history:** Catat perubahan status untuk audit trail
- **Email notifications:** Kirim notifikasi saat status berubah (opsional)

### Eager Loading
- **N+1 Prevention:** Menggunakan eager loading untuk mengoptimalkan query
- **Related data:** Include user dan product data dalam response
- **Image data:** Include product images untuk UI yang lebih lengkap
- **Performance:** Mengurangi jumlah query ke database

### Validation Rules
- **Quantity validation:** Pastikan quantity tidak melebihi stok yang tersedia
- **Product validation:** Pastikan produk masih aktif dan tersedia
- **User validation:** Pastikan user yang membuat transaksi valid
- **Status validation:** Pastikan status transaksi valid

## 🐛 Troubleshooting

### Common Errors

#### Insufficient Stock
```json
{
    "message": "Insufficient stock for this product",
    "data": {
        "available_stock": 5,
        "requested_quantity": 10
    }
}
```
**Solusi:**
- Kurangi jumlah quantity yang diminta
- Pilih produk dengan stok yang mencukupi
- Tunggu restock produk

#### Product Not Found
```json
{
    "message": "The selected product id is invalid."
}
```
**Solusi:**
- Pastikan product_id valid dan ada di database
- Pastikan produk tidak dalam status deleted
- Periksa spelling product_id

#### Permission Denied
```json
{
    "message": "You do not have permission to update this transaction"
}
```
**Solusi:**
- Pastikan token autentikasi valid
- Pastikan transaksi milik user yang sedang login
- Hubungi admin untuk akses yang diperlukan

#### Validation Errors
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "quantity": ["The quantity must be at least 1."],
        "status": ["The selected status is invalid."]
    }
}
```
**Solusi:**
- Pastikan quantity minimal 1
- Pastikan status valid sesuai daftar yang tersedia
- Periksa format data yang dikirim

### Database Issues
- **Foreign key constraint:** Pastikan product_id dan user_id valid
- **Data integrity:** Pastikan tidak ada data yang corrupted
- **Connection issues:** Periksa koneksi database

### Performance Issues
- **Slow queries:** Gunakan eager loading untuk optimasi
- **Large datasets:** Implement pagination untuk list transaksi
- **Memory usage:** Monitor memory usage untuk transaksi besar

## 📊 Usage Examples

### Create Transaction
```bash
curl -X POST \
  http://localhost:8000/api/transactions \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "product_id": 1,
    "quantity": 2,
    "status": "pending"
  }'
```

### Get User Transactions
```bash
curl -X GET \
  http://localhost:8000/api/my-transactions \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

### Update Transaction Status
```bash
curl -X PUT \
  http://localhost:8000/api/transactions/1 \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "status": "processing"
  }'
```

### Cancel Transaction
```bash
curl -X PUT \
  http://localhost:8000/api/transactions/1 \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "status": "cancelled"
  }'
```

## 🔧 Configuration

### Environment Variables
```env
TRANSACTIONS_PER_PAGE=10
ENABLE_STOCK_NOTIFICATIONS=true
ENABLE_EMAIL_NOTIFICATIONS=true
CURRENCY=IDR
DECIMAL_PLACES=2
```

### Database Indexes
```sql
-- Index untuk performa query
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_product_id ON transactions(product_id);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
```

## 📈 Monitoring & Analytics

### Transaction Metrics
- **Total transactions per user**
- **Total revenue per product**
- **Average order value**
- **Conversion rate**
- **Status distribution**

### Performance Monitoring
- **Query performance:** Monitor slow queries
- **API response time:** Monitor endpoint performance
- **Error rate:** Monitor error frequency
- **User activity:** Monitor user transaction patterns

### Business Intelligence
- **Best selling products**
- **Peak transaction times**
- **Customer lifetime value**
- **Churn rate analysis**
- **Revenue trends**

## 📋 Best Practices for Frontend

### Form Validation
- Validasi quantity di frontend sebelum submit
- Tampilkan stok yang tersedia secara real-time
- Validasi status transisi yang valid
- Tampilkan preview harga sebelum submit

### Error Handling
- Tangani error dengan user-friendly messages
- Tampilkan loading states untuk async operations
- Implement retry mechanism untuk network errors
- Log errors untuk debugging

### UI/UX Considerations
- Tampilkan status transaksi dengan warna yang sesuai
- Berikan konfirmasi sebelum menghapus transaksi
- Tampilkan detail produk dengan gambar
- Implement infinite scroll untuk list transaksi

### State Management
- Cache transaction data untuk performa yang lebih baik
- Implement optimistic updates untuk UX yang smooth
- Sync data dengan server secara berkala
- Handle offline scenarios dengan baik

---

⬅️ [Kembali ke Halaman Utama](index.md)