<?xml version="1.0" encoding="utf-8" ?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:date="http://exslt.org/dates-and-times"
				extension-element-prefixes="date"
				version="1.0">

<xsl:output
	 method="html"
	 doctype-system="about:legacy-compat"
	 encoding="UTF-8"
	 indent="yes"
	 omit-xml-declaration="yes"
	 cdata-section-elements="style" />

<xsl:param name="title">Some random title here</xsl:param>
<xsl:param name="keywords"><xsl:value-of select="$title" /></xsl:param>
<xsl:param name="description"><xsl:value-of select="$title" /></xsl:param>
<xsl:param name="publicRoot">.</xsl:param>
<xsl:param name="queryTime">0</xsl:param>
<xsl:param name="queryAmount">0</xsl:param>
<xsl:param name="executionTime">0</xsl:param>
<xsl:param name="sessionHandler">unknown</xsl:param>
<xsl:param name="browser">Unknown</xsl:param>
<xsl:param name="browserVersion">1</xsl:param>
<xsl:param name="debugMode"></xsl:param>
<xsl:param name="revisitAfter">1 day</xsl:param>
<xsl:param name="language"></xsl:param>
<xsl:param name="company">SuperHolder</xsl:param>

<xsl:param name="year" select="date:year()" />

<xsl:template match="//custommetas/meta">
	<meta>
		<xsl:if test="http-equiv != ''">
			<xsl:attribute name="http-equiv"><xsl:value-of select="http-equiv" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="property != ''">
			<xsl:attribute name="property"><xsl:value-of select="property" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="name != ''">
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="content != ''">
			<xsl:attribute name="content"><xsl:value-of select="content" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="scheme != ''">
			<xsl:attribute name="scheme"><xsl:value-of select="scheme" /></xsl:attribute>
		</xsl:if>
	</meta>
</xsl:template>

<xsl:template match="//customlinks/link">
	<link>
		<xsl:if test="rel != ''">
			<xsl:attribute name="rel"><xsl:value-of select="rel" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="type != ''">
			<xsl:attribute name="type"><xsl:value-of select="type" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="href != ''">
			<xsl:attribute name="href"><xsl:value-of select="href" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="media != ''">
			<xsl:attribute name="media"><xsl:value-of select="media" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="hreflang != ''">
			<xsl:attribute name="hreflang"><xsl:value-of select="hreflang" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="charset != ''">
			<xsl:attribute name="charset"><xsl:value-of select="charset" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="rev != ''">
			<xsl:attribute name="rev"><xsl:value-of select="rev" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="sizes != ''">
			<xsl:attribute name="sizes"><xsl:value-of select="sizes" /></xsl:attribute>
		</xsl:if>
		<xsl:if test="target != ''">
			<xsl:attribute name="target"><xsl:value-of select="target" /></xsl:attribute>
		</xsl:if>
	</link>
</xsl:template>

<xsl:template match="/document/customheadblocks/customheadblock">
	<xsl:value-of select="." disable-output-escaping="yes" />
</xsl:template>

<xsl:template match="/">
<html>
  <xsl:attribute name="lang"><xsl:value-of select="$language" /></xsl:attribute>
  <head>
  	<meta charset="utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description">
		<xsl:attribute name="content"><xsl:choose><xsl:when test="/document/description"><xsl:value-of select="/document/description" disable-output-escaping="yes" /></xsl:when><xsl:otherwise><xsl:value-of select="$description" disable-output-escaping="yes" /></xsl:otherwise></xsl:choose></xsl:attribute>
	</meta>
	<meta name="keywords">
		<xsl:attribute name="content"><xsl:choose><xsl:when test="/document/keywords"><xsl:value-of select="/document/keywords" disable-output-escaping="yes" /></xsl:when><xsl:otherwise><xsl:value-of select="$keywords" disable-output-escaping="yes" /></xsl:otherwise></xsl:choose></xsl:attribute>
	</meta>
	<meta name="author">
		<xsl:attribute name="content">copyright(c) <xsl:value-of select="$year" /> - <xsl:value-of select="$company" /></xsl:attribute>
	</meta>
	<meta name="robots" content="all" />
	<meta name="revisit-after">
		<xsl:attribute name="content"><xsl:value-of select="$revisitAfter" /></xsl:attribute>
	</meta>
	<xsl:apply-templates select="//customlinks" />
	<xsl:apply-templates select="//custommetas" />

	<link rel="shortcut icon" type="image/x-icon">
		<xsl:attribute name="href"><xsl:value-of select="$publicRoot" />favicon.ico</xsl:attribute>
	</link>

	<title><xsl:choose><xsl:when test="/document/title"><xsl:value-of select="/document/title" disable-output-escaping="yes" /></xsl:when><xsl:otherwise><xsl:value-of select="$title" disable-output-escaping="yes" /></xsl:otherwise></xsl:choose></title>
	<base>
		<xsl:attribute name="href"><xsl:value-of select="$publicRoot" /></xsl:attribute>
	</base>
	<script>
		var rootURL = '<xsl:value-of select="$publicRoot" />';
	</script>

	<xsl:apply-templates select="//cssfiles" />
	<xsl:apply-templates select="//jsfiles" />

	<xsl:apply-templates select="//customheadblocks" />

	<xsl:if test="//gacode != ''">
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', '<xsl:value-of select="//gacode" />', 'auto');
		  ga('send', 'pageview');

		</script>
	</xsl:if>
</head>
<body>
	<xsl:call-template name="siteframe" />
</body>
</html>

</xsl:template>

<xsl:template match="//cssfiles/cssfile">
	<link type="text/css">
		<xsl:attribute name="href"><xsl:value-of select="name" /><xsl:if test="filesize">?s=<xsl:value-of select="filesize" /></xsl:if></xsl:attribute>
		<xsl:attribute name="rel"><xsl:value-of select="rel" /></xsl:attribute>
		<xsl:attribute name="media"><xsl:value-of select="media" /></xsl:attribute>
	</link>
</xsl:template>

<xsl:template match="//jsfiles/jsfile">
	<script>
		<xsl:attribute name="src"><xsl:value-of select="name" /><xsl:if test="filesize">?s=<xsl:value-of select="filesize" /></xsl:if></xsl:attribute>
	</script>
</xsl:template>

<xsl:template match="//breadcrumbs">
	<ul>
		<xsl:apply-templates select="crumb" />
	</ul>
</xsl:template>

<xsl:template match="//breadcrumbs/crumb">
	<li>
		<a>
			<xsl:attribute name="href"><xsl:value-of select="link" /></xsl:attribute>
			<xsl:value-of select="name" disable-output-escaping="yes" />
		</a>
	</li>
</xsl:template>

</xsl:stylesheet>
