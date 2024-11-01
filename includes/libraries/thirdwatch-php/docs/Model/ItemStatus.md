# ItemStatus

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_session_id** | **string** | The user&#39;s current session ID, used to tie a user&#39;s action before and after login or account creation. Required if no user_id values is provided. | [optional] 
**_order_id** | **string** | The ID for the order that this chargeback is filed against. This field is not required if this chargeback was filed against a transaction with no _orderId. | [optional] 
**_item_id** | **string** | The item&#39;s unique identifier according to your systems. Use the same ID that you would use to look up items on your website&#39;s database. | [optional] 
**_item_status** | **string** | Indicates the high-level state of the order. e.g. _approved, _canceled, _held, _fulfilled, _returned, _rto | [optional] 
**_reason** | **string** | The reason for a cancellation. e.g. _paymentRisk, _abuse, _policy, _other | [optional] 
**_shipping_cost** | **string** | if _approved or _fulfilled than pass the shipping cost. e.g. \&quot;50\&quot; | [optional] 
**_tracking_number** | **string** | if _approved or _fulfilled than pass the tracking number. e.g. \&quot;55327470\&quot; | [optional] 
**_tracking_method** | **string** | if _approved or _fulfilled than pass the tracking url. e.g. \&quot;http://fedex.com/track?q&#x3D;abc123\&quot; | [optional] 
**_source** | **string** | The source of a decision. e.g. _automated, _manualReview\&quot; | [optional] 
**_analyst** | **string** | The analyst who made the decision, if manual. | [optional] 
**_description** | **string** | Any additional information about this order status change. | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


