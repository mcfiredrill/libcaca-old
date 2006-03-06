/*
 *  libcucul      Unicode canvas library
 *  Copyright (c) 2002-2006 Sam Hocevar <sam@zoy.org>
 *                All Rights Reserved
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the Do What The Fuck You Want To
 *  Public License, Version 2, as published by Sam Hocevar. See
 *  http://sam.zoy.org/wtfpl/COPYING for more details.
 */

/** \file char.c
 *  \version \$Id$
 *  \author Sam Hocevar <sam@zoy.org>
 *  \brief Character drawing
 *
 *  This file contains character and string drawing functions.
 */

#include "config.h"

#if defined(HAVE_INTTYPES_H) || defined(_DOXYGEN_SKIP_ME)
#   include <inttypes.h>
#else
typedef unsigned char uint8_t;
#endif

#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#include "cucul.h"
#include "cucul_internals.h"

/* HTML */

/** \brief Generate HTML representation of current image.
 *
 *  This function generates and returns the HTML representation of
 *  the current image.
 */
char* cucul_get_html(cucul_t *qq)
{
    static int const palette[] =
    {
        0x000, 0x008, 0x080, 0x088, 0x800, 0x808, 0x880, 0x888,
        0x444, 0x44f, 0x4f4, 0x4ff, 0xf44, 0xf4f, 0xff4, 0xfff,
    };
    char *buffer, *cur;
    unsigned int x, y, len;

    /* 13000 -> css palette
     * 40 -> max size used for a pixel (plus 10, never know)*/
    /* FIXME: Check this value */
    buffer = malloc((13000 + ((qq->width*qq->height) * 40)) * sizeof(char));
    cur = buffer;

    /* HTML header */
    cur += sprintf(cur, "<html>\n<head>\n<title>Generated by libcaca %s</title>\n", VERSION);

    /* CSS */
    cur += sprintf(cur, "<style>\n");
    cur += sprintf(cur, ".caca { font-family: monospace, fixed; font-weight: bold; }");
    for(x = 0; x < 0x100; x++)
    {
        cur += sprintf(cur, ".b%02x { color:#%03x; background-color:#%03x; }\n",
                       x, palette[x & 0xf ], palette[x >> 4]);
    }
    cur += sprintf(cur, "</style>\n</head>\n<body>\n");

    cur += sprintf(cur, "<div cellpadding='0' cellspacing='0' style='%s'>\n",
                        "font-family: monospace, fixed; font-weight: bold;");

    for(y = 0; y < qq->height; y++)
    {
        uint8_t *lineattr = qq->attr + y * qq->width;
        uint8_t *linechar = qq->chars + y * qq->width;

        for(x = 0; x < qq->width; x += len)
        {
            cur += sprintf(cur, "<span class='b%02x'>", lineattr[x]);

            for(len = 0;
                x + len < qq->width && lineattr[x + len] == lineattr[x];
                len++)
            {
                if(linechar[x + len] == ' ')
                    cur += sprintf(cur, "&nbsp;");
                else
                    cur += sprintf(cur, "%c", linechar[x + len]);
            }
            cur += sprintf(cur, "</span>");
        }
        /* New line */
        cur += sprintf(cur, "<br />\n");
    }

    cur += sprintf(cur, "</div></body></html>\n");

    /* Crop to really used size */
    buffer = realloc(buffer, (strlen(buffer) + 1) * sizeof(char));

    return buffer;
}

/** \brief Generate HTML3 representation of current image.
 *
 *  This function generates and returns the HTML3 representation of
 *  the current image. It is way bigger than cucul_get_html(), but
 *  permits viewing in old browsers (or limited ones such as links)
 *  Won't work under gecko (mozilla rendering engine) unless you set
 *  a correct header.
 */
char* cucul_get_html3(cucul_t *qq)
{
    static int const palette[] =
    {
        0x000000, 0x000088, 0x008800, 0x008888,
        0x880000, 0x880088, 0x888800, 0x888888,
        0x444444, 0x4444ff, 0x44ff44, 0x44ffff,
        0xff4444, 0xff44ff, 0xffff44, 0xffffff,
    };
    char *buffer, *cur;
    unsigned int x, y, len;

    /* 13000 -> css palette
     * 40 -> max size used for a pixel (plus 10, never know) */
    buffer = malloc((13000 + ((qq->width*qq->height)*40))*sizeof(char));
    cur = buffer;

    /* Table */
    cur += sprintf(cur, "<table cols='%d' cellpadding='0' cellspacing='0'>\n",
                        qq->height);

    for(y = 0; y < qq->height; y++)
    {
        uint8_t *lineattr = qq->attr + y * qq->width;
        uint8_t *linechar = qq->chars + y * qq->width;

        cur += sprintf(cur, "<tr>");

        for(x = 0; x < qq->width; x += len)
        {
            unsigned int i;

            /* Use colspan option to factorize cells with same attributes
             * (see below) */
            len = 1;
            while(x + len < qq->width && lineattr[x + len] == lineattr[x])
                len++;

            cur += sprintf(cur, "<td bgcolor=#%06x", palette[lineattr[x] >> 4]);

            if(len > 1)
                cur += sprintf(cur, " colspan=%d", len);

            cur += sprintf(cur, "><font color=#%06x>",
                                palette[lineattr[x] & 0x0f]);

            for(i = 0; i < len; i++)
            {
                if(linechar[x + i] == ' ')
                    cur += sprintf(cur, "&nbsp;");
                else
                    cur += sprintf(cur, "%c", linechar[x + i]);
            }

            cur += sprintf(cur, "</font></td>");
        }
        cur += sprintf(cur, "</tr>\n");
    }

    /* Footer */
    cur += sprintf(cur, "</table>\n");

    /* Crop to really used size */
    buffer = realloc(buffer, (strlen(buffer) + 1) * sizeof(char));

    return buffer;
}

