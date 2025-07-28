# Storage API

Dokumentasi lengkap untuk manajemen file dan penyimpanan dalam sistem.

⬅️ [Kembali ke Halaman Utama](index.md)

## 🎯 Fitur Utama
- Upload file dengan validasi tipe dan ukuran
- Download file dengan akses terproteksi
- Soft deletes untuk file management
- User tracking untuk audit trail
- Storage terorganisir berdasarkan user
- Support multiple file types

## 📋 Endpoints Overview

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/storage` | Mendapatkan daftar file user |
| POST | `/api/storage/upload` | Upload file baru |
| GET | `/api/storage/{filename}` | Download file |
| DELETE | `/api/storage/{filename}` | Hapus file (soft delete) |

## 🔐 Authentication

**Semua endpoints memerlukan Bearer Token untuk autentikasi.**

**Header yang diperlukan:**
```
Authorization: Bearer {your_access_token}
```

## 📊 Data Structure

### File Model
```
{
    id: integer,
    filename: string,
    original_name: string,
    mime_type: string,
    size: integer,
    user_id: integer,
    created_at: timestamp,
    updated_at: timestamp,
    deleted_at: timestamp|null,
    user: {
        id: integer,
        name: string,
        email: string
    }
}
```

## 📝 Supported File Types

### Image Files
- **Extensions:** .jpg, .jpeg, .png, .gif, .webp, .svg
- **Max Size:** 5MB per file
- **Usage:** Product images, profile pictures, documentation

### Document Files
- **Extensions:** .pdf, .doc, .docx, .xls, .xlsx, .ppt, .pptx
- **Max Size:** 10MB per file
- **Usage:** Product documentation, reports, certificates

### Archive Files
- **Extensions:** .zip, .rar, .7z
- **Max Size:** 20MB per file
- **Usage:** Product packages, multiple file uploads

### Text Files
- **Extensions:** .txt, .csv, .json, .xml
- **Max Size:** 2MB per file
- **Usage:** Configuration files, data exports

## 🔍 Endpoints Detail

### 1. List User Files

**Endpoint:** `GET /api/storage`

**Deskripsi:** Mendapatkan daftar semua file milik user yang sedang login, termasuk file yang belum dihapus.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**Query Parameters (optional):**
- `search`: Search berdasarkan nama file
- `type`: Filter berdasarkan tipe file (image, document, archive, text)
- `sort`: Sort berdasarkan kolom (filename, size, created_at)
- `order`: Urutan sort (asc, desc)

**Response Success (200):**
```json
{
    "data": [
        {
            "id": 1,
            "filename": "product_image_123.jpg",
            "original_name": "laptop_gaming.jpg",
            "mime_type": "image/jpeg",
            "size": 2048576,
            "user_id": 1,
            "created_at": "2024-01-15T08:00:00.000000Z",
            "updated_at": "2024-01-15T08:00:00.000000Z",
            "deleted_at": null,
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            }
        },
        {
            "id": 2,
            "filename": "report_2024.pdf",
            "original_name": "monthly_report.pdf",
            "mime_type": "application/pdf",
            "size": 5120000,
            "user_id": 1,
            "created_at": "2024-01-15T09:00:00.000000Z",
            "updated_at": "2024-01-15T09:00:00.000000Z",
            "deleted_at": null,
            "user": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            }
        }
    ]
}
```

### 2. Upload File

**Endpoint:** `POST /api/storage/upload`

**Deskripsi:** Upload file baru dengan validasi tipe dan ukuran file.

**Headers:**
```
Authorization: Bearer {your_access_token}
Content-Type: multipart/form-data
```

**Request Body:**
- `file` (required): file - File yang akan diupload
- `folder` (optional): string - Subfolder untuk organisasi file

**File Validation Rules:**
- **Max Size:** 20MB total
- **Allowed Types:**
  - Images: jpg, jpeg, png, gif, webp, svg (max 5MB)
  - Documents: pdf, doc, docx, xls, xlsx, ppt, pptx (max 10MB)
  - Archives: zip, rar, 7z (max 20MB)
  - Text: txt, csv, json, xml (max 2MB)

**Response Success (201):**
```json
{
    "message": "File uploaded successfully",
    "data": {
        "id": 3,
        "filename": "user_1_20240115120000_product_brochure.pdf",
        "original_name": "product_brochure.pdf",
        "mime_type": "application/pdf",
        "size": 3145728,
        "user_id": 1,
        "created_at": "2024-01-15T12:00:00.000000Z",
        "updated_at": "2024-01-15T12:00:00.000000Z",
        "deleted_at": null,
        "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        }
    }
}
```

**Response Error (422 - Validation):**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "file": [
            "The file field is required."
        ],
        "file": [
            "The file must be a file of type: jpg, jpeg, png, gif, webp, svg, pdf, doc, docx, xls, xlsx, ppt, pptx, zip, rar, 7z, txt, csv, json, xml."
        ],
        "file": [
            "The file may not be greater than 20480 kilobytes."
        ]
    }
}
```

### 3. Download File

**Endpoint:** `GET /api/storage/{filename}`

**Deskripsi:** Download file berdasarkan nama file. Hanya file milik user yang sedang login yang bisa didownload.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `filename` (required): string - Nama file yang akan didownload

