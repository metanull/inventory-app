# Amulets / Islamic Art Import Validation Report

Date: 2026-05-02

## Scope

This report validates a small, targeted sample of Islamic Art records displayed by `https://amulets.museumwnf.org` against:

- the live website API used by the amulets frontend;
- the production legacy source databases on the MWNF Windows server;
- the imported Inventory database on the OVH server.

The analysis is read-only. It does not use earlier reports in this folder as source material.

The live amulets collection endpoint currently reports 46 object records for the gallery context. The first collection page includes mwnf3 Islamic Art / EPM records and a small number of Sharing History records. This validation pass stays on the Islamic Art / mwnf3 side because the requested scope is Islamic Art website data.

Relevant model guidance:

- [Legacy Import](../../../docs/understanding/legacy-import.md) explains that the importer transforms legacy sources into the new model instead of copying tables one-to-one.
- [Core Model](../../../docs/understanding/core-model.md) explains Items, Partners, Translations, Tags, Images, and Timelines.
- [Validation Guide](../../../docs/understanding/validation-guide.md) explains the comparison method used here.

## Pages And Samples Checked

The sample intentionally covers several public website functions rather than many records from one page.

| Website function | Sample checked | Why it matters |
|---|---|---|
| Collection list | First page of `/api/v2/objects`, 12 records requested | Checks list cards: title, date, partner, country, image, project, and sorting context. |
| Object detail | `ISL / jo / Mus01 / 4`, `Inscription stone.` | Checks a Discover Islamic Art object with description, dimensions, image, dynasty, tags, and author data. |
| Object detail | `EPM / gr / Mus21 / 25`, `Amulet` | Checks an Explore Islamic Art Collections object that also appears in the amulets gallery context. |
| Object detail | `EPM / dn / Mus21 / 15`, `Amulet case` | Checks a case where the public dynasty is present as tag data but not as a structured dynasty link. |
| Object detail | `ISL / dz / Mus01 / 45`, `Medallions (bracteates).` | Checks a multi-dynasty object and a source-side holder/partner distinction. |
| Partner profile | `gr / Mus21`, Benaki Museum | Checks partner name, city, address, website, logo, and partner images. |
| Timeline | `mwnf3.hcr` event `1`, Algeria | Checks HCR chronology import and translation. |

## Overall Assessment

The sampled import is strong for core content identity and traceability.

Every sampled public object, partner, image, and timeline event was found in the imported Inventory database. The key public facts shown by the website - titles, date ranges, textual dates, locations, partner names, image source paths, and the timeline description - are traceable back to legacy source rows and are present in Inventory.

The main gaps are not missing records. They are presentation and modeling differences that matter for the future read-only API:

- public image copyright is not stored on `item_images` or `partner_logos` directly;
- one dynasty in the sample is imported as a tag but not as a structured dynasty relationship;
- imported picture child Item compatibility keys use lowercase project segments while object keys use uppercase project segments;
- the same item can have multiple English translations in different contexts, so a read-only API must choose the right context deliberately;
- some residual partner information still contains HTML inside `extra`.

These gaps are manageable, but they must be made explicit before customer validation starts, otherwise reviewers may report expected transformation differences as import failures.

## What Matched Well

### Object Identity And Main Fields

All four object samples exist in Inventory with the expected `backward_compatibility` values:

| Website sample | Inventory status | Main fields checked |
|---|---|---|
| `mwnf3:objects:ISL:jo:Mus01:4` | Found | Type `object`, country `jor`, project `Discover Islamic Art`, start/end `700 / 750`, English title `Inscription stone.`, date text, location `Amman`, dimensions, holder. |
| `mwnf3:objects:EPM:gr:Mus21:25` | Found | Type `object`, country `grc`, project `Explore Islamic Art Collections`, start/end `800 / 900`, English title `Amulet`, date text, location `Athens`, holder `Benaki Museum`. |
| `mwnf3:objects:EPM:dn:Mus21:15` | Found | Type `object`, country `dnk`, project `Explore Islamic Art Collections`, start/end `900 / 1100`, English title `Amulet case`, date text, location `Copenhagen`, holder `The David Collection`. |
| `mwnf3:objects:ISL:dz:Mus01:45` | Found | Type `object`, country `dza`, project `Discover Islamic Art`, start/end `1125 / 1575`, English title `Medallions (bracteates).`, date text, location `Setif`, dimensions, holder. |

Legacy source rows contain the same public values. For example, the Jordan object has `start_date=700`, `end_date=750`, the public date text, dimensions, and image `objects/isl/jo/1/4/1.jpg` in the source database. The imported Inventory record keeps the same business meaning after country-code normalization from `jo` to `jor`.

### Images

All four sampled object images are represented in Inventory in two ways:

