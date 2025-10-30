# VersionResource


## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**repository** | **string** |  | [default to undefined]
**build_timestamp** | **string** |  | [default to undefined]
**repository_url** | **string** |  | [default to undefined]
**api_client_version** | **string** |  | [default to undefined]
**app_version** | [**VersionResourceAppVersion**](VersionResourceAppVersion.md) |  | [default to undefined]
**commit_sha** | **string** |  | [default to undefined]

## Example

```typescript
import { VersionResource } from './api';

const instance: VersionResource = {
    repository,
    build_timestamp,
    repository_url,
    api_client_version,
    app_version,
    commit_sha,
};
```

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)
