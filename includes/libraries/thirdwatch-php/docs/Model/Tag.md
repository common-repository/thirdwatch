# Tag

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_is_bad** | **bool** | Indicates whether a user is engaging in behavior deemed harmful to your business. Set to true if the user is engaging in abusive activity. Set to false if the user is engaging in valid activity. | [optional] 
**_abuse_type** | **string** | The type of abuse for which you want to send a tag. It&#39;s important to send a tag specific to the type of abuse the user is committing so that thirdwatch can learn about specific patterns of behavior. You&#39;ll end up with more accurate results this way. e.g. _paymentAbuse, _contentAbuse, _promotionAbuse, _accountAbuse | [optional] 
**_description** | **string** | The text content of the tag.Useful as annotation on why the label was added. | [optional] 
**_source** | **string** | String describing the original source of the tag information (e.g. payment gateway, manual review, etc.). | [optional] 
**_analyst** | **string** | Unique identifier (e.g. email address) of the analyst who applied the label. Useful for tracking purposes after the fact. | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


