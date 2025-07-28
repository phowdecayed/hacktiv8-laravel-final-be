# Product API

This section details the endpoints for managing products.

## Authentication

All endpoints require a Bearer Token for authentication. The token can be obtained by logging in.

## Endpoints

### List Products

- **GET** `/api/products`
- **Description:** Retrieves a list of all products, including their associated images.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Response:**
  ```json
  [
      {
          "id": 1,
          "name": "Product 1",
          "description": "Description for product 1",
          "price": "19.99",
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "images": [
              {
                  "id": 1,
                  "product_id": 1,
                  "image_path": "http://localhost:8000/storage/product_images/image1.jpg",
                  "created_at": "2023-01-01T00:00:00.000000Z",
                  "updated_at": "2023-01-01T00:00:00.000000Z"
              }
          ]
      }
  ]
  ```

### Create Product

- **POST** `/api/products`
- **Description:** Creates a new product with optional image uploads.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Request Body:** `multipart/form-data`
  - `name`: (string, required) The name of the product.
  - `description`: (string, optional) The description of the product.
  - `price`: (numeric, required) The price of the product.
  - `images[]`: (file, optional) An array of image files (JPG, PNG, GIF, max 2MB per image).
- **Response (Success):**
  ```json
  {
      "id": 1,
      "name": "New Product",
      "description": "Description for new product",
      "price": "29.99",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "images": [
          {
              "id": 1,
              "product_id": 1,
              "image_path": "http://localhost:8000/storage/product_images/new_image.jpg",
              "created_at": "2023-01-01T00:00:00.000000Z",
              "updated_at": "2023-01-01T00:00:00.000000Z"
          }
      ]
  }
  ```
- **Response (Error - Validation):**
  ```json
  {
      "name": [
          "The name field is required."
      ],
      "price": [
          "The price field is required."
      ]
  }
  ```

### Get Product Details

- **GET** `/api/products/{id}`
- **Description:** Retrieves the details of a specific product, including its associated images.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `id`: The ID of the product.
- **Response (Success):**
  ```json
  {
      "id": 1,
      "name": "Product 1",
      "description": "Description for product 1",
      "price": "19.99",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "images": [
          {
              "id": 1,
              "product_id": 1,
              "image_path": "http://localhost:8000/storage/product_images/image1.jpg",
              "created_at": "2023-01-01T00:00:00.000000Z",
              "updated_at": "2023-01-01T00:00:00.000000Z"
          }
      ]
  }
  ```
- **Response (Error - Not Found):**
  ```json
  {
      "message": "Product not found"
  }
  ```

### Update Product

- **POST** `/api/products/{id}` (Use `_method: PUT` or `_method: PATCH` for form-data)
- **Description:** Updates an existing product's details and optionally replaces its images.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `id`: The ID of the product to update.
- **Request Body:** `multipart/form-data`
  - `_method`: (string, required) Must be `PUT` or `PATCH` to simulate a PUT/PATCH request with form-data.
  - `name`: (string, required) The updated name of the product.
  - `description`: (string, optional) The updated description of the product.
  - `price`: (numeric, required) The updated price of the product.
  - `images[]`: (file, optional) An array of new image files. If provided, existing images will be deleted and replaced.
- **Response (Success):**
  ```json
  {
      "id": 1,
      "name": "Updated Product Name",
      "description": "Updated description",
      "price": "39.99",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "images": [
          {
              "id": 2,
              "product_id": 1,
              "image_path": "http://localhost:8000/storage/product_images/updated_image.jpg",
              "created_at": "2023-01-01T00:00:00.000000Z",
              "updated_at": "2023-01-01T00:00:00.000000Z"
          }
      ]
  }
  ```
- **Response (Error - Validation or Not Found):**
  ```json
  {
      "message": "Product not found"
  }
  ```

### Delete Product

- **DELETE** `/api/products/{id}`
- **Description:** Deletes a specific product and all its associated images from storage.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `id`: The ID of the product to delete.
- **Response (Success):**
  ```json
  {
      "message": "Product deleted successfully"
  }
  ```
- **Response (Error - Not Found):**
  ```json
  {
      "message": "Product not found"
  }
  ```