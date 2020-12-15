.. include:: ../../../../Includes.txt

===============
link.editRecord
===============


Use this ViewHelper to provide edit links to records. The ViewHelper will
pass the uid and table to FormEngine.

The uid must be given as a positive integer.
For new records, use the newRecordViewHelper

= Examples =

<code title="Link to the record-edit action passed to FormEngine">
<be:link.editRecord uid="42" table="a_table" returnUrl="foo/bar" />
</code>
<output>
<a href="/typo3/index.php?route=/record/edit&edit[a_table][42]=edit&returnUrl=foo/bar">
  Edit record
</a>
</output>

Arguments
=========


additionalAttributes (anySimpleType)
------------------------------------


Additional tag attributes. They will be added directly to the resulting HTML tag.

data (anySimpleType)
--------------------


Additional data-* attributes. They will each be added with a &quot;data-&quot; prefix.

class (string)
--------------


CSS class(es) for this element

dir (string)
------------


Text direction for this HTML element. Allowed strings: &quot;ltr&quot; (left to right), &quot;rtl&quot; (right to left)

id (string)
-----------


Unique (in this file) identifier for this HTML element.

lang (string)
-------------


Language for this element. Use short names specified in RFC 1766

style (string)
--------------


Individual CSS styles for this element

title (string)
--------------


Tooltip text of element

accesskey (string)
------------------


Keyboard shortcut to access this element

tabindex (integer)
------------------


Specifies the tab order of this element

onclick (string)
----------------


JavaScript evaluated for the onclick event

uid (anySimpleType)
-------------------


uid of record to be edited

table (string)
--------------


target database table

returnUrl (string)
------------------