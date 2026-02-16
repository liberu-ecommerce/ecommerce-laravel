# Collection Management API

This document describes the Collection Management API endpoints for managing product collections in the ecommerce application.

## Authentication

All endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

## Endpoints

### List Collections

Retrieve all collections or a paginated list.

**Endpoint:** `GET /api/collections`

**Query Parameters:**
- `per_page` (optional): Number of items per page (max 100)
- `filter[name]` (optional): Filter by collection name
- `sort` (optional): Sort by field (name, created_at, updated_at)

**Response:**
```json
// Without pagination
[
  {
    "id": 1,
    "name": "Summer Collection",
    "slug": "summer-collection",
    "description": "Summer products",
    "price": 99.99,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "products": [...]
  }
]

// With pagination (?per_page=10)
{
  "data": [...],
  "links": {...},
  "meta": {...}
}
```

### Create Collection

Create a new collection.

**Endpoint:** `POST /api/collections`

**Request Body:**
```json
{
  "name": "Winter Collection",
  "slug": "winter-collection",  // Optional, auto-generated from name if not provided
  "description": "Winter products",  // Optional
  "price": 149.99  // Optional
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Collection created successfully",
  "data": {
    "id": 2,
    "name": "Winter Collection",
    "slug": "winter-collection",
    "description": "Winter products",
    "price": 149.99,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Show Collection

Retrieve a specific collection by ID or slug, including its products.

**Endpoint:** `GET /api/collections/{idOrSlug}`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Summer Collection",
    "slug": "summer-collection",
    "description": "Summer products",
    "price": 99.99,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "products": [
      {
        "id": 1,
        "name": "Product 1",
        "price": 29.99,
        "pivot": {
          "quantity": 1
        }
      }
    ]
  }
}
```

**Response (404):**
```json
{
  "success": false,
  "message": "Collection not found"
}
```

### Update Collection

Update an existing collection.

**Endpoint:** `PUT /api/collections/{id}`

**Request Body:**
```json
{
  "name": "Updated Collection Name",  // Optional
  "slug": "updated-slug",  // Optional, auto-generated if name changes
  "description": "Updated description",  // Optional
  "price": 199.99  // Optional
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Collection updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Collection Name",
    "slug": "updated-slug",
    "description": "Updated description",
    "price": 199.99,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-02T00:00:00.000000Z"
  }
}
```

### Add Products to Collection

Add one or more products to a collection.

**Endpoint:** `POST /api/collections/{id}/products`

**Request Body:**
```json
{
  "product_ids": [1, 2, 3],
  "quantities": [5, 10, 3]  // Optional, defaults to 1 for each product
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Products added to collection successfully",
  "data": {
    "id": 1,
    "name": "Summer Collection",
    "products": [...]
  }
}
```

### Remove Products from Collection

Remove one or more products from a collection.

**Endpoint:** `DELETE /api/collections/{id}/products`

**Request Body:**
```json
{
  "product_ids": [1, 2]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Products removed from collection successfully",
  "data": {
    "id": 1,
    "name": "Summer Collection",
    "products": [...]
  }
}
```

### Delete Collection

Soft delete a collection (can be restored later).

**Endpoint:** `DELETE /api/collections/{id}`

**Response (200):**
```json
{
  "success": true,
  "message": "Collection deleted successfully"
}
```

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "name": ["The name field is required."]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Collection not found"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

## Examples

### cURL Examples

**List all collections:**
```bash
curl -X GET "https://your-domain.com/api/collections" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Create a collection:**
```bash
curl -X POST "https://your-domain.com/api/collections" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Spring Collection",
    "description": "New spring products",
    "price": 79.99
  }'
```

**Add products to collection:**
```bash
curl -X POST "https://your-domain.com/api/collections/1/products" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "product_ids": [1, 2, 3],
    "quantities": [5, 10, 3]
  }'
```

## Notes

- All collections support soft deletes - deleted collections are not permanently removed
- Slugs are automatically generated from collection names if not provided
- Product quantities in collections default to 1 if not specified
- The `syncWithoutDetaching` method is used when adding products, so existing products won't be removed
