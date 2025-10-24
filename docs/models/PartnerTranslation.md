---
layout: default
title: PartnerTranslation
parent: Database Models
---

# PartnerTranslation

**Namespace:** `App\Models\PartnerTranslation`

| Property               | Type   | Description                           |
| ---------------------- | ------ | ------------------------------------- |
| partner_id             | uuid   | Foreign key to Partner                |
| language_id            | string | Foreign key to Language (ISO 639-3)   |
| context_id             | uuid   | Foreign key to Context                |
| name                   | string | Partner name in this language/context |
| description            | text   | Partner description                   |
| city_display           | string | Display city name                     |
| address_line_1         | string | Address line 1                        |
| address_line_2         | string | Address line 2                        |
| postal_code            | string | Postal/ZIP code                       |
| address_notes          | text   | Additional address notes              |
| contact_name           | string | Primary contact person name           |
| contact_email_general  | string | General contact email                 |
| contact_email_press    | string | Press/media contact email             |
| contact_phone          | string | Primary contact phone                 |
| contact_website        | string | Partner website URL                   |
| contact_notes          | text   | Additional contact information        |
| contact_emails         | json   | Array of additional email addresses   |
| contact_phones         | json   | Array of additional phone numbers     |
| backward_compatibility | string | Backward compatibility reference      |
| extra                  | json   | Additional metadata (flexible)        |

**Relationships:**

- `partner()`: Belongs to `Partner`
- `language()`: Belongs to `Language`
- `context()`: Belongs to `Context`
- `partnerTranslationImages()`: Has many `PartnerTranslationImage`

**Unique Constraint:**

The combination of `(partner_id, language_id, context_id)` must be unique.
