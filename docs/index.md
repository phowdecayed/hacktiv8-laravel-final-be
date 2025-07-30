# API Documentation

Selamat datang di dokumentasi API Hacktiv8 Laravel Final Project. Dokumentasi ini berisi panduan lengkap untuk menggunakan semua endpoint yang tersedia.

## 🧭 Navigasi Cepat

Gunakan tabel di bawah ini untuk navigasi cepat ke dokumentasi yang Anda butuhkan:

## 📋 Daftar Isi

| API | Deskripsi | Dokumentasi |
|-----|-----------|-------------|
| 🔐 **Authentication** | Otentikasi pengguna (Register, Login, Logout) | [Lihat Dokumentasi](api/authentication.md) |
| 💾 **Storage** | Manajemen file storage | [Lihat Dokumentasi](api/storage.md) |
| 🛍️ **Product** | Manajemen produk dan gambar produk | [Lihat Dokumentasi](api/products.md) |
| 🏷️ **Category** | Manajemen kategori produk | [Lihat Dokumentasi](api/category.md) |
| 🛒 **Shopping Cart** | Manajemen keranjang belanja | [Lihat Dokumentasi](api/shopping-cart.md) |
| 💳 **Transaction** | Manajemen transaksi pembelian | [Lihat Dokumentasi](api/transactions.md) |
| 📊 **Audit Trail** | Pelacakan aktivitas CRUD & audit log | [Lihat Dokumentasi](api/audit-trail.md) |
| 🔐 **Roles & Permissions** | Sistem role-based access control | [Lihat Dokumentasi](api/roles-and-permissions.md) |

## 📦 Postman Collections

| Collection | Description | Download |
|------------|-------------|----------|
| 🔐 **Authentication** | Postman collection for Authentication API | [Download](authentication_collection.json) |
| 💾 **Storage** | Postman collection for Storage API | [Download](storage_collection.json) |
| 🛍️ **Product** | Postman collection for Product API | [Download](product_collection.json) |
| 🏷️ **Category** | Postman collection for Category API | [Download](category_collection.json) |
| 🛒 **Shopping Cart** | Postman collection for Shopping Cart API | [Download](shopping_cart_collection.json) |
| 💳 **Transaction** | Postman collection for Transaction API | [Download](transactions_collection.json) |
| 📊 **Audit Trail** | Postman collection for Audit Trail API | [Download](audit_trail_collection.json) |
| 👥 **Users** | Postman collection for User Management API | [Download](users_collection.json) |

## 🚀 Fitur Utama

### Soft Deletes
Semua model dalam API ini menggunakan fitur **soft deletes**, yang berarti:
- Data yang dihapus tidak akan hilang permanen dari database
- Data yang dihapus akan memiliki nilai `deleted_at` yang terisi
- Data yang dihapus tidak akan muncul dalam query normal
- Data dapat dipulihkan jika diperlukan

### User Tracking
Setiap entitas yang dibuat atau diperbarui akan mencatat informasi user yang melakukan aksi:
- `user_id`: ID dari user yang membuat/mengubah data
- Informasi user akan disertakan dalam response untuk transparansi

### Eager Loading
API menggunakan eager loading untuk optimasi performa query dan menyertakan relasi yang relevan dalam response.

## 🛠️ Teknologi yang Digunakan

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Database**: SQLite
- **File Storage**: Laravel Storage (public disk)
- **Validation**: Laravel Validation
- **Testing**: PHPUnit & Pest

## 📖 Cara Menggunakan

1. **Registrasi**: Gunakan endpoint `/api/register` untuk membuat akun baru
2. **Login**: Gunakan endpoint `/api/login` untuk mendapatkan access token
3. **Gunakan Token**: Sertakan token dalam header setiap request: `Authorization: Bearer {token}`
4. **Akses Endpoint**: Gunakan endpoint sesuai kebutuhan Anda

## 📋 Status Response

| Status Code | Deskripsi |
|-------------|-----------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## 🔗 Base URL

```
http://localhost:8000
```

## 📞 Dukungan

Untuk pertanyaan atau masalah, silakan buat issue di repository atau hubungi tim pengembang.