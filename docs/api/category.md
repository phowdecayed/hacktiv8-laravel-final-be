# Category API

Dokumentasi lengkap untuk manajemen kategori produk dalam sistem.

⬅️ [Kembali ke Halaman Utama](index.md)

## 🎯 Fitur Utama
- CRUD lengkap untuk kategori
- Soft deletes untuk data recovery
- User tracking untuk audit trail
- Relasi dengan produk
- Validasi unik untuk nama kategori

## 📋 Endpoints Overview

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/categories` | Mendapatkan semua kategori |
| POST | `/api/categories` | Membuat kategori baru |
| GET | `/api/categories/{id}` | Mendapatkan detail kategori |
| PUT | `/api/categories/{id}` | Update kategori |
| DELETE | `/api/categories/{id}` | Hapus kategori (soft delete) |

## 🔐 Authentication

**Semua endpoints memerlukan Bearer Token untuk autentikasi.**

**Header yang diperlukan:**
```
Authorization: Bearer {your_access_token}
```

## 📊 Data Structure

### Category Model
```
{
    id: integer,
    name: string,
    description: string|null,
    user_id: integer,
    created_at: timestamp,
    updated_at: timestamp,
    deleted_at: timestamp|null,
    user: {
        id: integer,
        name: string,
        email: string
    },
    products: [
        {
            id: integer,
            name: string,
            description: string,
            price: decimal(10,2),
            category_id: integer,
            user_id: integer,
            created_at: timestamp,
            updated_at: timestamp
        }
    ]
}
```

## 🔍 Endpoints Detail

### 1. List All Categories

**Endpoint:** `GET /api/categories`

**Deskripsi:** Mendapatkan daftar semua kategori yang aktif (tidak terhapus), termasuk relasi dengan produk dan user yang membuatnya.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Query Parameters (optional):**
- `search`: Search berdasarkan nama kategori
- `with_products`: Include produk terkait (boolean: true/false)
- `sort`: Sort berdasarkan kolom (name, created_at)
- `order`: Urutan sort (asc, desc)

**Response Success (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "description": "Semua produk elektronik termasuk laptop, smartphone, dll",
            "user_id": 1,
            "created_at": "2024-01-15T08:00:00.000000Z",
            "updated_at": "2024-01-15T08:00:00.000000Z",
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "products": [
                {
                    "id": 1,
                    "name": "Laptop Gaming ASUS ROG",
                    "description": "Laptop gaming high-end dengan RTX 4060",
                    "price": "15000000.00",
                    "category_id": 1,
                    "user_id": 1,
                    "created_at": "2024-01-15T08:00:00.000000Z",
                    "updated_at": "2024-01-15T10:30:00.000000Z"
                },
                {
                    "id": 2,
                    "name": "iPhone 15 Pro",
                    "description": "Smartphone flagship dari Apple",
                    "price": "20000000.00",
                    "category_id": 1,
                    "user_id": 2,
                    "created_at": "2024-01-15T09:00:00.000000Z",
                    "updated_at": "2024-01-15T09:00:00.000000Z"
                }
            ]
        },
        {
            "id": 2,
            "name": "Fashion",
            "description": "Pakaian dan aksesoris fashion",
            "user_id": 2,
            "created_at": "2024-01-15T09:30:00.000000Z",
            "updated_at": "2024-01-15T09:30:00.000000Z",
            "user": {
                "id": 2,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "products": []
        }
    ]
}
```

### 2. Create New Category

**Endpoint:** `POST /api/categories`

