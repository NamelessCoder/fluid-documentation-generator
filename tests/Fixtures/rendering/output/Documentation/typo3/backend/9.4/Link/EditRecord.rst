.. include:: ../../../../Includes.txt

.. _typo3-backend-link-editrecord:

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


.. _link.editrecord_additionalattributes:
additionalAttributes
--------------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Additional tag attributes. They will be added directly to the resulting HTML tag.

.. _link.editrecord_data:
data
----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Additional data-* attributes. They will each be added with a "data-" prefix.

.. _link.editrecord_class:
class
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   CSS class(es) for this element

.. _link.editrecord_dir:
dir
---

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Text direction for this HTML element. Allowed strings: "ltr" (left to right), "rtl" (right to left)

.. _link.editrecord_id:
id
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Unique (in this file) identifier for this HTML element.

.. _link.editrecord_lang:
lang
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Language for this element. Use short names specified in RFC 1766

.. _link.editrecord_style:
style
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Individual CSS styles for this element

.. _link.editrecord_title:
title
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Tooltip text of element

.. _link.editrecord_accesskey:
accesskey
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Keyboard shortcut to access this element

.. _link.editrecord_tabindex:
tabindex
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Specifies the tab order of this element

.. _link.editrecord_onclick:
onclick
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   JavaScript evaluated for the onclick event

.. _link.editrecord_uid:
uid
---

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Uid of record to be edited

.. _link.editrecord_table:
table
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Target database table

.. _link.editrecord_returnurl:
returnUrl
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
