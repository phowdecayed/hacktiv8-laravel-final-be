# Product API

Dokumentasi lengkap untuk manajemen produk dan gambar produk dalam sistem.

⬅️ [Kembali ke Halaman Utama](index.md)

## 🎯 Fitur Utama
- CRUD lengkap untuk produk
- Upload multiple gambar produk
- Soft deletes untuk data recovery
- User tracking untuk audit trail
- Optimasi query dengan eager loading

## 📋 Endpoints Overview

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/products` | Mendapatkan semua produk |
| POST | `/api/products` | Membuat produk baru |
| GET | `/api/products/{id}` | Mendapatkan detail produk |
| POST | `/api/products/{id}` | Update produk (gunakan `_method: PUT`) |
| DELETE | `/api/products/{id}` | Hapus produk (soft delete) |

## 🔐 Authentication

**Semua endpoints memerlukan Bearer Token untuk autentikasi.**

**Header yang diperlukan:**
```
Authorization: Bearer {your_access_token}
```

## 📊 Data Structure

### Product Model
```
{
    id: integer,
    name: string,
    description: string|null,
    price: decimal(10,2),
    stock: integer,
    category_id: integer|null,
    user_id: integer,
    created_at: timestamp,
    updated_at: timestamp,
    deleted_at: timestamp|null,
    user: {
        id: integer,
        name: string,
        email: string
    },
    category: {
        id: integer,
        name: string
    },
    images: [
        {
            id: integer,
            product_id: integer,
            image_path: string,
            user_id: integer,
            created_at: timestamp,
            updated_at: timestamp,
            deleted_at: timestamp|null
        }
    ]
}
```

## 🔍 Endpoints Detail

### 1. List All Products

**Endpoint:** `GET /api/products`

**Deskripsi:** Mendapatkan daftar semua produk yang aktif (tidak terhapus), termasuk relasi gambar, user, dan kategori.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Query Parameters (optional):**
- `category_id`: Filter berdasarkan kategori
- `search`: Search berdasarkan nama produk
- `sort`: Sort berdasarkan kolom (name, price, created_at)
- `order`: Urutan sort (asc, desc)

**Response Success (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Laptop Gaming ASUS ROG",
            "description": "Laptop gaming high-end dengan RTX 4060",
            "price": "15000000.00",
            "category_id": 1,
            "user_id": 1,
            "created_at": "2024-01-15T08:00:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z",
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "category": {
                "id": 1,
                "name": "Electronics"
            },
            "images": [
                {
                    "id": 1,
                    "product_id": 1,
                    "image_path": "http://localhost:8000/storage/product_images/laptop_1.jpg",
                    "user_id": 1,
                    "created_at": "2024-01-15T08:00:00.000000Z",
                    "updated_at": "2024-01-15T08:00:00.000000Z"
                }
            ]
        }
    ]
}
```

### 2. Create New Product

**Endpoint:** `POST /api/products`

**Deskripsi:** Membuat produk baru dengan upload gambar opsional.

**Headers:**
```
Authorization: Bearer {your_access_token}
Content-Type: multipart/form-data
```

**Request Body:**
- `name` (required): string, max:255 - Nama produk
- `description` (optional): string|null - Deskripsi produk
- `price` (required): numeric, min:0 - Harga produk
- `stock` (required): integer, min:0 - Jumlah stock produk
- `category_id` (optional): integer, exists:categories - ID kategori
- `images[]` (optional): file, mimes:jpg,jpeg,png,gif, max:2048 - Gambar produk (multiple)

**Response Success (201):**
```json
{
    "message": "Product created successfully",
    "data": {
        "id": 2,
        "name": "Smartphone Samsung Galaxy S24",
        "description": "Smartphone flagship terbaru dari Samsung",
        "price": "12000000.00",
        "category_id": 1,
        "user_id": 1,
        "created_at": "2024-01-15T12:00:00.000000Z",
        "updated_at": "2024-01-15T12:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "category": {
            "id": 1,
            "name": "Electronics"
        },
        "images": [
            {
                "id": 2,
                "product_id": 2,
                "image_path": "http://localhost:8000/storage/product_images/samsung_s24_1.jpg",
                "user_id": 1,
                "created_at": "2024-01-15T12:00:00.000000Z",
                "updated_at": "2024-01-15T12:00:00.000000Z"
            }
        ]
    }
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "price": [
            "The price field is required."
        ],
        "images.0": [
            "The images.0 must be a file of type: jpg, jpeg, png, gif."
        ]
    }
}
```

