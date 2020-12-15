.. include:: ../../../../Includes.txt

==============
link.newRecord
==============


Use this ViewHelper to provide 'create new record' links.
The ViewHelper will pass the command to FormEngine.

The table argument is mandatory, it decides what record is to be created.

The pid argument will put the new record on this page, if 0 given it will
be placed to the root page.

The uid argument accepts only negative values. If this is given, the new
record will be placed (by sorting field) behind the record with the uid.
It will end up on the same pid as this given record, so the pid must not
be given explicitly by pid argument.

An exception will be thrown, if both uid and pid are given.
An exception will be thrown, if the uid argument is not a negative integer.

To edit records, use the editRecordViewHelper

= Examples =

<code title="Link to create a new record of a_table after record 17 on the same pid">
<be:link.newRecord table="a_table" returnUrl="foo/bar" uid="-17"/>
</code>
<output>
<a href="/typo3/index.php?route=/record/edit&edit[a_table][-17]=new&returnUrl=foo/bar">
  Edit record
</a>
</output>

<code title="Link to create a new record of a_table on root page">
<be:link.newRecord table="a_table" returnUrl="foo/bar""/>
</code>
<output>
<a href="/typo3/index.php?route=/record/edit&edit[a_table][]=new&returnUrl=foo/bar">
  Edit record
</a>
</output>

<code title="Link to create a new record of a_table on page 17">
<be:link.newRecord table="a_table" returnUrl="foo/bar" pid="17"/>
</code>
<output>
<a href="/typo3/index.php?route=/record/edit&edit[a_table][-17]=new&returnUrl=foo/bar">
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


uid &lt; 0 will insert the record after the given uid

pid (anySimpleType)
-------------------


the page id where the record will be created

table (string)
--------------


target database table

returnUrl (string)
------------------