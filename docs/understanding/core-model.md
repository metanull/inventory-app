---
layout: default
title: Core Model
parent: Understanding the Inventory
nav_order: 2
---

# Core Model

This page explains the main Inventory concepts in business terms. It uses the two initial model documents as source material and keeps the explanation focused on validation.

## Item

An Item is the central content unit. It represents something that can be described, illustrated, grouped, linked, and published.

| Item type | Meaning |
|---|---|
| `object` | A museum object or artifact. |
| `monument` | A monument, archaeological site, or architectural location. |
| `detail` | A component or close-up detail of another Item, usually a monument. |
| `picture` | A picture treated as its own identifiable child Item. |

An Item stores shared facts such as type, owner reference, MWNF reference, country, project, date range, coordinates, parent, and relationships. The readable text lives in Item Translations.

## Partner

A Partner represents an institution, museum, individual, or school connected to content. Partners replace legacy museums, institutions, schools, and similar content-owning bodies when they belong in the shared Inventory.

Partners have translations, country information, coordinates, images, logos, collection participation levels, and an optional link to the monument Item that represents their physical location.

## Collection

A Collection is a virtual grouping. It represents editorial structures rather than physical ownership.

Collection types include `collection`, `exhibition`, `gallery`, `theme`, `subtheme`, `exhibition trail`, `itinerary`, `location`, and `region`.

Collections form trees. For example:

- Explore uses thematic cycles, countries, regions, locations, and monuments.
- Travels uses a root collection, trails, itineraries, locations, and monuments.
- Thematic Galleries use galleries, themes, and item assignments.

Items can have one primary Collection and also appear in additional Collections.

## Project and Context

A Project represents a content initiative or legacy project grouping. During import, a legacy project creates a Project, a Context, and a Collection. These records share the same legacy reference.

A Context describes the editorial purpose of translated text. It lets the same Item have different descriptions for different uses without becoming separate Items.

## Translation

A Translation stores readable text for a record in a language and, for major content records, in a context.

Translations exist for Items, Partners, Collections, Glossary entries, Timeline Events, Contributors, and Item Links. This structure keeps the shared record stable while allowing multilingual and context-specific content.

## Tag and Item Link

A Tag is a flexible label used for materials, keywords, filters, gallery tags, and similar classifications.

An Item Link is an explicit relationship from one Item to another Item. It replaces legacy relationship tables when the source expresses a direct relationship between two content records.

## Images, media, and documents

Images attach to typed parents such as Items, Partners, Collections, Contributors, and Timeline Events. Object, monument, and detail pictures also become child Items of type `picture`, so every imported picture has its own identity.

Media records store audio or video URLs. Document records store file references such as PDFs.

## Glossary and Timelines

The Glossary stores specialized words, definitions, and spellings. Imported spellings are preserved as source data, including cases that need later editorial cleanup.

Timelines group Heritage Conservation Resources chronology events. Timeline Events hold date information, translations, images, and item links when the source data provides them.

## Legacy concept mapping

| Legacy concept | Inventory concept |
|---|---|
| Object rows | Item of type `object`, plus Item Translations. |
| Monument rows | Item of type `monument`, plus Item Translations. |
| Monument details | Item of type `detail`, usually child of a monument Item. |
| Object, monument, and detail pictures | Item Images, plus child Items of type `picture`. |
| Museums, institutions, schools | Partners. |
| Projects | Project, Context, and Collection. |
| Exhibitions, galleries, themes, subthemes | Collections in a hierarchy. |
| Trails, itineraries, locations, regions | Collections in a hierarchy. |
| Related item tables | Item Links or Collection membership, depending on meaning. |
| Keywords, materials, filters, gallery labels | Tags. |
| Glossary words, definitions, spellings | Glossary entries, translations, and spellings. |
| HCR chronology | Timelines and Timeline Events. |
| Audio, video, PDFs | Item Media, Collection Media, and Item Documents. |

## Related reference

- [Legacy Import](legacy-import) explains how legacy data enters this model.
- [Database Models](../models/) gives developer-level field and relationship details.
