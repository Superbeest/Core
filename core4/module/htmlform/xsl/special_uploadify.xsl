<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form//formelements/uploadify" mode="formelement">
	<script type="text/javascript">
		var uploadifyConfig_<xsl:value-of select="id" /> = {
			'auto'              : <xsl:value-of select="auto" />,
			<xsl:if test="buttonClass != ''">			'buttonClass'		: '<xsl:value-of select="buttonClass" />',</xsl:if>
			<xsl:if test="buttonCursor != ''">			'buttonCursor'		: '<xsl:value-of select="buttonCursor" />',</xsl:if>
			<xsl:if test="buttonImage != ''">			'buttonImage'		: '<xsl:value-of select="buttonImage" />',</xsl:if>
			<xsl:if test="buttonText != ''">			'buttonText'		: '<xsl:value-of select="buttonText" />',</xsl:if>
			'checkExisting'    	: <xsl:value-of select="checkExisting" />,
			'debug'    			: <xsl:value-of select="debug" />,
			<xsl:if test="fileObjNamet != ''">			'fileObjName'		: '<xsl:value-of select="fileObjName" />',</xsl:if>
			<xsl:if test="fileSizeLimit != ''">			'fileSizeLimit'		: '<xsl:value-of select="fileSizeLimit" />',</xsl:if>
			<xsl:if test="fileTypeDesc != ''">			'fileTypeDesc'		: '<xsl:value-of select="fileTypeDesc" />',</xsl:if>
			<xsl:if test="fileTypeExts != ''">			'fileTypeExts'		: '<xsl:value-of select="fileTypeExts" />',</xsl:if>
			<xsl:if test="formData != ''">				'formData'			: <xsl:value-of select="formData" />,</xsl:if>
			<xsl:if test="height != ''">				'height'			: '<xsl:value-of select="height" />',</xsl:if>
			<xsl:if test="itemTemplate != ''">			'itemTemplate'		: '<xsl:value-of select="itemTemplate" />',</xsl:if>
			'method'    		: '<xsl:value-of select="method" />',
			'multi'    			: <xsl:value-of select="multi" />,
			<xsl:if test="overrideEvents != ''">		'overrideEvents'	: '<xsl:value-of select="overrideEvents" />',</xsl:if>
			'preventCaching'    : <xsl:value-of select="preventCaching" />,
			<xsl:if test="progressData != ''">			'progressData'		: '<xsl:value-of select="progressData" />',</xsl:if>
			<xsl:if test="queueId != ''">				'queueID'			: '<xsl:value-of select="queueId" />',</xsl:if>
			<xsl:if test="queueSizeLimit != ''">		'queueSizeLimit'	: '<xsl:value-of select="queueSizeLimit" />',</xsl:if>
			'removeCompleted'   : <xsl:value-of select="removeCompleted" />,
			<xsl:if test="removeTimeout != ''">			'removeTimeout'		: '<xsl:value-of select="removeTimeout" />',</xsl:if>
			'requeueErrors'   	: <xsl:value-of select="requeueErrors" />,
			<xsl:if test="successTimeout != ''">		'successTimeout'	: '<xsl:value-of select="successTimeout" />',</xsl:if>
			'uploader'   		: '<xsl:value-of select="uploader" />',
			<xsl:if test="uploadLimit != ''">			'uploadLimit'		: '<xsl:value-of select="uploadLimit" />',</xsl:if>
			<xsl:if test="width != ''">					'width'				: '<xsl:value-of select="width" />',</xsl:if>

			<xsl:if test="onCancel != ''">				'onCancel'			: function(file) { <xsl:value-of select="onCancel" /> },</xsl:if>
			<xsl:if test="onClearQueue != ''">			'onClearQueue'		: function(queueItemCount) { <xsl:value-of select="onClearQueue" /> },</xsl:if>
			<xsl:if test="onDestroy != ''">				'onDestroy'			: function() { <xsl:value-of select="onDestroy" /> },</xsl:if>
			<xsl:if test="onDialogClose != ''">			'onDialogClose'		: function() { <xsl:value-of select="onDialogClose" /> },</xsl:if>
			<xsl:if test="onDialogOpen != ''">			'onDialogOpen'		: function() { <xsl:value-of select="onDialogOpen" /> },</xsl:if>
			<xsl:if test="onDisable != ''">				'onDisable'			: function() { <xsl:value-of select="onDisable" /> },</xsl:if>
			<xsl:if test="onEnable != ''">				'onEnable'			: function() { <xsl:value-of select="onEnable" /> },</xsl:if>
			<xsl:if test="onFallback != ''">			'onFallback'		: function() { <xsl:value-of select="onFallback" /> },</xsl:if>
			<xsl:if test="onInit != ''">				'onInit'			: function(instance) { <xsl:value-of select="onInit" /> },</xsl:if>
			<xsl:if test="onQueueComplete != ''">		'onQueueComplete'	: function(queueData) { <xsl:value-of select="onQueueComplete" /> },</xsl:if>
			<xsl:if test="onSelect != ''">				'onSelect'			: function(file) { <xsl:value-of select="onSelect" /> },</xsl:if>
			<xsl:if test="onSelectError != ''">			'onSelectError'		: function(file, errorCode, errorMsg) { <xsl:value-of select="onSelectError" /> },</xsl:if>
			<xsl:if test="onSWFReady != ''">			'onSWFReady'		: function() { <xsl:value-of select="onSWFReady" /> },</xsl:if>
			<xsl:if test="onUploadComplete != ''">		'onUploadComplete'	: function(file) { <xsl:value-of select="onUploadComplete" /> },</xsl:if>
			<xsl:if test="onUploadError != ''">			'onUploadError'		: function(file, errorCode, errorMsg, errorString) { <xsl:value-of select="onUploadError" /> },</xsl:if>
			<xsl:if test="onUploadProgress != ''">		'onUploadProgress'	: function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) { <xsl:value-of select="onUploadProgress" /> },</xsl:if>
			<xsl:if test="onUploadStart != ''">			'onUploadStart'		: function(file) { <xsl:value-of select="onUploadStart" /> },</xsl:if>
			<xsl:if test="onUploadSuccess != ''">		'onUploadSuccess'	: function(file, data, response) { <xsl:value-of select="onUploadSuccess" /> },</xsl:if>

			'swf'				: '<xsl:value-of select="$publicRoot" />htmlform/uploadifyswf/swf'
		};
		<xsl:if test="autoInit = 'true'">
			$(function() {
				$('#<xsl:value-of select="id" />').uploadify(uploadifyConfig_<xsl:value-of select="id" />);
			});
		</xsl:if>
	</script>
    <input type="file" id="{id}" name="{name}" />
</xsl:template>

</xsl:stylesheet>