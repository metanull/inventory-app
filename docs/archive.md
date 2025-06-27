---
layout: default
title: "Commit Archive"
permalink: /archive/
---

# All Commits

This page shows all commits to the main branch in chronological order.

<div class="archive-container">
  {% assign posts = site.posts | sort: 'date' | reverse %}
  {% assign current_year = '' %}
  
  {% for post in posts %}
    {% assign post_year = post.date | date: '%Y' %}
    
    {% if post_year != current_year %}
      {% unless forloop.first %}</ul>{% endunless %}
      <h2>{{ post_year }}</h2>
      <ul class="archive-list">
      {% assign current_year = post_year %}
    {% endif %}
    
    <li class="archive-item">
      <div class="archive-meta">
        <span class="archive-date">{{ post.date | date: "%b %-d" }}</span>
        {% if post.commit %}
          <a href="https://github.com/{{ site.repository }}/commit/{{ post.commit }}" target="_blank" class="commit-hash">
            <code>{{ post.commit | slice: 0, 7 }}</code>
          </a>
        {% endif %}
        {% if post.author %}
          <span class="archive-author">{{ post.author }}</span>
        {% endif %}
      </div>
      <h3 class="archive-title">
        <a href="{{ post.url | relative_url }}">{{ post.title | escape }}</a>
      </h3>
    </li>
    
    {% if forloop.last %}</ul>{% endif %}
  {% endfor %}
</div>

<style>
.archive-container h2 {
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #24292e;
  border-bottom: 1px solid #e1e5e9;
  padding-bottom: 0.5rem;
}

.archive-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.archive-item {
  margin-bottom: 1rem;
  padding: 1rem;
  border-left: 3px solid #e1e5e9;
  background-color: #fafbfc;
  transition: border-left-color 0.2s;
}

.archive-item:hover {
  border-left-color: #0366d6;
}

.archive-meta {
  font-size: 0.85rem;
  color: #586069;
  margin-bottom: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.archive-date {
  font-weight: 600;
  min-width: 60px;
}

.commit-hash {
  color: #0366d6;
  text-decoration: none;
  font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
  font-size: 0.8rem;
}

.commit-hash:hover {
  text-decoration: underline;
}

.archive-author {
  font-style: italic;
}

.archive-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.archive-title a {
  color: #24292e;
  text-decoration: none;
}

.archive-title a:hover {
  color: #0366d6;
  text-decoration: underline;
}
</style>
