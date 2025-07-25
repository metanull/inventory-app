---
layout: default
title: TypeScript API Client
nav_order: 5
has_children: true
---

# TypeScript API Client Documentation

This section contains the automatically generated documentation for the TypeScript-Axios API client.

## Installation

```bash
npm install @metanull/inventory-app-api-client@latest
```

## Usage

```typescript
import { Configuration, DefaultApi } from "@metanull/inventory-app-api-client";

const api = new DefaultApi(
  new Configuration({ basePath: "https://your.api.url" }),
);
api.addressIndex().then((response) => console.log(response.data));
```

## Documentation Categories

### APIs (28 items)

- [AddressApi]({{ site.baseurl }}/api-client/addressapi)
- [AddressTranslationApi]({{ site.baseurl }}/api-client/addresstranslationapi)
- [AvailableImageApi]({{ site.baseurl }}/api-client/availableimageapi)
- [CollectionApi]({{ site.baseurl }}/api-client/collectionapi)
- [ContactApi]({{ site.baseurl }}/api-client/contactapi)
- [ContactTranslationApi]({{ site.baseurl }}/api-client/contacttranslationapi)
- [ContextApi]({{ site.baseurl }}/api-client/contextapi)
- [CountryApi]({{ site.baseurl }}/api-client/countryapi)
- [DetailApi]({{ site.baseurl }}/api-client/detailapi)
- [DetailTranslationApi]({{ site.baseurl }}/api-client/detailtranslationapi)
- [DetailTranslationsApi]({{ site.baseurl }}/api-client/detailtranslationsapi)
- [ImageUploadApi]({{ site.baseurl }}/api-client/imageuploadapi)
- [ItemApi]({{ site.baseurl }}/api-client/itemapi)
- [ItemTranslationApi]({{ site.baseurl }}/api-client/itemtranslationapi)
- [ItemTranslationsApi]({{ site.baseurl }}/api-client/itemtranslationsapi)
- [LanguageApi]({{ site.baseurl }}/api-client/languageapi)
- [LocationApi]({{ site.baseurl }}/api-client/locationapi)
- [LocationTranslationApi]({{ site.baseurl }}/api-client/locationtranslationapi)
- [MarkdownAPIEndpointsForMarkdownProcessingAndConversionApi]({{ site.baseurl }}/api-client/markdownapiendpointsformarkdownprocessingandconversionapi)
- [MarkdownApi]({{ site.baseurl }}/api-client/markdownapi)
- [MobileAppAuthenticationApi]({{ site.baseurl }}/api-client/mobileappauthenticationapi)
- [PartnerApi]({{ site.baseurl }}/api-client/partnerapi)
- [PictureApi]({{ site.baseurl }}/api-client/pictureapi)
- [PictureTranslationApi]({{ site.baseurl }}/api-client/picturetranslationapi)
- [ProjectApi]({{ site.baseurl }}/api-client/projectapi)
- [ProvinceApi]({{ site.baseurl }}/api-client/provinceapi)
- [ProvinceTranslationApi]({{ site.baseurl }}/api-client/provincetranslationapi)
- [TagApi]({{ site.baseurl }}/api-client/tagapi)

### Models (27 items)

- [AddressResource]({{ site.baseurl }}/api-client/addressresource)
- [AddressTranslationResource]({{ site.baseurl }}/api-client/addresstranslationresource)
- [ArtistResource]({{ site.baseurl }}/api-client/artistresource)
- [AuthorResource]({{ site.baseurl }}/api-client/authorresource)
- [AvailableImageResource]({{ site.baseurl }}/api-client/availableimageresource)
- [CollectionResource]({{ site.baseurl }}/api-client/collectionresource)
- [CollectionTranslationResource]({{ site.baseurl }}/api-client/collectiontranslationresource)
- [ContactResource]({{ site.baseurl }}/api-client/contactresource)
- [ContactTranslationResource]({{ site.baseurl }}/api-client/contacttranslationresource)
- [ContextResource]({{ site.baseurl }}/api-client/contextresource)
- [CountryResource]({{ site.baseurl }}/api-client/countryresource)
- [DetailResource]({{ site.baseurl }}/api-client/detailresource)
- [DetailTranslationResource]({{ site.baseurl }}/api-client/detailtranslationresource)
- [ImageUploadResource]({{ site.baseurl }}/api-client/imageuploadresource)
- [ItemResource]({{ site.baseurl }}/api-client/itemresource)
- [ItemTranslationResource]({{ site.baseurl }}/api-client/itemtranslationresource)
- [LanguageResource]({{ site.baseurl }}/api-client/languageresource)
- [LocationResource]({{ site.baseurl }}/api-client/locationresource)
- [LocationTranslationResource]({{ site.baseurl }}/api-client/locationtranslationresource)
- [PartnerResource]({{ site.baseurl }}/api-client/partnerresource)
- [PictureResource]({{ site.baseurl }}/api-client/pictureresource)
- [PictureTranslationResource]({{ site.baseurl }}/api-client/picturetranslationresource)
- [ProjectResource]({{ site.baseurl }}/api-client/projectresource)
- [ProvinceResource]({{ site.baseurl }}/api-client/provinceresource)
- [ProvinceTranslationResource]({{ site.baseurl }}/api-client/provincetranslationresource)
- [TagResource]({{ site.baseurl }}/api-client/tagresource)
- [WorkshopResource]({{ site.baseurl }}/api-client/workshopresource)

