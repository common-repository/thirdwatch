# ai\thirdwatch\UpdateAccountApi

All URIs are relative to *https://localhost/event*

Method | HTTP request | Description
------------- | ------------- | -------------
[**updateAccount**](UpdateAccountApi.md#updateAccount) | **POST** /v1/update_account | Use update_account to record changes to the user&#39;s account information.


# **updateAccount**
> \ai\thirdwatch\Model\EventResponse updateAccount($json)

Use update_account to record changes to the user's account information.

For user accounts created before integration with Thirdwatch, there's no need to call `create_account` before `update_account`. Simply call `update_account` and we'll infer that account was created before integration.

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Configure API key authorization: api_key
$config = ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKey('X-THIRDWATCH-API-KEY', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = ai\thirdwatch\Configuration::getDefaultConfiguration()->setApiKeyPrefix('X-THIRDWATCH-API-KEY', 'Bearer');

$apiInstance = new ai\thirdwatch\Api\UpdateAccountApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$json = new \ai\thirdwatch\Model\UpdateAccount(); // \ai\thirdwatch\Model\UpdateAccount | Pass user details after update or change in user info. Only `_userID` is required field. But this should contain user info.

try {
    $result = $apiInstance->updateAccount($json);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling UpdateAccountApi->updateAccount: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **json** | [**\ai\thirdwatch\Model\UpdateAccount**](../Model/UpdateAccount.md)| Pass user details after update or change in user info. Only &#x60;_userID&#x60; is required field. But this should contain user info. |

### Return type

[**\ai\thirdwatch\Model\EventResponse**](../Model/EventResponse.md)

### Authorization

[api_key](../../README.md#api_key)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

