# Category API

This section details the endpoints for managing product categories.

## Authentication

All endpoints require a Bearer Token for authentication. The token can be obtained by logging in.

## Endpoints

### List Categories

- **GET** `/api/categories`
- **Description:** Retrieves a list of all product categories.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Response:**
  ```json
  [
      {
          "id": 1,
          "name": "Electronics",
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
      }
  ]
  ```

### Create Category

- **POST** `/api/categories`
- **Description:** Creates a new product category.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Request Body:** `application/json`
  ```json
  {
      "name": "New Category"
  }
  ```
- **Response (Success):**
  ```json
  {
      "id": 1,
      "name": "New Category",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
  }
  ```
- **Response (Error - Validation):**
  ```json
  {
      "name": [
          "The name field is required."
      ]
  }
  ```

### Get Category Details

- **GET** `/api/categories/{id}`
- **Description:** Retrieves the details of a specific product category.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `id`: The ID of the category.
- **Response (Success):**
  ```json
  {
      "id": 1,
      "name": "Electronics",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
  }
  ```
- **Response (Error - Not Found):**
  ```json
  {
      "message": "Category not found"
  }
  ```

### Update Category

- **PUT** `/api/categories/{id}`
- **Description:** Updates an existing product category.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `id`: The ID of the category to update.
- **Request Body:** `application/json`
  ```json
  {
      "name": "Updated Category Name"
  }
  ```
- **Response (Success):**
  ```json
  {
      "id": 1,
      "name": "Updated Category Name",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
  }
  ```
- **Response (Error - Validation or Not Found):**
  ```json
  {
      "message": "Category not found"
  }
  ```

### Delete Category

- **DELETE** `/api/categories/{id}`
- **Description:** Deletes a specific product category.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `id`: The ID of the category to delete.
- **Response (Success):**
  ```json
  {
      "message": "Category deleted successfully"
  }
  ```
- **Response (Error - Not Found):**
  ```json
  {
      "message": "Category not found"
  }
  ```