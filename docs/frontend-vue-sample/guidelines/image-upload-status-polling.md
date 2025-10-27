---
layout: default
title: Image Upload Status Polling
nav_order: 6
parent: Frontend Guidelines
grand_parent: Vue.js Sample Frontend
---

# Image Upload Status Polling API

This document describes how to use the status polling feature for image uploads.

## Overview

The image upload status polling feature allows clients to track the processing status of uploaded images. When an image is uploaded, it goes through processing (validation, resizing, optimization) before becoming available as an `AvailableImage`.

## Workflow

1. **Upload Image**: POST to `/api/image-upload` with image file
2. **Poll Status**: GET `/api/image-upload/{id}/status` to check processing status
3. **Process Complete**: When status is `processed`, the `AvailableImage` details are returned

## API Endpoints

### Upload Image

```http
POST /api/image-upload
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "file": [image file]
}
```

**Response:**

```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "path": "image_uploads/example.jpg",
    "name": "example.jpg",
    "extension": "jpg",
    "mime_type": "image/jpeg",
    "size": 1048576,
    "created_at": "2025-01-01T12:00:00Z",
    "updated_at": "2025-01-01T12:00:00Z"
  }
}
```

### Check Status

```http
GET /api/image-upload/{id}/status
Authorization: Bearer {token}
```

**Response - Processing:**

```json
{
  "status": "processing",
  "available_image": null
}
```

**Response - Processed:**

```json
{
  "status": "processed",
  "available_image": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "path": "images/example.jpg",
    "comment": null,
    "created_at": "2025-01-01T12:00:30Z",
    "updated_at": "2025-01-01T12:00:30Z"
  }
}
```

**Response - Not Found:**

```json
{
  "status": "not_found",
  "available_image": null
}
```

## Status Values

- `processing`: The image is still being processed
- `processed`: Processing complete, AvailableImage is ready
- `not_found`: No ImageUpload or AvailableImage found with this ID

## JavaScript Example

```javascript
async function uploadAndPollStatus(file) {
  // Upload the image
  const formData = new FormData()
  formData.append('file', file)

  const uploadResponse = await fetch('/api/image-upload', {
    method: 'POST',
    body: formData,
    headers: {
      Authorization: `Bearer ${token}`,
    },
  })

  const uploadData = await uploadResponse.json()
  const uploadId = uploadData.data.id

  // Poll for status
  let status = 'processing'
  let availableImage = null

  while (status === 'processing') {
    const statusResponse = await fetch(`/api/image-upload/${uploadId}/status`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    const statusData = await statusResponse.json()
    status = statusData.status
    availableImage = statusData.available_image

    if (status === 'processing') {
      // Wait 1 second before polling again
      await new Promise(resolve => setTimeout(resolve, 1000))
    }
  }

  if (status === 'processed') {
    console.log('Image processed successfully:', availableImage)

    // You can now use the available image
    const imageUrl = `/api/available-image/${availableImage.id}/view`
    const downloadUrl = `/api/available-image/${availableImage.id}/download`

    return { availableImage, imageUrl, downloadUrl }
  } else {
    throw new Error(`Image processing failed: ${status}`)
  }
}
```

## React Hook Example

```javascript
import { useState, useEffect } from 'react'

function useImageUploadStatus(uploadId) {
  const [status, setStatus] = useState('processing')
  const [availableImage, setAvailableImage] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!uploadId) return

    const pollStatus = async () => {
      try {
        const response = await fetch(`/api/image-upload/${uploadId}/status`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        })

        if (!response.ok) {
          throw new Error('Failed to check status')
        }

        const data = await response.json()
        setStatus(data.status)
        setAvailableImage(data.available_image)

        if (data.status === 'processing') {
          // Continue polling
          setTimeout(pollStatus, 1000)
        }
      } catch (err) {
        setError(err.message)
      }
    }

    pollStatus()
  }, [uploadId])

  return { status, availableImage, error }
}

// Usage
function ImageUploadComponent() {
  const [uploadId, setUploadId] = useState(null)
  const { status, availableImage, error } = useImageUploadStatus(uploadId)

  const handleUpload = async file => {
    const formData = new FormData()
    formData.append('file', file)

    const response = await fetch('/api/image-upload', {
      method: 'POST',
      body: formData,
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()
    setUploadId(data.data.id)
  }

  return (
    <div>
      <input type="file" onChange={e => handleUpload(e.target.files[0])} />

      {status === 'processing' && <p>Processing image...</p>}
      {status === 'processed' && availableImage && (
        <div>
          <p>Image processed successfully!</p>
          <img src={`/api/available-image/${availableImage.id}/view`} alt="Processed" />
        </div>
      )}
      {error && <p>Error: {error}</p>}
    </div>
  )
}
```

## Implementation Notes

1. **ImageUpload Lifecycle**: After processing, the `ImageUpload` record is deleted and replaced with an `AvailableImage` record using the same ID.

2. **Polling Frequency**: Avoid polling too frequently to prevent server overload. A 1-second interval is usually sufficient.

3. **Error Handling**: Always handle the `not_found` status, which indicates an error or that the resource never existed.

4. **Authentication**: All endpoints require authentication via Bearer token.

5. **File Access**: Once processed, use the `AvailableImage` endpoints for viewing and downloading:
   - `/api/available-image/{id}/view` - View inline
   - `/api/available-image/{id}/download` - Download file

## Best Practices

1. **Timeout**: Implement a timeout mechanism to avoid infinite polling
2. **Exponential Backoff**: Consider increasing poll intervals over time
3. **WebSocket Alternative**: For real-time updates, consider using WebSockets or Server-Sent Events
4. **Error Recovery**: Handle network errors gracefully with retry logic
5. **User Feedback**: Show progress indicators and status messages to users
