<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="//forms/form//formelements/input[@type[.='text']|@type[.='password']|@type[.='color']|@type[.='date']|@type[.='datetime']|@type[.='datetime-local']|@type[.='email']|@type[.='month']|@type[.='number']|@type[.='range']|@type[.='search']|@type[.='tel']|@type[.='time']|@type[.='url']|@type[.='week']]" mode="formelement">
    <input>
        <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
        <xsl:if test="(maxlength != '') and (maxlength > 0)"><xsl:attribute name="maxlength"><xsl:value-of select="maxlength" /></xsl:attribute></xsl:if>
        <xsl:if test="(tabindex != '') and (tabindex > 0)"><xsl:attribute name="tabindex"><xsl:value-of select="tabindex" /></xsl:attribute></xsl:if>
        <xsl:if test="(size != '') and (size > 0)"><xsl:attribute name="size"><xsl:value-of select="size" /></xsl:attribute></xsl:if>
        <xsl:if test="readonly != ''"><xsl:attribute name="readonly"><xsl:value-of select="readonly" /></xsl:attribute></xsl:if>
        <xsl:if test="value != ''"><xsl:attribute name="value"><xsl:value-of select="value" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="name != ''"><xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute></xsl:if>
        <xsl:if test="placeholder != ''"><xsl:attribute name="placeholder"><xsl:value-of select="placeholder" /></xsl:attribute></xsl:if>
        <xsl:if test="autocomplete != ''"><xsl:attribute name="autocomplete">off</xsl:attribute></xsl:if>
        <xsl:if test="autofocus != ''"><xsl:attribute name="autofocus">autofocus</xsl:attribute></xsl:if>

        <xsl:if test="form != ''"><xsl:attribute name="form"><xsl:value-of select="form" /></xsl:attribute></xsl:if>

        <xsl:if test="multiple != ''"><xsl:attribute name="multiple"><xsl:value-of select="multiple" /></xsl:attribute></xsl:if>

        <xsl:if test="pattern != ''"><xsl:attribute name="pattern"><xsl:value-of select="pattern" /></xsl:attribute></xsl:if>
        <xsl:if test="required != ''"><xsl:attribute name="required">required</xsl:attribute></xsl:if>

        <xsl:if test="max != ''"><xsl:attribute name="max"><xsl:value-of select="max" /></xsl:attribute></xsl:if>
        <xsl:if test="min != ''"><xsl:attribute name="min"><xsl:value-of select="min" /></xsl:attribute></xsl:if>
        <xsl:if test="step != ''"><xsl:attribute name="step"><xsl:value-of select="step" /></xsl:attribute></xsl:if>

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
        <xsl:if test="events/onchange != ''"><xsl:attribute name="onchange"><xsl:value-of select="events/onchange" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onfocus != ''"><xsl:attribute name="onfocus"><xsl:value-of select="events/onfocus" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onselect != ''"><xsl:attribute name="onselect"><xsl:value-of select="events/onselect" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />
    </input>
</xsl:template>

<xsl:template match="//forms/form//formelements/input[@type[.='checkbox']|@type[.='radio']]" mode="formelement">
    <input>
        <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
        <xsl:if test="(tabindex != '') and (tabindex > 0)"><xsl:attribute name="tabindex"><xsl:value-of select="tabindex" /></xsl:attribute></xsl:if>
        <xsl:if test="(size != '') and (size > 0)"><xsl:attribute name="size"><xsl:value-of select="size" /></xsl:attribute></xsl:if>
        <xsl:if test="value != ''"><xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute></xsl:if>
        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="checked != ''"><xsl:attribute name="checked"><xsl:value-of select="checked" /></xsl:attribute></xsl:if>
        <xsl:if test="name != ''"><xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute></xsl:if>
        <xsl:if test="autofocus != ''"><xsl:attribute name="autofocus">autofocus</xsl:attribute></xsl:if>

        <xsl:if test="form != ''"><xsl:attribute name="form"><xsl:value-of select="form" /></xsl:attribute></xsl:if>

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
        <xsl:if test="events/onchange != ''"><xsl:attribute name="onchange"><xsl:value-of select="events/onchange" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onfocus != ''"><xsl:attribute name="onfocus"><xsl:value-of select="events/onfocus" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onselect != ''"><xsl:attribute name="onselect"><xsl:value-of select="events/onselect" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />
    </input>
