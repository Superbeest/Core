<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form//formelements/captcha" mode="formelement">
    <div class="captcha">
        <img alt="Security code">
            <xsl:attribute name="onclick" disable-output-escaping="yes" >var tmp = new Date(); var str = '&amp;' + tmp.getTime(); this.src = "<xsl:value-of select="source" />" + str; return true;</xsl:attribute>
            <xsl:attribute name="src"><xsl:value-of select="source" /></xsl:attribute>

            <xsl:apply-templates select="datas" />
        </img>
    </div>
</xsl:template>

</xsl:stylesheet>