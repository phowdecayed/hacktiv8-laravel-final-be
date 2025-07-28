# Authentication API

This section details the authentication endpoints.

## Register

- **POST** `/api/register`
- **Description:** Registers a new user and returns an access token.
- **Request Body:**
  ```json
  {
      "name": "Test User",
      "email": "test@example.com",
      "password": "password",
      "password_confirmation": "password"
  }
  ```
- **Response (Success):**
  ```json
  {
      "user": {
          "name": "Test User",
          "email": "test@example.com",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "created_at": "2023-01-01T00:00:00.000000Z",
          "id": 1
      },
      "token": "your_access_token"
  }
  ```
- **Response (Error - Validation):**
  ```json
  {
      "message": "The given data was invalid.",
      "errors": {
          "email": [
              "The email has already been taken."
          ]
      }
  }
  ```

## Login

- **POST** `/api/login`
- **Description:** Authenticates a user and returns an access token.
- **Request Body:**
  ```json
  {
      "email": "user@example.com",
      "password": "your_password"
  }
  ```
- **Response (Success):**
  ```json
  {
      "user": {
          "id": 1,
          "name": "Test User",
          "email": "user@example.com",
          "email_verified_at": null,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
      },
      "token": "your_access_token"
  }
  ```
- **Response (Error - Invalid Credentials):**
  ```json
  {
      "message": "Invalid credentials"
  }
  ```

## Get Authenticated User

- **GET** `/api/user`
- **Description:** Retrieves the details of the currently authenticated user.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Response:**
  ```json
  {
      "id": 1,
      "name": "Test User",
      "email": "user@example.com",
      "email_verified_at": null,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
  }
  ```

## Logout

- **POST** `/api/logout`
- **Description:** Logs out the authenticated user by revoking their access token.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Response:** `204 No Content`