</xsl:template>

<xsl:template match="//forms/form//formelements/input[@type[.='image']]" mode="formelement">
    <input>
        <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
        <xsl:if test="(tabindex != '') and (tabindex > 0)"><xsl:attribute name="tabindex"><xsl:value-of select="tabindex" /></xsl:attribute></xsl:if>
        <xsl:if test="(size != '') and (size > 0)"><xsl:attribute name="size"><xsl:value-of select="size" /></xsl:attribute></xsl:if>
        <xsl:if test="value != ''"><xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute></xsl:if>
        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="name != ''"><xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute></xsl:if>
        <xsl:if test="alt != ''"><xsl:attribute name="alt"><xsl:value-of select="alt" /></xsl:attribute></xsl:if>
        <xsl:if test="src != ''"><xsl:attribute name="src"><xsl:value-of select="src" /></xsl:attribute></xsl:if>
        <xsl:if test="autofocus != ''"><xsl:attribute name="autofocus">autofocus</xsl:attribute></xsl:if>

        <xsl:if test="form != ''"><xsl:attribute name="form"><xsl:value-of select="form" /></xsl:attribute></xsl:if>
        <xsl:if test="formaction != ''"><xsl:attribute name="formaction"><xsl:value-of select="formaction" /></xsl:attribute></xsl:if>
        <xsl:if test="formenctype != ''"><xsl:attribute name="formenctype"><xsl:value-of select="formenctype" /></xsl:attribute></xsl:if>
        <xsl:if test="formtarget != ''"><xsl:attribute name="formtarget"><xsl:value-of select="formtarget" /></xsl:attribute></xsl:if>
        <xsl:if test="formmethod != ''"><xsl:attribute name="formmethod"><xsl:value-of select="formmethod" /></xsl:attribute></xsl:if>

        <xsl:if test="width != ''"><xsl:attribute name="width"><xsl:value-of select="width" /></xsl:attribute></xsl:if>
        <xsl:if test="height != ''"><xsl:attribute name="height"><xsl:value-of select="height" /></xsl:attribute></xsl:if>

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
        <xsl:if test="events/onchange != ''"><xsl:attribute name="onchange"><xsl:value-of select="events/onchange" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onfocus != ''"><xsl:attribute name="onfocus"><xsl:value-of select="events/onfocus" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onselect != ''"><xsl:attribute name="onselect"><xsl:value-of select="events/onselect" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />
    </input>
</xsl:template>

<xsl:template match="//forms/form//formelements/input[@type[.='file']]" mode="formelement">
    <input>
        <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
        <xsl:if test="(tabindex != '') and (tabindex > 0)"><xsl:attribute name="tabindex"><xsl:value-of select="tabindex" /></xsl:attribute></xsl:if>
        <xsl:if test="(size != '') and (size > 0)"><xsl:attribute name="size"><xsl:value-of select="size" /></xsl:attribute></xsl:if>
        <xsl:if test="value != ''"><xsl:attribute name="value"><xsl:value-of select="value" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="name != ''"><xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute></xsl:if>
        <xsl:if test="accept != ''"><xsl:attribute name="accept"><xsl:value-of select="accept" /></xsl:attribute></xsl:if>
        <xsl:if test="autofocus != ''"><xsl:attribute name="autofocus">autofocus</xsl:attribute></xsl:if>

        <xsl:if test="form != ''"><xsl:attribute name="form"><xsl:value-of select="form" /></xsl:attribute></xsl:if>

        <xsl:if test="multiple != ''"><xsl:attribute name="multiple"><xsl:value-of select="multiple" /></xsl:attribute></xsl:if>
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
        <xsl:if test="events/onchange != ''"><xsl:attribute name="onchange"><xsl:value-of select="events/onchange" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onfocus != ''"><xsl:attribute name="onfocus"><xsl:value-of select="events/onfocus" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onselect != ''"><xsl:attribute name="onselect"><xsl:value-of select="events/onselect" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />
    </input>
