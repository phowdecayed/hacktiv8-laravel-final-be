# Dokumentasi API Login

## Endpoint Login

### POST /api/login

Endpoint untuk login user dan mendapatkan token autentikasi.

#### Request
- **Method**: POST
- **URL**: `/api/login`
- **Content-Type**: `application/json`

#### Body Parameters
```json
{
    "email": "string (required)",
    "password": "string (required, min 6 karakter)"
}
```

#### Contoh Request
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

#### Response Success (200)
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "token": "1|laravel_sanctum_token_example",
        "token_type": "Bearer"
    }
}
```

#### Response Error (422)
```json
{
    "message": "Email atau password salah.",
    "errors": {
        "email": ["Email atau password salah."]
    }
}
```

#### Response Error Validasi (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

## Endpoint Logout

### POST /api/logout

Endpoint untuk logout user dan menghapus token autentikasi.

#### Request
- **Method**: POST
- **URL**: `/api/logout`
- **Headers**: 
  - `Authorization: Bearer {token}`

#### Contoh Request
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer 1|laravel_sanctum_token_example"
```

#### Response Success (200)
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

## User Testing

Untuk testing, gunakan user yang sudah dibuat oleh seeder:

### User Admin
- **Email**: `admin@example.com`
- **Password**: `password123`

### User Biasa
- **Email**: `user@example.com`
- **Password**: `password123`

## Setup Database

1. Jalankan migration:
```bash
php artisan migrate
```

2. Jalankan seeder untuk membuat user testing:
```bash
php artisan db:seed
```

3. Jalankan server:
```bash
php artisan serve
```

## Cara Penggunaan Token

Setelah login berhasil, simpan token yang diterima dan gunakan di header `Authorization` untuk mengakses endpoint yang memerlukan autentikasi:

```
Authorization: Bearer {your_token_here}
```