/** \brief Generate IRC representation of current image.
 *
 *  This function generates and returns an IRC representation of
 *  the current image.
 */
char* cucul_get_irc(cucul_t *qq)
{
    static int const palette[] =
    {
        1, 2, 3, 10, 5, 6, 7, 15, /* Dark */
        14, 12, 9, 11, 4, 13, 8, 0, /* Light */
    };

    char *buffer, *cur;
    unsigned int x, y;

    /* 11 bytes assumed for max length per pixel. Worst case scenario:
     * ^Cxx,yy   6 bytes
     * ^B^B      2 bytes
     * c         1 byte
     * \r\n      2 bytes
     * In real life, the average bytes per pixel value will be around 5.
     */
    buffer = malloc((2 + (qq->width * qq->height * 11)) * sizeof(char));
    cur = buffer;

    *cur++ = '\x0f';

    for(y = 0; y < qq->height; y++)
    {
        uint8_t *lineattr = qq->attr + y * qq->width;
        uint8_t *linechar = qq->chars + y * qq->width;

        uint8_t prevfg = -1;
        uint8_t prevbg = -1;

        for(x = 0; x < qq->width; x++)
        {
            uint8_t fg = palette[lineattr[x] & 0x0f];
            uint8_t bg = palette[lineattr[x] >> 4];
            uint8_t c = linechar[x];

            if(bg == prevbg)
            {
                if(fg == prevfg)
                    ; /* Same fg/bg, do nothing */
                else if(c == ' ')
                    fg = prevfg; /* Hackety hack */
                else
                {
                    cur += sprintf(cur, "\x03%d", fg);
                    if(c >= '0' && c <= '9')
                        cur += sprintf(cur, "\x02\x02");
                }
            }
            else
            {
                if(fg == prevfg)
                    cur += sprintf(cur, "\x03,%d", bg);
                else
                    cur += sprintf(cur, "\x03%d,%d", fg, bg);

                if(c >= '0' && c <= '9')
                    cur += sprintf(cur, "\x02\x02");
            }
            *cur++ = c;
            prevfg = fg;
            prevbg = bg;
        }
        *cur++ = '\r';
        *cur++ = '\n';
    }

    *cur++ = '\x0f';

    /* Crop to really used size */
    buffer = realloc(buffer, (strlen(buffer) + 1) * sizeof(char));

    return buffer;
}

/** \brief Generate ANSI representation of current image.
 *
 *  This function generates and returns an ANSI representation of
 *  the current image.
 *  \param trailing if 0, raw ANSI will be generated. Otherwise, you'll be
 *                  able to cut/paste the result to a function like printf
 *  \return buffer containing generated ANSI codes as a big string
 */
char * cucul_get_ansi(cucul_t *qq, int trailing)
{
    static int const palette[] =
    {
        30, 34, 32, 36, 31, 35, 33, 37, /* Both lines (light and dark) are the same, */
        30, 34, 32, 36, 31, 35, 33, 37, /* light colors handling is done later */
    };

    char *buffer, *cur;
    unsigned int x, y;

    /* 20 bytes assumed for max length per pixel.
     * Add height*9 to that (zeroes color at the end and jump to next line) */
    buffer = malloc(((qq->height*9) + (qq->width * qq->height * 20)) * sizeof(char));
    cur = buffer;

    // *cur++ = '';

    for(y = 0; y < qq->height; y++)
    {
        uint8_t *lineattr = qq->attr + y * qq->width;
        uint8_t *linechar = qq->chars + y * qq->width;

        uint8_t prevfg = -1;
        uint8_t prevbg = -1;

        for(x = 0; x < qq->width; x++)
        {
            uint8_t fg = palette[lineattr[x] & 0x0f];
            uint8_t bg = (palette[lineattr[x] >> 4])+10;
            uint8_t c = linechar[x];

            if(!trailing)
                cur += sprintf(cur, "\033[");
            else
                cur += sprintf(cur, "\\033[");

            if(fg > 7)
                cur += sprintf(cur, "1;%d;%dm",fg,bg);
            else
                cur += sprintf(cur, "0;%d;%dm",fg,bg);
            *cur++ = c;
            if((c == '%') && trailing)
                *cur++ = c;
            prevfg = fg;
            prevbg = bg;
        }
        if(!trailing)
            cur += sprintf(cur, "\033[0m\n\r");
        else
            cur += sprintf(cur, "\\033[0m\\n\n");
    }

    /* Crop to really used size */
    buffer = realloc(buffer, (strlen(buffer) + 1) * sizeof(char));

    return buffer;
}
