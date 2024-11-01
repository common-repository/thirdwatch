# SubmitReview

## Properties
Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**_user_id** | **string** | The user&#39;s account ID according to your systems. Note that user IDs are case sensitive. | [optional] 
**_session_id** | **string** | The user&#39;s current session ID, used to tie a user&#39;s action before and after login or account creation. Required if no user_id values is provided. | [optional] 
**_device_ip** | **string** | IP address of the request made by the user. Recommended for historical backfills and customers with mobile apps. | [optional] 
**_origin_timestamp** | **string** | Represents the time the event occured in your system. Send as a UNIX timestamp in milliseconds in string. | [optional] 
**_review_title** | **string** | The title of review submitted. | [optional] 
**_review_content** | **string** | The text content of review submitted. | [optional] 
**_item_id** | **string** | The ID of the product or service being reviewed. | [optional] 
**_submission_status** | **string** | If reviews in your system must be approved, use _submissionStatus to represent the status of the review. e.g. _success, _failure, _pending | [optional] 
**_rating** | **string** | The rating provided by the user. e.g. \&quot;4\&quot; | [optional] 
**_custom_info** | [**\ai\thirdwatch\Model\CustomInfo**](CustomInfo.md) |  | [optional] 

[[Back to Model list]](../README.md#documentation-for-models) [[Back to API list]](../README.md#documentation-for-api-endpoints) [[Back to README]](../README.md)


