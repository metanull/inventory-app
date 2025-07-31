---
layout: default
title: Picture
parent: Database Models
---
# Picture

**Namespace:** `App\Models\Picture`

| Property               | Type   | Description                          |
| ---------------------- | ------ | ------------------------------------ |
| internal_name          | string | Internal name                        |
| backward_compatibility | string | Backward compatibility info          |
| copyright_text         | string | Copyright text                       |
| copyright_url          | string | Copyright URL                        |
| path                   | string | Path to image file                   |
| upload_name            | string | Uploaded file name                   |
| upload_extension       | string | File extension                       |
| upload_mime_type       | string | MIME type                            |
| upload_size            | int    | File size                            |
| pictureable_type       | string | Type of parent (Item/Detail/Partner) |
| pictureable_id         | uuid   | Parent ID                            |
