<?php
/**
* Uploadify.class.php
*
* Copyright c 2015, SUPERHOLDER. All rights reserved.
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or at your option any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301  USA
*/


namespace Module\HTMLForm\SpecialElement;

if (!defined('InSite'))
{
    die ('Hacking attempt');
}

/**
* Implements a Uploadify component
* @package Module\HTMLForm\SpecialElement
*/
class Uploadify extends \Module\HTMLForm\HTMLElement
{
    /**
    * @publicset
    * @publicget
    * @var boolean Automatically upload files as they are added to the queue.
    */
    protected $auto = false;

	/**
	* @publicget
	* @publicset
	* @var bool Automatically create the upload object, defaults to true
	*/
    protected $autoInit = true;

	/**
	* @publicget
	* @publicset
	* @var string A class name to add to the Uploadify button.
	*/
	protected $buttonClass = null;

	const BUTTONCURSOR_HAND = 'hand';
	const BUTTONCURSOR_ARROW = 'arrow';

	/**
	* @publicget
	* @publicset
	* @var string Sets which cursor to display when hovering over the browse button.  The possible values are ‘hand’ and ‘arrow’.
	*/
	protected $buttonCursor = self::BUTTONCURSOR_HAND;

    /**
    * @publicset
    * @publicget
    * @var string The path to an image you would like to use as the browse button.
    */
    protected $buttonImage = null;

    /**
    * @publicset
    * @publicget
    * @var string The text that appears on the default button.
    */
    protected $buttonText = null;

	/**
	* @publicget
	* @publicset
	* @var bool The path to a file that checks whether the name of the file being uploaded currently exists in the destination folder.  The script should return 1 if the file name exists or 0 if the file name does not exist.
	*/
    protected $checkExisting = false;

	/**
	* @publicget
	* @publicset
	* @var bool Set to true to turn on the SWFUpload debugging mode.
	*/
    protected $debug = false;

    /**
    * @publicset
    * @publicget
    * @var string The name of the file object to use in your server-side script.  For example, in PHP, if this option is set to ‘the_files’, you can access the files that have been uploaded using $_FILES['the_files'];
    */
    protected $fileObjName = 'Filedata';

	/**
	* @publicget
	* @publicset
	* @var string The maximum size allowed for a file upload.  This value can be a number or string.  If it’s a string, it accepts a unit (B, KB, MB, or GB).  The default unit is in KB.  You can set this value to 0 for no limit.
	*/
    protected $fileSizeLimit = null;

    /**
    * @publicset
    * @publicget
    * @var string The description of the selectable files.  This string appears in the browse files dialog box in the file type drop down.
    */
    protected $fileTypeDesc = null;

    /**
    * @publicset
    * @publicget
    * @var string A list of allowable extensions that can be uploaded.  A manually typed in file name can bypass this level of security so you should always check file types in your server-side script.  Multiple extensions should be separated by semi-colons (i.e. ‘*.jpg; *.png; *.gif’).
    */
    protected $fileTypeExts = null;

    /**
    * @publicset
    * @publicget
    * @var string JSON object An object containing name/value pairs with additional information that should be sent to the back-end script when processing a file upload.
    */
    protected $formData = null;

    /**
    * @publicset
    * @publicget
    * @var integer The height of the browse button.
    */
    protected $height = 30;

	/**
	* @publicget
	* @publicset
	* @var string The itemTemplate option allows you to specify a special HTML template for each item that is added to the queue.
	* Four template tags are available:
	* 	•instanceID – The ID of the Uploadify instance
	* 	•fileID – The ID of the file added to the queue
	* 	•fileName – The name of the file added to the queue
	* 	•fileSize – The size of the file added to the queue
	*
	* Template tags are inserted into the template like such: ${fileName}.
	*/
    protected $itemTemplate = null;

	const METHOD_POST = 'post';
	const METHOD_GET = 'get';

    /**
    * @publicset
    * @publicget
    * @var string The form method for sending scriptData to the back-end script.
    */
    protected $method = self::METHOD_POST;

    /**
    * @publicset
    * @publicget
    * @var bool Set to false to allow only one file selection at a time.
    */
    protected $multi = true;

	/**
	* @publicset
	* @publicget
	* @var string An array of event names for which you would like to bypass the default scripts for.  You can tell which events can be overridden on the documentation page for each event.
	*/
	protected $overrideEvents = null;

	/**
	* @publicget
	* @publicset
	* @var bool If set to true, a random value is added to the SWF file URL so it doesn’t cache.  This will conflict with any existing querystring parameters on the SWF file URL.
	*/
	protected $preventCaching = true;

	const PROGRESSDATA_PERCENTAGE = 'percentage';
	const PROGRESSDATA_SPEED = 'speed';

	/**
	* @publicget
	* @publicset
	* @var string Set what type of data to display in the queue item during file upload progress updates.  The two options are ‘percentage’ or ‘speed’.
	*/
	protected $progressData = self::PROGRESSDATA_PERCENTAGE;

