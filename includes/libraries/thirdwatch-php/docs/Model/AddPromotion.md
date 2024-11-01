# AddPromotion

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_session_id** | **string** | The user&#39;s current session ID, used to tie a user&#39;s action before and after login or account creation. Required if no user_id values is provided. | [optional] 
**_device_ip** | **string** | IP address of the request made by the user. Recommended for historical backfills and customers with mobile apps. | [optional] 
**_origin_timestamp** | **string** | Represents the time the event occured in your system. Send as a UNIX timestamp in milliseconds in string. | [optional] 
**_promotions** | [**\ai\thirdwatch\Model\Promotion[]**](Promotion.md) | Contains all promotions that have been newly applied to the referenced user. | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