### 3. Get Product Details

**Endpoint:** `GET /api/products/{id}`

**Deskripsi:** Mendapatkan detail produk berdasarkan ID, termasuk relasi lengkap.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID produk

**Response Success (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Laptop Gaming ASUS ROG",
        "description": "Laptop gaming high-end dengan RTX 4060",
        "price": "15000000.00",
        "category_id": 1,
        "user_id": 1,
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "category": {
            "id": 1,
            "name": "Electronics"
        },
        "images": [
            {
                "id": 1,
                "product_id": 1,
                "image_path": "http://localhost:8000/storage/product_images/laptop_1.jpg",
                "user_id": 1,
                "created_at": "2024-01-15T08:00:00.000000Z",
                "updated_at": "2024-01-15T08:00:00.000000Z"
            }
        ]
    }
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Product not found"
}
```

### 4. Update Product

**Endpoint:** `POST /api/products/{id}`

**Deskripsi:** Update produk existing. Gunakan `_method: PUT` untuk form-data atau gunakan `PUT` dengan JSON.

**Headers:**
```
Authorization: Bearer {your_access_token}
Content-Type: multipart/form-data
```

**URL Parameters:**
- `id` (required): integer - ID produk

**Request Body:**
- `_method` (required): string - Isi dengan "PUT" untuk form-data
- `name` (optional): string, max:255 - Nama produk
- `description` (optional): string|null - Deskripsi produk
- `price` (optional): numeric, min:0 - Harga produk
- `category_id` (optional): integer, exists:categories - ID kategori
- `images[]` (optional): file, mimes:jpg,jpeg,png,gif, max:2048 - Gambar baru (akan replace existing)

**Response Success (200):**
```json
{
    "message": "Product updated successfully",
    "data": {
        "id": 1,
        "name": "Laptop Gaming ASUS ROG Updated",
        "description": "Updated description",
        "price": "16000000.00",
        "category_id": 2,
        "user_id": 1,
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T14:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "category": {
            "id": 2,
            "name": "Gaming"
        },
        "images": [
            {
                "id": 3,
                "product_id": 1,
                "image_path": "http://localhost:8000/storage/product_images/laptop_updated.jpg",
                "user_id": 1,
                "created_at": "2024-01-15T14:00:00.000000Z",
                "updated_at": "2024-01-15T14:00:00.000000Z"
            }
        ]
    }
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Product not found"
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "price": [
            "The price must be a number."
        ]
    }
}
```

### 5. Delete Product

**Endpoint:** `DELETE /api/products/{id}`

**Deskripsi:** Soft delete produk (data tidak hilang permanen).

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID produk

**Response Success (200):**
```json
{
    "message": "Product deleted successfully"
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Product not found"
}
```

## 📝 Notes & Best Practices

### Soft Deletes
- Produk yang dihapus tidak akan muncul dalam daftar produk
- Data masih tersimpan di database dengan `deleted_at` timestamp
- Untuk restore data, perlu akses langsung ke database atau endpoint khusus

### User Tracking
- Setiap produk mencatat `user_id` dari user yang membuat/mengubahnya
- Informasi user disertakan dalam response untuk transparansi

### Image Management
- Gambar disimpan di `storage/app/public/product_images/`
- URL gambar: `http://localhost:8000/storage/product_images/{filename}`
- Format yang didukung: JPG, JPEG, PNG, GIF
- Maksimal ukuran file: 2MB per gambar
- Gambar lama akan dihapus saat update produk dengan gambar baru

### Pagination
- Endpoint list products tidak menggunakan pagination untuk simplicity
- Untuk production, pertimbangkan implementasi pagination

### Error Handling
- Semua error response menggunakan format JSON standar
- Validation errors memberikan detail field yang bermasalah
- HTTP status codes digunakan secara konsisten

## 🐛 Troubleshooting

### Upload Gambar Gagal
- Pastikan file dalam format yang didukung
- Pastikan ukuran file tidak melebihi 2MB
- Pastikan folder `storage/app/public/product_images/` memiliki permission write

### Product Not Found
- Pastikan ID produk benar
- Pastikan produk belum dihapus (soft delete)

### Category Not Found
- Pastikan ID kategori yang digunakan valid
- Pastikan kategori belum dihapus

## 📞 Dukungan

Untuk pertanyaan atau masalah, silakan buat issue di repository atau hubungi tim pengembang.

---

⬅️ [Kembali ke Halaman Utama](index.md)