    /**
    * @publicset
    * @publicget
    * @var string The ID (without the hash) of a DOM element to use as the file queue.  File queue items will be appended directly to this element if defined.  If this option is set to false, a file queue will be generated and the queueID option will be dynamically set.
    */
    protected $queueId = false;

    /**
    * @publicset
    * @publicget
    * @var integer The maximum number of files that can be in the queue at one time.  This does not limit the number of files that can be uploaded.  To limit the number of files that can be uploaded, use uploadLimit.  If the number of files selected to add to the queue exceeds this limit, the onSelectError event is triggered.
    */
    protected $queueSizeLimit = 999;

    /**
    * @publicset
    * @publicget
    * @var boolean Set to false to keep files that have completed uploading in the queue.
    */
    protected $removeCompleted = true;

	/**
	* @publicget
	* @publicset
	* @var int The delay in seconds before a completed upload is removed from the queue.
	*/
    protected $removeTimeout = 3;

	/**
	* @publicget
	* @publicset
	* @var bool If set to true, files that return errors during an upload are requeued and the upload is repeatedly tried.
	*/
    protected $requeueErrors = false;

	/**
	* @publicget
	* @publicset
	* @var int The time in seconds to wait for the server’s response when a file has completed uploading.  After this amount of time, the SWF file will assume success.
	*/
    protected $successTimeout = 30;

    /**
    * @publicset
    * @publicget
    * @var string The path to the server-side upload script (uploadify.php).  This should be a path that is relative to the root is possible to avoid issues, but it will also accept a path that is relative to the current script.
    */
    protected $uploader = null;

    /**
    * @publicset
    * @publicget
    * @var integer The maximum number of files you are allowed to upload.  When this number is reached or exceeded, the onUploadError event is triggered.
    */
    protected $uploadLimit = 999;

    /**
    * @publicset
    * @publicget
    * @var integer The width of the browse button.
    */
    protected $width = 120;

    /**
    * @publicset
    * @publicget
    * @var string The name of the element
    */
    protected $name = '';

    /**
    * @publicset
    * @publicget
    * @var string The id of the element
    */
    protected $id = '';

	/**
	* @publicget
	* @publicset
	* @var string Triggered when a file is removed from the queue (but not if it’s replaced during  a select operation).
	* Arguments
	* •file
	* The file object being cancelled
	*/
    protected $onCancel = null;

	/**
	* @publicget
	* @publicset
	* @var string This event is triggered when the cancel method is called with an ‘*’ as the argument.
	* Arguments
	* •queueItemCount
	* The number of queue items that are being cancelled.
	*/
    protected $onClearQueue = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered when calling the destroy method.
	*/
	protected $onDestroy = null;

	/**
	* @publicset
	* @publicget
	* @var string Triggered when the browse files dialog box is closed.  If this event is added to the overrideEvents option, the default error message will not alert if errors occur when adding files to the queue.
	* Arguments
	* •queueData
	* The queueData object containing information about the queue: •filesSelected
 	* The number of files selected in browse files dialog
	* •filesQueued
 	* The number of files added to the queue (that didn’t return an error)
	* •filesReplaced
 	* The number of files replaced in the queue
	* •filesCancelled
 	* The number of files that were cancelled from being added to the queue (not replaced)
	* •filesErrored
	* The number of files that returned an error
	*/
	protected $onDialogClose = null;

	/**
	* @publicset
	* @publicget
	* @var string Triggered immediately before the browse files dialog is opened, but code placed in this function may not fire until the dialog box is closed.
	*/
	protected $onDialogOpen = null;

	/**
	* @publicset
	* @publicget
	* @var string Triggered when the instance of Uploadify is disabled by calling the disable method
	*/
	protected $onDisable = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered when the Uploadify button is enabled using the disable method.
	*/
	protected $onEnable = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered during initialization if a compatible version of Flash is not detected in the browser.
	*/
	protected $onFallback = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered at the very end of the initialization when Uploadify is first called.
	* Arguments
	* •instance
	* The instance of the uploadify object
	*/
	protected $onInit = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered when all files in the queue have been processed.
	* Arguments
	* •queueData
	* The queueData object containing information about the queue: •uploadsSuccessful
	* The number of uploads that were successfully completed
	* •uploadsErrored
	* The number of uploads that returned an error
	*/
	protected $onQueueComplete = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered for each file that is selected from the browse files dialog and added to the queue.
	* Arguments
	* •file
	* The file object that was selected.
	*/
	protected $onSelect = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered when an error is returned while selecting files.  This event is triggered for each file that returns an error.
	* Arguments
	* •file
 	* The file object that returned the error.
	* •errorCode
	* The error code that was returned.  The following constants can be used when determining the error code: •QUEUE_LIMIT_EXCEEDED – The number of files selected will push the size of the queue passed the limit that was set.
	* •FILE_EXCEEDS_SIZE_LIMIT – The size of the file exceeds the limit that was set.
	* •ZERO_BYTE_FILE – The file has no size.
	* •INVALID_FILETYPE – The file type does not match the file type limitations that were set.
	* •errorMsg
 	* The error message indicating the value of the limit that was exceeded.
	* *You can access a full error message using ‘this.queueData.errorMsg’ if you do not override the default event handler.
	*/
	protected $onSelectError = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered when the Flash object is loaded and ready.
	*/
	protected $onSWFReady = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered once for each file when uploading is completed whether it was successful or returned an error.  If you want to know if the upload was successful or not, it’s better to use the onUploadSuccess event or onUploadError event.
	* Arguments
	* •file
	* The file object that was uploaded or returned an error.
	*/
	protected $onUploadComplete = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered when a file has been uploaded but returns an error.
	* Arguments
	* •file
	* The file object that was uploaded
	* •errorCode
	* The error code that was returned
	* •errorMsg
	* The error message that was returned
	* •errorString
	* The human-readable error message containing all the details of the error
	*/
	protected $onUploadError = null;

