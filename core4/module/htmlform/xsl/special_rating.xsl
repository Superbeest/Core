<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form//formelements/rating" mode="formelement">
    <xsl:variable name="elemName" select="name" />
    <xsl:variable name="starSplit" select="starsplit" />
    <xsl:choose>
        <xsl:when test="disabled != ''">
            <div class="star-full">
                <xsl:attribute name="style">background-position: 0px -<xsl:value-of select="starwidth" />px; width: <xsl:value-of select="selectedwidth" />px;</xsl:attribute>
            </div>
            <div class="star-full">
                <xsl:attribute name="style">background-position: -<xsl:value-of select="unselectedoffset" />px 0px; width: <xsl:value-of select="unselectedwidth" />px;</xsl:attribute>
            </div>
        </xsl:when>

        <xsl:otherwise>
            <div>
            <xsl:for-each select="elements/elem">
                <input type="radio">
                    <xsl:attribute name="name"><xsl:value-of select="$elemName" /></xsl:attribute>
                    <xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute>
                    <xsl:attribute name="class">star {split:<xsl:value-of select="$starSplit" />}</xsl:attribute>

                    <xsl:apply-templates select="datas" />

                    <xsl:if test="@checked='checked'">
                        <xsl:attribute name="checked">checked</xsl:attribute>
                    </xsl:if>
                </input>
            </xsl:for-each>
            </div>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>