### Requests (43 items)

- [AddressStoreRequest]({{ site.baseurl }}/api-client/addressstorerequest)
- [AddressTranslationStoreRequest]({{ site.baseurl }}/api-client/addresstranslationstorerequest)
- [AddressTranslationUpdateRequest]({{ site.baseurl }}/api-client/addresstranslationupdaterequest)
- [AddressUpdateRequest]({{ site.baseurl }}/api-client/addressupdaterequest)
- [AvailableImageUpdateRequest]({{ site.baseurl }}/api-client/availableimageupdaterequest)
- [CollectionStoreRequest]({{ site.baseurl }}/api-client/collectionstorerequest)
- [CollectionUpdateRequest]({{ site.baseurl }}/api-client/collectionupdaterequest)
- [ContactStoreRequest]({{ site.baseurl }}/api-client/contactstorerequest)
- [ContactTranslationStoreRequest]({{ site.baseurl }}/api-client/contacttranslationstorerequest)
- [ContactTranslationUpdateRequest]({{ site.baseurl }}/api-client/contacttranslationupdaterequest)
- [ContactUpdateRequest]({{ site.baseurl }}/api-client/contactupdaterequest)
- [ContextSetDefaultRequest]({{ site.baseurl }}/api-client/contextsetdefaultrequest)
- [ContextStoreRequest]({{ site.baseurl }}/api-client/contextstorerequest)
- [CountryStoreRequest]({{ site.baseurl }}/api-client/countrystorerequest)
- [CountryUpdateRequest]({{ site.baseurl }}/api-client/countryupdaterequest)
- [DetailStoreRequest]({{ site.baseurl }}/api-client/detailstorerequest)
- [DetailTranslationStoreRequest]({{ site.baseurl }}/api-client/detailtranslationstorerequest)
- [DetailTranslationUpdateRequest]({{ site.baseurl }}/api-client/detailtranslationupdaterequest)
- [ItemStoreRequest]({{ site.baseurl }}/api-client/itemstorerequest)
- [ItemTranslationStoreRequest]({{ site.baseurl }}/api-client/itemtranslationstorerequest)
- [ItemTranslationUpdateRequest]({{ site.baseurl }}/api-client/itemtranslationupdaterequest)
- [ItemUpdateTagsRequest]({{ site.baseurl }}/api-client/itemupdatetagsrequest)
- [ItemWithAllTagsRequest]({{ site.baseurl }}/api-client/itemwithalltagsrequest)
- [LanguageStoreRequest]({{ site.baseurl }}/api-client/languagestorerequest)
- [LanguageUpdateRequest]({{ site.baseurl }}/api-client/languageupdaterequest)
- [LocationStoreRequest]({{ site.baseurl }}/api-client/locationstorerequest)
- [LocationTranslationStoreRequest]({{ site.baseurl }}/api-client/locationtranslationstorerequest)
- [LocationTranslationUpdateRequest]({{ site.baseurl }}/api-client/locationtranslationupdaterequest)
- [LocationUpdateRequest]({{ site.baseurl }}/api-client/locationupdaterequest)
- [MarkdownFromHtmlRequest]({{ site.baseurl }}/api-client/markdownfromhtmlrequest)
- [MarkdownIsMarkdownRequest]({{ site.baseurl }}/api-client/markdownismarkdownrequest)
- [MarkdownToHtmlRequest]({{ site.baseurl }}/api-client/markdowntohtmlrequest)
- [PartnerStoreRequest]({{ site.baseurl }}/api-client/partnerstorerequest)
- [PictureTranslationStoreRequest]({{ site.baseurl }}/api-client/picturetranslationstorerequest)
- [PictureTranslationUpdateRequest]({{ site.baseurl }}/api-client/picturetranslationupdaterequest)
- [PictureUpdateRequest]({{ site.baseurl }}/api-client/pictureupdaterequest)
- [ProjectSetEnabledRequest]({{ site.baseurl }}/api-client/projectsetenabledrequest)
- [ProjectSetLaunchedRequest]({{ site.baseurl }}/api-client/projectsetlaunchedrequest)
- [ProjectStoreRequest]({{ site.baseurl }}/api-client/projectstorerequest)
- [ProvinceTranslationStoreRequest]({{ site.baseurl }}/api-client/provincetranslationstorerequest)
- [ProvinceTranslationUpdateRequest]({{ site.baseurl }}/api-client/provincetranslationupdaterequest)
- [TagStoreRequest]({{ site.baseurl }}/api-client/tagstorerequest)
- [TokenAcquireRequest]({{ site.baseurl }}/api-client/tokenacquirerequest)

