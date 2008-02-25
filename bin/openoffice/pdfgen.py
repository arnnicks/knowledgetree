#!/usr/bin/env python
#
# $Id$
#
# KnowledgeTree Open Source Edition
# Document Management Made Simple
# Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
# 
# This program is free software; you can redistribute it and/or modify it under
# the terms of the GNU General Public License version 3 as published by the
# Free Software Foundation.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
# details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
# 
# You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
# Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
# 
# The interactive user interfaces in modified source and object code versions
# of this program must display Appropriate Legal Notices, as required under
# Section 5 of the GNU General Public License version 3.
# 
# In accordance with Section 7(b) of the GNU General Public License version 3,
# these Appropriate Legal Notices must retain the display of the "Powered by
# KnowledgeTree" logo and retain the original copyright notice. If the display of the 
# logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
# must display the words "Powered by KnowledgeTree" and retain the original 
# copyright notice. 
# Contributor( s): ______________________________________
#

import uno
import sys
from com.sun.star.beans import PropertyValue

NoConnectException = uno.getClass("com.sun.star.connection.NoConnectException")
IllegalArgumentException = uno.getClass("com.sun.star.lang.IllegalArgumentException")
RuntimeException = uno.getClass("com.sun.star.uno.RuntimeException")
IOException = uno.getClass("com.sun.star.io.IOException")

url_original = uno.systemPathToFileUrl(sys.argv[1])
url_save = uno.systemPathToFileUrl(sys.argv[2])

try:
    ### Get Service Manager
    context = uno.getComponentContext()
    resolver = context.ServiceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", context)
    ctx = resolver.resolve("uno:socket,host=localhost,port=8100;urp;StarOffice.ComponentContext")
    smgr = ctx.ServiceManager

    ### Load document
    properties = []
    p = PropertyValue()
    p.Name = "Hidden"
    p.Value = True
    properties.append(p)
    properties = tuple(properties)

    desktop = smgr.createInstanceWithContext("com.sun.star.frame.Desktop", ctx)

except NoConnectException, e:
    sys.stderr.write("OpenOffice process not found or not listening (" + e.Message + ")\n")
    sys.exit(1)
except IllegalArgumentException, e:
    sys.stderr.write("The url is invalid ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")

try:
    doc = desktop.loadComponentFromURL(url_original, "_blank", 0, properties)
except IOException, e:
    sys.stderr.write("URL couldn't be found or was corrupt (" + e.Message + ")\n")
    sys.exit(1)
except IllegalArgumentException, e:
    sys.stderr.write("Given parameters don't conform to the specification ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")

if doc == None:
    sys.stderr.write("The document could not be opened for conversion. This could indicate an unsupported mimetype.\n")
    sys.exit(1)
    

### Save File
properties = []
p = PropertyValue()
p.Name = "Overwrite"
p.Value = True
properties.append(p)
p = PropertyValue()
p.Name = "FilterName"
p.Value = 'writer_pdf_Export'
properties.append(p)
properties = tuple(properties)

try:
    doc.storeToURL(url_save, properties)
    doc.dispose()
except IOException, e:
    sys.stderr.write("URL (" + url_save + ") couldn't be found or was corrupt (" + e.Message + ")\n")
    sys.exit(1)
except IllegalArgumentException, e:
    sys.stderr.write("Given parameters don't conform to the specification ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")