</xsl:template>

<xsl:template match="//forms/form//formelements/input[@type[.='button']|@type[.='reset']|@type[.='submit']|@type[.='hidden']]" mode="formelement">
    <input>
        <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
        <xsl:if test="(tabindex != '') and (tabindex > 0)"><xsl:attribute name="tabindex"><xsl:value-of select="tabindex" /></xsl:attribute></xsl:if>
        <xsl:if test="(size != '') and (size > 0)"><xsl:attribute name="size"><xsl:value-of select="size" /></xsl:attribute></xsl:if>
        <xsl:if test="value != ''"><xsl:attribute name="value"><xsl:value-of select="value" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="class != ''"><xsl:attribute name="class"><xsl:value-of select="class" /></xsl:attribute></xsl:if>
        <xsl:if test="dir != ''"><xsl:attribute name="dir"><xsl:value-of select="dir" /></xsl:attribute></xsl:if>
        <xsl:if test="id != ''"><xsl:attribute name="id"><xsl:value-of select="id" /></xsl:attribute></xsl:if>
        <xsl:if test="lang != ''"><xsl:attribute name="lang"><xsl:value-of select="lang" /></xsl:attribute></xsl:if>
        <xsl:if test="style != ''"><xsl:attribute name="style"><xsl:value-of select="style" /></xsl:attribute></xsl:if>
        <xsl:if test="title != ''"><xsl:attribute name="title"><xsl:value-of select="title" /></xsl:attribute></xsl:if>
        <xsl:if test="disabled != ''"><xsl:attribute name="disabled"><xsl:value-of select="disabled" /></xsl:attribute></xsl:if>
        <xsl:if test="name != ''"><xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute></xsl:if>
        <xsl:if test="autofocus != ''"><xsl:attribute name="autofocus">autofocus</xsl:attribute></xsl:if>

        <xsl:if test="form != ''"><xsl:attribute name="form"><xsl:value-of select="form" /></xsl:attribute></xsl:if>
        <xsl:if test="formaction != ''"><xsl:attribute name="formaction"><xsl:value-of select="formaction" /></xsl:attribute></xsl:if>
        <xsl:if test="formenctype != ''"><xsl:attribute name="formenctype"><xsl:value-of select="formenctype" /></xsl:attribute></xsl:if>
        <xsl:if test="formtarget != ''"><xsl:attribute name="formtarget"><xsl:value-of select="formtarget" /></xsl:attribute></xsl:if>
        <xsl:if test="formmethod != ''"><xsl:attribute name="formmethod"><xsl:value-of select="formmethod" /></xsl:attribute></xsl:if>
        <xsl:if test="formnovalidate != ''"><xsl:attribute name="formnovalidate">formnovalidate</xsl:attribute></xsl:if>

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
        <xsl:if test="events/onchange != ''"><xsl:attribute name="onchange"><xsl:value-of select="events/onchange" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onfocus != ''"><xsl:attribute name="onfocus"><xsl:value-of select="events/onfocus" disable-output-escaping="yes" /></xsl:attribute></xsl:if>
        <xsl:if test="events/onselect != ''"><xsl:attribute name="onselect"><xsl:value-of select="events/onselect" disable-output-escaping="yes" /></xsl:attribute></xsl:if>

        <xsl:apply-templates select="datas" />
    </input>
</xsl:template>

</xsl:stylesheet>