**Response Success (200):**
- **Content-Type:** Sesuai dengan tipe file (image/jpeg, application/pdf, dll)
- **Content-Disposition:** attachment; filename="original_name"
- **Body:** Binary file content

**Response Error (404 - Not Found):**
```json
{
    "message": "File not found"
}
```

**Response Error (403 - Forbidden):**
```json
{
    "message": "You do not have permission to access this file"
}
```

### 4. Delete File

**Endpoint:** `DELETE /api/storage/{filename}`

**Deskripsi:** Soft delete file milik user. File tidak akan hilang permanen dari storage, hanya ditandai sebagai deleted.

**Headers:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
```

**URL Parameters:**
- `filename` (required): string - Nama file yang akan dihapus

**Response Success (200):**
```json
{
    "message": "File deleted successfully"
}
```

**Response Error (404 - Not Found):**
```json
{
    "message": "File not found"
}
```

**Response Error (403 - Forbidden):**
```json
{
    "message": "You do not have permission to delete this file"
}
```

## 🗂️ File Storage Structure

### Directory Organization
```
storage/
├── app/
│   └── public/
│       ├── user_1/
│       │   ├── images/
│       │   ├── documents/
│       │   └── archives/
│       ├── user_2/
│       │   ├── images/
│       │   ├── documents/
│       │   └── archives/
│       └── shared/
```

### File Naming Convention
- **Format:** `{user_id}_{timestamp}_{original_name}`
- **Example:** `1_20240115120000_product_image.jpg`
- **Purpose:** Prevent duplicate names, easy identification

## 📊 Usage Examples

### Upload Product Image
```bash
curl -X POST \
  http://localhost:8000/api/storage/upload \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -F 'file=@/path/to/product_image.jpg'
```

### Download Document
```bash
curl -X GET \
  http://localhost:8000/api/storage/report_2024.pdf \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  --output downloaded_report.pdf
```

### List All Files
```bash
curl -X GET \
  http://localhost:8000/api/storage \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

## 📝 Notes & Best Practices

### Soft Deletes
- File yang dihapus tidak akan muncul dalam daftar file
- Data file masih tersimpan di database dengan `deleted_at` timestamp
- File fisik tetap ada di storage untuk recovery
- Untuk restore file, perlu akses langsung ke database atau endpoint khusus

### User Tracking
- Setiap file mencatat `user_id` dari user yang menguploadnya
- File hanya bisa diakses oleh user yang menguploadnya
- Implementasi keamanan: user tidak bisa mengakses file milik user lain

### Storage Management
- **Quota:** Implementasi per-user storage quota (opsional)
- **Cleanup:** Scheduled task untuk menghapus file yang sudah lama dihapus
- **Backup:** Regular backup untuk file yang masih aktif

### Security
- **File Type Validation:** Strict validation untuk mencegah upload file berbahaya
- **Path Traversal:** Sanitasi nama file untuk mencegah path traversal attacks
- **Access Control:** File-level permission untuk keamanan yang lebih baik

### Performance
- **File Size Limit:** 20MB per file untuk menjaga performa server
- **Upload Progress:** Support untuk upload progress indicator
- **CDN Integration:** Untuk production, pertimbangkan CDN untuk file delivery

## 🐛 Troubleshooting

### Upload Failed
- **Error 413:** File terlalu besar, periksa ukuran file
- **Error 415:** Tipe file tidak didukung, periksa ekstensi file
- **Error 422:** Validasi gagal, periksa format request

### File Not Found
- Pastikan nama file benar (case-sensitive)
- Pastikan file belum dihapus (soft delete)
- Pastikan file milik user yang sedang login

### Permission Denied
- Pastikan token autentikasi valid
- Pastikan file milik user yang sedang login
- Check user ID dalam database record

### Storage Issues
- **Disk Space:** Monitor disk space secara berkala
- **Permissions:** Pastikan folder storage memiliki permission yang benar
- **Symlink:** Untuk production, pastikan storage symlink aktif

### Common Errors
```json
{
    "message": "The file failed to upload.",
    "errors": {
        "file": ["The file upload failed."]
    }
}
```

```json
{
    "message": "File type not allowed",
    "errors": {
        "file": ["The file must be a file of type: jpg, jpeg, png, gif, webp, svg, pdf, doc, docx, xls, xlsx, ppt, pptx, zip, rar, 7z, txt, csv, json, xml."]
    }
}
```

## 🔧 Configuration

### Environment Variables
```env
MAX_FILE_SIZE=20480
ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,txt,csv,json,xml
STORAGE_PATH=storage/app/public/
```

### Storage Link
Untuk production, pastikan storage link aktif:
```bash
php artisan storage:link
```

## 📈 Monitoring & Analytics

### Usage Statistics
- Total files per user
- Total storage usage per user
- File type distribution
- Upload frequency

### Health Checks
- Storage disk space
- File integrity verification
- Backup status
- Performance metrics

## 📞 Dukungan

Untuk pertanyaan atau masalah, silakan buat issue di repository atau hubungi tim pengembang.

---

⬅️ [Kembali ke Halaman Utama](index.md)