- an image attached to the parent object Item;
- a child Item of type `picture`, also with an attached image.

This matches the documented import model: object pictures attach to the parent content and also become identifiable child picture Items.

| Website image | Inventory evidence |
|---|---|
| `objects/isl/jo/1/4/1.jpg` | Parent image exists; picture child Item exists. |
| `objects/epm/gr/21/25/1.jpg` | Parent image exists; picture child Item exists. |
| `objects/epm/dn/21/15/1.jpg` | Parent image exists; picture child Item exists. |
| `objects/isl/dz/1/45/1.jpg` | Parent image exists; picture child Item exists. |

The imported image storage path is UUID-based, while `original_name` preserves the legacy source path. That is expected and supports traceability.

### Partner Profile

The Benaki Museum partner sample is present and correctly linked to the imported data:

| Public field | Legacy source | Inventory result |
|---|---|---|
| Name | `Benaki Museum` | Present in English partner translation. |
| City | `Athens` | Present as `city_display`. |
| Website | `http://www.benaki.gr/` | Present as contact website. |
| Logo | `museums/gr/21/logos/1.jpg` | Present as a partner logo. |
| Partner images | `museums/gr/21/1.jpg`, `/2.jpg`, `/3.jpg` | All three present as partner images. |
| Image copyright | `Benaki Museum` | Preserved in partner image `extra`. |

### Timeline

The timeline sample is present and traceable:

| Public field | Legacy source | Inventory result |
|---|---|---|
| Event id | `mwnf3.hcr.hcr_id=1` | Imported as `mwnf3:hcr:1`. |
| Country | `dz`, Algeria | Imported under country `dza`. |
| Date | `500` | Imported as `year_from=500`, `year_to=0`, with English date description `500`. |
| Description | `Vandal occupation and the Berber kingdom of the Djeddars.` | Present in English timeline event translation. |

## Gaps And Risks

### 1. Image Copyright Is Not On Item Images

The live website shows image copyright from the legacy picture row, for example:

- `EPM / gr / Mus21 / 25`: `Benaki Museum`;
- `EPM / dn / Mus21 / 15`: `The David Collection`.

Inventory has the images, and the source image path is preserved as `original_name`, but `item_images` has no copyright field. In the checked EPM samples, copyright appears on the child picture translation `extra`, not on the parent object's image row.

Business impact: a future read-only API cannot display object image copyright by reading only the parent Item image. It must either join to the picture child translation data or use a deliberately designed projection.

### 2. One Public Dynasty Is A Tag, Not A Structured Dynasty Link

`EPM / dn / Mus21 / 15`, `Amulet case`, shows `Samanid` publicly. The legacy source has this value as free text/tag data. Inventory contains the `Samanid` dynasty-category tag, but no structured `item_dynasty` relationship for this item.

This is not a missing object import. It is a difference between:

- public/gallery classification data, which is tag-based;
- normalized dynasty relationships, which only exist when the legacy normalized dynasty table provides them.

Business impact: if reviewers expect all displayed dynasties to appear in the same Inventory relationship, this item will look incomplete. The validation UI or read-only API should show both structured dynasties and dynasty-category tags, or clearly define which source is authoritative for public display.

### 3. Picture Compatibility Keys Differ In Case

Object Items use uppercase project codes in their compatibility keys, for example:

```text
mwnf3:objects:EPM:gr:Mus21:25
```

The matching picture child Items are stored with lowercase project codes, for example:

```text
mwnf3:objects_pictures:epm:gr:Mus21:25:1
```

The database collation matches them case-insensitively, so the queried rows are found. A binary comparison does not match.

Business impact: this is a traceability sharp edge. Any validation tool, read-only API, or export that compares compatibility values outside the database should normalize case for picture keys or use the stored value exactly.

### 4. Multiple Contexts Exist For The Same Public Language

Some sampled Items have more than one English translation because Inventory keeps translations by language and context.

Examples found:

- `ISL / jo / Mus01 / 4` has English translations for `mwnf3:projects:ISL` and `mwnf3:projects:EPM`.
- `EPM / gr / Mus21 / 25` has its object translation and an additional thematic-gallery context translation for gallery `47`; one checked gallery-context English row has a blank name.
- `ISL / dz / Mus01 / 45` has English translations for `mwnf3:projects:ISL` and `mwnf3:projects:EPM`.

Business impact: the imported data supports multiple editorial contexts, but public validation must not pick an arbitrary English row. The future read-only API needs a clear context selection rule for each website or gallery.

### 5. Some Residual Partner Data Still Contains HTML

Object descriptions checked in Inventory did not contain HTML tags. Partner description and address fields checked for Benaki Museum also did not contain HTML tags in the main normalized fields.

