# 🔄 Aplikasi Flow - E-Commerce Laravel API

Dokumentasi ini menjelaskan alur kerja keseluruhan aplikasi e-commerce Laravel dari awal hingga akhir.

## 📋 Daftar Isi
- [Arsitektur Umum](#arsitektur-umum)
- [Flow Autentikasi](#flow-autentikasi)
- [Flow Produk & Kategori](#flow-produk--kategori)
- [Flow Shopping Cart](#flow-shopping-cart)
- [Flow Transaksi](#flow-transaksi)
- [Flow Audit Trail](#flow-audit-trail)
- [Role-Based Access Control](#role-based-access-control)

## 🏗️ Arsitektur Umum

### Technology Stack
- **Backend**: Laravel 12 (API Only)
- **Authentication**: Laravel Sanctum (Token-based)
- **Database**: SQLite
- **Storage**: Local filesystem with public access
- **Testing**: Pest PHP
- **API Documentation**: Postman Collection

### Database Structure
```
users
├── products (user_id FK)
├── categories (user_id FK)
├── transactions (user_id FK)
├── shopping_cart (user_id FK)
└── audit_trails (user_id FK)

products
├── product_images (product_id FK)
├── categories (category_id FK)
├── transaction_items (product_id FK)
└── shopping_cart (product_id FK)

transactions
└── transaction_items (transaction_id FK)
```

## 🔐 Flow Autentikasi

### 1. Register User
```
POST /api/register
→ Validasi input (name, email, password)
→ Hash password
→ Assign role: customer (default)
→ Generate API token
→ Return user data + token
```

### 2. Login User
```
POST /api/login
→ Validasi credentials
→ Check user status
→ Generate new API token
→ Return user data + token
```

### 3. Logout User
```
POST /api/logout (Auth Required)
→ Revoke current token
→ Return success message
```

## 🛍️ Flow Produk & Kategori

### Kategori Management

#### Admin/Editor Flow:
1. **Create Category**
   ```
   POST /api/categories (Admin/Editor)
   → Validasi input (name, description)
   → Set user_id dari auth user
   → Create category
   → Log audit trail
   ```

2. **Update Category**
   ```
   PUT /api/categories/{id} (Admin/Editor)
   → Check ownership (Admin bisa edit semua)
   → Update data
   → Log audit trail
   ```

3. **Delete Category**
   ```
   DELETE /api/categories/{id} (Admin/Editor)
   → Soft delete (set deleted_at)
   → Log audit trail
   ```

#### Public Access:
- **List Categories**: GET /api/categories
- **Detail Category**: GET /api/categories/{id}

### Produk Management

#### Admin/Editor Flow:
1. **Create Product**
   ```
   POST /api/products (Admin/Editor)
   → Validasi input (name, price, description, stock, category_id)
   → Upload images ke storage
   → Create product record
   → Create product_images records
   → Log audit trail
   ```

2. **Update Product**
   ```
   PUT /api/products/{id} (Admin/Editor)
   → Check ownership
   → Update product data
   → Handle image updates
   → Log audit trail
   ```

3. **Delete Product**
   ```
   DELETE /api/products/{id} (Admin/Editor)
   → Soft delete product
   → Soft delete related images
   → Log audit trail
   ```

#### Public Access:
- **List Products**: GET /api/products (with pagination, filtering)
- **Detail Product**: GET /api/products/{id}

## 🛒 Flow Shopping Cart

### Customer Flow:
1. **Add to Cart**
   ```
   POST /api/cart (Auth Required)
   → Validasi product_id & quantity
   → Check stock availability
   → Check existing cart item
   → Create/update cart item
   → Calculate total
   ```

2. **View Cart**
   ```
   GET /api/cart (Auth Required)
   → Get user's cart items
   → Include product details
   → Calculate subtotal
   ```

3. **Update Cart**
   ```
   PUT /api/cart/{cart} (Auth Required)
   → Update quantity
   → Re-validate stock
   → Update total
   ```

4. **Checkout from Cart**
   ```
   POST /api/cart/checkout (Auth Required)
   → Validate all items in cart
   → Check stock for all products
   → Calculate total amount
   → Create transaction
   → Create transaction_items
   → Clear cart
   → Update product stock
   → Log audit trail
   ```

## 💳 Flow Transaksi

### Struktur Baru: Multi-Item Transaction

#### Customer Flow:
1. **Create Transaction**
   ```
   POST /api/transactions (Auth Required)
   → Validasi items array (min 1 item)
   → Validasi stock untuk semua items
   → Hitung total_amount otomatis
   → Create transaction record
   → Create transaction_items records
   → Log audit trail
   → Return transaction detail dengan items
   ```

2. **View My Transactions**
   ```
   GET /api/my-transactions (Auth Required)
   → Filter by user_id
   → Include transaction_items dengan product details
   → Support filtering & sorting
   ```

3. **Update Transaction Status**
   ```
   PUT /api/transactions/{id} (Admin/Moderator)
   → Validasi status baru dan/atau notes
   → Handle stock changes (cancelled = restore stock)
   → Log audit trail
   ```

#### Admin/Moderator Flow:
1. **List All Transactions**
   ```
   GET /api/transactions (Admin/Moderator)
   → View all transactions
   → Filter by status, user, date range
   → Include user & items details
   ```

2. **View Transaction Detail**
   ```
   GET /api/transactions/{id} (Admin/Moderator)
   → Detail transaksi dengan semua items
   → Include user information
   ```

### Status Transaksi Flow:
```
pending → processing → shipped → delivered
   ↓
cancelled (restore stock)
   ↓
refunded (restore stock)
```

## 📊 Flow Audit Trail

### System-Wide Logging:
1. **Create Operation**
   ```
   Setiap create: products, categories, transactions
   → Log: action_type='create', old_values=null, new_values=full_data
   ```

2. **Update Operation**
   ```
   Setiap update: products, categories, transactions
   → Log: action_type='update', old_values=before, new_values=after
   ```

3. **Delete Operation**
   ```
   Setiap soft delete
   → Log: action_type='delete', old_values=full_data, new_values=null
   ```

### Access Patterns:
- **Admin/Moderator**: GET /api/audit-trails (view all)
- **User**: GET /api/my-audit-trails (view own activities)
- **Model Specific**: GET /api/audit-trails/model/{type}/{id}

## 👥 Role-Based Access Control

### Role Hierarchy:
1. **Admin** (Full Access)
   - Manage users
   - Manage all products & categories
   - Manage all transactions
   - View all audit trails

2. **Moderator**
   - Manage transactions (view all, update status)
   - View audit trails
   - View products & categories

3. **Editor**
   - Manage products & categories (own + others)
   - Upload/manage storage files
   - View products & categories

4. **Customer** (Default)
   - Manage own cart
   - Create/view own transactions
   - View products & categories
   - View own audit trail

### Middleware Flow:
```
Request → auth:sanctum → role check → permission check → execute
```

## 🔄 Complete User Journey

### Customer Journey:
1. **Register/Login** → Get API Token
2. **Browse Products** → View list & detail
3. **Add to Cart** → Manage shopping cart
4. **Checkout** → Create transaction
5. **Track Order** → View transaction status
6. **Receive Goods** → Status updated to delivered

### Admin Journey:
1. **Login as Admin** → Get admin token
2. **Manage Categories** → CRUD categories
3. **Manage Products** → CRUD products with images
4. **Manage Transactions** → View & update status
5. **Monitor Activity** → View audit trails
6. **Manage Users** → CRUD users

## 📁 File Storage Flow

### Image Upload:
1. **Upload via API** → POST /api/storage (Admin/Editor)
2. **Validation** → Check file type & size
3. **Storage** → Save to public/products/ directory
4. **Database** → Record in product_images table
5. **Access** → Public URL via /api/storage/{filename}

### Cleanup:
- Soft delete images (set deleted_at)
- Physical file deletion via scheduled task
- Audit trail logging

## 🔍 Error Handling Flow

### Validation Errors:
- Return 422 with detailed error messages
- Include field-specific validation rules

### Authentication Errors:
- 401 for invalid/missing token
- 403 for insufficient permissions

### Business Logic Errors:
- 400 for stock issues
- 404 for not found resources
- 409 for conflicts (duplicate email, etc)

## 📋 Testing Flow

### Manual Testing Sequence:
1. **Register new user** → Get customer token
2. **Create categories** → As admin
3. **Create products** → As admin with images
4. **Add to cart** → As customer
5. **Checkout** → Create transaction
6. **Update status** → As admin/moderator
7. **View audit trail** → Verify logging

### Automated Testing:
- **Feature Tests**: Role-based access
- **Unit Tests**: Business logic validation
- **API Tests**: Endpoint functionality
