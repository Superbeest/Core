<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form//datas/data">
	<xsl:attribute name="data-{@key}"><xsl:value-of select="@value" /></xsl:attribute>
</xsl:template>

<!-- Default elements -->
<xsl:include href="element_input.xsl" />
<xsl:include href="element_label.xsl" />
<xsl:include href="element_elementcontainer.xsl" />
<xsl:include href="element_fieldset.xsl" />
<xsl:include href="element_select.xsl" />
<xsl:include href="element_legend.xsl" />
<xsl:include href="element_textarea.xsl" />
<xsl:include href="element_button.xsl" />
<xsl:include href="element_form.xsl" />

<!-- Special elements -->
<xsl:include href="special_rating.xsl" />
<xsl:include href="special_captcha.xsl" />
<xsl:include href="special_uploadify.xsl" />

</xsl:stylesheet>