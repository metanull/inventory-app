---
layout: default
title: "Daily Archive"
permalink: /archive/
---

# Development Daily Archive

This page shows all daily development summaries in chronological order.

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
        {% if post.commit_count %}
          <span class="commit-count-badge">{{ post.commit_count }} commit{{ post.commit_count | pluralize: "", "s" }}</span>
        {% endif %}
        {% if post.github_refs and post.github_refs.size > 0 %}
          <span class="github-refs">
            {% for ref in post.github_refs %}
              <a href="https://github.com/{{ site.repository }}/issues/{{ ref }}" target="_blank" class="issue-ref">#{{ ref }}</a>
            {% endfor %}
          </span>
        {% endif %}
        {% if post.author %}
          <span class="archive-author">{{ post.author }}</span>
        {% endif %}
      </div>
      <h3 class="archive-title">
        <a href="{{ post.url | relative_url }}">{{ post.title | escape }}</a>
      </h3>
      {% if post.excerpt %}
        <div class="archive-excerpt">
          {{ post.excerpt | strip_html | truncatewords: 20 }}
        </div>
      {% endif %}
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
  flex-wrap: wrap;
}

.archive-date {
  font-weight: 600;
  min-width: 60px;
}

.commit-count-badge {
  background-color: #f1f3f4;
  color: #0366d6;
  padding: 0.2rem 0.5rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.github-refs {
  display: flex;
  gap: 0.25rem;
}

.issue-ref {
  background-color: #e1f5fe;
  color: #0366d6;
  padding: 0.15rem 0.3rem;
  border-radius: 6px;
  text-decoration: none;
  font-size: 0.75rem;
  font-weight: 600;
}

.issue-ref:hover {
  background-color: #b3e5fc;
  text-decoration: none;
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

.archive-excerpt {
  margin-top: 0.5rem;
  color: #586069;
  font-size: 0.9rem;
  line-height: 1.4;
}
</style>
