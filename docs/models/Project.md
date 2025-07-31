---
layout: default
title: Project
parent: Database Models
---
# Project

**Namespace:** `App\Models\Project`

| Property               | Type    | Description                 |
| ---------------------- | ------- | --------------------------- |
| internal_name          | string  | Internal name               |
| backward_compatibility | string  | Backward compatibility info |
| launch_date            | date    | Launch date                 |
| is_launched            | boolean | Is launched                 |
| is_enabled             | boolean | Is enabled                  |
| context_id             | uuid    | Foreign key to Context      |
| language_id            | string  | Foreign key to Language     |
