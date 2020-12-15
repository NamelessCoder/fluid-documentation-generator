.. include:: ../../../../Includes.txt

==============
uri.editRecord
==============


Use this ViewHelper to provide edit links (only the uri) to records. The ViewHelper will
pass the uid and table to FormEngine.

The uid must be given as a positive integer.
For new records, use the newRecordViewHelper

= Examples =

<code title="URI to the record-edit action passed to FormEngine">
<be:uri.editRecord uid="42" table="a_table" returnUrl="foo/bar" />
</code>
<output>
/typo3/index.php?route=/record/edit&edit[a_table][42]=edit&returnUrl=foo/bar
</output>

Arguments
=========


uid (anySimpleType)
-------------------


uid of record to be edited, 0 for creation

table (string)
--------------


target database table

returnUrl (string)
------------------