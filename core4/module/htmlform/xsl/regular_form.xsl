<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form/formelements">
    <xsl:apply-templates select="./*" />
</xsl:template>

<xsl:template match="//forms/form//formelements/*">
    <div class="clear">
        <xsl:apply-templates select="." mode="formelement" />
    </div>
</xsl:template>

<xsl:template match="//forms/form//label/formelements/*">
    <xsl:apply-templates select="." mode="formelement" />
</xsl:template>

<xsl:template match="//forms/form//fieldset/formelements/*">
    <div class="clear">
        <xsl:apply-templates select="." mode="formelement" />
    </div>
</xsl:template>

<xsl:template match="//forms/form//fieldset/formelements/*[@type[.='submit']|@type[.='reset']|@type[.='button']]">
    <xsl:apply-templates select="." mode="formelement" />
</xsl:template>

</xsl:stylesheet>