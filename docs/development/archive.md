---
layout: default
title: Development Archive
nav_order: 3
parent: Development
---

# Development Archive

Welcome to the Inventory API development archive! Here you'll find the latest updates, changes, and improvements to the application throughout its development history.

{: .highlight }

> This page is automatically updated with the latest changes from our GitHub repository. Each entry represents a day's worth of development activity.

---

{% for post in site.posts %}

## {{ post.date | date: "%B %d, %Y" }}

{{ post.content }}

---

{% endfor %}

{% if site.docs %}

## Recent Commits

{% assign sorted_docs = site.docs | sort: 'date' | reverse %}
{% for doc in sorted_docs limit:10 %}

### [{{ doc.title }}]({{ doc.url | relative_url }})

**{{ doc.date | date: "%B %d, %Y at %I:%M %p" }}** by {{ doc.author }}

{% if doc.commit_hash %}
**Commit:** `{{ doc.commit_hash }}`
{% endif %}

{% assign content_preview = doc.content | strip_html | truncatewords: 75 %}
{{ content_preview }}

[View full commit details]({{ doc.url | relative_url }})

---

{% endfor %}
{% endif %}

---

_This page is automatically generated from our Git commit history and updated daily._

_Last updated: {{ site.time | date: "%B %d, %Y at %I:%M %p" }}_