**Deskripsi:** Membuat kategori baru dengan validasi nama unik.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Home & Living",
    "description": "Furniture dan dekorasi rumah"
}
```

**Field Validation:**
- `name` (required): string, max:255, unique:categories - Nama kategori harus unik
- `description` (optional): string, max:1000 - Deskripsi kategori

**Response Success (201):**
```json
{
    "message": "Category created successfully",
    "data": {
        "id": 3,
        "name": "Home & Living",
        "description": "Furniture dan dekorasi rumah",
        "user_id": 1,
        "created_at": "2024-01-15T10:00:00.000000Z",
        "updated_at": "2024-01-15T10:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "products": []
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
        "name": [
            "The name has already been taken."
        ]
    }
}
```

### 3. Get Category Details

**Endpoint:** `GET /api/categories/{id}`

**Deskripsi:** Mendapatkan detail kategori berdasarkan ID, termasuk relasi dengan produk dan user yang membuatnya.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID kategori

**Query Parameters (optional):**
- `with_products`: Include produk terkait (boolean: true/false)

**Response Success (200):**
```json
{
    "data": {
        "id": 1,
        "name": "Electronics",
        "description": "Semua produk elektronik termasuk laptop, smartphone, dll",
        "user_id": 1,
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T08:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "products": [
            {
                "id": 1,
                "name": "Laptop Gaming ASUS ROG",
                "description": "Laptop gaming high-end dengan RTX 4060",
                "price": "15000000.00",
                "category_id": 1,
                "user_id": 1,
                "created_at": "2024-01-15T08:00:00.000000Z",
                "updated_at": "2024-01-15T10:30:00.000000Z"
            }
        ]
    }
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Category not found"
}
```

### 4. Update Category

**Endpoint:** `PUT /api/categories/{id}`

**Deskripsi:** Update kategori existing dengan validasi nama unik.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

**URL Parameters:**
- `id` (required): integer - ID kategori

**Request Body:**
```json
{
    "name": "Electronics & Gadgets",
    "description": "Updated description for electronics category"
}
```

**Field Validation:**
- `name` (optional): string, max:255, unique:categories - Nama kategori harus unik
- `description` (optional): string, max:1000 - Deskripsi kategori

**Response Success (200):**
```json
{
    "message": "Category updated successfully",
    "data": {
        "id": 1,
        "name": "Electronics & Gadgets",
        "description": "Updated description for electronics category",
        "user_id": 1,
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T11:00:00.000000Z",
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "products": [
            {
                "id": 1,
                "name": "Laptop Gaming ASUS ROG",
                "description": "Laptop gaming high-end dengan RTX 4060",
                "price": "15000000.00",
                "category_id": 1,
                "user_id": 1,
                "created_at": "2024-01-15T08:00:00.000000Z",
                "updated_at": "2024-01-15T10:30:00.000000Z"
            }
        ]
    }
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Category not found"
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name has already been taken."
        ]
    }
}
```

### 5. Delete Category

**Endpoint:** `DELETE /api/categories/{id}`

**Deskripsi:** Soft delete kategori (data tidak hilang permanen). Kategori yang memiliki produk masih bisa dihapus, namun produk akan tetap terkait dengan ID kategori yang dihapus.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `id` (required): integer - ID kategori

**Response Success (200):**
```json
{
    "message": "Category deleted successfully"
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "Category not found"
}
```

**Response Error (409 - Conflict):**
```json
{
    "message": "Cannot delete category. This category has associated products. Please reassign products first."
}
```

## 📝 Notes & Best Practices

### Soft Deletes
- Kategori yang dihapus tidak akan muncul dalam daftar kategori
- Data masih tersimpan di database dengan `deleted_at` timestamp
- Untuk restore data, perlu akses langsung ke database atau endpoint khusus
- Kategori yang dihapus tidak akan muncul dalam dropdown/filter produk

### User Tracking
- Setiap kategori mencatat `user_id` dari user yang membuat/mengubahnya
- Informasi user disertakan dalam response untuk transparansi

### Unique Names
- Nama kategori harus unik untuk mencegah duplikasi
- Validasi dilakukan case-insensitive ("Electronics" = "electronics")

### Product Relationships
- Kategori bisa memiliki banyak produk (one-to-many relationship)
- Saat kategori dihapus, produk tetap terkait dengan ID kategori tersebut
- Untuk produksi, pertimbangkan untuk mengatur produk ke kategori default saat kategori dihapus

### Error Handling
- Semua error response menggunakan format JSON standar
- Validation errors memberikan detail field yang bermasalah
- HTTP status codes digunakan secara konsisten

## 🐛 Troubleshooting

### Category Name Already Exists
- Gunakan nama yang lebih spesifik atau tambahkan identifier
- Contoh: "Electronics - Gaming" vs "Electronics - Office"

### Category Not Found
- Pastikan ID kategori benar
- Pastikan kategori belum dihapus (soft delete)

### Cannot Delete Category with Products
- Reassign produk ke kategori lain terlebih dahulu
- Update field `category_id` pada produk yang terkait
- Atau implementasi soft delete yang lebih advance untuk handling ini

### Database Constraints
- Nama kategori unik: `UNIQUE(name)`
- Foreign key ke users: `user_id`
- Soft delete field: `deleted_at`

## 📞 Dukungan

Untuk pertanyaan atau masalah, silakan buat issue di repository atau hubungi tim pengembang.

---

⬅️ [Kembali ke Halaman Utama](index.md)