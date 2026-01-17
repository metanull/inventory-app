# Copyright Rules

## Watermark eligibility

Only the sections listed in DatabaseCopyrightResolver::WATERMARK*SECTIONS (objects, monuments, trails + variants, activities, products, books, schools, presslounge, explore_location, sh*\_, thg\_\_, etc.) receive a copyright. Other sections fall back to '© Museum With No Frontiers'.

## Global defaults & special cases

If no project-specific copyright can be found, the resolver returns '© Museum With No Frontiers'.

## Fixed project text

- project_id === 'isl': always '© Discover Islamic Art (MWNF)'.
- project_id === 'iam': '© Islamic Art in the Mediterranean (MWNF)'.
- section === 'presslounge': '© MWNF'.

## Database-driven text

Projects that rely on the database include bar, epm, dca, dga, exhcolour, galleries, and the “other” cases for monuments/objects. The resolver fetches from the following tables/columns (always where the copyright column is non-empty; English rows):

- mwnf3.monuments_pictures → copyright (keys: project_id, country, institution_id, number, lang='en', image_number).
- mwnf3.monument_detail_pictures → copyright (adds detail_id, picture_id).
- mwnf3.objects_pictures → copyright (museum_id, number, image_number, project_id, country, lang='en').
- mwnf3_explore.locations_pictures (explore_location only) → copyright filtered by locationId/image_number.
- sh_object_image_texts, sh_monument_image_texts (Sharing History objects/monuments) → copyright (keys: project_id, country, number, image_number).
- sh_project_about_images_lang/sh_project_about_chronology_images_texts (section === sh_about) → copyright.
- mwnf3_thematic_gallery.thg_object_image_texts, thg_monument_image_texts (Thematic Gallery objects/monuments) → copyright.

In every case the resolver formats the returned text with spacing to match legacy appearance (formatCopyrightWithSpacing): long copyrights get '© …', shorter ones get '© …'.

## Section-specific defaults when DB unavailable

When the respective DB connection is missing or the query returns nothing, the resolver returns section-branded fallback strings:

- Sharing History: '© MWNF | Sharing History' (objects), '© MWNF | Sharing History' (monuments/about).
- Thematic Gallery: '© MWNF | Thematic Gallery' or '© MWNF | Thematic Gallery', depending on length.