However, Benaki Museum partner `extra` contains `opening_hours` with `<br/>` HTML.

Business impact: this does not break the sampled public profile fields, but any future display of `extra.opening_hours` should clean or render that value intentionally.

### 6. Source Holder And Website Partner Can Differ

For `ISL / dz / Mus01 / 45`, the object row source holder is `National Museum of Setif`, while the public partner page comes from the museum master row for `dz / Mus01`, `National Museum of Antiquities and Islamic Art`.

Inventory preserves the object holder on the item translation and also links the Item to the partner record.

Business impact: this is a source-data distinction, not necessarily an importer error. Reviewers should compare holder and partner as separate concepts.

## Sample-by-Sample Notes

### `Inscription stone.` - `ISL / jo / Mus01 / 4`

Status: good match.

- Website values for title, date, start/end year, location, dimensions, image, dynasty, materials, and type tags are present in the legacy source.
- Inventory contains the object Item, English translations, parent image, child picture Item, structured dynasty `Umayyads`, and dynasty tag `Umayyad`.
- The holder remains `Jordan Archaeological Museum.` in Inventory, including the final period from the source.

### `Amulet` - `EPM / gr / Mus21 / 25`

Status: good match with gallery-context caution.

- Website values for title, date, image, image copyright, partner, location, dynasty, and tags are present in the legacy source.
- The thematic gallery reference exists in the source as gallery `47`, theme `13`, item `13`.
- Inventory contains the object, parent image, picture child Item, structured dynasty `Abbasids`, and tag `Abbasid`.
- Inventory also contains a gallery-context English translation with a blank name; public API consumers must choose the correct context.

### `Amulet case` - `EPM / dn / Mus21 / 15`

Status: core match, dynasty modeling gap.

- Website values for title, date, image, image copyright, partner, location, and material/type tags are present in the legacy source.
- Inventory contains the object, parent image, picture child Item, and expected tags.
- Inventory does not contain a structured dynasty link for `Samanid`; it contains `Samanid` as a dynasty-category tag.

### `Medallions (bracteates).` - `ISL / dz / Mus01 / 45`

Status: good match with holder/partner distinction.

- Website values for title, date, date range, image, dimensions, and dynasties are traceable to legacy source rows.
- Inventory contains structured dynasties `Almohads` and `Hafsids`, plus matching dynasty tags.
- The source object holder is `National Museum of Setif`; the partner master record used by the website is `National Museum of Antiquities and Islamic Art`.

### Benaki Museum Partner - `gr / Mus21`

Status: good match, residual HTML in extra.

- Name, city, website, logo, and three partner images are present in Inventory.
- Partner image copyright is preserved in partner image `extra`.
- Main normalized description and address fields checked without HTML.
- `extra.opening_hours` still contains `<br/>` HTML.

### Algeria Timeline Event - `mwnf3.hcr / 1`

Status: good match.

- Legacy `hcr` and `hcr_events` rows contain the public date and English description.
- Inventory contains the timeline event and English translation under the Algeria timeline.

## Practical Validation Guidance

When customers validate this imported content, they should check the following separately:

1. Identity: does the record correspond to the same legacy object, partner, or timeline event?
2. Meaning: do the title, date, description, location, holder, and partner have the same business meaning after code normalization and text cleanup?
3. Context: is the displayed translation coming from the correct project or gallery context?
4. Images: is the image present, and is the legacy image path preserved as `original_name`?
5. Public image credit: is copyright available through the right related record, not only the parent image row?
6. Classifications: are public dynasties/tags represented either as structured dynasty links or as tags?

## Recommended Follow-Up Checks

These are data validation checks, not development estimates.

| Check | Purpose |
|---|---|
| Review all amulets object image copyright paths | Confirm whether copyright consistently lives on child picture translations or partner image `extra`, and design the read-only API around that fact. |
| Review all dynasty-category tags without structured `item_dynasty` links | Separate true import gaps from cases where the legacy source only provides tag/free-text dynasty information. |
| Review picture child compatibility key casing | Decide whether the importer should preserve uppercase project codes for traceability, or whether validation tooling should normalize picture keys. |
| Review gallery-context translations with blank names | Confirm whether blank contextual fields are expected source data or need importer cleanup before public API projection. |
| Review `extra` fields before public exposure | Identify remaining HTML in fields such as partner opening hours before they are displayed by a new client. |

## Conclusion

The sampled Islamic Art import is fit for business validation of core content. The primary objects, partner profile, object images, picture child Items, tags, structured dynasties where available, and timeline event are present and traceable.

The main work before a public read-only API is not to re-import these samples. It is to define the public projection rules: which translation context to use, how to expose image credits, how to combine structured dynasties with dynasty tags, and how to handle residual HTML in `extra` fields.