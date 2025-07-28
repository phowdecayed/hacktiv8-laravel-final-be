# Storage API

This section details the endpoints for managing files in storage.

## Authentication

All endpoints require a Bearer Token for authentication. The token can be obtained by logging in.

## Endpoints

### List Files

- **GET** `/api/storage`
- **Description:** Retrieves a list of all files in the storage, including those in subdirectories.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Response:**
  ```json
  [
      "file1.txt",
      "folder/file2.txt"
  ]
  ```

### Upload File

- **POST** `/api/storage`
- **Description:** Uploads a new file to the storage.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **Request Body:** `multipart/form-data` with a `file` field containing the file.
- **Response:**
  ```json
  {
      "path": "public/your-file-name.ext",
      "url": "/storage/public/your-file-name.ext"
  }
  ```

### Download File

- **GET** `/api/storage/{filename}`
- **Description:** Downloads a specific file from the storage.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `filename`: The name of the file to download (e.g., `public/your-file-name.ext`).
- **Response:** The file content.

### Delete File

- **DELETE** `/api/storage/{filename}`
- **Description:** Deletes a specific file from the storage.
- **Headers:** `Authorization: Bearer <your_access_token>`
- **URL Parameters:**
  - `filename`: The name of the file to delete (e.g., `public/your-file-name.ext`).
- **Response:** `204 No Content`
