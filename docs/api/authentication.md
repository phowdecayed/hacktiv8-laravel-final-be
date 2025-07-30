# Authentication API

Dokumentasi ini menjelaskan endpoint otentikasi untuk manajemen pengguna menggunakan Laravel Sanctum.

⬅️ [Kembali ke Halaman Utama](index.md)

## 🔐 Fitur Utama
- Registrasi pengguna baru
- Login dengan email dan password
- Mendapatkan informasi user yang sedang login
- Logout dengan revoke token

## 📝 Endpoints Overview

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/api/register` | Registrasi user baru |
| POST | `/api/login` | Login user |
| GET | `/api/user` | Get user yang sedang login |
| POST | `/api/logout` | Logout user |

## 🚀 Getting Started

### 1. Register

**Endpoint:** `POST /api/register`

**Deskripsi:** Membuat akun pengguna baru dan mengembalikan access token.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Validasi:**
- `name`: required, string, max:255
- `email`: required, email, unique:users
- `password`: required, string, min:8, confirmed

**Response Success (201):**
```json
{
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "email_verified_at": null,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    "token": "1|laravel_sanctum_token_here"
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email has already been taken."
        ],
        "password": [
            "The password confirmation does not match."
        ]
    }
}
```

### 2. Login

**Endpoint:** `POST /api/login`

**Deskripsi:** Autentikasi user dan mendapatkan access token.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "your_password"
}
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Test User",
            "email": "user@example.com"
        },
        "token": "2|laravel_sanctum_token_here",
        "token_type": "Bearer"
    }
}
```

**Response Error (401 - Invalid Credentials):**
```json
{
    "message": "Email atau password salah."
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password field is required."
        ]
    }
}
```

### 3. Get Authenticated User

**Endpoint:** `GET /api/user`

**Deskripsi:** Mendapatkan detail user yang sedang login.

**Headers:**
```
Authorization: Bearer {your_token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "Data user berhasil diambil",
    "data": {
        "user": {
            "id": 1,
            "name": "Test User",
            "email": "user@example.com",
            "role": "user",
            "email_verified_at": null,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z",
            "permissions": [
                "create_transactions",
                "view_own_transactions"
            ],
            "statistics": {
                "total_products": 0,
                "total_categories": 0,
                "total_transactions": 0
            },
            "role_description": "Standard user role",
            "api_token_count": 1,
            "last_login": "2023-01-01T00:00:00.000000Z"
        }
    }
}
```

**Response Error (401 - Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

### 4. Logout

**Endpoint:** `POST /api/logout`

**Deskripsi:** Logout user dengan mencabut access token.

**Headers:**
```
Authorization: Bearer {your_token}
Accept: application/json
```

**Response Success (200):**
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```
**Response Error (401 - Unauthorized):**
```json
{
    "message": "Unauthenticated."
}
```

## 🔒 Keamanan

### Token Management
- Token tidak memiliki masa berlaku (tidak expired)
- Token dapat dicabut saat logout
- Gunakan HTTPS untuk produksi
- Simpan token dengan aman di client

### Rate Limiting
- Tidak ada rate limiting khusus untuk endpoint auth
- Implementasikan rate limiting di production untuk mencegah brute force

## 📝 Best Practices

1. **Validasi Input**: Selalu validasi input di client dan server
2. **Password Security**: Gunakan password yang kuat (min 8 karakter)
3. **Token Storage**: Simpan token di secure storage (localStorage/sessionStorage)
4. **HTTPS**: Selalu gunakan HTTPS untuk production
5. **Token Refresh**: Implementasikan refresh token mechanism untuk aplikasi mobile

## 🐛 Troubleshooting

### Email sudah terdaftar
Pastikan email yang digunakan belum terdaftar di sistem.

### Password tidak match
Pastikan password dan password_confirmation sama persis.

### Token tidak valid
Pastikan token masih aktif dan tidak dicabut.

### Unauthorized errors
Pastikan token disertakan dalam header dengan format yang benar: `Authorization: Bearer {token}`

---

⬅️ [Kembali ke Halaman Utama](index.md)

### Token Management
- Token tidak memiliki masa berlaku (tidak expired)
- Token dapat dicabut saat logout
- Gunakan HTTPS untuk produksi
- Simpan token dengan aman di client

### Rate Limiting
- Tidak ada rate limiting khusus untuk endpoint auth
- Implementasikan rate limiting di production untuk mencegah brute force

## 📝 Best Practices

1. **Validasi Input**: Selalu validasi input di client dan server
2. **Password Security**: Gunakan password yang kuat (min 8 karakter)
3. **Token Storage**: Simpan token di secure storage (localStorage/sessionStorage)
4. **HTTPS**: Selalu gunakan HTTPS untuk production
5. **Token Refresh**: Implementasikan refresh token mechanism untuk aplikasi mobile

## 🐛 Troubleshooting

### Email sudah terdaftar
Pastikan email yang digunakan belum terdaftar di sistem.

### Password tidak match
Pastikan password dan password_confirmation sama persis.

### Token tidak valid
Pastikan token masih aktif dan tidak dicabut.

### Unauthorized errors
Pastikan token disertakan dalam header dengan format yang benar: `Authorization: Bearer {token}`