### Responses (54 items)

- [AddressIndex200Response]({{ site.baseurl }}/api-client/addressindex200response)
- [AddressStore201Response]({{ site.baseurl }}/api-client/addressstore201response)
- [AddressStore422Response]({{ site.baseurl }}/api-client/addressstore422response)
- [AddressTranslationIndex200Response]({{ site.baseurl }}/api-client/addresstranslationindex200response)
- [AddressTranslationStore200Response]({{ site.baseurl }}/api-client/addresstranslationstore200response)
- [AvailableImageIndex200Response]({{ site.baseurl }}/api-client/availableimageindex200response)
- [AvailableImageShow200Response]({{ site.baseurl }}/api-client/availableimageshow200response)
- [CollectionIndex200Response]({{ site.baseurl }}/api-client/collectionindex200response)
- [CollectionStore200Response]({{ site.baseurl }}/api-client/collectionstore200response)
- [ContactIndex200Response]({{ site.baseurl }}/api-client/contactindex200response)
- [ContactStore201Response]({{ site.baseurl }}/api-client/contactstore201response)
- [ContactTranslationIndex200Response]({{ site.baseurl }}/api-client/contacttranslationindex200response)
- [ContactTranslationStore200Response]({{ site.baseurl }}/api-client/contacttranslationstore200response)
- [ContextGetDefault404Response]({{ site.baseurl }}/api-client/contextgetdefault404response)
- [ContextIndex200Response]({{ site.baseurl }}/api-client/contextindex200response)
- [ContextSetDefault200Response]({{ site.baseurl }}/api-client/contextsetdefault200response)
- [CountryIndex200Response]({{ site.baseurl }}/api-client/countryindex200response)
- [CountryStore200Response]({{ site.baseurl }}/api-client/countrystore200response)
- [DetailIndex200Response]({{ site.baseurl }}/api-client/detailindex200response)
- [DetailStore200Response]({{ site.baseurl }}/api-client/detailstore200response)
- [DetailTranslationShow200Response]({{ site.baseurl }}/api-client/detailtranslationshow200response)
- [ImageUploadIndex200Response]({{ site.baseurl }}/api-client/imageuploadindex200response)
- [ImageUploadStatus200Response]({{ site.baseurl }}/api-client/imageuploadstatus200response)
- [ImageUploadStatus404Response]({{ site.baseurl }}/api-client/imageuploadstatus404response)
- [ImageUploadStore200Response]({{ site.baseurl }}/api-client/imageuploadstore200response)
- [ItemIndex200Response]({{ site.baseurl }}/api-client/itemindex200response)
- [ItemStore200Response]({{ site.baseurl }}/api-client/itemstore200response)
- [ItemTranslationShow200Response]({{ site.baseurl }}/api-client/itemtranslationshow200response)
- [LanguageGetDefault404Response]({{ site.baseurl }}/api-client/languagegetdefault404response)
- [LanguageGetEnglish404Response]({{ site.baseurl }}/api-client/languagegetenglish404response)
- [LanguageIndex200Response]({{ site.baseurl }}/api-client/languageindex200response)
- [LanguageSetDefault200Response]({{ site.baseurl }}/api-client/languagesetdefault200response)
- [LocationIndex200Response]({{ site.baseurl }}/api-client/locationindex200response)
- [LocationStore201Response]({{ site.baseurl }}/api-client/locationstore201response)
- [LocationTranslationIndex200Response]({{ site.baseurl }}/api-client/locationtranslationindex200response)
- [LocationTranslationStore200Response]({{ site.baseurl }}/api-client/locationtranslationstore200response)
- [MarkdownPreview200Response]({{ site.baseurl }}/api-client/markdownpreview200response)
- [MarkdownPreview422Response]({{ site.baseurl }}/api-client/markdownpreview422response)
- [MarkdownPreview500Response]({{ site.baseurl }}/api-client/markdownpreview500response)
- [PartnerIndex200Response]({{ site.baseurl }}/api-client/partnerindex200response)
- [PartnerStore200Response]({{ site.baseurl }}/api-client/partnerstore200response)
- [PictureDetachFromItem422Response]({{ site.baseurl }}/api-client/picturedetachfromitem422response)
- [PictureIndex200Response]({{ site.baseurl }}/api-client/pictureindex200response)
- [PictureShow200Response]({{ site.baseurl }}/api-client/pictureshow200response)
- [PictureTranslationIndex200Response]({{ site.baseurl }}/api-client/picturetranslationindex200response)
- [PictureTranslationStore200Response]({{ site.baseurl }}/api-client/picturetranslationstore200response)
- [ProjectEnabled200Response]({{ site.baseurl }}/api-client/projectenabled200response)
- [ProjectSetLaunched200Response]({{ site.baseurl }}/api-client/projectsetlaunched200response)
- [ProvinceIndex200Response]({{ site.baseurl }}/api-client/provinceindex200response)
- [ProvinceStore201Response]({{ site.baseurl }}/api-client/provincestore201response)
- [ProvinceTranslationIndex200Response]({{ site.baseurl }}/api-client/provincetranslationindex200response)
- [ProvinceTranslationStore200Response]({{ site.baseurl }}/api-client/provincetranslationstore200response)
- [TagIndex200Response]({{ site.baseurl }}/api-client/tagindex200response)
- [TagStore200Response]({{ site.baseurl }}/api-client/tagstore200response)

