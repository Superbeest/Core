<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form//formelements/select" mode="formelement">
    <select>
        <xsl:if test="multiple != ''"><xsl:attribute name="multiple"><xsl:value-of select="multiple" /></xsl:attribute></xsl:if>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="name != ''"><xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute></xsl:if>
        <xsl:if test="(size != '') and (size > 0)"><xsl:attribute name="size"><xsl:value-of select="size" /></xsl:attribute></xsl:if>

        <xsl:if test="(tabindex != '') and (tabindex > 0)"><xsl:attribute name="tabindex"><xsl:value-of select="tabindex" /></xsl:attribute></xsl:if>

        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>

        <xsl:if test="required != ''"><xsl:attribute name="required">required</xsl:attribute></xsl:if>

        <xsl:if test="events/onkeydown != ''"><xsl:attribute name="onkeydown"><xsl:value-of select="events/onkeydown" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onkeypress != ''"><xsl:attribute name="onkeypress"><xsl:value-of select="events/onkeypress" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onkeyup != ''"><xsl:attribute name="onkeyup"><xsl:value-of select="events/onkeyup" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:if test="events/onclick != ''"><xsl:attribute name="onclick"><xsl:value-of select="events/onclick" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/ondblclick != ''"><xsl:attribute name="ondblclick"><xsl:value-of select="events/ondblclick" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmousedown != ''"><xsl:attribute name="onmousedown"><xsl:value-of select="events/onmousedown" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmousemove != ''"><xsl:attribute name="onmousemove"><xsl:value-of select="events/onmousemove" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseout != ''"><xsl:attribute name="onmouseout"><xsl:value-of select="events/onmouseout" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseover != ''"><xsl:attribute name="onmouseover"><xsl:value-of select="events/onmouseover" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseup != ''"><xsl:attribute name="onmouseup"><xsl:value-of select="events/onmouseup" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:if test="events/onblur != ''"><xsl:attribute name="onblur"><xsl:value-of select="events/onblur" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onfocus != ''"><xsl:attribute name="onfocus"><xsl:value-of select="events/onfocus" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />

        <xsl:apply-templates select="options" />
    </select>
</xsl:template>

<xsl:template match="//forms/form//formelements/select//options/option">
    <option>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="selected != ''"><xsl:attribute name="selected"><xsl:value-of select="selected" /></xsl:attribute></xsl:if>
        <xsl:if test="label != ''"><xsl:attribute name="label"><xsl:value-of select="label" /></xsl:attribute></xsl:if>
        <xsl:if test="value != ''"><xsl:attribute name="value"><xsl:value-of select="value" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>

        <xsl:if test="events/onkeydown != ''"><xsl:attribute name="onkeydown"><xsl:value-of select="events/onkeydown" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onkeypress != ''"><xsl:attribute name="onkeypress"><xsl:value-of select="events/onkeypress" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onkeyup != ''"><xsl:attribute name="onkeyup"><xsl:value-of select="events/onkeyup" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:if test="events/onclick != ''"><xsl:attribute name="onclick"><xsl:value-of select="events/onclick" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/ondblclick != ''"><xsl:attribute name="ondblclick"><xsl:value-of select="events/ondblclick" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmousedown != ''"><xsl:attribute name="onmousedown"><xsl:value-of select="events/onmousedown" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmousemove != ''"><xsl:attribute name="onmousemove"><xsl:value-of select="events/onmousemove" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseout != ''"><xsl:attribute name="onmouseout"><xsl:value-of select="events/onmouseout" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseover != ''"><xsl:attribute name="onmouseover"><xsl:value-of select="events/onmouseover" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseup != ''"><xsl:attribute name="onmouseup"><xsl:value-of select="events/onmouseup" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />

        <xsl:value-of select="text" />
    </option>
</xsl:template>

<xsl:template match="//forms/form//formelements/select/options/optgroup">
    <optgroup>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="label != ''"><xsl:attribute name="label"><xsl:value-of select="label" /></xsl:attribute></xsl:if>

        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>

        <xsl:if test="events/onkeydown != ''"><xsl:attribute name="onkeydown"><xsl:value-of select="events/onkeydown" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onkeypress != ''"><xsl:attribute name="onkeypress"><xsl:value-of select="events/onkeypress" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onkeyup != ''"><xsl:attribute name="onkeyup"><xsl:value-of select="events/onkeyup" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:if test="events/onclick != ''"><xsl:attribute name="onclick"><xsl:value-of select="events/onclick" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/ondblclick != ''"><xsl:attribute name="ondblclick"><xsl:value-of select="events/ondblclick" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmousedown != ''"><xsl:attribute name="onmousedown"><xsl:value-of select="events/onmousedown" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmousemove != ''"><xsl:attribute name="onmousemove"><xsl:value-of select="events/onmousemove" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseout != ''"><xsl:attribute name="onmouseout"><xsl:value-of select="events/onmouseout" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseover != ''"><xsl:attribute name="onmouseover"><xsl:value-of select="events/onmouseover" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onmouseup != ''"><xsl:attribute name="onmouseup"><xsl:value-of select="events/onmouseup" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />

        <xsl:apply-templates select="options" />
    </optgroup>
</xsl:template>


</xsl:stylesheet>