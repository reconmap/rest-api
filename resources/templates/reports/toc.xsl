<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:outline="http://wkhtmltopdf.org/outline"
                xmlns="http://www.w3.org/1999/xhtml">
    <xsl:output doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitiona
l.dtd"
                indent="yes"/>
    <xsl:template match="outline:outline">
        <html>
            <head>
                <title>Table of Contents</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style>
                    body {
                        margin-top: 25.4mm;
                        margin-bottom: 25.4mm;
                        margin-left: 31.7mm;
                        margin-right: 31.7mm;
                        font-size: 9pt;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
                    }
                      h2{
                        margin-top: 1cm;
                        border-bottom: 1px solid #748094;
                        padding-bottom: .25cm;
                    }
                    span {float: right;}
                    li {
                        list-style: none;
                        display: block;
                        padding: 8px;
                        margin-bottom: 4px;
                        border-bottom: 1px dotted #f3f3f3;
                        }
                    ul {
                        font-size: 9pt;
                    }
                    ul {padding-left: 0em;}
                    ul ul {padding-left: 1em;}
                    a {text-decoration:none; color: black;}
                </style>
            </head>
            <body>
                <h2>Table of Contents</h2>
                <ul>
                    <xsl:apply-templates select="outline:item/outline:item"/>
                </ul>
            </body>
        </html>
    </xsl:template>
    <xsl:template match="outline:item[count(ancestor::outline:item)&lt;=1]">
        <li>
            <xsl:if test="@title!=''">
                <a>
                    <xsl:if test="@link">
                        <xsl:attribute name="href">
                            <xsl:value-of select="@link"/>
                        </xsl:attribute>
                    </xsl:if>
                    <xsl:if test="@backLink">
                        <xsl:attribute name="name">
                            <xsl:value-of select="@backLink"/>
                        </xsl:attribute>
                    </xsl:if>
                    <xsl:value-of select="@title"/>
                </a>
                <span>
                    <xsl:value-of select="@page"/>
                </span>
            </xsl:if>
            <ul>
                <xsl:comment>added to prevent self-closing tags in QtXmlPatterns</xsl:comment>
                <xsl:apply-templates select="outline:item"/>
            </ul>
        </li>
    </xsl:template>
</xsl:stylesheet>