### Other (14 items)

- [AddressStoreRequestTranslationsInner]({{ site.baseurl }}/api-client/addressstorerequesttranslationsinner)
- [AddressUpdateRequestTranslationsInner]({{ site.baseurl }}/api-client/addressupdaterequesttranslationsinner)
- [ContactStoreRequestTranslationsInner]({{ site.baseurl }}/api-client/contactstorerequesttranslationsinner)
- [ContactUpdateRequestTranslationsInner]({{ site.baseurl }}/api-client/contactupdaterequesttranslationsinner)
- [ImageUploadStatus200ResponseAnyOf]({{ site.baseurl }}/api-client/imageuploadstatus200responseanyof)
- [ImageUploadStatus200ResponseAnyOf1]({{ site.baseurl }}/api-client/imageuploadstatus200responseanyof1)
- [InlineObject]({{ site.baseurl }}/api-client/inlineobject)
- [InlineObject1]({{ site.baseurl }}/api-client/inlineobject1)
- [LocationStoreRequestTranslationsInner]({{ site.baseurl }}/api-client/locationstorerequesttranslationsinner)
- [LocationUpdateRequestTranslationsInner]({{ site.baseurl }}/api-client/locationupdaterequesttranslationsinner)
- [MarkdownPreview200ResponseData]({{ site.baseurl }}/api-client/markdownpreview200responsedata)
- [PictureTranslationIndex200ResponseLinks]({{ site.baseurl }}/api-client/picturetranslationindex200responselinks)
- [PictureTranslationIndex200ResponseMeta]({{ site.baseurl }}/api-client/picturetranslationindex200responsemeta)
- [PictureTranslationIndex200ResponseMetaLinksInner]({{ site.baseurl }}/api-client/picturetranslationindex200responsemetalinksinner)

## Package Information

- **Package Name:** `@metanull/inventory-app-api-client`
- **Repository:** [GitHub Packages](https://github.com/metanull/inventory-app/packages)
- **Generated:** 2025-07-09 17:37:44

## Generation

The client is generated using:

```powershell
. ./scripts/generate-api-client.ps1
```

And published using:

```powershell
. ./scripts/publish-api-client.ps1 -Credential (Get-Credential -Message "GitHub PAT")
```

---

_This documentation was automatically generated on 2025-07-09T17:37:44.655343_