	/**
	* @publicget
	* @publicset
	* @var string Triggered each time the progress of a file upload is updated.
	* Arguments
	* •file
	* The file object being uploaded
	* •bytesUploaded
	* The number of bytes of the file that have been uploaded
	* •bytesTotal
	* The total number of bytes of the file
	* •totalBytesUploaded
	* The total number of bytes uploaded in the current upload operation (all files)
	* •totalBytesTotal
	* The total number of bytes to be uploaded (all files)
	*/
	protected $onUploadProgress = null;

	/**
    * @publicget
    * @publicset
    * @var string Triggered immediate before a file is uploaded.
    * Arguments
	* •file
	* The file object that is about to be uploaded
    */
	protected $onUploadStart = null;

	/**
    * @publicget
    * @publicset
    * @var string Triggered for each file that successfully uploads.
    * Arguments
	* •file
	* The file object that was successfully uploaded
	* •data
	* The data that was returned by the server-side script (anything that was echoed by the file)
	* •response
	* The response returned by the server—true on success or false if no response.  If false is returned, after the successTimeout option expires, a response of true is assumed.
    */
	protected $onUploadSuccess = null;

    /**
    * Creates the uploadify element
    * @param string The name of the element
    * @param string The url to where uploads will be posted
    */
    public function __construct($name, $uploader = '')
    {
        $this->name = $name;
        $this->id = $name;

        $this->uploader = $uploader;
    }

    /**
    * Generates the XML child in the given XML root.
    * This function should be implemented by every FormElement.
    * @param \SimpleXMLElement The XML node to add the current element to.
    * @return \SimpleXMLElement The newly created node
    */
    public function generateXML(\SimpleXMLElement $xml)
    {
        $child = $xml->addChild('uploadify');
        $child->name = $this->name;
        $child->id = $this->id;
        $child->auto = ($this->auto ? 'true': 'false');
        $child->autoInit = ($this->autoInit ? 'true': 'false');
        $child->buttonClass = $this->buttonClass;
        $child->buttonCursor = $this->buttonCursor;
        $child->buttonImage = $this->buttonImage;
        $child->buttonText = $this->buttonText;
		$child->checkExisting = ($this->checkExisting ? 'true' : 'false');
		$child->debug = ($this->debug ? 'true' : 'false');
		$child->fileObjName = $this->fileObjName;
		$child->fileSizeLimit = $this->fileSizeLimit;
		$child->fileTypeDesc = $this->fileTypeDesc;
		$child->fileTypeExts = $this->fileTypeExts;
		$child->formData = $this->formData;
		$child->height = $this->height;
		$child->itemTemplate = $this->itemTemplate;
		$child->method = $this->method;
		$child->multi = ($this->multi ? 'true' : 'false');
		$child->overrideEvents = $this->overrideEvents;
		$child->preventCaching = ($this->preventCaching ? 'true' : 'false');
		$child->progressData = $this->progressData;
		$child->queueId = $this->queueId;
		$child->queueSizeLimit = $this->queueSizeLimit;
		$child->removeCompleted = ($this->removeCompleted ? 'true' : 'false');
		$child->removeTimeout = $this->removeTimeout;
		$child->requeueErrors = ($this->requeueErrors ? 'true' : 'false');
		$child->successTimeout = $this->successTimeout;
		$child->uploader = $this->uploader;
		$child->uploadLimit = $this->uploadLimit;
		$child->width = $this->width;

		$child->onCancel = $this->onCancel;
		$child->onClearQueue = $this->onClearQueue;
		$child->onDestroy = $this->onDestroy;
		$child->onDialogClose = $this->onDialogClose;
		$child->onDialogOpen = $this->onDialogOpen;
		$child->onDisable = $this->onDisable;
		$child->onEnable = $this->onEnable;
		$child->onFallback = $this->onFallback;
		$child->onInit = $this->onInit;
		$child->onQueueComplete = $this->onQueueComplete;
		$child->onSelect = $this->onSelect;
		$child->onSelectError = $this->onSelectError;
		$child->onSWFReady = $this->onSWFReady;
		$child->onUploadComplete = $this->onUploadComplete;
		$child->onUploadError = $this->onUploadError;
		$child->onUploadProgress = $this->onUploadProgress;
		$child->onUploadStart = $this->onUploadStart;
		$child->onUploadSuccess = $this->onUploadSuccess;

		return $child;
    }
}